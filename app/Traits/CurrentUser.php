<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait CurrentUser
{
    public function scopeCurrentUser(Builder $query): Builder
    {
        return $query->where($this->getTable() . 'user_id', auth()->id());
    }

}
