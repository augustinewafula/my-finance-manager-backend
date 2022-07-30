<?php

namespace App\Traits;

trait StoresCreatedBy
{
    protected static function bootStoresCreatedBy(): void
    {
        static::creating(static function ($model) {
            $model->created_by = auth()->id();
        });
    }

}
