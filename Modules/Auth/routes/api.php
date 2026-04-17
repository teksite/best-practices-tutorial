<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Api\V1\Auth\CheckUserController;
use Modules\Auth\Http\Controllers\Api\V1\Auth\ForgotPasswordController;
use Modules\Auth\Http\Controllers\Api\V1\Auth\LoginController;
use Modules\Auth\Http\Controllers\Api\V1\Auth\RegisterController;
use Modules\Auth\Http\Controllers\Api\V1\Auth\VerificationCodeController;
use Modules\Auth\Http\Controllers\Api\V1\Auth\VerifyContactsController;
use Modules\Auth\Http\Controllers\Api\V1\Auth\WhoAmIController;
use Modules\Auth\Http\Middleware\EnsureContactsAreVerifiedMiddleware;

Route::post('v1/test', function () {
    $user = \Modules\User\Models\User::find(1);
    ((new \Modules\User\Services\NotificationPreferencesService)->getFilteredPreference($user));
    $user->notify(new \Modules\Auth\Notifications\WelcomeNotification());
});


Route::prefix('v1/auth')->name('v1.auth.')->group(function () {

    Route::post("/check-user", [CheckUserController::class, 'check'])->name('check-user');

    Route::prefix('verification-code')->name('verification_code.')->group(function () {
        Route::post("send", [VerificationCodeController::class, 'send',])->name('send')->middleware('throttle:check-user');
        Route::post("verify", [VerificationCodeController::class, 'verify',])->name('verify')->middleware('throttle:send-verification-code');
    });

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get("/who-am-i", [WhoAmIController::class, 'whoAmI'])->name('who-am-i')->middleware([EnsureContactsAreVerifiedMiddleware::class]);

        Route::post("/verify-contact", [VerifyContactsController::class, 'verify'])->name('verify-contact');
    });

    Route::middleware(['guest'])->group(function () {
        Route::post("/register", [RegisterController::class, 'store'])->name('register');
        Route::post("/login", [LoginController::class, 'login'])->name('login');
        Route::post("/forgot-password", [ForgotPasswordController::class, 'forgot'])->name('forgot-password');
    });


});
