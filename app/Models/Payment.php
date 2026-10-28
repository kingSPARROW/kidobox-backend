<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\BelongsToStore;

class Payment extends Model
{
    use BelongsToStore;

    protected $connection = 'mongodb';
    protected $collection = 'payments';

    protected $fillable = [
        'store_id',
        'order_id',
        'provider', // 'razorpay', 'cashfree', 'payu', 'phonepe', 'ccavenue', 'cod'
        'transaction_id',
        'gateway_order_id',
        'gateway_payment_id',
        'amount',
        'currency',
        'status', // 'pending', 'successful', 'failed', 'refunded'
        'idempotency_key',
        'is_signature_verified',
        'gateway_response',
        'failure_reason',
    ];

    protected $casts = [
        'amount' => 'float',
        'is_signature_verified' => 'boolean',
        'gateway_response' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
