<?php

namespace Modules\Auth\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Enums\VerificationUsernameType;
use Modules\User\Models\User;
use Random\RandomException;

class VerificationCodeService
{

    /**
     * @throws RandomException
     */
    public function handle(User $user, VerificationUsernameType $to, VerificationActionType $type, $expiredAt = null): void
    {
        $code = $this->generateCode();
        $key = $this->getKey($user->id, $to->value, $type->value);
        $this->storeGenerateCode($code ,$key);


    }

    /**
     * @throws RandomException
     */
    public function generateCode(): int
    {
        return random_int(100000, 999999);

    }

    public function getKey(int|string $id, string|int $to, string $type): string
    {
        $column = encrypt(VerificationUsernameType::getColumn($to));
        $id = encrypt($id);
        return "verification::$id::$column::$type";

    }


    public function storeGenerateCode(int $code, ?string $key = null): void
    {

        Cache::put($key, [
            'code' => $code,
            'expired_at' => $this->calculateExpireTime(),

        ], $this->calculateExpireTime());
    }

    public function calculateExpireTime(): Carbon
    {
        return now()->addMinutes(15);
    }
}
