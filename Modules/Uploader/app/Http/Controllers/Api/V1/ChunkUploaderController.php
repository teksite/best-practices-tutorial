<?php

namespace Modules\Uploader\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Main\Services\ResponseJson;
use Modules\Uploader\Models\UploadFile;
use Modules\Uploader\Service\UploaderService;
use Modules\Uploader\Transformers\FileCollection;

class ChunkUploaderController extends Controller
{


    public function upload(Request $request)
    {
        dd($request->file('file'));
        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    }
}
