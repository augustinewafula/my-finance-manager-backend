<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\StoresCreatedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    protected function name(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => $value,
            set: static fn ($value) => ucfirst(strtolower($value)),
        );
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->whereCreatedBy(null);
    }

    public function transactionCategory(): BelongsTo
    {
        return $this->belongsTo(TransactionCategory::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
