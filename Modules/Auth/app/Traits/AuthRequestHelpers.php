<?php

namespace Modules\Auth\Traits;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;
use Modules\Auth\Enums\AuthIdentifierType;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Services\VerificationTokenService;
use Modules\User\Models\User;

trait AuthRequestHelpers
{
    /**
     * @param string $username
     * @return AuthIdentifierType
     */
    protected function detectRecipientType(string $username): AuthIdentifierType
    {
        return AuthIdentifierType::detectType($username);
    }

    /**
     * @param Validator $validator
     * @return void
     */
    protected function selectLoginType(Validator $validator): void
    {
        $validated = $validator->validated();

        $password = $validated['password'] ?? null;
        $token = $validated['token'] ?? null;
        if ($password && $token) {
            $validator->errors()->add('username', __('auth::validation.too many arguments'));
            return;
        }
        if ($password) {
            $this->findByPassword($validator);
            return;
        }
        if ($token) {
            $this->checkToken($validator);
            return;
        }
    }


    /**
     * @param Validator $validator
     * @return void
     */
    protected function findUser(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) return;

        $validated = $validator->validated();
        $username = $validated['username'];
        $column = AuthIdentifierType::getColumn($username, true);
        if (is_null($column)) {
            $validator->errors()->add('username', __('Invalid credentials recognization.'));
            return;
        };

        $user = User::query()->where($column, $username)->first();
        if (!$user) {
            $validator->errors()->add('username', __('Invalid credentials'));
            return;
        }
        $this->user = $user;
    }

    /**
     * @param Validator $validator
     * @return void
     */
    protected function findByPassword(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) return;
        $validated = $validator->validated();
        $this->findUser($validator);

        if ($validator->errors()->isNotEmpty()) return;
        $password = $validated['password'];
        if ($password && !Hash::check($password, $this->user->password)) {
            $validator->errors()->add('username', __('Invalid credentials'));
            $this->user = null;
        }

        return;
    }

    /**
     * @param Validator $validator
     */
    protected function checkToken(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) return;
        $validated = $validator->validated();
        $username = $validated['username'] ?? null;
        $token = $validated['token'];
        $identityParams = [$this->userAgent(), $this->ip()];
        $service = new VerificationTokenService();
        $actionType = $this->actionType;

        if (is_null($token)) {
            $validator->errors()->add('token', __('auth::validation.invalid_token'));
            return;

        };
        if (is_null($actionType)) {
            $validator->errors()->add('username', __('auth::validation.wrong_action'));
            return;
        }

        $recipients = [
            'phone' => $this->recipientType === AuthIdentifierType::Phone ? $username : ($validated['phone'] ?? null),
            'email' => $this->recipientType === AuthIdentifierType::Email ? $username : ($validated['email'] ?? null),
        ];
        if (is_null($recipients['phone']) && is_null($recipients['email'])) {
            $validator->errors()->add('username', __('auth::validation.username_invalid'));
            return;
        }

        $tokenData = $service->getCheckedToken($token, $recipients, $actionType, $identityParams);

        if (!$tokenData || !is_array($tokenData) || count($tokenData) < 1) {
            $validator->errors()->add('token', __('auth::validation.invalid_token'));
            return;
        }
    }


    /**
     * @param Validator $validator
     * @return void
     */
    protected function checkLoginCondition(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) return;
        $actionType = $this->actionType;
        $isLogin = auth()->guard('sanctum')->check();

        if (
            (in_array($actionType, [VerificationActionType::Verify, VerificationActionType::Change]) && !$isLogin) ||
            (in_array($actionType, [VerificationActionType::Register, VerificationActionType::Login]) && $isLogin)
        ) {
            $validator->errors()->add('credential', __('auth::validation.wrong_action'));
            return;
        }

    }

    /**
     * @param Validator $validator
     * @return void
     */
    protected function detectActionType(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) return;

        $action = $this->input('action', null);
        $type = null;
        if ($action) {
            $type = VerificationActionType::detectType($action);
        } else {
            $type = match (true) {
                $this->routeIs('api.v1.auth.register') => VerificationActionType::Register,
                $this->routeIs('api.v1.auth.login') => VerificationActionType::Login,
                $this->routeIs('api.v1.auth.forget-password') => VerificationActionType::Forget,
                $this->routeIs('api.v1.auth.verify') => VerificationActionType::Verify,
                $this->routeIs('api.v1.auth.change') => VerificationActionType::Change,
                default => null,
            };
        }
        if (is_null($type)) {
            $validator->errors()->add('username', __('auth::validation.wrong_action'));
            return;
        }
        $this->actionType = $type;

    }

    /**
     * @param Validator $validator
     * @return void
     */
    public function detectColumnNull(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) return;
        $this->user = auth()->guard('sanctum')->user();
        $validated = $validator->validated();
        $phone = $validated['phone'] ?? null;
        $email = $validated['email'] ?? null;

        if (is_null($phone) && is_null($email)) {
            $validator->errors()->add('credential', __('auth::validation.verify_empty'));
        }

        if ($phone) {
            if (!is_null($this->user->phone_verified_at)) {
                $validator->errors()->add('phone', __('auth::validation.verified_before', ['attribute' => 'phone']));
            }
        }
        if ($email) {
            if (!is_null($this->user->email_verified_at)) {
                $validator->errors()->add('email', __('auth::validation.verified_before', ['attribute' => 'email']));
            }
        }


    }
}
