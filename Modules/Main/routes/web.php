<?php

use Illuminate\Support\Facades\Route;
use Modules\Main\Http\Controllers\MainController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('mains', MainController::class)->names('main');
});
