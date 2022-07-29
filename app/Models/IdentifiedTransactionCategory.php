<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\StoresUserId;
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

    public function transactionCategory(): BelongsTo
    {
        return $this->belongsTo(TransactionCategory::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
