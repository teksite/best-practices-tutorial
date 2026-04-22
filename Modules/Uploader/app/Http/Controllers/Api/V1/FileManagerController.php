<?php

namespace Modules\Uploader\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Dotenv\Parser\Parser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Main\Services\ResponseJson;
use Modules\Uploader\Enums\DiskType;
use Modules\Uploader\Events\UploadedNewFileEvent;
use Modules\Uploader\Jobs\ResizeImageJob;
use Modules\Uploader\Models\UploadFile;
use Modules\Uploader\Service\UploaderService;
use Modules\Uploader\Transformers\FileCollection;
use Modules\Uploader\Transformers\FileResource;
use Modules\User\Models\User;

class FileManagerController extends Controller
{


    public function index(Request $request)
    {

        $collectionFiles = FileCollection::make(UploadFile::query()->latest()->paginate());
        return ResponseJson::Success($collectionFiles);
    }

    public function show(UploadFile $file)
    {
        $file = FileResource::make($file);
        return ResponseJson::Success(compact('file'));
    }


    public function upload(Request $request)
    {
        $file =UploaderService::make(DiskType::ARVAN_PUBLIC)->upload($request->file('file'), null, false, null);
        if (!!$file) {
            event(new UploadedNewFileEvent($file));
            return ResponseJson::Success(['file' => FileResource::make($file)], trans('uploader::messages.uploader.upload_success'));
        } else {
            return ResponseJson::Failed(trans('main::messages.global.server_wrong'), trans('uploader::messages.uploader.upload_failed'));
        }

    }

    public function uploadByModel(Request $request)
    {
        $model = User::query()->find(1);
        if (!!$model && method_exists($model, 'uploader')) {
            $file = UploaderService::make(DiskType::ARVAN_PRIVATE)->upload($request->file('file'), null, false, null);
            event(new UploadedNewFileEvent($file));

            if (!!$file) {
                $model->uploader()->syncWithPivotValues($file->id, ['name' => 'avatar']);
                return ResponseJson::Success(['file' =>  FileResource::make($file)], trans('uploader::messages.uploader.upload_success'));
            }
        } else {
            $classModel = !!$model ? get_class($model) : $request->input('model');;
            Log::error("the model {$classModel} doesn't have method 'uploader'");
        }

        return ResponseJson::Failed(trans('uploader::messages.uploader.method_not_exist'), trans('uploader::messages.uploader.upload_failed'));

    }

    public function delete(UploadFile|string|array $file)
    {
        $res = !!$this->uploaderService->remove($file);

        if ($res) return ResponseJson::Success([], trans('uploader::messages.uploader.delete_success'));

        return ResponseJson::Failed(trans('main::messages.global.server_wrong'), trans('uploader::messages.uploader.delete_failed'));
    }
}
