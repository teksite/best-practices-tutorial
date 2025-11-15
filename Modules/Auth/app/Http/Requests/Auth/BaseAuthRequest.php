<?php

namespace Modules\Auth\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Modules\Auth\Enums\AuthIdentifierType;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Rules\UsernameTypeRule;
use Modules\Auth\Services\VerificationTokenService;
use Modules\User\Models\User;

class BaseAuthRequest extends FormRequest
{
    public ?User $user = null;
    public ?AuthIdentifierType $recipientType = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'username' => ['bail', 'required', 'string', new UsernameTypeRule()],
        ];
    }

    /**
     * @param string $username
     * @return AuthIdentifierType
     */
    protected function detectRecipientType(string $username): AuthIdentifierType
    {
        return AuthIdentifierType::detectType($username);
    }

    /**
     * @param string $username
     * @param Validator $validator
     * @return User|null
     */
    protected function findUser(string $username, Validator $validator): ?User
    {
        $column = AuthIdentifierType::getColumn($username, true);
        if (is_null($column)) {
            $validator->errors()->add('username', __('Invalid credentials recognization.'));
            return null;
        };

        $user = User::query()->where($column, $username)->first();
        if (!$user) {
            $validator->errors()->add('username', __('Invalid credentials'));
            return null;
        }

        return $user;
    }


    /**
     * @param string $username
     * @param string $token
     * @param VerificationActionType|string $action
     * @param Validator $validator
     */
    protected function checkToken(string $username, string $token, VerificationActionType|string $action, Validator $validator): void
    {
        $type = AuthIdentifierType::detectType($username);
        $action = $action instanceof VerificationActionType ? $action : VerificationActionType::detectType($action);
        $service = new VerificationTokenService();
        $tokenData = $service->getToken(
            $token,
            [$type->value => $username],
            $action,
            [$this->userAgent(), $this->ip()]
        );
        if (!$tokenData || !is_array($tokenData)  || count($tokenData) < 1) {
            $validator->errors()->add('token', __('auth::validation.invalid_token'));
            return;
        }
        $this->recipientType = AuthIdentifierType::detectType($tokenData['recipientType'] ?? null);
    }


}
