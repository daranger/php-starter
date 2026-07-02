<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Contracts\MiddlewareInterface;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

class MaintenanceMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next): Response
    {
        if (function_exists('setting') && setting('maintenance_mode') === '1') {
            $path = $request->path();
            
            // Allow paths related to login so admin can log in
            if (str_starts_with($path, '/api/auth/') || $path === '/login') {
                return $next($request);
            }
            
            if (!function_exists('admin') || !admin()) {
                if ($request->isAjax() || str_starts_with($path, '/api/')) {
                    return Response::json(['error' => 'Site is currently under maintenance. Please try again later.'], 503);
                }
                
                // Return a simple maintenance HTML response with a login form
                $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Maintenance</title>
    <style>
        body { font-family: system-ui, -apple-system, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #f8fafc; color: #334155; }
        .container { text-align: center; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h1 { margin-top: 0; color: #0f172a; font-size: 24px; }
        p { margin-bottom: 25px; line-height: 1.5; color: #64748b; }
        .form-group { margin-bottom: 15px; text-align: left; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px; }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; font-size: 15px; }
        .form-control:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        .btn { display: block; width: 100%; padding: 10px 20px; background: #3b82f6; color: #fff; border: none; font-size: 16px; cursor: pointer; text-decoration: none; border-radius: 6px; font-weight: 500; transition: background 0.2s; }
        .btn:hover { background: #2563eb; }
        #error-msg { color: #ef4444; margin-bottom: 15px; font-size: 14px; display: none; }
        .toggle-btn { background: none; border: none; color: #3b82f6; cursor: pointer; text-decoration: underline; padding: 0; margin-top: 15px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="container" id="main-view">
        <h1 style="font-size: 40px; margin-bottom: 10px;">🛠</h1>
        <h1>Site Under Maintenance</h1>
        <p>We are currently performing scheduled maintenance. We should be back shortly. Thank you for your patience.</p>
        <button class="toggle-btn" onclick="document.getElementById('main-view').style.display='none'; document.getElementById('login-view').style.display='block';">Admin Login</button>
    </div>

    <div class="container" id="login-view" style="display: none;">
        <h1>Admin Login</h1>
        <p>Login to bypass maintenance mode</p>
        <div id="error-msg"></div>
        <form id="login-form">
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn" id="submit-btn">Login</button>
        </form>
        <button class="toggle-btn" onclick="document.getElementById('login-view').style.display='none'; document.getElementById('main-view').style.display='block';">Cancel</button>
    </div>

    <script>
    document.getElementById('login-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('submit-btn');
        const err = document.getElementById('error-msg');
        
        btn.disabled = true;
        btn.innerText = 'Logging in...';
        err.style.display = 'none';

        try {
            const res = await fetch('/api/auth/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    email: document.getElementById('email').value,
                    password: document.getElementById('password').value
                })
            });
            const data = await res.json();
            
            if (data.success) {
                window.location.reload();
            } else {
                err.innerText = data.error || 'Invalid credentials';
                err.style.display = 'block';
                btn.disabled = false;
                btn.innerText = 'Login';
            }
        } catch (e) {
            err.innerText = 'A network error occurred.';
            err.style.display = 'block';
            btn.disabled = false;
            btn.innerText = 'Login';
        }
    });
    </script>
</body>
</html>
HTML;
                return new Response($html, 503);
            }
        }

        return $next($request);
    }
}
