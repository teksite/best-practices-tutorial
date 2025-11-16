<?php

namespace Modules\Auth\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Auth\Enums\AuthIdentifierType;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Http\Requests\Base\BaseAuthRequest;
use Modules\Auth\Rules\UsernameTypeRule;
use Modules\Auth\Services\VerificationTokenService;

class ChangeUserRequest extends BaseAuthRequest
{

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('sanctum')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'username' => ['bail', 'required', 'string', new UsernameTypeRule(), $this->uniqueUsernameRule()],
            'token' => 'bail|required|string|max:255|min:5',
        ];
    }


    private function uniqueUsernameRule(): \Illuminate\Validation\Rules\Unique
    {
        $column = AuthIdentifierType::getColumn($this->input('username') , true);
        return Rule::unique('users', $column)->ignore($this->user);
    }
    public function after(): array
    {
        return array_merge(parent::after(), [
            fn(Validator $validator) => $this->checkToken($validator),
        ]);

    }

}
