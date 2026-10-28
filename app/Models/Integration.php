<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\BelongsToStore;
use Illuminate\Support\Facades\Crypt;

class Integration extends Model
{
    use BelongsToStore;

    protected $connection = 'mongodb';
    protected $collection = 'integrations';

    protected $fillable = [
        'store_id',
        'provider_key', // e.g. 'razorpay', 'shiprocket', 'cashfree', 'phonepe', 'mailchimp', 'google_analytics'
        'name',
        'category', // 'payments', 'shipping', 'marketing', 'analytics', 'support', 'notifications'
        'is_connected',
        'is_enabled',
        'encrypted_credentials', // Encrypted string or json
        'settings', // public non-secret settings e.g. mode ('sandbox'/'live'), webhook_url
        'health_status', // 'healthy', 'unhealthy', 'pending', 'error'
        'last_tested_at',
        'last_error',
    ];

    protected $casts = [
        'is_connected' => 'boolean',
        'is_enabled' => 'boolean',
        'settings' => 'array',
        'last_tested_at' => 'datetime',
    ];

    /**
     * Store credentials safely encrypted
     */
    public function setCredentialsAttribute(array $credentials): void
    {
        $this->attributes['encrypted_credentials'] = Crypt::encryptString(json_encode($credentials));
    }

    /**
     * Retrieve decrypted credentials
     */
    public function getCredentialsAttribute(): array
    {
        if (empty($this->encrypted_credentials)) {
            return [];
        }

        try {
            return json_decode(Crypt::decryptString($this->encrypted_credentials), true) ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }
}
