<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\StoreController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\CollectionController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\DiscountController;
use App\Http\Controllers\Api\V1\ThemeController;
use App\Http\Controllers\Api\V1\IntegrationController;
use App\Http\Controllers\Api\V1\DashboardAnalyticsController;
use App\Http\Controllers\Api\V1\StorefrontController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\BannerController;
use App\Http\Controllers\Api\V1\CustomerAuthController;
use App\Http\Controllers\Api\V1\NavigationController;
use App\Http\Controllers\Api\V1\TestimonialController;

Route::prefix('v1')->group(function () {

    // --- HEALTH CHECK ---
    Route::get('/health', function () {
        return response()->json([
            'status' => 'healthy',
            'platform' => 'HomZy Commerce Engine',
            'version' => '1.0.0',
            'database' => 'MongoDB 7.0 Connected',
            'timestamp' => now()->toISOString(),
        ]);
    });

    // --- STOREFRONT CUSTOMER AUTHENTICATION ---
    Route::prefix('storefront/auth')->group(function () {
        Route::post('/register', [CustomerAuthController::class, 'register']);
        Route::post('/login', [CustomerAuthController::class, 'login']);
        Route::middleware('auth:sanctum')->get('/me', [CustomerAuthController::class, 'me']);
        Route::middleware('auth:sanctum')->post('/logout', [CustomerAuthController::class, 'logout']);
    });

    // --- PUBLIC AUTHENTICATION ---
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // --- PUBLIC STOREFRONT APIS ---
    Route::prefix('storefront')->group(function () {
        Route::get('/data', [StorefrontController::class, 'getStorefrontData']);
        Route::get('/products', [StorefrontController::class, 'getProducts']);
        Route::get('/products/{slug}', [StorefrontController::class, 'getProductBySlug']);
        Route::get('/banners', [BannerController::class, 'storefrontBanners']);
        Route::get('/navigation', [NavigationController::class, 'getStorefrontNavigation']);
        Route::get('/testimonials', [TestimonialController::class, 'storefrontTestimonials']);
        Route::get('/theme', [ThemeController::class, 'show']);
    });

    // --- PUBLIC CHECKOUT & PAYMENTS ---
    Route::prefix('checkout')->group(function () {
        Route::post('/calculate', [CheckoutController::class, 'calculate']);
        Route::post('/order', [CheckoutController::class, 'createOrder']);
        Route::post('/verify-payment', [CheckoutController::class, 'verifyPayment']);
        Route::post('/shipping-rates', [OrderController::class, 'getShippingRates']);
    });

    // --- AUTHENTICATED STORE ADMIN & STAFF ROUTES ---
    Route::middleware('auth:sanctum')->group(function () {
        // Merchant Profile & Store Context
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/store/onboarding', [AuthController::class, 'updateOnboarding']);

        Route::get('/content', [\App\Http\Controllers\Api\V1\ContentController::class, 'show']);
        Route::put('/content', [\App\Http\Controllers\Api\V1\ContentController::class, 'update']);

        // Store Settings
        Route::get('/store', [StoreController::class, 'show']);
        Route::put('/store', [StoreController::class, 'update']);

        // Dashboard Analytics
        Route::get('/dashboard/analytics', [DashboardAnalyticsController::class, 'index']);

        // Products & Inventory
        Route::post('/products/upload', [ProductController::class, 'uploadImage']);
        Route::get('/products/export/csv', [ProductController::class, 'exportCsv']);
        Route::post('/products/import/csv', [ProductController::class, 'importCsv']);
        Route::apiResource('products', ProductController::class);
        Route::post('/variants/{variantId}/adjust-stock', [ProductController::class, 'adjustStock']);

        // Collections
        Route::apiResource('collections', CollectionController::class);

        // Orders & Shipping (Shiprocket)
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::patch('/orders/{id}/status', [OrderController::class, 'updateStatus']);
        Route::post('/orders/{id}/shipment', [OrderController::class, 'createShipment']);
        Route::get('/orders/{id}/tracking', [OrderController::class, 'trackShipment']);
        Route::post('/shipping/rates', [OrderController::class, 'getShippingRates']);

        // Customers
        Route::get('/customers', [CustomerController::class, 'index']);
        Route::get('/customers/{id}', [CustomerController::class, 'show']);
        Route::post('/customers', [CustomerController::class, 'store']);

        // Discounts
        Route::get('/discounts', [DiscountController::class, 'index']);
        Route::post('/discounts', [DiscountController::class, 'store']);
        Route::patch('/discounts/{id}/toggle', [DiscountController::class, 'toggle']);
        Route::delete('/discounts/{id}', [DiscountController::class, 'destroy']);

        // Theme Management
        Route::get('/theme', [ThemeController::class, 'show']);
        Route::put('/theme', [ThemeController::class, 'update']);
        Route::post('/theme/preset', [ThemeController::class, 'applyPreset']);

        // Banner Management
        Route::post('/banners/upload', [BannerController::class, 'uploadImage']);
        Route::apiResource('banners', BannerController::class);

        // Testimonial Management
        Route::patch('/testimonials/{id}/toggle', [TestimonialController::class, 'toggle']);
        Route::apiResource('testimonials', TestimonialController::class);

        // Menus & Footer Management
        Route::get('/navigation', [NavigationController::class, 'index']);
        Route::post('/navigation/menus', [NavigationController::class, 'saveMenu']);
        Route::delete('/navigation/menus/{id}', [NavigationController::class, 'deleteMenu']);
        Route::post('/navigation/footer', [NavigationController::class, 'saveFooter']);

        // Integrations & App Store
        Route::get('/integrations', [IntegrationController::class, 'index']);
        Route::post('/integrations/{key}', [IntegrationController::class, 'save']);
        Route::post('/integrations/{key}/test', [IntegrationController::class, 'test']);
        Route::delete('/integrations/{key}', [IntegrationController::class, 'disconnect']);
    });
});
