<?php

namespace App\Models;

use App\Traits\CurrentUser;
use App\Traits\HasUuid;
use App\Traits\StoresUserId;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasUuid, HasFactory, StoresUserId, CurrentUser;

    public $incrementing = false;
    protected $keyType = 'uuid';
    protected $dateFormat = 'Y-m-d H:i:s.u';
    protected $fillable = [
        'user_id',
        'reference_code',
        'message',
        'type',
        'amount',
        'subject',
        'transaction_category_id',
        'transaction_sub_category_id',
        'date',
        'transaction_cost',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    protected function subject(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => $value,
            set: static fn ($value) => ucwords(strtolower($value)),
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactionCategory(): BelongsTo
    {
        return $this->belongsTo(TransactionCategory::class);
    }

    public function transactionSubCategory(): BelongsTo
    {
        return $this->belongsTo(TransactionSubCategory::class);
    }
}
