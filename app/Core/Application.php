<?php

declare(strict_types=1);

namespace App\Core;

use ReflectionClass;
use ReflectionNamedType;

/**
 * Application container: owns paths, binds singletons, boots the request
 * pipeline and provides dependency resolution with autowiring.
 */
final class Application
{
    private static ?Application $instance = null;

    private string $root;
    private array $singletons = [];
    private array $resolved = [];
    private array $middlewareMap = [];

    private function __construct(string $root)
    {
        $this->root = rtrim($root, '/\\');
    }

    public static function instance(): self
    {
        return static::$instance;
    }

    public static function create(string $root): self
    {
        return static::$instance = new self($root);
    }

    public function path(string $path = ''): string
    {
        return $this->root . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : '');
    }

    // ------------------------------------------------------------------
    // Container
    // ------------------------------------------------------------------

    public function singleton(string $abstract, callable $resolver): void
    {
        $this->singletons[$abstract] = $resolver;
    }

    public function bind(string $abstract, callable $resolver): void
    {
        $this->singletons[$abstract] = $resolver;
    }

    public function has(string $abstract): bool
    {
        return isset($this->singletons[$abstract]);
    }

    public function resolve(string $abstract): mixed
    {
        if (isset($this->singletons[$abstract])) {
            if (!array_key_exists($abstract, $this->resolved)) {
                $this->resolved[$abstract] = $this->singletons[$abstract]();
            }
            return $this->resolved[$abstract];
        }
        return $this->autowire($abstract);
    }

    public function singletonFor(string $abstract, callable $factory): mixed
    {
        if (!isset($this->singletons[$abstract])) {
            $this->singletons[$abstract] = $factory;
        }
        if (!array_key_exists($abstract, $this->resolved)) {
            $this->resolved[$abstract] = $this->singletons[$abstract]();
        }
        return $this->resolved[$abstract];
    }

    private function autowire(string $class): object
    {
        $ref = new ReflectionClass($class);
        $ctor = $ref->getConstructor();
        if ($ctor === null) {
            return $ref->newInstance();
        }
        $args = [];
        foreach ($ctor->getParameters() as $param) {
            $args[] = $this->resolveParam($param);
        }
        return $ref->newInstanceArgs($args);
    }

    /**
     * Resolve the arguments for a controller method (method injection).
     */
    public function methodArgs(object $instance, string $method): array
    {
        $ref = new \ReflectionMethod($instance, $method);
        $args = [];
        foreach ($ref->getParameters() as $param) {
            $args[] = $this->resolveParam($param);
        }
        return $args;
    }

    private function resolveParam(\ReflectionParameter $param): mixed
    {
        $type = $param->getType();
        if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
            return $this->resolve($type->getName());
        }
        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }
        return null;
    }

    // ------------------------------------------------------------------
    // Bootstrapping
    // ------------------------------------------------------------------

    public function bootstrap(): void
    {
        $this->loadEnvironment();
        $this->loadConfig();
        $this->setRuntimeOptions();

        $this->singleton(Router::class, fn () => new Router());
        $this->singleton(Request::class, fn () => new Request());
        $this->singleton(Auth::class, fn () => new Auth());
        $this->singleton(Gate::class, fn () => new Gate());
        $this->singleton(Logger::class, fn () => new Logger(storage_path('logs')));
        $this->singleton(Console::class, fn () => new Console($this));

        Session::start();
        $this->registerMiddlewareMap();
        $this->registerExceptionHandler();
    }

    public function bootstrapConsole(): void
    {
        $this->loadEnvironment();
        $this->loadConfig();
        $this->setRuntimeOptions();

        $this->singleton(Router::class, fn () => new Router());
        $this->singleton(Auth::class, fn () => new Auth());
        $this->singleton(Gate::class, fn () => new Gate());
        $this->singleton(Logger::class, fn () => new Logger(storage_path('logs')));
        $this->singleton(Console::class, fn () => new Console($this));
    }

    private function loadEnvironment(): void
    {
        $file = $this->path('.env');
        if (!is_file($file)) {
            return;
        }
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            if ($key === '' || getenv($key) !== false) {
                continue;
            }
            $value = trim($value, " \t\n\r\0\x0B\"'");
            putenv("$key=$value");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    private function loadConfig(): void
    {
        $files = glob($this->path('config') . '/*.php') ?: [];
        sort($files);
        foreach ($files as $file) {
            require $file;
        }
    }

    private function setRuntimeOptions(): void
    {
        date_default_timezone_set((string)config('app.timezone', 'Asia/Kathmandu'));
        mb_internal_encoding('UTF-8');
        Config::set('app.root', $this->root);
        Config::set('app.log_dir', storage_path('logs'));
    }

    public function registerMiddlewareMap(): void
    {
        $this->middlewareMap = [
            '*' => ['security-headers'],
            'web' => ['csrf', 'rate:general'],
            'guest' => ['guest-only'],
            'auth' => ['authenticate'],
            'customer' => ['auth', 'role:customer'],
            'manager' => ['auth', 'role:manager,admin'],
            'delivery' => ['auth', 'role:delivery'],
            'admin' => ['auth', 'role:admin'],
            'api' => ['json', 'csrf', 'rate:api'],
        ];
    }

    public function middlewareMap(): array
    {
        return $this->middlewareMap;
    }

    public function registerRoutes(): void
    {
        $web = $this->path('routes/web.php');
        $api = $this->path('routes/api.php');
        if (is_file($web)) {
            require $web;
        }
        if (is_file($api)) {
            require $api;
        }
    }

    public function dispatch(): Response
    {
        return $this->router()->dispatch($this->request(), $this);
    }

    // ------------------------------------------------------------------
    // Middleware resolution
    // ------------------------------------------------------------------

    /**
     * @return array<int, object> middleware instances
     */
    public function resolveMiddlewareList(array $names): array
    {
        $expanded = [];
        foreach ($names as $name) {
            $this->expandMiddleware($name, $expanded);
        }
        return array_map(fn (string $name) => $this->makeMiddleware($name), $expanded);
    }

    public function resolveMiddleware(string $name): object
    {
        return $this->makeMiddleware($name);
    }

    private function expandMiddleware(string $name, array &$out, array &$seen = []): void
    {
        if (in_array($name, $seen, true)) {
            return;
        }
        $seen[] = $name;
        if (isset($this->middlewareMap[$name])) {
            foreach ($this->middlewareMap[$name] as $child) {
                $this->expandMiddleware($child, $out, $seen);
            }
            return;
        }
        $out[] = $name;
    }

    private function makeMiddleware(string $name): object
    {
        if (preg_match('/^role:(.+)$/', $name, $m)) {
            return new \App\Middlewares\Role(array_map('trim', explode(',', $m[1])));
        }
        if (preg_match('/^rate:(.+)$/', $name, $m)) {
            return new \App\Middlewares\RateLimit($m[1]);
        }
        $class = match ($name) {
            'security-headers' => \App\Middlewares\SecurityHeaders::class,
            'csrf' => \App\Middlewares\Csrf::class,
            'guest-only' => \App\Middlewares\Guest::class,
            'authenticate' => \App\Middlewares\Authenticate::class,
            'email-verified' => \App\Middlewares\VerifyEmail::class,
            'json' => \App\Middlewares\JsonApi::class,
            default => throw new \RuntimeException("Unknown middleware [{$name}]"),
        };
        return $this->resolve($class);
    }

    // ------------------------------------------------------------------
    // Accessors
    // ------------------------------------------------------------------

    public function router(): Router
    {
        return $this->resolve(Router::class);
    }

    public function request(): Request
    {
        return $this->resolve(Request::class);
    }

    public function auth(): Auth
    {
        return $this->resolve(Auth::class);
    }

    public function gate(): Gate
    {
        return $this->resolve(Gate::class);
    }

    public function logger(): Logger
    {
        return $this->resolve(Logger::class);
    }

    public function console(): Console
    {
        return $this->resolve(Console::class);
    }

    public function registerExceptionHandler(): void
    {
        set_exception_handler(function (\Throwable $e) {
            $status = $e->getCode() === 404 ? 404 : (is_int($e->getCode()) && $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500);

            if ($e instanceof HttpException && request()->wantsJson()) {
                $payload = ['success' => false, 'message' => $e->getMessage()];
                if ($e->getPayload() !== null) {
                    $payload['errors'] = $e->getPayload();
                }
                Response::json($payload, $status)->send();
                return;
            }

            $this->logger()->error($e->getMessage(), [
                'file' => $e->getFile() . ':' . $e->getLine(),
                'trace' => substr($e->getTraceAsString(), 0, 2000),
            ]);

            if (request()->wantsJson() || $status === 404 && str_starts_with(request()->path(), '/api/')) {
                $body = Response::json(['message' => $e->getMessage()], $status);
            } else {
                $layout = str_starts_with(request()->path(), '/owner/')
                    ? 'portal'
                    : (str_starts_with(request()->path(), '/admin/') ? 'admin' : 'storefront');
                $body = View::render('errors.' . ($status === 404 ? '404' : '500'), [
                    'title' => $status === 404 ? 'Page not found' : 'Something went wrong',
                    'message' => $status === 404 ? 'The page you are looking for could not be found.' : 'An unexpected error occurred.',
                    'exception' => config('app.debug', false) ? $e : null,
                ], $layout);
                $body = Response::make($body, $status);
            }
            $body->send();
        });

        set_error_handler(function (int $severity, string $message, string $file, int $line) {
            if (!(error_reporting() & $severity)) {
                return false;
            }
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });
    }
}
