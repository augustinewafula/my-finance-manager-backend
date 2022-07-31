<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasUuid, HasApiTokens, HasFactory, Notifiable;

    public $incrementing = false;
    protected $keyType = 'uuid';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(MpesaTransaction::class);
    }

    //transaction categories that belongs to user
    public function transactionCategories(): BelongsToMany
    {
        return $this->belongsToMany(TransactionCategory::class);
    }


    public function transactionSubCategories(): BelongsToMany
    {
        return $this->belongsToMany(TransactionSubCategory::class);
    }

    public function identifiedTransactionCategories(): HasMany
    {
        return $this->hasMany(IdentifiedTransactionCategory::class);
    }

}
