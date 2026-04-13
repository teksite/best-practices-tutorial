<?php

namespace Modules\Auth\Enums;

enum VerificationActionType: string
{
    case REGISTER = 'register';
    case LOGIN = 'login';



    case RESET_PASSWORD = 'reset_password';
    case VERIFY_PHONE = 'verify_phone';
    case VERIFY_EMAIL = 'verify_email';
    case VERIFY_PHONE_OTP = 'verify_phone_otp';
    case VERIFY_EMAIL_OTP = 'verify_email_otp';

}
