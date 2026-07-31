<?php
require_once __DIR__ . '/../config.php';
$count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
header('Content-Type: application/json');
echo json_encode(['count' => $count]);
