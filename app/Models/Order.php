<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\BelongsToStore;

class Order extends Model
{
    use BelongsToStore;

    protected $connection = 'mongodb';
    protected $collection = 'orders';

    protected $fillable = [
        'store_id',
        'order_number', // e.g. "#1001"
        'customer_id',
        'customer_email',
        'customer_phone',
        'customer_name',
        'items', // Snapshot array: [{variant_id, product_id, title, variant_title, sku, price, quantity, total, hsn_code, tax_rate}]
        'pricing', // ['subtotal', 'discount_code', 'discount_amount', 'shipping_fee', 'tax_cgst', 'tax_sgst', 'tax_igst', 'tax_total', 'total']
        'currency',
        'financial_status', // 'pending', 'authorized', 'paid', 'partially_refunded', 'refunded', 'voided'
        'fulfillment_status', // 'unfulfilled', 'partially_fulfilled', 'fulfilled'
        'order_status', // 'open', 'completed', 'cancelled'
        'payment_method', // 'razorpay', 'cashfree', 'payu', 'phonepe', 'ccavenue', 'cod'
        'shipping_address', // ['name', 'phone', 'address1', 'address2', 'city', 'state', 'postal_code', 'country']
        'billing_address',
        'notes',
        'timeline', // Array of events: [{action, timestamp, note, user}]
        'cancelled_at',
        'cancel_reason',
    ];

    protected $casts = [
        'items' => 'array',
        'pricing' => 'array',
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'timeline' => 'array',
        'cancelled_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'order_id');
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'order_id');
    }
}
