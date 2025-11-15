<?php

namespace Modules\Auth\Actions;

use Illuminate\Support\Facades\Crypt;
use Modules\Auth\Enums\AuthIdentifierType;
use Modules\Auth\Http\Requests\Auth\RegisterRequest;
use Modules\User\Models\User;

class AuthTokenAction
{
    public function create(User $user): string
    {

        $token = $user->createToken('x_web_token', expiresAt: now()->addDays(30))->plainTextToken;
        return Crypt::encrypt($token);
    }
}
