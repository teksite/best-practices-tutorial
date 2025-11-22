<?php

namespace Modules\User\Services;

use Illuminate\Support\Arr;
use Modules\User\Models\User;

class NotificationPreferenceService
{
    public function getPreferences(User $user)
    {
        $defaults = config('user.notifications.defaults');
        $userPreferences = $user->notificationPreference->preferences ?? [];


       return collect($defaults)->map(function ($channels, $type) use ($userPreferences) {
            return collect($channels)->merge($userPreferences[$type] ?? [])->toArray();
        });

    }

    public function updatePreferences(User $user, string $type, string $channel , bool $value)
    {
        $userPreferences =$user->notificationPreference()->firstOrCreate();
        $defaults = config('user.notifications.defaults');
        $defaults[$type][$channel]= $value;
        return $userPreferences->update(['preferences' =>$defaults]);


    }

    public function allowedChannel(User $user , string $type , array $force=[])
    {
        $preferences = $user->notificationPreference->preferences ?? [];
        $preferType  = Arr::get($preferences, $type, []);

        if (empty($preferType)) {
            return [];
        }


        $channelClass = config('user.notifications.channels', []);

        return collect($preferType)
            ->filter(fn ($enabled) => $enabled)
            ->map(fn ($enabled, $channel) => $channelClass[$channel] ?? null)
            ->filter()
            ->values()
            ->merge($force)
            ->toArray();
    }
}
