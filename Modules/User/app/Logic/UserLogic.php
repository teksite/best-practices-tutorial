<?php

namespace Modules\User\Logic;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Modules\User\Models\User;

class UserLogic
{
    /**
     * @param array $data
     * @return false|User
     */
    public static function register(array $data): false|User
    {
        try {
            return User::query()->create($data);
        } catch (\Exception $e) {
            Log::error($e);
            return false;
        }

    }

    public function resetPassword(Authenticatable|User $user , string $password): bool
    {
        try {
            $user->update(['password' => $password]);
            return true;
        } catch (\Exception $e) {
            Log::error($e);
            return false;
        }
    }
}
