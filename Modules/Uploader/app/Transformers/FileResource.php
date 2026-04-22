<?php

namespace Modules\Uploader\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'original_name' => $this->original_name,
            'title'         => $this->title,
            'path'          => $this->path,
            'sizes'         => $this->sizes,
            'mime_type'     => $this->mime_type,
            'disk'          => $this->disk,
        ];
    }
}
