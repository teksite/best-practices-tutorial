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

class VerificationCodeService
{
    const int CODE_LENGTH = 6;

    const array CHAR_CODE = [1, 2, 3, 4, 5, 6, 7, 8, 9];
    const int SMS_EXPIRATION = 60;      //second
    const int EMAIL_EXPIRATION = 60 * 5;//second
    const bool ENCRYPT_CODE = true;

    const bool PRODUCTION_MODE = false;

    public function __construct()
    {
    }

    /**
     * @param string $to
     * @param VerificationActionType $action
     * @param Carbon|int|null $expiration
     * @return false|array
     */
    public function generate(string $to, VerificationActionType $action, null|Carbon|int $expiration = null): false|array
    {
        $gateway = DetectContactType::handle($to);
        if (is_null($gateway)) return false;

        $expireAt = $this->expiration($gateway, $expiration);
        $cacheKey = $this->generateCacheKey($gateway, $action, $to);
        $code = $this->randomCode();
        $this->cache($cacheKey, $code, $expireAt);

        return [
            'code'      => $code,
            'to'        => $to,
            'expire_at' => $expireAt->format('Y-m-d H:i:s'),
            'gateway'   => $gateway,
        ];
    }

    /**
     * @return string
     */
    private function randomCode(): string
    {
        $code = '';
        for ($i = 0; $i < self::CODE_LENGTH; $i++) {
            $code .= Arr::random(self::CHAR_CODE);
        };
        return $code;
    }

    /**
     * @param ContactType $gateway
     * @param Carbon|int|null $expiration
     * @return Carbon
     */
    private function expiration(ContactType $gateway, null|Carbon|int $expiration = null): Carbon
    {
        if (is_null($expiration)) {
            if ($gateway === ContactType::PHONE) {
                return Carbon::now()->addSeconds(self::SMS_EXPIRATION);
            }
            if ($gateway === ContactType::EMAIL) {
                return Carbon::now()->addSeconds(self::EMAIL_EXPIRATION);
            }
        }
        if ($expiration instanceof Carbon) return $expiration;

        return Carbon::now()->addSeconds($expiration);
    }

    /**
     * @param ContactType $gateway
     * @param VerificationActionType $action
     * @param string $to
     * @return string
     */
    private function generateCacheKey(ContactType $gateway, VerificationActionType $action, string $to): string
    {
        return "verification_code::" . $action->value . "::" . $gateway->value . "::" . $to;
    }

    /**
     * @param string $cacheKey
     * @param string $code
     * @param Carbon $expireAt
     * @return void
     */
    private function cache(string $cacheKey, string $code, Carbon $expireAt): void
    {
        Cache::put($cacheKey, [
            'code'      => self::ENCRYPT_CODE ? encrypt($code) : $code,
            'expire_at' => (string)$expireAt,
            'secure'    => self::ENCRYPT_CODE,
        ], $expireAt);
    }


    /**
     * @param string $to
     * @param VerificationActionType $action
     * @param bool $different
     * @return int|Carbon
     */
    public function getRetryTime(string $to, VerificationActionType $action, bool $different = true): int|Carbon
    {
        if (!self::PRODUCTION_MODE) return 0;
        $gateway = DetectContactType::handle($to);

        $cacheKey = $this->generateCacheKey($gateway, $action, $to);

        $cachedData = Cache::get($cacheKey);

        $reservedTime = $cachedData['expire_at'] ?? now();

        if (!$different) return $reservedTime;
        $diff = now()->diffInSeconds($reservedTime);
        return max($diff, 0);


    }

    /**
     * @param $code
     * @param string $contact
     * @param VerificationActionType $action
     * @return bool
     */
    public function check($code, string $contact, VerificationActionType $action): bool
    {
        $gateway = DetectContactType::handle($contact);

        $cacheKey = $this->generateCacheKey($gateway, $action, $contact);

        $cachedData = Cache::get($cacheKey);

        if (is_null($cachedData)) return false;

        $reservedTime = $cachedData['expire_at'] ?? now();

        $cachedCode = $cachedData['code'] ?? '';

        $diff = now()->diffInSeconds($reservedTime);

        if ($diff == 0 || $cachedCode === $code) return false;

        return true;

    }

    public function verify($code, string $contact, VerificationActionType $action): bool
    {
        $isValid= $this->check($code , $contact ,$action);
        if (!$isValid) return false;
        $this->forget($contact , $action);
        return true;
    }

    public function forget(string $contact, VerificationActionType $action ): void
    {
        $gateway = DetectContactType::handle($contact);

        $cacheKey = $this->generateCacheKey($gateway, $action, $contact);

        Cache::forget($cacheKey);
    }
}

