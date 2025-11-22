<?php

namespace Modules\Auth\Traits;

use Modules\User\Services\NotificationPreferenceService;

trait PreferenceNotificationAware
{
    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return (new NotificationPreferenceService())->allowedChannel($notifiable ,$this->type, $this->force ?? []);
    }
}
