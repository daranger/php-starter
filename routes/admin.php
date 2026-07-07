<?php

declare(strict_types=1);

use App\Admin\Controllers\TemplateController;
use App\Core\Router;
use App\Core\Request;
use App\Core\Response;
use App\Admin\Controllers\AdminController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CsrfMiddleware;

// Группа админки защищена CsrfMiddleware и AdminMiddleware
Router::group(['middleware' => [CsrfMiddleware::class, AdminMiddleware::class]], function () {

    // Вспомогательная функция для вызова AdminController и рендера
    $adminCall = function (string $method) {
        return function (Request $request) use ($method): Response {
            $controller = new AdminController();
            $res = $controller->$method($request->path());

            // Если метод не возвращает вьюху, он мог вернуть просто данные
            if (!isset($res['view'])) {
                if (isset($res['data'])) {
                    return Response::json($res['data']);
                }
                return new Response(''); 
            }

            // Иначе рендерим админский layout. 
            // Передаем view, чтобы layout.php знал, что рендерить внутри.
            $data = array_merge($res['data'], ['view' => $res['view'], 'no_layout' => true]);
            $html = view('admin.layout', $data);
            return new Response($html);
        };
    };

    Router::get('/admin', $adminCall('index'));
    Router::get('/admin/', $adminCall('index'));
    Router::get('/admin/stream_logs', $adminCall('streamLogs'));
    Router::get('/admin/system_stats', $adminCall('stats'));
    
    // Глобальные настройки
    Router::get('/admin/settings', function(Request $request) {
        $controller = new \App\Admin\Controllers\SettingsController();
        $res = $controller->index();
        $data = array_merge($res['data'], ['view' => $res['view'], 'no_layout' => true]);
        return new Response(view('admin.layout', $data));
    });
    Router::post('/admin/settings/save', function(Request $request) {
        $controller = new \App\Admin\Controllers\SettingsController();
        return new Response((string)$controller->save());
    });
    Router::post('/admin/settings/upload-favicon', function(Request $request) {
        if (isset($_FILES['favicon']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
            $tmpName = $_FILES['favicon']['tmp_name'];
            $target = __DIR__ . '/../public/assets/favicon.png';
            move_uploaded_file($tmpName, $target);
            return Response::json(['success' => true]);
        }
        return Response::json(['success' => false, 'error' => 'Upload failed']);
    });

    // Редактор шаблонов
    Router::get('/admin/templates', function(Request $request) {
        $controller = new TemplateController();
        $res = $controller->index();
        $data = array_merge($res['data'], ['view' => $res['view'], 'no_layout' => true]);
        return new Response(view('admin.layout', $data));
    });
    Router::post('/admin/templates/save', function(Request $request) {
        $controller = new TemplateController();
        return new Response((string)$controller->save());
    });
    Router::post('/admin/templates/restore', function(Request $request) {
        $controller = new TemplateController();
        return new Response((string)$controller->restore());
    });

    // Динамические роуты для таблиц
    Router::get('/admin/{table}', $adminCall('list'));
    Router::get('/admin/{table}/edit/{id}', $adminCall('edit'));
    Router::post('/admin/{table}/edit/{id}', $adminCall('edit'));
    Router::get('/admin/{table}/delete/{id}', $adminCall('delete'));
    Router::get('/admin/{table}/spam/{id}', $adminCall('spam'));
});
