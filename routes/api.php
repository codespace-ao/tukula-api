<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

# API routes
Route::prefix('v01')->group(function () {

    Route::get('/health', function () {
        return ['health' => 'Running OK'];
    });

    // Rota para verificar e-mail
    Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify')
    ;

    # Rotas para autenticação
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']); // Mudado de GET para POST
        Route::post('login', [AuthController::class, 'login']); // Mudado de GET para POST

        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update'); // Mudado de GET para POST
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email'); // Mudado de GET para POST
    });

    # Rotas para obter dados e realizar logout
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('user', [AuthController::class, 'getAuthenticatedUser']);
        Route::post('logout', [AuthController::class, 'logout']); // Mudado de GET para POST
    });
});
