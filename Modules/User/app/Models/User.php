<?php

namespace Modules\User\Models;

use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Auth\Interfaces\Auth\MustVerifyPhone;
use Modules\Auth\Traits\MustVerifyPhone as PhoneMethod;

class User extends Authenticatable implements MustVerifyEmail, MustVerifyPhone
{
    use HasFactory, Notifiable, PhoneMethod, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function telegramChats()
    {
        return $this->hasOne(TelegraphChat::class ,'id','telegraph_chat_id',);
    }

    public function makeTelegramRegisterCommand(): ?string
    {
        if ($this->telegraph_chat_id) return null;
        $data = [
            'id' => $this->id,
            'email' => $this->email,
            'timestamp' => now(),
        ];
        $token = encrypt(implode("::", $data));
        return "/login {$token}";
    }
}
