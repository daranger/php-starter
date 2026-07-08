<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Contracts\MiddlewareInterface;
use App\Core\Request;
use App\Core\Response;

class AdminMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next): Response
    {
        if (!admin()) {
            return Response::redirect('/');
        }
        
        try {
            $res = \App\Core\Db::query("SELECT setting_value FROM settings WHERE setting_key = 'admin_ip_whitelist'");
            $row = $res->fetch_assoc();
            $whitelistStr = $row ? trim((string)$row['setting_value']) : '';
            
            if ($whitelistStr !== '') {
                // Split by comma and trim whitespace
                $allowedIps = array_filter(array_map('trim', explode(',', $whitelistStr)));
                $userIp = $request->ip();
                
                if (!empty($allowedIps) && !in_array($userIp, $allowedIps, true)) {
                    // IP is not allowed, block access
                    return Response::redirect('/'); // Redirect or return 403
                }
            }
        } catch (\Exception $e) {
            // DB error or settings table missing, ignore to not break admin
        }

        return $next($request);
    }
}

