<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $products = DB::connection('mongodb')->getMongoDB()->selectCollection('products');
        foreach ($products->find([], ['projection' => ['tags' => 1, 'collection_ids' => 1]]) as $product) {
            $updates = [];
            foreach (['tags', 'collection_ids'] as $field) {
                if (isset($product[$field]) && is_string($product[$field])) {
                    $decoded = json_decode($product[$field], true);
                    if (is_array($decoded)) $updates[$field] = array_values($decoded);
                }
            }
            if ($updates) $products->updateOne(['_id' => $product['_id']], ['$set' => $updates]);
        }
    }

    public function down(): void
    {
        // Native arrays retain all information and are supported by the old read path.
        // Do not reintroduce the broken JSON-string membership queries on rollback.
    }
};
