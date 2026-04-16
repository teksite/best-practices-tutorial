<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Rules\ContactCheckRule;
use Modules\Main\Foundation\BaseAuthRequest;


class SendVerificationCodeRequest extends BaseAuthRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'contact' => ['bail', 'required', 'string', 'min:5', 'max:100', new ContactCheckRule],
            'action'   => ['bail', 'required', 'string', Rule::enum(VerificationActionType::class)],
        ];
    }

    public function after(): array
    {
        return [
            fn(Validator $validator) => $this->appendContactData($validator),
            fn(Validator $validator) => $this->checkExistenceContactCondition($validator),
            fn(Validator $validator) => $this->getRetryTimeToSendCode($validator),
            fn(Validator $validator) => $this->checkIfContactIsNull($validator),

        ];
    }



}
