<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Minimal pattern router.
 *
 *   $router->get('/maps/{slug}', [MapController::class, 'show']);
 *
 * `{name}` captures one path segment and is passed to the controller method as
 * a named argument.
 */
final class Router
{
    /** @var array<int, array{method: string, regex: string, params: array<int, string>, handler: callable|array{0: class-string, 1: string}}> */
    private array $routes = [];

    /** @param callable|array{0: class-string, 1: string} $handler */
    public function get(string $pattern, callable|array $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    /** @param callable|array{0: class-string, 1: string} $handler */
    public function post(string $pattern, callable|array $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    /** @param callable|array{0: class-string, 1: string} $handler */
    private function add(string $method, string $pattern, callable|array $handler): void
    {
        $params = [];

        $regex = preg_replace_callback(
            '/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
            function (array $matches) use (&$params): string {
                $params[] = $matches[1];

                return '([^/]+)';
            },
            $pattern
        ) ?? $pattern;

        $this->routes[] = [
            'method'  => $method,
            'regex'   => '#^' . $regex . '$#',
            'params'  => $params,
            'handler' => $handler,
        ];
    }

    /**
     * Match the request and invoke its handler, returning the rendered body.
     *
     * @throws NotFoundException when no route matches the path
     */
    public function dispatch(Request $request): string
    {
        $path       = $request->path();
        $pathExists = false;

        foreach ($this->routes as $route) {
            if (preg_match($route['regex'], $path, $matches) !== 1) {
                continue;
            }

            $pathExists = true;

            if ($route['method'] !== $request->method()) {
                continue;
            }

            array_shift($matches);
            $arguments = array_combine($route['params'], $matches) ?: [];

            return $this->invoke($route['handler'], $arguments, $request);
        }

        if ($pathExists) {
            http_response_code(405);
            header('Allow: GET, POST');
        }

        throw new NotFoundException('No route matches ' . $path);
    }

    /**
     * @param  callable|array{0: class-string, 1: string}  $handler
     * @param  array<string, string>  $arguments
     */
    private function invoke(callable|array $handler, array $arguments, Request $request): string
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;
            $controller = new $class($request);

            return (string) $controller->{$method}(...$arguments);
        }

        return (string) $handler($request, ...array_values($arguments));
    }
}
