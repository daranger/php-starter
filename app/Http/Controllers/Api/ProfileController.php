<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\UserRepository;

class ProfileController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly \RobThree\Auth\TwoFactorAuth $tfa
    ) {}

    private function getUserId(): ?int
    {
        return Session::get('user_id');
    }

    public function updatePassword(Request $request): Response
    {
        $userId = $this->getUserId();
        if (!$userId) return Response::json(['error' => 'Unauthorized'], 401);

        $current = (string) $request->input('current_password');
        $new = (string) $request->input('new_password');
        $confirm = (string) $request->input('confirm_password');

        if (empty($current) || empty($new)) {
            return Response::json(['error' => 'Заполните все поля'], 422);
        }

        if ($new !== $confirm) {
            return Response::json(['error' => 'Новые пароли не совпадают'], 422);
        }

        $user = $this->userRepository->find($userId);
        if (!password_verify($current, $user->password)) {
            return Response::json(['error' => 'Текущий пароль неверен'], 422);
        }

        $this->userRepository->update($userId, [
            'password' => password_hash($new, PASSWORD_DEFAULT)
        ]);

        return Response::json(['success' => true]);
    }

    public function enable2fa(Request $request): Response
    {
        $userId = $this->getUserId();
        if (!$userId) return Response::json(['error' => 'Unauthorized'], 401);

        $code = (string) $request->input('code');
        $secret = Session::get('pending_2fa_secret');

        if (!$secret || empty($code)) {
            return Response::json(['error' => 'Неверный запрос'], 400);
        }

        if ($this->tfa->verifyCode($secret, $code)) {
            $this->userRepository->update($userId, [
                'two_factor_secret' => $secret
            ]);
            Session::remove('pending_2fa_secret');
            return Response::json(['success' => true]);
        }

        return Response::json(['error' => 'Неверный код'], 422);
    }

    public function disable2fa(Request $request): Response
    {
        $userId = $this->getUserId();
        if (!$userId) return Response::json(['error' => 'Unauthorized'], 401);

        $this->userRepository->update($userId, [
            'two_factor_secret' => null
        ]);

        return Response::json(['success' => true]);
    }

    public function uploadAvatar(Request $request): Response
    {
        $userId = $this->getUserId();
        if (!$userId) return Response::json(['error' => 'Unauthorized'], 401);

        $file = $request->file('avatar');
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return Response::json(['error' => 'Ошибка загрузки файла'], 422);
        }

        // Защита от DoS: ограничиваем размер файла (максимум 2 МБ)
        if ($file['size'] > 2 * 1024 * 1024) {
            return Response::json(['error' => 'Файл слишком большой. Максимальный размер 2 МБ'], 422);
        }

        $info = @getimagesize($file['tmp_name']);
        if (!$info) {
            return Response::json(['error' => 'Некорректное изображение'], 422);
        }

        $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP, IMAGETYPE_GIF];
        if (!in_array($info[2], $allowedTypes)) {
            return Response::json(['error' => 'Разрешены только JPEG, PNG, WEBP и GIF'], 422);
        }

        $uploadDir = __DIR__ . '/../../../../public/uploads/avatars';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Фиксированное имя файла: 1 пользователь = 1 файл на диске
        $ext = match ($info[2]) {
            IMAGETYPE_JPEG => 'jpg',
            IMAGETYPE_PNG => 'png',
            IMAGETYPE_WEBP => 'webp',
            IMAGETYPE_GIF => 'gif',
            default => 'png'
        };
        $filename = $userId . '.' . $ext;
        $targetPath = $uploadDir . '/' . $filename;

        // Удаляем старые аватары с другими расширениями, если есть
        foreach (['jpg', 'png', 'webp', 'gif'] as $oldExt) {
            if ($oldExt !== $ext && file_exists($uploadDir . '/' . $userId . '.' . $oldExt)) {
                @unlink($uploadDir . '/' . $userId . '.' . $oldExt);
            }
        }

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Добавляем ?v=time() чтобы сбросить кэш браузера при загрузке
            $avatarUrl = '/uploads/avatars/' . $filename . '?v=' . time();
            $this->userRepository->update($userId, [
                'avatar' => $avatarUrl
            ]);

            return Response::json(['success' => true, 'avatar_url' => $avatarUrl]);
        }

        return Response::json(['error' => 'Не удалось сохранить файл'], 500);
    }
}
