<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\StoresUserId;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpesaTransaction extends Model
{
    use HasUuid, HasFactory, StoresUserId;

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
}
