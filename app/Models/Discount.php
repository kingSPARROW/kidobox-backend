<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\BelongsToStore;

class Discount extends Model
{
    use BelongsToStore;

    protected $connection = 'mongodb';
    protected $collection = 'discounts';

    protected $fillable = [
        'store_id',
        'code',
        'title',
        'type', // 'percentage', 'fixed_amount', 'free_shipping'
        'value', // e.g. 15 for 15% or 200 for ₹200
        'min_cart_amount',
        'max_discount_amount',
        'usage_limit',
        'usage_count',
        'is_active',
        'starts_at',
        'expires_at',
        'eligible_collection_ids',
        'eligible_product_ids',
    ];

    protected $casts = [
        'value' => 'float',
        'min_cart_amount' => 'float',
        'max_discount_amount' => 'float',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'eligible_collection_ids' => 'array',
        'eligible_product_ids' => 'array',
    ];

    public function isValidForCart(float $subtotal): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && now()->gt($this->expires_at)) {
            return false;
        }

        if ($this->usage_limit > 0 && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        if ($this->min_cart_amount > 0 && $subtotal < $this->min_cart_amount) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $subtotal, float $shippingFee = 0): float
    {
        if (!$this->isValidForCart($subtotal)) {
            return 0.0;
        }

        if ($this->type === 'free_shipping') {
            return $shippingFee;
        }

        if ($this->type === 'percentage') {
            $discount = ($subtotal * $this->value) / 100.0;
            if ($this->max_discount_amount > 0 && $discount > $this->max_discount_amount) {
                return $this->max_discount_amount;
            }
            return round($discount, 2);
        }

        if ($this->type === 'fixed_amount') {
            return min($this->value, $subtotal);
        }

        return 0.0;
    }
}
