<?php

namespace Modules\Auth\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Http\Requests\Base\BaseAuthRequest;
use Modules\Auth\Services\VerificationTokenService;

class RegisterRequest extends BaseAuthRequest
{

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'bail|required|string|between:2,100',
            'email' => 'bail|required|string|email|max:100|unique:users,email',
            'phone' => 'bail|required|string|between:1,20|unique:users,phone',
            'password' => 'bail|required|string|confirmed|min:6|max:100',
            'token' => 'bail|required|string|max:255|min:5',
        ];
    }

    public function after(): array
    {
        return array_merge(parent::after(), [
            fn(Validator $validator) => $this->checkToken($validator),
        ]);

    }

}
