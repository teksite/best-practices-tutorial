<?php

namespace Modules\Auth\Traits;

use Illuminate\Validation\Validator;
use Modules\Auth\Actions\DetectContactType;
use Modules\Auth\Actions\NormalizeContact;
use Modules\Auth\Enums\ContactType;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Services\VerificationCodeService;
use Modules\User\Models\User;

trait VerificationCodeRequestTrait
{
    /**
     * @param Validator $validator
     * @return void
     */
    protected function checkCode(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) return;
        $verificationService = new VerificationCodeService();

        if ($verificationService::CODE_LENGTH !== strlen((string)$this->input('code'))) {
            $validator->errors()->add('contact', trans('auth::messages.verification_code.not_valid'));
        }

        $isValid = $verificationService->verify($this->input('code'), $this->contactValue, VerificationActionType::tryFrom($this->input('action')));

        if (!$isValid) {
            $validator->errors()->add('contact', trans('auth::messages.verification_code.not_valid'));
            return;
        }

    }

    /**
     * @param Validator $validator
     * @return void
     */
    protected function getRetryTime(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) return;

        $retryTime = (new VerificationCodeService())->getRetryTime($this->contactValue, VerificationActionType::tryFrom($this->input('action')));

        if ($retryTime > 0) {
            $validator->errors()->add('time', trans('auth::messages.verification_code.wait', ['seconds' => $retryTime]));
            return;
        }
    }
}
