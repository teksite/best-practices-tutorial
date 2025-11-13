<?php

namespace Modules\Auth\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Services\TokenService;

class RegisterRequest extends FormRequest
{
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
            'email' => 'bail|required|string|email|max:100|unique:users',
            'password' => 'bail|required|string|confirmed|min:6',
            'password_confirmation' => 'bail|required|string|same:password',
            'phone' => 'bail|required|string|between:1,20',
            'token' => 'bail|required|string'
        ];
    }

    public function after()
    {
        return [fn(Validator $validator) => $this->checkToken($validator)];
    }

    protected function checkToken(Validator $validator)
    {
        if ($validator->errors()->isNotEmpty()) return;

        $validatedData = $validator->validated();

        $token = $validatedData['token'];
        $email = $validatedData['email'];
        $phone = $validatedData['token'];

        $tokenData = (new TokenService())->getToken([
            $token,
            [
                'email' => $email,
                'phone' => $phone
            ],
            VerificationActionType::Register
        ]);

        if ($tokenData) {
            $validator->errors()->add('token', 'auth::verify.invalid_token');
        }


    }

}
