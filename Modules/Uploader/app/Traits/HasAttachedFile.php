<?php

namespace Modules\Uploader\Traits;

use Illuminate\Support\Facades\DB;
use Modules\Uploader\Models\Media;
use function PHPUnit\Framework\throwException;

trait HasAttachedFile
{

    public function media()
    {
        return $this->morphToMany(Media::class, 'model', 'media_models');
    }


    /**
     * @throws \Throwable
     */
    public function attachFile(array|Media $media): void
    {
        if ($media instanceof Media) {
            $media = [$media];
        }

        try {
            DB::beginTransaction();
            foreach ($media as $index => $file) {
                if (!$file instanceof Media) {
                    throw new \Exception("The file at index $index is not an instance of Media");
                }
                $this->media()->attach($file->id);
            }

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }


}
