<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\BelongsToStore;

class Menu extends Model
{
    use BelongsToStore;

    protected $connection = 'mongodb';
    protected $collection = 'menus';

    protected $fillable = [
        'store_id',
        'title',
        'handle',
        'items',
        'is_active',
    ];

    protected $casts = [
        'items' => 'array',
        'is_active' => 'boolean',
    ];
}
