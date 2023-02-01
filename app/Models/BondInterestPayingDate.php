<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BondInterestPayingDate extends Model
{
    use HasFactory, HasUuid;

    public $incrementing = false;
    protected $keyType = 'uuid';

    protected $fillable = [
        'bond_id',
        'date',
    ];

    protected $dates = [
        'date',
    ];

    public function bond(): BelongsTo
    {
        return $this->belongsTo(Bond::class);
    }
}
