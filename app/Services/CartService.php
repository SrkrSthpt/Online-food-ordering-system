<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Models\MenuItem;

/**
 * Session-backed cart. Line items are stored as menu_item_id => quantity so
 * the cart survives logins/guest browsing the same way the legacy app did,
 * but with the server as the single source of truth for prices.
 */
final class CartService
{
    private const KEY = 'cart';

    /**
     * @return array<int, int> menu_item_id => quantity
     */
    public function lines(): array
    {
        $cart = Session::get(self::KEY);
        return is_array($cart) ? array_map('intval', $cart) : [];
    }

    public function itemCount(): int
    {
        return array_sum($this->lines());
    }

    /**
     * @return array<int, array<string, mixed>> hydrated line items with subtotal
     */
    public function items(): array
    {
        $out = [];
        foreach ($this->lines() as $itemId => $qty) {
            $item = MenuItem::find((int)$itemId);
            if ($item === null) {
                continue;
            }
            $out[] = [
                'item' => $item,
                'quantity' => $qty,
                'subtotal' => round((float)$item->price * $qty, 2),
            ];
        }
        return $out;
    }

    public function subtotal(): float
    {
        $total = 0.0;
        foreach ($this->items() as $line) {
            $total += $line['subtotal'];
        }
        return round($total, 2);
    }

    public function total(): float
    {
        return round($this->subtotal() + (float)config('app.delivery_fee', 99), 2);
    }

    public function isEmpty(): bool
    {
        return count($this->lines()) === 0;
    }

    public function add(int $itemId, int $quantity = 1): void
    {
        $cart = $this->lines();
        $quantity = max(1, $quantity);
        $cart[$itemId] = ($cart[$itemId] ?? 0) + $quantity;
        $this->store($cart);
    }

    public function set(int $itemId, int $quantity): void
    {
        $cart = $this->lines();
        if ($quantity <= 0) {
            unset($cart[$itemId]);
        } else {
            $cart[$itemId] = $quantity;
        }
        $this->store($cart);
    }

    public function remove(int $itemId): void
    {
        $cart = $this->lines();
        unset($cart[$itemId]);
        $this->store($cart);
    }

    public function clear(): void
    {
        Session::forget(self::KEY);
    }

    private function store(array $cart): void
    {
        Session::put(self::KEY, $cart);
    }
}
