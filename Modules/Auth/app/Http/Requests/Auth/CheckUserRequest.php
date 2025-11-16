<?php

namespace Modules\Auth\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Auth\Http\Requests\Base\BaseAuthRequest;
use Modules\Auth\Rules\UsernameTypeRule;

class CheckUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username' => ['bail', 'required', 'string', new UsernameTypeRule()],
        ];
    }
}
