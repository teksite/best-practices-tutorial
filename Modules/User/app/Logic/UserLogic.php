<?php

namespace Modules\User\Logic;

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
}
