<?php

declare(strict_types=1);

namespace App\Core;

use Exception;

class Router
{
    private static array $routes = [];
    private static array $groupAttributes = [];
    private static array $namedRoutes = [];
    private static ?array $lastRoute = null;

    public static function get(string $uri, array|callable $action): self
    {
        return self::addRoute('GET', $uri, $action);
    }

    public static function currentRouteIs(string $name): bool {
        $uri = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');
        $name = $name === 'home' ? '' : trim($name, '/');
        return $uri === $name;
    }

    public static function url(string $name): string {
        return $name === 'home' ? '/' : '/' . ltrim($name, '/');
    }

    public static function currentRouteName(): string
    {
        // Получаем текущий URI без начальных и конечных слешей
        $uri = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');

        // Если мы на главной, считаем, что имя роута — 'home'.
        // Иначе просто возвращаем сам путь (например, 'menu', 'about').
        return $uri === '' ? 'home' : $uri;
    }

    public static function post(string $uri, array|callable $action): self
    {
        return self::addRoute('POST', $uri, $action);
    }

    public static function put(string $uri, array|callable $action): self
    {
        return self::addRoute('PUT', $uri, $action);
    }

    public static function delete(string $uri, array|callable $action): self
    {
        return self::addRoute('DELETE', $uri, $action);
    }

    public static function patch(string $uri, array|callable $action): self
    {
        return self::addRoute('PATCH', $uri, $action);
    }

    public static function group(array $attributes, callable $callback): void
    {
        $previousGroupAttributes = self::$groupAttributes;
        
        self::$groupAttributes = self::mergeGroupAttributes($previousGroupAttributes, $attributes);
        
        $callback();
        
        self::$groupAttributes = $previousGroupAttributes;
    }

    private static function mergeGroupAttributes(array $old, array $new): array
    {
        return [
            'prefix' => trim($old['prefix'] ?? '', '/') . '/' . trim($new['prefix'] ?? '', '/'),
            'middleware' => array_merge($old['middleware'] ?? [], $new['middleware'] ?? [])
        ];
    }

    private static function addRoute(string $method, string $uri, array|callable $action): self
    {
        $prefix = self::$groupAttributes['prefix'] ?? '';
        $uri = '/' . trim($prefix . '/' . trim($uri, '/'), '/');
        
        $route = [
            'method' => $method,
            'uri' => $uri,
            'action' => $action,
            'middleware' => self::$groupAttributes['middleware'] ?? [],
            'name' => null,
        ];

        self::$routes[$method][$uri] = $route;
        self::$lastRoute = ['method' => $method, 'uri' => $uri];
        
        return new self();
    }

    public function name(string $name): self
    {
        if (self::$lastRoute) {
            $method = self::$lastRoute['method'];
            $uri = self::$lastRoute['uri'];
            self::$routes[$method][$uri]['name'] = $name;
            self::$namedRoutes[$name] = $uri;
        }
        return $this;
    }

    public function middleware(string|array $middleware): self
    {
        if (self::$lastRoute) {
            $method = self::$lastRoute['method'];
            $uri = self::$lastRoute['uri'];
            
            $middlewares = is_array($middleware) ? $middleware : [$middleware];
            self::$routes[$method][$uri]['middleware'] = array_merge(
                self::$routes[$method][$uri]['middleware'], 
                $middlewares
            );
        }
        return $this;
    }

    public static function getRoutes(): array
    {
        return self::$routes;
    }

    public static function route(string $name, array $parameters = []): string
    {
        if (!isset(self::$namedRoutes[$name])) {
            throw new Exception("Route [{$name}] not defined.");
        }

        $uri = self::$namedRoutes[$name];
        
        foreach ($parameters as $key => $value) {
            $uri = str_replace('{' . $key . '}', (string) $value, $uri);
        }

        return $uri;
    }

    public static function dispatch(Request $request): Response
    {
        $method = $request->method();
        $uri = $request->path();

        if ($method === 'POST' && $request->has('_method')) {
            $method = strtoupper((string) $request->input('_method'));
        }

        $routeInfo = self::findRoute($method, $uri);

        if (!$routeInfo) {
            throw new Exception("Route not found: {$method} {$uri}", 404);
        }

        foreach ($routeInfo['parameters'] as $key => $value) {
            $request->setParameter($key, $value);
        }

        return self::runRoute($routeInfo['route'], $request);
    }

    private static function findRoute(string $method, string $uri): ?array
    {
        if (!isset(self::$routes[$method])) {
            return null;
        }

        foreach (self::$routes[$method] as $routeUri => $route) {
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<\1>[a-zA-Z0-9_-]+)', $routeUri);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $uri, $matches)) {
                $parameters = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $parameters[$key] = $value;
                    }
                }
                return ['route' => $route, 'parameters' => $parameters];
            }
        }

        return null;
    }

    private static function runRoute(array $route, Request $request): Response
    {
        $middlewares = $route['middleware'];
        
        // Add MaintenanceMiddleware as the first middleware to execute globally
        array_unshift($middlewares, \App\Http\Middleware\MaintenanceMiddleware::class);
        
        $action = function ($req) use ($route) {
            $action = $route['action'];
            
            if (is_callable($action)) {
                $response = Container::getInstance()->call($action, ['request' => $req]);
            } elseif (is_array($action)) {
                [$controllerClass, $method] = $action;
                $controller = Container::getInstance()->make($controllerClass);
                $response = Container::getInstance()->call([$controller, $method], ['request' => $req]);
            } else {
                throw new Exception("Invalid route action");
            }

            if (!$response instanceof Response) {
                if (is_array($response) || is_object($response)) {
                    return Response::json($response);
                }
                return new Response((string) $response);
            }
            
            return $response;
        };

        $pipeline = array_reduce(
            array_reverse($middlewares),
            function ($next, $middleware) {
                return function ($req) use ($next, $middleware) {
                    $middlewareInstance = Container::getInstance()->make($middleware);
                    return $middlewareInstance->handle($req, $next);
                };
            },
            $action
        );

        return $pipeline($request);
    }
}