<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/', function (Request $request) {
    dd($request->userAgent(), $request->ip(),$request->host() , $request);
});
