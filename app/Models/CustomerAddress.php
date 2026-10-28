<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\BelongsToStore;

class CustomerAddress extends Model
{
    use BelongsToStore;

    protected $connection = 'mongodb';
    protected $collection = 'customer_addresses';

    protected $fillable = [
        'store_id',
        'customer_id',
        'first_name',
        'last_name',
        'company',
        'phone',
        'address1',
        'address2',
        'city',
        'state',
        'postal_code',
        'country',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
