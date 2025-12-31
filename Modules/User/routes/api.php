<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\Api\Panel\Users\UsersController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::patch('/profile', [UsersController::class ,'update'])->name('profile.update');
});
