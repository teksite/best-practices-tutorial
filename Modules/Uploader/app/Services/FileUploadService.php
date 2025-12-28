<?php

namespace Modules\Uploader\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Modules\Uploader\Enums\DiskType;
use Modules\Uploader\Models\Media;
use RuntimeException;

class FileUploadService
{
    public function __construct(protected DiskType $disk = DiskType::LOCAL)
    {
    }

    public static function resolve(DiskType $disk = DiskType::LOCAL): FileUploadService
    {
        return new self($disk);
    }

    /**
     * @return string|null
     */
    public function defaultDirectory(): ?string
    {
        $now = Carbon::now();
        return 'uploader' . DIRECTORY_SEPARATOR . $now->format('Y') . DIRECTORY_SEPARATOR . $now->format('m');;
    }

    /**
     * @param UploadedFile $file
     * @param string|null $directory
     * @param string|null $filename
     * @param bool $overWrite
     *
     * @param Model|null $relatedModel
     *
     * @return Model|Media
     * @throws Exception
     */
    public function upload(
        UploadedFile $file,
        ?string      $directory,
        ?string      $filename = null,
        bool         $overWrite = false,
        ?Model       $relatedModel = null
    ): Model|Media
    {
        $directory= $directory ?: $this->defaultDirectory();

        $fileData = $this->storeFile($file, $directory,$filename, $overWrite);


        $media = Media::query()->create([
            'original_name' => $fileData['original_name'],
            'name' => $fileData['name'],
            'path' => $fileData['path'],
            'mime_type' => $fileData['mime_type'],
            'extension' => $fileData['extension'],
            'size' => $fileData['size'],
            'disk' => $this->disk->value,
        ]);
        if ($relatedModel) {
            $this->attachToModel($relatedModel, $media);
        }
        return $media;

    }

    public function resolveFileName(string $filename,string $directory, string $extension, bool $overWrite = false): string
    {
        $filename = str_replace(' ', '_', $filename);
        $baseName = "{$filename}.{$extension}";
        $path = "{$directory}/{$baseName}";
        $disk = Storage::disk($this->disk->value);

        if ($overWrite) {
            $this->deleteExistingMediaFromDB($path);
            return $baseName;
        }
        $i = 1;
        while ($disk->exists($path)) {
            $baseName = "{$filename}_{$i}.{$extension}";
            $path = $this->defaultDirectory() . DIRECTORY_SEPARATOR . $baseName;
            $i++;
        }
        return $baseName;
    }

    /**
     * @param UploadedFile $file
     * @param string|null $filename
     * @param bool $overWrite
     *
     * @return array
     */
    public function storeFile(UploadedFile $file ,string $directory, ?string $filename, bool $overWrite): array
    {
        $extension = $file->getClientOriginalExtension();
        $originalName = $file->getClientOriginalName();
        $finalName = $filename ? $this->resolveFileName($filename ,$directory, $extension, $overWrite) : null;
        $path = $finalName
            ? $file->storeAs($directory, $finalName, ['disk' => $this->disk->value]) :
            $file->store($directory, ['disk' => $this->disk->value]);


        return [
            'extension' => $extension,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'original_name' => $originalName,
            'path' => $path,
            'name' => $finalName
                ? pathinfo($finalName, PATHINFO_FILENAME)
                : pathinfo($originalName, PATHINFO_FILENAME),
        ];
    }

    /**
     * @param Model $relatedModel
     * @param Model|Media $media
     *
     * @return void
     * @throws Exception
     */
    public function attachToModel(Model $relatedModel, Model|Media $media): void
    {
        if (!class_exists($relatedModel) || !method_exists($relatedModel, 'attachFile')) {
            throw new RuntimeException('Model must use HasAttachedFile trait.');
        }
        $relatedModel->attachFile($media);
    }

    /**
     * @param string $path
     *
     * @return mixed
     */
    public function deleteExistingMediaFromDB(string $path): mixed
    {
        return Media::query()->where('disk', $this->disk->value)->where('path', $path)->delete();
    }

    public function deleteExistingMediaFromPath(string $path): bool
    {
        return Storage::disk($this->disk->value)->delete($path);
    }

    public function delete(string $path): void
    {
        if ($this->deleteExistingMediaFromPath($path)) $this->deleteExistingMediaFromDB($path);
    }

}
