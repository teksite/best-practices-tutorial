<?php

namespace Modules\Auth\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Validator;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Enums\AuthIdentifierType;
use Modules\Auth\Rules\UsernameTypeRule;
use Modules\Auth\Services\VerificationCodeService;

class SendVerificationCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (($this->routeIs('api.v1.auth.change') && !auth('sanctum')->check())){
            return false;
        }
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'usernameType' => AuthIdentifierType::detectType($this->input('username', ''))?->value,
        ]);
    }

    public function rules(): array
    {
        $rules = [
            'action' => ['bail', 'required', 'string', new Enum(VerificationActionType::class)],
            'username' => ['bail', 'required', 'string', new UsernameTypeRule(), ...$this->usernameExistenceRule(),],
        ];

        if ($this->isVerificationRoute()) {
            $rules['code'] = ['bail', 'required', 'string'];
        }

        return $rules;
    }

    protected function usernameExistenceRule(): array
    {
        $action = VerificationActionType::detectType($this->input('action', ''));
        $usernameType = $this->input('usernameType');

        if (!$action || !$usernameType) {
            return [];
        }

        $column = AuthIdentifierType::getColumn($usernameType);

        return match ($action) {
            VerificationActionType::Register, VerificationActionType::Change => [Rule::unique('users', $column)],
            default => [Rule::exists('users', $column)],
        };
    }


    public function after(): array
    {
        return [
            fn(Validator $validator) => $this->checkLoginCondition($validator),
            fn(Validator $validator) => $this->checkWaitTime($validator),
        ];
    }

    protected function checkWaitTime(Validator $validator): void
    {

        if ($validator->errors()->isNotEmpty() || $this->isVerificationRoute()) return;

        $recipient = $this->input('username');
        $action = VerificationActionType::from($this->input('action'));

        $service = app(VerificationCodeService::class);

        if ($waitTime = $service->waitTime($recipient, $action)) {
            $validator->errors()->add(
                'action',
                trans('auth::validation.wait_time', ['time' => "{$waitTime}s"])
            );
        }
    }


    protected function isVerificationRoute(): bool
    {
        return $this->routeIs('api.v1.auth.verify-code');
    }

    protected function checkLoginCondition(Validator $validator): void
    {
        $actionType = VerificationActionType::detectType($this->input('action'));
        $error=false;
        $isLoing=auth()->guard('sanctum')->check();
        if ($actionType === VerificationActionType::Verify && !$isLoing) {
            $error=true;
        }

        if (($actionType === VerificationActionType::Register || $actionType === VerificationActionType::Login)  && $isLoing){
            $error=true;

        }
        if ($error) $validator->errors()->add('credential' , __('auth::validation.wrong_action'));

        return;
    }
}
