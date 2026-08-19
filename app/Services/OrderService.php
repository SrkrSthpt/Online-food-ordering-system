<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusLog;
use App\Models\Payment;
use RuntimeException;

/**
 * Order placement, tracking queries and the status transition engine.
 *
 * The lifecycle is:
 *   pending -> accepted -> preparing -> prepared -> out_for_delivery -> delivered
 *          (customer)     (hotel)                (auto-assign courier)  (courier)
 */
final class OrderService
{
    /**
     * Allowed forward transitions per current status.
     *
     * @var array<string, array<int, string>>
     */
    private const TRANSITIONS = [
        'pending' => ['accepted'],
        'accepted' => ['preparing'],
        'preparing' => ['prepared'],
        'prepared' => ['out_for_delivery'],
        'out_for_delivery' => ['delivered'],
    ];

    /**
     * Place an order from the current cart. Every line item must belong to the
     * same restaurant. Creates the order, its line items and a pending Cash on
     * Delivery payment record atomically, then clears the cart.
     */
    public function place(CartService $cart): Order
    {
        if ($cart->isEmpty()) {
            throw new RuntimeException('Cart is empty');
        }

        $items = $cart->items();
        $restaurantId = null;
        foreach ($items as $line) {
            $restaurantId ??= (int)($line['item']->restaurant_id ?? 0);
            if ($restaurantId !== (int)($line['item']->restaurant_id ?? 0)) {
                throw new RuntimeException(
                    'Your cart contains items from different restaurants. Please order from one at a time.'
                );
            }
        }

        if ($restaurantId === null || $restaurantId === 0) {
            throw new RuntimeException('This item is not linked to a restaurant.');
        }

        return Database::connect()->transaction(function (Database $db) use ($cart, $items, $restaurantId): Order {
            $order = Order::create([
                'user_id' => auth()->id(),
                'restaurant_id' => $restaurantId,
                'status' => 'pending',
                'total' => $cart->total(),
            ]);

            foreach ($items as $line) {
                OrderItem::create([
                    'order_id' => (int)$order->id,
                    'menu_item_id' => (int)$line['item']->id,
                    'quantity' => $line['quantity'],
                    'price' => (float)$line['item']->price,
                ]);
            }

            OrderStatusLog::create([
                'order_id' => (int)$order->id,
                'status' => 'pending',
            ]);

            // Cash on Delivery is the only payment method; record it up front
            // and mark it completed when the customer confirms it.
            Payment::create([
                'order_id' => (int)$order->id,
                'amount' => (float)$order->total,
                'method' => 'cod',
                'status' => 'pending',
            ]);

            $cart->clear();
            return $order;
        });
    }

    /**
     * The single valid next step for a given status (or null when finished).
     */
    public function nextStatus(string $status): ?string
    {
        return self::TRANSITIONS[$status][0] ?? null;
    }

    /**
     * Move an order forward one step in the pipeline. Enforces the allowed
     * transitions, records the change in the timeline and triggers side
     * effects (courier auto-assignment + ETA once the food is prepared).
     */
    public function transition(Order $order, string $toStatus): void
    {
        $from = (string)$order->status;
        if (!in_array($toStatus, self::TRANSITIONS[$from] ?? [], true)) {
            throw new RuntimeException("Cannot move order #{$order->id} from \"{$from}\" to \"{$toStatus}\".");
        }

        $order->update(['status' => $toStatus]);
        OrderStatusLog::create([
            'order_id' => (int)$order->id,
            'status' => $toStatus,
        ]);

        if ($toStatus === 'prepared') {
            $this->assignDeliveryPerson($order);
        } elseif ($toStatus === 'delivered') {
            $order->update(['eta' => null]);
        }
    }

    /**
     * Auto-assign the least-loaded available courier and stamp the system ETA.
     * If no courier is online the order stays prepared and can be claimed by a
     * courier from the delivery dashboard.
     */
    private function assignDeliveryPerson(Order $order): void
    {
        $candidates = Database::connect()->query(
            'SELECT u.id,
                    (SELECT COUNT(*) FROM orders o
                     WHERE o.delivery_person_id = u.id
                       AND o.status IN (\'prepared\', \'out_for_delivery\')) AS active_count
             FROM users u
             WHERE u.role = \'delivery\' AND u.is_active = 1
             ORDER BY active_count ASC, u.id ASC
             LIMIT 1'
        );

        if (empty($candidates)) {
            return;
        }

        $eta = time() + (int)config('app.delivery_eta_minutes', 30) * 60;
        $order->update([
            'delivery_person_id' => (int)$candidates[0]['id'],
            'eta' => date('Y-m-d H:i:s', $eta),
        ]);
    }

    /**
     * Claim an unassigned prepared order for a courier.
     */
    public function acceptByCourier(Order $order, int $courierId): void
    {
        if ((string)$order->status !== 'prepared') {
            throw new RuntimeException('This order is not ready for delivery yet.');
        }
        if ($order->delivery_person_id !== null && (int)$order->delivery_person_id !== $courierId) {
            throw new RuntimeException('This order is already assigned to another courier.');
        }
        $order->update(['delivery_person_id' => $courierId]);
    }

    /**
     * Let a courier adjust the ETA for an order they are assigned to.
     */
    public function setEta(Order $order, int $courierId, int $minutesFromNow): void
    {
        if ((int)$order->delivery_person_id !== $courierId) {
            throw new RuntimeException('You are not assigned to this order.');
        }
        $minutes = max(1, $minutesFromNow);
        $order->update([
            'eta' => date('Y-m-d H:i:s', time() + $minutes * 60),
        ]);
    }

    /**
     * Orders with a completed payment, newest first — what the customer sees.
     *
     * @return array<int, array<string, mixed>>
     */
    public function ordersForUser(int $userId): array
    {
        return Database::connect()->query(
            'SELECT o.*, p.method, p.transaction_id, p.status AS payment_status,
                    r.name AS restaurant_name, d.name AS delivery_name
             FROM orders o
             JOIN payments p ON p.order_id = o.id
             LEFT JOIN restaurants r ON r.id = o.restaurant_id
             LEFT JOIN users d ON d.id = o.delivery_person_id
             WHERE o.user_id = ? AND p.status = \'completed\'
             ORDER BY o.created_at DESC',
            [$userId]
        );
    }

    /**
     * Incoming orders for a hotel's restaurant.
     *
     * @return array<int, array<string, mixed>>
     */
    public function ordersForRestaurant(int $restaurantId): array
    {
        return Database::connect()->query(
            'SELECT o.*, p.method, p.status AS payment_status,
                    u.name AS user_name, d.name AS delivery_name
             FROM orders o
             JOIN users u ON u.id = o.user_id
             LEFT JOIN payments p ON p.order_id = o.id
             LEFT JOIN users d ON d.id = o.delivery_person_id
             WHERE o.restaurant_id = ?
             ORDER BY o.created_at DESC',
            [$restaurantId]
        );
    }

    /**
     * Orders currently assigned to a courier and not yet finished.
     *
     * @return array<int, array<string, mixed>>
     */
    public function ordersForCourier(int $courierId): array
    {
        return Database::connect()->query(
            'SELECT o.*, r.name AS restaurant_name, u.name AS user_name
             FROM orders o
             JOIN users u ON u.id = o.user_id
             LEFT JOIN restaurants r ON r.id = o.restaurant_id
             WHERE o.delivery_person_id = ?
               AND o.status IN (\'prepared\', \'out_for_delivery\')
             ORDER BY o.created_at ASC',
            [$courierId]
        );
    }

    /**
     * Prepared orders with no courier yet — available to be claimed.
     *
     * @return array<int, array<string, mixed>>
     */
    public function availableOrdersForCourier(): array
    {
        return Database::connect()->query(
            'SELECT o.*, r.name AS restaurant_name, u.name AS user_name
             FROM orders o
             JOIN users u ON u.id = o.user_id
             LEFT JOIN restaurants r ON r.id = o.restaurant_id
             WHERE o.status = \'prepared\' AND o.delivery_person_id IS NULL
             ORDER BY o.created_at ASC'
        );
    }

    /**
     * All orders with related names — admin overview.
     *
     * @return array<int, array<string, mixed>>
     */
    public function allOrdersWithCustomer(): array
    {
        return Database::connect()->query(
            'SELECT o.*, u.name AS user_name, u.email AS user_email,
                    r.name AS restaurant_name, d.name AS delivery_name,
                    p.status AS payment_status, p.method
             FROM orders o
             JOIN users u ON u.id = o.user_id
             LEFT JOIN restaurants r ON r.id = o.restaurant_id
             LEFT JOIN users d ON d.id = o.delivery_person_id
             LEFT JOIN payments p ON p.order_id = o.id
             ORDER BY o.created_at DESC'
        );
    }

    /**
     * Full timeline of status changes for an order.
     *
     * @return array<int, array<string, mixed>>
     */
    public function statusTimeline(int $orderId): array
    {
        $logs = OrderStatusLog::where('order_id', $orderId);
        $timeline = [];
        foreach ($logs as $log) {
            $timeline[] = [
                'status' => (string)$log->status,
                'note' => (string)$log->note,
                'created_at' => (string)$log->created_at,
            ];
        }
        return $timeline;
    }

    public function paymentForOrder(int $orderId): ?array
    {
        $payment = Payment::firstWhere('order_id', $orderId);
        if ($payment === null) {
            return null;
        }
        $data = $payment->attributes();
        $order = Order::find($orderId);
        if ($order !== null) {
            $data['total'] = $order->total;
        }
        return $data;
    }
}
