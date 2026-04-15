<?php

return [
    'auth'              => [
        'usernameType'           => 'the contact pattern is not valid',
        'user_exist'             => 'The user exists',
        'user_not_found'         => 'The user does not exist',
        'contact_is_used_before' => 'the entered :attribute already use by another user or is not accepted by the system',
        'invalid_token'          => 'invalid token, please try again',
        'conflict_password_code' => 'something went wrong with the authentication way',
        'conflict_password_code_existence' => 'password and code should not use together',
        'credentials'=>'The credentials do not match',
        'reset_password'=>'resetting password is done successfully',
    ],
    'verification_code' => [
        'sent_successfully' => 'the verification code was successfully sent',
        'wait'              => 'you can retry in :seconds seconds',
        'email_subject'     => 'verification code',
        'failed'            => 'something went wrong to send the verification code, try again later',
        'not_valid'         => 'verification code is not valid',
        'valid'             => 'verification code is verified successfully',
        'wrong_auth_token'  => 'The token code in not valid',
    ],
];
