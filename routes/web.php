<?php
declare(strict_types=1);

use App\Core\Router;

// Общая группа (доступна всем)
Router::group(['middleware' => [\App\Http\Middleware\CsrfMiddleware::class]], function () {
    Router::get('/', function () {
        return view('home', ['title' => __('nav_home')]);
    })->name('home');

    Router::get('/faq', function () {
        return view('faq', ['title' => 'FAQ']);
    });

    Router::get('/components', function () {
        return view('components', ['title' => 'UI Components']);
    });

    Router::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index']);
});