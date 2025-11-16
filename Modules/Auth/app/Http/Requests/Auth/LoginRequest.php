<?php

namespace Modules\Auth\Http\Requests\Auth;

use Illuminate\Validation\Validator;
use Modules\Auth\Http\Requests\Base\BaseAuthRequest;
use Modules\Auth\Rules\UsernameTypeRule;

class LoginRequest extends BaseAuthRequest
{

    public function rules(): array
    {
        return [
            'password' => ['required_without:token', 'min:8', 'max:255'],
            'token' => ['bail', 'required_without:password', 'string', 'max:255', 'min:5'],
            'username' => ['bail', 'required', 'string', new UsernameTypeRule()],
        ];
    }


    public function after(): array
    {
        return array_merge(parent::after(), [
            fn(Validator $validator) => $this->selectLoginType($validator),
            fn(Validator $validator) => $this->findUser($validator),
        ]);

    }

}
