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
        $savedFilePath = $this->storeInDisk($file, $preparedPath, $preparedFileName, $overwrite);

        if (!$savedFilePath) return false;

        $model = $this->storeInDatabase($originalName, $savedFilePath, $mimeType, $size, $title, $overwrite);
        if ($model) return $model;

        $this->removeFromDisk($savedFilePath);
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

        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $baseName = match (true) {
            $customName === -1   => Str::uuid()->toString(),
            $customName !== null => $customName,
            default              => pathinfo($fileName, PATHINFO_FILENAME)
        };
        $fileName = "{$baseName}.{$extension}";

        if ($overwrite) return $fileName;

        $appendix = 1;
        while (Storage::disk($this->disk->value)->exists("{$path}/{$fileName}")) {
            $fileName = "{$baseName}_{$appendix}.{$extension}";
            $appendix++;
        }
        return $fileName;
    }


    /**
     * @param string|null $path
     * @return string
     */
    protected function preparePath(?string $path = null): string
    {
        $dir = empty($path)
            ? 'uploads/' . Carbon::now()->format('Y/m/d')
            : 'uploads/' . trim($path, '/');

        $storage = Storage::disk($this->disk->value);
        if (!$storage->exists($dir)) $storage->makeDirectory($dir);
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
            $fullPath = "{$path}/{$name}";
            $disk = Storage::disk($this->disk->value);
            if (!$overwrite && $disk->exists($fullPath)) return $fullPath;
            $disk->putFileAs($path, $file, $name);
            return $fullPath;
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
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
            $attributes = [
                'path'      => $path,
                'disk'      => $this->disk->value,
                'mime_type' => $mimeType,
            ];

            $values = [
                'original_name' => $originalName,
                'title'         => $title,
                'size'         => $size,
            ];

            return $overWrite
                ? UploadFile::query()->updateOrCreate($attributes, $values)
                : UploadFile::query()->create($attributes + $values);
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    /**
     * @param UploadFile|string $uploadFile
     * @param DiskType|null $disk
     * @return bool
     */
    public function remove(UploadFile|string $uploadFile, null|DiskType $disk = null): bool
    {
        $file = $uploadFile instanceof UploadFile
            ? $uploadFile
            : UploadFile::query()->where('disk', $disk ?? $this->disk->value)->where('id', $uploadFile)->first();

        if (!$file) return false;
        return $file->delete();
    }

    /**
     * @param string $path
     * @param DiskType|null $disk
     * @return bool
     */
    public function removeFromDisk(string $path, null|DiskType $disk = null): bool
    {
        return Storage::disk($disk ?? $this->disk->value)->delete($path);
    }

    /**
     * @param UploadFile|string $uploadFile
     * @return bool|null
     */
    public function removeFromDatabase(UploadFile|string $uploadFile): bool|null
    {
        $file = $uploadFile instanceof UploadFile
            ? $uploadFile
            : UploadFile::query()->where('disk', $disk ?? $this->disk->value)->where('id', $uploadFile)->first();

        if (!$file) return false;

        return $file?->delete();
    }
}
