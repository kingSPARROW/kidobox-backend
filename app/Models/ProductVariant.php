<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\BelongsToStore;

class ProductVariant extends Model
{
    use BelongsToStore;

    protected $connection = 'mongodb';
    protected $collection = 'product_variants';

    protected $fillable = [
        'store_id',
        'product_id',
        'title', // e.g. "Small / Red" or "Default Title"
        'sku',
        'barcode',
        'price',
        'compare_at_price',
        'cost_per_item',
        'inventory_quantity',
        'weight',
        'weight_unit', // 'kg', 'g'
        'options_map', // ['Size' => 'M', 'Color' => 'Red']
        'image_url',
    ];

    protected $casts = [
        'price' => 'float',
        'compare_at_price' => 'float',
        'cost_per_item' => 'float',
        'inventory_quantity' => 'integer',
        'weight' => 'float',
        'options_map' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class, 'variant_id');
    }
}
