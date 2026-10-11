<?php

class Router
{
    private array $routes = [];

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function patch(string $path, callable $handler): void
    {
        $this->routes['PATCH'][$path] = $handler;
    }

    public function dispatch(string $method, string $path): void
    {
        $handler = $this->routes[$method][$path] ?? null;

        if ($handler !== null) {
            $handler();
            return;
        }

        // Match a ticket URL containing a ticket ID to help pass it to the get single item and patch controller methods
        if (
            in_array($method, ['GET', 'PATCH']) &&
            preg_match('#^/api/tickets/([1-9][0-9]*)$#', $path, $matches)
        ) {
            $id = (int) $matches[1];

            $handler = $this->routes[$method]['/api/tickets'] ?? null;

            if ($handler !== null) {
                $handler($id);
                return;
            }
        }

        http_response_code(404);

        echo json_encode([
            'error' => 'Route not found'
        ]);
    }
}