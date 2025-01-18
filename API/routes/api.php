<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\CountryController;



Route::post('register', [LoginController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);

Route::middleware(['jwt'])->group(function () {
    Route::get('getuser', [LoginController::class, 'getUser']);

    Route::prefix('users')->group(function (){
        Route::get('/',[UserController::class,'index']);
        Route::get('/{user}',[UserController::class,'show']);
        Route::get('/create',[UserController::class,'create']);
    });

    Route::post('logout', [LoginController::class, 'logout']);



});