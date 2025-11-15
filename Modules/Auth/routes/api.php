<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Api\Auth\AuthenticationController;
use Modules\Auth\Http\Controllers\Api\Auth\VerificationController;

Route::middleware([])->prefix('auth')->name('auth.')->group(function () {
    Route::post('check-user', [AuthenticationController::class ,'checkUser'])->name('check-user')/*->middleware(['throttle:api.auth.check-user'])*/;
    Route::post('send-code', [VerificationController::class ,'send'])->name('send-code')/*->middleware(['throttle:api.auth.send-code'])*/;
    Route::post('verify-code', [VerificationController::class ,'verify'])->name('verify-code')/*->middleware(['throttle:api.auth.verify-code'])*/;
    Route::post('register', [AuthenticationController::class ,'register'])->name('register')/*->middleware(['throttle:api.auth.verify-code'])*/;
    Route::post('login', [AuthenticationController::class ,'login'])->name('login')/*->middleware(['throttle:api.auth.verify-code'])*/;
    Route::post('forget-password', [AuthenticationController::class ,'forget'])->name('forget-password')/*->middleware(['throttle:api.auth.forget-password'])*/;
});


Route::middleware(['auth:sanctum'])->prefix('auth')->name('auth.')->group(function () {
    Route::get('who', [AuthenticationController::class ,'who'])->name('who')/*->middleware(['throttle:api.auth.who'])*/;
    Route::get('verify', [AuthenticationController::class ,'who'])->name('who')/*->middleware(['throttle:api.auth.who'])*/;
});

