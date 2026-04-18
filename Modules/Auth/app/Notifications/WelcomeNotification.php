<?php

namespace Modules\Auth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Main\Notifications\Channels\SmsChannel;
use Modules\User\Services\NotificationPreferencesService;

class WelcomeNotification extends Notification implements ShouldQueue
{
    use Queueable;


    protected string $type = 'welcome_message';

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return (new NotificationPreferencesService($notifiable))->getChannels($this->type) ?? [];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->line(trans('welcome.message'))
            ->action('Notification Action', 'https://laravel.com')
            ->line('Thank you for using our application!');
    }

    public function toSMS($notifiable): array
    {
        return [
            'templateID' => 21126,
            'mobile'     => $notifiable->phone,
            'params'     => [
                $notifiable->name,
            ],
        ];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'title'=>'welcome message',
            'content'=>'welcome message, this is a test message',
        ];
    }

    public function databaseType(object $notifiable): string
    {
        return 'welcoming';
    }
}
