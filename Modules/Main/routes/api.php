<?php

use Illuminate\Support\Facades\Route;
use Modules\Main\Http\Controllers\MainController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('mains', MainController::class)->names('main');
});
