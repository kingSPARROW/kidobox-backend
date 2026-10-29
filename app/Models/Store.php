<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Store extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'stores';

    protected $fillable = [
        'merchant_id',
        'name',
        'slug',
        'custom_domain',
        'currency',
        'timezone',
        'status', // 'active', 'setup', 'suspended'
        'content',
        'branding', // ['logo_url', 'favicon_url', 'primary_color', 'font_family']
        'contact', // ['email', 'phone', 'support_email', 'address', 'city', 'state', 'postal_code', 'country']
        'settings', // ['tax_mode', 'default_gst_rate', 'allow_cod', 'cod_fee', 'free_shipping_threshold']
        'seo', // ['title_tag', 'meta_description', 'social_image_url', 'google_verification', 'allow_indexing', 'canonical_url']
        'onboarding_completed',
        'onboarding_step',
    ];

    protected $casts = [
        'content' => 'array',
        'branding' => 'array',
        'contact' => 'array',
        'settings' => 'array',
        'seo' => 'array',
        'onboarding_completed' => 'boolean',
        'onboarding_step' => 'integer',
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'store_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'store_id');
    }

    public function collections()
    {
        return $this->hasMany(Collection::class, 'store_id');
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'store_id');
    }

    public function discounts()
    {
        return $this->hasMany(Discount::class, 'store_id');
    }

    public function integrations()
    {
        return $this->hasMany(Integration::class, 'store_id');
    }

    public function theme()
    {
        return $this->hasOne(Theme::class, 'store_id')->where('is_active', true);
    }
}
