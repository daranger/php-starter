<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    public function __construct(
        protected string $content = '',
        protected int $status = 200,
        protected array $headers = []
    ) {}

    public static function json(array|object $data, int $status = 200, array $headers = []): self
    {
        $headers['Content-Type'] = 'application/json; charset=utf-8';
        return new self(json_encode($data, JSON_THROW_ON_ERROR), $status, $headers);
    }

    public static function redirect(string $url, int $status = 302, array $headers = []): self
    {
        $headers['Location'] = $url;
        return new self('', $status, $headers);
    }

    public static function view(string $view, array $data = [], int $status = 200, array $headers = []): self
    {
        extract($data);
        ob_start();
        
        $viewPath = __DIR__ . '/../../resources/views/' . str_replace('.', '/', $view) . '.php';
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            throw new \Exception("View [{$view}] not found at {$viewPath}");
        }
        
        $content = ob_get_clean();
        
        $headers['Content-Type'] = 'text/html; charset=utf-8';
        return new self($content, $status, $headers);
    }

    public function withHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function withCookie(string $name, string $value, int $minutes = 60, string $path = '/', string $domain = '', bool $secure = false, bool $httpOnly = true): self
    {
        setcookie($name, $value, time() + ($minutes * 60), $path, $domain, $secure, $httpOnly);
        return $this;
    }

    public function send(): void
    {
        if (headers_sent()) {
            echo $this->content;
            return;
        }

        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        http_response_code($this->status);

        echo $this->content;
    }
}
