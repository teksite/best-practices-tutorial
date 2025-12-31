<?php

namespace Modules\Uploader\Http\Controllers\Api\Uploader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
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
        $request->validate([
            'file' => 'required|file|mimes:jpeg,jpg,png,gif|between:3,2048',
        ]);
        $result = Image::read($request->file)->toWebp(80);


        $file = uploadFile($request->file('file'), [
            'disk' => DiskType::PUBLIC,
        ]);
        return $file;

        return ApiResponse::success();
    }

}
