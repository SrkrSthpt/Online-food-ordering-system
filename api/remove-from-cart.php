<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

$itemId = (int)($_POST['item_id'] ?? 0);
unset($_SESSION['cart'][$itemId]);

echo json_encode(['success' => true, 'message' => 'Item removed']);
