<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Models\Store;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    /**
     * List all testimonials for merchant admin
     */
    public function index(Request $request)
    {
        $store = app('current_store') ?? Store::first();

        $query = Testimonial::query()->orderBy('order', 'asc');

        if ($status = $request->query('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        $testimonials = $query->get();

        return response()->json([
            'success' => true,
            'data' => $testimonials,
        ]);
    }

    /**
     * Store new testimonial
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'author_name' => 'required|string|max:120',
            'avatar_url' => 'nullable|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'content' => 'required|string|max:1000',
            'location' => 'nullable|string|max:100',
            'is_verified' => 'boolean',
            'status' => 'nullable|in:active,inactive',
            'order' => 'nullable|integer',
            'product_title' => 'nullable|string|max:150',
        ]);

        $validated['status'] = $validated['status'] ?? 'active';
        $validated['order'] = $validated['order'] ?? (Testimonial::count() + 1);
        $validated['is_verified'] = $validated['is_verified'] ?? true;
        if (empty($validated['avatar_url'])) {
            $validated['avatar_url'] = 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80';
        }

        $store = app('current_store') ?? Store::first();
        if ($store) {
            $validated['store_id'] = (string) $store->_id;
        }

        $testimonial = Testimonial::create($validated);

        AuditLog::record('testimonial_created', [
            'testimonial_id' => (string) $testimonial->_id,
            'author_name' => $testimonial->author_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Testimonial created successfully.',
            'data' => $testimonial,
        ], 201);
    }

    /**
     * Show single testimonial
     */
    public function show($id)
    {
        $testimonial = Testimonial::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $testimonial,
        ]);
    }

    /**
     * Update testimonial
     */
    public function update(Request $request, $id)
    {
        $testimonial = Testimonial::findOrFail($id);

        $validated = $request->validate([
            'author_name' => 'sometimes|required|string|max:120',
            'avatar_url' => 'nullable|string|max:1000',
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'content' => 'sometimes|required|string|max:1000',
            'location' => 'nullable|string|max:100',
            'is_verified' => 'boolean',
            'status' => 'sometimes|required|in:active,inactive',
            'order' => 'nullable|integer',
            'product_title' => 'nullable|string|max:150',
        ]);

        $testimonial->update($validated);

        AuditLog::record('testimonial_updated', [
            'testimonial_id' => (string) $testimonial->_id,
            'author_name' => $testimonial->author_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Testimonial updated successfully.',
            'data' => $testimonial,
        ]);
    }

    /**
     * Delete testimonial
     */
    public function destroy($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $author = $testimonial->author_name;
        $testimonial->delete();

        AuditLog::record('testimonial_deleted', [
            'testimonial_id' => $id,
            'author_name' => $author,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Testimonial removed successfully.',
        ]);
    }

    /**
     * Toggle testimonial active status
     */
    public function toggle($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->status = ($testimonial->status === 'active') ? 'inactive' : 'active';
        $testimonial->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated to ' . $testimonial->status,
            'data' => $testimonial,
        ]);
    }

    /**
     * Public storefront endpoint returning active testimonials
     */
    public function storefrontTestimonials(Request $request)
    {
        $store = app('current_store') ?? Store::first();
        if (!$store) {
            return response()->json(['success' => true, 'data' => []]);
        }


        $testimonials = Testimonial::where(function($q) use ($store) {
                $q->where('store_id', (string) $store->_id)->orWhereNull('store_id');
            })
            ->where('status', 'active')
            ->orderBy('order', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $testimonials,
        ]);
    }
}
