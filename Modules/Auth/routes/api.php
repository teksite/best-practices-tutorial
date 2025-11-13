<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Api\Auth\AuthenticationController;
use Modules\Auth\Http\Controllers\Api\Auth\VerificationController;

Route::middleware([])->prefix('auth')->name('auth.')->group(function () {
    Route::post('check-user', [AuthenticationController::class ,'checkUser'])->name('check-user')/*->middleware(['throttle:api.auth.check-user'])*/;
    Route::post('send-code', [VerificationController::class ,'send'])->name('send-code')/*->middleware(['throttle:api.auth.send-code'])*/;
    Route::post('verify-code', [VerificationController::class ,'verify'])->name('verify-code')/*->middleware(['throttle:api.auth.verify-code'])*/;
    Route::post('register', [AuthenticationController::class ,'register'])->name('register')/*->middleware(['throttle:api.auth.verify-code'])*/;
});
