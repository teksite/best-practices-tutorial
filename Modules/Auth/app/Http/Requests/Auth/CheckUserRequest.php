<?php

namespace Modules\Auth\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;
use Modules\Auth\Rules\UsernameTypeRule;

class CheckUserRequest extends BaseAuthRequest
{
    public function rules(): array
    {
        return [
            'username' => ['bail', 'required', 'string', new UsernameTypeRule()],
        ];
    }
}
