<?php

/** @var string $title */
/** @var string $content */
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Services\CartService;

$cartCount = (new CartService())->itemCount();
$siteName = config('app.name', 'Bitezy');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title ?? $siteName) ?> - Food Ordering</title>
  <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
</head>
<body>

<nav class="navbar">
  <div class="nav-container">
    <a href="<?= e(url('/')) ?>" class="nav-logo">
      <i class="fas fa-utensils"></i> <?= e($siteName) ?>
    </a>
    <div class="nav-toggle" id="navToggle">
      <span></span><span></span><span></span>
    </div>
    <ul class="nav-menu" id="navMenu">
      <li><a href="<?= e(url('/')) ?>"><i class="fas fa-home"></i> Home</a></li>
      <li><a href="<?= e(route('menu.index')) ?>"><i class="fas fa-book-open"></i> Menu</a></li>
      <?php if (auth()->check()) : ?>
        <li><a href="<?= e(route('cart.index')) ?>"><i class="fas fa-shopping-cart"></i> Cart <span id="cart-count" class="cart-badge"><?= $cartCount ?></span></a></li>
        <li><a href="<?= e(route('orders.index')) ?>"><i class="fas fa-truck"></i> Orders</a></li>
            <?php if (auth()->isDelivery()) : ?>
          <li><a href="<?= e(route('delivery.index')) ?>"><i class="fas fa-motorcycle"></i> Deliveries</a></li>
            <?php endif; ?>
            <?php if (auth()->isManager()) : ?>
          <li><a href="<?= e(route('admin.dashboard')) ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <?php endif; ?>
        <li>
          <form method="POST" action="<?= e(route('auth.logout')) ?>" style="display:inline;">
            <?= csrf_field() ?>
            <button type="submit" style="background:none;border:none;color:inherit;font:inherit;cursor:pointer;padding:0;display:inline-flex;gap:8px;align-items:center;">
              <i class="fas fa-sign-out-alt"></i> Logout
            </button>
          </form>
        </li>
        <li class="nav-user"><i class="fas fa-user-circle"></i> <?= e(auth()->user()->name ?? '') ?></li>
      <?php else : ?>
        <li><a href="<?= e(route('auth.login')) ?>"><i class="fas fa-sign-in-alt"></i> Login</a></li>
        <li><a href="<?= e(route('auth.register')) ?>" class="btn-nav-signup"><i class="fas fa-user-plus"></i> Sign Up</a></li>
      <?php endif; ?>
    </ul>
  </div>
</nav>

<main class="main-content">
  <?php foreach (\App\Core\Session::getFlashed() as $type => $message) : ?>
    <div class="flash flash--<?= e($type) ?>" data-flash="<?= e($message) ?>" data-flash-type="<?= e($type) ?>"><?= e($message) ?></div>
  <?php endforeach; ?>
  <?= $content ?>
</main>

<footer class="footer">
  <div class="footer-container">
    <div class="footer-grid">
      <div class="footer-col">
        <h4><i class="fas fa-utensils"></i> <?= e($siteName) ?></h4>
        <p>Delicious food delivered to your doorstep. Order from the best restaurants in town.</p>
        <div class="footer-social">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-youtube"></i></a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Quick Links</h4>
        <ul>
          <li><a href="<?= e(url('/')) ?>">Home</a></li>
          <li><a href="<?= e(route('menu.index')) ?>">Menu</a></li>
          <li><a href="<?= e(route('cart.index')) ?>">Cart</a></li>
          <li><a href="<?= e(route('orders.index')) ?>">Track Order</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Support</h4>
        <ul>
          <li><a href="#">Help Center</a></li>
          <li><a href="#">Terms of Service</a></li>
          <li><a href="#">Privacy Policy</a></li>
          <li><a href="#">Contact Us</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Contact Info</h4>
        <ul>
          <li><i class="fas fa-map-marker-alt"></i> 123 Food Street, NYC</li>
          <li><i class="fas fa-phone"></i> +1 555-123-4567</li>
          <li><i class="fas fa-envelope"></i> hello@<?= e(strtolower((string)$siteName)) ?>.com</li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> <?= e($siteName) ?>. All rights reserved.</p>
    </div>
  </div>
</footer>

<script src="<?= e(asset('js/script.js')) ?>"></script>
</body>
</html>