<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class KidoboxCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = json_decode(
            file_get_contents(__DIR__.'/kidobox-catalog.json'),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $storeId = (string) Store::where('slug', 'kidobox')->firstOrFail()->getKey();
        $media = Storage::disk('public');

        foreach ($catalog as $category) {
            foreach ($category['products'] as $entry) {
                foreach ($entry['images'] as $path) {
                    if (! $media->exists($path) && ! is_file(__DIR__.'/'.$path)) {
                        throw new \RuntimeException("Missing catalog image: {$path}");
                    }
                }
            }
        }

        foreach ($catalog as $category) {
            $collection = Collection::firstOrCreate(
                ['store_id' => $storeId, 'slug' => $category['slug']],
                ['title' => $category['name'], 'type' => 'manual', 'is_published' => false],
            );
            $collectionId = (string) $collection->getKey();

            foreach ($category['products'] as $entry) {
                $product = Product::firstOrNew(['store_id' => $storeId, 'slug' => $entry['slug']]);
                $product->fill([
                    'title' => $entry['title'],
                    'description' => $entry['description'],
                    'vendor' => $entry['vendor'],
                    'product_type' => $category['name'],
                    'collection_ids' => array_values(array_unique([
                        ...($product->collection_ids ?? []), $collectionId,
                    ])),
                ]);

                if (! $product->exists) {
                    $product->fill(['status' => 'draft', 'has_variants' => false]);
                }
                if ($entry['images'] !== []) {
                    foreach ($entry['images'] as $path) {
                        if (! $media->exists($path) && ! $media->put($path, file_get_contents(__DIR__.'/'.$path))) {
                            throw new \RuntimeException("Could not store catalog image: {$path}");
                        }
                    }
                    $product->images = array_map(fn ($path) => $media->url($path), $entry['images']);
                }
                $product->save();

                $productId = (string) $product->getKey();
                $variant = ProductVariant::where('product_id', $productId)->oldest()->first()
                    ?? new ProductVariant([
                        'store_id' => $storeId,
                        'product_id' => $productId,
                        'sku' => 'KDB-'.strtoupper($category['slug']).'-'.$entry['number'],
                        'title' => 'Default Title',
                        'inventory_quantity' => 0,
                    ]);
                // Once a product goes live, pricing belongs to the merchant.
                if ($product->status === 'draft') {
                    $variant->price = $entry['price'];
                    $variant->compare_at_price = $entry['mrp'] > $entry['price'] ? $entry['mrp'] : null;
                }
                $variant->save();
            }
        }
    }
}
