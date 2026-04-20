<?php

namespace Modules\Uploader\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Uploader\Service\UploaderService;

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
}
