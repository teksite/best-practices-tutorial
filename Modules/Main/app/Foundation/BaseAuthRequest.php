<?php

namespace Modules\Main\Foundation;

use Modules\Auth\Http\Requests\Afters\AuthDataRequestTrait;
use Modules\Auth\Http\Requests\Afters\PasswordRequestTrait;
use Modules\Auth\Http\Requests\Afters\TokenCodeRequestTrait;
use Modules\Auth\Http\Requests\Afters\VerificationCodeRequestTrait;

class BaseAuthRequest extends ApiFormRequest
{
    use VerificationCodeRequestTrait , TokenCodeRequestTrait , AuthDataRequestTrait ,PasswordRequestTrait;

}
