<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\BelongsToStore;

class Testimonial extends Model
{
    use BelongsToStore;

    protected $connection = 'mongodb';
    protected $collection = 'testimonials';

    protected $fillable = [
        'store_id',
        'author_name',
        'avatar_url',
        'rating',
        'content',
        'location',
        'is_verified',
        'status',
        'order',
        'product_title',
    ];

    protected $casts = [
        'rating' => 'integer',
        'order' => 'integer',
        'is_verified' => 'boolean',
    ];
}
