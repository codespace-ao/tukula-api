<?php

use App\Http\Controllers\Auth\AuthController;
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

        Route::get('register', [AuthController::class, 'register']); // Nova rota para registro
        Route::get('login', [AuthController::class, 'login']); // Nova rota para login
    });

    // Nova rota para buscar os dados do usuário autenticado
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('user', [AuthController::class, 'getAuthenticatedUser']);
        Route::get('logout', [AuthController::class, 'logout']);
    });
});
