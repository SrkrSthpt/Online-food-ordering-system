<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Server-side template renderer (no external engine).
 *
 * View names use dot notation: 'customer.home' → app/Views/customer/home.php.
 * A layout wraps a view's output as `$content`.
 */
final class View
{
    private static array $shared = [];

    public static function share(string $key, mixed $value): void
    {
        static::$shared[$key] = $value;
    }

    public static function render(string $view, array $data = [], ?string $layout = 'storefront'): string
    {
        $data = array_merge(static::$shared, $data);
        $content = static::renderPartial($view, $data);

        if ($layout === null) {
            return $content;
        }

        $data['content'] = $content;
        $data['title'] = $data['title'] ?? config('app.name', 'Foodly');
        $data['__active'] = $data['__active'] ?? null;

        return static::renderPartial('layouts.' . $layout, $data, false);
    }

    public static function renderPartial(string $view, array $data = [], bool $includeShared = true): string
    {
        $file = view_path($view);
        if (!is_file($file)) {
            throw new \RuntimeException("View not found: [$view] ($file)");
        }

        if ($includeShared) {
            $data = array_merge(static::$shared, $data);
        }

        extract($data, EXTR_SKIP);
        $__app = Application::instance();

        ob_start();
        include $file;
        return (string)ob_get_clean();
    }

    public static function exists(string $view): bool
    {
        return is_file(view_path($view));
    }
}
