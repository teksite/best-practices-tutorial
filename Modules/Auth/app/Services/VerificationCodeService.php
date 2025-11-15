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

class VerificationCodeService
{
    protected int $length = 5;
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
    public function handle(int|string $recipient, VerificationActionType $type, AuthIdentifierType $way): array
    {
        $key = $this->getKey($recipient, $type);

        $code = $this->generateCode();
        $this->storeCode($code, $key, $way);

        $payload = Cache::get($key);
        $expiredAt = $payload['expired_at'];

        return [
            'code' => $code,
            'expired_at' => $expiredAt,
        ];


    }

    public function waitTime(int|string $recipient, VerificationActionType $type ): int
    {
        $limiterKey = "send-code:" . request()->ip();
        if ($this->rateLimiter->tooManyAttempts($limiterKey, $this->maxSendsPerHour)) return false;

        $this->rateLimiter->hit($limiterKey, 3600);

        $key = $this->getKey($recipient, $type);
        $payload = Cache::get($key);

        if (!$payload) return 0;

        $now = now();
        $expiredAt = Carbon::parse($payload['expired_at']);
        $nextTry = Carbon::parse($payload['next_try']);

        if ($now->greaterThan($expiredAt)) {
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
        return "verification::" . md5($type->value . "::" . (string)$recipient);
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


    public function storeCode(string|int $code, string $key, AuthIdentifierType $way): int|string
    {
        $hashed = Hash::make($code);
        $payload = [
            'hash' => $hashed,
            'created_at' => Carbon::now()->toDateTimeString(),
            'expired_at' => $this->calculateExpireTime($way)->toDateTimeString(),
            'attempts' => 0,
            'next_try' => $this->calculateNextTry($way)->toDateTimeString(),
        ];

        Cache::put($key, $payload, $this->calculateExpireTime($way));

        return $code;
    }

    /**
     * @param int|string $recipient
     * @param VerificationActionType $type
     * @return void
     */
    public function forget(int|string $recipient, VerificationActionType $type): void
    {
        $key = $this->getKey($recipient, $type);
        Cache::forget($key);
    }


    /**
     * @param int|string $recipient
     * @param VerificationActionType $type
     * @param string $inputCode
     * @return bool
     */
    public function verify(int|string $recipient, VerificationActionType $type, string $inputCode): bool
    {
        $key = $this->getKey($recipient, $type);
        $payload = Cache::get($key, null);

        if (!$payload) return false;
        $expiresAt=Carbon::parse($payload['expired_at']);

        if (Carbon::now()->gt($expiresAt) ) {
            Cache::forget($key);
            return false;
        }


        $payload['attempts'] = ($payload['attempts'] ?? 0) + 1;

        if ($payload['attempts'] > $this->maxAttempts) {
                  Cache::forget($key);

            return false;
        }



        if (!empty($payload['hash']) && Hash::check($inputCode, $payload['hash'])) {
            Cache::forget($key);
            return true;
        }

        Cache::put($key, $payload, $expiresAt);

        return false;
    }

    protected function makeId(int|string $recipient): string|int
    {
        return preg_replace('/\s+/', '', $recipient);

    }

    public function calculateExpireTime(AuthIdentifierType $type): Carbon
    {
        return match ($type) {
            AuthIdentifierType::Email => now()->addMinutes(15),
            AuthIdentifierType::Phone => now()->addMinutes(2),
            default => now(),
        };
    }

    public function calculateNextTry(AuthIdentifierType $type): Carbon
    {

        return match ($type) {
            AuthIdentifierType::Email => now()->addMinutes(1),
            AuthIdentifierType::Phone => now()->addMinutes(2),
            default => now(),
        };
    }

    public function send(int|string $code, int|string $recipient, VerificationActionType $type): \Illuminate\Http\JsonResponse
    {
        $key = $this->getKey($recipient, $type);
        $payload = Cache::get($key, null);
        $expiredAt = $payload['expired_at'];

        $res = match (AuthIdentifierType::detectType($recipient)) {

            AuthIdentifierType::Email => $this->SendByEmail($code, $recipient, $expiredAt),
            AuthIdentifierType::Phone => $this->sendBySMS($code, $recipient, $expiredAt),
            default => throw new RuntimeException('Unknown verification method'),
        };
        if (!$res) {
            $this->forget($recipient, $type);
            return response()->json([
                'errors' => [
                    'error end sending code'
                ],
                'message' => 'failed',

            ])->setStatusCode(422);
        }

        return response()->json([
            'errors' => [],
            'message' => 'success',
        ])->setStatusCode(200);

    }

    public function SendByEmail($code, $recipient, $expired_at): bool
    {
        try {
            $res= Mail::to($recipient)->send(new VerificatrionCodeEmail($code ,$expired_at));
           return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    public function SendBySMS($code, $recipient, $expired_at): bool
    {
        try {
            $url = "https://api.msgway.com/send";
            $apiKey = "0f6c84f39517013ade943a46ffce20f6";
            $response = Http::withHeaders([
                'apiKey' => $apiKey,
            ])->post($url, [
                'mobile' => $recipient,
                'method' => 'sms',
                'templateID' => 16625,
                "params" => [
                    (string)$code,
                    $expired_at
                ]
            ]);

            $response->throw();
            return $response->successful();

        } catch (\Exception $e) {
            return false;
        }

    }


    public function createVerificationToken($action ,$recipient): string
    {

        do {
            $token = Str::random(60) . time();
            $key=Cache::has("verification::after_verify::$token");
        } while (Cache::has($key));

        Cache::put($key, [
            'action' => $action,
            'recipient' => $recipient,
            'recipientType' => AuthIdentifierType::detectType($recipient),
        ], now()->addMinutes(15));

        return $token;
    }

}
