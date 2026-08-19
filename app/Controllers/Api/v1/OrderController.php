<?php

declare(strict_types=1);

namespace App\Controllers\Api\v1;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use RuntimeException;

/**
 * AJAX order placement (legacy api/place-order.php).
 */
final class OrderController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly OrderService $orders
    ) {
    }

    public function store(): Response
    {
        if ($this->cart->isEmpty()) {
            return $this->json(['success' => false, 'message' => 'Cart is empty']);
        }

        try {
            $order = $this->orders->place($this->cart);
        } catch (RuntimeException $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()]);
        }

        return $this->json([
            'success' => true,
            'message' => 'Order placed!',
            'order_id' => (int)$order->id,
            'redirect' => url('/payment?order_id=' . (int)$order->id),
        ]);
    }

    /**
     * Live tracking payload used by the customer order page (auto-polling).
     */
    public function track(Request $request): Response
    {
        $orderId = (int)$request->routeParam('id');
        $order = Order::find($orderId);
        if ($order === null || (int)$order->user_id !== (int)auth()->id()) {
            return $this->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        $delivery = $order->deliveryPerson();

        return $this->json([
            'success' => true,
            'status' => (string)$order->status,
            'eta' => (string)$order->eta,
            'delivery_name' => $delivery?->name ?? '',
            'timeline' => $this->orders->statusTimeline((int)$order->id),
        ]);
    }
}
