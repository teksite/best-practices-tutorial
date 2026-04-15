<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Api\V1\Auth\CheckUserController;
use Modules\Auth\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use Modules\Auth\Http\Controllers\Api\V1\Auth\LoginController;
use Modules\Auth\Http\Controllers\Api\V1\Auth\RegisterController;
use Modules\Auth\Http\Controllers\Api\V1\Auth\VerificationCodeController;
use Modules\Auth\Http\Controllers\Api\V1\Auth\WhoAmIController;

Route::prefix('v1/auth')->name('v1.auth.')->group(function () {

    Route::Post("/check-user", [CheckUserController::class, 'check'])->name('check-user');

    Route::prefix('verification-code')->name('verification_code.')->group(function () {
        Route::Post("send", [VerificationCodeController::class, 'send',])->name('send')->middleware('throttle:check-user');
        Route::Post("verify", [VerificationCodeController::class, 'verify',])->name('verify')->middleware('throttle:send-verification-code');
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get("/who-am-i", [WhoAmIController::class, 'whoAmI'])->name('who-am-i');
    });

    Route::middleware(['guest'])->group(function () {
        Route::Post("/register", [RegisterController::class, 'store'])->name('register');
        Route::Post("/login", [LoginController::class, 'login'])->name('login');
        Route::Post("/forgot-password", [ForgotPasswordController::class, 'forgot'])->name('forgot-password');
    });


});
