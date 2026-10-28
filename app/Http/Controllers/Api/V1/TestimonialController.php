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
     * Default testimonials for initial seed
     */
    private function seedDefaultsIfNeeded($storeId)
    {
        if (Testimonial::where('store_id', (string) $storeId)->count() === 0) {
            $defaults = [
                [
                    'store_id' => (string) $storeId,
                    'author_name' => 'Priya Sharma',
                    'avatar_url' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&q=80',
                    'rating' => 5,
                    'content' => 'Amazing quality and beautiful packaging! The candle smells divine and instantly makes my home feel so calm.',
                    'location' => 'Mumbai, MH',
                    'is_verified' => true,
                    'status' => 'active',
                    'order' => 1,
                    'product_title' => 'Botanical Amber Glass Soy Candle',
                ],
                [
                    'store_id' => (string) $storeId,
                    'author_name' => 'Rahul Mehta',
                    'avatar_url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=150&q=80',
                    'rating' => 5,
                    'content' => 'Super useful tiered plant stands and storage bins. Great quality and very sturdy. My kitchen and balcony look so organized too.',
                    'location' => 'Bengaluru, KA',
                    'is_verified' => true,
                    'status' => 'active',
                    'order' => 2,
                    'product_title' => 'Tiered Metal Plant Stand Set',
                ],
                [
                    'store_id' => (string) $storeId,
                    'author_name' => 'Sneha Kapoor',
                    'avatar_url' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80',
                    'rating' => 5,
                    'content' => 'Loved the mats and woven baskets! Soft, absorbent, and perfect for daily use. HOMZY never disappoints.',
                    'location' => 'New Delhi, DL',
                    'is_verified' => true,
                    'status' => 'active',
                    'order' => 3,
                    'product_title' => 'Handwoven Macrame Basket',
                ],
            ];

            foreach ($defaults as $item) {
                Testimonial::create($item);
            }
        }
    }

    /**
     * List all testimonials for merchant admin
     */
    public function index(Request $request)
    {
        $store = app('current_store') ?? Store::first();
        if ($store) {
            $this->seedDefaultsIfNeeded($store->_id);
        }

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
            'status' => 'required|in:active,inactive',
            'order' => 'nullable|integer',
            'product_title' => 'nullable|string|max:150',
        ]);

        $validated['order'] = $validated['order'] ?? (Testimonial::count() + 1);
        $validated['is_verified'] = $validated['is_verified'] ?? true;
        if (empty($validated['avatar_url'])) {
            $validated['avatar_url'] = 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80';
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

        $this->seedDefaultsIfNeeded($store->_id);

        $testimonials = Testimonial::where('store_id', (string) $store->_id)
            ->where('status', 'active')
            ->orderBy('order', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $testimonials,
        ]);
    }
}
