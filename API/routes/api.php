<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\v1\CountryController;



Route::prefix('v1')->group(function () {
    Route::get('/countries', [CountryController::class, 'index'])->name('country.index');
});