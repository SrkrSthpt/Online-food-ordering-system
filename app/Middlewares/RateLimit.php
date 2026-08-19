<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Config;
use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;

/**
 * Simple sliding-window rate limiter backed by storage/cache/rate_limit.
 */
final class RateLimit implements Middleware
{
    public function __construct(private readonly string $bucket = 'general')
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        if (config('app.env') === 'testing') {
            return $next($request);
        }

        $max = (int)Config::get('security.rate_limit.max_attempts', 5);
        $window = (int)Config::get('security.rate_limit.window', 60);
        $key = $this->key($request);
        $now = time();

        $hits = $this->read($key);
        $hits = array_values(array_filter($hits, fn (int $t) => $t > $now - $window));

        if (count($hits) >= $max) {
            if ($request->wantsJson()) {
                return Response::json(['message' => 'Too many requests. Please try again later.'], 429);
            }
            return Response::make('Too many requests', 429);
        }

        $hits[] = $now;
        $this->write($key, $hits);

        return $next($request);
    }

    private function key(Request $request): string
    {
        return 'rl_' . md5($this->bucket . ':' . $request->ip() . ':' . ($request->header('User-Agent') ?? ''));
    }

    private function read(string $key): array
    {
        $file = $this->file($key);
        if (!is_file($file)) {
            return [];
        }
        $data = (array)json_decode((string)@file_get_contents($file), true);
        return $data['hits'] ?? [];
    }

    private function write(string $key, array $hits): void
    {
        @file_put_contents($this->file($key), json_encode(['hits' => $hits]));
    }

    private function file(string $key): string
    {
        $dir = storage_path('cache/rate_limit');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        return $dir . DIRECTORY_SEPARATOR . $key . '.json';
    }
}
