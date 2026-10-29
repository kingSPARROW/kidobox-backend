<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Banner;
use App\Models\Discount;
use App\Models\Testimonial;
use App\Http\Resources\StorefrontProduct;
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
                        ->get();

        $collections = Collection::where('store_id', (string) $store->_id)
            ->where('is_published', true)
            ->get();

        $banners = \App\Models\Banner::where('store_id', (string) $store->_id)
            ->visible()
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
            'featured_products' => StorefrontProduct::collection($featuredProducts),
            'products' => StorefrontProduct::collection($featuredProducts),
            'content' => $store->content ?? (object) [],
            'testimonials' => Testimonial::where('store_id', (string) $store->_id)->where('status', 'active')->orderBy('order')->get(),
            'offers' => Discount::where('store_id', (string) $store->_id)->where('is_active', true)->get()->filter(fn ($offer) => $offer->isValidForCart(PHP_FLOAT_MAX))->map(fn ($offer) => $offer->only(['id', 'code', 'title', 'type', 'value', 'min_cart_amount', 'max_discount_amount', 'expires_at', 'eligible_collection_ids', 'eligible_product_ids']))->values(),
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
            $col = Collection::where('store_id', (string) $store->_id)->where('slug', $collectionSlug)->where('is_published', true)->first();
            if (!$col) {
                return response()->json(['success' => true, 'data' => [], 'meta' => ['total' => 0, 'current_page' => 1, 'last_page' => 1]]);
            }
            if ($col) {
                $query->where('collection_ids', (string) $col->_id);
            }
        }

        // Variants live in a separate collection; sort their actual prices before slicing.
        $products = $query->latest()->get();
        $sort = $request->query('sort', 'latest');
        if (in_array($sort, ['price-low-high', 'price-high-low'])) {
            $products = $products->sortBy(fn ($p) => $p->variants->min('price'), SORT_REGULAR, $sort === 'price-high-low')->values();
        }
        $page = max(1, (int) $request->query('page', 1));
        $total = $products->count();
        $items = $products->slice(($page - 1) * 20, 20)->values();

        return response()->json([
            'success' => true,
            'data' => StorefrontProduct::collection($items),
            'meta' => [
                'total' => $total,
                'current_page' => $page,
                'last_page' => max(1, (int) ceil($total / 20)),
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
            ->where('status', 'active')
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
            'product' => new StorefrontProduct($product),
            'related' => StorefrontProduct::collection($relatedProducts),
        ]);
    }
}
