<?php

namespace Modules\Uploader\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Main\Services\ResponseJson;
use Modules\Uploader\Enums\DiskType;
use Modules\Uploader\Models\UploadFile;
use Modules\Uploader\Service\UploaderService;
use Modules\Uploader\Transformers\FileCollection;
use Modules\Uploader\Transformers\FileResource;

class ChunkUploaderController extends Controller
{


    public function upload(Request $request)
    {

        $validated = $request->validate([
            'file'            => 'required|file|max:6000',
            'chunk_number'    => 'required|integer|min:1',
            'total_chunks'    => 'required|integer|min:1',
            'file_identifier' => 'required|string',
            'original_name'   => 'required|string',
        ]);

        $chunk = $validated['file'];
        $chunkNumber = $validated['chunk_number'];
        $total_chunks = $validated['total_chunks'];
        $fileIdentifier = $validated['file_identifier'];
        $originalName = $validated['original_name'];

        $tempDisk = DiskType::LOCAL;
        $tempDir = "temp/chunk/$fileIdentifier";
        $tempStorage = Storage::disk($tempDisk->value);

        if (!$tempStorage->exists($tempDir)) $tempStorage->makeDirectory($tempDir);

        $tempStorage->putFileAs($tempDir, $chunk, "$chunkNumber.part");

        if ($chunkNumber < $total_chunks) return ResponseJson::success([], 'Chunk upload successful, wait for more ...');

        //TODO Validation by mimeType , size
        //TODO add job to unify chunked files and move to disk (s3 , ....)
        $chunksFile = collect(Storage::disk($tempDisk->value)->files($tempDir))
            ->filter(fn($chnk) => str_ends_with($chnk, ".part"))
            ->sortBy(fn($file) => pathinfo($file, PATHINFO_FILENAME));

        $filename = Str::ulid()->toString() . $originalName;

        $finalFilePath = Storage::disk($tempDisk)->path("temp/chunk/$fileIdentifier/$filename");
        $outStream = fopen($finalFilePath, "ab");

        foreach ($chunksFile as $chunked) {
            $chunkStream = $tempStorage->readStream($chunked);
            stream_copy_to_stream($chunkStream, $outStream);
        }
        fclose($outStream);


        return ResponseJson::Success(['file' => ''], trans('uploader::messages.uploader.upload_success'));

    }
}
