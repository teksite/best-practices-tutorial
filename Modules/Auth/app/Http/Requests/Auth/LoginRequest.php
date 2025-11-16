<?php

namespace Modules\Auth\Http\Requests\Auth;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Http\Requests\Base\BaseAuthRequest;
use Modules\Auth\Rules\UsernameTypeRule;

class LoginRequest extends BaseAuthRequest
{

    public function rules(): array
    {
        return [
            'password' => ['required_without:token', 'min:8', 'max:255'],
            'token' => ['required_without:password', 'max:255'],
            'username' => ['bail', 'required', 'string', new UsernameTypeRule()],
        ];
    }


    public function after(): array
    {
        return [
            function (Validator $validator) {

                if ($validator->errors()->isNotEmpty()) return;


                $data = $validator->validated();
                $username = $data['username'];
                $this->user = $this->findUser($username, $validator);
                if (!$this->user) return;

                $password = $data['password'] ?? null;
                $token = $data['token'] ?? null;

                if ($password && $token) {
                    $validator->errors()->add('username', __('too many argument'));
                    return;
                }
                if ($password && !Hash::check($password, $this->user->password)) {
                    $validator->errors()->add('username', __('Invalid credentials'));
                    return;
                }

                if ($token && !$password) $this->checkToken($username, $token, VerificationActionType::Login, $validator);
                return;
            }];
    }
}
