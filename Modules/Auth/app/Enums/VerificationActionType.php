<?php

namespace Modules\Auth\Enums;

use Illuminate\Validation\Rule;
use phpDocumentor\Reflection\Types\Self_;

enum VerificationActionType: string
{
    case Register = 'register';
    case Login = 'login';

    public static function detectType(string $input): VerificationActionType
    {
        if ($input === self::Register->value) {
            return self::Register;
        }
        return self::Login;
    }


}
