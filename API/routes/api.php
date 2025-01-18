<?php

use App\Http\Controllers\LoginController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\CountryController;



Route::post('register', [LoginController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

Route::middleware(['jwt'])->group(function () {
    Route::get('user', [LoginController::class, 'getUser']);
    Route::post('logout', [LoginController::class, 'logout']);
});