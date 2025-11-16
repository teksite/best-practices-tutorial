<?php

namespace Modules\Auth\Http\Requests\Base;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Modules\Auth\Enums\AuthIdentifierType;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Rules\UsernameTypeRule;
use Modules\Auth\Traits\AuthRequestHelpers;
use Modules\User\Models\User;

class BaseAuthRequest extends FormRequest
{
    use AuthRequestHelpers;

    public ?User $user = null;
    public ?AuthIdentifierType $recipientType = null;
    public ?VerificationActionType $actionType = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->recipientType = AuthIdentifierType::detectType($this->input('username', null));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'username' => ['bail', 'required', 'string', new UsernameTypeRule()],
            'token' => 'bail|required|string|max:255|min:5',

        ];
    }



    public function after(): array
    {
        return [
            fn(Validator $validator) => $this->detectActionType($validator),
            fn(Validator $validator) => $this->checkLoginCondition($validator),
        ];
    }

}
