<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AIController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Rotas públicas de recuperação de senha
Route::post('/forgot-password', [ProfileController::class, 'forgotPassword']);
Route::post('/reset-password', [ProfileController::class, 'resetPassword']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Perfil do usuário
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'changePassword']);

    Route::get('/admin/dashboard', [\App\Http\Controllers\DashboardController::class, 'getAdminData']);
    Route::get('/member/dashboard', [\App\Http\Controllers\DashboardController::class, 'getMemberData']);

    Route::post('/ai/generate-workout', [AIController::class, 'generateWorkout']);
    Route::apiResource('products', ProductController::class);
});
