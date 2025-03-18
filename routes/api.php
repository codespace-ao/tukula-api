<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


# API routes
Route::middleware('web')->prefix('v01')->group(function () {

    Route::get('/health', function () {
        return ['health' => 'Running OK'];
    });

    # -----------------------
    # AUTHENTICATION
    # -----------------------
    Route::prefix('auth')->group(function () {
        # OAUTH2    
        Route::get('/google', [LoginController::class, 'redirectToGoogle'])->name('auth.google');
        Route::get('/google/callback', [LoginController::class, 'handleGoogleCallback']);
    });

});