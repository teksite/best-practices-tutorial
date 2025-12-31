<?php

namespace Modules\Uploader\Services;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Modules\Uploader\Enums\DiskType;
use Modules\Uploader\Models\Media;
use RuntimeException;

class FileUploadService
{
    /**
     * @param DiskType $disk
     */
    public function __construct(protected DiskType $disk = DiskType::LOCAL)
    {
    }

    /**
     * @param DiskType $disk
     *
     * @return FileUploadService
     */
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
     * @param bool $optimize
     *
     * @return Model|Media
     * @throws Exception
     */
    public function upload(
        UploadedFile $file,
        ?string      $directory,
        ?string      $filename = null,
        bool         $overWrite = false,
        ?Model       $relatedModel = null,
        bool         $optimize = true
    ): Model|Media
    {
        $directory = $directory ?: $this->defaultDirectory();

        $fileData = $this->storeFile($file, $directory, $filename, $overWrite);


        $media = Media::query()->create([
            'original_name' => $fileData['original_name'],
            'name' => $fileData['name'],
            'path' => $fileData['path'],
            'mime_type' => $fileData['mime_type'],
            'extension' => $fileData['extension'],
            'size' => $fileData['size'],
            'disk' => $this->disk->value,
            'sources' => $optimize ? $this->optimize($fileData, $directory) : [],
        ]);
        if ($relatedModel) {
            $this->attachToModel($relatedModel, $media);
        }
        return $media;

    }

    /**
     * @param string $filename
     * @param string $directory
     * @param string $extension
     * @param bool $overWrite
     *
     * @return string
     */
    public function resolveFileName(string $filename, string $directory, string $extension, bool $overWrite = false): string
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
     * @param string $directory
     * @param string|null $filename
     * @param bool $overWrite
     *
     * @return array
     */
    public function storeFile(UploadedFile $file, string $directory, ?string $filename, bool $overWrite): array
    {
        $extension = $file->getClientOriginalExtension();
        $originalName = $file->getClientOriginalName();
        $finalName = $filename ? $this->resolveFileName($filename, $directory, $extension, $overWrite) : null;
        $path = $finalName
            ? $file->storeAs($directory, $finalName, ['disk' => $this->disk->value]) :
            $file->store($directory, ['disk' => $this->disk->value]);
        return [
            'extension' => $extension,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'original_name' => $originalName,
            'path' => $path,
            'name' => $finalName ?: pathinfo($path, PATHINFO_FILENAME),
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

    /**
     * @param string $path
     *
     * @return bool
     */
    public function deleteExistingMediaFromPath(string $path): bool
    {
        return Storage::disk($this->disk->value)->delete($path);
    }

    /**
     * @param string $path
     *
     * @return void
     */
    public function delete(string $path): void
    {
        if ($this->deleteExistingMediaFromPath($path)) $this->deleteExistingMediaFromDB($path);
    }

    /**
     * @param $fileData
     * @param $directory
     *
     * @return array
     */
    public function optimize($fileData, $directory): array
    {
        $optimizedFiles = [];
        if (in_array(strtolower($fileData['extension']), ['jpg', 'jpeg', 'png', 'gif', 'bmp'])) {
            $disk = Storage::disk($this->disk->value);
            $pathDirectory=$disk->path($directory);
            $originalFilePath = $disk->path($fileData['path']);
            $image = Image::read($originalFilePath);
            $optimizedWithout=$pathDirectory.'/'.$fileData['name'].'.'.$fileData['extension'];
            $image->toWebp(80)->save($optimizedWithout.'.webp');
            $optimizedFiles['web']=$optimizedWithout.'.webp';

            foreach ([100,200 ,400 ,900] as $size) {
                if ($image->width() > $size) {
                    $image->scaleDown($size)->toWebp(80)->save($optimizedWithout.'-'.$size.'.webp');
                    $optimizedFiles[$size]=$optimizedWithout.'-'.$size.'.webp';
                }
            }
        }
        return $optimizedFiles;

    }

}
