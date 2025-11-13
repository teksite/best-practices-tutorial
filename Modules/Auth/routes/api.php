<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Api\Auth\CheckUserController;
use Modules\Auth\Http\Controllers\Api\Auth\VerificationCodeController;

Route::middleware([])->prefix('auth')->name('auth.')->group(function () {
    Route::post('check-user', [CheckUserController::class ,'checkUser'])->name('check-user')/*->middleware(['throttle:api.auth.check-user'])*/;
    Route::post('send-code', [VerificationCodeController::class ,'send'])->name('send-code')/*->middleware(['throttle:api.auth.send-code'])*/;
    Route::post('verify-code', [VerificationCodeController::class ,'verify'])->name('verify-code')/*->middleware(['throttle:api.auth.verify-code'])*/;
});
