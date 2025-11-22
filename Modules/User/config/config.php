<?php

return [
    'name' => 'User',
    'notifications' => [
        "defaults" => [
            "welcome" => [
                "email" => true,
                "sms" => false,
                "telegram" => true,
            ]
        ],
        "channels" => [
            "telegram" => \Modules\TelegramBot\Notifications\Channels\TelegramChannel::class,
            "sms"=> \Modules\Auth\Notifications\Channels\SmsChannel::class,
            "email"=>"mail"
        ],
    ],

];
