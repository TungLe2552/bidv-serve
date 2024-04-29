<?php

namespace App\Models\Auth;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Foundation\Auth\User as Authenticatable;

class BankCard extends Authenticatable
{
    protected $table = 'bank_cards';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'mount',
        'limit',
        'code',
        'count_false_pin',
        'count_false_otp',
        'active'
    ];
    protected $casts = [
        'active' => 'boolean',
    ];
    protected static function booted()
    {
        static::updating(function ($bank) {
            if ($bank->count_false_pin >= 3 || $bank->count_false_otp) {
                $bank->active = false;
            }
        });
    }
}
