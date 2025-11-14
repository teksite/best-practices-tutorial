<?php

namespace Modules\Auth\Actions;

use Modules\Auth\Enums\AuthIdentifierType;
use Modules\Auth\Http\Requests\Auth\RegisterRequest;
use Modules\User\Models\User;

class CreateUser
{
    public function handle(RegisterRequest $request) {

        $userData = $request->validated();

        $user=User::query()->create($userData);

        if($request->recipientType == AuthIdentifierType::Email->value) {
            $user->markEmailAsVerified();
        }
        if($request->recipientType == AuthIdentifierType::Phone->value) {
            $user->markPhoneAsVerified();
        }

        return $user;
    }
}
