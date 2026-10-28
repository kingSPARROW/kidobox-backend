<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\BelongsToStore;

class InventoryMovement extends Model
{
    use BelongsToStore;

    protected $connection = 'mongodb';
    protected $collection = 'inventory_movements';

    protected $fillable = [
        'store_id',
        'variant_id',
        'delta', // positive (restock) or negative (order/loss)
        'resulting_quantity',
        'reason', // 'order_placed', 'manual_adjustment', 'restock', 'cancellation'
        'reference_type', // 'order', 'manual'
        'reference_id',
    ];

    protected $casts = [
        'delta' => 'integer',
        'resulting_quantity' => 'integer',
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
