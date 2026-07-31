<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if (!isAuthenticated()) {
  echo json_encode(['success' => false, 'message' => 'Please login first', 'redirect' => '/bitezy/pages/login.php']);
  exit;
}

$itemId = (int)($_POST['item_id'] ?? 0);
$qty = max(1, (int)($_POST['quantity'] ?? 1));

if ($itemId <= 0) {
  echo json_encode(['success' => false, 'message' => 'Invalid item']);
  exit;
}

$item = dbQuery("SELECT * FROM menu_items WHERE id = ?", [$itemId]);
if (!$item || $item->num_rows === 0) {
  echo json_encode(['success' => false, 'message' => 'Item not found']);
  exit;
}

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if (isset($_SESSION['cart'][$itemId])) {
  $_SESSION['cart'][$itemId] += $qty;
} else {
  $_SESSION['cart'][$itemId] = $qty;
}

echo json_encode(['success' => true, 'message' => 'Added to cart!']);
