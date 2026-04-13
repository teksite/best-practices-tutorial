<?php

namespace Modules\Auth\Traits;

use Illuminate\Validation\Validator;
use Modules\Auth\Actions\DetectContactType;
use Modules\Auth\Actions\NormalizeContact;
use Modules\Auth\Enums\ContactType;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Services\VerificationTokenService;
use Modules\Auth\Services\VerificationCodeService;
use Modules\User\Models\User;

trait TokenCodeRequestTrait
{
    public function checkToken(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) return;

        $token = $this->input('token');

        $contactType = $this->contactType;
        $contactValue = $this->contactValue;
        $actionType = $this->actionType;

        $tokenService = new VerificationTokenService();

        if (!$tokenService->verify($token, $contactValue, $actionType)) {
            $validator->errors()->add('token', trans('auth::messages.auth.invalid_token'));
            return;
        }


    }
}
