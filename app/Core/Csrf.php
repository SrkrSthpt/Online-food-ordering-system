<?php

declare(strict_types=1);

namespace App\Core;

/**
 * CSRF token generation and validation, stored in the session.
 */
final class Csrf
{
    public static function token(): string
    {
        $token = Session::get('_csrf_token');
        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(32));
            Session::put('_csrf_token', $token);
        }
        return $token;
    }

    public static function validate(?string $token): bool
    {
        $sessionToken = Session::get('_csrf_token');
        if (!is_string($sessionToken) || $sessionToken === '') {
            return false;
        }
        return is_string($token) && hash_equals($sessionToken, $token);
    }

    public static function regenerate(): void
    {
        Session::put('_csrf_token', bin2hex(random_bytes(32)));
    }
}
