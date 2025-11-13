<?php

namespace Modules\Auth\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Auth\Emails\VerificatrionCodeEmail;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Enums\AuthIdentifierType;
use Modules\User\Models\User;
use Random\RandomException;
use RuntimeException;

class TokenService
{
    protected int $length = 100;


    /**
     * @param VerificationActionType $action
     * @param string|int $recipient
     * @return string
     */
    public function createVerificationToken(VerificationActionType $action, string|int $recipient): string
    {
        do {
            $token = Str::random($this->length);
            $key = Cache::has($this->getKey($token));
        } while (Cache::has($key));

        Cache::put($key, [
            'action' => $action,
            'recipient' => $recipient,
            'recipientType' => AuthIdentifierType::detectType($recipient),
        ], now()->addMinutes(15));

        return $token;
    }

    /**
     * @param string $token
     * @param array $recipients
     * @param VerificationActionType $action
     * @return mixed
     */
    public function getToken(string $token, array $recipients, VerificationActionType $action): mixed
    {
        if (!Cache::has($this->getKey($token))) return null;

        $payload = Cache::pull($this->getKey($token));

        $cachedAction = $payload['action'] ?? null;
        $cachedRecipient = $payload['recipient'] ?? null;
        $cachedRecipientType = $payload['recipientType'] ?? null;

        if ($cachedAction || $cachedRecipient || $cachedRecipientType) return null;


        $recipient = $recipients[$cachedRecipientType] ?? null;

        if (!$recipient ||
            ($cachedRecipient !== $recipient) ||
            ($cachedAction !== $action->value)
        ) return null;

        return $payload;
    }

    /**
     * @param string $token
     * @return string
     */
    public function getKey(string $token): string
    {
        return "verification::after_verify::$token";
    }

}
