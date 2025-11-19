<?php

namespace Modules\Auth\Notifications;

use DefStudio\Telegraph\Facades\Telegraph;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;
use Modules\Auth\Notifications\Channels\SmsChannel;
use Modules\TelegramBot\Notifications\Channels\TelegramChannel;

class WelcomeNotification extends Notification /*implements ShouldQueue*/
{
    use Queueable;

    public int $tries = 1;

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
        return [/*'mail' ,*//* SMSChannel::class ,*/TelegramChannel::class];
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
            'message' =>"$notifiable->name عزیز، به سایت laratek.net خوش آمدید. ",
        ];
    }



}
