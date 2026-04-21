<?php

namespace Modules\Uploader\Traits;

use Modules\Uploader\Models\UploadFile;

trait HasAttachedFile
{
    public function uploader()
    {
        return $this->morphToMany(UploadFile::class, 'model' , 'upload_files_models' ,'model_id' ,'upload_id'  ,'' ,'')->withPivot(['name']);

    }
}
