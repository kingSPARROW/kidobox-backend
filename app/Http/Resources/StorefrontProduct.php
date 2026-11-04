<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StorefrontProduct extends JsonResource
{
    public function toArray($request): array
    {
        // Explicit public projection: never expose supplier costs or internal metadata.
        return array_merge($this->only(['title', 'slug', 'description', 'heading_h1', 'heading_h2', 'heading_h3', 'vendor', 'product_type', 'tags', 'images', 'options', 'collection_ids', 'seo', 'canonical_url', 'meta_robots', 'schema_markup']), [
            'id' => (string) $this->getKey(),
            'variants' => $this->variants->map(fn ($variant) => array_merge(
                $variant->only(['title', 'price', 'compare_at_price', 'inventory_quantity', 'options_map', 'image_url']),
                ['id' => (string) $variant->getKey()]
            ))->values()->all(),
        ]);
    }
}
