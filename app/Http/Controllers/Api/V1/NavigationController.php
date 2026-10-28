<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Store;

class NavigationController extends Controller
{
    /**
     * Get all menus and footer config for Admin
     */
    public function index(Request $request)
    {
        $store = app('current_store') ?? Store::where('status', 'active')->first();
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Store not found.'], 404);
        }

        $storeId = (string) $store->_id;
        $this->ensureDefaultMenus($storeId);

        $menus = Menu::where('store_id', $storeId)->get();
        $footerConfig = $this->getFooterConfig($store);

        return response()->json([
            'success' => true,
            'menus' => $menus,
            'footer' => $footerConfig,
        ]);
    }

    /**
     * Public endpoint for Storefront header and footer
     */
    public function getStorefrontNavigation(Request $request)
    {
        $store = app('current_store') ?? Store::where('status', 'active')->first();
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Store not found.'], 404);
        }

        $storeId = (string) $store->_id;
        $this->ensureDefaultMenus($storeId);

        $mainMenu = Menu::where('store_id', $storeId)
            ->where('handle', 'main-menu')
            ->where('is_active', true)
            ->first();

        $footerMenus = Menu::where('store_id', $storeId)
            ->where('handle', '!=', 'main-menu')
            ->where('is_active', true)
            ->get();

        $footerConfig = $this->getFooterConfig($store);

        return response()->json([
            'success' => true,
            'main_menu' => $mainMenu ? $mainMenu->items : [],
            'footer_menus' => $footerMenus,
            'footer' => $footerConfig,
        ]);
    }

    /**
     * Create or Update a Menu
     */
    public function saveMenu(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|string',
            'title' => 'required|string|max:100',
            'handle' => 'required|string|max:100',
            'items' => 'required|array',
            'is_active' => 'boolean',
        ]);

        $store = app('current_store') ?? Store::where('status', 'active')->first();
        $storeId = (string) $store->_id;

        if (!empty($validated['id'])) {
            $menu = Menu::where('store_id', $storeId)->where('_id', $validated['id'])->first();
            if (!$menu) {
                return response()->json(['success' => false, 'message' => 'Menu not found.'], 404);
            }
        } else {
            // Check if handle already exists for this store
            $menu = Menu::where('store_id', $storeId)->where('handle', $validated['handle'])->first();
            if (!$menu) {
                $menu = new Menu();
                $menu->store_id = $storeId;
            }
        }

        $menu->title = $validated['title'];
        $menu->handle = $validated['handle'];
        $menu->items = $validated['items'];
        $menu->is_active = $validated['is_active'] ?? true;
        $menu->save();

        return response()->json([
            'success' => true,
            'message' => "Menu '{$menu->title}' saved successfully.",
            'menu' => $menu,
        ]);
    }

    /**
     * Delete a Menu
     */
    public function deleteMenu(Request $request, $id)
    {
        $store = app('current_store') ?? Store::where('status', 'active')->first();
        $storeId = (string) $store->_id;

        $menu = Menu::where('store_id', $storeId)->where('_id', $id)->first();
        if (!$menu) {
            return response()->json(['success' => false, 'message' => 'Menu not found.'], 404);
        }

        if ($menu->handle === 'main-menu') {
            return response()->json(['success' => false, 'message' => 'Cannot delete the default Main Menu.'], 400);
        }

        $menu->delete();

        return response()->json([
            'success' => true,
            'message' => 'Menu deleted successfully.',
        ]);
    }

    /**
     * Save Footer Configuration
     */
    public function saveFooter(Request $request)
    {
        $validated = $request->validate([
            'about_text' => 'nullable|string',
            'copyright_text' => 'nullable|string',
            'show_newsletter' => 'boolean',
            'newsletter_title' => 'nullable|string',
            'newsletter_subtitle' => 'nullable|string',
            'social_links' => 'nullable|array',
            'columns' => 'nullable|array',
            'guarantees' => 'nullable|array',
        ]);

        $store = app('current_store') ?? Store::where('status', 'active')->first();
        $settings = $store->settings ?? [];

        $settings['footer'] = [
            'about_text' => $validated['about_text'] ?? 'Crafting inspiring living spaces with artisanal ceramics, ambient lighting, and bespoke minimalist furniture.',
            'copyright_text' => $validated['copyright_text'] ?? '© ' . date('Y') . " {$store->name}. Built with HomZy Commerce Engine.",
            'show_newsletter' => $validated['show_newsletter'] ?? true,
            'newsletter_title' => $validated['newsletter_title'] ?? 'Join the HomZy Inner Circle',
            'newsletter_subtitle' => $validated['newsletter_subtitle'] ?? 'Subscribe to receive exclusive launches, private sales, and 10% off your first order.',
            'social_links' => $validated['social_links'] ?? [
                ['platform' => 'Instagram', 'url' => 'https://instagram.com'],
                ['platform' => 'Facebook', 'url' => 'https://facebook.com'],
                ['platform' => 'Twitter', 'url' => 'https://twitter.com'],
                ['platform' => 'Pinterest', 'url' => 'https://pinterest.com'],
            ],
            'columns' => $validated['columns'] ?? [],
            'guarantees' => $validated['guarantees'] ?? [
                ['title' => 'Free Delivery on Orders ₹999+', 'desc' => 'Across 28,000+ PIN codes with Shiprocket express tracking'],
                ['title' => 'Razorpay & Verified COD', 'desc' => '100% encrypted and RBI compliant transaction gateway'],
                ['title' => '7-Day Easy Returns & Exchange', 'desc' => 'Hassle-free reverse doorstep pickup guaranteed'],
            ],
        ];

        $store->settings = $settings;
        $store->save();

        return response()->json([
            'success' => true,
            'message' => 'Footer configuration updated successfully.',
            'footer' => $settings['footer'],
        ]);
    }

    /**
     * Helper to load footer config with defaults
     */
    private function getFooterConfig(Store $store): array
    {
        $stored = $store->settings['footer'] ?? $store->footer_config ?? [];

        return [
            'about_text' => $stored['about_text'] ?? 'Thoughtfully curated home essentials for a cleaner, calmer and happier home. Designed for modern Indian living spaces.',
            'copyright_text' => $stored['copyright_text'] ?? '© 2026 HOMZY. Home Made Easy. All rights reserved.',
            'show_newsletter' => $stored['show_newsletter'] ?? true,
            'newsletter_title' => $stored['newsletter_title'] ?? 'Join the HOMZY Family',
            'newsletter_subtitle' => $stored['newsletter_subtitle'] ?? 'Get exclusive offers, new arrivals and home inspiration straight to your inbox.',
            'social_links' => $stored['social_links'] ?? [
                ['platform' => 'Instagram', 'url' => 'https://instagram.com/homzy'],
                ['platform' => 'Facebook', 'url' => 'https://facebook.com/homzy'],
                ['platform' => 'Twitter', 'url' => 'https://twitter.com/homzy'],
                ['platform' => 'Pinterest', 'url' => 'https://pinterest.com/homzy'],
            ],
            'columns' => $stored['columns'] ?? [
                [
                    'title' => 'Shop',
                    'links' => [
                        ['label' => 'Best Sellers', 'url' => '/products'],
                        ['label' => 'New Arrivals', 'url' => '/products?filter=new'],
                        ['label' => 'Offers & Deals', 'url' => '/products?filter=sale'],
                        ['label' => 'Combos & Gifts', 'url' => '/products?category=gifts'],
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
            'guarantees' => $stored['guarantees'] ?? [
                ['title' => 'Free Delivery on Orders ₹499+', 'desc' => 'Across 28,000+ PIN codes with Shiprocket express tracking'],
                ['title' => 'Cash on Delivery Available', 'desc' => '100% safe & verified delivery at your doorstep'],
                ['title' => 'Easy 7-Day Returns & Exchange', 'desc' => 'Hassle-free reverse doorstep pickup guaranteed'],
            ],
        ];
    }

    /**
     * Ensure default Main Menu and Footer Links exist
     */
    private function ensureDefaultMenus(string $storeId): void
    {
        $mainMenu = Menu::where('store_id', $storeId)->where('handle', 'main-menu')->first();
        if (!$mainMenu) {
            Menu::create([
                'store_id' => $storeId,
                'title' => 'Main Menu',
                'handle' => 'main-menu',
                'is_active' => true,
                'items' => [
                    ['id' => 'm1', 'label' => 'Home', 'url' => '/', 'target' => '_self'],
                    ['id' => 'm2', 'label' => 'All Products', 'url' => '/products', 'target' => '_self'],
                    [
                        'id' => 'm3',
                        'label' => 'Collections',
                        'url' => '/collections',
                        'target' => '_self',
                        'children' => [
                            ['id' => 'm3_1', 'label' => 'Plant Care & Garden', 'url' => '/collections/plant-care', 'target' => '_self'],
                            ['id' => 'm3_2', 'label' => 'Home & Kitchen Organizer', 'url' => '/collections/home-organizer', 'target' => '_self'],
                            ['id' => 'm3_3', 'label' => 'Decor Items & Planters', 'url' => '/collections/decor-items', 'target' => '_self'],
                            ['id' => 'm3_4', 'label' => 'Baby Care Items', 'url' => '/collections/baby-care', 'target' => '_self'],
                        ]
                    ],
                    ['id' => 'm4', 'label' => 'Track Order', 'url' => '/tracking', 'target' => '_self'],
                ],
            ]);
        }

        $footerMenu = Menu::where('store_id', $storeId)->where('handle', 'footer-explore')->first();
        if (!$footerMenu) {
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
        }
    }
}
