<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/**
 * RBAC gate. Bitezy uses a flat role model (customer / manager / admin),
 * so authorization is resolved from the role column without extra tables.
 */
final class Gate
{
    /**
     * Permission model for Bitezy:
     *   - manage_catalog, manage_orders, view_analytics  -> manager and admin
     *   - manage_platform                                -> admin only
     */
    private const ROLE_PERMISSIONS = [
        'customer' => [],
        'manager' => ['manage_catalog', 'manage_orders', 'view_analytics'],
        'admin' => ['manage_catalog', 'manage_orders', 'view_analytics', 'manage_platform'],
    ];

    public function allows(User $user, string $permission): bool
    {
        $role = $user->roleSlug();
        return in_array($permission, self::ROLE_PERMISSIONS[$role] ?? [], true);
    }

    public function denies(User $user, string $permission): bool
    {
        return !$this->allows($user, $permission);
    }

    public function forCurrentUser(string $permission): bool
    {
        $user = auth()->user();
        return $user !== null && $this->allows($user, $permission);
    }

    public function roleAllows(string $roleSlug, string $permission): bool
    {
        return in_array($permission, self::ROLE_PERMISSIONS[$roleSlug] ?? [], true);
    }
}
