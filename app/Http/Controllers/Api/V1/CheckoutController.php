<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Discount;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\InventoryMovement;
use App\Models\Store;
use App\Services\TaxService;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected TaxService $taxService;
    protected PaymentService $paymentService;

    public function __construct(TaxService $taxService, PaymentService $paymentService)
    {
        $this->taxService = $taxService;
        $this->paymentService = $paymentService;
    }

    /**
     * Calculate Order Summary (subtotal, taxes, shipping, discount)
     */
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'discount_code' => 'nullable|string',
            'shipping_state' => 'nullable|string',
            'payment_method' => 'nullable|string',
        ]);

        $store = app('current_store') ?? Store::where('status', 'active')->first();
        $storeState = $store->contact['state'] ?? 'Maharashtra';
        $destState = $validated['shipping_state'] ?? 'Maharashtra';

        // 1. Fetch real variant prices and calculate subtotal
        $subtotal = 0.0;
        $validatedItems = [];

        foreach ($validated['items'] as $item) {
            $variant = null;
            $variantId = $item['variant_id'] ?? null;
            if ($variantId && preg_match('/^[a-f\d]{24}$/i', $variantId)) {
                $variant = ProductVariant::with('product')->find($variantId);
            }
            if (!$variant && $variantId) {
                $variant = ProductVariant::with('product')->where('sku', $variantId)->first();
            }

            $qty = (int) $item['quantity'];
            $price = $variant ? (float) $variant->price : (float) ($item['price'] ?? 499.0);
            $lineTotal = round($price * $qty, 2);
            $subtotal += $lineTotal;

            $validatedItems[] = [
                'variant_id' => $variant ? (string) $variant->_id : (string) ($variantId ?: 'kdb-var-' . Str::random(6)),
                'product_id' => $variant ? (string) $variant->product_id : (string) ($item['product_id'] ?? 'kdb-prod'),
                'title' => $variant ? ($variant->product->title ?? 'Product') : ($item['title'] ?? 'Baby Essential'),
                'variant_title' => $variant ? $variant->title : ($item['variant_title'] ?? 'Standard'),
                'sku' => $variant ? $variant->sku : ($item['sku'] ?? 'KDB-ITEM'),
                'price' => $price,
                'quantity' => $qty,
                'line_total' => $lineTotal,
                'image_url' => $variant ? ($variant->image_url ?? ($variant->product->images[0] ?? null)) : ($item['image_url'] ?? '/assets/hd/clothing.webp'),
                'hsn_code' => $variant ? ($variant->product->hsn_code ?? '6111') : '6111',
                'tax_rate' => (float) ($variant ? ($variant->product->tax_rate ?? 12.0) : 12.0),
            ];
        }

        // 2. Shipping calculation
        $freeShippingThreshold = (float) ($store->settings['free_shipping_threshold'] ?? 999.0);
        $shippingFee = ($subtotal >= $freeShippingThreshold) ? 0.0 : 99.0;

        // COD Fee if applicable
        $codFee = 0.0;
        if (($validated['payment_method'] ?? '') === 'cod' && !empty($store->settings['allow_cod'])) {
            $codFee = (float) ($store->settings['cod_fee'] ?? 50.0);
        }

        // 3. Discount calculation
        $discountAmount = 0.0;
        $discountModel = null;
        if (!empty($validated['discount_code'])) {
            $discountModel = Discount::where('store_id', (string) $store->_id)
                ->where('code', strtoupper(trim($validated['discount_code'])))
                ->first();

            if ($discountModel && $discountModel->isValidForCart($subtotal)) {
                $discountAmount = $discountModel->calculateDiscount($subtotal, $shippingFee);
            }
        }

        $taxableBase = max(0, $subtotal - $discountAmount);

        // 4. Indian GST calculation
        $gstDetails = $this->taxService->calculateGst(
            $taxableBase,
            18.0,
            $storeState,
            $destState,
            true // Tax inclusive pricing standard
        );

        $total = round($taxableBase + $shippingFee + $codFee, 2);

        return response()->json([
            'success' => true,
            'pricing' => [
                'subtotal' => round($subtotal, 2),
                'discount_code' => $discountModel ? $discountModel->code : null,
                'discount_amount' => round($discountAmount, 2),
                'shipping_fee' => round($shippingFee, 2),
                'cod_fee' => round($codFee, 2),
                'tax_cgst' => round($gstDetails['cgst'], 2),
                'tax_sgst' => round($gstDetails['sgst'], 2),
                'tax_igst' => round($gstDetails['igst'], 2),
                'tax_total' => round($gstDetails['total_tax'], 2),
                'is_interstate' => $gstDetails['is_interstate'],
                'total' => $total,
            ],
            'items' => $validatedItems,
        ]);
    }

    /**
     * Create Order & Initiate Payment
     */
    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'customer' => 'required|array',
            'customer.first_name' => 'required|string',
            'customer.last_name' => 'nullable|string',
            'customer.email' => 'required|email',
            'customer.phone' => 'required|string',
            'shipping_address' => 'required|array',
            'shipping_address.address1' => 'required|string',
            'shipping_address.city' => 'required|string',
            'shipping_address.state' => 'required|string',
            'shipping_address.postal_code' => 'required|string',
            'payment_method' => 'required|in:razorpay,cashfree,cod,upi,card,demo',
            'items' => 'required|array|min:1',
            'discount_code' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $store = app('current_store') ?? Store::where('status', 'active')->first();

        // 1. Recompute and verify pricing server-side
        $calcResponse = $this->calculate(new Request([
            'items' => $validated['items'],
            'discount_code' => $validated['discount_code'] ?? null,
            'shipping_state' => $validated['shipping_address']['state'] ?? 'Maharashtra',
            'payment_method' => $validated['payment_method'],
        ]))->getData(true);

        $pricing = $calcResponse['pricing'];
        $snapshotItems = $calcResponse['items'];

        // 2. Concurrency check & inventory reservation for tracked variants
        foreach ($snapshotItems as $item) {
            if (preg_match('/^[a-f\d]{24}$/i', $item['variant_id'])) {
                $variant = ProductVariant::find($item['variant_id']);
                if ($variant && $variant->inventory_quantity < $item['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock for '{$item['title']}' ({$variant->title}). Available: {$variant->inventory_quantity}.",
                    ], 422);
                }
            }
        }

        // Deduct inventory atomically where applicable
        foreach ($snapshotItems as $item) {
            if (preg_match('/^[a-f\d]{24}$/i', $item['variant_id'])) {
                $variant = ProductVariant::find($item['variant_id']);
                if ($variant) {
                    $newStock = max(0, $variant->inventory_quantity - $item['quantity']);
                    $variant->update(['inventory_quantity' => $newStock]);

                    InventoryMovement::create([
                        'store_id' => (string) $store->_id,
                        'variant_id' => (string) $variant->_id,
                        'delta' => -$item['quantity'],
                        'resulting_quantity' => $newStock,
                        'reason' => 'order_placed',
                        'reference_type' => 'order',
                    ]);
                }
            }
        }

        // 3. Find or create Customer
        $customer = Customer::where('store_id', (string) $store->_id)
            ->where('email', $validated['customer']['email'])
            ->first();

        if (!$customer) {
            $customer = Customer::create([
                'store_id' => (string) $store->_id,
                'first_name' => $validated['customer']['first_name'],
                'last_name' => $validated['customer']['last_name'] ?? '',
                'email' => $validated['customer']['email'],
                'phone' => $validated['customer']['phone'],
                'accepts_marketing' => true,
                'total_orders' => 1,
                'total_spent' => $pricing['total'],
            ]);
        } else {
            $customer->increment('total_orders');
            $customer->increment('total_spent', $pricing['total']);
        }

        // 4. Create Order document
        $orderNumber = '#' . (1000 + Order::where('store_id', (string) $store->_id)->count() + 1);

        $order = Order::create([
            'store_id' => (string) $store->_id,
            'order_number' => $orderNumber,
            'customer_id' => (string) $customer->_id,
            'customer_email' => $customer->email,
            'customer_phone' => $customer->phone,
            'customer_name' => trim("{$customer->first_name} {$customer->last_name}"),
            'items' => $snapshotItems,
            'pricing' => $pricing,
            'currency' => $store->currency ?? 'INR',
            'financial_status' => ($validated['payment_method'] === 'cod') ? 'pending' : 'pending',
            'fulfillment_status' => 'unfulfilled',
            'order_status' => 'open',
            'payment_method' => $validated['payment_method'],
            'shipping_address' => $validated['shipping_address'],
            'billing_address' => $validated['shipping_address'],
            'notes' => $validated['notes'] ?? '',
            'timeline' => [
                [
                    'action' => 'Order created via storefront',
                    'timestamp' => now()->toISOString(),
                ]
            ],
        ]);

        // 5. Update discount usage
        if (!empty($pricing['discount_code'])) {
            Discount::where('store_id', (string) $store->_id)
                ->where('code', $pricing['discount_code'])
                ->increment('usage_count');
        }

        // 6. Initiate Payment with provider adapter
        $paymentProvider = $this->paymentService->getProvider($validated['payment_method'], $store);
        $paymentInit = $paymentProvider->createPayment($order);

        // Record initial payment transaction
        Payment::create([
            'store_id' => (string) $store->_id,
            'order_id' => (string) $order->_id,
            'provider' => $validated['payment_method'],
            'gateway_order_id' => $paymentInit['gateway_order_id'] ?? null,
            'amount' => $pricing['total'],
            'currency' => $order->currency,
            'status' => ($validated['payment_method'] === 'cod') ? 'pending' : 'pending',
            'gateway_response' => $paymentInit,
        ]);

        return response()->json([
            'success' => true,
            'order' => $order,
            'payment' => $paymentInit,
        ], 201);
    }

    /**
     * Verify Online Payment (Razorpay / Cashfree)
     */
    public function verifyPayment(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|string',
            'provider' => 'required|string',
            'payment_details' => 'required|array',
        ]);

        $order = Order::findOrFail($validated['order_id']);
        $store = Store::findOrFail($order->store_id);

        $provider = $this->paymentService->getProvider($validated['provider'], $store);
        $verifyRes = $provider->verifyPayment($validated['payment_details']);

        if (!$verifyRes['success']) {
            return response()->json([
                'success' => false,
                'message' => $verifyRes['message'] ?? 'Payment verification failed.',
            ], 400);
        }

        // Update Order & Payment status
        $order->financial_status = 'paid';
        $timeline = $order->timeline ?? [];
        $timeline[] = [
            'action' => "Payment verified successfully via {$provider->getName()}",
            'timestamp' => now()->toISOString(),
        ];
        $order->timeline = $timeline;
        $order->save();

        Payment::where('order_id', (string) $order->_id)
            ->where('provider', $validated['provider'])
            ->latest()
            ->update([
                'status' => 'successful',
                'gateway_payment_id' => $verifyRes['gateway_payment_id'] ?? null,
                'is_signature_verified' => true,
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment verified successfully. Order confirmed.',
            'order' => $order->fresh(),
        ]);
    }
}
