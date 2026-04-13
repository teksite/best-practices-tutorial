<?php

namespace Modules\Main\Foundation;

use Modules\Auth\Traits\TokenCodeRequestTrait;
use Modules\Auth\Traits\UserAuthRequestTrait;
use Modules\Auth\Traits\VerificationCodeRequestTrait;
use Modules\Main\Foundation\ApiFormRequest;

class BaseAuthRequest extends ApiFormRequest
{
    use VerificationCodeRequestTrait , TokenCodeRequestTrait , UserAuthRequestTrait;

}
