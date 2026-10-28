<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\BelongsToStore;

class Cart extends Model
{
    use BelongsToStore;

    protected $connection = 'mongodb';
    protected $collection = 'carts';

    protected $fillable = [
        'store_id',
        'session_token',
        'customer_id',
        'items', // [ ['variant_id', 'product_id', 'title', 'sku', 'price', 'quantity', 'image_url'] ]
        'subtotal',
        'discount_code',
        'discount_amount',
        'shipping_fee',
        'tax_cgst',
        'tax_sgst',
        'tax_igst',
        'tax_total',
        'total',
        'currency',
        'expires_at',
    ];

    protected $casts = [
        'items' => 'array',
        'subtotal' => 'float',
        'discount_amount' => 'float',
        'shipping_fee' => 'float',
        'tax_cgst' => 'float',
        'tax_sgst' => 'float',
        'tax_igst' => 'float',
        'tax_total' => 'float',
        'total' => 'float',
        'expires_at' => 'datetime',
    ];
}
