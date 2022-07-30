<?php

namespace App\Models;

use App\Traits\HasUuid;
use App\Traits\StoresUserId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTransactionSubCategory extends Model
{
    use HasUuid, HasFactory, StoresUserId;

    public $incrementing = false;
    protected $keyType = 'uuid';
}
