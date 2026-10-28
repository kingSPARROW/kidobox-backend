<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    /**
     * Get Current Store Information & Settings
     */
    public function show()
    {
        $store = app('current_store');
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Store context not resolved.'], 404);
        }

        return response()->json([
            'success' => true,
            'store' => $store,
        ]);
    }

    /**
     * Update Store Information & Settings
     */
    public function update(Request $request)
    {
        $store = app('current_store');
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Store context not resolved.'], 404);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'custom_domain' => 'nullable|string|max:255',
            'currency' => 'nullable|string|size:3',
            'timezone' => 'nullable|string|max:50',
            'branding' => 'nullable|array',
            'branding.logo_url' => 'nullable|string',
            'branding.favicon_url' => 'nullable|string',
            'branding.primary_color' => 'nullable|string',
            'branding.font_family' => 'nullable|string',
            'contact' => 'nullable|array',
            'contact.email' => 'nullable|email',
            'contact.phone' => 'nullable|string',
            'contact.support_email' => 'nullable|email',
            'contact.address' => 'nullable|string',
            'contact.city' => 'nullable|string',
            'contact.state' => 'nullable|string',
            'contact.postal_code' => 'nullable|string',
            'contact.country' => 'nullable|string',
            'settings' => 'nullable|array',
            'settings.tax_mode' => 'nullable|in:inclusive,exclusive',
            'settings.default_gst_rate' => 'nullable|numeric',
            'settings.allow_cod' => 'nullable|boolean',
            'settings.cod_fee' => 'nullable|numeric',
            'settings.free_shipping_threshold' => 'nullable|numeric',
            'seo' => 'nullable|array',
            'seo.title_tag' => 'nullable|string|max:100',
            'seo.meta_description' => 'nullable|string|max:350',
            'seo.social_image_url' => 'nullable|string',
            'seo.google_verification' => 'nullable|string|max:100',
            'seo.allow_indexing' => 'nullable|boolean',
            'seo.canonical_url' => 'nullable|string|max:255',
        ]);

        $updateData = [];

        if (isset($validated['name'])) $updateData['name'] = $validated['name'];
        if (isset($validated['custom_domain'])) $updateData['custom_domain'] = $validated['custom_domain'];
        if (isset($validated['currency'])) $updateData['currency'] = $validated['currency'];
        if (isset($validated['timezone'])) $updateData['timezone'] = $validated['timezone'];

        if (isset($validated['branding'])) {
            $updateData['branding'] = array_merge($store->branding ?? [], $validated['branding']);
        }
        if (isset($validated['contact'])) {
            $updateData['contact'] = array_merge($store->contact ?? [], $validated['contact']);
        }
        if (isset($validated['settings'])) {
            $updateData['settings'] = array_merge($store->settings ?? [], $validated['settings']);
        }
        if (isset($validated['seo'])) {
            $updateData['seo'] = array_merge($store->seo ?? [], $validated['seo']);
        }

        $store->update($updateData);

        AuditLog::record('store_settings_updated', ['updated_fields' => array_keys($updateData)]);

        return response()->json([
            'success' => true,
            'message' => 'Store settings saved successfully.',
            'store' => $store->fresh(),
        ]);
    }
}
