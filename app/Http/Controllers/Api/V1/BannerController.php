<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Store;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    /**
     * List all banners for merchant admin
     */
    public function index(Request $request)
    {
        $query = Banner::query()->orderBy('order', 'asc');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($position = $request->query('position')) {
            $query->where('position', $position);
        }

        $banners = $query->get();

        return response()->json([
            'success' => true,
            'data' => $banners,
        ]);
    }

    /**
     * Create a new banner
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'title_tag' => 'nullable|string|max:100',
            'subtitle' => 'nullable|string',
            'desktop_image_url' => 'required|string',
            'mobile_image_url' => 'nullable|string',
            'cta_text' => 'nullable|string|max:60',
            'cta_link' => 'nullable|string|max:255',
            'secondary_cta_text' => 'nullable|string|max:60',
            'secondary_cta_link' => 'nullable|string|max:255',
            'text_alignment' => 'nullable|in:left,center,right',
            'overlay_opacity' => 'nullable|numeric|min:0|max:1',
            'status' => 'required|in:active,scheduled,inactive',
            'position' => 'nullable|in:hero,promotional_strip,footer_banner',
            'order' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        // Default mobile image to desktop if not provided
        if (empty($validated['mobile_image_url'])) {
            $validated['mobile_image_url'] = $validated['desktop_image_url'];
        }

        $validated['text_alignment'] = $validated['text_alignment'] ?? 'left';
        $validated['overlay_opacity'] = isset($validated['overlay_opacity']) ? (float) $validated['overlay_opacity'] : 0.55;
        $validated['position'] = $validated['position'] ?? 'hero';
        $validated['order'] = $validated['order'] ?? (Banner::count() + 1);

        $banner = Banner::create($validated);

        AuditLog::record('banner_created', ['banner_id' => (string) $banner->_id, 'title' => $banner->title]);

        return response()->json([
            'success' => true,
            'message' => 'Banner created successfully.',
            'data' => $banner,
        ], 201);
    }

    /**
     * Get single banner by ID
     */
    public function show($id)
    {
        $banner = Banner::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $banner,
        ]);
    }

    /**
     * Update banner
     */
    public function update(Request $request, $id)
    {
        $banner = Banner::findOrFail($id);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'title_tag' => 'nullable|string|max:100',
            'subtitle' => 'nullable|string',
            'desktop_image_url' => 'sometimes|required|string',
            'mobile_image_url' => 'nullable|string',
            'cta_text' => 'nullable|string|max:60',
            'cta_link' => 'nullable|string|max:255',
            'secondary_cta_text' => 'nullable|string|max:60',
            'secondary_cta_link' => 'nullable|string|max:255',
            'text_alignment' => 'nullable|in:left,center,right',
            'overlay_opacity' => 'nullable|numeric|min:0|max:1',
            'status' => 'sometimes|required|in:active,scheduled,inactive',
            'position' => 'nullable|in:hero,promotional_strip,footer_banner',
            'order' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        if (array_key_exists('mobile_image_url', $validated) && empty($validated['mobile_image_url']) && !empty($validated['desktop_image_url'])) {
            $validated['mobile_image_url'] = $validated['desktop_image_url'];
        }

        $banner->update($validated);

        AuditLog::record('banner_updated', ['banner_id' => (string) $banner->_id, 'title' => $banner->title]);

        return response()->json([
            'success' => true,
            'message' => 'Banner updated successfully.',
            'data' => $banner->fresh(),
        ]);
    }

    /**
     * Delete banner
     */
    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);
        $title = $banner->title;
        $banner->delete();

        AuditLog::record('banner_deleted', ['banner_id' => $id, 'title' => $title]);

        return response()->json([
            'success' => true,
            'message' => 'Banner deleted successfully.',
        ]);
    }

    /**
     * Public storefront endpoint returning active banners
     */
    public function storefrontBanners(Request $request)
    {
        $store = app('current_store') ?? Store::first();
        if (!$store) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $banners = Banner::where('store_id', (string) $store->_id)
            ->visible()
            ->orderBy('order', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $banners,
        ]);
    }

    /**
     * Upload banner image (desktop or mobile)
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,avif,gif|max:10240',
        ]);

        $file = $request->file('image');
        $filename = 'banner_' . time() . '_' . \Illuminate\Support\Str::random(8) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('banners', $filename, 'public');

        $url = asset('storage/' . $path);

        return response()->json([
            'success' => true,
            'url' => $url,
            'filename' => $filename,
        ]);
    }
}
