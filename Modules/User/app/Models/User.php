<?php

namespace Modules\User\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Modules\Auth\Enums\ContactType;
use Modules\User\Database\Factories\UserFactory;
use Modules\User\Traits\MustVerifyPhone;

#[Fillable(['name', 'email', 'password', 'phone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, MustVerifyPhone, HasApiTokens;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function checkVerifiedContactTypes(): array
    {
        return [
            'phone' => $this->hasVerifiedPhone(),
            'email' => $this->hasVerifiedEmail(),
        ];
    }

    public function verifyingContactType(?ContactType $contactType = null, bool $overwrite = false, ?Carbon $date = null): void
    {
        $date ??= Carbon::now();

        $ways = $contactType
            ? [$contactType->value . '_verified_at',]
            : ['email_verified_at', 'phone_verified_at'];
        foreach ($ways as $way) {
            if ($overwrite || is_null($this->{$way})) {
                $this->forceFill([$way => $date])->save();
            }
        }

    }
}
