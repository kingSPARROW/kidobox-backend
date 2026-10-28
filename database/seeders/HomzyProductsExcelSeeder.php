<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Store;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Collection;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\InventoryMovement;

class HomzyProductsExcelSeeder extends Seeder
{
    public function run(): void
    {
        $store = Store::first() ?? Store::where('status', 'active')->first();
        if (!$store) {
            $this->command->error('No store found.');
            return;
        }

        $storeId = (string) $store->_id;

        // 1. Ensure Categories / Collections match Homzy Excel taxonomy
        $colPlantCare = Collection::firstOrCreate(
            ['store_id' => $storeId, 'slug' => 'plant-care'],
            [
                'title' => 'Plant Care & Garden',
                'description' => 'Multi-tier metal plant stands, hanging planters, and botanical accessories.',
                'image_url' => '/products/plant-stand-set-3/1.jpg',
                'type' => 'manual',
                'sort_order' => 'best-selling',
                'is_published' => true,
            ]
        );

        $colHomeOrganizer = Collection::firstOrCreate(
            ['store_id' => $storeId, 'slug' => 'home-organizer'],
            [
                'title' => 'Home & Kitchen Organizer',
                'description' => 'Streamlined countertop organizers, tissue dispensers, acacia boards, and macrame baskets.',
                'image_url' => '/products/paper-towel-holder/1.png',
                'type' => 'manual',
                'sort_order' => 'best-selling',
                'is_published' => true,
            ]
        );

        $colDecorItems = Collection::firstOrCreate(
            ['store_id' => $storeId, 'slug' => 'decor-items'],
            [
                'title' => 'Decor Items & Planters',
                'description' => 'Artisanal terracotta and ceramic flower pots, ambient candles, and handcrafted home accents.',
                'image_url' => '/products/orange-flower-pot/1.jpg',
                'type' => 'manual',
                'sort_order' => 'best-selling',
                'is_published' => true,
            ]
        );

        $colDailyUse = Collection::firstOrCreate(
            ['store_id' => $storeId, 'slug' => 'daily-use'],
            [
                'title' => 'Daily Use & Aromatherapy',
                'description' => 'Natural incense cones, dhoop, essential oils, and calming home fragrances.',
                'image_url' => 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=800&q=80',
                'type' => 'manual',
                'sort_order' => 'best-selling',
                'is_published' => true,
            ]
        );

        $colBabyCare = Collection::firstOrCreate(
            ['store_id' => $storeId, 'slug' => 'baby-care'],
            [
                'title' => 'Baby Care & Essentials',
                'description' => 'Ultra-soft 100% organic cotton muslin baby blankets, cot sheets, and nursery gifts.',
                'image_url' => 'https://images.unsplash.com/photo-1519689680058-324335c77eba?auto=format&fit=crop&w=800&q=80',
                'type' => 'manual',
                'sort_order' => 'best-selling',
                'is_published' => true,
            ]
        );

        // 2. Clear out older sample products to replace cleanly with Homzy Excel inventory
        // (preserve Nordic armchair if desired)
        $productsToSeed = [
            // PRODUCT 1: Plant Stand - Set 2 (from ##STOCK row 4)
            [
                'title' => 'Plant Stand - Set of 2',
                'slug' => 'plant-stand-set-2',
                'description' => "Elevate your indoor botanicals with the HomZy Modern Metal Plant Stand (Set of 2). Crafted with rust-resistant powder-coated wrought iron, these sturdy dual-height stands offer exceptional balance for ceramic, terracotta, or metal pots.\n\nKey Highlights:\n- Heavy-duty solid metal frame with anti-rust powder coat\n- Dual heights for stylish visual layering in living rooms, balconies, and bedrooms\n- Protective non-slip floor glides to prevent scratches on tiles and hardwood\n- Pre-assembled and ready to display",
                'vendor' => 'HomZy Botanicals',
                'product_type' => 'Plant Care',
                'tags' => ['plant-care', 'plant-stand', 'balcony-decor', 'metal-stand', 'indoor-garden'],
                'status' => 'active',
                'images' => [
                    '/products/plant-stand-set-2/1.jpg',
                    '/products/plant-stand-set-2/2.jpg',
                    '/products/plant-stand-set-2/3.jpg',
                    '/products/plant-stand-set-2/5.jpg',
                    '/products/plant-stand-set-2/6.jpg',
                    '/products/plant-stand-set-2/7.jpg',
                    '/products/plant-stand-set-2/8.jpg',
                ],
                'options' => [
                    ['name' => 'Set', 'values' => ['Set of 2']],
                ],
                'has_variants' => false,
                'hsn_code' => '9403',
                'tax_rate' => 18.0,
                'collections' => [$colPlantCare],
                'variants' => [
                    [
                        'title' => 'Set of 2',
                        'sku' => 'HZ-PS-SET2',
                        'price' => 999.0,
                        'compare_at_price' => 1299.0,
                        'cost_per_item' => 520.0,
                        'inventory_quantity' => 149,
                        'weight' => 1.8,
                        'options_map' => ['Set' => 'Set of 2'],
                        'image_url' => '/products/plant-stand-set-2/1.jpg',
                    ]
                ],
            ],

            // PRODUCT 2: Plant Stand - Set 3 (from ##STOCK row 5)
            [
                'title' => 'Plant Stand - Set of 3',
                'slug' => 'plant-stand-set-3',
                'description' => "Create an eye-catching botanical display with the HomZy Tiered Metal Plant Stand (Set of 3). Engineered with heavy-gauge wrought iron in three progressive heights (Small, Medium, Large) to create a cascading green oasis in your home.\n\nKey Highlights:\n- 3-Piece tiered height arrangement for maximum foliage presentation\n- Weatherproof electro-coating suitable for indoor verandas and semi-outdoor balconies\n- High weight capacity supporting pots up to 25kg each\n- Minimalist geometric silhouette suited for modern Indian homes",
                'vendor' => 'HomZy Botanicals',
                'product_type' => 'Plant Care',
                'tags' => ['plant-care', 'plant-stand', 'tiered-stand', 'indoor-plants', 'balcony'],
                'status' => 'active',
                'images' => [
                    '/products/plant-stand-set-3/1.jpg',
                    '/products/plant-stand-set-3/2.jpg',
                    '/products/plant-stand-set-3/3.jpg',
                    '/products/plant-stand-set-3/4.jpg',
                    '/products/plant-stand-set-3/5.jpg',
                    '/products/plant-stand-set-3/6.jpg',
                    '/products/plant-stand-set-3/7.jpg',
                ],
                'options' => [
                    ['name' => 'Set', 'values' => ['Set of 3']],
                ],
                'has_variants' => false,
                'hsn_code' => '9403',
                'tax_rate' => 18.0,
                'collections' => [$colPlantCare],
                'variants' => [
                    [
                        'title' => 'Set of 3',
                        'sku' => 'HZ-PS-SET3',
                        'price' => 1299.0,
                        'compare_at_price' => 1598.0,
                        'cost_per_item' => 690.0,
                        'inventory_quantity' => 149,
                        'weight' => 2.6,
                        'options_map' => ['Set' => 'Set of 3'],
                        'image_url' => '/products/plant-stand-set-3/1.jpg',
                    ]
                ],
            ],

            // PRODUCT 3: Paper Towel Holder (from ##STOCK row 1)
            [
                'title' => 'Modern Countertop Paper Towel Holder',
                'slug' => 'paper-towel-holder',
                'description' => "Upgrade your kitchen countertop and dining table with the HomZy Sleek Paper Towel Holder. Designed with a weighted circular base for effortless one-handed tearing, smooth spin mechanism, and anti-rust matte coating.\n\nKey Highlights:\n- Weighted anti-topple base keeps the dispenser grounded during quick tears\n- Accommodates all standard, jumbo, and commercial tissue rolls\n- Clean minimalist design coordinates seamlessly with stainless steel and marble counters\n- Non-marking silicone base pads protect polished marble and granite surfaces",
                'vendor' => 'HomZy Organizers',
                'product_type' => 'Home Organizer',
                'tags' => ['home-organizer', 'kitchen', 'towel-holder', 'tabletop', 'dining'],
                'status' => 'active',
                'images' => [
                    '/products/paper-towel-holder/1.png',
                    '/products/paper-towel-holder/2.jpg',
                    '/products/paper-towel-holder/3.jpg',
                    '/products/paper-towel-holder/4.jpg',
                    '/products/paper-towel-holder/5.png',
                    '/products/paper-towel-holder/6.jpg',
                ],
                'options' => [
                    ['name' => 'Color', 'values' => ['Matte Black']],
                ],
                'has_variants' => false,
                'hsn_code' => '7323',
                'tax_rate' => 18.0,
                'collections' => [$colHomeOrganizer],
                'variants' => [
                    [
                        'title' => 'Matte Black',
                        'sku' => 'HZ-PTH-01',
                        'price' => 199.0,
                        'compare_at_price' => 299.0,
                        'cost_per_item' => 95.0,
                        'inventory_quantity' => 88,
                        'weight' => 0.45,
                        'options_map' => ['Color' => 'Matte Black'],
                        'image_url' => '/products/paper-towel-holder/1.png',
                    ]
                ],
            ],

            // PRODUCT 4: Orange Flower Pot (from ##STOCK row 3)
            [
                'title' => 'Artisanal Terracotta Orange Flower Pot',
                'slug' => 'orange-flower-pot',
                'description' => "Imbue earthy warmth into your interiors with the HomZy Handcrafted Terracotta Orange Flower Pot. Formed with natural terracotta clay that promotes natural root aeration and regulates soil moisture balance.\n\nKey Highlights:\n- Breathable porous clay construction prevents over-watering and root rot\n- Vibrant terracotta orange glaze with artisanal hand-finished rim\n- Ideal for succulents, dwarf jade, snake plants, and indoor herbs\n- Integrated bottom drainage hole for healthy root cultivation",
                'vendor' => 'HomZy Pottery',
                'product_type' => 'Decor Items',
                'tags' => ['decor-items', 'flower-pot', 'orange-pot', 'planter', 'terracotta'],
                'status' => 'active',
                'images' => [
                    '/products/orange-flower-pot/1.jpg',
                    '/products/orange-flower-pot/2.jpg',
                    '/products/orange-flower-pot/3.jpg',
                    '/products/orange-flower-pot/4.jpg',
                    '/products/orange-flower-pot/5.jpg',
                    '/products/orange-flower-pot/6.jpg',
                    '/products/orange-flower-pot/7.jpg',
                ],
                'options' => [
                    ['name' => 'Color', 'values' => ['Terracotta Orange']],
                ],
                'has_variants' => false,
                'hsn_code' => '6913',
                'tax_rate' => 12.0,
                'collections' => [$colDecorItems],
                'variants' => [
                    [
                        'title' => 'Terracotta Orange',
                        'sku' => 'HZ-POT-ORG',
                        'price' => 199.0,
                        'compare_at_price' => 293.0,
                        'cost_per_item' => 90.0,
                        'inventory_quantity' => 36,
                        'weight' => 0.65,
                        'options_map' => ['Color' => 'Terracotta Orange'],
                        'image_url' => '/products/orange-flower-pot/1.jpg',
                    ]
                ],
            ],

            // PRODUCT 5: Yellow Flower Pot (from ##STOCK row 2)
            [
                'title' => 'Artisanal Sunshine Yellow Flower Pot',
                'slug' => 'yellow-flower-pot',
                'description' => "Brighten up your study desk, console, or windowsill with the HomZy Sunshine Yellow Ceramic Planter Pot. Made with high-fired ceramic stoneware and finished in a mood-lifting sunny pastel hue.\n\nKey Highlights:\n- Glossy easy-wipe finish that resists sun fading and mineral stains\n- Ergonomic cylindrical shape optimized for compact tabletops and shelves\n- Encourages healthy indoor greens and adds cheerful focal points\n- Sturdy base with drainage protection",
                'vendor' => 'HomZy Pottery',
                'product_type' => 'Decor Items',
                'tags' => ['decor-items', 'flower-pot', 'yellow-pot', 'ceramic-pot', 'table-decor'],
                'status' => 'active',
                'images' => [
                    '/products/yellow-flower-pot/1.jpg',
                    '/products/yellow-flower-pot/2.jpg',
                    '/products/yellow-flower-pot/3.jpg',
                    '/products/yellow-flower-pot/4.jpg',
                    '/products/yellow-flower-pot/5.jpg',
                    '/products/yellow-flower-pot/6.jpg',
                    '/products/yellow-flower-pot/7.jpg',
                ],
                'options' => [
                    ['name' => 'Color', 'values' => ['Sunshine Yellow']],
                ],
                'has_variants' => false,
                'hsn_code' => '6913',
                'tax_rate' => 12.0,
                'collections' => [$colDecorItems],
                'variants' => [
                    [
                        'title' => 'Sunshine Yellow',
                        'sku' => 'HZ-POT-YLW',
                        'price' => 199.0,
                        'compare_at_price' => 299.0,
                        'cost_per_item' => 90.0,
                        'inventory_quantity' => 27,
                        'weight' => 0.65,
                        'options_map' => ['Color' => 'Sunshine Yellow'],
                        'image_url' => '/products/yellow-flower-pot/1.jpg',
                    ]
                ],
            ],

            // PRODUCT 6: Organic Acacia Wood Chopping Board (from ##STOCK rows 6-9)
            [
                'title' => 'Handcrafted Acacia Hardwood Chopping Board',
                'slug' => 'acacia-wood-chopping-board',
                'description' => "Crafted from 100% sustainable Indian Acacia hardwood, the HomZy Organic Cutting & Serving Board combines culinary resilience with rustic kitchen beauty. Knife-friendly grain prevents dulling while natural antibacterial wood density resists moisture absorption.\n\nKey Highlights:\n- Reversible dual-sided design: one side with perimeter deep juice groove, reverse side smooth for charcuterie & cheese plating\n- Hand-seasoned with food-safe botanical mineral oils\n- Available in 4 calibrated sizes for compact prepping to grand banquet carving",
                'vendor' => 'HomZy Crafts',
                'product_type' => 'Kitchen',
                'tags' => ['kitchen', 'chopping-board', 'acacia-wood', 'cutting-board', 'charcuterie'],
                'status' => 'active',
                'images' => [
                    'https://images.unsplash.com/photo-1590794056226-79ef3a8147e1?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=1000&q=80',
                ],
                'options' => [
                    ['name' => 'Size', 'values' => ['30x20 cm', '36x25 cm', '50x30 cm', '52x32 cm']],
                ],
                'has_variants' => true,
                'hsn_code' => '4419',
                'tax_rate' => 12.0,
                'collections' => [$colHomeOrganizer],
                'variants' => [
                    [
                        'title' => 'Small (30x20 cm)',
                        'sku' => 'HZ-CB-30X20',
                        'price' => 399.0,
                        'compare_at_price' => 599.0,
                        'cost_per_item' => 180.0,
                        'inventory_quantity' => 50,
                        'weight' => 0.75,
                        'options_map' => ['Size' => '30x20 cm'],
                        'image_url' => 'https://images.unsplash.com/photo-1590794056226-79ef3a8147e1?auto=format&fit=crop&w=1000&q=80',
                    ],
                    [
                        'title' => 'Medium (36x25 cm)',
                        'sku' => 'HZ-CB-36X25',
                        'price' => 599.0,
                        'compare_at_price' => 899.0,
                        'cost_per_item' => 270.0,
                        'inventory_quantity' => 47,
                        'weight' => 1.1,
                        'options_map' => ['Size' => '36x25 cm'],
                        'image_url' => 'https://images.unsplash.com/photo-1590794056226-79ef3a8147e1?auto=format&fit=crop&w=1000&q=80',
                    ],
                    [
                        'title' => 'Large (50x30 cm)',
                        'sku' => 'HZ-CB-50X30',
                        'price' => 899.0,
                        'compare_at_price' => 1299.0,
                        'cost_per_item' => 420.0,
                        'inventory_quantity' => 74,
                        'weight' => 1.7,
                        'options_map' => ['Size' => '50x30 cm'],
                        'image_url' => 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=1000&q=80',
                    ],
                    [
                        'title' => 'Jumbo Chef (52x32 cm)',
                        'sku' => 'HZ-CB-52X32',
                        'price' => 1199.0,
                        'compare_at_price' => 1599.0,
                        'cost_per_item' => 580.0,
                        'inventory_quantity' => 13,
                        'weight' => 2.1,
                        'options_map' => ['Size' => '52x32 cm'],
                        'image_url' => 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=1000&q=80',
                    ],
                ],
            ],

            // PRODUCT 7: Muslin Baby Blanket (from Sheet L. Baby Care Items)
            [
                'title' => 'Pure Organic Cotton Muslin Baby Blanket',
                'slug' => 'organic-cotton-muslin-baby-blanket',
                'description' => "Woven with 4 breathable layers of 100% GOTS-certified organic cotton muslin. Featherlight, cloud-soft, and pre-washed to prevent shrinkage. Sourced in collaboration with Apparel Nation for sensitive infant skin.\n\nKey Highlights:\n- Breathable open-weave regulates baby's temperature without overheating\n- Multi-purpose: ideal as nursery blanket, nursing cover, stroller shade, or playtime mat\n- Gets softer with every wash",
                'vendor' => 'Apparel Nation',
                'product_type' => 'Baby Care',
                'tags' => ['baby-care', 'muslin-blanket', 'swaddle', 'organic-cotton', 'nursery'],
                'status' => 'active',
                'images' => [
                    'https://images.unsplash.com/photo-1519689680058-324335c77eba?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=1000&q=80',
                ],
                'options' => [
                    ['name' => 'Color', 'values' => ['Pastel Sage', 'Cloud White', 'Warm Honey']],
                ],
                'has_variants' => true,
                'hsn_code' => '6301',
                'tax_rate' => 5.0,
                'collections' => [$colBabyCare],
                'variants' => [
                    [
                        'title' => 'Pastel Sage',
                        'sku' => 'HZ-BB-SGE',
                        'price' => 499.0,
                        'compare_at_price' => 699.0,
                        'cost_per_item' => 320.0,
                        'inventory_quantity' => 20,
                        'weight' => 0.35,
                        'options_map' => ['Color' => 'Pastel Sage'],
                        'image_url' => 'https://images.unsplash.com/photo-1519689680058-324335c77eba?auto=format&fit=crop&w=1000&q=80',
                    ],
                    [
                        'title' => 'Cloud White',
                        'sku' => 'HZ-BB-WHT',
                        'price' => 499.0,
                        'compare_at_price' => 699.0,
                        'cost_per_item' => 320.0,
                        'inventory_quantity' => 15,
                        'weight' => 0.35,
                        'options_map' => ['Color' => 'Cloud White'],
                        'image_url' => 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=1000&q=80',
                    ],
                    [
                        'title' => 'Warm Honey',
                        'sku' => 'HZ-BB-HNY',
                        'price' => 499.0,
                        'compare_at_price' => 699.0,
                        'cost_per_item' => 320.0,
                        'inventory_quantity' => 15,
                        'weight' => 0.35,
                        'options_map' => ['Color' => 'Warm Honey'],
                        'image_url' => 'https://images.unsplash.com/photo-1519689680058-324335c77eba?auto=format&fit=crop&w=1000&q=80',
                    ],
                ],
            ],

            // PRODUCT 8: Botanical Soy Scented Candle (from Sheet A. Daily Use & D. Decor)
            [
                'title' => 'Botanical Amber Glass Soy Candle',
                'slug' => 'botanical-amber-glass-soy-candle',
                'description' => "Slow-burning natural soy candle infused with steam-distilled pure botanical fragrance oils and lead-free cotton wick. Poured by hand in apothecary amber glass jars.\n\nKey Highlights:\n- 45+ hours of clean soot-free burn time\n- Calming aromatherapy throw that transforms bedrooms and living areas\n- Eco-friendly vegan soy wax",
                'vendor' => 'HomZy Fragrance',
                'product_type' => 'Home Fragrance',
                'tags' => ['daily-use', 'scented-candle', 'aromatherapy', 'soy-candle', 'amber-glass'],
                'status' => 'active',
                'images' => [
                    'https://images.unsplash.com/photo-1603006905003-be475563bc59?auto=format&fit=crop&w=1000&q=80',
                ],
                'options' => [
                    ['name' => 'Scent', 'values' => ['Wild Lavender', 'Smoked Teakwood']],
                ],
                'has_variants' => true,
                'hsn_code' => '3406',
                'tax_rate' => 18.0,
                'collections' => [$colDailyUse, $colDecorItems],
                'variants' => [
                    [
                        'title' => 'Wild Lavender',
                        'sku' => 'HZ-CNDL-LAV',
                        'price' => 399.0,
                        'compare_at_price' => 599.0,
                        'cost_per_item' => 160.0,
                        'inventory_quantity' => 35,
                        'weight' => 0.4,
                        'options_map' => ['Scent' => 'Wild Lavender'],
                        'image_url' => 'https://images.unsplash.com/photo-1603006905003-be475563bc59?auto=format&fit=crop&w=1000&q=80',
                    ],
                    [
                        'title' => 'Smoked Teakwood',
                        'sku' => 'HZ-CNDL-TKW',
                        'price' => 399.0,
                        'compare_at_price' => 599.0,
                        'cost_per_item' => 160.0,
                        'inventory_quantity' => 30,
                        'weight' => 0.4,
                        'options_map' => ['Scent' => 'Smoked Teakwood'],
                        'image_url' => 'https://images.unsplash.com/photo-1603006905003-be475563bc59?auto=format&fit=crop&w=1000&q=80',
                    ],
                ],
            ],

            // PRODUCT 9: Handwoven Macrame Cotton Rope Basket (from Sheet C. Home Organizer)
            [
                'title' => 'Handwoven Macrame Cotton Storage Basket',
                'slug' => 'macrame-cotton-storage-basket',
                'description' => "Artisanal handwoven cotton rope basket designed for modern living. Keep throws, pillows, magazines, toys, or laundry effortlessly organized.\n\nKey Highlights:\n- Premium natural braided cotton cord with reinforced handles\n- Flexible yet shape-retaining structured weave\n- Chemical-free and safe for children's bedrooms and nursery corners",
                'vendor' => 'HomZy Crafts',
                'product_type' => 'Home Organizer',
                'tags' => ['home-organizer', 'macrame-basket', 'storage-basket', 'cotton-rope', 'decor'],
                'status' => 'active',
                'images' => [
                    'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=1000&q=80',
                ],
                'options' => [],
                'has_variants' => false,
                'hsn_code' => '5607',
                'tax_rate' => 12.0,
                'collections' => [$colHomeOrganizer],
                'variants' => [
                    [
                        'title' => 'Natural Ivory',
                        'sku' => 'HZ-MC-BSK-01',
                        'price' => 599.0,
                        'compare_at_price' => 899.0,
                        'cost_per_item' => 260.0,
                        'inventory_quantity' => 40,
                        'weight' => 0.8,
                        'options_map' => [],
                        'image_url' => 'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=1000&q=80',
                    ]
                ],
            ],

            // PRODUCT 10: Charcoal-Free Incense & Dhoop Cones (from Sheet A. Daily Use)
            [
                'title' => 'Artisanal Charcoal-Free Dhoop Cones',
                'slug' => 'charcoal-free-dhoop-cones',
                'description' => "Pure organic flower waste and pure wood powder dhoop cones. Completely charcoal-free and dipped in therapeutic Mysore Chandan (Sandalwood) and Gugl extracts.\n\nKey Highlights:\n- 100% Charcoal-free: produces zero toxic black smoke\n- Sacred calming fragrance for meditation, puja, and daily wellness\n- 40 cones per box with complementary ceramic burning plate",
                'vendor' => 'HomZy Fragrance',
                'product_type' => 'Daily Use',
                'tags' => ['daily-use', 'incense', 'dhoop', 'chandan', 'gugl', 'wellness'],
                'status' => 'active',
                'images' => [
                    'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=1000&q=80',
                ],
                'options' => [
                    ['name' => 'Fragrance', 'values' => ['Mysore Chandan', 'Organic Gugl']],
                ],
                'has_variants' => true,
                'hsn_code' => '3307',
                'tax_rate' => 12.0,
                'collections' => [$colDailyUse],
                'variants' => [
                    [
                        'title' => 'Mysore Chandan',
                        'sku' => 'HZ-DH-SAN',
                        'price' => 199.0,
                        'compare_at_price' => 299.0,
                        'cost_per_item' => 75.0,
                        'inventory_quantity' => 50,
                        'weight' => 0.25,
                        'options_map' => ['Fragrance' => 'Mysore Chandan'],
                        'image_url' => 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=1000&q=80',
                    ],
                    [
                        'title' => 'Organic Gugl',
                        'sku' => 'HZ-DH-GUG',
                        'price' => 199.0,
                        'compare_at_price' => 299.0,
                        'cost_per_item' => 75.0,
                        'inventory_quantity' => 50,
                        'weight' => 0.25,
                        'options_map' => ['Fragrance' => 'Organic Gugl'],
                        'image_url' => 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?auto=format&fit=crop&w=1000&q=80',
                    ],
                ],
            ],

            // PRODUCT 11: Nordic Solid Teak Armchair (preserving link)
            [
                'title' => 'Nordic Solid Teak Armchair',
                'slug' => 'nordic-solid-teak-armchair',
                'description' => "A timeless accent armchair handcrafted with sustainable plantation teak wood, natural rattan webbing, and premium linen cushioning.",
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
                'collections' => [$colPlantCare],
                'variants' => [
                    [
                        'title' => 'Natural Teak',
                        'sku' => 'NT-ARM-01',
                        'price' => 14499.0,
                        'compare_at_price' => 17999.0,
                        'cost_per_item' => 8500.0,
                        'inventory_quantity' => 12,
                        'weight' => 8.5,
                        'options_map' => ['Finish' => 'Natural Teak'],
                        'image_url' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=1000&q=80',
                    ],
                    [
                        'title' => 'Dark Walnut',
                        'sku' => 'DW-ARM-02',
                        'price' => 15499.0,
                        'compare_at_price' => 18999.0,
                        'cost_per_item' => 9000.0,
                        'inventory_quantity' => 6,
                        'weight' => 8.5,
                        'options_map' => ['Finish' => 'Dark Walnut'],
                        'image_url' => 'https://images.unsplash.com/photo-1567538096630-e0c55bd6374c?auto=format&fit=crop&w=1000&q=80',
                    ]
                ],
            ],
        ];

        // Seed products into MongoDB
        foreach ($productsToSeed as $pData) {
            $colIds = array_map(function ($c) {
                return (string) $c->_id;
            }, $pData['collections']);

            // Upsert product by slug
            $product = Product::where('store_id', $storeId)->where('slug', $pData['slug'])->first();
            if (!$product) {
                $product = new Product();
                $product->store_id = $storeId;
                $product->slug = $pData['slug'];
            }

            $product->title = $pData['title'];
            $product->description = $pData['description'];
            $product->vendor = $pData['vendor'];
            $product->product_type = $pData['product_type'];
            $product->tags = $pData['tags'];
            $product->status = $pData['status'];
            $product->images = $pData['images'];
            $product->options = $pData['options'];
            $product->has_variants = $pData['has_variants'];
            $product->hsn_code = $pData['hsn_code'];
            $product->tax_rate = $pData['tax_rate'];
            $product->collection_ids = $colIds;
            $product->seo_title = "{$pData['title']} | HomZy Living";
            $product->seo_description = substr(strip_tags($pData['description']), 0, 155);
            $product->save();

            // Clear existing variants for this product to prevent duplicate SKUs
            ProductVariant::where('product_id', (string) $product->_id)->delete();

            // Insert fresh variants with accurate stock
            foreach ($pData['variants'] as $vData) {
                $variant = ProductVariant::create([
                    'store_id' => $storeId,
                    'product_id' => (string) $product->_id,
                    'title' => $vData['title'],
                    'sku' => $vData['sku'],
                    'price' => (float) $vData['price'],
                    'compare_at_price' => (float) $vData['compare_at_price'],
                    'cost_per_item' => (float) $vData['cost_per_item'],
                    'inventory_quantity' => (int) $vData['inventory_quantity'],
                    'weight' => (float) $vData['weight'],
                    'options_map' => $vData['options_map'],
                    'image_url' => $vData['image_url'],
                ]);

                InventoryMovement::create([
                    'store_id' => $storeId,
                    'variant_id' => (string) $variant->_id,
                    'delta' => (int) $vData['inventory_quantity'],
                    'resulting_quantity' => (int) $vData['inventory_quantity'],
                    'reason' => 'initial_stock_seed_from_excel',
                    'reference_type' => 'excel_sync',
                ]);
            }
        }

        // Ensure customer demo credentials exist
        Customer::firstOrCreate(
            ['email' => 'customer@aura.com'],
            [
                'store_id' => $storeId,
                'first_name' => 'Pooja',
                'last_name' => 'Mehta',
                'email' => 'customer@aura.com',
                'password' => Hash::make('password123'),
                'phone' => '+91 98200 12345',
                'tags' => ['VIP', 'Repeat Buyer'],
                'notes' => 'Prefers express delivery via Bluedart.',
                'accepts_marketing' => true,
                'total_orders' => 2,
                'total_spent' => 16998.0,
            ]
        );

        $this->command->info('HomZy Excel products & real images seeded successfully!');
    }
}
