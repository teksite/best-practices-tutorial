<?php

namespace Modules\User\Traits;


use Modules\User\Notifications\VerifyPhone;

trait MustVerifyPhone {
    /**
     * Determine if the user has verified their phone address.
     *
     * @return bool
     */
    public function hasVerifiedPhone(): bool
    {
        return ! is_null($this->phone_verified_at);
    }

    /**
     * Mark the user's phone as verified.
     *
     * @return bool
     */
    public function markPhoneAsVerified(): bool
    {
        return $this->forceFill([
            'phone_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    /**
     * Mark the user's phone as unverified.
     *
     * @return bool
     */
    public function markPhoneAsUnverified(): bool
    {
        return $this->forceFill([
            'phone_verified_at' => null,
        ])->save();
    }

    /**
     * Send the phone verification notification.
     *
     * @return void
     */
    public function sendPhoneVerificationNotification(): void
    {
        $this->notify(new VerifyPhone);
    }

    /**
     * Get the phone address that should be used for verification.
     *
     * @return string
     */
    public function getPhoneForVerification(): string
    {
        return $this->phone;
    }
}
