<?php

declare(strict_types=1);

namespace App\Controllers\Customer;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentService;
use RuntimeException;

/**
 * Payment completion flow (legacy pages/payment.php + payment-success.php).
 */
final class PaymentController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly PaymentService $payments
    ) {
    }

    public function index(Request $request): Response
    {
        $orderId = (int)$request->input('order_id');
        $order = Order::find($orderId);

        if ($order === null || (int)$order->user_id !== (int)auth()->id()) {
            Session::flash('error', 'Order not found.');
            return $this->redirect('/cart');
        }

        $payment = $order->payment();
        if ($payment !== null && $payment->status === 'completed') {
            return $this->redirect('/orders');
        }

        return $this->view('payment.index', [
            'title' => 'Complete Payment',
            'order' => $order,
        ]);
    }

    public function success(Request $request): Response
    {
        $orderId = (int)$request->input('order_id');
        $payment = $orderId > 0 ? $this->orders->paymentForOrder($orderId) : null;

        return $this->view('payment.success', [
            'title' => 'Payment Successful',
            'payment' => $payment,
        ]);
    }
}
