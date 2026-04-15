<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'name'  => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'phone_verification' => $this->phone_verified_at,
            'email_verification' => $this->email_verified_at,
        ];
    }
}
