<?php
/**
 * API Router
 *
 * Routes HTTP requests to controller methods
 *
 * @package BotDeployment\Http
 * @author  Ecigdis Limited
 * @version 1.0.0
 */

namespace BotDeployment\Http;

use BotDeployment\Controllers\BotController;

class Router
{
    private array $routes = [];
    private Middleware $middleware;

    public function __construct()
    {
        $this->middleware = new Middleware();
        $this->registerRoutes();
    }

    /**
     * Register all routes
     */
    private function registerRoutes(): void
    {
        // Bot routes
        $this->get('/api/bots', [BotController::class, 'index']);
        $this->get('/api/bots/scheduled', [BotController::class, 'scheduled']);
        $this->get('/api/bots/due', [BotController::class, 'due']);
        $this->get('/api/bots/{id}', [BotController::class, 'show']);
        $this->post('/api/bots', [BotController::class, 'store']);
        $this->put('/api/bots/{id}', [BotController::class, 'update']);
        $this->delete('/api/bots/{id}', [BotController::class, 'destroy']);
        $this->post('/api/bots/{id}/execute', [BotController::class, 'execute']);
        $this->get('/api/bots/{id}/metrics', [BotController::class, 'metrics']);
        $this->post('/api/bots/{id}/pause', [BotController::class, 'pause']);
        $this->post('/api/bots/{id}/activate', [BotController::class, 'activate']);
        $this->post('/api/bots/{id}/archive', [BotController::class, 'archive']);

        // Session routes
        $this->post('/api/sessions', [BotController::class, 'createSession']);
        $this->get('/api/sessions', [BotController::class, 'listSessions']);
        $this->get('/api/sessions/{id}', [BotController::class, 'getSession']);
    }

    /**
     * Register GET route
     */
    public function get(string $path, array $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    /**
     * Register POST route
     */
    public function post(string $path, array $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    /**
     * Register PUT route
     */
    public function put(string $path, array $handler): void
    {
        $this->addRoute('PUT', $path, $handler);
    }

    /**
     * Register DELETE route
     */
    public function delete(string $path, array $handler): void
    {
        $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * Add route to registry
     */
    private function addRoute(string $method, string $path, array $handler): void
    {
        // Convert path parameters to regex pattern
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $handler,
            'params' => $this->extractParams($path)
        ];
    }

    /**
     * Extract parameter names from path
     */
    private function extractParams(string $path): array
    {
        preg_match_all('/\{([^}]+)\}/', $path, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Dispatch request to appropriate handler
     */
    public function dispatch(Request $request): void
    {
        try {
            // Handle CORS preflight
            if ($request->method() === 'OPTIONS') {
                Response::handlePreflight();
                return;
            }

            // Apply CORS
            $this->middleware->validateCors($request);

            // Authenticate
            if (!$this->middleware->authenticate($request)) {
                Response::unauthorized('Invalid API key');
                return;
            }

            // Rate limit
            if (!$this->middleware->checkRateLimit($request)) {
                Response::tooManyRequests('Rate limit exceeded', 60);
                return;
            }

            // Log request
            $this->middleware->logRequest($request);

            // Find matching route
            $route = $this->findRoute($request->method(), $request->uri());

            if (!$route) {
                Response::notFound('Endpoint not found: ' . $request->uri());
                return;
            }

            // Extract route parameters
            $params = $this->matchParams($route, $request->uri());
            $request->setParams($params);

            // Call handler
            list($controllerClass, $method) = $route['handler'];
            $controller = new $controllerClass();
            $controller->$method($request);

        } catch (\Exception $e) {
            Response::serverError('Request handling failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Find route matching method and URI
     */
    private function findRoute(string $method, string $uri): ?array
    {
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri)) {
                return $route;
            }
        }

        return null;
    }

    /**
     * Match route parameters from URI
     */
    private function matchParams(array $route, string $uri): array
    {
        preg_match($route['pattern'], $uri, $matches);
        array_shift($matches); // Remove full match

        $params = [];
        foreach ($route['params'] as $index => $name) {
            $params[$name] = $matches[$index] ?? null;
        }

        return $params;
    }
}
