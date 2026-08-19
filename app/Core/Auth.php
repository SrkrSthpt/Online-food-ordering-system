<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/**
 * Authentication guard. Single guard (session) in v1.
 */
final class Auth
{
    private const SESSION_KEY = '_auth_user_id';

    private ?User $user = null;

    public function user(): ?User
    {
        if ($this->user !== null) {
            return $this->user;
        }
        $id = Session::get(self::SESSION_KEY);
        if (!is_numeric($id)) {
            return null;
        }
        $this->user = User::find((int)$id);
        if ($this->user !== null && !(bool)$this->user->is_active) {
            $this->logout();
            return null;
        }
        return $this->user;
    }

    public function id(): ?int
    {
        $id = Session::get(self::SESSION_KEY);
        return is_numeric($id) ? (int)$id : null;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return !$this->check();
    }

    public function login(User $user): void
    {
        Session::regenerate(true);
        Session::put(self::SESSION_KEY, (int)$user->id);
        $this->user = $user;
        Session::put('_previous_url', url('/'));
    }

    public function loginById(int $id): bool
    {
        $user = User::find($id);
        if ($user === null) {
            return false;
        }
        $this->login($user);
        return true;
    }

    public function attempt(string $email, string $password, bool $remember = false): bool
    {
        $user = User::firstWhere('email', $email);
        if ($user === null || !(bool)$user->is_active || !password_verify($password, (string)$user->password)) {
            return false;
        }
        if (password_needs_rehash((string)$user->password, PASSWORD_ARGON2ID)) {
            $user->update(['password' => password_hash($password, PASSWORD_ARGON2ID)]);
        }
        $this->login($user);
        return true;
    }

    public function logout(): void
    {
        $this->user = null;
        Session::forget(self::SESSION_KEY);
        Session::regenerate(true);
    }

    /**
     * @return string[] role slugs the user belongs to
     */
    public function roles(): array
    {
        $user = $this->user();
        return $user !== null ? [$user->roleSlug()] : [];
    }

    public function isRole(string $slug): bool
    {
        $user = $this->user();
        return $user !== null && $user->roleSlug() === $slug;
    }

    public function isManager(): bool
    {
        return $this->isRole('manager') || $this->isRole('admin');
    }

    public function isAdmin(): bool
    {
        return $this->isRole('admin');
    }

    public function isCustomer(): bool
    {
        return $this->isRole('customer');
    }

    public function isDelivery(): bool
    {
        return $this->isRole('delivery');
    }
}
