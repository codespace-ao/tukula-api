<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('api/v01')->group(function () {

    Route::prefix('auth')->group(function () {
        # OAUTH2    
        Route::get('/google', [LoginController::class, 'redirectToGoogle'])->name('auth.google');
        Route::get('/google/callback', [LoginController::class, 'handleGoogleCallback']);
    });
});
