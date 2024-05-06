<?php

namespace App\Models\Auth;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\HasSetLayer;
use App\Traits\UserHasApplicationTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Storage;
use Validator;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, UserHasApplicationTrait;
    use HasSetLayer;
    protected $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'email',
        'password',
        'user_name',
        'active',
        'face_id',
        'count_false',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'face_id',
        'email',
        'count_false'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
        'password' => 'hashed',
    ];
    public function bankCard()
    {
        return $this->hasOne(BankCard::class, 'user_id');
    }
    public function partner()
    {
        return $this->hasOne(Partner::class, 'user_id');
    }
    protected static function booted()
    {
        static::updating(function ($user) {
            // nếu count_false> 3 thì cập nhật lại trạng thái của tài khoản thành false (bị khoá)
            if ($user->count_false >= 3) {
                $user->active = false;
            }
        });
    }
}
