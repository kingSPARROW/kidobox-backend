<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\BelongsToStore;

class Banner extends Model
{
    use BelongsToStore;

    protected $connection = 'mongodb';
    protected $collection = 'banners';

    protected $fillable = [
        'store_id',
        'title',
        'title_tag',             // e.g. "Limited Edition • 40% Off", "New Season 2026"
        'subtitle',
        'desktop_image_url',     // High-res widescreen for desktop view
        'mobile_image_url',      // Aspect ratio optimized for mobile view
        'cta_text',
        'cta_link',
        'secondary_cta_text',
        'secondary_cta_link',
        'text_alignment',        // 'left', 'center', 'right'
        'overlay_opacity',       // 0 to 1 float
        'status',                // 'active', 'scheduled', 'inactive'
        'position',              // 'hero', 'promotional_strip', 'footer_banner'
        'order',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'overlay_opacity' => 'float',
        'order' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];
}
