<?php

namespace Modules\Auth\Http\Requests\Afters;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

trait PasswordRequestTrait
{
    /**
     * @param Validator $validator
     * @return void
     */
    protected function verifyPassword(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) return;

        $credentials =[
            $this->contactType->value =>$this->contactValue,
            'password'=>$this->input('password')
        ];

        if (!Auth::once($credentials)){
            $validator->errors()->add('credentials', trans('auth::messages.auth.credentials'));
            return;
        }


    }

}
