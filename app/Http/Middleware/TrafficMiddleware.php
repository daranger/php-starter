<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Core\Request;
use App\Core\Container;
use Predis\ClientInterface;

class TrafficMiddleware
{
    public function handle(Request $request, callable $next)
    {
        $this->recordTraffic($request);
        return $next($request);
    }

    private function recordTraffic(Request $request): void
    {
        try {
            $redis = Container::getInstance()->make(ClientInterface::class);
            if (!$redis) return;
        } catch (\Throwable $e) {
            return;
        }
        
        $ua = (string) $request->header('user-agent', '');
        if (preg_match('/bot|crawl|slurp|spider|mediapartners|apis-google/i', $ua)) {
            return; // Отсеиваем ботов
        }
        
        $ip = $request->ip();

        try {
            $date = date('Y-m-d');
            $min = floor(date('i') / 5) * 5;
            $timeKey = date('Y-m-d:H') . ':' . str_pad((string)$min, 2, '0', STR_PAD_LEFT);
            
            $todayKey = "traffic:unique:today:$date";
            $bucketKey = "traffic:unique:5min:$timeKey";
            
            $redis->pfadd($todayKey, [$ip]);
            $redis->expire($todayKey, 172800); // храним 2 суток
            
            $redis->pfadd($bucketKey, [$ip]);
            $redis->expire($bucketKey, 7200); // храним 2 часа
        } catch (\Exception $e) {
            // Игнорируем ошибки Redis
        }
    }
}
