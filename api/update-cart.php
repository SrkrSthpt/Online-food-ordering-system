<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if (!isAuthenticated()) {
  echo json_encode(['success' => false, 'message' => 'Please login first']);
  exit;
}

$itemId = (int)($_POST['item_id'] ?? 0);
$qty = max(0, (int)($_POST['quantity'] ?? 0));

if ($qty === 0) {
  unset($_SESSION['cart'][$itemId]);
  echo json_encode(['success' => true, 'message' => 'Item removed']);
  exit;
}

if (isset($_SESSION['cart'][$itemId])) {
  $_SESSION['cart'][$itemId] = $qty;
  $result = dbQuery("SELECT price FROM menu_items WHERE id = ?", [$itemId]);
  $price = 0;
  if ($result && $row = $result->fetch_assoc()) {
    $price = $row['price'];
  }
  echo json_encode(['success' => true, 'item_total' => $price * $qty]);
  exit;
}

echo json_encode(['success' => false, 'message' => 'Item not in cart']);
