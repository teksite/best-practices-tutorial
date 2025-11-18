<?php
return [
    'default' => 'kevenegar',
    'providers' => [
        'kevenegar' => [
            'api_key' => env('KAVENEGAR_SMS_API_KEY', "442F6749537A766A2B4D645871644478626E72484A536171632B562B6E4D48623632536437363868536E6B3D"),
            'url' => "https://api.kavenegar.com/v1/{API_KEY}/sms/send.json",
            'sender' => "0018018949161",
        ]
    ]
];
