<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Contracts\MiddlewareInterface;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class CsrfMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next): Response
    {
        // Только для пишущих методов
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $token = $request->input('_csrf') ?? $request->header('X-CSRF-TOKEN');
            $sessionToken = Session::token();

            if (!$token || !hash_equals($sessionToken, $token)) {
                if ($request->isAjax()) {
                    return Response::json(['error' => 'CSRF token mismatch'], 419);
                }
                
                Session::flash('error', 'Срок действия страницы истек, попробуйте еще раз.');
                return Response::redirect($request->header('referer', '/'));
            }
        }

        // Для GET генерируем токен (чтобы он был в сессии)
        Session::token();

        return $next($request);
    }
}
