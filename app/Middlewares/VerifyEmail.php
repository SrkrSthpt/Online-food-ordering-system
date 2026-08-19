<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;

/**
 * Requires the user's email to be verified for sensitive flows.
 */
final class VerifyEmail implements Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        $user = auth()->user();
        if ($user !== null && empty($user->email_verified_at)) {
            if ($request->wantsJson()) {
                return Response::json(['message' => 'Please verify your email first.'], 403);
            }
            return Response::redirect(route('auth.login'), 302);
        }
        return $next($request);
    }
}
