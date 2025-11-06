<?php
/**
 * Application Router
 *
 * Routes HTTP requests to appropriate controllers
 */

declare(strict_types=1);

namespace MultiBot\Core;

class Router
{
    private array $routes = [];
    private array $middlewares = [];

    /**
     * Add GET route
     */
    public function get(string $path, callable $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }

    /**
     * Add POST route
     */
    public function post(string $path, callable $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }

    /**
     * Add PUT route
     */
    public function put(string $path, callable $handler): self
    {
        return $this->addRoute('PUT', $handler);
    }

    /**
     * Add DELETE route
     */
    public function delete(string $path, callable $handler): self
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * Add route
     */
    private function addRoute(string $method, string $path, callable $handler): self
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
        ];
        return $this;
    }

    /**
     * Add middleware
     */
    public function middleware(callable $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * Dispatch request to appropriate handler
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove base path
        $basePath = '/multi-bot-system/public';
        if (str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
        }

        // Run middlewares
        foreach ($this->middlewares as $middleware) {
            $result = $middleware();
            if ($result === false) {
                return;
            }
        }

        // Find matching route
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path)) {
                $params = $this->extractParams($route['path'], $path);
                call_user_func($route['handler'], $params);
                return;
            }
        }

        Response::notFound('Route not found');
    }

    /**
     * Match path pattern
     */
    private function matchPath(string $pattern, string $path): bool
    {
        $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([^/]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';
        return (bool) preg_match($pattern, $path);
    }

    /**
     * Extract path parameters
     */
    private function extractParams(string $pattern, string $path): array
    {
        preg_match_all('/\{([a-zA-Z0-9_]+)\}/', $pattern, $keys);
        $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([^/]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';

        preg_match($pattern, $path, $values);
        array_shift($values);

        return array_combine($keys[1] ?? [], $values);
    }
}
