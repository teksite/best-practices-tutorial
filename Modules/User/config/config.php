<?php

return [
    'name' => 'User',

    'notifications' => [
        'preferences' => [
            'default' => [
                'welcome_message' => [
                    'email'    => true,
                    'sms'      => false,
                    'telegram' => true,
                ],
            ],
        ],
        'channels'    => [
            'email'    => 'mail',
            'sms'      => \Modules\Main\Notifications\Channels\SmsChannel::class,
            'telegram' => \Modules\Main\Notifications\Channels\TelegramChannel::class,
        ],
        'types'       => [
            'welcome_message',
        ],
    ],
];
