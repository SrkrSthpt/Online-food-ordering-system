<?php

declare(strict_types=1);

namespace App\Core;

/**
 * HTTP request abstraction over superglobals, with JSON body support.
 */
final class Request
{
    private array $routeParams = [];

    public function __construct()
    {
        $this->rememberPreviousUrl();
    }

    public function method(): string
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        // Support REST verbs via hidden _method field or X-HTTP-Method-Override.
        if ($method === 'POST') {
            $override = $_POST['_method'] ?? $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? null;
            if ($override !== null) {
                $method = strtoupper((string)$override);
            }
        }
        return $method;
    }

    public function isMethod(string $method): bool
    {
        return $this->method() === strtoupper($method);
    }

    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function isPut(): bool
    {
        return $this->method() === 'PUT';
    }

    public function isPatch(): bool
    {
        return $this->method() === 'PATCH';
    }

    public function isDelete(): bool
    {
        return $this->method() === 'DELETE';
    }

    public function isAjax(): bool
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    }

    public function wantsJson(): bool
    {
        return $this->isAjax()
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
            || str_starts_with($this->path(), '/api/');
    }

    public function path(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $base = dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php');
        if ($base !== '/' && $base !== '\\' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }
        $uri = '/' . ltrim($uri, '/');
        return $uri === '/' ? '/' : rtrim($uri, '/');
    }

    public function fullUrl(): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . ($_SERVER['REQUEST_URI'] ?? '/');
    }

    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public function input(string $key, mixed $default = null): mixed
    {
        $data = $this->all();
        return $data[$key] ?? $default;
    }

    public function all(): array
    {
        if (
            ($this->isPost() || $this->isPut() || $this->isPatch() || $this->isDelete())
            && empty($_POST)
            && ($raw = file_get_contents('php://input')) !== ''
        ) {
            $parsed = json_decode($raw, true);
            if (is_array($parsed)) {
                return array_merge($_GET, $parsed);
            }
        }
        return array_merge($_GET, $_POST);
    }

    public function only(array $keys): array
    {
        $data = $this->all();
        return array_intersect_key($data, array_flip($keys));
    }

    public function except(array $keys): array
    {
        $data = $this->all();
        foreach ($keys as $key) {
            unset($data[$key]);
        }
        return $data;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->all());
    }

    public function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    public function hasFile(string $key): bool
    {
        return isset($_FILES[$key]) && ($_FILES[$key]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    public function setRouteParam(string $key, mixed $value): void
    {
        $this->routeParams[$key] = $value;
    }

    public function routeParam(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function routeParams(): array
    {
        return $this->routeParams;
    }

    public function header(string $name, mixed $default = null): mixed
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$key] ?? $default;
    }

    private function rememberPreviousUrl(): void
    {
        if ($this->isGet() && !$this->isAjax() && !str_starts_with($this->path(), '/api/')) {
            Session::put('_previous_url', $this->fullUrl());
        }
    }
}
