<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Validation\Validator;
use Modules\Auth\Rules\ContactCheckRule;
use Modules\Main\Foundation\BaseAuthRequest;

class LoginRequest extends BaseAuthRequest
{

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return !auth('sanctum')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'contact'  => ['bail', 'required', 'string', 'min:5', 'max:100', new ContactCheckRule],
            'password' => ['bail', 'required_without:token'],
            'token'    => ['bail', 'required_without:password'],
        ];
    }

    public function after(): array
    {
        return [
            fn(Validator $validator) => $this->appendContactData($validator),
            fn(Validator $validator) => $this->checkPasswordOrVerificationToken($validator),
        ];
    }

    private function checkPasswordOrVerificationToken(Validator $validator): void
    {
        $verificationToken = $this->input('token');
        $password = $this->input('password');

        if (is_null($verificationToken) && $password) {
            $this->verifyPassword($validator);
            return;
        } elseif (is_null($password) && $verificationToken) {
            $this->checkToken($validator);
            return;
        }

        $validator->errors()->add('overall', trans('auth::messages.auth.conflict_password_code_existence'));
        return;
    }

}
