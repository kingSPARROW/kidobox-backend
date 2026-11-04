<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Store;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CollectionController extends Controller
{
    public function index()
    {
        $collections = Collection::latest()->get()->each(function ($collection) {
            $collection->product_count = \App\Models\Product::where('collection_ids', (string) $collection->_id)->count();
        });

        return response()->json([
            'success' => true,
            'data' => $collections,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string',
            'image_alt' => 'nullable|string|max:255',
            'heading_h1' => 'nullable|string|max:255',
            'heading_h2' => 'nullable|string|max:255',
            'heading_h3' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|string|max:500',
            'meta_robots' => 'nullable|string|max:100',
            'schema_markup' => 'nullable|string',
            'type' => 'nullable|in:manual,automated',
            'rules' => 'nullable|array',
            'sort_order' => 'nullable|string',
            'seo' => 'nullable|array',
            'is_published' => 'nullable|boolean',
        ]);

        $rawSlug = !empty($validated['slug']) ? $validated['slug'] : $validated['title'];
        $baseSlug = Str::slug($rawSlug);
        $slug = $baseSlug;
        $counter = 1;
        while (Collection::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $store = app('current_store') ?? Store::first();

        $seoTitle = $validated['seo']['title'] ?? $validated['title'];
        $seoDesc = $validated['seo']['description'] ?? Str::limit(strip_tags($validated['description'] ?? ''), 160);
        $canonicalUrl = $validated['canonical_url'] ?? ($validated['seo']['canonical_url'] ?? null);
        $metaRobots = $validated['meta_robots'] ?? ($validated['seo']['meta_robots'] ?? 'index, follow');
        $schemaMarkup = $validated['schema_markup'] ?? ($validated['seo']['schema_markup'] ?? null);

        $collection = Collection::create([
            'store_id' => $store ? (string) $store->_id : null,
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'] ?? '',
            'image_url' => $validated['image_url'] ?? null,
            'image_alt' => $validated['image_alt'] ?? null,
            'heading_h1' => $validated['heading_h1'] ?? null,
            'heading_h2' => $validated['heading_h2'] ?? null,
            'heading_h3' => $validated['heading_h3'] ?? null,
            'canonical_url' => $canonicalUrl,
            'meta_robots' => $metaRobots,
            'schema_markup' => $schemaMarkup,
            'type' => $validated['type'] ?? 'manual',
            'rules' => $validated['rules'] ?? [],
            'sort_order' => $validated['sort_order'] ?? 'best-selling',
            'seo' => [
                'title' => $seoTitle,
                'description' => $seoDesc,
                'canonical_url' => $canonicalUrl,
                'meta_robots' => $metaRobots,
                'schema_markup' => $schemaMarkup,
            ],
            'is_published' => $validated['is_published'] ?? true,
        ]);

        AuditLog::record('collection_created', ['collection_id' => (string) $collection->_id, 'title' => $collection->title]);

        return response()->json([
            'success' => true,
            'message' => 'Collection created successfully.',
            'collection' => $collection,
        ], 201);
    }

    public function show($id)
    {
        $collection = Collection::findOrFail($id);

        return response()->json([
            'success' => true,
            'collection' => $collection,
        ]);
    }

    public function update(Request $request, $id)
    {
        $collection = Collection::findOrFail($id);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string',
            'image_alt' => 'nullable|string|max:255',
            'heading_h1' => 'nullable|string|max:255',
            'heading_h2' => 'nullable|string|max:255',
            'heading_h3' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|string|max:500',
            'meta_robots' => 'nullable|string|max:100',
            'schema_markup' => 'nullable|string',
            'type' => 'nullable|in:manual,automated',
            'rules' => 'nullable|array',
            'sort_order' => 'nullable|string',
            'seo' => 'nullable|array',
            'is_published' => 'nullable|boolean',
        ]);

        if (isset($validated['slug'])) {
            $rawSlug = !empty($validated['slug']) ? $validated['slug'] : ($validated['title'] ?? $collection->title);
            $baseSlug = Str::slug($rawSlug);
            $slug = $baseSlug;
            $counter = 1;
            while (Collection::where('slug', $slug)->where('_id', '!=', $collection->_id)->exists()) {
                $slug = "{$baseSlug}-{$counter}";
                $counter++;
            }
            $validated['slug'] = $slug;
        }

        if (isset($validated['seo']) || isset($validated['canonical_url']) || isset($validated['meta_robots']) || isset($validated['schema_markup'])) {
            $currentSeo = $collection->seo ?? [];
            $seoTitle = $validated['seo']['title'] ?? ($currentSeo['title'] ?? ($validated['title'] ?? $collection->title));
            $seoDesc = $validated['seo']['description'] ?? ($currentSeo['description'] ?? Str::limit(strip_tags($validated['description'] ?? ($collection->description ?? '')), 160));
            $canonicalUrl = $validated['canonical_url'] ?? ($validated['seo']['canonical_url'] ?? ($currentSeo['canonical_url'] ?? null));
            $metaRobots = $validated['meta_robots'] ?? ($validated['seo']['meta_robots'] ?? ($currentSeo['meta_robots'] ?? 'index, follow'));
            $schemaMarkup = $validated['schema_markup'] ?? ($validated['seo']['schema_markup'] ?? ($currentSeo['schema_markup'] ?? null));

            $validated['canonical_url'] = $canonicalUrl;
            $validated['meta_robots'] = $metaRobots;
            $validated['schema_markup'] = $schemaMarkup;
            $validated['seo'] = [
                'title' => $seoTitle,
                'description' => $seoDesc,
                'canonical_url' => $canonicalUrl,
                'meta_robots' => $metaRobots,
                'schema_markup' => $schemaMarkup,
            ];
        }

        $collection->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Collection updated successfully.',
            'collection' => $collection->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $collection = Collection::findOrFail($id);
        \App\Models\Product::where('collection_ids', (string) $collection->_id)->get()->each(function ($product) use ($collection) {
            $product->update(['collection_ids' => array_values(array_diff($product->collection_ids ?? [], [(string) $collection->_id]))]);
        });
        $collection->delete();

        AuditLog::record('collection_deleted', ['collection_id' => $id]);

        return response()->json([
            'success' => true,
            'message' => 'Collection deleted successfully.',
        ]);
    }
}
