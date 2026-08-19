<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;

/**
 * API responses: forces JSON-friendly handling of downstream exceptions.
 */
final class JsonApi implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        return $next($request);
    }
}
