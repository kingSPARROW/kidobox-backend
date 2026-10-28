<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class AuditLog extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'audit_logs';

    protected $fillable = [
        'store_id',
        'actor_type', // 'merchant', 'platform_admin', 'system'
        'actor_id',
        'actor_email',
        'action', // 'store_created', 'order_status_updated', 'integration_connected', 'refund_issued'
        'details',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public static function record(string $action, array $details = [], ?string $storeId = null): self
    {
        $actor = auth()->user();

        return static::create([
            'store_id' => $storeId ?? (app()->bound('current_store') && app('current_store') ? (string) app('current_store')->_id : null),
            'actor_type' => $actor ? class_basename($actor) : 'system',
            'actor_id' => $actor ? (string) ($actor->_id ?? $actor->id) : null,
            'actor_email' => $actor ? $actor->email : null,
            'action' => $action,
            'details' => $details,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
