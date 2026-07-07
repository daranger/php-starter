<?php
declare(strict_types=1);

use App\Core\Router;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\CsrfMiddleware;

// Общая группа (доступна всем)
Router::group(['middleware' => [CsrfMiddleware::class]], function () {
    Router::get('/', function () {
        return view('home', ['title' => __('nav_home')]);
    })->name('home');

    Router::get('/faq', function () {
        return view('faq', ['title' => 'FAQ']);
    });

    Router::get('/components', function () {
        return view('components', ['title' => 'UI Components']);
    });

    Router::get('/profile', [ProfileController::class, 'index']);
});