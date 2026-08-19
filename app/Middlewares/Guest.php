<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * Redirects already-authenticated users away from guest-only pages.
 */
final class Guest implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (auth()->check()) {
            return Response::redirect(Session::previousUrl(), 302);
        }
        return $next($request);
    }
}
