<?php

namespace Modules\Auth\Actions;

use Modules\Auth\Enums\ContactType;

class NormalizeContact
{
    const string MOBILE_REGEX = '/^(09|\+?989|00989)\d{9}$/';

    public static function handle(string|int|null $username): ?string
    {
        $type = DetectContactType::handle($username);
        return match ($type) {
            ContactType::EMAIL => $username,
            ContactType::PHONE => self::normalizePhoneNumber($username),
            default => null
        };

    }

    private static function normalizePhoneNumber(string|int $phoneNumber): string
    {
        if (str_starts_with($phoneNumber, '989')) {
            return '0' . substr($phoneNumber, 2);
        } elseif (str_starts_with($phoneNumber, '+989')) {
            return '0' . substr($phoneNumber, 3);
        } elseif (str_starts_with($phoneNumber, '00989')) {
            return '0' . substr($phoneNumber, 4);
        } elseif (str_starts_with($phoneNumber, '9')) {
            return '0' . $phoneNumber;
        } else {
            return $phoneNumber;
        }

    }
}
