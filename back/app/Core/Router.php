<?php
namespace App\Core;

final class Router{
    private array $routes = [
        'GET'     => [],
        'POST'    => [],
        'PUT'     => [],
        'PATCH'   => [],
        'DELETE'  => [],
        'OPTIONS' => [],
    ];

    public function add(string $method, string $path, callable|array $handler): void {
        $this->routes[strtoupper($method)][] = [
            rtrim($path, '/') ?: '/',
            $handler
        ];
    }

    public function get(string $path, callable|array $handler): void{
        $this->add('GET', $path, $handler);
    }

    public function post(string $path, callable|array $handler): void{
        $this->add('POST', $path, $handler);
    }

    public function put(string $path, callable|array $handler): void{
        $this->add('PUT', $path, $handler);
    }

    public function patch(string $path, callable|array $handler): void{
        $this->add('PATCH', $path, $handler);
    }

    public function delete(string $path, callable|array $handler): void{
        $this->add('DELETE', $path, $handler);
    }

    public function options(string $path, callable|array $handler): void{
        $this->add('OPTIONS', $path, $handler);
    }

    public function dispatch(?string $method = null, ?string $uri = null): void{
        $method = strtoupper($method ?? ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $uri    = $uri ?? ($_SERVER['REQUEST_URI'] ?? '/');
        $path   = parse_url($uri, PHP_URL_PATH) ?? '/';

        $scriptDir = rtrim(str_replace('index.php', '', $_SERVER['SCRIPT_NAME'] ?? ''), '/');
        if ($scriptDir && str_starts_with($path, $scriptDir)) {
            $path = substr($path, strlen($scriptDir)) ?: '/';
        }

        $path  = rtrim($path, '/') ?: '/';
        $table = $this->routes[$method] ?? [];

        
        foreach ($table as [$routePath, $handler]) {
            if ($routePath === $path) {
                if ($method === 'OPTIONS') {
                    http_response_code(204);
                    return;
                }
                $this->call($handler, []);
                return;
            }
        }

        
        foreach ($table as [$routePath, $handler]) {
            $pattern = preg_replace(
                '#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#',
                '(?P<$1>[^/]+)',
                $routePath
            );
            $regex = '#^' . ($routePath === '/' ? '/' : rtrim($pattern, '/')) . '$#';

            if (preg_match($regex, $path, $matches)) {
                if ($method === 'OPTIONS') {
                    http_response_code(204);
                    return;
                }
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->call($handler, $params);
                return;
            }
        }

        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Not found';
    }

    private function call(callable|array $handler, array $params): void{
        if (is_array($handler)) {
            [$class, $fn] = $handler;
            (new $class())->{$fn}($params);
            return;
        }
        $handler($params);
    }
}
