<?php

namespace Modules\Auth\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Modules\Auth\Actions\DetectContactType;
use Modules\Auth\Enums\ContactType;
use Modules\Auth\Enums\VerificationActionType;
use function Laravel\Prompts\select;

class VerificationTokenService
{
    /**
     * @param string $contact
     * @param VerificationActionType $action
     * @return string
     */
    public function create(string $contact, VerificationActionType $action): string
    {
        do {
            $token = $this->generateToken();
        } while (Cache::has($this->getOrCreateKey($token)));

        Cache::put($this->getOrCreateKey($token), [
            'contact' => $contact,
            'action'  => $action,

        ], now()->addMinutes(10));
        return $token;
    }

    /**
     * @param int|null $length
     * @return string
     */
    public function generateToken(?int $length = 60): string
    {
        return Str::random($length);
    }

    /**
     * @param string $token
     * @return string
     */
    public function getOrCreateKey(string $token): string
    {
        return "verification::after_verify::token::" . $token;
    }

    /**
     * @param string $token
     * @param string $contact
     * @param VerificationActionType $action
     * @return bool
     */
    public function verify(string $token, string $contact, VerificationActionType $action): bool
    {
        $reservedToken = Cache::get($this->getOrCreateKey($token));
        if (is_null($reservedToken) || ($reservedToken['contact'] ?? null) !== $contact || ($reservedToken['action'] ?? null) !== $action) return false;

        return true;
    }

    /**
     * @param string|null $token
     * @return void
     */
    public function forget(?string $token): void
    {
        if (is_null($token)) return;
        Cache::forget($this->getOrCreateKey($token));
    }
}

