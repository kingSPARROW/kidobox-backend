<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Store;
use App\Models\Banner;
use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Menu;

class HomzyRealDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get or Update Store
        $store = Store::first() ?? new Store();
        $store->name = 'HOMZY';
        $store->slug = 'homzy';
        $store->status = 'active';
        $store->currency = 'INR';
        $store->branding = [
            'logo_url' => '',
            'favicon_url' => '',
            'primary_color' => '#c55325',
            'font_family' => 'Inter, sans-serif',
        ];
        $store->contact = [
            'email' => 'support@homzy.com',
            'phone' => '+91 98765 43210',
            'support_email' => 'care@homzy.com',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'country' => 'India',
        ];
        $store->seo = [
            'title_tag' => 'HOMZY | Home Made Easy - A Cleaner, Calmer, Happier Home',
            'meta_description' => 'Thoughtfully curated home essentials for a cleaner, calmer and happier home.',
            'social_image_url' => '/banners/HOMZY Cozy Home Essentials Banner.png',
            'allow_indexing' => true,
        ];
        $store->save();
        $storeId = (string) $store->_id;

        // 2. Clear old dummy banners and insert real Homzy banners
        Banner::where('store_id', $storeId)->delete();
        Banner::where('title', 'like', '%Artisanal Living%')->delete();
        Banner::where('title', 'like', '%Ceramic Studio%')->delete();

        Banner::create([
            'store_id' => $storeId,
            'title' => 'Home Made Easy',
            'title_tag' => 'A BEAUTIFUL HOME, A CALMER YOU',
            'subtitle' => "Thoughtfully curated home essentials for a\ncleaner, calmer and happier home.",
            'desktop_image_url' => '/banners/homzy-cozy-banner.png',
            'mobile_image_url' => '/banners/organized-home-mobile.png',
            'cta_text' => 'Shop Now',
            'cta_link' => '/products',
            'secondary_cta_text' => 'Explore Collections',
            'secondary_cta_link' => '/collections',
            'text_alignment' => 'left',
            'overlay_opacity' => 0.5,
            'status' => 'active',
            'position' => 'hero',
            'order' => 1,
        ]);

        Banner::create([
            'store_id' => $storeId,
            'title' => 'Festive Home Essentials',
            'title_tag' => 'FESTIVE SEASON EXCLUSIVE',
            'subtitle' => "Tiered metal plant stands, artisanal planters & organic aromatherapy\nto elevate your daily rituals.",
            'desktop_image_url' => '/banners/festive-banner.png',
            'mobile_image_url' => '/banners/festive-banner-mobile.png',
            'cta_text' => 'Explore Festive Deals',
            'cta_link' => '/collections/decor-items',
            'secondary_cta_text' => 'View All Products',
            'secondary_cta_link' => '/products',
            'text_alignment' => 'left',
            'overlay_opacity' => 0.5,
            'status' => 'active',
            'position' => 'hero',
            'order' => 2,
        ]);

        // 3. Clear old dummy collections and insert real Homzy collections
        Collection::where('store_id', $storeId)->whereIn('slug', [
            'handcrafted-ceramics',
            'ambient-lighting',
            'minimalist-furniture',
        ])->delete();

        $collectionsData = [
            [
                'slug' => 'plant-care',
                'title' => 'Plant Care & Garden',
                'description' => 'Multi-tier metal plant stands, hanging planters, and botanical accessories.',
                'image_url' => '/products/plant-stand-set-3/1.jpg',
            ],
            [
                'slug' => 'home-organizer',
                'title' => 'Home & Kitchen Organizer',
                'description' => 'Streamlined countertop organizers, tissue dispensers, acacia boards, and macrame baskets.',
                'image_url' => '/products/paper-towel-holder/1.png',
            ],
            [
                'slug' => 'decor-items',
                'title' => 'Decor Items & Planters',
                'description' => 'Artisanal terracotta and ceramic flower pots, ambient candles, and handcrafted home accents.',
                'image_url' => '/products/orange-flower-pot/1.jpg',
            ],
            [
                'slug' => 'daily-use',
                'title' => 'Daily Use & Aromatherapy',
                'description' => 'Natural incense cones, dhoop, essential oils, and calming home fragrances.',
                'image_url' => 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'slug' => 'baby-care',
                'title' => 'Baby Care & Essentials',
                'description' => 'Ultra-soft 100% organic cotton muslin baby blankets, cot sheets, and nursery gifts.',
                'image_url' => 'https://images.unsplash.com/photo-1519689680058-324335c77eba?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'slug' => 'home-fragrance',
                'title' => 'Home Fragrance & Candles',
                'description' => 'Pure soy wax candles, artisanal diffusers, and botanical room sprays.',
                'image_url' => 'https://images.unsplash.com/photo-1603006905003-be475563bc59?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'slug' => 'kitchen-dining',
                'title' => 'Kitchen & Dining Accessories',
                'description' => 'Acacia cutting boards, paper towel dispensers, and airtight glass containers.',
                'image_url' => '/products/paper-towel-holder/2.jpg',
            ],
            [
                'slug' => 'furnishings-mats',
                'title' => 'Furnishings, Mats & Rugs',
                'description' => 'Absorbent memory foam bath mats, cotton cushions, and floor runners.',
                'image_url' => 'https://images.unsplash.com/photo-1584100936595-c0654b55a2e2?auto=format&fit=crop&w=800&q=80',
            ],
        ];

        foreach ($collectionsData as $col) {
            Collection::updateOrCreate(
                ['store_id' => $storeId, 'slug' => $col['slug']],
                [
                    'title' => $col['title'],
                    'description' => $col['description'],
                    'image_url' => $col['image_url'],
                    'type' => 'manual',
                    'sort_order' => 'best-selling',
                    'is_published' => true,
                ]
            );
        }

        // 4. Clean out dummy products (Aura Brass lamp, Nordic armchair)
        Product::where('store_id', $storeId)->whereIn('slug', [
            'aura-brass-linen-pendant-light',
            'terra-stoneware-vase-trio',
            'nordic-solid-teak-armchair',
        ])->delete();

        // 5. Update Navigation Menus
        Menu::where('store_id', $storeId)->delete();

        Menu::create([
            'store_id' => $storeId,
            'title' => 'Main Menu',
            'handle' => 'main-menu',
            'is_active' => true,
            'items' => [
                ['id' => 'm1', 'label' => 'Home', 'url' => '/', 'target' => '_self'],
                ['id' => 'm2', 'label' => 'Plant Care', 'url' => '/collections/plant-care', 'target' => '_self'],
                ['id' => 'm3', 'label' => 'Home Organizers', 'url' => '/collections/home-organizer', 'target' => '_self'],
                ['id' => 'm4', 'label' => 'Decor & Planters', 'url' => '/collections/decor-items', 'target' => '_self'],
                ['id' => 'm5', 'label' => 'Daily Essentials', 'url' => '/collections/daily-use', 'target' => '_self'],
                ['id' => 'm6', 'label' => 'Baby Care', 'url' => '/collections/baby-care', 'target' => '_self'],
                ['id' => 'm7', 'label' => 'All Products', 'url' => '/products', 'target' => '_self'],
                ['id' => 'm8', 'label' => 'Track Order', 'url' => '/tracking', 'target' => '_self'],
            ],
        ]);

        Menu::create([
            'store_id' => $storeId,
            'title' => 'Footer - Explore Store',
            'handle' => 'footer-explore',
            'is_active' => true,
            'items' => [
                ['id' => 'fe1', 'label' => 'All Products', 'url' => '/products', 'target' => '_self'],
                ['id' => 'fe2', 'label' => 'Plant Care & Garden', 'url' => '/collections/plant-care', 'target' => '_self'],
                ['id' => 'fe3', 'label' => 'Home Organizer', 'url' => '/collections/home-organizer', 'target' => '_self'],
                ['id' => 'fe4', 'label' => 'Decor & Planters', 'url' => '/collections/decor-items', 'target' => '_self'],
                ['id' => 'fe5', 'label' => 'Baby Care Essentials', 'url' => '/collections/baby-care', 'target' => '_self'],
            ],
        ]);

        // 6. Save Store Footer Configuration into both settings and footer_config
        $footerConf = [
            'about_text' => 'Thoughtfully curated home essentials for a cleaner, calmer and happier home. Designed for modern Indian living spaces.',
            'copyright_text' => '© 2026 HOMZY. Home Made Easy. All rights reserved.',
            'show_newsletter' => true,
            'newsletter_title' => 'Join the HOMZY Family',
            'newsletter_subtitle' => 'Get exclusive offers, new arrivals and home inspiration straight to your inbox.',
            'social_links' => [
                ['platform' => 'Instagram', 'url' => 'https://instagram.com/homzy'],
                ['platform' => 'Facebook', 'url' => 'https://facebook.com/homzy'],
                ['platform' => 'Twitter', 'url' => 'https://twitter.com/homzy'],
                ['platform' => 'Pinterest', 'url' => 'https://pinterest.com/homzy'],
            ],
            'columns' => [
                [
                    'title' => 'Shop',
                    'links' => [
                        ['label' => 'Best Sellers', 'url' => '/products'],
                        ['label' => 'New Arrivals', 'url' => '/products?filter=new'],
                        ['label' => 'Plant Care', 'url' => '/collections/plant-care'],
                        ['label' => 'Home Organizers', 'url' => '/collections/home-organizer'],
                        ['label' => 'Track My Order', 'url' => '/tracking'],
                    ],
                ],
                [
                    'title' => 'Categories',
                    'links' => [
                        ['label' => 'Plant Care & Garden', 'url' => '/collections/plant-care'],
                        ['label' => 'Home & Kitchen Organizer', 'url' => '/collections/home-organizer'],
                        ['label' => 'Decor Items & Planters', 'url' => '/collections/decor-items'],
                        ['label' => 'Daily Use & Aromatherapy', 'url' => '/collections/daily-use'],
                        ['label' => 'Baby Care Items', 'url' => '/collections/baby-care'],
                    ],
                ],
                [
                    'title' => 'Customer Care',
                    'links' => [
                        ['label' => 'Track Order', 'url' => '/tracking'],
                        ['label' => 'Shipping Policy', 'url' => '/tracking'],
                        ['label' => 'Returns & Refunds', 'url' => '/tracking'],
                        ['label' => 'FAQs & Help', 'url' => '/tracking'],
                        ['label' => 'Merchant Admin', 'url' => '/admin'],
                    ],
                ],
                [
                    'title' => 'Company',
                    'links' => [
                        ['label' => 'About HOMZY', 'url' => '/about'],
                        ['label' => 'Our Story', 'url' => '/about'],
                        ['label' => 'Blog & Home Tips', 'url' => '/blog'],
                        ['label' => 'Careers', 'url' => '/careers'],
                        ['label' => 'Terms & Privacy', 'url' => '/terms'],
                    ],
                ],
            ],
            'guarantees' => [
                [
                    'title' => 'Free Delivery on Orders ₹499+',
                    'desc' => 'Across 28,000+ PIN codes in India with express Shiprocket tracking',
                ],
                [
                    'title' => 'Cash on Delivery Available',
                    'desc' => '100% safe & verified delivery at your doorstep with easy pay',
                ],
                [
                    'title' => 'Easy 7-Day Returns',
                    'desc' => 'Hassle-free reverse doorstep pickup & instant refund guaranteed',
                ],
            ],
        ];

        $settings = $store->settings ?? [];
        $settings['footer'] = $footerConf;
        $store->settings = $settings;
        $store->footer_config = $footerConf;

        $store->save();

        echo "Homzy real data seeded successfully!\n";
    }
}
