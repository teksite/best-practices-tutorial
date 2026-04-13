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

class TokenService
{
    /**
     * @param string $contact
     * @param VerificationActionType $action
     * @return string
     */
    public function create(string $contact, VerificationActionType $action): string
    {
        do {
            $token = Str::random(60);
        } while (Cache::has("verification::after_verify::token::" . $token));

        Cache::put('verification::after_verify::token::' . $token, [
            'contact' => $contact,
            'action'  => $action,

        ], now()->addMinutes(10));

        return $token;

    }

    /**
     * @param string $token
     * @param string $contact
     * @param VerificationActionType $action
     * @return bool
     */
    public function verify(string $token, string $contact, VerificationActionType $action): bool
    {
        $token = Cache::get("verification::after_verify::token::" . $token);

        if (is_null($token) || ($token['contact'] ?? null) !== $contact || ($token['action'] ?? null) !== $action) return false;

        return true;
    }
}

