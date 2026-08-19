<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Session;
use App\Models\Order;
use App\Services\OrderService;
use RuntimeException;

/**
 * Manage orders. A manager (hotel) only sees and advances orders for their own
 * restaurant; admins see everything and can drive the full pipeline.
 */
final class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders)
    {
    }

    public function index(): Response
    {
        $user = auth()->user();
        $rows = $user !== null && (int)($user->restaurant_id ?? 0) > 0
            ? $this->orders->ordersForRestaurant((int)$user->restaurant_id)
            : $this->orders->allOrdersWithCustomer();

        foreach ($rows as &$row) {
            $next = $this->orders->nextStatus((string)($row['status'] ?? ''));
            if (!$user?->isAdmin() && !in_array($next, ['accepted', 'preparing', 'prepared'], true)) {
                $next = null; // courier steps belong to the delivery person
            }
            $row['next_status'] = $next;
        }
        unset($row);

        return $this->view('admin.orders', [
            'title' => 'Manage Orders',
            'orders' => $rows,
        ], 'admin');
    }

    public function update(): Response
    {
        $orderId = (int)request()->input('order_id');
        $status = (string)request()->input('status');

        $order = Order::find($orderId);
        if ($order === null) {
            Session::flash('error', 'Order not found.');
            return $this->redirect('/admin/orders');
        }

        $user = auth()->user();
        if (!$user?->isAdmin()) {
            if ((int)$user?->restaurant_id !== (int)$order->restaurant_id) {
                Session::flash('error', 'You can only update orders for your own restaurant.');
                return $this->redirect('/admin/orders');
            }
            if (!in_array($status, ['accepted', 'preparing', 'prepared'], true)) {
                Session::flash('error', 'Couriers handle the delivery steps.');
                return $this->redirect('/admin/orders');
            }
        }

        try {
            $this->orders->transition($order, $status);
            Session::flash('success', "Order #{$orderId} updated to \"{$status}\".");
        } catch (RuntimeException $e) {
            Session::flash('error', $e->getMessage());
        }

        return $this->redirect('/admin/orders');
    }
}
