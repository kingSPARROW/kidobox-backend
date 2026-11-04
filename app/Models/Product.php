<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\BelongsToStore;

class Product extends Model
{
    use BelongsToStore;

    protected $connection = 'mongodb';
    protected $collection = 'products';

    protected $fillable = [
        'store_id',
        'title',
        'slug',
        'description',
        'vendor',
        'product_type',
        'tags',
        'status', // 'draft', 'active', 'archived'
        'images', // array of image objects/URLs
        'options', // e.g. [{"name": "Size", "values": ["S", "M", "L"]}]
        'has_variants',
        'hsn_code',
        'tax_rate',
        'seo', // ['title', 'description', 'canonical_url', 'meta_robots', 'schema_markup', 'focus_keyword']
        'seo_title',
        'seo_description',
        'heading_h1',
        'heading_h2',
        'heading_h3',
        'canonical_url',
        'meta_robots',
        'schema_markup',
        'focus_keyword',
        'collection_ids',
    ];

    protected $casts = [
        'images' => 'array',
        'tags' => \App\Casts\NativeArray::class,
        'options' => 'array',
        'has_variants' => 'boolean',
        'tax_rate' => 'float',
        'seo' => 'array',
        'collection_ids' => \App\Casts\NativeArray::class,
    ];

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }
}
