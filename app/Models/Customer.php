<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\BelongsToStore;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Model implements AuthenticatableContract
{
    use BelongsToStore, Authenticatable, HasApiTokens;

    protected $connection = 'mongodb';
    protected $collection = 'customers';

    protected $fillable = [
        'store_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'tags',
        'notes',
        'accepts_marketing',
        'total_orders',
        'total_spent',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'tags' => 'array',
        'accepts_marketing' => 'boolean',
        'total_orders' => 'integer',
        'total_spent' => 'float',
        'last_login_at' => 'datetime',
    ];

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class, 'customer_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }
}
