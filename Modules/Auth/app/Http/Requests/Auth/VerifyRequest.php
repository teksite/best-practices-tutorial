<?php

namespace Modules\Auth\Http\Requests\Auth;

use Illuminate\Validation\Validator;
use Modules\Auth\Http\Requests\Base\BaseAuthRequest;
use Modules\Auth\Enums\VerificationActionType;
use Modules\Auth\Rules\UsernameTypeRule;

class VerifyRequest extends BaseAuthRequest
{
    /**
     * Rules for verifying user via email or phone.
     */
    public function rules(): array
    {
        $user = auth('sanctum')->user();

        return [
            'token' => ['bail', 'required', 'string', 'min:5', 'max:255'],

            'phone' => ['nullable', 'string', 'min:5', 'max:20' , new UsernameTypeRule(), function ($attribute, $value, $fail) use ($user) {
                    if (!$value) return;

                    if ($user->phone !== $value) {
                        $fail(__('auth::validation.mismatch_record'));
                    }

                    if ($user->phone_verified_at !== null) {
                        $fail(__('auth::validation.verified_before'));
                    }
                }
            ],

            'email' => ['nullable', 'string', 'email:rfc,dns', 'min:5', 'max:255', new UsernameTypeRule(), function ($attribute, $value, $fail) use ($user) {
                    if (!$value) return;

                    if ($user->email !== $value) {
                        $fail(__('auth::validation.mismatch_record'));
                    }

                    if ($user->email_verified_at !== null) {
                        $fail(__('auth::validation.verified_before'));
                    }
                }
            ],
        ];
    }

    /**
     * After validation pipeline.
     */
    public function after(): array
    {

        return array_merge(parent::after(), [
            fn(Validator $validator) => $this->checkToken($validator),
            fn(Validator $validator) => $this->ensureRecipientProvided($validator),
        ]);
    }

    /**
     * Ensure user provided either email OR phone
     */
    protected function ensureRecipientProvided(Validator $validator): void
    {
        if ($validator->errors()->isNotEmpty()) return;

        $data = $validator->validated();

        if (empty($data['email']) && empty($data['phone'])) {
            $validator->errors()->add('recipient', __('auth::validation.no_recipient_provided'));
        }
    }

}
