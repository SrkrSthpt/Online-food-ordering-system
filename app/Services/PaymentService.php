<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use RuntimeException;

/**
 * Payment processing. Only Cash on Delivery is currently available; other
 * gateways (eSewa, PayPal, ...) are listed in the UI as "not available".
 */
final class PaymentService
{
    /** Methods that can actually complete a payment today. */
    public const AVAILABLE_METHODS = ['cod'];

    /**
     * All methods shown on the payment page, with availability flag.
     *
     * @return array<int, array{code: string, label: string, available: bool}>
     */
    public function methods(): array
    {
        return [
            ['code' => 'cod', 'label' => 'Cash on Delivery', 'available' => true],
            ['code' => 'esewa', 'label' => 'eSewa', 'available' => false],
            ['code' => 'paypal', 'label' => 'PayPal', 'available' => false],
        ];
    }

    public function process(int $orderId, string $method): array
    {
        $order = Order::find($orderId);
        if ($order === null) {
            throw new RuntimeException('Order not found');
        }
        if (!in_array($method, self::AVAILABLE_METHODS, true)) {
            throw new RuntimeException('This payment method is not available yet.');
        }

        $payment = $order->payment();
        if ($payment !== null && $payment->status === 'completed') {
            throw new RuntimeException('Payment already processed');
        }

        if ($payment === null) {
            $payment = Payment::create([
                'order_id' => (int)$order->id,
                'amount' => (float)$order->total,
                'method' => 'cod',
                'status' => 'completed',
            ]);
        } else {
            $payment->update(['status' => 'completed']);
        }

        return [
            'payment' => $payment,
            'transaction_id' => null,
            'method' => 'cod',
            'amount' => (float)$order->total,
        ];
    }
}
