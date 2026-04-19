<?php

namespace Modules\Uploader\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Fillable(['original_name', 'title', 'path', 'sizes', 'mime_type', 'disk'])]
class UploadFile extends Model
{
    public function models()
    {
        return $this->morphedByMany(Model::class, 'model' , 'upload_files_models' ,'model_id');
    }
}
