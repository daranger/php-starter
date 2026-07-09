<?php

namespace App\Core;

use App\Exceptions\Handler;
use App\Http\Controllers\InstallController;
use App\Providers\AppServiceProvider;

class Application
{


    public function __construct()
    {
        $this->loadEnv(__DIR__ . '/../../.env');
        Handler::init();
        (new AppServiceProvider())->register();
    }

    public function run(): void
    {
        require_once __DIR__ . '/../../routes/api.php';
        require_once __DIR__ . '/../../routes/web.php';
        require_once __DIR__ . '/../../routes/admin.php';

        $request = Request::capture();

        if (($_ENV['APP_INSTALLED'] ?? 'true') === 'false') {
            $controller = new InstallController();
            $response = $controller->handle($request);
            $response->send();
            return;
        }
        $response = Router::dispatch($request);
        $response->send();
    }

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

}