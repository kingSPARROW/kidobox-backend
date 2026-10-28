<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Merchant;
use App\Models\Store;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Order;
use App\Models\Discount;
use App\Services\TaxService;
use App\Services\Payments\PaymentService;

class CommerceWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (Store::count() === 0) {
            $this->seed(\Database\Seeders\HomzyDemoSeeder::class);
        }
    }
    /**
     * Test Health Check API
     */
    public function test_api_health_check_returns_healthy(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'healthy',
                'platform' => 'HomZy Commerce Engine',
            ]);
    }

    /**
     * Test Public Storefront Data Retrieval
     */
    public function test_storefront_data_returns_theme_and_products(): void
    {
        $response = $this->getJson('/api/v1/storefront/data');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'store' => ['name', 'slug', 'currency'],
                'theme' => ['config'],
                'featured_products',
                'collections',
            ]);
    }

    /**
     * Test TaxService Indian GST Calculation
     */
    public function test_indian_gst_intra_and_inter_state_calculations(): void
    {
        $taxService = new TaxService();

        // Intra-state (Maharashtra to Maharashtra) -> CGST 9% + SGST 9% on ₹1,000 tax-inclusive
        $intra = $taxService->calculateGst(1000.0, 18.0, 'Maharashtra', 'Maharashtra', true);
        $this->assertFalse($intra['is_interstate']);
        $this->assertEquals(847.46, $intra['taxable_amount']);
        $this->assertEquals(76.27, $intra['cgst']);
        $this->assertEquals(76.27, $intra['sgst']);
        $this->assertEquals(0.0, $intra['igst']);
        $this->assertEquals(152.54, $intra['total_tax']);

        // Inter-state (Maharashtra to Karnataka) -> 100% IGST 18%
        $inter = $taxService->calculateGst(1000.0, 18.0, 'Maharashtra', 'Karnataka', true);
        $this->assertTrue($inter['is_interstate']);
        $this->assertEquals(0.0, $inter['cgst']);
        $this->assertEquals(0.0, $inter['sgst']);
        $this->assertEquals(152.54, $inter['igst']);
        $this->assertEquals(152.54, $inter['total_tax']);
    }

    /**
     * Test Checkout Calculation API with Discount & Taxes
     */
    public function test_checkout_calculate_validates_pricing_and_coupons(): void
    {
        $variant = ProductVariant::first();
        if (!$variant) {
            $this->markTestSkipped('No variants in database.');
        }

        $response = $this->postJson('/api/v1/checkout/calculate', [
            'items' => [
                ['variant_id' => (string) $variant->_id, 'quantity' => 1]
            ],
            'discount_code' => 'WELCOME10',
            'shipping_state' => 'Maharashtra',
            'payment_method' => 'razorpay',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'pricing' => [
                    'subtotal',
                    'discount_amount',
                    'shipping_fee',
                    'tax_cgst',
                    'tax_sgst',
                    'total',
                ],
                'items',
            ]);
    }

    /**
     * Test Payment Provider Factory and Capabilities
     */
    public function test_payment_provider_factory_resolves_adapters(): void
    {
        $paymentService = new PaymentService();

        $razorpay = $paymentService->getProvider('razorpay');
        $this->assertEquals('razorpay', $razorpay->getKey());
        $this->assertTrue($razorpay->getCapabilities()['upi']);

        $cod = $paymentService->getProvider('cod');
        $this->assertEquals('cod', $cod->getKey());
        $this->assertTrue($cod->getCapabilities()['cash']);
    }

    /**
     * Test Merchant CSV Product Import & Export
     */
    public function test_merchant_csv_product_import_and_export(): void
    {
        $store = Store::first();
        $merchant = Merchant::first();

        // 1. Test CSV Import
        $csvContent = "Title,Vendor,Product Type,Status,Variant Title,SKU,Price,Compare At Price,Stock,HSN Code,Tax Rate,Tags,Description\n"
                    . "Nordic Oak Desk,Artisan Studio,Furniture,active,Natural Oak,DSK-OAK-01,18500,22000,15,9403,18,\"furniture, desk\",Minimalist desk crafted from oak.";

        $importResponse = $this->actingAs($merchant, 'sanctum')
            ->withHeader('X-Store-Id', (string) $store->_id)
            ->postJson('/api/v1/products/import/csv', [
                'csv_data' => $csvContent,
            ]);

        $importResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'count' => 1,
            ]);

        $this->assertDatabaseHas('products', [
            'title' => 'Nordic Oak Desk',
            'store_id' => (string) $store->_id,
        ]);

        // 2. Test CSV Export
        $exportResponse = $this->actingAs($merchant, 'sanctum')
            ->withHeader('X-Store-Id', (string) $store->_id)
            ->get('/api/v1/products/export/csv');

        $exportResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('content-type'));
    }
}
