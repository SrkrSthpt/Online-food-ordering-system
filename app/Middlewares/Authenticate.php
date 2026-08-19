<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * Requires an authenticated user. JSON routes get a 401.
 */
final class Authenticate implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (!auth()->check()) {
            if ($request->wantsJson()) {
                return Response::json(['message' => 'Authentication required.'], 401);
            }
            Session::flash('error', 'Please sign in to continue.');
            Session::put('_intended_url', $request->fullUrl());
            return Response::redirect(route('auth.login'), 302);
        }
        return $next($request);
    }
}
