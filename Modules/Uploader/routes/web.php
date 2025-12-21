<?php

use Illuminate\Support\Facades\Route;
use Modules\Uploader\Http\Controllers\Api\Uploader\UploaderController;

Route::middleware(['auth', 'verified'])->group(function () {
});
