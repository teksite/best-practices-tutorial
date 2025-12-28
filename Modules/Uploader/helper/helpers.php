<?php


use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Modules\Uploader\Enums\DiskType;
use Modules\Uploader\Models\Media;
use Modules\Uploader\Services\FileUploadService;

if (!function_exists('uploadFile')) {

    /**
     * @param UploadedFile $file
     * @param array $options
     * @param Model|null $relatedModel
     *
     * @return Media
     * @throws Exception
     */
    function uploadFile(UploadedFile $file, array $options = [], null|Model $relatedModel = null): Media
    {
        return FileUploadService::resolve($options['disk'] ?? DiskType::PUBLIC)->upload($file, $options['directory'] ?? null, $options['name'] ?? null, $options['overwrite'] ?? false, $relatedModel);
    }
}



if (!function_exists('deleteFile')) {


    /**
     * @param string $path
     * @param DiskType $disk
     *
     * @return void
     */
    function deleteFile(string $path, DiskType $disk =DiskType::PUBLIC): void
    {
         FileUploadService::resolve($disk )->delete($path);
    }
}
