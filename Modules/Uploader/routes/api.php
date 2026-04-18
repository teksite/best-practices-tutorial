<?php

use Illuminate\Support\Facades\Route;
use Modules\Uploader\Http\Controllers\UploaderController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('uploaders', UploaderController::class)->names('uploader');
});
