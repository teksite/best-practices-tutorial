<?php

namespace Modules\Auth\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Modules\Auth\Actions\DetectContactType;
use Modules\Auth\Enums\ContactType;

class ContactCheckRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $usernameType = DetectContactType::handle($value);
        if (is_null($usernameType)) {
            $fail(trans('auth::messages.auth.usernameType'));
            return;
        }
        #TODO uncomment it in production mode
        if ($usernameType === ContactType::EMAIL) {
//            $validator = Validator::make([$attribute => $value], [
//                $attribute => 'email:rfc,dns'
//            ]);
//            if ($validator->fails()) {
//                $fail(__('validation.email', ['attribute' => $attribute]));
//            }
        }


    }
}
