<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use App\Traits\BelongsToStore;

class Theme extends Model
{
    use BelongsToStore;

    protected $connection = 'mongodb';
    protected $collection = 'themes';

    protected $fillable = [
        'store_id',
        'name',
        'preset', // 'modern_minimal', 'luxury_chic', 'bold_vibrant', 'artisan_craft'
        'is_active',
        'is_published',
        'config', // Structured JSON containing sections, branding, colors, typography
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_published' => 'boolean',
        'config' => 'array',
    ];

    /**
     * Default starter theme configuration
     */
    public static function getDefaultThemeConfig(string $storeName = 'KIDOBOX'): array
    {
        return [
            'branding' => [
                'logo_text' => $storeName,
                'logo_url' => '/logo.png',
                'favicon_url' => '/logo.png',
                'default_mode' => 'girl', // 'boy' or 'girl'
                'primary_color' => '#ff268b', // Rose Pink
                'secondary_color' => '#0284c7', // Sky Blue
                'girl_color' => '#ff268b',
                'boy_color' => '#0284c7',
                'accent_color' => '#ff8bbb',
                'font_family' => 'Fredoka, Nunito, sans-serif',
                'border_radius' => '1.25rem',
            ],
            'announcement_bar' => [
                'enabled' => true,
                'free_shipping_text' => 'Free shipping over ₹999',
                'free_shipping_threshold' => 999,
                'returns_text' => '7-day easy returns',
                'guarantee_text' => '100% Original Products',
                'tagline' => 'Little Needs. Big Smiles.',
                'background_color' => '#fff0f6',
                'text_color' => '#122e50',
            ],
            'header' => [
                'sticky' => true,
                'show_search' => true,
                'show_cart_count' => true,
                'navigation_links' => [
                    ['label' => 'Home', 'href' => '/'],
                    ['label' => 'All Products', 'href' => '/products'],
                    ['label' => 'Featured', 'href' => '/collections/featured'],
                    ['label' => 'About Us', 'href' => '/pages/about'],
                    ['label' => 'Track Order', 'href' => '/tracking'],
                ],
            ],
            'sections' => [
                [
                    'id' => 'hero_1',
                    'type' => 'hero_banner',
                    'enabled' => true,
                    'title' => 'Curated Living. Elevated Design.',
                    'subtitle' => 'Explore handcrafted luxury homeware, contemporary furniture, and premium decor.',
                    'cta_text' => 'Shop Collections',
                    'cta_link' => '/products',
                    'image_url' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=1600&q=80',
                    'overlay_opacity' => 0.45,
                ],
                [
                    'id' => 'features_1',
                    'type' => 'value_props',
                    'enabled' => true,
                    'items' => [
                        ['icon' => 'Truck', 'title' => 'Fast Express Delivery', 'desc' => 'Across 28,000+ PIN codes with live Shiprocket tracking'],
                        ['icon' => 'ShieldCheck', 'title' => 'Secure Payments', 'desc' => 'Cards, UPI, Netbanking & Verified COD'],
                        ['icon' => 'RotateCcw', 'title' => '7-Day Easy Returns', 'desc' => 'Hassle-free return & exchange policy'],
                        ['icon' => 'Award', 'title' => 'Authentic Quality', 'desc' => '100% genuine guaranteed items direct from makers'],
                    ],
                ],
                [
                    'id' => 'featured_products_1',
                    'type' => 'featured_products',
                    'enabled' => true,
                    'title' => 'Trending & New Arrivals',
                    'subtitle' => 'Handpicked favorites designed for modern lifestyle',
                    'limit' => 8,
                ],
                [
                    'id' => 'categories_1',
                    'type' => 'category_grid',
                    'enabled' => true,
                    'title' => 'Browse by Category',
                    'subtitle' => 'Find the perfect piece for every space',
                ],
                [
                    'id' => 'newsletter_1',
                    'type' => 'newsletter',
                    'enabled' => true,
                    'title' => 'Join the HomZy Inner Circle',
                    'subtitle' => 'Subscribe to receive exclusive launches, private sales, and 10% off your first order.',
                    'button_text' => 'Subscribe',
                ],
            ],
            'footer' => [
                'tagline' => 'Crafting inspiring environments for modern homes.',
                'copyright' => '© ' . date('Y') . ' ' . $storeName . '. Powered by HomZy Commerce.',
                'social_links' => [
                    ['platform' => 'Instagram', 'url' => 'https://instagram.com'],
                    ['platform' => 'Twitter', 'url' => 'https://twitter.com'],
                    ['platform' => 'Facebook', 'url' => 'https://facebook.com'],
                ],
            ],
        ];
    }
}
