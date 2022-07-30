<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionSubCategory extends Model
{
    use HasUuid, HasFactory;

    public $incrementing = false;
    protected $keyType = 'uuid';
    protected $fillable = [
        'name',
        'transaction_category_id',
        'created_by',
    ];
}
