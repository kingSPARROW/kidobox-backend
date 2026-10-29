<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\{Store, Merchant, Product, ProductVariant, Discount, Testimonial};
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

class StorefrontIntegrationTest extends TestCase
{
    private string $database;
    protected function setUp(): void
    {
        parent::setUp();
        $this->database = 'kidobox_integration_' . bin2hex(random_bytes(6));
        config(['database.connections.mongodb.database' => $this->database, 'database.connections.mongodb.options' => ['directConnection' => true]]);
        DB::purge('mongodb');
        $store = Store::create(['name' => 'Test Store', 'slug' => 'test', 'status' => 'active']);
        app()->instance('current_store', $store);
        Sanctum::actingAs(Merchant::create(['name' => 'Test Admin', 'email' => 'admin@example.test', 'password' => bcrypt('test-password')]));
    }
    protected function tearDown(): void
    {
        if (isset($this->database) && str_starts_with($this->database, 'kidobox_integration_')) DB::connection('mongodb')->getMongoDB()->drop();
        parent::tearDown();
    }
    public function test_catalog_crud_is_reflected_in_public_storefront(): void
    {
        $c = $this->postJson('/api/v1/collections', ['title' => 'Test category', 'type' => 'manual'])->assertCreated()->json('collection');
        $cid = $c['id'] ?? $c['_id'];
        $p = $this->postJson('/api/v1/products', ['title' => 'Test product', 'status' => 'active', 'images' => ['/old.jpg'], 'collection_ids' => [$cid], 'tags' => ['featured'], 'variants' => [['title' => 'Small', 'price' => 100, 'cost_per_item' => 10, 'inventory_quantity' => 3]]])->assertCreated()->json('product');
        $id = $p['id'] ?? $p['_id'];
        $vid = $p['variants'][0]['id'] ?? $p['variants'][0]['_id'];
        $this->getJson('/api/v1/storefront/data')->assertOk()->assertJsonPath('products.0.title', 'Test product')->assertJsonMissingPath('products.0.variants.0.cost_per_item');
        $this->putJson("/api/v1/products/$id", ['description' => 'Updated', 'images' => ['/new.jpg'], 'variants' => [['id' => $vid, 'title' => 'Small', 'price' => 250, 'inventory_quantity' => 0]]])->assertOk();
        $this->getJson('/api/v1/storefront/products/test-product')->assertOk()->assertJsonPath('product.description', 'Updated')->assertJsonPath('product.images.0', '/new.jpg')->assertJsonPath('product.variants.0.price', 250)->assertJsonPath('product.variants.0.inventory_quantity', 0)->assertJsonPath('product.variants.0.id', $vid);
        $this->getJson('/api/v1/storefront/products?collection=missing')->assertJsonCount(0, 'data');
        $this->putJson("/api/v1/collections/$cid", ['is_published' => false])->assertOk();
        $this->getJson('/api/v1/storefront/data')->assertJsonCount(0, 'collections');
        $this->putJson("/api/v1/products/$id", ['status' => 'draft'])->assertOk();
        $this->getJson('/api/v1/storefront/products/test-product')->assertNotFound();
        $this->getJson('/api/v1/storefront/data')->assertJsonCount(0, 'products');
        $this->deleteJson("/api/v1/products/$id")->assertOk();
        $this->deleteJson("/api/v1/collections/$cid")->assertOk();
        $this->assertSame(0, Product::count());
        $this->assertSame(0, ProductVariant::count());
    }
    public function test_content_schedules_reviews_and_offer_expiry(): void
    {
        $this->putJson('/api/v1/content', ['content' => ['benefits' => [['title' => 'Care', 'subtitle' => 'Daily']]]])->assertOk();
        $b = $this->postJson('/api/v1/banners', ['title' => 'Live banner', 'desktop_image_url' => '/banner.jpg', 'position' => 'hero', 'status' => 'scheduled', 'start_date' => now()->subDay()->toISOString(), 'end_date' => now()->addDay()->toISOString()])->assertCreated()->json('data');
        $bid = $b['id'] ?? $b['_id'];
        $this->getJson('/api/v1/storefront/data')->assertJsonPath('content.benefits.0.title', 'Care')->assertJsonCount(1, 'banners');
        $this->putJson("/api/v1/banners/$bid", ['end_date' => now()->subHour()->toISOString()])->assertOk();
        $this->getJson('/api/v1/storefront/banners')->assertJsonCount(0, 'data');
        $t = $this->postJson('/api/v1/testimonials', ['author_name' => 'Test', 'content' => 'Review', 'rating' => 4, 'status' => 'active', 'is_verified' => false])->assertCreated()->json('data');
        $this->getJson('/api/v1/storefront/data')->assertJsonCount(1, 'testimonials');
        $this->deleteJson('/api/v1/testimonials/' . ($t['id'] ?? $t['_id']))->assertOk();
        $this->getJson('/api/v1/storefront/testimonials')->assertJsonCount(0, 'data');
        $this->assertSame(0, Testimonial::count());
        $d = Discount::create(['code' => 'LIVE', 'type' => 'percentage', 'value' => 10, 'is_active' => true, 'usage_limit' => 1, 'usage_count' => 0]);
        $this->getJson('/api/v1/storefront/data')->assertJsonCount(1, 'offers');
        $d->update(['usage_count' => 1]);
        $this->getJson('/api/v1/storefront/data')->assertJsonCount(0, 'offers');
    }
    public function test_variant_removal_price_sort_and_collection_cleanup(): void
    {
        $c = $this->postJson('/api/v1/collections', ['title' => 'Category', 'type' => 'manual'])->assertCreated()->json('collection');
        $cid = $c['id'] ?? $c['_id'];
        $p = $this->postJson('/api/v1/products', ['title' => 'Variant product', 'status' => 'active', 'collection_ids' => [$cid], 'variants' => [
            ['title' => 'First', 'price' => 50, 'inventory_quantity' => 1], ['title' => 'Second', 'price' => 150, 'inventory_quantity' => 2],
        ]])->assertCreated()->json('product');
        $id = $p['id'] ?? $p['_id'];
        $vid = $p['variants'][1]['id'] ?? $p['variants'][1]['_id'];
        $this->putJson("/api/v1/products/$id", ['variants' => [['id' => $vid, 'title' => 'Second', 'price' => 150, 'inventory_quantity' => 2]]])->assertOk();
        $this->assertSame(1, ProductVariant::where('product_id', $id)->count());
        $this->postJson('/api/v1/products', ['title' => 'Cheaper', 'status' => 'active', 'price' => 100])->assertCreated();
        $this->getJson('/api/v1/storefront/products?sort=price-low-high')->assertJsonPath('data.0.title', 'Cheaper');
        $this->getJson('/api/v1/storefront/products?sort=price-high-low')->assertJsonPath('data.0.title', 'Variant product');
        $this->putJson("/api/v1/collections/$cid", ['title' => 'Updated category', 'image_url' => '/updated.jpg'])->assertOk();
        $this->getJson('/api/v1/storefront/data')->assertJsonPath('collections.0.title', 'Updated category')->assertJsonPath('collections.0.image_url', '/updated.jpg');
        $this->assertSame(1, Product::where("collection_ids", $cid)->count());
        $this->deleteJson("/api/v1/collections/$cid")->assertOk();
        $this->assertSame([], Product::findOrFail($id)->collection_ids);
    }

    public function test_legacy_json_catalog_arrays_are_migrated_idempotently(): void
    {
        $collection = DB::connection('mongodb')->getMongoDB()->selectCollection('products');
        $insert = $collection->insertOne(['title' => 'Legacy', 'tags' => '["featured"]', 'collection_ids' => '["category-id"]']);
        $migration = require database_path('migrations/2026_10_29_120000_normalize_product_catalog_arrays.php');
        $migration->up();
        $migration->up();
        $this->assertSame(1, Product::where('collection_ids', 'category-id')->count());
        $this->assertSame(['featured'], Product::findOrFail((string) $insert->getInsertedId())->tags);
    }

    public function test_invalid_values_are_rejected(): void
    {
        $this->postJson('/api/v1/products', ['title' => 'Invalid', 'status' => 'active', 'variants' => [['title' => 'Default', 'price' => -1, 'inventory_quantity' => -1]]])->assertUnprocessable();
        $this->putJson('/api/v1/content', ['content' => ['articles' => [['minutes' => -1]]]])->assertUnprocessable();
        $this->assertSame(0, Product::count());
    }
}
