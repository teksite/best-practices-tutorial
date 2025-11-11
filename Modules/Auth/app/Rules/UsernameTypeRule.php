<?php

namespace Modules\Auth\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class UsernameTypeRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $validator = Validator::make(['email' => $value], ['email' => 'email:rfc,dns']);
            if ($validator->fails()) {
                $fail(__('auth::validation.username_invalid_email', ['attribute' => 'email']));
            }
            return;
        } elseif (preg_match('/^\+?[0-9]+$/', $value)) {
            $validator = Validator::make(['phone' => $value], [
                'phone' => ['regex:/^(?:\+?98|0098|0)?9\d{9}$/']
            ]);
            if ($validator->fails()) {
                $fail(__('auth::validation.username_invalid_phone', ['attribute' => 'phone']));
            }
            return;
        }
        $fail(__('auth::validation.username_invalid'));
    }
}
