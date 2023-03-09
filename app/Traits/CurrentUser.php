<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

trait CurrentUser
{
    public function scopeCurrentUser(Builder $query): Builder
    {
        if (Schema::hasColumn($this->getTable(), 'user_id')) {
            return $query->where($this->getTable() . '.user_id', auth()->id());
        }

        return $query->where($this->getTable() . '.created_by', auth()->id());
    }

}
