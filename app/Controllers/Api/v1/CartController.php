<?php

declare(strict_types=1);

namespace App\Controllers\Api\v1;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\MenuItem;
use App\Services\CartService;
use RuntimeException;

/**
 * AJAX cart endpoints consumed by assets/js/script.js.
 */
final class CartController extends Controller
{
    public function __construct(private readonly CartService $cart)
    {
    }

    public function count(Request $request): Response
    {
        return $this->json(['count' => $this->cart->itemCount()]);
    }

    public function add(Request $request): Response
    {
        $itemId = (int)$request->input('item_id');
        $quantity = max(1, (int)$request->input('quantity', 1));

        if (MenuItem::find($itemId) === null) {
            return $this->json(['success' => false, 'message' => 'Item not found'], 404);
        }

        $this->cart->add($itemId, $quantity);
        return $this->json([
            'success' => true,
            'message' => 'Added to cart!',
            'count' => $this->cart->itemCount(),
        ]);
    }

    public function update(Request $request): Response
    {
        $itemId = (int)$request->input('item_id');
        $quantity = max(0, (int)$request->input('quantity', 0));

        if ($quantity === 0) {
            $this->cart->remove($itemId);
            return $this->json(['success' => true, 'message' => 'Item removed', 'count' => $this->cart->itemCount()]);
        }

        if (!in_array($itemId, array_keys($this->cart->lines()), true)) {
            return $this->json(['success' => false, 'message' => 'Item not in cart']);
        }

        $this->cart->set($itemId, $quantity);
        $item = MenuItem::find($itemId);
        $itemTotal = $item !== null ? round((float)$item->price * $quantity, 2) : 0;

        return $this->json([
            'success' => true,
            'item_total' => $itemTotal,
            'count' => $this->cart->itemCount(),
        ]);
    }

    public function destroy(Request $request): Response
    {
        $itemId = (int)$request->input('item_id');
        $this->cart->remove($itemId);

        return $this->json(['success' => true, 'message' => 'Item removed', 'count' => $this->cart->itemCount()]);
    }
}
