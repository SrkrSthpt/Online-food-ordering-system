<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if (!isAuthenticated()) {
  echo json_encode(['success' => false, 'message' => 'Please login first', 'redirect' => '/bitezy/pages/login.php']);
  exit;
}

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
  echo json_encode(['success' => false, 'message' => 'Cart is empty']);
  exit;
}

$total = 0;
$items = [];
foreach ($cart as $itemId => $qty) {
  $result = dbQuery("SELECT * FROM menu_items WHERE id = ?", [(int)$itemId]);
  if ($result && $item = $result->fetch_assoc()) {
    $subtotal = $item['price'] * $qty;
    $total += $subtotal;
    $items[] = ['item' => $item, 'qty' => $qty, 'price' => $item['price']];
  }
}

$total += DELIVERY_FEE;

$orderId = dbInsert("INSERT INTO orders (user_id, status, total) VALUES (?, 'pending', ?)",
  [$_SESSION['user_id'], $total]);

if (!$orderId) {
  echo json_encode(['success' => false, 'message' => 'Failed to create order']);
  exit;
}

foreach ($items as $it) {
  dbInsert("INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)",
    [$orderId, $it['item']['id'], $it['qty'], $it['price']]);
}

$_SESSION['cart'] = [];

echo json_encode(['success' => true, 'message' => 'Order placed!', 'order_id' => $orderId, 'redirect' => '/bitezy/pages/payment.php?order_id=' . $orderId]);
