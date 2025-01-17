<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

require __DIR__.'/auth.php';


Route::resource('countries', App\Http\Controllers\Api\v1\CountryController::class)->except('edit', 'update', 'destroy');


Route::resource('countries', App\Http\Controllers\Api\v1\CountryController::class)->except('edit', 'update', 'destroy');


Route::resource('countries', App\Http\Controllers\Api\v1\CountryController::class)->except('edit', 'update', 'destroy');


Route::resource('countries', App\Http\Controllers\Api\v1\CountryController::class)->except('edit', 'update', 'destroy');


Route::resource('countries', App\Http\Controllers\Api\v1\CountryController::class)->except('edit', 'update', 'destroy');


Route::resource('countries', App\Http\Controllers\Api\v1\CountryController::class)->except('edit', 'update', 'destroy');
