<?php
declare(strict_types=1);

namespace Core;

class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $path, string $handler): void
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;
    }

    public function post(string $path, string $handler): void
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
    }

    public function dispatch(string $uri, string $method): void
    {
        $rawPath = parse_url($uri, PHP_URL_PATH) ?? '/';

        // In XAMPP setups the app is usually served from something like:
        //   /FarmHub/public/login
        // Convert that to a route path like:
        //   /login
        $publicPos = strrpos($rawPath, '/public');
        if ($publicPos !== false) {
            $path = substr($rawPath, $publicPos + strlen('/public'));
        } else {
            $path = $rawPath;
        }

        $path = $this->normalize($path);
        $method = strtoupper($method);

        $handler = $this->routes[$method][$path] ?? null;
        if ($handler === null) {
            http_response_code(404);
            echo '404 Not Found';
            return;
        }

        [$controllerName, $action] = explode('@', $handler);
        $controllerClass = 'Controllers\\' . $controllerName;

        if (!class_exists($controllerClass) || !method_exists($controllerClass, $action)) {
            http_response_code(500);
            echo 'Controller or action not found';
            return;
        }

        $controller = new $controllerClass();
        $controller->$action();
    }

    private function normalize(string $path): string
    {
        if ($path === '') {
            return '/';
        }
        $path = rtrim($path, '/');
        return $path === '' ? '/' : $path;
    }
}

