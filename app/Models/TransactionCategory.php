<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\StoresCreatedBy;

class TransactionCategory extends Model
{
    use HasUuid, HasFactory, StoresCreatedBy;

    public $incrementing = false;
    protected $keyType = 'uuid';
    protected $fillable = [
        'name',
        'created_by',
    ];
}
