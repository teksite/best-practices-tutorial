<?php

namespace Modules\Auth\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Services\VerificationTokenService;

class RegisterRequest extends FormRequest
{

    public ?string $recipientType = null;

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
            'name' => 'bail|required|string|between:2,100',
            'email' => 'bail|required|string|email|max:100|unique:users,email',
            'phone' => 'bail|required|string|between:1,20|unique:users,phone',
            'password' => 'bail|required|string|confirmed|min:6',
            'token' => 'bail|required|string'
        ];
    }

    public function after(): array
    {
        return [fn(Validator $validator) => $this->checkToken($validator)];
    }

    protected function checkToken(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) return;

        $validatedData = $validator->validated();

        $token = $validatedData['token'];
        $email = $validatedData['email'];
        $phone = $validatedData['phone'];

        $tokenData = (new VerificationTokenService())->getToken(
            $token,
            ['email' => $email, 'phone' => $phone],
            VerificationActionType::Register,
            [$this->userAgent(), $this->ip()]

        );

        if (!$tokenData) {
            $validator->errors()->add('token', __('auth::validation.invalid_token'));
            return;
        }

        $this->recipientType = $tokenData['recipientType'];


    }

}
