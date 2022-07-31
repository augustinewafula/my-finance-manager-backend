<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\StoresUserId;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdentifiedTransactionCategory extends Model
{
    use HasUuid, HasFactory, StoresUserId;

    public $incrementing = false;
    protected $keyType = 'uuid';
    protected $fillable = [
        'user_id',
        'subject',
        'transaction_category_id'
    ];

    protected function subject(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => $value,
            set: static fn ($value) => ucwords(strtolower($value)),
        );
    }

    public function transactionCategory(): BelongsTo
    {
        return $this->belongsTo(TransactionCategory::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
