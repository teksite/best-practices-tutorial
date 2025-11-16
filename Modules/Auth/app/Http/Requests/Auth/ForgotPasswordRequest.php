<?php

namespace Modules\Auth\Http\Requests\Auth;

use Illuminate\Validation\Validator;
use Modules\Auth\Http\Requests\Base\BaseAuthRequest;
use Modules\Auth\Rules\UsernameTypeRule;


class ForgotPasswordRequest extends BaseAuthRequest
{

    public function rules(): array
    {
        return [
            'token' => 'bail|required|string|max:255|min:5',
            'password' => 'bail|required|string|confirmed|min:6|max:100',
            'username' => ['bail', 'required', 'string', new UsernameTypeRule()],
        ];
    }

    public function after(): array
    {
        return array_merge(parent::after(), [
            fn(Validator $validator) => $this->findUser($validator),
            fn(Validator $validator) => $this->checkToken($validator),
        ]);
    }

}
