<?php

use App\Core\Firewall;

if (!function_exists('env')) {
    /**
     * Получает значение переменной окружения из $_ENV с приведением типов.
     */
    function env(string $key, mixed $default = null): mixed
    {
        if (!isset($_ENV[$key])) {
            return $default;
        }

        $value = $_ENV[$key];

        // Корректно приводим строковые булевы и null типы
        switch (strtolower($value)) {
            case 'true':
                return true;
            case 'false':
                return false;
            case 'null':
                return null;
            case 'empty':
                return '';
        }

        return $value;
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key);
        $file = array_shift($parts);
        $path = __DIR__ . "/../../config/{$file}.php";
        
        if (!file_exists($path)) {
            return $default;
        }
        
        $config = require $path;
        
        foreach ($parts as $part) {
            if (!is_array($config) || !array_key_exists($part, $config)) {
                return $default;
            }
            $config = $config[$part];
        }
        
        return $config;
    }
}

if (!function_exists('__')) {
    function __(string $key): string
    {
        $lang = $_COOKIE['lang'] ?? config('app.locale', 'ru');
        
        // Защита от LFI: проверяем, что язык есть в доступных
        $availableLocales = config('app.available_locales', ['ru' => 'Русский']);
        if (!array_key_exists($lang, $availableLocales)) {
            $lang = config('app.locale', 'ru');
        }
        
        static $translations = [];
        if (!isset($translations[$lang])) {
            $path = __DIR__ . "/../../resources/lang/{$lang}.php";
            $translations[$lang] = file_exists($path) ? require $path : [];
        }
        
        return $translations[$lang][$key] ?? $key;
    }
}

if (!function_exists('admin')) {
    function admin(): bool
    {
        $userId = \App\Core\Session::get('user_id');
        if (!$userId) return false;

        static $isAdmin = null;
        if ($isAdmin !== null) return $isAdmin;

        $user = \App\Core\Db::query("SELECT role FROM users WHERE id = " . (int)$userId)->fetchObject();
        $isAdmin = ($user && $user->role === 'admin');
        
        return $isAdmin;
    }
}


function view(string $_view_path, array $_data = []): string
{
    // ... твой код проверок 404 / 401 / 403 ...

    extract($_data, EXTR_SKIP);
    ob_start();
    include __DIR__ . "/../../resources/views/" . str_replace('.', '/', $_view_path) . ".php";
    $content = ob_get_clean();

    // 1. Твоя проверка на PJAX (если нужна для совместимости)
    if (Firewall::$pjax) {
        header("X-Robots-Tag: noindex, nofollow");
        header('Content-Type: application/json');
        return json_encode([
            'title' => $title ?? '',
            'content' => $content,
            'update' => $update ?? 0,
            'type' => $page_type ?? 'page'
        ]);
    }

    // 2. Обработка кастомной AJAX-навигации
    $isAjaxNav = (isset($_SERVER['HTTP_X_AJAX_NAV']) && $_SERVER['HTTP_X_AJAX_NAV'] === '1') ||
                 (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
                 (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

    if ($isAjaxNav) {
        header('Vary: X-AJAX-Nav, X-Requested-With, Accept');
        header('Content-Type: application/json; charset=utf-8');
        return json_encode([
            'title' => $title ?? 'PHP Starter Kit',
            'content' => $content
        ], JSON_THROW_ON_ERROR);
    }

    // 3. Обычный полный рендеринг для прямой перезагрузки страницы в браузере
    header('Vary: X-AJAX-Nav, X-Requested-With, Accept');
    
    if ($_data['no_layout'] ?? false) {
        return $content;
    }

    ob_start();
    include __DIR__ . '/../../resources/views/layout.php';
    return ob_get_clean();
}

/**
 * Retrieves a setting value from the database.
 * Statically caches results during the request lifecycle.
 */
function setting(string $key, $default = null)
{
    static $settingsCache = null;

    if ($settingsCache === null) {
        try {
            $rows = \App\Core\Db::query("SELECT setting_key, setting_value FROM settings");
            $settingsCache = [];
            foreach ($rows as $r) {
                $settingsCache[$r['setting_key']] = $r['setting_value'];
            }
        } catch (\Throwable $e) {
            $settingsCache = [];
        }
    }

    return $settingsCache[$key] ?? $default;
}
