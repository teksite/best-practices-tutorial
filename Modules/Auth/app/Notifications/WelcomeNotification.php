<?php

namespace Modules\Auth\Notifications;

use DefStudio\Telegraph\Facades\Telegraph;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Notifications\Channels\SmsChannel;
use Modules\Auth\Traits\PreferenceNotificationAware;
use Modules\TelegramBot\Notifications\Channels\TelegramChannel;
use Modules\User\Services\NotificationPreferenceService;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable ,PreferenceNotificationAware;

    protected string $type='welcome';
    protected array $force=['database'];
    public int $tries = 1;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
    }

    public function databaseType(object $notifiable): string
    {
        return 'welcome-msg';
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->from('no-reply@teksite.net')
            ->markdown('mails.welcome', ['name' => $notifiable->name]);

    }

    public function failed($throwable)
    {
        Log::error('welcome notification: ' . $throwable);
    }

    public function toSms($notifiable): array
    {
        return [
            'message' => "$notifiable->name عزیز، به سایت laratek.net خوش آمدید. ",
        ];
    }

    public function toTelegram($notifiable): array
    {
        return [
            'message' => "$notifiable->name عزیز، به سایت laratek.net خوش آمدید. ",
        ];
    }

    public function toDatabase($notifiable): array
    {
        return [
          'message'=>$notifiable->name,
        ];
    }


}
