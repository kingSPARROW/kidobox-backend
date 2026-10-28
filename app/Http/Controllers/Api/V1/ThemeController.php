<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Theme;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    /**
     * Get active theme and configurations
     */
    public function show()
    {
        $theme = Theme::where('is_active', true)->first();

        if (!$theme) {
            $store = app('current_store');
            $theme = Theme::create([
                'name' => 'KIDOBOX Playful Pastel',
                'preset' => 'kidobox_pastel',
                'is_active' => true,
                'is_published' => true,
                'config' => Theme::getDefaultThemeConfig($store ? $store->name : 'KIDOBOX'),
            ]);
        }

        return response()->json([
            'success' => true,
            'theme' => $theme,
        ]);
    }

    /**
     * Update Theme Configuration (sections, colors, branding)
     */
    public function update(Request $request)
    {
        $theme = Theme::where('is_active', true)->first();

        if (!$theme) {
            $store = app('current_store');
            $theme = Theme::create([
                'name' => 'KIDOBOX Playful Pastel',
                'preset' => 'kidobox_pastel',
                'is_active' => true,
                'is_published' => true,
                'config' => Theme::getDefaultThemeConfig($store ? $store->name : 'KIDOBOX'),
            ]);
        }

        $validated = $request->validate([
            'config' => 'required|array',
            'is_published' => 'nullable|boolean',
        ]);

        $theme->update([
            'config' => $validated['config'],
            'is_published' => $validated['is_published'] ?? $theme->is_published,
        ]);

        AuditLog::record('theme_updated', ['theme_id' => (string) $theme->_id]);

        return response()->json([
            'success' => true,
            'message' => 'Theme updated and published successfully.',
            'theme' => $theme->fresh(),
        ]);
    }

    /**
     * Switch theme preset
     */
    public function applyPreset(Request $request)
    {
        $validated = $request->validate([
            'preset' => 'required|in:kidobox_pastel,boy_blue,girl_pink,mint_neutral',
        ]);

        $theme = Theme::where('is_active', true)->firstOrFail();
        $config = $theme->config ?? Theme::getDefaultThemeConfig('KIDOBOX');

        switch ($validated['preset']) {
            case 'boy_blue':
                $config['branding']['default_mode'] = 'boy';
                $config['branding']['primary_color'] = '#0284c7';
                break;
            case 'girl_pink':
                $config['branding']['default_mode'] = 'girl';
                $config['branding']['primary_color'] = '#ff268b';
                break;
            case 'mint_neutral':
                $config['branding']['primary_color'] = '#20a886';
                $config['branding']['secondary_color'] = '#0284c7';
                break;
            default:
                $config['branding']['default_mode'] = 'girl';
                $config['branding']['primary_color'] = '#ff268b';
                $config['branding']['boy_color'] = '#0284c7';
                break;
        }

        $theme->update([
            'preset' => $validated['preset'],
            'config' => $config,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Theme preset '{$validated['preset']}' applied.",
            'theme' => $theme->fresh(),
        ]);
    }
}
