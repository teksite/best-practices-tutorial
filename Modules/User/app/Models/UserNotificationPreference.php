<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

// use Modules\User\Database\Factories\UserNotificationPreferenceFactory;

#[Fillable(['preferences'])]
class UserNotificationPreference extends Model
{
    protected $table = 'user__notification_preferences';

    public function casts(): array
    {
        return [
          'preferences' =>'json',
        ];
    }

}
