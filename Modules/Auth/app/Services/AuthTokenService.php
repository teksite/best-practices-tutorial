<?php

namespace Modules\Auth\Services;


use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Crypt;
use Modules\User\Models\User;

class AuthTokenService
{
    const string PREFIX = 'x_web_token';
    const bool ENCRYPTING_TOKEN = true;

    public function create(User|Authenticatable $user): string
    {
        $token = $user->createToken(self::PREFIX, expiresAt: now()->addDays(28))->plainTextToken;
        return self::ENCRYPTING_TOKEN ? Crypt::encrypt($token) : $token;
    }

}

