<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StorefrontProduct extends JsonResource
{
    public function toArray($request): array
    {
        // Explicit public projection: never expose supplier costs or internal metadata.
        return array_merge($this->only(['title', 'slug', 'description', 'vendor', 'product_type', 'tags', 'images', 'options', 'collection_ids', 'seo']), [
            'id' => (string) $this->getKey(),
            'variants' => $this->variants->map(fn ($variant) => array_merge(
                $variant->only(['title', 'price', 'compare_at_price', 'inventory_quantity', 'options_map', 'image_url']),
                ['id' => (string) $variant->getKey()]
            ))->values()->all(),
        ]);
    }
}
