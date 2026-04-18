<?php

use Illuminate\Support\Facades\Route;
use Modules\Uploader\Http\Controllers\UploaderController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('uploaders', UploaderController::class)->names('uploader');
});
