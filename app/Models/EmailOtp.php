<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

class EmailOtp extends Model
{
    protected $table = 'email_otps';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'otp_code',
        'email',
        'expired_at',
        'id'
    ];
}
