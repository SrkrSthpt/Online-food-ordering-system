<?php

declare(strict_types=1);

namespace App\Controllers\Api\v1;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Order;
use App\Services\OrderService;
use RuntimeException;

/**
 * Courier (delivery person) API: their order queue, claiming prepared orders,
 * moving orders out for delivery / delivered and adjusting the ETA.
 */
final class DeliveryController extends Controller
{
    public function __construct(private readonly OrderService $orders)
    {
    }

    public function orders(): Response
    {
        return $this->json([
            'success' => true,
            'assigned' => $this->orders->ordersForCourier((int)auth()->id()),
            'available' => $this->orders->availableOrdersForCourier(),
        ]);
    }

    public function accept(Request $request): Response
    {
        $order = $this->findOrder($request);

        try {
            $this->orders->acceptByCourier($order, (int)auth()->id());
        } catch (RuntimeException $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return $this->json(['success' => true, 'message' => 'Order accepted.']);
    }

    public function status(Request $request): Response
    {
        $order = $this->findOrder($request);
        $status = (string)$request->input('status', 'out_for_delivery');

        if (!in_array($status, ['out_for_delivery', 'delivered'], true)) {
            return $this->json(['success' => false, 'message' => 'Invalid delivery status.'], 422);
        }
        if ((int)$order->delivery_person_id !== (int)auth()->id()) {
            return $this->json(['success' => false, 'message' => 'You are not assigned to this order.'], 403);
        }

        try {
            $this->orders->transition($order, $status);
        } catch (RuntimeException $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $message = $status === 'out_for_delivery' ? 'Order is on its way.' : 'Order delivered.';
        return $this->json(['success' => true, 'message' => $message]);
    }

    public function eta(Request $request): Response
    {
        $order = $this->findOrder($request);
        $minutes = (int)$request->input('minutes', 30);

        try {
            $this->orders->setEta($order, (int)auth()->id(), $minutes);
        } catch (RuntimeException $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return $this->json([
            'success' => true,
            'message' => 'ETA updated.',
            'eta' => (string)$order->eta,
        ]);
    }

    private function findOrder(Request $request): Order
    {
        $orderId = (int)$request->routeParam('id');
        $order = Order::find($orderId);
        if ($order === null) {
            throw new RuntimeException('Order not found', 404);
        }
        return $order;
    }
}
