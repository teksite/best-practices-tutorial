<?php

namespace Modules\Auth\Services;


use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Crypt;
use Modules\Auth\Models\PersonalAccessToken;
use Modules\User\Models\User;

class AuthTokenService
{
    const string PREFIX = 'x_web_token';
    const string CLIENT_TOKEN_PREFIX = "Bearer ";
    const bool ENCRYPTING_TOKEN = true;

    /**
     * @param User|Authenticatable $user
     * @return string
     */
    public function create(User|Authenticatable $user): string
    {
        $token = $user->createToken(self::PREFIX, expiresAt: now()->addDays(28))->plainTextToken;
        return self::ENCRYPTING_TOKEN ? Crypt::encrypt($token) : $token;
    }


    /**
     * @param string|null $token
     * @return void
     */
    public function delete(?string $token): void
    {
        $token = PersonalAccessToken::findToken($token);
        $token->delete();
    }

    /**
     * @param Authenticatable|User $user
     * @return void
     */
    public function flush(Authenticatable|User $user): void
    {
        $user->tokens()->delete();
    }


    /**
     * @param bool $extracted
     * @return string
     */
    public function getCurrentToken(bool $extracted = true): string
    {
        $authorizationToken = request()->header('authorization') ?? request()->cookies->get(AuthTokenService::PREFIX);

        return $extracted
            ? str_replace(self::CLIENT_TOKEN_PREFIX, '', $authorizationToken)
            : $authorizationToken;
    }
}

