<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Order;
use App\Services\OrderService;

/**
 * Courier dashboard (web). Lists the courier's assigned deliveries plus any
 * prepared orders that still need a courier.
 */
final class DeliveryController extends Controller
{
    public function __construct(private readonly OrderService $orders)
    {
    }

    public function index(): Response
    {
        $assigned = $this->orders->ordersForCourier((int)auth()->id());
        $available = $this->orders->availableOrdersForCourier();

        foreach ($assigned as &$row) {
            $order = Order::find((int)$row['id']);
            $row['timeline'] = $order !== null ? $this->orders->statusTimeline((int)$order->id) : [];
        }
        unset($row);

        return $this->view('delivery.index', [
            'title' => 'Delivery Dashboard',
            'assigned' => $assigned,
            'available' => $available,
        ], 'admin');
    }
}
