<?php

namespace App\Models;

use App\Traits\StoresUserId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpesaTransaction extends Model
{
    use HasFactory, StoresUserId;

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = [
        'user_id',
        'reference_code',
        'message',
        'type',
        'amount',
        'subject',
        'transaction_category_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactionCategory(): BelongsTo
    {
        return $this->belongsTo(TransactionCategory::class);
    }
}
