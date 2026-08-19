<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Fluent route registration facade. Proxies to the Application router.
 */
final class Route
{
    public static function get(string $uri, mixed $action): RouteDefinition
    {
        return static::add(['GET'], $uri, $action);
    }

    public static function post(string $uri, mixed $action): RouteDefinition
    {
        return static::add(['POST'], $uri, $action);
    }

    public static function put(string $uri, mixed $action): RouteDefinition
    {
        return static::add(['PUT'], $uri, $action);
    }

    public static function patch(string $uri, mixed $action): RouteDefinition
    {
        return static::add(['PATCH'], $uri, $action);
    }

    public static function delete(string $uri, mixed $action): RouteDefinition
    {
        return static::add(['DELETE'], $uri, $action);
    }

    public static function match(array $methods, string $uri, mixed $action): RouteDefinition
    {
        return static::add($methods, $uri, $action);
    }

    public static function any(string $uri, mixed $action): RouteDefinition
    {
        return static::add(['GET', 'POST', 'PUT', 'PATCH', 'DELETE'], $uri, $action);
    }

    public static function resource(string $name, string $controller, ?string $uriPrefix = null): void
    {
        $prefix = $uriPrefix ?? $name;
        $singular = rtrim($name, 's');
        $def = static::add(['GET'], '/' . $prefix, [$controller, 'index'])->name($name . '.index');
        static::add(['GET'], '/' . $prefix . '/create', [$controller, 'create'])->name($name . '.create');
        static::add(['POST'], '/' . $prefix, [$controller, 'store'])->name($name . '.store');
        static::add(['GET'], '/' . $prefix . '/{' . $singular . '}', [$controller, 'show'])->name($name . '.show');
        static::add(['GET'], '/' . $prefix . '/{' . $singular . '}/edit', [$controller, 'edit'])->name($name . '.edit');
        static::add(['PUT'], '/' . $prefix . '/{' . $singular . '}', [$controller, 'update'])->name($name . '.update');
        static::add(['DELETE'], '/' . $prefix . '/{' . $singular . '}', [$controller, 'destroy'])->name($name . '.destroy');
    }

    public static function group(array $attributes, callable $callback): void
    {
        Application::instance()->router()->group($attributes, $callback);
    }

    public static function prefix(string $prefix): RouteGroup
    {
        return new RouteGroup(['prefix' => $prefix]);
    }

    public static function name(string $name): RouteGroup
    {
        return new RouteGroup(['name' => $name]);
    }

    public static function middleware(mixed $middleware): RouteGroup
    {
        return new RouteGroup(['middleware' => $middleware]);
    }

    private static function add(array $methods, string $uri, mixed $action): RouteDefinition
    {
        return Application::instance()->router()->addRoute($methods, $uri, $action);
    }
}
