<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '4058');
define('DB_NAME', 'bitezy');

define('SITE_NAME', 'Bitezy');
define('SITE_URL', 'http://localhost/bitezy');

define('CURRENCY', 'rs. ');
define('DELIVERY_FEE', 99);

define('PAYPAL_CLIENT_ID', 'YOUR_PAYPAL_CLIENT_ID');
define('PAYPAL_SECRET', 'YOUR_PAYPAL_SECRET');
define('PAYPAL_MODE', 'sandbox');

error_reporting(E_ALL);
ini_set('display_errors', 0);

function sanitize($input) {
  return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

function isAuthenticated() {
  return isset($_SESSION['user_id']);
}

function isAdmin() {
  return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isManager() {
  return isset($_SESSION['role']) && ($_SESSION['role'] === 'manager' || $_SESSION['role'] === 'admin');
}

function redirect($path) {
  header('Location: ' . SITE_URL . $path);
  exit();
}
