<?php

declare(strict_types=1);

namespace App\Providers;

use App\Core\Container;
use App\Core\DatabaseManagerInterface;
use App\Core\MySQLManager;
use App\Core\PostgreSQLManager;
use PDO;

class AppServiceProvider
{
    public function register(): void
    {
        $container = Container::getInstance();

        // 1. Идеальное решение проблемы типов: биндим Predis\ClientInterface
        $container->bind(\Predis\ClientInterface::class, function () {
            return new \Predis\Client([
                'scheme' => 'tcp',
                'host'   => env('REDIS_HOST', '127.0.0.1'),
                'port'   => (int)env('REDIS_PORT', 6379),
            ]);
        }, true);

        // Алиас для обратной совместимости, если в коде где-то остался тайпхинт \Redis
        $container->bind(\Redis::class, function ($c) {
            return $c->make(\Predis\ClientInterface::class);
        }, true);

        // Инициализируем инстанс через интерфейс Predis
        $redis = $container->make(\Predis\ClientInterface::class);

        // Делаем инстанс доступным через global $redis для старых узлов (например, RateLimiter)
        global $redis;
        $GLOBALS['redis'] = $redis;

        // 2. Формируем DSN и регистрируем PDO с использованием хелпера env()
        $driver = env('DB_DRIVER', 'mysql');
        $host   = env('DB_HOST', '127.0.0.1');
        $port   = env('DB_PORT', '3306');
        $dbname = env('DB_NAME', 'php-starter');
        $user   = env('DB_USER', 'root');
        $pass   = env('DB_PASS', '');

        $dsn = "{$driver}:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        $container->bind(\PDO::class, function () use ($dsn, $user, $pass) {
            return new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }, true);

        // 3. Привязываем интерфейс СУБД к конкретной реализации на основе .env
        $dbManagerClass = ($driver === 'pgsql') ? PostgreSQLManager::class : MySQLManager::class;
        $container->bind(DatabaseManagerInterface::class, $dbManagerClass, true);

        // 4. Двухфакторная аутентификация
        $container->bind(\RobThree\Auth\TwoFactorAuth::class, function () {
            $qrProvider = new \RobThree\Auth\Providers\Qr\QRServerProvider();
            return new \RobThree\Auth\TwoFactorAuth($qrProvider, setting('app_name', 'PHP Starter Kit'));
        });
    }
}
