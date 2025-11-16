<?php

namespace Modules\Auth\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Enums\AuthIdentifierType;


class VerificationTokenService
{
    protected int $length = 100;

    /**
     * @param VerificationActionType $action
     * @param string|int $recipient
     * @param array $identityParams
     * @return string
     */
    public function createVerificationToken(VerificationActionType $action, string|int $recipient , array|string $identityParams): string
    {

        do {
            $token = Str::random($this->length);
            $key = $this->getKey($token);
        } while (Cache::has($key));
        Cache::put($key, [
            'identity' =>is_string($identityParams) ? $identityParams : $this->makeIdentity($identityParams),
            'action' => $action->value,
            'recipient' => $recipient,
            'recipientType' => AuthIdentifierType::detectType($recipient)->value,
        ], now()->addMinutes(10));
        return $token;
    }

    /**
     * @param string $token
     * @return int|array|null
     */
    public function getToken(string $token): null|int|array
    {
        if (!Cache::has($this->getKey($token))) return null;
        return Cache::get($this->getKey($token));
    }

    public function getCheckedToken(string $token, array $recipients, VerificationActionType $action,  array|string $identityParams): null|int|array
    {
        if (!Cache::has($this->getKey($token))) return null;

        $payload = Cache::get($this->getKey($token));
        $cachedAction = $payload['action'] ?? null;
        $cachedRecipient = $payload['recipient'] ?? null;
        $cachedRecipientType = $payload['recipientType'] ?? null;
        $cachedIdentity=$payload['identity'] ?? null;

        $identifier=is_string($identityParams) ? $identityParams : $this->makeIdentity($identityParams);

        if (!$cachedAction || !$cachedRecipient || !$cachedRecipientType || !$cachedIdentity) return null;

        $recipient = $recipients[$cachedRecipientType] ?? null;
        if (!$recipient ||
            ($cachedRecipient !== $recipient) ||
            ($cachedAction !== $action->value) ||
            ($cachedIdentity !== $identifier)
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

    public function forget(?String $token): void
    {
        if ($token === null) return;
        $key = $this->getKey($token);
        Cache::forget($key);
    }

    public function makeIdentity(array $params): string
    {
        return hash('sha256', implode('::', $params));
    }

}
