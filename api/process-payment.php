<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if (!isAuthenticated()) {
  echo json_encode(['success' => false, 'message' => 'Please login first']);
  exit;
}

$orderId = (int)($_POST['order_id'] ?? 0);
$method = sanitize($_POST['method'] ?? 'paypal');

if (!$orderId) {
  echo json_encode(['success' => false, 'message' => 'Invalid order']);
  exit;
}

$order = dbQuery("SELECT * FROM orders WHERE id = ? AND user_id = ?", [$orderId, $_SESSION['user_id']]);
$order = $order ? $order->fetch_assoc() : null;

if (!$order) {
  echo json_encode(['success' => false, 'message' => 'Order not found']);
  exit;
}

$existing = dbQuery("SELECT * FROM payments WHERE order_id = ?", [$orderId]);
if ($existing && $existing->num_rows > 0) {
  echo json_encode(['success' => false, 'message' => 'Payment already processed']);
  exit;
}

$txnId = 'TXN_' . strtoupper(uniqid());

$payId = dbInsert("INSERT INTO payments (order_id, amount, method, status, transaction_id) VALUES (?, ?, ?, 'completed', ?)",
  [$orderId, $order['total'], $method, $txnId]);

dbQuery("UPDATE orders SET status = 'preparing' WHERE id = ?", [$orderId]);

echo json_encode([
  'success' => true,
  'message' => 'Payment successful!',
  'transaction_id' => $txnId,
  'redirect' => '/bitezy/pages/order-tracking.php'
]);
