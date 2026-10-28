<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Merchant extends Model implements AuthenticatableContract
{
    use Authenticatable, HasApiTokens;

    protected $connection = 'mongodb';
    protected $collection = 'merchants';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'status', // 'active', 'suspended'
        'subscription_plan', // 'starter', 'pro', 'enterprise'
        'email_verified_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    public function stores()
    {
        return $this->hasMany(Store::class, 'merchant_id');
    }

    public function primaryStore()
    {
        return $this->hasOne(Store::class, 'merchant_id')->oldest();
    }
}
