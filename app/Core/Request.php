<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    private array $parameters = [];

    public function __construct(
        public readonly array $query = [],
        public readonly array $request = [],
        public readonly array $attributes = [],
        public readonly array $cookies = [],
        public readonly array $files = [],
        public readonly array $server = []
    ) {}

    public static function capture(): static
    {
        $requestData = $_POST;

        if (isset($_SERVER['CONTENT_TYPE']) && str_starts_with($_SERVER['CONTENT_TYPE'], 'application/json')) {
            $json = json_decode(file_get_contents('php://input'), true);
            if (is_array($json)) {
                $requestData = array_merge($requestData, $json);
            }
        }

        return new static(
            $_GET,
            $requestData,
            [],
            $_COOKIE,
            $_FILES,
            $_SERVER
        );
    }

    public function setParameter(string $key, mixed $value): void
    {
        $this->parameters[$key] = $value;
    }

    public function route(string $key, mixed $default = null): mixed
    {
        return $this->parameters[$key] ?? $default;
    }

    public function ip(): string
    {
        return $this->server['HTTP_CF_CONNECTING_IP'] 
            ?? $this->server['HTTP_X_FORWARDED_FOR'] 
            ?? $this->server['REMOTE_ADDR'] 
            ?? '0.0.0.0';
    }

    public function method(): string
    {
        return $this->server['REQUEST_METHOD'] ?? 'GET';
    }

    public function host(): string
    {
        return $this->server['HTTP_HOST'] ?? '';
    }

    public function path(): string
    {
        $path = parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $path = preg_replace('#/+#', '/', $path);
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }
        return $path;
    }

    public function uri(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->request[$key] ?? $this->query[$key] ?? $default;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->request[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($this->request[$key]) || isset($this->query[$key]);
    }

    public function header(string $key, mixed $default = null): mixed
    {
        $headerName = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $this->server[$headerName] ?? $default;
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    public function isAjax(): bool
    {
        return strtolower((string) $this->header('X-Requested-With')) === 'xmlhttprequest';
    }

    public function json(string $key = null, mixed $default = null): mixed
    {
        static $data = null;

        if ($data === null) {
            $data = json_decode(file_get_contents('php://input'), true) ?? [];
        }

        if ($key === null) {
            return $data;
        }

        return $data[$key] ?? $default;
    }

    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }
}