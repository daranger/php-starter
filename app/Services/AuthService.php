<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UserRepository;
use App\Models\User;
use App\Core\Session;
use Exception;

class AuthService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly MailService $mailService,
        private readonly \RobThree\Auth\TwoFactorAuth $tfa,
        private readonly \PDO $db
    ) {}

    public function sendPasswordResetLink(string $email): bool
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            return false;
        }

        $token = bin2hex(random_bytes(32));
        $stmt = $this->db->prepare("INSERT INTO password_resets (email, token) VALUES (:email, :token)");
        $stmt->execute(['email' => $email, 'token' => password_hash($token, PASSWORD_DEFAULT)]);

        $resetLink = setting('app_url', 'http://localhost:8000') . "/api/auth/reset-password?token={$token}&email=" . urlencode($email);
        $body = "Для сброса пароля перейдите по ссылке: <a href='{$resetLink}'>Сбросить пароль</a>";

        return $this->mailService->send($email, 'Восстановление пароля', $body);
    }

    public function resetPassword(string $email, string $token, string $newPassword): bool
    {
        $stmt = $this->db->prepare("SELECT token FROM password_resets WHERE email = :email ORDER BY created_at DESC LIMIT 1");
        $stmt->execute(['email' => $email]);
        $record = $stmt->fetch();

        if (!$record || !password_verify($token, $record['token'])) {
            return false;
        }

        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            return false;
        }

        $this->userRepository->update($user->id, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);

        $this->db->prepare("DELETE FROM password_resets WHERE email = :email")->execute(['email' => $email]);
        return true;
    }

    public function handleGoogleCallback(string $code): ?User
    {
        $clientId = setting('google_client_id');
        $clientSecret = setting('google_client_secret');
        $redirectUri = setting('app_url', 'http://localhost:8000') . '/api/auth/google/callback';

        $tokenContext = stream_context_create([
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query([
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                    'redirect_uri'  => $redirectUri,
                    'grant_type'    => 'authorization_code',
                    'code'          => $code,
                ]),
            ],
        ]);

        $tokenResponse = @file_get_contents('https://oauth2.googleapis.com/token', false, $tokenContext);
        if (!$tokenResponse) return null;

        $tokenData = json_decode($tokenResponse, true);
        if (empty($tokenData['access_token'])) return null;

        $userContext = stream_context_create([
            'http' => [
                'header' => "Authorization: Bearer {$tokenData['access_token']}\r\n",
                'method' => 'GET',
            ],
        ]);

        $userResponse = @file_get_contents('https://www.googleapis.com/oauth2/v2/userinfo', false, $userContext);
        if (!$userResponse) return null;

        $googleUser = json_decode($userResponse, true);
        if (empty($googleUser['email']) || empty($googleUser['id'])) return null;

        // Поиск пользователя по email или google_id
        $user = $this->userRepository->findByEmail($googleUser['email']);
        
        if ($user) {
            // Обновляем google_id, если он не был установлен
            if (!$user->google_id) {
                $this->userRepository->update($user->id, ['google_id' => $googleUser['id']]);
            }
        } else {
            // Создаем нового пользователя
            $userId = $this->userRepository->create([
                'email' => $googleUser['email'],
                'password' => null,
                'name' => $googleUser['name'] ?? 'User',
                'google_id' => $googleUser['id'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $user = $this->userRepository->find($userId);
        }

        $this->login($user);
        return $user;
    }

    public function attempt(string $email, string $password): bool
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user->password)) {
            return false;
        }

        // Если включена 2FA
        if ($user->two_factor_secret) {
            Session::set('2fa_pending_user_id', $user->id);
            return true; // Требуется 2FA
        }

        $this->login($user);
        return true;
    }

    public function verify2FA(int $userId, string $code): bool
    {
        $user = $this->userRepository->find($userId);
        if (!$user || !$user->two_factor_secret) {
            return false;
        }

        if ($this->tfa->verifyCode($user->two_factor_secret, $code)) {
            Session::remove('2fa_pending_user_id');
            $this->login($user);
            return true;
        }

        return false;
    }

    public function register(array $data): User
    {
        if ($this->userRepository->findByEmail($data['email'])) {
            throw new Exception("Пользователь с таким email уже существует.");
        }

        $userId = $this->userRepository->create([
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'name' => $data['name'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $user = $this->userRepository->find($userId);
        $this->login($user);

        return $user;
    }

    public function login(User $user): void
    {
        Session::regenerate();
        Session::set('user_id', $user->id);
    }

    public function logout(): void
    {
        Session::destroy();
    }
}
