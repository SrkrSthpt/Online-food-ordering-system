<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Csrf as CsrfGuard;
use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * Validates the CSRF token on state-changing requests.
 */
final class Csrf implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $token = $request->input('_token')
                ?? $request->header('X-CSRF-TOKEN')
                ?? $request->header('X-XSRF-TOKEN');

            if (!CsrfGuard::validate(is_string($token) ? $token : null)) {
                if ($request->wantsJson()) {
                    return Response::json(['message' => 'CSRF token mismatch.'], 419);
                }
                Session::flash('error', 'Your session expired. Please try again.');
                return Response::redirect(Session::previousUrl(), 302);
            }
        }
        return $next($request);
    }
}
