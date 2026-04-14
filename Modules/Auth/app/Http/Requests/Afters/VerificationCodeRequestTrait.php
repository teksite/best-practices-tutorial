<?php

namespace Modules\Auth\Http\Requests\Afters;

use Illuminate\Validation\Validator;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Services\VerificationCodeService;

trait VerificationCodeRequestTrait
{
    /**
     * @param Validator $validator
     * @return void
     */
    protected function checkVerificationCode(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) return;
        $verificationService = new VerificationCodeService();

        if ($verificationService::CODE_LENGTH !== strlen((string)$this->input('code'))) {
            $validator->errors()->add('credentials', trans('auth::messages.verification_code.not_valid'));
        }

        $isValid = $verificationService->verify($this->input('code'), $this->contactValue, VerificationActionType::tryFrom($this->input('action')));

        if (!$isValid) {
            $validator->errors()->add('credentials', trans('auth::messages.verification_code.not_valid'));
            return;
        }

    }

    /**
     * @param Validator $validator
     * @param bool $testing
     * @return void
     */
    protected function getRetryTimeToSendCode(Validator $validator , bool $testing = false): void
    {
        if ($validator->errors()->isNotEmpty()) return;

        $retryTime = (new VerificationCodeService())->getRetryTime($this->contactValue, VerificationActionType::tryFrom($this->input('action')) , testing: $testing);

        if ($retryTime > 0) {
            $validator->errors()->add('credentials', trans('auth::messages.verification_code.wait', ['seconds' => $retryTime]));
            return;
        }
    }
}
