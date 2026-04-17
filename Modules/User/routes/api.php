<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\Api\V1\Preferences\PreferencesController;




Route::prefix('v1/panel')->name('v1.auth.')->group(function () {


    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('notification-preferences')->name('notification_preferences.')->group(function () {
            Route::get("/", [PreferencesController::class, 'index'])->name('get');
            Route::post("/", [PreferencesController::class, 'update'])->name('update');
        });
    });

});
