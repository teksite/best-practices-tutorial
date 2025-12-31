<?php

namespace Modules\Uploader\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;
use Modules\Uploader\Enums\DiskType;

class Media extends Model
{
    use HasUlids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['id', 'original_name', 'name', 'title', 'path', 'mime_type', 'extension', 'size', 'disk',];

    protected static function boot()
    {
        parent::boot();
        static::deleting(function (Media $media) {
            if (Storage::disk($media->disk)->exists($media->path)) {
                $result = Storage::disk($media->disk)->delete($media->path);
                if (!$result) return false;
            }
            return true;
        });
    }

    protected function casts(): array
    {
        return [
            'disk' => DiskType::class,
        ];
    }

    public function modeled(): MorphTo
    {
        return $this->morphTo();
    }
}
