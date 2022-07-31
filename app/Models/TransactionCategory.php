<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\StoresCreatedBy;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransactionCategory extends Model
{
    use HasUuid, HasFactory, StoresCreatedBy;

    public $incrementing = false;
    protected $keyType = 'uuid';
    protected $fillable = [
        'name',
        'created_by',
    ];

    public function scopeDefault(Builder $query): Builder
    {
        return $query->whereCreatedBy(null);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

}
