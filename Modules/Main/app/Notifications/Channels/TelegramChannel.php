<?php

namespace Modules\Main\Notifications\Channels;

use Exception;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

class TelegramChannel
{
    /**
     * @throws Exception
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (!method_exists($notification, 'toSms')) throw new Exception("the" . get_class($notification) . ' does not have toSms method');

        $data = $notification->toSms($notifiable);

        $apiKey = config('services.msgway');
        $params = [
            "method" => "sms",
            $data,
        ];

        $res = Http::withHeaders(['apiKey' => $apiKey, 'Accept' => 'application/json'])
                   ->post('https://api.msgway.com/send', $params);

        if (!$res->successful()) throw new Exception("Error sending SMS");

    }


}
