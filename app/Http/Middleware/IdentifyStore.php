<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Store;
use Symfony\Component\HttpFoundation\Response;

class IdentifyStore
{
    /**
     * Handle an incoming request and bind tenant store context.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Single Store Architecture: Always resolve the single store
        $store = Store::first();

        if (!$store) {
            $store = Store::create([
                'name' => 'Aura Living',
                'slug' => 'aura-living',
                'currency' => 'INR',
                'timezone' => 'Asia/Kolkata',
                'status' => 'active',
                'settings' => [
                    'tax_mode' => 'inclusive',
                    'default_gst_rate' => 18,
                    'allow_cod' => true,
                    'cod_fee' => 50,
                    'free_shipping_threshold' => 999,
                ],
                'contact' => [
                    'email' => 'contact@auraliving.in',
                    'phone' => '+91 98200 12345',
                    'address' => 'Plot 42, Artisan Industrial Park',
                    'city' => 'Mumbai',
                    'state' => 'Maharashtra',
                    'postal_code' => '400018',
                    'country' => 'India',
                ],
            ]);
        }

        app()->instance('current_store', $store);
        $request->attributes->set('store', $store);

        return $next($request);
    }
}
