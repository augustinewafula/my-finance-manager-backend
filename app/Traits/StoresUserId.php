<?php

namespace App\Traits;

trait StoresUserId
{
    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function ($model) {
            $model->user_id = auth()->id();
        });
    }

}
