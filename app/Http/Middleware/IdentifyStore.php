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
        $store = Store::where('status', 'active')->first();

        app()->instance('current_store', $store);
        $request->attributes->set('store', $store);

        return $next($request);
    }
}
