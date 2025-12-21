<?php

namespace Modules\Uploader\Http\Controllers\Api\Uploader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Main\Services\ApiResponse;
use Modules\Uploader\Enums\DiskType;
use Modules\Uploader\Services\FileUploadService;

class UploaderController extends Controller
{
    /**
     * @throws \Exception
     */
    function uploader(Request $request)
    {

        $file = (new FileUploadService(DiskType::LOCAL))->upload($request->file('file') ,'dfgdfgdfgfdgdf' ,auth()->user());
        return $file;

        return ApiResponse::success();
    }

}
