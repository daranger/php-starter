<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Core\Router;
use App\Http\Middleware\CsrfMiddleware;

Router::group(['middleware' => [CsrfMiddleware::class]], function () {
    // Авторизация
    Router::post('/api/auth/login', [AuthController::class, 'login']);
    Router::post('/api/auth/verify-2fa', [AuthController::class, 'verify2fa']);
    Router::post('/api/auth/register', [AuthController::class, 'register']);
    Router::post('/api/auth/logout', [AuthController::class, 'logout']);
    Router::post('/api/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Router::post('/api/auth/reset-password', [AuthController::class, 'resetPassword']);

    // Google OAuth
    Router::get('/api/auth/google', [AuthController::class, 'googleRedirect']);
    Router::get('/api/auth/google/callback', [AuthController::class, 'googleCallback']);
    Router::post('/api/auth/google/redirect', [AuthController::class, 'googleRedirect']);

    // Профиль и настройки
    Router::post('/api/profile/password', [ProfileController::class, 'updatePassword']);
    Router::post('/api/profile/avatar', [ProfileController::class, 'uploadAvatar']);
    Router::post('/api/profile/2fa/enable', [ProfileController::class, 'enable2fa']);
    Router::post('/api/profile/2fa/disable', [ProfileController::class, 'disable2fa']);
});