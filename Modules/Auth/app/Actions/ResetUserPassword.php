<?php

namespace Modules\Auth\Actions;

use Exception;
use Modules\User\Models\User;

class ResetUserPassword
{
    /**
     * @throws Exception
     */
    public function handle(?User $user, string $password): User
    {
        $user ??= auth('api')->user();

        $user->update(['password' => $password]);

        return $user;

    }
}
