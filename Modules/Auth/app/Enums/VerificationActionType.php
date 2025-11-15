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

    public static function detectType(string $input): ?VerificationActionType
    {
        return match ($input) {
            self::Register->value => self::Register,
            self::Login->value => self::Login,
            self::Forget->value => self::Forget,
            self::Verify->value => self::Verify,
            default => null,
        };
    }


}
