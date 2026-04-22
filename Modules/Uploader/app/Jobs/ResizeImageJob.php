<?php

namespace Modules\Uploader\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Format;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Laravel\Facades\Image;
use Modules\Uploader\Models\UploadFile;

class ResizeImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const array RESIZE_WIDTH = [
        900, 600, 400, 150, 75,
    ];
    const int QUALITY = 70;

    /**
     * Create a new job instance.
     */
    public function __construct(protected UploadFile $file)
    {
        $this->onQueue('imageresize');
        $this->onConnection('sync');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $imageModel = $this->file;
        $disk = $imageModel->disk;
        $path = $imageModel->path;

        $fullPath = Storage::disk($disk)->get($path);
        $image = Image::decodeBinary($fullPath);
        $resizedImages = $this->resize($image);

        $storedImage = $this->storeInDisk($resizedImages, $path, $disk);
        $imageModel->update(['other' => $storedImage]);

    }

    /**
     * @param ImageInterface $image
     * @return array
     */
    public function resize(ImageInterface $image): array
    {
        $resizedImages = [];
        $widths = collect([$image->width(), ...self::RESIZE_WIDTH])->unique()->toArray();
        foreach ($widths as $w) {
            if ($image->width() >= $w) {
                $resizedImages[$w] = $image->scale($w)->encodeUsingFormat(Format::WEBP, self::QUALITY);
            }
        }
        return $resizedImages;
    }


    public function storeInDisk(array $images, $path, $disk): array
    {
        $preparedImage = [];
        $storage= Storage::disk($disk);
        $dir = $this->makeDir($storage , $path);

        foreach ($images as $w => $image) {
            $newPath = $dir . $w . '.65.webp';
            $storage->put($newPath, $image);
            $preparedImage[] = [
                'width' => $w,
                'path'  => $newPath,
            ];
        }
        return $preparedImage;
    }

    public function makeDir(Filesystem $storage ,string $path): string
    {
       $newPath = str_replace('uploads', 'client', $path);

        $before = Str::beforeLast($newPath, '/');
        $after  = Str::afterLast($newPath, '/');
        if (!$storage->exists($before)) $storage->makeDirectory($before);

        return $newPath;
    }
}
