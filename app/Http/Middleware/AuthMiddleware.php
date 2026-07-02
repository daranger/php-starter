<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Contracts\MiddlewareInterface;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class AuthMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next): Response
    {
        // Проверяем авторизацию через сессию
        $isLoggedIn = Session::get('user_id') !== null;

        if (!$isLoggedIn) {
            if ($request->isAjax()) {
                return Response::json(['error' => 'Unauthorized'], 401);
            }
            
            Session::flash('error', 'Вам необходимо авторизоваться.');
            return Response::redirect('/login');
        }

        return $next($request);
    }
}
