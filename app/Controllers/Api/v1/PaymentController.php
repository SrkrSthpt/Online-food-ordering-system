<?php

declare(strict_types=1);

namespace App\Controllers\Api\v1;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\Order;
use App\Services\PaymentService;
use RuntimeException;

/**
 * AJAX payment processing (legacy api/process-payment.php).
 */
final class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments)
    {
    }

    public function process(Request $request): Response
    {
        $orderId = (int)$request->input('order_id');
        $method = (string)$request->input('method', 'cod');

        $order = Order::find($orderId);
        if ($order === null || (int)$order->user_id !== (int)auth()->id()) {
            return $this->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        try {
            $result = $this->payments->process((int)$order->id, $method);
        } catch (RuntimeException $e) {
            return $this->json(['success' => false, 'message' => $e->getMessage()]);
        }

        return $this->json([
            'success' => true,
            'message' => 'Payment successful!',
            'transaction_id' => $result['transaction_id'],
            'redirect' => url('/payment/success?order_id=' . (int)$order->id),
        ]);
    }
}
