<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Store;
use App\Models\Menu;

class UpdateKidoboxFooterSeeder extends Seeder
{
    public function run(): void
    {
        $stores = Store::whereIn('slug', ['kidobox', 'homzy', 'aura-living'])->get();
        if ($stores->isEmpty()) {
            $stores = Store::all();
        }

        $footerData = [
            'about_text' => "Thoughtfully chosen baby and toddler essentials for life's sweetest little moments. Soft organic cotton clothing, gentle bath care, food-grade tableware, and Montessori toys.",
            'copyright_text' => '© ' . date('Y') . " KIDOBOX Baby Essentials Pvt Ltd. All rights reserved. Crafted with",
            'show_newsletter' => true,
            'newsletter_title' => 'Join the KIDOBOX Family',
            'newsletter_subtitle' => 'Subscribe for parenting tips, new arrivals, and special family offers.',
            'social_links' => [
                ['platform' => 'Instagram', 'url' => 'https://instagram.com/kidobox'],
                ['platform' => 'Facebook', 'url' => 'https://facebook.com/kidobox'],
                ['platform' => 'YouTube', 'url' => 'https://youtube.com/kidobox'],
                ['platform' => 'Pinterest', 'url' => 'https://pinterest.com/kidobox'],
            ],
            'columns' => [
                [
                    'title' => 'Shop Essentials',
                    'links' => [
                        ['label' => 'Baby Clothing', 'url' => '/clothing'],
                        ['label' => 'Diapering & Care', 'url' => '/diapering'],
                        ['label' => 'Feeding & Tableware', 'url' => '/feeding'],
                        ['label' => 'Bath & Gentle Care', 'url' => '/bath-care'],
                        ['label' => 'Toys & Playtime', 'url' => '/toys'],
                        ['label' => 'Nursery & Bedding', 'url' => '/nursery'],
                    ],
                ],
                [
                    'title' => 'Shop by Age',
                    'links' => [
                        ['label' => 'Newborn (0-6M)', 'url' => '/age-newborn'],
                        ['label' => 'Baby (6-12M)', 'url' => '/age-baby'],
                        ['label' => 'Toddler (1-2Y)', 'url' => '/age-toddler'],
                        ['label' => 'Kids (2-4Y)', 'url' => '/age-kids'],
                        ['label' => 'All Stages', 'url' => '/ages'],
                    ],
                ],
                [
                    'title' => 'Customer Care',
                    'links' => [
                        ['label' => 'Track Your Order', 'url' => '/tracking'],
                        ['label' => 'Shipping & Delivery', 'url' => '/shipping'],
                        ['label' => '7-Day Easy Returns', 'url' => '/returns'],
                        ['label' => 'Help & FAQs', 'url' => '/faq'],
                        ['label' => 'Contact Us', 'url' => '/contact'],
                    ],
                ],
                [
                    'title' => 'About KIDOBOX',
                    'links' => [
                        ['label' => 'Our Story', 'url' => '/our-story'],
                        ['label' => 'Parenting Guides', 'url' => '/guides'],
                        ['label' => 'Family Community', 'url' => '/community'],
                        ['label' => 'Privacy Policy', 'url' => '/privacy'],
                        ['label' => 'Terms of Service', 'url' => '/terms'],
                    ],
                ],
            ],
            'guarantees' => [
                ['title' => 'Free Delivery on Orders ₹999+', 'desc' => 'Across 28,000+ PIN codes with express Shiprocket tracking'],
                ['title' => '100% Genuine Products', 'desc' => 'Pediatrician-tested and certified non-toxic materials'],
                ['title' => '7-Day Easy Returns', 'desc' => 'Hassle-free doorstep pickup & instant refund guaranteed'],
            ],
        ];

        foreach ($stores as $store) {
            $settings = $store->settings ?? [];
            $settings['footer'] = $footerData;
            $store->settings = $settings;
            $store->footer_config = $footerData;
            $store->save();

            $storeId = (string) $store->_id;

            // Ensure footer menu exists
            Menu::updateOrCreate(
                ['store_id' => $storeId, 'handle' => 'footer-explore'],
                [
                    'title' => 'Footer - Explore Store',
                    'is_active' => true,
                    'items' => [
                        ['id' => 'fe1', 'label' => 'Baby Clothing', 'url' => '/clothing', 'target' => '_self'],
                        ['id' => 'fe2', 'label' => 'Diapering Care', 'url' => '/diapering', 'target' => '_self'],
                        ['id' => 'fe3', 'label' => 'Feeding Essentials', 'url' => '/feeding', 'target' => '_self'],
                        ['id' => 'fe4', 'label' => 'Gentle Bath & Body', 'url' => '/bath-care', 'target' => '_self'],
                        ['id' => 'fe5', 'label' => 'Montessori Toys', 'url' => '/toys', 'target' => '_self'],
                    ],
                ]
            );

            echo "Updated footer data for store: {$store->name} ({$store->slug})\n";
        }
    }
}
