<?php

namespace Modules\Auth\Actions;

use Modules\Auth\Enums\ContactType;

class DetectContactType
{
    const string MOBILE_REGEX = '/^(09|\+?989|00989)\d{9}$/';

    public static function handle(string|int $username): ?ContactType
    {
        if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
            return ContactType::EMAIL;
        } elseif (!!preg_match(self::MOBILE_REGEX, $username)) {
            return ContactType::PHONE;
        }
        return null;
    }
}
