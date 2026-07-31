<?php
require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if (!isManager()) {
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit;
}

$id = (int)($_POST['id'] ?? 0);
$type = sanitize($_POST['type'] ?? '');

if ($id <= 0) {
  echo json_encode(['success' => false, 'message' => 'Invalid ID']);
  exit;
}

try {
  if ($type === 'restaurant') {
    dbQuery("DELETE FROM restaurants WHERE id = ?", [$id]);
  } elseif ($type === 'menu') {
    dbQuery("DELETE FROM menu_items WHERE id = ?", [$id]);
  } else {
    echo json_encode(['success' => false, 'message' => 'Invalid type']);
    exit;
  }
  echo json_encode(['success' => true, 'message' => 'Deleted']);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Delete failed: ' . $e->getMessage()]);
}
