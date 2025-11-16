<?php

namespace Modules\Auth\Enums;

use Illuminate\Validation\Rule;
use phpDocumentor\Reflection\Types\Self_;

enum VerificationActionType: string
{
    case Register = 'register';
    case Login = 'login';
    case Forget = 'forget';
    case Verify = 'verify';
    case Change = 'change';

    public static function detectType(string $input): ?self
    {
        return self::tryFrom(strtolower(trim($input)));
    }


}
