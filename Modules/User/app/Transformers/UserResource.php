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
            'name'=>$this->name,
            'phone'=>$this->phone,
            'email'=>$this->email,
            'created_at'=>$this->created_at,
            'updated_at'=>$this->updated_at,
            'email_verified_at'=>$this->email_verified_at,
            'phone_verified_at'=>$this->phone_verified_at,
        ];
    }
}
