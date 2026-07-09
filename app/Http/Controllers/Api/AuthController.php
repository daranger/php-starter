<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Services\AuthService;

class AuthController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly \App\Core\RateLimiter $rateLimiter
    ) {}

    public function login(Request $request): Response
    {
        $email = (string) $request->input('email');
        $password = (string) $request->input('password');
        $code = (string) $request->input('code');

        if (empty($email) || empty($password)) {
            return Response::json(['error' => 'Заполните все поля'], 422);
        }

        $this->rateLimiter->attempt('login', 5, 60); // 5 попыток в минуту

        $success = $this->authService->attempt($email, $password);

        if (!$success) {
            return Response::json(['error' => 'Неверный логин или пароль'], 401);
        }

        if (Session::has('2fa_pending_user_id')) {
            if (empty($code)) {
                return Response::json(['requires_2fa' => true]);
            }

            $userId = Session::get('2fa_pending_user_id');
            
            $this->rateLimiter->attempt('verify2fa', 5, 60, 'user_' . $userId);
            
            $verifySuccess = $this->authService->verify2FA((int)$userId, $code);

            if (!$verifySuccess) {
                return Response::json(['error' => 'Неверный код 2FA'], 401);
            }
        }

        return Response::json(['success' => true]);
    }

    public function verify2fa(Request $request): Response
    {
        $code = (string) $request->input('code');

        if (empty($code)) {
            return Response::json(['error' => 'Введите код'], 422);
        }

        $userId = Session::get('2fa_pending_user_id');
        if (!$userId) {
            return Response::json(['error' => 'Сессия устарела. Войдите заново.'], 401);
        }

        $this->rateLimiter->attempt('verify2fa', 5, 60, 'user_' . $userId);

        $success = $this->authService->verify2FA((int)$userId, $code);

        if (!$success) {
            return Response::json(['error' => 'Неверный код'], 401);
        }

        return Response::json(['success' => true]);
    }

    public function register(Request $request): Response
    {
        $email = (string) $request->input('email');
        $password = (string) $request->input('password');

        if (empty($email) || empty($password)) {
            return Response::json(['error' => 'Заполните все обязательные поля'], 422);
        }

        if (strlen($password) < 6) {
            return Response::json(['error' => 'Пароль должен быть не менее 6 символов'], 422);
        }

        $this->rateLimiter->attempt('register', 3, 60); // 3 попытки регистрации в минуту

        try {
            $this->authService->register([
                'email' => $email,
                'password' => $password,
                'name' => null
            ]);
            return Response::json(['success' => true]);
        } catch (\Exception $e) {
            return Response::json(['error' => $e->getMessage()], 422);
        }
    }

    public function logout(): Response
    {
        $this->authService->logout();
        return Response::redirect('/');
    }

    public function forgotPassword(Request $request): Response
    {
        $email = (string) $request->input('email');

        if (empty($email)) {
            return Response::json(['error' => 'Введите email'], 422);
        }

        $this->rateLimiter->attempt('forgot_password', 3, 60); // 3 попытки восстановления в минуту

        $this->authService->sendPasswordResetLink($email);

        // Всегда возвращаем success, чтобы не раскрывать существующие email'ы
        return Response::json(['success' => true, 'message' => 'Если email найден, на него отправлена ссылка.']);
    }

    public function resetPassword(Request $request): Response
    {
        $email = (string) $request->input('email');
        $token = (string) $request->input('token');
        $password = (string) $request->input('password');
        $passwordConfirm = (string) $request->input('password_confirm');

        if (empty($email) || empty($token) || empty($password)) {
            return Response::json(['error' => 'Заполните все поля'], 422);
        }

        if ($password !== $passwordConfirm) {
            return Response::json(['error' => 'Пароли не совпадают'], 422);
        }

        if ($this->authService->resetPassword($email, $token, $password)) {
            return Response::json(['success' => true]);
        }

        return Response::json(['error' => 'Недействительный токен или ссылка устарела'], 400);
    }

    public function googleRedirect(): Response
    {
        $clientId = setting('google_client_id');
        $redirectUri = setting('app_url', 'http://localhost:8000') . '/api/auth/google/callback';
        $scope = 'email profile';
        
        $state = bin2hex(random_bytes(16));
        Session::set('oauth_state', $state);
        
        $url = "https://accounts.google.com/o/oauth2/v2/auth?client_id={$clientId}&redirect_uri={$redirectUri}&response_type=code&scope={$scope}&access_type=online&state={$state}";
        
        return Response::redirect($url);
    }

    public function googleCallback(Request $request): Response
    {
        $code = (string) $request->input('code');
        $state = (string) $request->input('state');
        $savedState = Session::get('oauth_state');

        if (empty($code) || empty($state) || $state !== $savedState) {
            return new Response("<script>alert('Ошибка авторизации Google или неверный state'); window.close();</script>", 200, ['Content-Type' => 'text/html; charset=utf-8']);
        }
        
        Session::remove('oauth_state');

        $user = $this->authService->handleGoogleCallback($code);

        if ($user) {
            return new Response("<script>window.opener.location.reload(); window.close();</script>", 200, ['Content-Type' => 'text/html; charset=utf-8']);
        }

        return new Response("<script>alert('Не удалось получить данные от Google'); window.close();</script>", 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }
}
