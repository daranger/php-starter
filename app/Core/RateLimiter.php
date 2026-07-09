<?php

declare(strict_types=1);

namespace App\Core;

use Predis\ClientInterface;

class RateLimiter
{
    private ClientInterface $redis;

    public function __construct(ClientInterface $redis)
    {
        $this->redis = $redis;
    }

    /**
     * Метод проверки лимитов.
     * Если лимит превышен — отдаем 429 и мягко завершаем работу,
     * если всё ок — скрипт идет дальше.
     */
    public function attempt(string $action, int $maxAttempts, int $decaySeconds, ?string $identifier = null): void
    {
        // 1. Используем переданный идентификатор или IP-адрес по умолчанию
        $identifier = $identifier ?? (new Request())->ip();

        $key = "rate_limit:{$action}:{$identifier}";

        try {
            // 2. Получаем текущее количество попыток из Redis
            $current = $this->redis->get($key);

            if ($current !== null && (int)$current >= $maxAttempts) {
                // Если брутфорсят — отдаем 429 Too Many Requests
                http_response_code(429);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'error' => 'Слишком много попыток входа. Попробуйте позже.'
                ], JSON_UNESCAPED_UNICODE);
                exit; // Здесь exit оправдан, так как мы заблокировали злоумышленника
            }

            // 3. Атомарно увеличиваем счетчик через пайплайн Predis
            $pipe = $this->redis->pipeline();
            $pipe->incr($key);

            if ($current === null) {
                $pipe->expire($key, $decaySeconds);
            }

            $pipe->execute();

        } catch (\Throwable $e) {
            // Если Redis лёг или упала ошибка синтаксиса — логируем её,
            // но НЕ рубим авторизацию админа, а даем пройти.
            error_log("RateLimiter Error: " . $e->getMessage());
            return;
        }
    }
}