<?php

namespace App\Models\Auth;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Model;

class UserMaxTransaction extends Model
{
    protected $table = 'user_max_transactions';

    protected $fillable = [
        'user_id',
        'transaction_type',
        'max_value'
    ];
}
