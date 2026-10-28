<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Collection;
use App\Models\Theme;
use App\Models\Store;
use Illuminate\Http\Request;

class StorefrontController extends Controller
{
    /**
     * Get Storefront Theme, Branding, and Homepage configuration
     */
    public function getStorefrontData(Request $request)
    {
        $store = app('current_store');

        if (!$store) {
            $store = Store::where('status', 'active')->first();
        }

        if (!$store) {
            return response()->json([
                'success' => false,
                'message' => 'No active store found.',
            ], 404);
        }

        $theme = Theme::where('store_id', (string) $store->_id)
            ->where('is_active', true)
            ->first();

        if (!$theme) {
            $theme = Theme::create([
                'store_id' => (string) $store->_id,
                'name' => 'Aura Modern Luxe',
                'preset' => 'modern_minimal',
                'is_active' => true,
                'is_published' => true,
                'config' => Theme::getDefaultThemeConfig($store->name),
            ]);
        }

        $featuredProducts = Product::where('store_id', (string) $store->_id)
            ->where('status', 'active')
            ->with('variants')
            ->latest()
            ->limit(8)
            ->get();

        $collections = Collection::where('store_id', (string) $store->_id)
            ->where('is_published', true)
            ->get();

        $banners = \App\Models\Banner::where('store_id', (string) $store->_id)
            ->where('status', 'active')
            ->orderBy('order', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'store' => [
                'id' => (string) $store->_id,
                'name' => $store->name,
                'slug' => $store->slug,
                'currency' => $store->currency ?? 'INR',
                'branding' => $store->branding ?? [],
                'contact' => $store->contact ?? [],
                'settings' => $store->settings ?? [],
                'seo' => $store->seo ?? [
                    'title_tag' => 'Little Needs. Big Smiles. | KIDOBOX',
                    'meta_description' => 'Thoughtfully chosen baby & toddler essentials. 100% skin-safe, gentle, organic, and parent-approved.',
                    'social_image_url' => '/assets/hd/hero.webp',
                    'allow_indexing' => true,
                ],
            ],
            'theme' => $theme,
            'banners' => $banners,
            'featured_products' => $featuredProducts,
            'collections' => $collections,
        ]);
    }

    /**
     * Public Products Listing with search, collection filter, and sorting
     */
    public function getProducts(Request $request)
    {
        $store = app('current_store') ?? Store::where('status', 'active')->first();
        if (!$store) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $query = Product::where('store_id', (string) $store->_id)
            ->where('status', 'active')
            ->with('variants');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('vendor', 'like', "%{$search}%")
                  ->orWhere('tags', $search);
            });
        }

        if ($collectionSlug = $request->query('collection')) {
            $col = Collection::where('store_id', (string) $store->_id)->where('slug', $collectionSlug)->first();
            if ($col) {
                $query->where('collection_ids', (string) $col->_id);
            }
        }

        $sort = $request->query('sort', 'latest');
        if ($sort === 'price-low-high') {
            $query->orderBy('variants.price', 'asc');
        } elseif ($sort === 'price-high-low') {
            $query->orderBy('variants.price', 'desc');
        } else {
            $query->latest();
        }

        $products = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'meta' => [
                'total' => $products->total(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
            ],
        ]);
    }

    /**
     * Get Product by Slug for Product Detail Page
     */
    public function getProductBySlug($slug)
    {
        $store = app('current_store') ?? Store::where('status', 'active')->first();
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Store not found.'], 404);
        }

        $product = Product::where('store_id', (string) $store->_id)
            ->where('slug', $slug)
            ->with('variants')
            ->firstOrFail();

        // Also fetch related products
        $relatedProducts = Product::where('store_id', (string) $store->_id)
            ->where('status', 'active')
            ->where('_id', '!=', $product->_id)
            ->with('variants')
            ->limit(4)
            ->get();

        return response()->json([
            'success' => true,
            'product' => $product,
            'related' => $relatedProducts,
        ]);
    }
}
