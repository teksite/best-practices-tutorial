<?php

namespace Modules\User\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        if ($this->read_at === null) $this->markAsRead();
        return [
            'id'=>$this->id,
            'data'=>$this->data,
            'read'=>$this->read_at,
        ];
    }
}
