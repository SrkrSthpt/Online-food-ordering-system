<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Config;

/**
 * Session wrapper with flash data, CSRF storage and a pluggable driver.
 *
 * v1 ships a file driver; the interface mirrors PHP's native session so a
 * database driver can be swapped in without touching application code.
 */
final class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (static::$started) {
            return;
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            static::$started = true;
            return;
        }

        $path = config('session.path', storage_path('session'));
        if (!is_dir($path)) {
            @mkdir($path, 0775, true);
        }
        session_save_path($path);
        session_name(config('session.cookie', 'foodly_session'));
        session_set_cookie_params([
            'lifetime' => (int)config('session.lifetime', 120) * 60,
            'path' => '/',
            'domain' => '',
            'secure' => (bool)config('session.secure', false),
            'httponly' => true,
            'samesite' => config('session.same_site', 'Lax'),
        ]);

        session_start();
        static::$started = true;
    }

    public static function put(string $key, mixed $value): void
    {
        static::ensureStarted();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        static::ensureStarted();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        static::ensureStarted();
        return array_key_exists($key, $_SESSION);
    }

    public static function forget(string $key): void
    {
        static::ensureStarted();
        unset($_SESSION[$key]);
    }

    public static function pull(string $key, mixed $default = null): mixed
    {
        $value = static::get($key, $default);
        static::forget($key);
        return $value;
    }

    public static function flash(string $type, string $message): void
    {
        static::ensureStarted();
        $_SESSION['_flash'][$type] = $message;
    }

    public static function getFlashed(): array
    {
        static::ensureStarted();
        $flashes = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $flashes;
    }

    public static function flush(): void
    {
        static::ensureStarted();
        $_SESSION = [];
    }

    public static function regenerate(bool $destroy = false): void
    {
        static::ensureStarted();
        if ($destroy) {
            session_regenerate_id(true);
        } else {
            session_regenerate_id();
        }
    }

    public static function id(): string
    {
        static::ensureStarted();
        return session_id();
    }

    public static function destroy(): void
    {
        static::ensureStarted();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
        static::$started = false;
    }

    public static function previousUrl(): string
    {
        static::ensureStarted();
        return (string)($_SESSION['_previous_url'] ?? url('/'));
    }

    private static function ensureStarted(): void
    {
        if (!static::$started) {
            static::start();
        }
    }
}
