<?php

use Illuminate\Support\Facades\Route;
use Modules\Uploader\Http\Controllers\Api\Uploader\UploaderController;


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('uploader', [UploaderController::class ,'uploader'])->name('uploader');
});
