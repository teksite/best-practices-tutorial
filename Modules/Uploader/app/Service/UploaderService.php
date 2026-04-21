<?php

namespace Modules\Uploader\Service;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Uploader\Enums\DiskType;
use Modules\Uploader\Models\UploadFile;

class UploaderService
{

    public function __construct(protected DiskType $disk = DiskType::LOCAL)
    {


    }

    /**
     * @param UploadedFile $file
     * @param int|string|null $customName set customName to -1 to set the file name by uuid
     * @param bool $overwrite
     * @param string|null $path
     * @param string|null $title
     * @return false|UploadFile
     */
    public function upload(UploadedFile $file, null|int|string $customName = null, bool $overwrite = false, ?string $path = null, ?string $title = null): false|UploadFile
    {
        $originalName = $file->getClientOriginalName();
        $preparedPath = $this->preparePath($path);
        $mimeType = $file->getMimeType();
        $size = $file->getSize();
        $preparedFileName = $this->prepareFileName($preparedPath, $originalName, $customName, $overwrite);


        $savedFileInDisk = $this->storeInDisk($file, $preparedPath, $preparedFileName, $overwrite);

        if ($savedFileInDisk) {
            $model = $this->storeInDatabase($originalName, $savedFileInDisk, $mimeType, $size, $title);
            if (!!$model) return $model;

            $this->removeFromDisk($savedFileInDisk);
            return false;

        }
        return false;


    }

    /**
     * @param string $path
     * @param string $fileName
     * @param int|string|null $customName
     * @param bool $overwrite
     * @return string
     */
    public function prepareFileName(string $path, string $fileName, null|int|string $customName = null, bool $overwrite = false): string
    {
        if ($overwrite) return $customName ?? $fileName;

        if ($customName == -1) $customName = Str::uuid()->toString();
        $baseName = $customName ?? pathinfo($fileName, PATHINFO_FILENAME);
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

        if (!Storage::disk($this->disk->value)->exists($dir)) {
            Storage::disk($this->disk->value)->makeDirectory($dir);
        }

        return $dir;

    }

    /**
     * @param UploadedFile $file
     * @param string $path
     * @param string $name
     * @param bool $overwrite
     * @return false|string
     */
    public function storeInDisk(UploadedFile $file, string $path, string $name, bool $overwrite = false): false|string
    {
        try {
            if (Storage::disk($this->disk->value)->exists($path . '/' . $name)) {
                Storage::disk($this->disk->value)->putFileAs($path, $file, $name);
            } else {
                Storage::disk($this->disk->value)->path($path . '/' . $name);
            }
            return $path . '/' . $name;
        } catch (\Exception $exception) {
            Log::error($exception);
            return false;
        }

    }

    /**
     * @param string $originalName
     * @param string $path
     * @param string $mimeType
     * @param string|int $size
     * @param string|null $title
     * @param bool $overWrite
     * @return false|UploadFile
     */
    public function storeInDatabase(string $originalName, string $path, string $mimeType, string|int $size, ?string $title = null, bool $overWrite = false): false|UploadFile
    {
        try {
            if ($overWrite) {
                return UploadFile::query()->updateOrCreate([
                    'path'      => $path,
                    'disk'      => $this->disk->value,
                    'mime_type' => $mimeType,
                ],
                    [
                        'original_name' => $originalName,
                        'title'         => $title,
                        'sizes'         => $size,
                    ]);
            } else {
                return UploadFile::query()->create([
                    'path'          => $path,
                    'disk'          => $this->disk->value,
                    'mime_type'     => $mimeType,
                    'original_name' => $originalName,
                    'title'         => $title,
                    'sizes'         => $size,
                ]);
            }
        } catch
        (\Exception $exception) {
            Log::error($exception);
            return false;
        }
    }

    /**
     * @param UploadFile|int|string $uploadFile
     * @return bool
     */
    public function remove(UploadFile|int|string $uploadFile): bool
    {
        if ($uploadFile instanceof UploadFile) {
            $file = $uploadFile;
        } else {
            $file = UploadFile::query()->where('disk', $this->disk->value)->where(function ($query) use ($uploadFile) {
                return $query->where('id', $uploadFile)->orWhere('path', $uploadFile);
            })->first();
        };
        return $file->delete();

    }

    /**
     * @param string $path
     * @return bool
     */
    public function removeFromDisk(string $path): bool
    {
        return Storage::disk($this->disk->value)->delete($path);
    }

    /**
     * @param UploadFile|int $uploadFile
     * @return bool|null
     */
    public function removeFromDatabase(UploadFile|int $uploadFile): bool|null
    {
        if (is_integer($uploadFile)) {
            $uploadFile = UploadFile::query()->find($uploadFile);
        }
        return $uploadFile?->delete();
    }
}
