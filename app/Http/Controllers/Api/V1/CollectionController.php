<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CollectionController extends Controller
{
    public function index()
    {
        $collections = Collection::latest()->get();

        return response()->json([
            'success' => true,
            'data' => $collections,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string',
            'type' => 'required|in:manual,automated',
            'rules' => 'nullable|array',
            'sort_order' => 'nullable|string',
            'seo' => 'nullable|array',
            'is_published' => 'nullable|boolean',
        ]);

        $baseSlug = Str::slug($validated['title']);
        $slug = $baseSlug;
        $counter = 1;
        while (Collection::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $collection = Collection::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'] ?? '',
            'image_url' => $validated['image_url'] ?? null,
            'type' => $validated['type'],
            'rules' => $validated['rules'] ?? [],
            'sort_order' => $validated['sort_order'] ?? 'best-selling',
            'seo' => $validated['seo'] ?? ['title' => $validated['title'], 'description' => ''],
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
            'description' => 'nullable|string',
            'image_url' => 'nullable|string',
            'type' => 'nullable|in:manual,automated',
            'rules' => 'nullable|array',
            'sort_order' => 'nullable|string',
            'is_published' => 'nullable|boolean',
        ]);

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
        $collection->delete();

        AuditLog::record('collection_deleted', ['collection_id' => $id]);

        return response()->json([
            'success' => true,
            'message' => 'Collection deleted successfully.',
        ]);
    }
}
