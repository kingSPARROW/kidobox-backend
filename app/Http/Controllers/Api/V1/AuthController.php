<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\Store;
use App\Models\Theme;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Merchant Registration and First Store Creation
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8',
            'store_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        // Check if merchant already exists
        if (Merchant::where('email', $validated['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => ['A merchant with this email address already exists.'],
            ]);
        }

        // Generate clean unique slug for store
        $baseSlug = Str::slug($validated['store_name']);
        $slug = $baseSlug;
        $counter = 1;
        while (Store::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        // Create Merchant
        $merchant = Merchant::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'status' => 'active',
            'subscription_plan' => 'pro',
            'email_verified_at' => now(),
            'last_login_at' => now(),
        ]);

        // Create Default Store for Merchant
        $store = Store::create([
            'merchant_id' => (string) $merchant->_id,
            'name' => $validated['store_name'],
            'slug' => $slug,
            'custom_domain' => null,
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
            'status' => 'active',
            'branding' => [
                'logo_url' => '',
                'favicon_url' => '',
                'primary_color' => '#2563eb',
                'font_family' => 'Inter, sans-serif',
            ],
            'contact' => [
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? '',
                'support_email' => $validated['email'],
                'address' => 'Main Road, Market Hub',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'postal_code' => '400001',
                'country' => 'India',
            ],
            'settings' => [
                'tax_mode' => 'inclusive',
                'default_gst_rate' => 18,
                'allow_cod' => true,
                'cod_fee' => 50,
                'free_shipping_threshold' => 999,
            ],
            'onboarding_completed' => false,
            'onboarding_step' => 1,
        ]);

        // Initialize Default Theme for Store
        Theme::create([
            'store_id' => (string) $store->_id,
            'name' => 'Aura Modern Luxe',
            'preset' => 'modern_minimal',
            'is_active' => true,
            'is_published' => true,
            'config' => Theme::getDefaultThemeConfig($store->name),
        ]);

        AuditLog::record('merchant_registered', ['merchant_id' => (string) $merchant->_id, 'store_id' => (string) $store->_id], (string) $store->_id);

        $token = $merchant->createToken('merchant_auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Merchant registered successfully.',
            'token' => $token,
            'merchant' => [
                'id' => (string) $merchant->_id,
                'name' => $merchant->name,
                'email' => $merchant->email,
                'subscription_plan' => $merchant->subscription_plan,
            ],
            'store' => $store,
        ], 201);
    }

    /**
     * Merchant Login
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $merchant = Merchant::where('email', $validated['email'])->first();

        if (!$merchant || !Hash::check($validated['password'], $merchant->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password credentials provided.'],
            ]);
        }

        if ($merchant->status === 'suspended') {
            return response()->json([
                'success' => false,
                'message' => 'Your merchant account has been suspended by platform administration.',
            ], 403);
        }

        $merchant->update(['last_login_at' => now()]);

        $store = $merchant->stores()->first();
        $token = $merchant->createToken('merchant_auth_token')->plainTextToken;

        AuditLog::record('merchant_login', ['merchant_id' => (string) $merchant->_id], $store ? (string) $store->_id : null);

        return response()->json([
            'success' => true,
            'message' => 'Logged in successfully.',
            'token' => $token,
            'merchant' => [
                'id' => (string) $merchant->_id,
                'name' => $merchant->name,
                'email' => $merchant->email,
                'subscription_plan' => $merchant->subscription_plan,
            ],
            'store' => $store,
        ]);
    }

    /**
     * Get Current Authenticated Merchant & Stores
     */
    public function me(Request $request)
    {
        $merchant = $request->user();
        $stores = $merchant->stores;
        $activeStore = app()->bound('current_store') ? app('current_store') : $stores->first();

        return response()->json([
            'success' => true,
            'merchant' => [
                'id' => (string) $merchant->_id,
                'name' => $merchant->name,
                'email' => $merchant->email,
                'subscription_plan' => $merchant->subscription_plan,
            ],
            'stores' => $stores,
            'active_store' => $activeStore,
        ]);
    }

    /**
     * Update Onboarding Step
     */
    public function updateOnboarding(Request $request)
    {
        $store = app('current_store');
        if (!$store) {
            return response()->json(['success' => false, 'message' => 'Store not found.'], 404);
        }

        $validated = $request->validate([
            'step' => 'required|integer',
            'completed' => 'nullable|boolean',
        ]);

        $store->update([
            'onboarding_step' => $validated['step'],
            'onboarding_completed' => $validated['completed'] ?? $store->onboarding_completed,
        ]);

        return response()->json([
            'success' => true,
            'store' => $store->fresh(),
        ]);
    }

    /**
     * Update Merchant Password
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);

        $merchant = $request->user();
        if (!$merchant || !Hash::check($validated['current_password'], $merchant->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password provided is incorrect.'],
            ]);
        }

        $merchant->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully.',
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}
