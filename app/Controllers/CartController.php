<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Services\CartService;
use RuntimeException;

/**
 * Cart page (line items shown server-side from the session cart).
 */
final class CartController extends Controller
{
    public function __construct(private readonly CartService $cart)
    {
    }

    public function index(): Response
    {
        $items = $this->cart->items();
        return $this->view('cart.index', [
            'items' => $items,
            'subtotal' => $this->cart->subtotal(),
            'delivery' => (float)config('app.delivery_fee', 99),
            'total' => $this->cart->total(),
        ]);
    }
}
