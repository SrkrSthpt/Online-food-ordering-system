<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * RBAC role gate. Constructor receives the allowed role slugs.
 */
final class Role implements Middleware
{
    /** @param array<int,string> $allowed */
    public function __construct(private readonly array $allowed)
    {
    }

    public function handle(Request $request, callable $next): Response
    {
        $user = auth()->user();
        if ($user === null) {
            if ($request->wantsJson()) {
                return Response::json(['message' => 'Authentication required.'], 401);
            }
            Session::flash('error', 'Please sign in to continue.');
            return Response::redirect(route('auth.login'), 302);
        }

        $slug = $user->roleSlug();
        if (!in_array($slug, $this->allowed, true)) {
            if ($request->wantsJson()) {
                return Response::json(['message' => 'Forbidden.'], 403);
            }
            $fallback = match (true) {
                $slug === 'admin' => '/admin',
                auth()->isManager() => '/admin',
                default => '/',
            };
            Session::flash('error', 'You do not have permission to view that page.');
            return Response::redirect($fallback, 302);
        }

        return $next($request);
    }
}
