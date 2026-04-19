<?php

namespace Modules\Uploader\Service;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class UploaderService
{

    public function __construct(protected string $disk = "local")
    {


    }

    public function upload(UploadedFile $file, ?string $customName = null, bool $overwrite = false, ?string $path = null)
    {
        $originalName = $file->getClientOriginalName();
        $preparedPath = $this->preparePath($path);
        $preparedFileName = $this->prepareFileName($preparedPath, $originalName , $customName, $overwrite );


        $savedFile = $this->storeInDisk($file, $preparedPath, $preparedFileName);

        //rest of the code
    }

    /**
     * @param string $path
     * @param string $fileName
     * @param string|null $customName
     * @param bool $overwrite
     * @return string
     */
    public function prepareFileName(string $path, string $fileName, ?string $customName = null, bool $overwrite = false): string
    {
        if ($overwrite) return $customName ?? $fileName;

        $baseName = $customName ??  pathinfo($fileName, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);

        $appendixNumber = 1;

        while (Storage::disk($this->disk)->exists("{$path}/{$fileName}")) {
            $fileName = "{$baseName}_{$appendixNumber}.$extension";
            $appendixNumber++;
        }

        return $fileName;
    }

    protected function preparePath(?string $path = null): string
    {
        if (empty($path)) {
            $now = Carbon::now();
            $dir = "{$now->year}/{$now->month}/{$now->day}";
        } else {
            $dir = trim($path, '/');
        }

        $dir = "uploads/{$dir}";

        if (!Storage::disk($this->disk)->exists($dir)) {
            Storage::disk($this->disk)->makeDirectory($dir);
        }

        return $dir;

    }

    /**
     * @param UploadedFile $file
     * @param string $path
     * @param string $name
     * @return false|string
     */
    public function storeInDisk(UploadedFile $file, string $path, string $name): false|string
    {
        return Storage::disk('public')->putFileAs($path, $file, $name);
    }
}
