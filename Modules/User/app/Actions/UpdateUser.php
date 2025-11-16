<?php

namespace Modules\User\Actions;

use Illuminate\Database\Eloquent\Model;
use Modules\Auth\Enums\AuthIdentifierType;
use Modules\Auth\Http\Requests\Auth\RegisterRequest;
use Modules\Auth\Http\Requests\Auth\ChangeUserRequest;
use Modules\User\Models\User;

class UpdateUser
{
    /**
     * @param ChangeUserRequest $request
     * @param User|null $user
     * @return User
     */
    public function handle(ChangeUserRequest $request, ?User $user = null): User
    {
        $validated = $request->validated();
        $user ??= auth('sanctum')->user();
        $username = $validated['username'];
        $column = AuthIdentifierType::getColumn($username, true);

        if ($column === 'phone') {
            $user->update([
                'phone' => $username,
                'phone_verified_at' => null,
            ]);
        }

        if ($column === 'email') {
            $user->update([
                'email' => $username,
                'email_verified_at' => null,
            ]);
        }

        return $user;

    }
}
