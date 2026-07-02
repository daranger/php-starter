<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use Exception;

class InstallerService
{
    /**
     * Выполняет установку приложения.
     * @return array ['success' => bool, 'error' => string]
     */
    public static function install(
        string $dbHost, 
        string $dbPort, 
        string $dbName, 
        string $dbUser, 
        string $dbPass, 
        string $adminEmail, 
        string $adminPassword
    ): array {
        try {
            // 1. Подключаемся к базе для проверки и создания таблиц
            $dsn = "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            // Создаем БД, если её нет (очищаем старую, чтобы не было мусора)
            $pdo->exec("DROP DATABASE IF EXISTS `$dbName`");
            $pdo->exec("CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$dbName`");



            // 3. Выполняем миграции
            $migrationsDir = __DIR__ . '/../../database/Migrations';
            if (is_dir($migrationsDir)) {
                $files = scandir($migrationsDir);
                sort($files);
                foreach ($files as $file) {
                    if (str_ends_with($file, '.sql')) {
                        try {
                            $sql = file_get_contents($migrationsDir . '/' . $file);
                            $pdo->exec($sql);
                        } catch (\PDOException $e) {
                            // Игнорируем ошибки: 42S01 (Таблица уже существует), 42S21 (Дубликат колонки)
                            if ($e->getCode() !== '42S01' && $e->getCode() !== '42S21') {
                                throw $e;
                            }
                        }
                    }
                }
            }

            // 4. Создаем админа
            $hash = password_hash($adminPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (email, name, password, role) VALUES (:email, :name, :password, 'admin') ON DUPLICATE KEY UPDATE password = :password, role = 'admin'");
            $name = explode('@', $adminEmail)[0];
            $stmt->execute([
                'email' => $adminEmail,
                'name' => $name,
                'password' => $hash,
            ]);

            // 5. Генерируем .env
            $envExamplePath = __DIR__ . '/../../.env.example';
            $envPath = __DIR__ . '/../../.env';
            
            if (!file_exists($envExamplePath)) {
                return ['success' => false, 'error' => '.env.example не найден'];
            }

            $envContent = file_get_contents($envExamplePath);
            $envContent = preg_replace('/^DB_HOST=.*$/m', "DB_HOST={$dbHost}", $envContent);
            $envContent = preg_replace('/^DB_PORT=.*$/m', "DB_PORT={$dbPort}", $envContent);
            $envContent = preg_replace('/^DB_NAME=.*$/m', "DB_NAME={$dbName}", $envContent);
            $envContent = preg_replace('/^DB_USER=.*$/m', "DB_USER={$dbUser}", $envContent);
            $envContent = preg_replace('/^DB_PASS=.*$/m', "DB_PASS={$dbPass}", $envContent);

            file_put_contents($envPath, $envContent);

            return ['success' => true];

        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
