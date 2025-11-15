<?php

namespace Modules\Auth\Http\Requests\Auth;

use Modules\Auth\Http\Requests\Base\BaseAuthRequest;
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
