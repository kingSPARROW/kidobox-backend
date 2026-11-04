<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\BelongsToStore;

class Collection extends Model
{
    use BelongsToStore;

    protected $connection = 'mongodb';
    protected $collection = 'collections';

    protected $fillable = [
        'store_id',
        'title',
        'slug',
        'description',
        'image_url',
        'image_alt',
        'heading_h1',
        'heading_h2',
        'heading_h3',
        'canonical_url',
        'meta_robots',
        'schema_markup',
        'type', // 'manual', 'automated'
        'rules', // e.g. [{"column": "tag", "relation": "equals", "condition": "summer"}]
        'sort_order', // 'best-selling', 'price-ascending', 'created-descending'
        'seo',
        'is_published',
    ];

    protected $casts = [
        'rules' => 'array',
        'seo' => 'array',
        'is_published' => 'boolean',
    ];
}
