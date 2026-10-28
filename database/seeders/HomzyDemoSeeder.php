<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Merchant;
use App\Models\Store;
use App\Models\Theme;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Collection;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Shipment;
use App\Models\Payment;
use App\Models\Discount;
use App\Models\Integration;
use App\Models\InventoryMovement;

class HomzyDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Merchant
        $merchant = Merchant::firstOrCreate(
            ['email' => 'merchant@homzy.com'],
            [
                'name' => 'Rahul Sharma',
                'password' => Hash::make('password123'),
                'phone' => '+91 98765 43210',
                'status' => 'active',
                'subscription_plan' => 'pro',
                'email_verified_at' => now(),
                'last_login_at' => now(),
            ]
        );

        // 2. Store
        $store = Store::firstOrCreate(
            ['slug' => 'aura-living'],
            [
                'merchant_id' => (string) $merchant->_id,
                'name' => 'Aura Living',
                'slug' => 'aura-living',
                'custom_domain' => null,
                'currency' => 'INR',
                'timezone' => 'Asia/Kolkata',
                'status' => 'active',
                'branding' => [
                    'logo_url' => '',
                    'favicon_url' => '',
                    'primary_color' => '#2563eb',
                    'font_family' => 'Inter, sans-serif',
                ],
                'contact' => [
                    'email' => 'hello@auraliving.in',
                    'phone' => '+91 98765 43210',
                    'support_email' => 'support@auraliving.in',
                    'address' => '42 Heritage Lane, Kala Ghoda, Fort',
                    'city' => 'Mumbai',
                    'state' => 'Maharashtra',
                    'postal_code' => '400001',
                    'country' => 'India',
                ],
                'settings' => [
                    'tax_mode' => 'inclusive',
                    'default_gst_rate' => 18,
                    'allow_cod' => true,
                    'cod_fee' => 50,
                    'free_shipping_threshold' => 999,
                ],
                'seo' => [
                    'title_tag' => 'Aura Living | Minimalist Artisanal Home & Living',
                    'meta_description' => 'Discover artisanal ceramics, ambient lighting, and bespoke minimalist furniture handcrafted for modern homes.',
                    'social_image_url' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=1200&q=80',
                    'google_verification' => 'google-site-verification=homzy_aura_living_verified',
                    'allow_indexing' => true,
                    'canonical_url' => 'https://auraliving.in',
                ],
                'onboarding_completed' => true,
                'onboarding_step' => 4,
            ]
        );

        $storeId = (string) $store->_id;
        app()->instance('current_store', $store);

        // 3. Theme
        Theme::firstOrCreate(
            ['store_id' => $storeId, 'is_active' => true],
            [
                'name' => 'Aura Modern Luxe',
                'preset' => 'modern_minimal',
                'is_active' => true,
                'is_published' => true,
                'config' => Theme::getDefaultThemeConfig('Aura Living'),
            ]
        );

        // 3.1 Banners
        \App\Models\Banner::firstOrCreate(
            ['store_id' => $storeId, 'title' => 'Minimalist Artisanal Living'],
            [
                'title_tag' => 'NEW SEASON 2026 • HANDCRAFTED',
                'subtitle' => 'Elevate your sanctuary with handcrafted stoneware ceramics, ambient sculptural lighting, and bespoke minimalist teak furniture.',
                'desktop_image_url' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=1920&q=80',
                'mobile_image_url' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=800&q=80',
                'cta_text' => 'Shop Artisanal Collection',
                'cta_link' => '/products',
                'secondary_cta_text' => 'Explore Lookbook',
                'secondary_cta_link' => '/collections',
                'text_alignment' => 'left',
                'overlay_opacity' => 0.6,
                'status' => 'active',
                'position' => 'hero',
                'order' => 1,
            ]
        );

        \App\Models\Banner::firstOrCreate(
            ['store_id' => $storeId, 'title' => 'Festive Ceramic Studio Edition'],
            [
                'title_tag' => 'LIMITED EDITION • COMPLIMENTARY SHIPPING',
                'subtitle' => 'Tactile matte glaze pottery crafted by master potters across Rajasthan. Limited small-batch release.',
                'desktop_image_url' => 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=1920&q=80',
                'mobile_image_url' => 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=800&q=80',
                'cta_text' => 'Discover Ceramics',
                'cta_link' => '/collections',
                'text_alignment' => 'center',
                'overlay_opacity' => 0.5,
                'status' => 'active',
                'position' => 'hero',
                'order' => 2,
            ]
        );

        // 4. Collections
        $colCeramics = Collection::create([
            'store_id' => $storeId,
            'title' => 'Handcrafted Ceramics',
            'slug' => 'handcrafted-ceramics',
            'description' => 'Artisanal stoneware and tactile pottery made by master Indian craftspeople.',
            'image_url' => 'https://images.unsplash.com/photo-1612196808214-b8e1d6145a8c?auto=format&fit=crop&w=800&q=80',
            'type' => 'manual',
            'sort_order' => 'best-selling',
            'is_published' => true,
        ]);

        $colLighting = Collection::create([
            'store_id' => $storeId,
            'title' => 'Ambient Lighting',
            'slug' => 'ambient-lighting',
            'description' => 'Warm sculptural lighting fixtures, brass desk lamps, and linen pendants.',
            'image_url' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=800&q=80',
            'type' => 'manual',
            'sort_order' => 'price-ascending',
            'is_published' => true,
        ]);

        $colFurniture = Collection::create([
            'store_id' => $storeId,
            'title' => 'Minimalist Furniture',
            'slug' => 'minimalist-furniture',
            'description' => 'Solid teak, cane weaving, and clean Scandinavian silhouettes.',
            'image_url' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=800&q=80',
            'type' => 'manual',
            'sort_order' => 'best-selling',
            'is_published' => true,
        ]);

        // 5. Products
        $p1 = Product::create([
            'store_id' => $storeId,
            'title' => 'Nórdic Solid Teak Armchair',
            'slug' => 'nordic-solid-teak-armchair',
            'description' => 'A timeless accent armchair handcrafted with sustainable plantation teak wood, natural rattan webbing, and premium linen cushioning.',
            'vendor' => 'Aura Studio',
            'product_type' => 'Furniture',
            'tags' => ['seating', 'living-room', 'furniture', 'teak'],
            'status' => 'active',
            'images' => [
                'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1567538096630-e0c55bd6374c?auto=format&fit=crop&w=1000&q=80',
            ],
            'options' => [
                ['name' => 'Finish', 'values' => ['Natural Teak', 'Dark Walnut']],
            ],
            'has_variants' => true,
            'hsn_code' => '9401',
            'tax_rate' => 18.0,
            'collection_ids' => [(string) $colFurniture->_id],
        ]);

        ProductVariant::create([
            'store_id' => $storeId,
            'product_id' => (string) $p1->_id,
            'title' => 'Natural Teak',
            'sku' => 'NT-ARM-01',
            'price' => 14499.0,
            'compare_at_price' => 17999.0,
            'cost_per_item' => 8500.0,
            'inventory_quantity' => 12,
            'weight' => 8.5,
            'options_map' => ['Finish' => 'Natural Teak'],
            'image_url' => $p1->images[0],
        ]);

        ProductVariant::create([
            'store_id' => $storeId,
            'product_id' => (string) $p1->_id,
            'title' => 'Dark Walnut',
            'sku' => 'DW-ARM-02',
            'price' => 15499.0,
            'compare_at_price' => 18999.0,
            'cost_per_item' => 9000.0,
            'inventory_quantity' => 6,
            'weight' => 8.5,
            'options_map' => ['Finish' => 'Dark Walnut'],
            'image_url' => $p1->images[1],
        ]);

        $p2 = Product::create([
            'store_id' => $storeId,
            'title' => 'Terra Stoneware Vase Trio',
            'slug' => 'terra-stoneware-vase-trio',
            'description' => 'Sculptural organic vases with matte unglazed clay finish. Perfect for dry florals or minimalist tabletop displays.',
            'vendor' => 'Clay & Kiln',
            'product_type' => 'Ceramics',
            'tags' => ['decor', 'ceramics', 'vases'],
            'status' => 'active',
            'images' => [
                'https://images.unsplash.com/photo-1612196808214-b8e1d6145a8c?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=1000&q=80',
            ],
            'options' => [
                ['name' => 'Color', 'values' => ['Raw Sandstone', 'Charcoal Matte']],
            ],
            'has_variants' => true,
            'hsn_code' => '6913',
            'tax_rate' => 12.0,
            'collection_ids' => [(string) $colCeramics->_id],
        ]);

        ProductVariant::create([
            'store_id' => $storeId,
            'product_id' => (string) $p2->_id,
            'title' => 'Raw Sandstone',
            'sku' => 'VASE-SND-01',
            'price' => 2499.0,
            'compare_at_price' => 3200.0,
            'cost_per_item' => 950.0,
            'inventory_quantity' => 25,
            'weight' => 1.8,
            'options_map' => ['Color' => 'Raw Sandstone'],
            'image_url' => $p2->images[0],
        ]);

        ProductVariant::create([
            'store_id' => $storeId,
            'product_id' => (string) $p2->_id,
            'title' => 'Charcoal Matte',
            'sku' => 'VASE-CHR-02',
            'price' => 2699.0,
            'compare_at_price' => 3400.0,
            'cost_per_item' => 1050.0,
            'inventory_quantity' => 15,
            'weight' => 1.8,
            'options_map' => ['Color' => 'Charcoal Matte'],
            'image_url' => $p2->images[1],
        ]);

        $p3 = Product::create([
            'store_id' => $storeId,
            'title' => 'Aura Brass & Linen Pendant Light',
            'slug' => 'aura-brass-linen-pendant-light',
            'description' => 'Diffuse golden radiance into dining and bedroom spaces with spun solid brass accents and textured natural flax linen shade.',
            'vendor' => 'Lumen Craft',
            'product_type' => 'Lighting',
            'tags' => ['lighting', 'pendant', 'brass'],
            'status' => 'active',
            'images' => [
                'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?auto=format&fit=crop&w=1000&q=80',
            ],
            'options' => [],
            'has_variants' => false,
            'hsn_code' => '9405',
            'tax_rate' => 18.0,
            'collection_ids' => [(string) $colLighting->_id],
        ]);

        ProductVariant::create([
            'store_id' => $storeId,
            'product_id' => (string) $p3->_id,
            'title' => 'Default Title',
            'sku' => 'LGT-PEN-01',
            'price' => 4999.0,
            'compare_at_price' => 6499.0,
            'cost_per_item' => 2100.0,
            'inventory_quantity' => 8,
            'weight' => 2.2,
            'options_map' => [],
            'image_url' => $p3->images[0],
        ]);

        // 6. Customers
        $c1 = Customer::create([
            'store_id' => $storeId,
            'first_name' => 'Pooja',
            'last_name' => 'Mehta',
            'email' => 'pooja.mehta@example.com',
            'phone' => '+91 98200 12345',
            'tags' => ['VIP', 'Repeat Buyer'],
            'notes' => 'Prefers express delivery via Bluedart.',
            'accepts_marketing' => true,
            'total_orders' => 2,
            'total_spent' => 16998.0,
        ]);

        CustomerAddress::create([
            'store_id' => $storeId,
            'customer_id' => (string) $c1->_id,
            'first_name' => 'Pooja',
            'last_name' => 'Mehta',
            'phone' => '+91 98200 12345',
            'address1' => 'Flat 502, Sky High Towers, Worli',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'postal_code' => '400018',
            'country' => 'India',
            'is_default' => true,
        ]);

        // 7. Orders
        $ord1 = Order::create([
            'store_id' => $storeId,
            'order_number' => '#1001',
            'customer_id' => (string) $c1->_id,
            'customer_email' => $c1->email,
            'customer_phone' => $c1->phone,
            'customer_name' => 'Pooja Mehta',
            'items' => [
                [
                    'variant_id' => 'var_1',
                    'product_id' => (string) $p1->_id,
                    'title' => 'Nórdic Solid Teak Armchair',
                    'variant_title' => 'Natural Teak',
                    'sku' => 'NT-ARM-01',
                    'price' => 14499.0,
                    'quantity' => 1,
                    'line_total' => 14499.0,
                    'image_url' => $p1->images[0],
                ],
                [
                    'variant_id' => 'var_2',
                    'product_id' => (string) $p2->_id,
                    'title' => 'Terra Stoneware Vase Trio',
                    'variant_title' => 'Raw Sandstone',
                    'sku' => 'VASE-SND-01',
                    'price' => 2499.0,
                    'quantity' => 1,
                    'line_total' => 2499.0,
                    'image_url' => $p2->images[0],
                ]
            ],
            'pricing' => [
                'subtotal' => 16998.0,
                'discount_code' => 'WELCOME10',
                'discount_amount' => 1699.8,
                'shipping_fee' => 0.0,
                'tax_cgst' => 1166.8,
                'tax_sgst' => 1166.8,
                'tax_igst' => 0.0,
                'tax_total' => 2333.6,
                'total' => 15298.2,
            ],
            'currency' => 'INR',
            'financial_status' => 'paid',
            'fulfillment_status' => 'fulfilled',
            'order_status' => 'open',
            'payment_method' => 'razorpay',
            'shipping_address' => [
                'name' => 'Pooja Mehta',
                'phone' => '+91 98200 12345',
                'address1' => 'Flat 502, Sky High Towers, Worli',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'postal_code' => '400018',
                'country' => 'India',
            ],
            'timeline' => [
                ['action' => 'Order placed online', 'timestamp' => now()->subDays(2)->toISOString()],
                ['action' => 'Payment verified via Razorpay', 'timestamp' => now()->subDays(2)->toISOString()],
                ['action' => 'Shipment dispatched via Shiprocket (Bluedart Express)', 'timestamp' => now()->subDay()->toISOString()],
            ],
        ]);

        Payment::create([
            'store_id' => $storeId,
            'order_id' => (string) $ord1->_id,
            'provider' => 'razorpay',
            'transaction_id' => 'pay_rzp_99482741',
            'gateway_order_id' => 'order_Kj98fFh28s',
            'amount' => 15298.2,
            'currency' => 'INR',
            'status' => 'successful',
            'is_signature_verified' => true,
        ]);

        Shipment::create([
            'store_id' => $storeId,
            'order_id' => (string) $ord1->_id,
            'provider' => 'shiprocket',
            'shipment_id' => 'SR_88492019',
            'awb_code' => 'SRKT748291034',
            'courier_name' => 'Bluedart Express Surface',
            'status' => 'in_transit',
            'label_url' => 'https://sandbox.shiprocket.in/sample_shipping_label.pdf',
            'tracking_history' => [
                ['activity' => 'Package picked up from Mumbai Hub', 'location' => 'Mumbai Hub', 'date' => now()->subDay()->format('d M Y, h:i A')],
                ['activity' => 'In transit to Worli delivery station', 'location' => 'Worli Station', 'date' => now()->subHours(6)->format('d M Y, h:i A')],
            ],
        ]);

        // 8. Discounts
        Discount::create([
            'store_id' => $storeId,
            'code' => 'WELCOME10',
            'title' => 'Welcome 10% Off',
            'type' => 'percentage',
            'value' => 10.0,
            'min_cart_amount' => 1000.0,
            'max_discount_amount' => 2000.0,
            'usage_limit' => 500,
            'usage_count' => 14,
            'is_active' => true,
            'starts_at' => now()->subDays(10),
            'expires_at' => now()->addDays(60),
        ]);

        Discount::create([
            'store_id' => $storeId,
            'code' => 'FREESHIP',
            'title' => 'Complimentary Free Express Shipping',
            'type' => 'free_shipping',
            'value' => 99.0,
            'min_cart_amount' => 499.0,
            'usage_limit' => 1000,
            'usage_count' => 38,
            'is_active' => true,
            'starts_at' => now()->subDays(15),
            'expires_at' => now()->addDays(90),
        ]);

        // 9. Integrations
        $razorpay = Integration::create([
            'store_id' => $storeId,
            'provider_key' => 'razorpay',
            'name' => 'Razorpay',
            'category' => 'payments',
            'is_connected' => true,
            'is_enabled' => true,
            'health_status' => 'healthy',
            'last_tested_at' => now(),
            'settings' => ['mode' => 'sandbox'],
        ]);
        $razorpay->credentials = ['key_id' => 'rzp_test_homzy_demo', 'key_secret' => 'demo_secret_key_123'];
        $razorpay->save();

        $shiprocket = Integration::create([
            'store_id' => $storeId,
            'provider_key' => 'shiprocket',
            'name' => 'Shiprocket',
            'category' => 'shipping',
            'is_connected' => true,
            'is_enabled' => true,
            'health_status' => 'healthy',
            'last_tested_at' => now(),
            'settings' => ['default_pickup_location' => 'Primary Warehouse'],
        ]);
        $shiprocket->credentials = ['email' => 'merchant@homzy.com', 'password' => 'secret_shiprocket_api'];
        $shiprocket->save();
    }
}
