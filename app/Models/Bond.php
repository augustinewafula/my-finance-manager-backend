<?php

namespace App\Models;

use App\Traits\CurrentUser;
use App\Traits\HasUuid;
use App\Traits\StoresUserId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bond extends Model
{
    use HasFactory, HasUuid, StoresUserId, CurrentUser;

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'user_id',
        'issue_number',
        'coupon_rate',
        'amount_invested'
    ];

    public function interestPayingDates(): HasMany
    {
        return $this->hasMany(BondInterestPayingDate::class);
    }
}
