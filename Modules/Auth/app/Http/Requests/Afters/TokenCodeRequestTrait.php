<?php

namespace Modules\Auth\Http\Requests\Afters;

use Illuminate\Validation\Validator;
use Modules\Auth\Services\VerificationTokenService;

trait TokenCodeRequestTrait
{
    /**
     * @param Validator $validator
     * @return void
     */
    public function checkToken(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) return;

        $token = $this->input('token');

        $contactType = $this->contactType;
        $contactValue = $this->contactValue;
        $actionType = $this->actionType;

        $tokenService = new VerificationTokenService();
        if (!$tokenService->verify($token, $contactValue, $actionType)) {
            $validator->errors()->add('credentials', trans('auth::messages.auth.invalid_token'));
            return;
        }


    }
}
