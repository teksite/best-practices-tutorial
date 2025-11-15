<?php

namespace Modules\Auth\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;
use Modules\Auth\Enums\AuthIdentifierType;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Rules\UsernameTypeRule;
use Modules\Auth\Services\VerificationTokenService;
use Modules\User\Models\User;

class LoginRequest extends BaseAuthRequest
{

    public function rules(): array
    {
        return [
            'password' => ['required_without:token', 'min:8', 'max:255'],
            'token' => ['required_without:password', 'max:255'],
            'username' => ['bail','required','string', new UsernameTypeRule()],
        ];
    }


    public function after(): array
    {
        return [
            function (Validator $validator) {

            if ($validator->errors()->isNotEmpty()){
                return;
            }

            $data = $validator->validated();
            $username = $data['username'];
            $password = $data['password'] ?? null;
            $token = $data['token'] ?? null;

            $this->user = $this->findUser($username, $validator);
            if (!$this->user) return;

            if ($password && $token){
                return $validator->errors()->add('username', __('too many argument'));

            }
            if ($password && !Hash::check($password, $this->user->password)) {
                return $validator->errors()->add('username', __('Invalid credentials'));
            }

            if ($token && !$password)
                $this->checkToken($username, $token, VerificationActionType::Login, $validator);
        }];
    }
}
