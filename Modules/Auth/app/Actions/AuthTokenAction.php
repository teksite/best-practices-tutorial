<?php

namespace Modules\Auth\Actions;

use Illuminate\Support\Facades\Crypt;
use Modules\Auth\Enums\AuthIdentifierType;
use Modules\Auth\Http\Requests\Auth\RegisterRequest;
use Modules\User\Models\User;

class AuthTokenAction
{
    public function create(User $user ,string $tokenName='x_web_token' , int $days =30 ,bool $encryption=true): string
    {

        $token = $user->createToken($tokenName, expiresAt: now()->addDays($days))->plainTextToken;
        return $encryption ? Crypt::encrypt($token) : $token;
    }
}
