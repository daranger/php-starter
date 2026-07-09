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
        (new \App\Providers\AppServiceProvider())->register();
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


        $response = Router::dispatch($request);
        $response->send();
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