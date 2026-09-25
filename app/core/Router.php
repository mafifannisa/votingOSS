<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array|callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, array|callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, array|callable $handler): void
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $parsedUrl = parse_url($uri, PHP_URL_PATH);
        $requestPath = '/' . trim($parsedUrl ?: '', '/');
        
        // Sesuaikan dengan base path subfolder jika dijalankan tanpa virtual host
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        if ($scriptDir !== '/' && $scriptDir !== '\\' && str_starts_with($requestPath, $scriptDir)) {
            $requestPath = substr($requestPath, strlen($scriptDir));
            $requestPath = '/' . trim($requestPath, '/');
        }

        $method = strtoupper($method);

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $requestPath, $matches)) {
                $params = [];
                foreach ($matches as $key => $val) {
                    if (is_string($key)) {
                        $params[$key] = $val;
                    }
                }

                $handler = $route['handler'];

                if (is_callable($handler)) {
                    call_user_func_array($handler, $params);
                    return;
                }

                if (is_array($handler) && count($handler) === 2) {
                    [$controllerClass, $action] = $handler;
                    if (class_exists($controllerClass)) {
                        $controller = new $controllerClass();
                        if (method_exists($controller, $action)) {
                            call_user_func_array([$controller, $action], $params);
                            return;
                        }
                    }
                }
            }
        }

        // 404 Not Found
        http_response_code(404);
        require_once __DIR__ . '/../views/404.php';
    }
}
