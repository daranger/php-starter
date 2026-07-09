<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\Handler;

use PDO;
use Exception;

class Application
{


    public function __construct()
    {
        // 1. Загружаем переменные окружения
        $this->loadEnv(__DIR__ . '/../../.env');

        // 2. Инициализируем глобальный обработчик ошибок
        Handler::init();


        $this->registerShutdownHandler();

        // 4. Инициализируем и настраиваем DI Контейнер
        $this->bootstrapContainer();
    }

    public function run(): void
    {
        // Подгружаем карты маршрутов
        require_once __DIR__ . '/../../routes/api.php';
        require_once __DIR__ . '/../../routes/web.php';
        require_once __DIR__ . '/../../routes/admin.php';

        $request = Request::capture();

        if (($_ENV['APP_INSTALLED'] ?? 'true') === 'false') {
            $controller = new \App\Http\Controllers\InstallController();
            $response = $controller->handle($request);
            $response->send();
            return;
        }


        try {
            $response = Router::dispatch($request);
            $response->send();
        } catch (Exception $e) {
            if (php_sapi_name() !== 'cli') {
                $code = (int)$e->getCode();
                if ($code < 100 || $code > 599) {
                    $code = 500;
                }
                http_response_code($code);
            }

            // Если упал HTML-роут — отдаем красивую ошибку, если API — JSON
            $errorUri = $request->path();
            if (str_starts_with($errorUri, '/api/')) {
                echo json_encode([
                    'success' => false,
                    'error'   => $e->getMessage()
                ]);
            } else {
                Handler::init()->render($e);
            }
        }
    }



    /**
     * Конфигурация и наполнение DI Контейнера системными сервисами
     */
    private function bootstrapContainer(): void
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

    /**
     * Легковесный парсер .env
     */
    private function loadEnv(string $path): void
    {
        if (!is_file($path)) {
            $_ENV['APP_INSTALLED'] = 'false';
            return;
        }
        
        $_ENV['APP_INSTALLED'] = 'true';

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                $value = trim($value, '"\'');

                $_ENV[$key] = $value;
            }
        }

    }

    private function registerShutdownHandler(): void
    {
        register_shutdown_function(function () {
            $error = error_get_last();
            if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                if (php_sapi_name() !== 'cli') {
                    http_response_code(500);
                }
                echo json_encode([
                    'success' => false,
                    'error'   => 'Internal Server Error',
                    'details' => setting('panel_env', 'production') === 'development' ? $error['message'] : 'Произошла критическая ошибка'
                ]);
            }
        });
    }
}