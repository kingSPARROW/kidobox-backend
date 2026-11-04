<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Collection;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    /**
     * Default core pages for SEO management
     */
    public static function getDefaultPages(): array
    {
        return [
            [
                'path' => '/',
                'name' => 'Home Page',
                'default_title' => 'Little Needs. Big Smiles. | KIDOBOX',
                'default_description' => 'Thoughtfully chosen baby and toddler essentials. Organic cotton clothing, non-toxic feeding sets, and gentle care products.',
                'default_h1' => 'Little Needs. Big Smiles.',
                'default_h2' => 'Thoughtfully chosen baby essentials for life’s sweetest little moments.',
                'default_h3' => 'Gentle on skin, safe for milestones, and approved by happy parents.',
            ],
            [
                'path' => '/shop',
                'name' => 'Shop All Products',
                'default_title' => 'Shop All Baby Essentials | KIDOBOX',
                'default_description' => 'Explore the complete range of pediatrician-approved baby clothing, silicone tableware, gentle bath care, and toys.',
                'default_h1' => 'All Baby & Toddler Essentials',
                'default_h2' => 'Everything your little one needs, all in one happy place.',
                'default_h3' => 'Filter by category, age stage, or bestsellers.',
            ],
            [
                'path' => '/categories',
                'name' => 'Categories Directory',
                'default_title' => 'Browse by Category | KIDOBOX',
                'default_description' => 'Explore essential baby categories: Clothing, Diapering, Feeding, Bath Care, Toys, Nursery, and Health & Safety.',
                'default_h1' => 'Shop by Category',
                'default_h2' => 'Curated collections designed for sweet everyday care.',
                'default_h3' => '100% skin-safe and toxin-free materials.',
            ],
            [
                'path' => '/ages',
                'name' => 'Shop by Age',
                'default_title' => 'Shop by Age & Stage | KIDOBOX',
                'default_description' => 'Find developmentally-appropriate essentials for Newborns (0-6M), Babies (6-12M), Toddlers (1-2Y), and Kids (2-4Y).',
                'default_h1' => 'Every Little Stage',
                'default_h2' => 'Tailored comfort and playtime for every milestone.',
                'default_h3' => 'From first cuddles to first playground steps.',
            ],
            [
                'path' => '/guides',
                'name' => 'Parenting Guides & Blog',
                'default_title' => 'Parenting Tips, Guides & Milestones | KIDOBOX Journal',
                'default_description' => 'Read gentle, pediatrician-backed advice on newborn sleep routines, baby-led weaning, play milestones, and baby care.',
                'default_h1' => 'KIDOBOX Parenting Journal',
                'default_h2' => 'Practical parenting wisdom for sweet, messy family life.',
                'default_h3' => 'Curated articles written with love for modern parents.',
            ],
            [
                'path' => '/our-story',
                'name' => 'Our Story',
                'default_title' => 'Our Story & Brand Promise | KIDOBOX',
                'default_description' => 'Learn how KIDOBOX was born out of a desire for truly pure, gentle, and joyful baby care essentials.',
                'default_h1' => 'Crafted with Love for Little Moments',
                'default_h2' => 'Why we created KIDOBOX and our commitment to pure safety.',
                'default_h3' => 'Pediatrician-tested, sustainable materials, and fair care.',
            ],
            [
                'path' => '/faq',
                'name' => 'Help & FAQs',
                'default_title' => 'Frequently Asked Questions & Support | KIDOBOX',
                'default_description' => 'Find quick answers to common questions about orders, shipping, delivery, payments, returns, and safety certifications.',
                'default_h1' => 'How Can We Help You Today?',
                'default_h2' => 'Quick answers to everything you need to know about KIDOBOX.',
                'default_h3' => 'Shipping timelines, returns policy, and product safety info.',
            ],
            [
                'path' => '/contact',
                'name' => 'Contact Us',
                'default_title' => 'Contact Customer Care | KIDOBOX',
                'default_description' => 'Get in touch with the KIDOBOX customer support team via phone, email, or WhatsApp. We are here to help.',
                'default_h1' => 'We’d Love to Hear From You',
                'default_h2' => 'Have questions or feedback? Our team is always here for your family.',
                'default_h3' => 'Response guaranteed within 24 business hours.',
            ],
            [
                'path' => '/shipping',
                'name' => 'Shipping & Delivery',
                'default_title' => 'Shipping Information & PIN Codes | KIDOBOX',
                'default_description' => 'Fast delivery across 28,000+ Indian PIN codes via Shiprocket with real-time SMS updates and tracking.',
                'default_h1' => 'Safe & Fast Delivery Across India',
                'default_h2' => 'Free shipping on all orders above ₹999.',
                'default_h3' => 'Express dispatch within 24 hours of order placement.',
            ],
            [
                'path' => '/returns',
                'name' => '7-Day Easy Returns',
                'default_title' => '7-Day Easy Returns & Refund Guarantee | KIDOBOX',
                'default_description' => 'Hassle-free 7-day doorstep pickup and instant refunds for unused baby products in original packaging.',
                'default_h1' => 'Hassle-Free 7-Day Returns',
                'default_h2' => 'Doorstep pickup and zero-stress refunds guaranteed.',
                'default_h3' => 'Because parenting has enough surprises.',
            ],
            [
                'path' => '/tracking',
                'name' => 'Track Your Order',
                'default_title' => 'Track Your Package & Shipment | KIDOBOX',
                'default_description' => 'Enter your KIDOBOX order reference number or AWB to view real-time delivery milestone updates.',
                'default_h1' => 'Where Is Your Little Package?',
                'default_h2' => 'Real-time courier milestones powered by Shiprocket.',
                'default_h3' => 'Live updates from warehouse dispatch to your doorstep.',
            ],
            [
                'path' => '/privacy',
                'name' => 'Privacy Policy',
                'default_title' => 'Privacy Policy & Data Security | KIDOBOX',
                'default_description' => 'Learn how KIDOBOX protects your personal data, payment details, and browsing privacy.',
                'default_h1' => 'Your Privacy & Trust Matter to Us',
                'default_h2' => 'How we responsibly protect and handle family data.',
                'default_h3' => 'Transparent policies, zero third-party data selling.',
            ],
            [
                'path' => '/terms',
                'name' => 'Terms of Service',
                'default_title' => 'Terms of Service | KIDOBOX',
                'default_description' => 'Read our terms and conditions for ordering, shipping, promotions, and store browsing.',
                'default_h1' => 'Terms of Service',
                'default_h2' => 'Clear terms governing orders, returns, and digital services.',
                'default_h3' => 'Governed by the laws of India.',
            ],
        ];
    }

    /**
     * Get complete SEO configuration for store and all pages
     */
    public function index()
    {
        $store = app('current_store') ?? Store::first();
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Store context not resolved.'], 404);
        }

        $globalSeo = $store->seo ?? [
            'title_tag' => 'Little Needs. Big Smiles. | KIDOBOX',
            'title_template' => '%s | KIDOBOX',
            'meta_description' => 'Thoughtfully chosen baby and toddler essentials. Organic cotton clothing, silicone tableware, and gentle care products.',
            'social_image_url' => '/assets/hd/hero.webp',
            'google_verification' => '',
            'allow_indexing' => true,
            'canonical_url' => 'https://kidobox.store',
            'meta_robots' => 'index, follow',
            'schema_markup' => '',
        ];

        $savedPageSeo = $store->page_seo ?? [];
        $defaultPages = self::getDefaultPages();

        $mergedPages = array_map(function ($page) use ($savedPageSeo, $store) {
            $key = $page['path'];
            $saved = $savedPageSeo[$key] ?? [];

            return [
                'path' => $page['path'],
                'name' => $page['name'],
                'title' => $saved['title'] ?? $page['default_title'],
                'meta_description' => $saved['meta_description'] ?? $page['default_description'],
                'heading_h1' => $saved['heading_h1'] ?? $page['default_h1'],
                'heading_h2' => $saved['heading_h2'] ?? $page['default_h2'],
                'heading_h3' => $saved['heading_h3'] ?? $page['default_h3'],
                'canonical_url' => $saved['canonical_url'] ?? ($store->seo['canonical_url'] ?? 'https://kidobox.store') . ($page['path'] === '/' ? '' : $page['path']),
                'meta_robots' => $saved['meta_robots'] ?? 'index, follow',
                'schema_markup' => $saved['schema_markup'] ?? '',
                'content_blocks' => $saved['content_blocks'] ?? [],
                'updated_at' => $saved['updated_at'] ?? null,
            ];
        }, $defaultPages);

        $productCount = Product::where('store_id', (string) $store->_id)->count();
        $collectionCount = Collection::where('store_id', (string) $store->_id)->count();
        $articles = $store->content['articles'] ?? [];
        $articleCount = count($articles);

        return response()->json([
            'success' => true,
            'data' => [
                'global' => $globalSeo,
                'pages' => $mergedPages,
                'stats' => [
                    'total_products' => $productCount,
                    'total_collections' => $collectionCount,
                    'total_articles' => $articleCount,
                    'total_pages' => count($mergedPages),
                ],
            ],
        ]);
    }

    /**
     * Update global and page SEO settings
     */
    public function update(Request $request)
    {
        $store = app('current_store') ?? Store::first();
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Store context not resolved.'], 404);
        }

        $validated = $request->validate([
            'global' => 'nullable|array',
            'global.title_tag' => 'nullable|string|max:150',
            'global.title_template' => 'nullable|string|max:100',
            'global.meta_description' => 'nullable|string|max:500',
            'global.social_image_url' => 'nullable|string',
            'global.google_verification' => 'nullable|string|max:150',
            'global.allow_indexing' => 'nullable|boolean',
            'global.canonical_url' => 'nullable|string|max:255',
            'global.meta_robots' => 'nullable|string|max:100',
            'global.schema_markup' => 'nullable|string',
            'pages' => 'nullable|array',
            'pages.*.path' => 'required|string',
            'pages.*.title' => 'nullable|string|max:150',
            'pages.*.meta_description' => 'nullable|string|max:500',
            'pages.*.heading_h1' => 'nullable|string|max:255',
            'pages.*.heading_h2' => 'nullable|string|max:255',
            'pages.*.heading_h3' => 'nullable|string|max:255',
            'pages.*.canonical_url' => 'nullable|string|max:255',
            'pages.*.meta_robots' => 'nullable|string|max:100',
            'pages.*.schema_markup' => 'nullable|string',
            'pages.*.content_blocks' => 'nullable|array',
        ]);

        $updateData = [];

        if (isset($validated['global'])) {
            $updateData['seo'] = array_merge($store->seo ?? [], $validated['global']);
        }

        if (isset($validated['pages'])) {
            $currentPageSeo = $store->page_seo ?? [];
            foreach ($validated['pages'] as $pageItem) {
                $path = $pageItem['path'];
                $currentPageSeo[$path] = array_merge($pageItem, ['updated_at' => now()->toIsoString()]);
            }
            $updateData['page_seo'] = $currentPageSeo;
        }

        $store->update($updateData);

        AuditLog::record('seo_settings_updated', ['global' => isset($validated['global']), 'pages_updated' => count($validated['pages'] ?? [])]);

        return response()->json([
            'success' => true,
            'message' => 'SEO settings successfully updated.',
            'data' => [
                'global' => $store->fresh()->seo,
                'page_seo' => $store->fresh()->page_seo,
            ],
        ]);
    }
}

