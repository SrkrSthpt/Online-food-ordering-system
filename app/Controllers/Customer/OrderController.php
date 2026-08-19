<?php

declare(strict_types=1);

namespace App\Controllers\Customer;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Order;
use App\Services\OrderService;

/**
 * Order tracking page — lists the customer's confirmed orders with a live
 * status stepper, ETA and a timeline, mirroring the legacy tracking page.
 */
final class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders)
    {
    }

    public function index(): Response
    {
        $rows = $this->orders->ordersForUser((int)auth()->id());

        $orders = [];
        foreach ($rows as $row) {
            $order = Order::find((int)$row['id']);
            if ($order === null) {
                continue;
            }
            $keys = ['method', 'transaction_id', 'payment_status', 'restaurant_name', 'delivery_name'];
            foreach ($keys as $key) {
                $order->{$key === 'method' ? 'payment_method' : $key} = $row[$key] ?? '';
            }
            $order->timeline = $this->orders->statusTimeline((int)$order->id);
            $orders[] = $order;
        }

        return $this->view('orders.index', [
            'title' => 'My Orders',
            'orders' => $orders,
        ]);
    }
}
