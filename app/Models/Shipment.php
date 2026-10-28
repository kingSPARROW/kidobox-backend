<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\BelongsToStore;

class Shipment extends Model
{
    use BelongsToStore;

    protected $connection = 'mongodb';
    protected $collection = 'shipments';

    protected $fillable = [
        'store_id',
        'order_id',
        'provider', // 'shiprocket', 'delhivery', 'manual'
        'shipment_id',
        'awb_code',
        'courier_name',
        'courier_company_id',
        'status', // 'created', 'awb_assigned', 'pickup_scheduled', 'in_transit', 'out_for_delivery', 'delivered', 'rto', 'cancelled'
        'label_url',
        'manifest_url',
        'pickup_token_number',
        'tracking_url',
        'tracking_history', // [ ['status', 'activity', 'location', 'date'] ]
        'error_logs',
    ];

    protected $casts = [
        'tracking_history' => 'array',
        'error_logs' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
