<?php

namespace Modules\Uploader\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Modules\Uploader\Enums\DiskType;
use Modules\Uploader\Models\Media;

class FileUploadService
{
    public function __construct(protected DiskType $disk = DiskType::LOCAL, protected ?string $directory = null)
    {
        if (is_null($this->directory) || trim($this->directory) === '') {
            $this->directory = 'uploader' . DIRECTORY_SEPARATOR . Carbon::now()->format('Y') . DIRECTORY_SEPARATOR . Carbon::now()->format('m');
        }
    }

    /**
     * @param UploadedFile $file
     * @param string|null $filename
     * @param Model|null $relatedModel
     * @return Model|Media
     * @throws \Exception
     */
    public function upload(UploadedFile $file, ?string $filename = null, ?Model $relatedModel = null): Model|Media
    {
        $extension = $file->getClientOriginalExtension();
        $size = $file->getSize();
        $mime_type = $file->getMimeType();
        $originalName = $file->getClientOriginalName();
        if ($filename) {
            $finalName = $this->fileName($filename, $extension);
            $path = $file->storeAs($this->directory, $finalName, [
                'disk' => $this->disk
            ]);
            $name = $finalName;

        } else {
            $path = $file->store($this->directory, [
                'disk' => $this->disk
            ]);
            $name = pathinfo($originalName, PATHINFO_FILENAME);

        }


        $media = Media::query()->create([
            'original_name' => $originalName,
            'name' => $name,
            'path' => $path,
            'mime_type' => $mime_type,
            'extension' => $extension,
            'size' => $size,
            'disk' => $this->disk->value,
        ]);
        if ($relatedModel) {
            if (!class_exists($relatedModel) && !method_exists($relatedModel, 'attachFile')) {
                throw new \Exception('please add HasAttachedFile trait');
            }
            $relatedModel->attachFile($media);
        }
        return $media;

    }

    public function fileName(string $filename, string $extension): string
    {
        $newFileName = $filename . '.' . $extension;
        $path = Storage::disk($this->disk)->path($this->directory . DIRECTORY_SEPARATOR . $newFileName);
        $i = 1;
        while (File::exists($path)) {
            $newFileName = $filename . '_' . $i . '.' . $extension;
            $path = Storage::disk($this->disk)->path($this->directory . DIRECTORY_SEPARATOR . $newFileName);
            $i++;
        }

        return $newFileName;

    }

}
