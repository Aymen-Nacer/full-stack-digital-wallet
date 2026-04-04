<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'full_name',
    ];

    public $timestamps = false;

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            $user->created_at = now();
        });
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }
}
