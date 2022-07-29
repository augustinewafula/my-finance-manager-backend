<?php

namespace App\Traits;

trait StoresUserId
{
    protected static function bootStoresUserId(): void
    {
        static::creating(static function ($model) {
            $model->user_id = auth()->id();
        });
    }

}
