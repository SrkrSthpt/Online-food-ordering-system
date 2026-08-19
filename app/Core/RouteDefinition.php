<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Builder for a single route. Returned by Route::get/post/...
 */
final class RouteDefinition
{
    /** @param array<int,string> $methods */
    public function __construct(
        private array $methods,
        private string $uri,
        private mixed $action,
        private string $name = '',
        private array $middleware = [],
        private array $constraints = []
    ) {
    }

    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function middleware(string|array $middleware): self
    {
        $this->middleware = array_merge($this->middleware, (array)$middleware);
        return $this;
    }

    public function where(string $param, string $pattern): self
    {
        $this->constraints[$param] = $pattern;
        return $this;
    }

    public function methods(): array
    {
        return $this->methods;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function action(): mixed
    {
        return $this->action;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function middlewareList(): array
    {
        return $this->middleware;
    }

    public function constraints(): array
    {
        return $this->constraints;
    }
}
