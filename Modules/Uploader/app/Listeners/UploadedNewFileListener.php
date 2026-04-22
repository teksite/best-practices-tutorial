<?php

namespace Modules\Uploader\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Uploader\Events\UploadedNewFileEvent;
use Modules\Uploader\Jobs\ResizeImageJob;

class UploadedNewFileListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(UploadedNewFileEvent $event): void
    {

        $mimeType = $event->file?->mime_type ?? null;
        if (is_null($mimeType)) return;
        $typeArray = explode('/', $mimeType);

        $type = strtolower(trim($typeArray[0]));
        $extension = strtolower(trim($typeArray[1]));


        if ($type == 'image') {
            dispatch(new ResizeImageJob($event->file));
        }
    }
}
