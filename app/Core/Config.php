<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Runtime configuration store with dot-notation access.
 *
 * Config files under /config are loaded at boot; each returns nothing but
 * calls Config::set() so settings are mergeable across files.
 */
final class Config
{
    private static array $items = [];

    public static function set(string $key, mixed $value): void
    {
        static::$items[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::$items[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return array_key_exists($key, static::$items);
    }

    public static function all(): array
    {
        return static::$items;
    }
}
