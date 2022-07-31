<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\StoresCreatedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TransactionSubCategory extends Model
{
    use HasUuid, HasFactory, StoresCreatedBy;

    public $incrementing = false;
    protected $keyType = 'uuid';
    protected $fillable = [
        'name',
        'transaction_category_id',
        'created_by',
    ];

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('created_by', null);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
