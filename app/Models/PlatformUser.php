<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class PlatformUser extends Model implements AuthenticatableContract
{
    use Authenticatable, HasApiTokens;

    protected $connection = 'mongodb';
    protected $collection = 'platform_users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // 'super_admin', 'support_lead', 'auditor'
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];
}
