<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/** Native BSON storage, with read compatibility for legacy JSON-encoded arrays. */
class NativeArray implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): array
    {
        if (is_string($value)) return json_decode($value, true) ?: [];
        return is_array($value) ? $value : [];
    }

    public function set($model, string $key, $value, array $attributes): array
    {
        return [$key => array_values(is_array($value) ? $value : [])];
    }
}
