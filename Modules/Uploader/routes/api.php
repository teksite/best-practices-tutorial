<?php

use Illuminate\Support\Facades\Route;
use Modules\Uploader\Http\Controllers\Api\V1\FileManagerController;
use Modules\Uploader\Http\Controllers\Api\V1\ChunkUploaderController;

Route::prefix('v1/file_manager')->name('v1.file_manager.')->group(function () {

        Route::get("/", [FileManagerController::class, 'index'])->name('index');
        Route::get("/{file}", [FileManagerController::class, 'show'])->name('show');
        Route::post("/", [FileManagerController::class, 'upload'])->name('upload');
        Route::post("/by-model", [FileManagerController::class, 'uploadByModel'])->name('upload.by.model');
        Route::delete("/{file}", [FileManagerController::class, 'delete'])->name('destroy');


    Route::post("/upload-chunk", [ChunkUploaderController::class , 'upload'])->name('upload-chunk');


});
