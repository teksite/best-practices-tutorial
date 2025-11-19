<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Api\Auth\AuthenticationController;
use Modules\Auth\Http\Controllers\Api\Auth\VerificationController;
use Modules\Auth\Http\Middleware\EnsureUserVerifiedMiddleware;
use Modules\Auth\Notifications\WelcomeNotification;
use Modules\Main\Services\ApiResponse;

Route::middleware([])->prefix('auth')->name('auth.')->group(function () {
    Route::post('send-code', [VerificationController::class, 'send'])->name('send-code')/*->middleware(['throttle:api.auth.send-code'])*/;
    Route::post('verify-code', [VerificationController::class, 'verify'])->name('verify-code')/*->middleware(['throttle:api.auth.verify-code'])*/;
    Route::post('register', [AuthenticationController::class, 'register'])->name('register')/*->middleware(['throttle:api.auth.verify-code'])*/
    ;
    Route::post('login', [AuthenticationController::class, 'login'])->name('login')/*->middleware(['throttle:api.auth.verify-code'])*/
    ;
    Route::post('forget-password', [AuthenticationController::class, 'forget'])->name('forget-password')/*->middleware(['throttle:api.auth.forget-password'])*/
    ;
});


Route::middleware(['auth:sanctum'])->prefix('auth')->name('auth.')->group(function () {
    Route::post('check-user', [AuthenticationController::class, 'checkUser'])->middleware([EnsureUserVerifiedMiddleware::class])->name('check-user')/*->middleware(['throttle:api.auth.check-user'])*/
    ;
    Route::get('who', [AuthenticationController::class, 'who'])->name('who')/*->middleware(['throttle:api.auth.who'])*/
    ;
    Route::post('verify', [AuthenticationController::class, 'verify'])->name('verify')/*->middleware(['throttle:api.auth.who'])*/
    ;
    Route::post('change', [AuthenticationController::class, 'change'])->name('change')/*->middleware(['throttle:api.auth.who'])*/
    ;
});
Route::middleware([])->prefix('notification')->name('notification.')->group(function () {

    Route::get('send-welcome', function (\Illuminate\Http\Request $request) {
        $user = \Modules\User\Models\User::find(1);
//        $res=$user->notify(new WelcomeNotification());
//        Http::withHeaders([
//            "Content-Type: text/plain" .
//            "Title: Unauthorized access detected" .
//            "Priority: urgent" .
//            "Tags: warning,skull",
//            'content' => 'Remote access to phils-laptop detected. Act right away.'
//        ])->post("https://ntfy.sh/lareon",[
//            'Remote access to phils-laptop detected. Act right away.'
//        ] );
        try {
            $user->notify(new WelcomeNotification());
            return ApiResponse::success();
        }catch (\Exception $e){
            Log::error('notification error: ' . $e->getMessage());
        }
    });
});

