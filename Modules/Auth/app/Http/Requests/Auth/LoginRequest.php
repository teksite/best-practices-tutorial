<?php

namespace Modules\Auth\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;
use Modules\Auth\Enums\AuthIdentifierType;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Services\VerificationTokenService;
use Modules\User\Models\User;

class LoginRequest extends FormRequest
{
    public ?User $user = null;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            $this->baseRules(),
            $this->usernameRules()
        );
    }

    protected function baseRules(): array
    {
        return [
            'password' => ['required_without:token', 'min:8', 'max:255'],
            'token'    => ['required_without:password', 'max:255'],
        ];
    }

    protected function usernameRules(): array
    {
        $username = $this->get('username' ,null);

        return [
            'username' => match (AuthIdentifierType::detectType($username)) {
                AuthIdentifierType::Email => 'bail|required|string|email:rfc,dns',
                AuthIdentifierType::Phone => 'bail|required|string|between:8,15',
                default => [function ($attribute, $value, $fail) {
                    $fail('This username is unrecognized.');
                }]
            }
        ];
    }

    public function after(): array
    {
        return [fn(Validator $validator) => $this->validateUser($validator)];
    }

    protected function validateUser(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) return;

        $validated = $validator->validated();
        $username  = $validated['username'];
        $password  = $validated['password'] ?? null;
        $token     = $validated['token'] ?? null;


        $this->user = $this->findUser($username, $validator);
        if (!$this->user) return;


        if ($password) {
            $this->validatePassword($password, $validator);
        }

        if (!$password && $token) {
            $this->validateToken($username, $token, $validator);
        }
    }

    protected function findUser(string $username, Validator $validator): ?User
    {
        $column = AuthIdentifierType::getColumn($username, true);
        $user = User::query()->where($column, $username)->first();

        if (!$user) {
            $validator->errors()->add('username', __('Invalid credentials'));
            return null;
        }

        return $user;
    }
    protected function validatePassword(string $password, Validator $validator): void
    {
        if (!Hash::check($password, $this->user->password)) {
            $validator->errors()->add('username', __('Invalid credentials (username or password)'));
        }
    }

    protected function validateToken(string $username, string $token, Validator $validator): void
    {
        $usernameType = AuthIdentifierType::detectType($username);

        $tokenService = new VerificationTokenService();
        $tokenData = $tokenService->getToken(
            $token,
            [$usernameType->value => $username],
            VerificationActionType::Login,
            [$this->userAgent(), $this->ip()]
        );

        if (!$tokenData) {
            $validator->errors()->add('token', __('auth::validation.invalid_token'));
        }
    }

}
