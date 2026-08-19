<?php

declare(strict_types=1);

use App\Core\Application;
use App\Core\Config;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Core\Auth;

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return Application::instance()->path($path);
    }
}

if (!function_exists('app_path')) {
    function app_path(string $path = ''): string
    {
        return base_path('app' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (!function_exists('config_path')) {
    function config_path(string $path = ''): string
    {
        return base_path('config' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return base_path('storage' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        return base_path('public' . ($path ? DIRECTORY_SEPARATOR . $path : ''));
    }
}

if (!function_exists('view_path')) {
    function view_path(string $view): string
    {
        return app_path('Views' . DIRECTORY_SEPARATOR . str_replace('.', DIRECTORY_SEPARATOR, $view) . '.php');
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
        return match (strtolower((string)$value)) {
            'true', '(true)' => true,
            'false', '(false)' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => $value,
        };
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return Config::get($key, $default);
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = config('app.url', '');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $v = config('app.asset_version', '1');
        return url('assets/' . ltrim($path, '/')) . '?v=' . urlencode($v);
    }
}

if (!function_exists('route')) {
    function route(string $name, array $params = []): string
    {
        return Application::instance()->router()->urlFor($name, $params);
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        $old = Session::get('_old_input', []);
        return $old[$key] ?? $default;
    }
}

if (!function_exists('flash')) {
    function flash(string $type = 'success'): ?string
    {
        $flashes = Session::get('_flash', []);
        return $flashes[$type] ?? null;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Csrf::token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('auth')) {
    function auth(): Auth
    {
        return Application::instance()->auth();
    }
}

if (!function_exists('request')) {
    function request(): Request
    {
        return Application::instance()->request();
    }
}

if (!function_exists('setting')) {
    /**
     * Site setting. Bitezy reads these from config so views stay simple.
     */
    function setting(string $key, mixed $default = ''): mixed
    {
        static $cache = null;
        if ($cache === null) {
            $cache = [
                'site.name' => config('app.name', 'Bitezy'),
                'site.currency' => config('app.currency', 'NPR'),
                'site.delivery_fee' => config('app.delivery_fee', 99),
                'site.tagline' => 'Your favourite food, delivered fast.',
            ];
        }
        return $cache[$key] ?? $default;
    }
}

if (!function_exists('money')) {
    function money(mixed $amount): string
    {
        return config('app.currency_symbol', 'rs. ') . number_format((float)$amount, 2);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url, int $status = 302): Response
    {
        return Response::redirect($url, $status);
    }
}

if (!function_exists('abort')) {
    function abort(int $status = 404, string $message = ''): never
    {
        throw new \RuntimeException($message !== '' ? $message : 'Not found', $status);
    }
}

if (!function_exists('back')) {
    function back(int $status = 302): Response
    {
        return redirect(Session::get('_previous_url', url('/')), $status);
    }
}
