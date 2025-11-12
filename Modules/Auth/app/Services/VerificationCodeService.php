<?php

namespace Modules\Auth\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Cache\RateLimiter;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Enums\VerificationUsernameType;
use Modules\User\Models\User;
use Random\RandomException;
use RuntimeException;

class VerificationCodeService
{
    protected int $length = 6;
    protected int $maxAttempts = 5;
    protected int $maxSendsPerHour = 10;

    protected RateLimiter $rateLimiter;

    public function __construct()
    {
        $this->rateLimiter = app(RateLimiter::class);

    }


    /**
     * @throws RandomException
     */
    public function send(int|string $recipient, VerificationActionType $type, VerificationUsernameType $way): void
    {
        $key = $this->getKey($recipient, $type);

        $code = $this->generateCode();
        $this->storeCode($code, $key, $way);

        switch ($way) {
            case (VerificationUsernameType::Email):
                $this->SendByEmail($code , $recipient);
                break;
            case (VerificationUsernameType::Phone):
                $this->sendBySMS($code , $recipient);
                break;
            default:
                throw new RuntimeException('Unknown verification method');

        }

    }

    public function waitTime(int|string $recipient, VerificationActionType $type): int
    {
        $limiterKey = "send-code:" . request()->ip();
        if ($this->rateLimiter->tooManyAttempts($limiterKey, $this->maxSendsPerHour ))  return false;

        $this->rateLimiter->hit($limiterKey, 3600);

        $key = $this->getKey($recipient, $type);
        $payload = Cache::get($key);

        if (!$payload) return 0;

        $now = now();
        $expiresAt = Carbon::parse($payload['expires_at']);
        $nextTry = Carbon::parse($payload['next_try']);

        if ($now->greaterThan($expiresAt)) {
            Cache::forget($key);
            return 0;
        }

        if ($now->lessThan($nextTry)) {
           return $now->diffInSeconds($nextTry);
        }

        return 0;

    }


    /**
     * @param int|string $recipient
     * @param VerificationActionType $type
     * @return string
     */
    public function getKey(int|string $recipient, VerificationActionType $type): string
    {
        return "verification::" . md5($type->value."::".(string)$recipient);
    }

    /**
     * @throws RandomException
     */
    public function generateCode(): int|string
    {
        $min = (int)pow(10, $this->length - 1);
        $max = (int)pow(10, $this->length) - 1;
        return random_int($min, $max);

    }


    public function storeCode(string|int $code, string $key, VerificationUsernameType $way): int|string
    {
        $hashed = Hash::make($code);

        $payload = [
            'hash' => $hashed,
            'created_at' => Carbon::now()->toDateTimeString(),
            'expires_at' => $this->calculateExpireTime($way)->toDateTimeString(),
            'attempts' => 0,
            'next_try' =>$this->calculateNextTry($way)->toDateTimeString(),
        ];

        Cache::put($key, $payload, $this->calculateExpireTime($way));

        return $code;
    }

    /**
     * @param int|string|User $identification
     * @param VerificationActionType $type
     * @param string|int $to
     * @return void
     */
    public function forget(int|string|User $identification, VerificationActionType $type, string|int $to): void
    {
        $identification = $this->makeId($identification);
        $key = $this->getKey($identification, $type, $to);
        Cache::forget($key);
    }


    public function verify(int|string $recipient, VerificationActionType $type, string $inputCode): bool
    {
        $key = $this->getKey($recipient, $type);
        $payload = Cache::get($key, null);

        if (!$payload) return false;


        if (Carbon::now()->gt(Carbon::parse($payload['expires_at'])) || $payload['attempts'] >= $this->maxAttempts) {
            Cache::forget($key);
            return false;
        }


        if (Hash::check($inputCode, $payload['hash'])) {
            Cache::forget($key);
            return true;
        }


        $payload['attempts'] = ($payload['attempts'] ?? 0) + 1;


        Cache::put($key, $payload, Carbon::parse($payload['expires_at']));

        return false;
    }

    protected function makeId(int|string $recipient): string|int
    {
        return preg_replace('/\s+/', '', $recipient);

    }

    public function calculateExpireTime(VerificationUsernameType $type): Carbon
    {
        return match ($type) {
            VerificationUsernameType::Email => now()->addMinutes(15),
            VerificationUsernameType::Phone => now()->addMinutes(2),
            default => now(),
        };
    }
    public function calculateNextTry(VerificationUsernameType $type): Carbon
    {

        return match ($type) {
            VerificationUsernameType::Email => now()->addMinutes(1),
            VerificationUsernameType::Phone => now()->addMinutes(2),
            default => now(),
        };
    }

    protected function SendByEmail($code ,$to)
    {
        try {

            Log::info("send by email $code , $to");
        } catch (\Exception $e) {

        }
    }
    protected function SendBySMS($code ,$to)
    {
        try {
            Log::info("send by SMS $code , $to");
        } catch (\Exception $e) {

        }
    }

}
