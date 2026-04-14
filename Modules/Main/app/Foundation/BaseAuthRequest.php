<?php

namespace Modules\Main\Foundation;

use Modules\Auth\Traits\PasswordRequestTrait;
use Modules\Auth\Traits\TokenCodeRequestTrait;
use Modules\Auth\Traits\AuthDataRequestTrait;
use Modules\Auth\Traits\VerificationCodeRequestTrait;
use Modules\Main\Foundation\ApiFormRequest;

class BaseAuthRequest extends ApiFormRequest
{
    use VerificationCodeRequestTrait , TokenCodeRequestTrait , AuthDataRequestTrait ,PasswordRequestTrait;

}
