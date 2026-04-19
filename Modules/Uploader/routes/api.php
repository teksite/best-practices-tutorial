<?php

use Illuminate\Support\Facades\Route;
use Modules\Uploader\Http\Controllers\Api\V1\FileManagerController;
use Modules\Uploader\Http\Controllers\UploaderController;

Route::prefix('v1/file_manager')->name('v1.file_manager.')->group(function () {

        Route::post("/", [FileManagerController::class, 'upload'])->name('upload');
//        Route::post("/", [UploadFilesController::class, 'update'])->name('update');

});
