<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    private function addRoute(string $method, string $path, array|callable $handler, array $middleware): void
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $this->convertPathToRegex($path),
            'handler' => $handler,
            'middleware' => $middleware,
            'originalPath' => $path
        ];
    }

    private function convertPathToRegex(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH) ?: '/';
        $method = strtoupper($method);

        // Check for _method override in POST requests (for PUT/DELETE from forms)
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run middleware
                foreach ($route['middleware'] as $mwClass) {
                    $mw = new $mwClass();
                    if (!$mw->handle()) {
                        return; // Middleware aborted the request
                    }
                }

                // Execute handler
                if (is_callable($route['handler'])) {
                    call_user_func($route['handler'], $params);
                    return;
                }

                if (is_array($route['handler'])) {
                    [$class, $action] = $route['handler'];
                    $controller = new $class();
                    $controller->$action($params);
                    return;
                }
            }
        }

        // 404 handler
        http_response_code(404);
        if (str_starts_with($uri, '/api/')) {
            Response::error('Endpoint not found', 404);
        } else {
            View::render('errors/404', ['title' => '404 Not Found - nkinfyx groups'], 'layouts/main');
        }
    }
}
