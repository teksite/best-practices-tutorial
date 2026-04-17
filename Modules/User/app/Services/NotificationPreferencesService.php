<?php

namespace Modules\User\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Modules\User\Models\User;

class NotificationPreferencesService
{
    /**
     * @param Authenticatable|User $user
     */
    public function __construct(public Authenticatable|User $user)
    {
    }

    /**
     * @param string|null $type
     * @return array
     */
    public function storedPreferences(?string $type = null): array
    {
        $storedData = $this->user->notificationPreferences?->preferences ?? [];
        return !!$type ? $storedData[$type] : $storedData;
    }

    /**
     * @param string|null $type
     * @return array
     */
    public function defaultPreferences(?string $type = null): array
    {
        $defaultData = config('user.notifications.preferences.default');
        return !!$type ? $defaultData[$type] : $defaultData;

    }


    /**
     * @param string|null $type
     * @return array
     */
    public function getPreferences(?string $type = null): array
    {
        $mergedPreferences = collect($this->defaultPreferences())->map(function ($preferences, $type) {
            return collect($preferences)->map(function ($value, $channel) use ($type) {
                return $this->storedPreferences($type)[$channel] ?? $value;
            });
        })->toArray();

        return !!$type ? ($mergedPreferences[$type] ?? []) : $mergedPreferences;
    }


    /**
     * @param string|null $type
     * @return array
     */
    public function getFilteredPreferences(?string $type = null): array
    {
        $filteredData = collect($this->getPreferences())->map(function ($preferences, $type) {
            return collect($preferences)->filter(fn($value, $channel) => $value)->toArray();
        })->toArray();

        return !!$type ? ($filteredData[$type] ?? []) : $filteredData;
    }

    /**
     * @param string $type
     * @return array
     */
    public function getChannels(string $type): array
    {
        $preferences = $this->getFilteredPreferences($type) ?? [];
        $filteredPreferences = array_keys($preferences);
        $allChannels = config('user.notifications.channels');

        $channels = [];

        foreach ($allChannels as $channel => $channelClass) {
            if (in_array($channel, $filteredPreferences)) $channels[] = $channelClass;
        }
        return $channels;


    }

    /**
     * @param string $type
     * @param string $channel
     * @param bool $value
     * @return Model
     */
    public function updatePreference(string $type, string $channel, bool $value): Model
    {
        $data = $this->prepareToUpdate($type, $channel, $value);

        return $this->update($data);
    }

    /**
     * @param array $data
     * @return Model
     */
    public function updateMany(array $data = []): Model
    {
        $allTypes = config('user.notifications.types');
        $data = [];

        foreach ($allTypes as $type => $preferences) {
            foreach ($preferences as $channel => $value) {
                $data[$type] = $this->prepareToUpdate($type, $channel, $value);
            }
        }
        return $this->update($data);
    }

    /**
     * @param string $type
     * @param string $channel
     * @param bool $value
     * @return array
     */
    public function prepareToUpdate(string $type, string $channel, bool $value): array
    {
        $defaultPreference = $this->defaultPreferences($type) ?? [];

        $stored = $this->storedPreferences();

        $stored[$type][$channel] = $value;

        $data[$type] = collect($stored[$type])->filter(function ($value, $channel) use ($defaultPreference) {
            return $value !== ($defaultPreference[$channel] ?? false);
        })->toArray();

        return $data;
    }

    /**
     * @param $data
     * @return Model
     */
    protected function update($data): Model
    {
        return $this->user->notificationPreferences()->updateOrCreate(
            ['user_id' => $this->user->id],
            ['preferences' => $data],
        );
    }


}
