<?php

namespace Modules\User\Actions;

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
