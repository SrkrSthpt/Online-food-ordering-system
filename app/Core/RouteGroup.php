<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Route group builder — collects attributes, then applies them to routes
 * registered inside the group's callback.
 */
final class RouteGroup
{
    private array $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function prefix(string $prefix): self
    {
        $this->attributes['prefix'] = $prefix;
        return $this;
    }

    public function name(string $name): self
    {
        $this->attributes['name'] = $name;
        return $this;
    }

    public function middleware(string|array $middleware): self
    {
        $this->attributes['middleware'] = array_merge(
            (array)($this->attributes['middleware'] ?? []),
            (array)$middleware
        );
        return $this;
    }

    public function group(callable $callback): void
    {
        Application::instance()->router()->group($this->attributes, $callback);
    }
}
