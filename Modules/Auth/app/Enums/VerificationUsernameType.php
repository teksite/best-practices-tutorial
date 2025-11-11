<?php

namespace Modules\Auth\Enums;

enum VerificationUsernameType: string
{
    case Email = 'email';
    case Phone = 'phone';

    public static function detectType(string $input): ?VerificationUsernameType
    {
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            return self::Email;
        }

        if (preg_match('/^\+?[0-9]+$/', $input)) {
            return self::Phone;
        }

        return null;
    }

    public static function getColumn(string $input, bool $byValue = false): ?string
    {
        if ($byValue) {
            return match (self::detectType($input)) {
                self::Email => 'email',
                self::Phone => 'phone',
                default => null
            };
        }

        return match ($input) {
            self::Email->value => 'email',
            self::Phone->value => 'phone',
            default => null
        };
    }
}
