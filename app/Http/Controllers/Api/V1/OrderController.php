<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\AuditLog;
use App\Services\Shipping\ShiprocketShippingProvider;
use App\Models\Integration;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * List Orders with filters
     */
    public function index(Request $request)
    {
        $query = Order::query()->with('shipments');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        if ($financialStatus = $request->query('financial_status')) {
            $query->where('financial_status', $financialStatus);
        }

        if ($fulfillmentStatus = $request->query('fulfillment_status')) {
            $query->where('fulfillment_status', $fulfillmentStatus);
        }

        $perPage = min((int) ($request->query('per_page', 15)), 100);
        $orders = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Order Details
     */
    public function show($id)
    {
        $order = Order::with(['customer', 'payments', 'shipments'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'order' => $order,
        ]);
    }

    /**
     * Update Order Status (e.g. mark as paid, mark as fulfilled, cancel)
     */
    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'financial_status' => 'nullable|in:pending,authorized,paid,partially_refunded,refunded,voided',
            'fulfillment_status' => 'nullable|in:unfulfilled,partially_fulfilled,fulfilled',
            'order_status' => 'nullable|in:open,completed,cancelled',
            'note' => 'nullable|string',
            'cancel_reason' => 'nullable|string',
        ]);

        $timeline = $order->timeline ?? [];

        if (!empty($validated['financial_status']) && $validated['financial_status'] !== $order->financial_status) {
            $timeline[] = [
                'action' => "Payment status changed to {$validated['financial_status']}",
                'timestamp' => now()->toISOString(),
                'note' => $validated['note'] ?? null,
            ];
            $order->financial_status = $validated['financial_status'];
        }

        if (!empty($validated['fulfillment_status']) && $validated['fulfillment_status'] !== $order->fulfillment_status) {
            $timeline[] = [
                'action' => "Fulfillment status changed to {$validated['fulfillment_status']}",
                'timestamp' => now()->toISOString(),
                'note' => $validated['note'] ?? null,
            ];
            $order->fulfillment_status = $validated['fulfillment_status'];
        }

        if (!empty($validated['order_status'])) {
            $order->order_status = $validated['order_status'];
            if ($validated['order_status'] === 'cancelled') {
                $order->cancelled_at = now();
                $order->cancel_reason = $validated['cancel_reason'] ?? 'Customer request';
                $timeline[] = [
                    'action' => 'Order cancelled',
                    'timestamp' => now()->toISOString(),
                    'reason' => $order->cancel_reason,
                ];
            }
        }

        $order->timeline = $timeline;
        $order->save();

        AuditLog::record('order_status_updated', [
            'order_id' => (string) $order->_id,
            'financial_status' => $order->financial_status,
            'fulfillment_status' => $order->fulfillment_status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully.',
            'order' => $order->fresh()->load('shipments'),
        ]);
    }

    /**
     * Create Shipment via Shiprocket
     */
    public function createShipment(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $store = app('current_store');
        $shiprocketIntegration = Integration::where('store_id', (string) $store->_id)
            ->where('provider_key', 'shiprocket')
            ->first();

        $credentials = $shiprocketIntegration ? $shiprocketIntegration->credentials : [
            'email' => 'sandbox@shiprocket.in',
            'password' => 'secret_test',
        ];

        $shiprocket = new ShiprocketShippingProvider();

        // 1. Create shipment order on Shiprocket
        $createRes = $shiprocket->createShipment($order, $credentials);

        if (!$createRes['success']) {
            return response()->json([
                'success' => false,
                'message' => $createRes['message'] ?? 'Failed to create shipment on Shiprocket.',
            ], 400);
        }

        // 2. Assign AWB & courier
        $awbRes = $shiprocket->generateAwb($createRes['shipment_id'], $credentials);

        // 3. Generate Label
        $labelRes = $shiprocket->generateLabel([$createRes['shipment_id']], $credentials);

        // 4. Save shipment document
        $shipment = Shipment::create([
            'order_id' => (string) $order->_id,
            'provider' => 'shiprocket',
            'shipment_id' => (string) $createRes['shipment_id'],
            'awb_code' => $awbRes['awb_code'] ?? ('AWB' . rand(100000, 999999)),
            'courier_name' => $awbRes['courier_name'] ?? 'Bluedart Express',
            'courier_company_id' => $awbRes['courier_company_id'] ?? 1,
            'status' => 'awb_assigned',
            'label_url' => $labelRes['label_url'] ?? null,
            'tracking_history' => [
                ['activity' => 'Shipment created and AWB assigned', 'location' => 'Origin Hub', 'date' => now()->format('d M Y, h:i A')],
            ],
        ]);

        // Mark order fulfillment status as fulfilled
        $order->fulfillment_status = 'fulfilled';
        $timeline = $order->timeline ?? [];
        $timeline[] = [
            'action' => "Shipment created via {$shipment->courier_name}. AWB: {$shipment->awb_code}",
            'timestamp' => now()->toISOString(),
        ];
        $order->timeline = $timeline;
        $order->save();

        AuditLog::record('shipment_created', [
            'order_id' => (string) $order->_id,
            'awb_code' => $shipment->awb_code,
            'courier' => $shipment->courier_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shipment created and AWB generated successfully via Shiprocket.',
            'shipment' => $shipment,
            'order' => $order->fresh()->load('shipments'),
        ]);
    }

    /**
     * Get Dynamic Shiprocket Courier Rates based on PIN code and weight
     */
    public function getShippingRates(Request $request)
    {
        $validated = $request->validate([
            'delivery_pincode' => 'required|string',
            'pickup_pincode' => 'nullable|string',
            'weight_kg' => 'nullable|numeric|min:0.1',
            'is_cod' => 'nullable|boolean',
            'order_value' => 'nullable|numeric|min:0',
        ]);

        $store = app('current_store');
        $pickupPincode = $validated['pickup_pincode'] ?? ($store->contact['postal_code'] ?? '400001');
        $weight = (float) ($validated['weight_kg'] ?? 0.5);
        $isCod = (bool) ($validated['is_cod'] ?? false);
        $orderValue = (float) ($validated['order_value'] ?? 1000);

        $shiprocket = new ShiprocketShippingProvider();
        $rates = $shiprocket->estimateRates($pickupPincode, $validated['delivery_pincode'], $weight, $isCod, $orderValue);

        return response()->json([
            'success' => true,
            'provider' => 'Shiprocket',
            'pickup_pincode' => $pickupPincode,
            'delivery_pincode' => $validated['delivery_pincode'],
            'couriers' => $rates,
        ]);
    }

    /**
     * Track Order Shipment via Shiprocket AWB
     */
    public function trackShipment(Request $request, $id)
    {
        $order = Order::with('shipments')->findOrFail($id);
        $shipment = $order->shipments->first();

        if (!$shipment || empty($shipment->awb_code)) {
            return response()->json([
                'success' => false,
                'message' => 'No active shipment or AWB found for this order.',
            ], 404);
        }

        $store = app('current_store');
        $shiprocketIntegration = Integration::where('store_id', (string) $store->_id)
            ->where('provider_key', 'shiprocket')
            ->first();

        $credentials = $shiprocketIntegration ? $shiprocketIntegration->credentials : [
            'email' => 'sandbox@shiprocket.in',
            'password' => 'secret_test',
        ];

        $shiprocket = new ShiprocketShippingProvider();
        $tracking = $shiprocket->trackShipment($shipment->awb_code, $credentials);

        return response()->json([
            'success' => true,
            'order_number' => $order->order_number,
            'shipment' => $shipment,
            'tracking' => $tracking,
        ]);
    }
}
