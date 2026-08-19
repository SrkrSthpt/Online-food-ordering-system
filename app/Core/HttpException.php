<?php

declare(strict_types=1);

namespace App\Core;

/**
 * HTTP exception with an optional structured payload for JSON responses.
 */
final class HttpException extends \RuntimeException
{
    public function __construct(
        string $message,
        int $status = 400,
        private readonly mixed $payload = null
    ) {
        parent::__construct($message, $status);
    }

    public function getPayload(): mixed
    {
        return $this->payload;
    }
}
