<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Middleware contract.
 */
interface Middleware
{
    public function handle(Request $request, callable $next): Response;
}
