<?php

namespace App\Traits;

use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToStore
{
    /**
     * Boot single store document association.
     */
    protected static function bootBelongsToStore(): void
    {
        // Automatically assign store_id when creating documents
        static::creating(function ($model) {
            if (empty($model->store_id) && app()->bound('current_store')) {
                $store = app('current_store');
                if ($store && !empty($store->_id ?? $store->id)) {
                    $model->store_id = (string) ($store->_id ?? $store->id);
                }
            }
        });
    }

    /**
     * Store relationship
     */
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}
