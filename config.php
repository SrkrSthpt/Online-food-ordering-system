<?php
session_start();

function loadEnv($path) {
  if (!file_exists($path)) return;
  $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') continue;
    if (strpos($line, '=') === false) continue;
    list($key, $value) = explode('=', $line, 2);
    $key = trim($key);
    $value = trim($value, " \t\"'");
    if ($key === '') continue;
    putenv("$key=$value");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
  }
}

loadEnv(__DIR__ . '/.env');

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_USER', getenv('DB_USERNAME') ?: (getenv('DB_USER') ?: 'root'));
define('DB_PASS', getenv('DB_PASSWORD') ?: (getenv('DB_PASS') ?: ''));
define('DB_NAME', getenv('DB_DATABASE') ?: (getenv('DB_NAME') ?: 'bitezy'));

define('SITE_NAME', getenv('APP_NAME') ?: 'Bitezy');
define('SITE_URL', getenv('APP_URL') ?: 'http://localhost/bitezy');

define('CURRENCY', getenv('APP_CURRENCY_SYMBOL') ?: 'rs. ');
define('DELIVERY_FEE', getenv('DELIVERY_FEE') ?: 99);

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
