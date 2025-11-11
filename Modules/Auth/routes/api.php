<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Api\Auth\CheckUserController;
use Modules\Auth\Http\Controllers\Api\Auth\VerificationCodeController;

Route::middleware([])->prefix('auth')->name('auth.')->group(function () {
    Route::post('check-user', [CheckUserController::class ,'checkUser'])->name('check-user')/*->middleware(['throttle:5,1'])*/;
    Route::post('send-code', [VerificationCodeController::class ,'send'])->name('send-code')/*->middleware(['throttle:2,1'])*/;
});
