<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use App\Models\Store;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function index()
    {
        $discounts = Discount::latest()->get();

        return response()->json([
            'success' => true,
            'data' => $discounts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'title' => 'nullable|string|max:255',
            'type' => 'required|in:percentage,fixed_amount,fixed,free_shipping',
            'value' => 'required|numeric|min:0',
            'min_cart_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        $code = strtoupper(trim($validated['code']));
        $type = $validated['type'] === 'fixed' ? 'fixed_amount' : $validated['type'];
        $title = !empty($validated['title']) ? $validated['title'] : ($code . ' Promotion');

        $store = app('current_store') ?? Store::first();

        $discount = Discount::create([
            'store_id' => $store ? (string) $store->_id : null,
            'code' => $code,
            'title' => $title,
            'type' => $type,
            'value' => (float) $validated['value'],
            'min_cart_amount' => (float) ($validated['min_cart_amount'] ?? 0),
            'max_discount_amount' => isset($validated['max_discount_amount']) ? (float) $validated['max_discount_amount'] : null,
            'usage_limit' => (int) ($validated['usage_limit'] ?? 0),
            'usage_count' => 0,
            'starts_at' => $validated['starts_at'] ?? now(),
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        AuditLog::record('discount_created', ['discount_id' => (string) $discount->_id, 'code' => $discount->code]);

        return response()->json([
            'success' => true,
            'message' => 'Discount promotion created successfully.',
            'discount' => $discount,
        ], 201);
    }

    public function toggle($id)
    {
        $discount = Discount::findOrFail($id);
        $discount->update(['is_active' => !$discount->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Discount status toggled.',
            'discount' => $discount->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $discount = Discount::findOrFail($id);
        $discount->delete();

        return response()->json([
            'success' => true,
            'message' => 'Discount deleted successfully.',
        ]);
    }
}
