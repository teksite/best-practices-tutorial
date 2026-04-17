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
                ],
            ],
        ],
        'channels' => [
            'email' => 'mail',
            'sms' => \Modules\Main\Notifications\Channels\SmsChannel::class,
        ]
    ],
];
