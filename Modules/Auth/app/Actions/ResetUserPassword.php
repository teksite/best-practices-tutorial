<?php

namespace Modules\Auth\Actions;

use Modules\Auth\Enums\AuthIdentifierType;
use Modules\Auth\Http\Requests\Auth\RegisterRequest;
use Modules\User\Models\User;

class ResetUserPassword
{
    /**
     * @throws \Exception
     */
    public function handle(?User $user, string $password): User
    {
        $user ??= auth('api')->user();

        $user->update(['password' => $password]);

        return $user;

    }
}
