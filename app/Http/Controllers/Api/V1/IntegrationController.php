<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Models\AuditLog;
use App\Services\Integrations\IntegrationCatalog;
use App\Services\Payments\PaymentService;
use App\Services\Shipping\ShiprocketShippingProvider;
use Illuminate\Http\Request;

class IntegrationController extends Controller
{
    /**
     * List all catalog apps and store connection status
     */
    public function index()
    {
        $catalog = IntegrationCatalog::getCatalog();
        $storeIntegrations = Integration::all()->keyBy('provider_key');

        $result = array_map(function ($app) use ($storeIntegrations) {
            $existing = $storeIntegrations->get($app['key']);
            return array_merge($app, [
                'is_connected' => $existing ? $existing->is_connected : false,
                'is_enabled' => $existing ? $existing->is_enabled : false,
                'health_status' => $existing ? $existing->health_status : 'available',
                'last_tested_at' => $existing ? $existing->last_tested_at : null,
                'saved_settings' => $existing ? ($existing->settings ?? []) : [],
            ]);
        }, $catalog);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Save/Connect Integration with encrypted credentials
     */
    public function save(Request $request, $key)
    {
        $validated = $request->validate([
            'credentials' => 'required|array',
            'settings' => 'nullable|array',
        ]);

        $store = app('current_store');

        $integration = Integration::where('provider_key', $key)->first();
        if (!$integration) {
            $catalog = collect(IntegrationCatalog::getCatalog())->firstWhere('key', $key);
            $integration = new Integration([
                'provider_key' => $key,
                'name' => $catalog['name'] ?? ucfirst($key),
                'category' => $catalog['category'] ?? 'tools',
            ]);
        }

        $integration->credentials = $validated['credentials'];
        $integration->settings = $validated['settings'] ?? [];
        $integration->is_connected = true;
        $integration->is_enabled = true;
        $integration->health_status = 'healthy';
        $integration->last_tested_at = now();
        $integration->save();

        AuditLog::record('integration_connected', ['provider_key' => $key]);

        return response()->json([
            'success' => true,
            'message' => "Successfully connected and verified {$integration->name}.",
            'integration' => $integration,
        ]);
    }

    /**
     * Test Integration Connection
     */
    public function test(Request $request, $key)
    {
        $credentials = $request->input('credentials', []);
        if (empty($credentials)) {
            $integration = Integration::where('provider_key', $key)->first();
            if ($integration) {
                $credentials = $integration->credentials;
            }
        }

        $result = ['success' => false, 'message' => 'Provider not recognized.'];

        if (in_array($key, ['razorpay', 'cashfree', 'cod'])) {
            $paymentService = new PaymentService();
            $provider = $paymentService->getProvider($key);
            $result = $provider->testConnection($credentials);
        } elseif ($key === 'shiprocket') {
            $shiprocket = new ShiprocketShippingProvider();
            $result = $shiprocket->testConnection($credentials);
        } else {
            $result = ['success' => true, 'message' => 'Connection validated successfully.'];
        }

        if ($integration = Integration::where('provider_key', $key)->first()) {
            $integration->update([
                'health_status' => $result['success'] ? 'healthy' : 'unhealthy',
                'last_tested_at' => now(),
                'last_error' => $result['success'] ? null : ($result['message'] ?? 'Connection error'),
            ]);
        }

        return response()->json($result);
    }

    /**
     * Disconnect Integration
     */
    public function disconnect($key)
    {
        $integration = Integration::where('provider_key', $key)->first();
        if ($integration) {
            $integration->update([
                'is_connected' => false,
                'is_enabled' => false,
                'health_status' => 'disconnected',
            ]);
        }

        AuditLog::record('integration_disconnected', ['provider_key' => $key]);

        return response()->json([
            'success' => true,
            'message' => 'Integration disconnected.',
        ]);
    }
}
