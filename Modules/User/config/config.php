<?php

return [
    'name' => 'User',

    'notifications' => [
        'preferences' => [
            'default' => [
                'welcome_message' => [
                    'email'    => true,
                    'sms'      => false,
                    'telegram' => false,
                    'database' => true,
                ],
            ],
        ],
        'channels'    => [
            'email'    => 'mail',
            'database'    => 'database',
            'sms'      => \Modules\Main\Notifications\Channels\SmsChannel::class,
            'telegram' => \Modules\Main\Notifications\Channels\TelegramChannel::class,
        ],
        'types'       => [
            'welcome_message',
        ],
    ],
];
