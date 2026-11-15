<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Seeder;

class KidoboxAgeStageSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = json_decode(file_get_contents(__DIR__.'/kidobox-catalog.json'), true, flags: JSON_THROW_ON_ERROR);
        $entries = array_merge(...array_column($catalog, 'products'));
        $storeId = (string) Store::where('slug', 'kidobox')->firstOrFail()->getKey();
        $products = Product::where('store_id', $storeId)
            ->whereIn('slug', array_column($entries, 'slug'))
            ->get()
            ->keyBy('slug');

        foreach ($entries as $entry) {
            if (! $products->has($entry['slug'])) {
                throw new \RuntimeException('Missing KIDOBOX product: '.$entry['slug']);
            }
        }

        foreach ($entries as $entry) {
            $product = $products->get($entry['slug']);
            $otherTags = array_filter($product->tags ?? [], fn ($tag) => ! str_starts_with(strtolower($tag), 'age:'));
            $stages = array_map(fn ($stage) => 'age:'.$stage, $entry['age_stages']);
            $product->update(['tags' => array_values(array_unique([...$otherTags, ...$stages]))]);
        }
    }
}
