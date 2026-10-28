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
use App\Models\Discount;
use App\Models\Banner;
use App\Models\Testimonial;
use App\Models\InventoryMovement;

class KidoboxSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Merchant Account
        $merchant = Merchant::firstOrCreate(
            ['email' => 'admin@kidobox.com'],
            [
                'name' => 'KIDOBOX Admin',
                'password' => Hash::make('admin123'),
                'phone' => '+91 98765 43210',
                'status' => 'active',
                'subscription_plan' => 'enterprise',
                'email_verified_at' => now(),
                'last_login_at' => now(),
            ]
        );

        // Alias for merchant@homzy.com so legacy credentials also work
        Merchant::firstOrCreate(
            ['email' => 'merchant@homzy.com'],
            [
                'name' => 'Rahul Sharma (Admin)',
                'password' => Hash::make('password123'),
                'phone' => '+91 98765 43210',
                'status' => 'active',
                'subscription_plan' => 'pro',
                'email_verified_at' => now(),
            ]
        );

        // 2. Primary Store: KIDOBOX
        $store = Store::firstOrCreate(
            ['slug' => 'kidobox'],
            [
                'merchant_id' => (string) $merchant->_id,
                'name' => 'KIDOBOX',
                'slug' => 'kidobox',
                'custom_domain' => null,
                'currency' => 'INR',
                'timezone' => 'Asia/Kolkata',
                'status' => 'active',
                'branding' => [
                    'logo_url' => '/logo.png',
                    'favicon_url' => '/logo.png',
                    'primary_color' => '#ff268b',
                    'boy_color' => '#0284c7',
                    'font_family' => 'Fredoka, Nunito, sans-serif',
                ],
                'contact' => [
                    'email' => 'hello@kidobox.com',
                    'phone' => '+91 (022) 4890-KIDO',
                    'support_email' => 'support@kidobox.com',
                    'address' => 'KIDOBOX Baby Essentials Pvt Ltd, Bandra West, Mumbai, MH 400050',
                ],
                'settings' => [
                    'free_shipping_threshold' => 999.0,
                    'shipping_fee' => 79.0,
                    'allow_cod' => true,
                    'cod_fee' => 0.0,
                    'enable_reviews' => true,
                    'enable_gender_modes' => true,
                    'default_mode' => 'girl',
                ],
                'seo' => [
                    'title_tag' => 'Little Needs. Big Smiles. | KIDOBOX',
                    'meta_description' => 'Thoughtfully chosen baby and toddler essentials. Organic cotton clothes, food-grade silicone tableware, Montessori sensory toys, and gentle care.',
                    'social_image_url' => '/assets/hd/hero.webp',
                    'allow_indexing' => true,
                ],
            ]
        );

        $storeId = (string) $store->_id;

        // Also update any existing store if slug was 'homzy' or 'aura-living'
        Store::whereIn('slug', ['homzy', 'aura-living'])->update([
            'name' => 'KIDOBOX',
            'slug' => 'kidobox',
            'branding.primary_color' => '#ff268b',
        ]);

        // 3. Theme Configuration
        Theme::where('store_id', $storeId)->delete();
        $theme = Theme::create([
            'store_id' => $storeId,
            'name' => 'KIDOBOX Playful Pastel',
            'preset' => 'kidobox_pastel',
            'is_active' => true,
            'is_published' => true,
            'config' => Theme::getDefaultThemeConfig('KIDOBOX'),
        ]);

        // 4. Categories & Age Collections
        $collectionsData = [
            ['title' => 'Baby Clothing', 'slug' => 'clothing', 'image' => '/assets/hd/clothing.webp', 'desc' => 'Soft organic cotton outfits, rompers, and sleepsuits.'],
            ['title' => 'Diapering', 'slug' => 'diapering', 'image' => '/assets/hd/wipes.webp', 'desc' => 'Gentle wipes and diaper care for delicate baby skin.'],
            ['title' => 'Feeding', 'slug' => 'feeding', 'image' => '/assets/hd/feeding.webp', 'desc' => 'Food-grade silicone tableware, suction bowls, and spoons.'],
            ['title' => 'Bath & Care', 'slug' => 'bath-care', 'image' => '/assets/hd/bath.webp', 'desc' => 'Hypoallergenic soothing shampoos, washes, and bamboo towels.'],
            ['title' => 'Toys & Play', 'slug' => 'toys', 'image' => '/assets/hd/toys.webp', 'desc' => 'Montessori wooden toys and ultra-soft plush friends.'],
            ['title' => 'Nursery', 'slug' => 'nursery', 'image' => '/assets/hd/nursery.webp', 'desc' => 'Cozy organic bedding, swaddles, and nursery decor.'],
            ['title' => 'Mom & Baby', 'slug' => 'mom-baby', 'image' => '/assets/hd/mom.webp', 'desc' => 'Thoughtful comfort essentials for nursing and postpartum care.'],
            ['title' => 'Health & Safety', 'slug' => 'health-safety', 'image' => '/assets/hd/health.webp', 'desc' => 'Pediatrician-recommended thermometers and safety kits.'],
            ['title' => 'Newborn (0-6M)', 'slug' => 'age-newborn', 'image' => '/assets/hd/hero.webp', 'desc' => 'First cuddles and essential newborn wardrobe.'],
            ['title' => 'Baby (6-12M)', 'slug' => 'age-baby', 'image' => '/assets/hd/feeding.webp', 'desc' => 'Exploring flavors, sitting up, and baby-led weaning.'],
            ['title' => 'Toddler (1-2Y)', 'slug' => 'age-toddler', 'image' => '/assets/hd/toys.webp', 'desc' => 'First steps, active playtime, and motor exploration.'],
            ['title' => 'Kids (2-4Y)', 'slug' => 'age-kids', 'image' => '/assets/hd/clothing.webp', 'desc' => 'Sturdy, playful, vibrant outfits for curious minds.'],
        ];

        $collectionIdMap = [];
        foreach ($collectionsData as $col) {
            $c = Collection::updateOrCreate(
                ['store_id' => $storeId, 'slug' => $col['slug']],
                [
                    'title' => $col['title'],
                    'description' => $col['desc'],
                    'image_url' => $col['image'],
                    'type' => 'manual',
                    'sort_order' => 'best-selling',
                    'is_published' => true,
                ]
            );
            $collectionIdMap[$col['slug']] = (string) $c->_id;
        }

        // 5. Products & Variants (Matching frontend kidobox-data.ts)
        $productsData = [
            [
                'title' => 'Organic Cotton Kimono Romper',
                'slug' => 'organic-cotton-romper',
                'description' => 'Everyday cloud softness crafted from 100% GOTS organic combed cotton. Features nickel-free snap buttons for breezy, tear-free diaper changes.',
                'vendor' => 'KIDOBOX',
                'product_type' => 'Clothing',
                'tags' => ['Bestseller', 'Organic', 'Skin-Safe', 'Newborn'],
                'images' => ['/assets/hd/clothing.webp', '/assets/hd/hero.webp'],
                'price' => 599.0,
                'compare_at' => 799.0,
                'category_slug' => 'clothing',
                'age_slug' => 'age-newborn',
                'options' => [
                    ['name' => 'Size', 'values' => ['0–3 months', '3–6 months', '6–12 months']],
                    ['name' => 'Color', 'values' => ['Blush Pink', 'Sky Blue', 'Cloud Cream']],
                ],
            ],
            [
                'title' => 'Soft Touch Biodegradable Baby Wipes',
                'slug' => 'soft-touch-baby-wipes',
                'description' => 'Gentle 99% pure water wipes infused with organic chamomile and aloe vera extract. 100% plant-derived fibers that are kind to baby skin and our planet.',
                'vendor' => 'KIDOBOX',
                'product_type' => 'Diapering',
                'tags' => ['Parent Favorite', 'Eco-Friendly', 'Hypoallergenic'],
                'images' => ['/assets/hd/wipes.webp'],
                'price' => 249.0,
                'compare_at' => 349.0,
                'category_slug' => 'diapering',
                'age_slug' => 'age-newborn',
                'options' => [
                    ['name' => 'Pack', 'values' => ['Single Pack (72 wipes)', 'Pack of 3 (216 wipes)']],
                ],
            ],
            [
                'title' => 'Silicone Suction Bowl & Feeding Spoon Set',
                'slug' => 'silicone-feeding-set',
                'description' => 'Food-grade silicone tableware with an ultra-strong suction lock base to prevent mealtime flips. Microwave, freezer, and dishwasher safe up to 220°C.',
                'vendor' => 'KIDOBOX',
                'product_type' => 'Feeding',
                'tags' => ['Bestseller', 'BPA-Free', 'Baby-Led Weaning'],
                'images' => ['/assets/hd/feeding.webp'],
                'price' => 799.0,
                'compare_at' => 999.0,
                'category_slug' => 'feeding',
                'age_slug' => 'age-baby',
                'options' => [
                    ['name' => 'Color', 'values' => ['Sage Green', 'Blush Pink', 'Ocean Blue', 'Warm Sand']],
                ],
            ],
            [
                'title' => 'Cuddle Bunny Sensory Plush Toy',
                'slug' => 'cuddle-bunny-plush',
                'description' => 'A floppy-eared cuddle friend crafted from tactile organic plush with embroidered child-safe facial details. Machine washable with zero loose buttons.',
                'vendor' => 'KIDOBOX',
                'product_type' => 'Toys & Play',
                'tags' => ['Bestseller', 'Sensory', 'Gift Idea'],
                'images' => ['/assets/hd/toys.webp'],
                'price' => 499.0,
                'compare_at' => 699.0,
                'category_slug' => 'toys',
                'age_slug' => 'age-toddler',
                'options' => [
                    ['name' => 'Size', 'values' => ['Small (25cm)', 'Medium (35cm)']],
                    ['name' => 'Color', 'values' => ['Blush Pink', 'Oatmeal', 'Sky Blue']],
                ],
            ],
            [
                'title' => 'Calming Organic Baby Lotion & Massage Oil',
                'slug' => 'gentle-baby-lotion',
                'description' => 'Nourishing cold-pressed sweet almond and shea butter formula that seals in moisture for 24 hours. Zero artificial fragrance, parabens, or mineral oils.',
                'vendor' => 'Chicco',
                'product_type' => 'Bath & Care',
                'tags' => ['Dermatologist-Approved', 'Organic', 'Soothing'],
                'images' => ['/assets/hd/bath.webp'],
                'price' => 349.0,
                'compare_at' => 499.0,
                'category_slug' => 'bath-care',
                'age_slug' => 'age-newborn',
                'options' => [
                    ['name' => 'Volume', 'values' => ['200ml Bottle', '400ml Pump Pack']],
                ],
            ],
            [
                'title' => 'Bamboo Muslin Swaddle Blankets (Set of 2)',
                'slug' => 'bamboo-swaddle-blanket',
                'description' => 'Silky-soft 70% bamboo and 30% organic cotton muslin swaddles. Highly breathable open weave regulates body temperature during peaceful nap times.',
                'vendor' => 'KIDOBOX',
                'product_type' => 'Nursery',
                'tags' => ['Bestseller', 'Newborn Essential', 'Breathable'],
                'images' => ['/assets/hd/newborn.webp'],
                'price' => 849.0,
                'compare_at' => 1199.0,
                'category_slug' => 'nursery',
                'age_slug' => 'age-newborn',
                'options' => [
                    ['name' => 'Palette', 'values' => ['Dreamy Stars & Moon', 'Soft Botanical Florals', 'Ocean Waves']],
                ],
            ],
            [
                'title' => 'Natural Wooden Sensory Stacking Rings',
                'slug' => 'wooden-stacking-rings',
                'description' => 'Smooth beechwood rings coated in non-toxic water-based food dyes. Boosts hand-eye coordination, spatial problem-solving, and sensory curiosity.',
                'vendor' => 'Fisher-Price',
                'product_type' => 'Toys & Play',
                'tags' => ['Montessori', 'Non-Toxic', 'Motor Skills'],
                'images' => ['/assets/hd/toys.webp'],
                'price' => 799.0,
                'compare_at' => 1099.0,
                'category_slug' => 'toys',
                'age_slug' => 'age-toddler',
                'options' => [
                    ['name' => 'Palette', 'values' => ['Pastel Rainbow', 'Natural Earthy Hues']],
                ],
            ],
            [
                'title' => 'Infrared No-Touch Baby Forehead Thermometer',
                'slug' => 'digital-baby-thermometer',
                'description' => 'Medical-grade infrared sensor that provides accurate 1-second body temperature readings without disturbing a sleeping child. Features silent night mode.',
                'vendor' => 'Philips Avent',
                'product_type' => 'Health & Safety',
                'tags' => ['Pediatrician-Approved', 'Instant Read', 'Fever Alert'],
                'images' => ['/assets/hd/health.webp'],
                'price' => 699.0,
                'compare_at' => 899.0,
                'category_slug' => 'health-safety',
                'age_slug' => 'age-baby',
                'options' => [
                    ['name' => 'Color', 'values' => ['Sky Blue', 'Cloud White']],
                ],
            ],
        ];

        foreach ($productsData as $pData) {
            $catColId = $collectionIdMap[$pData['category_slug']] ?? null;
            $ageColId = $collectionIdMap[$pData['age_slug']] ?? null;
            $colIds = array_values(array_filter([$catColId, $ageColId]));

            $product = Product::updateOrCreate(
                ['store_id' => $storeId, 'slug' => $pData['slug']],
                [
                    'title' => $pData['title'],
                    'description' => $pData['description'],
                    'vendor' => $pData['vendor'],
                    'product_type' => $pData['product_type'],
                    'tags' => $pData['tags'],
                    'status' => 'active',
                    'images' => $pData['images'],
                    'options' => $pData['options'],
                    'has_variants' => true,
                    'hsn_code' => '6111',
                    'tax_rate' => 12.0,
                    'seo' => [
                        'title' => "{$pData['title']} | KIDOBOX",
                        'description' => $pData['description'],
                    ],
                    'collection_ids' => $colIds,
                ]
            );

            // Create Variants
            $firstOption = $pData['options'][0] ?? null;
            $secondOption = $pData['options'][1] ?? null;

            if ($firstOption && $secondOption) {
                foreach ($firstOption['values'] as $v1) {
                    foreach ($secondOption['values'] as $v2) {
                        $sku = strtoupper(substr($pData['slug'], 0, 3)) . '-' . substr(md5($v1 . $v2), 0, 5);
                        ProductVariant::updateOrCreate(
                            [
                                'store_id' => $storeId,
                                'product_id' => (string) $product->_id,
                                'title' => "{$v1} / {$v2}",
                            ],
                            [
                                'sku' => $sku,
                                'price' => $pData['price'],
                                'compare_at_price' => $pData['compare_at'],
                                'cost_per_item' => round($pData['price'] * 0.45, 2),
                                'inventory_quantity' => 45,
                                'weight' => 0.25,
                                'weight_unit' => 'kg',
                                'options_map' => [
                                    $firstOption['name'] => $v1,
                                    $secondOption['name'] => $v2,
                                ],
                                'image_url' => $pData['images'][0] ?? null,
                            ]
                        );
                    }
                }
            } elseif ($firstOption) {
                foreach ($firstOption['values'] as $v1) {
                    $sku = strtoupper(substr($pData['slug'], 0, 3)) . '-' . substr(md5($v1), 0, 5);
                    ProductVariant::updateOrCreate(
                        [
                            'store_id' => $storeId,
                            'product_id' => (string) $product->_id,
                            'title' => $v1,
                        ],
                        [
                            'sku' => $sku,
                            'price' => $pData['price'],
                            'compare_at_price' => $pData['compare_at'],
                            'cost_per_item' => round($pData['price'] * 0.45, 2),
                            'inventory_quantity' => 60,
                            'weight' => 0.2,
                            'weight_unit' => 'kg',
                            'options_map' => [
                                $firstOption['name'] => $v1,
                            ],
                            'image_url' => $pData['images'][0] ?? null,
                        ]
                    );
                }
            }
        }

        // 6. Hero Carousel Banners (Matching live hero slider)
        Banner::where('store_id', $storeId)->delete();
        Banner::create([
            'store_id' => $storeId,
            'title' => 'Little Needs. Big Smiles.',
            'subtitle' => 'Everything your little one needs to grow, play, and shine with safe, gentle care.',
            'eyebrow' => 'A LITTLE LOVE, EVERY DAY',
            'cta_text' => 'Shop Essentials',
            'cta_link' => '/shop',
            'image_url' => '/assets/hd/hero.webp',
            'bg_gradient' => 'linear-gradient(135deg, #fff2f6 0%, #ffe9f2 50%, #fff7fa 100%)',
            'theme_color' => '#ff268b',
            'status' => 'active',
            'order' => 1,
            'badges' => [
                ['icon' => 'Leaf', 'title' => 'Safe & Gentle', 'subtitle' => 'Tested for delicate skin'],
                ['icon' => 'Heart', 'title' => 'Parent-Approved', 'subtitle' => 'Loved by 20,000+ parents'],
                ['icon' => 'ShieldCheck', 'title' => 'Certified Safe', 'subtitle' => 'Zero toxins & BPA free'],
            ],
        ]);

        Banner::create([
            'store_id' => $storeId,
            'title' => 'Pure Cloud Softness, Happy Baby.',
            'subtitle' => 'Ultra-breathable onesies, rompers, and sleepsuits crafted for dreamy comfort and easy diaper changes.',
            'eyebrow' => '100% GOTS ORGANIC COTTON',
            'cta_text' => 'Shop Clothing',
            'cta_link' => '/clothing',
            'image_url' => '/assets/hd/clothing.webp',
            'bg_gradient' => 'linear-gradient(135deg, #eef9f6 0%, #e1f5ef 50%, #f4fbf9 100%)',
            'theme_color' => '#20a886',
            'status' => 'active',
            'order' => 2,
            'badges' => [
                ['icon' => 'Feather', 'title' => 'Feather-Soft', 'subtitle' => '100% combed cotton'],
                ['icon' => 'CheckCircle2', 'title' => 'Nickel-Free Snaps', 'subtitle' => 'Quick 2-way zippers'],
                ['icon' => 'Tag', 'title' => 'Tagless Design', 'subtitle' => 'Zero scratchiness'],
            ],
        ]);

        Banner::create([
            'store_id' => $storeId,
            'title' => 'Nourish & Play, Every Single Day.',
            'subtitle' => 'Food-grade silicone tableware and Montessori toys engineered to stimulate learning and motor milestones.',
            'eyebrow' => 'DEVELOPMENTAL & BPA-FREE',
            'cta_text' => 'Shop Feeding & Toys',
            'cta_link' => '/feeding',
            'image_url' => '/assets/hd/feeding.webp',
            'bg_gradient' => 'linear-gradient(135deg, #eff9ff 0%, #e2f4ff 50%, #f7fcff 100%)',
            'theme_color' => '#0284c7',
            'status' => 'active',
            'order' => 3,
            'badges' => [
                ['icon' => 'Utensils', 'title' => 'Suction Base Tech', 'subtitle' => 'Zero mealtime spills'],
                ['icon' => 'Sparkles', 'title' => 'Dishwasher Safe', 'subtitle' => 'Withstands -40°C to 220°C'],
                ['icon' => 'Award', 'title' => 'Baby-Led Weaning', 'subtitle' => 'Pediatrician recommended'],
            ],
        ]);

        // 7. Verified Parent Testimonials
        Testimonial::where('store_id', $storeId)->delete();
        Testimonial::create([
            'store_id' => $storeId,
            'name' => 'Priya Sharma',
            'role' => 'Mom of 4-month-old Aarav • Mumbai',
            'avatar_url' => '/assets/hd/bath.webp',
            'rating' => 5,
            'content' => 'The organic cotton onesies are unbelievably soft! After dozens of machine washes, they retain their shape, softness, and vibrant pastel colors. Zero rashes, happy baby!',
            'verified' => true,
            'status' => 'active',
            'order' => 1,
        ]);

        Testimonial::create([
            'store_id' => $storeId,
            'name' => 'Ananya Deshmukh',
            'role' => 'Mom of 14-month-old Myra • Bengaluru',
            'avatar_url' => '/assets/hd/feeding.webp',
            'rating' => 5,
            'content' => 'The silicone suction bowl actually stays stuck to her high chair tray! Cleanups take 10 seconds and the soft silicone spoon is perfect for self-feeding practice.',
            'verified' => true,
            'status' => 'active',
            'order' => 2,
        ]);

        Testimonial::create([
            'store_id' => $storeId,
            'name' => 'Rohit Khanna',
            'role' => 'Dad of 8-month-old Vihaan • Delhi NCR',
            'avatar_url' => '/assets/hd/toys.webp',
            'rating' => 5,
            'content' => 'The wooden stacking rings and sensory teething toys are top notch. No chemical smell, rounded edges everywhere, and lightning fast 2-day delivery!',
            'verified' => true,
            'status' => 'active',
            'order' => 3,
        ]);

        Testimonial::create([
            'store_id' => $storeId,
            'name' => 'Sneha Patel',
            'role' => 'Mom of 2-month-old twins • Ahmedabad',
            'avatar_url' => '/assets/hd/hero.webp',
            'rating' => 5,
            'content' => 'KIDOBOX has been our lifesaver for newborn essentials. The bamboo swaddles and gentle bath wash keep both babies calm and smiling all day long.',
            'verified' => true,
            'status' => 'active',
            'order' => 4,
        ]);

        // 8. Discounts & Coupons
        Discount::where('store_id', $storeId)->delete();
        Discount::create([
            'store_id' => $storeId,
            'code' => 'LITTLE10',
            'type' => 'percentage',
            'value' => 10.0,
            'minimum_cart_amount' => 499.0,
            'is_active' => true,
            'usage_limit' => 1000,
            'times_used' => 184,
            'starts_at' => now()->subDays(30),
            'expires_at' => now()->addYear(),
        ]);

        Discount::create([
            'store_id' => $storeId,
            'code' => 'FREESHIP',
            'type' => 'free_shipping',
            'value' => 79.0,
            'minimum_cart_amount' => 499.0,
            'is_active' => true,
            'usage_limit' => 500,
            'times_used' => 92,
            'starts_at' => now()->subDays(30),
            'expires_at' => now()->addYear(),
        ]);

        // 9. Customers & Sample Orders
        $customer1 = Customer::firstOrCreate(
            ['store_id' => $storeId, 'email' => 'priya.sharma@example.com'],
            [
                'first_name' => 'Priya',
                'last_name' => 'Sharma',
                'phone' => '+91 98711 22334',
                'total_orders' => 3,
                'total_spent' => 3890.0,
                'accepts_marketing' => true,
            ]
        );

        $customer2 = Customer::firstOrCreate(
            ['store_id' => $storeId, 'email' => 'ananya.deshmukh@example.com'],
            [
                'first_name' => 'Ananya',
                'last_name' => 'Deshmukh',
                'phone' => '+91 98200 44556',
                'total_orders' => 2,
                'total_spent' => 2148.0,
                'accepts_marketing' => true,
            ]
        );

        // Sample Orders
        Order::firstOrCreate(
            ['store_id' => $storeId, 'order_number' => 'KDB-100293'],
            [
                'customer_id' => (string) $customer1->_id,
                'customer_email' => 'priya.sharma@example.com',
                'financial_status' => 'paid',
                'fulfillment_status' => 'delivered',
                'currency' => 'INR',
                'subtotal' => 1398.0,
                'discount_amount' => 139.8,
                'shipping_fee' => 0.0,
                'tax_amount' => 150.98,
                'total_amount' => 1258.2,
                'payment_gateway' => 'razorpay',
                'shipping_address' => [
                    'first_name' => 'Priya',
                    'last_name' => 'Sharma',
                    'address1' => 'Flat 402, Sunshine Heights, Powai',
                    'city' => 'Mumbai',
                    'state' => 'Maharashtra',
                    'postal_code' => '400076',
                    'phone' => '+91 98711 22334',
                ],
                'items' => [
                    [
                        'title' => 'Organic Cotton Kimono Romper',
                        'variant_title' => '3–6 months / Blush Pink',
                        'sku' => 'ROM-BLUSH-036',
                        'price' => 599.0,
                        'quantity' => 1,
                        'line_total' => 599.0,
                        'image_url' => '/assets/hd/clothing.webp',
                    ],
                    [
                        'title' => 'Silicone Suction Bowl & Feeding Spoon Set',
                        'variant_title' => 'Blush Pink',
                        'sku' => 'SET-BLUSH-01',
                        'price' => 799.0,
                        'quantity' => 1,
                        'line_total' => 799.0,
                        'image_url' => '/assets/hd/feeding.webp',
                    ],
                ],
            ]
        );

        Order::firstOrCreate(
            ['store_id' => $storeId, 'order_number' => 'KDB-100345'],
            [
                'customer_id' => (string) $customer2->_id,
                'customer_email' => 'ananya.deshmukh@example.com',
                'financial_status' => 'paid',
                'fulfillment_status' => 'processing',
                'currency' => 'INR',
                'subtotal' => 849.0,
                'discount_amount' => 0.0,
                'shipping_fee' => 79.0,
                'tax_amount' => 101.88,
                'total_amount' => 928.0,
                'payment_gateway' => 'cod',
                'shipping_address' => [
                    'first_name' => 'Ananya',
                    'last_name' => 'Deshmukh',
                    'address1' => '12B, Palm Meadows, Indiranagar',
                    'city' => 'Bengaluru',
                    'state' => 'Karnataka',
                    'postal_code' => '560038',
                    'phone' => '+91 98200 44556',
                ],
                'items' => [
                    [
                        'title' => 'Bamboo Muslin Swaddle Blankets (Set of 2)',
                        'variant_title' => 'Dreamy Stars & Moon',
                        'sku' => 'SWA-STARS-02',
                        'price' => 849.0,
                        'quantity' => 1,
                        'line_total' => 849.0,
                        'image_url' => '/assets/hd/newborn.webp',
                    ],
                ],
            ]
        );

        $this->command->info('KIDOBOX store, theme, products, categories, banners, testimonials, and orders seeded successfully!');
    }
}
