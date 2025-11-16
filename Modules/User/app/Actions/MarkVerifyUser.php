<?php

namespace Modules\User\Actions;

use Modules\Auth\Enums\AuthIdentifierType;
use Modules\User\Models\User;

class MarkVerifyUser
{
    public function handle(User $user ,string $type): User
    {

        if($type === 'email') {
            $user->markEmailAsVerified();
        }
        if($type === 'phone') {
            $user->markPhoneAsVerified();
        }

        return $user;
    }
}
