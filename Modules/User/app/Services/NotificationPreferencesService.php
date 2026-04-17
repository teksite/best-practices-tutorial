<?php

namespace Modules\User\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Modules\User\Models\User;

class NotificationPreferencesService
{
    /**
     * @param Authenticatable|User $user
     * @return array
     */
    public function getPreferences(Authenticatable|User $user): array
    {
        return $user->notificationPreferences?->preferences ?? config('user.notifications.preferences.default');
    }

    public function getFilteredPreference(Authenticatable|User $user)
    {
        collect($this->getPreferences($user))->map(function ($preference) use ($user) {
            dd($preference, $user);
        });

    }
}
