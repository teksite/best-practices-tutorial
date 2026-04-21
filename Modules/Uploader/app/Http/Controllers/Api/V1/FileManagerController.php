<?php

namespace Modules\Uploader\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Uploader\Service\UploaderService;
use Modules\User\Models\User;

class FileManagerController extends Controller
{

  protected UploaderService $uploaderService;
    public function __construct()
    {
        $this->uploaderService=new UploaderService();
    }

    public function upload(Request $request)
    {
       $uploadedFile= $this->uploaderService->upload($request->file('file') ,null , false , null);

    }

    public function uploadByModel(Request $request ,null|Model $model = null)
    {
        $model = User::query()->find(1);
        $uploadedFile= $this->uploaderService->upload($request->file('file') ,null , false , null);
        if (!!$model && method_exists($model,'uploader')){
            $model->uploader()->syncWithPivotValues( $uploadedFile->id ,['name'=>'avatar'] );
        }

    }
}
