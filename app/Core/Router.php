<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Route registry and dispatcher.
 */
final class Router
{
    /** @var RouteDefinition[] */
    private array $routes = [];

    private string $groupPrefix = '';
    private string $groupName = '';
    private array $groupMiddleware = [];

    public function addRoute(array $methods, string $uri, mixed $action): RouteDefinition
    {
        $fullUri = $this->groupPrefix . '/' . trim($uri, '/');
        $fullUri = '/' . trim($fullUri, '/');
        if ($fullUri !== '/') {
            $fullUri = rtrim($fullUri, '/');
        }

        $route = new RouteDefinition($methods, $fullUri, $action);
        $route->middleware($this->groupMiddleware);
        if ($this->groupName !== '') {
            $route->name($this->groupName . $route->getName());
        }

        $this->routes[] = $route;
        return $route;
    }

    public function group(array $attributes, callable $callback): void
    {
        $prevPrefix = $this->groupPrefix;
        $prevName = $this->groupName;
        $prevMiddleware = $this->groupMiddleware;

        if (isset($attributes['prefix'])) {
            $this->groupPrefix = $prevPrefix . '/' . trim($attributes['prefix'], '/');
        }
        if (isset($attributes['name'])) {
            $this->groupName = $prevName . $attributes['name'];
        }
        if (isset($attributes['middleware'])) {
            $this->groupMiddleware = array_merge($prevMiddleware, (array)$attributes['middleware']);
        }

        $callback();

        $this->groupPrefix = $prevPrefix;
        $this->groupName = $prevName;
        $this->groupMiddleware = $prevMiddleware;
    }

    public function dispatch(Request $request, Application $app): Response
    {
        $path = $request->path();
        $method = $request->method();

        foreach ($this->routes as $route) {
            if (!in_array($method, $route->methods(), true)) {
                continue;
            }
            $params = $this->match($path, $route);
            if ($params === null) {
                continue;
            }
            foreach ($params as $key => $value) {
                $request->setRouteParam($key, $value);
            }
            return $this->run($route, $request, $app);
        }

        // 405 check: path matched another method?
        foreach ($this->routes as $route) {
            if ($this->match($path, $route) !== null) {
                $allowed = implode(', ', $route->methods());
                return Response::make('', 405, ['Allow' => $allowed]);
            }
        }

        throw new \RuntimeException('404', 404);
    }

    public function urlFor(string $name, array $params = []): string
    {
        foreach ($this->routes as $route) {
            if ($route->getName() !== $name) {
                continue;
            }
            $uri = $route->uri();
            foreach ($params as $key => $value) {
                $uri = str_replace('{' . $key . '}', (string)$value, $uri);
            }
            if (preg_match('/\{[a-zA-Z0-9_]+\}/', $uri)) {
                throw new \RuntimeException("Missing route parameter for [{$name}]");
            }
            return url($uri);
        }
        throw new \RuntimeException("Route [{$name}] not defined");
    }

    public function routes(): array
    {
        return $this->routes;
    }

    private function match(string $path, RouteDefinition $route): ?array
    {
        $pattern = preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function (array $m) use ($route) {
            $pattern = $route->constraints()[$m[1]] ?? '[^/]+';
            return '(?P<' . $m[1] . '>' . $pattern . ')';
        }, $route->uri());

        if (!preg_match('#^' . $pattern . '$#', $path, $matches)) {
            return null;
        }
        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }
        return $params;
    }

    private function run(RouteDefinition $route, Request $request, Application $app): Response
    {
        $middleware = array_merge($app->middlewareMap()['*'] ?? [], $route->middlewareList());
        $instances = $app->resolveMiddlewareList($middleware);
        $pipeline = array_reduce(
            array_reverse($instances),
            fn (callable $next, object $mw) => function (Request $req) use ($mw, $next): Response {
                return $mw->handle($req, $next);
            },
            fn (Request $req): Response => $this->callAction($route->action(), $req, $app)
        );
        return $pipeline($request);
    }

    private function callAction(mixed $action, Request $request, Application $app): Response
    {
        if (is_callable($action) && !is_array($action)) {
            return $action($request);
        }
        if (is_array($action)) {
            [$controller, $method] = $action;
            $instance = $app->resolve($controller);
            return $instance->{$method}(...$app->methodArgs($instance, $method));
        }
        throw new \RuntimeException('Invalid route action');
    }
}
