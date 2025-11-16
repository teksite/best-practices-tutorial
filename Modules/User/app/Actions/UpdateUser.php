<?php

namespace Modules\User\Actions;

use Modules\Auth\Enums\AuthIdentifierType;
use Modules\Auth\Http\Requests\Auth\RegisterRequest;
use Modules\User\Models\User;

class CreateUser
{
    /**
     * @param RegisterRequest $request
     * @return Model|User
     */
    public function handle(RegisterRequest $request): User
    {

        $userData = $request->validated();

        $user = User::query()->create($userData);

        $verifyType = $request->recipientType->value;
        (new MarkVerifyUser())->handle($user, $verifyType);
        return $user;

    }
}
