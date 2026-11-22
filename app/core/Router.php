<?php

class Router
{
    private array $routes = [];

    public function get(string $path, string $controller, string $method): void
    {
        $this->add('GET', $path, $controller, $method);
    }

    public function post(string $path, string $controller, string $method): void
    {
        $this->add('POST', $path, $controller, $method);
    }

    private function add(string $httpMethod, string $path, string $controller, string $method): void
    {
        $normalized = '/' . trim($path, '/');
        if ($normalized === '//') {
            $normalized = '/';
        }

        $pattern = preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $normalized);
        $this->routes[] = [
            'http' => $httpMethod,
            'path' => $normalized,
            'pattern' => '#^' . $pattern . '/?$#',
            'controller' => $controller,
            'method' => $method,
        ];
    }

    public function dispatch(string $httpMethod, string $uri): void
    {
        $config = require dirname(__DIR__, 2) . '/config/config.php';
        $base = rtrim($config['base_url'], '/');
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        if ($base && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }
        $path = '/' . ltrim($path, '/');
        if ($path === '/index.php') {
            $path = '/';
        }

        foreach ($this->routes as $route) {
            if ($route['http'] !== $httpMethod) {
                continue;
            }
            if (preg_match($route['pattern'], $path, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $controllerClass = $route['controller'];
                $method = $route['method'];
                $controller = new $controllerClass();
                $controller->$method(...array_values($params));
                return;
            }
        }

        http_response_code(404);
        View::render('errors/404', ['title' => 'Halaman tidak ditemukan'], 'main');
    }
}
