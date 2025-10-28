<?php

use App\Modules\Usuarios\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/**
 * Rotas do Módulo de Usuários
 *
 * Todas as rotas deste arquivo já estão prefixadas com /api
 * devido à configuração do RouteServiceProvider
 */

// ============================
// Rotas públicas (sem token)
// ============================
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// ============================
// Rotas protegidas (com token)
// ============================
Route::middleware('auth:api')->group(function () {
    // Autenticação
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);
    Route::get('auth/me', [AuthController::class, 'me']);
});
