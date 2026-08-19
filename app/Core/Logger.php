<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Simple PSR-3-style file logger.
 */
final class Logger
{
    private string $dir;

    public function __construct(string $dir)
    {
        $this->dir = $dir;
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $line = sprintf(
            "[%s] %s.%s: %s %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            (string)getmypid(),
            $message,
            $context === [] ? '' : json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
        $file = $this->dir . DIRECTORY_SEPARATOR . date('Y-m-d') . '.log';
        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        if ((bool)config('app.debug', false)) {
            $this->log('debug', $message, $context);
        }
    }

    public static function channel(): self
    {
        return new self(config('app.log_dir', storage_path('logs')));
    }
}
