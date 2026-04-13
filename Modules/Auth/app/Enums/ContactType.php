<?php

namespace Modules\Auth\Enums;

use Modules\Auth\Actions\DetectContactType;

enum ContactType :string
{
   case EMAIL = 'email';
   case PHONE = 'phone';

   public static function getType(int|string $username): ContactType
   {
       return DetectContactType::handle($username);
   }

}
