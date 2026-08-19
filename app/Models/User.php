<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Auth;
use App\Core\BaseModel;

/**
 * User (customer / manager / admin).
 */
final class User extends BaseModel
{
    protected string $table = 'users';
    protected array $fillable = ['name', 'email', 'password', 'role', 'restaurant_id'];
    protected array $casts = [
        'id' => 'int',
        'restaurant_id' => 'int',
        'is_active' => 'bool',
    ];

    public function getAttribute(string $key): mixed
    {
        if ($key === 'is_active') {
            $raw = parent::getAttribute('is_active');
            return $raw === null ? true : (bool)$raw;
        }
        return parent::getAttribute($key);
    }

    public function roleSlug(): string
    {
        return (string)$this->role;
    }

    public function isManager(): bool
    {
        return in_array($this->role, ['manager', 'admin'], true);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isDelivery(): bool
    {
        return $this->role === 'delivery';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function restaurant(): ?Restaurant
    {
        return $this->restaurant_id !== null ? Restaurant::find($this->restaurant_id) : null;
    }

    public static function helper(): Auth
    {
        return auth();
    }
}
