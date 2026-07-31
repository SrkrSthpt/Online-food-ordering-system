<?php require_once __DIR__ . '/../config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo SITE_NAME; ?> - Food Ordering</title>
  <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<nav class="navbar">
  <div class="nav-container">
    <a href="<?php echo SITE_URL; ?>/index.php" class="nav-logo">
      <i class="fas fa-utensils"></i> Bitezy
    </a>
    <div class="nav-toggle" id="navToggle">
      <span></span><span></span><span></span>
    </div>
    <ul class="nav-menu" id="navMenu">
      <li><a href="<?php echo SITE_URL; ?>/index.php"><i class="fas fa-home"></i> Home</a></li>
      <li><a href="<?php echo SITE_URL; ?>/pages/menu.php"><i class="fas fa-book-open"></i> Menu</a></li>
      <?php if (isAuthenticated()): ?>
        <li><a href="<?php echo SITE_URL; ?>/pages/cart.php"><i class="fas fa-shopping-cart"></i> Cart <span id="cart-count" class="cart-badge">0</span></a></li>
        <li><a href="<?php echo SITE_URL; ?>/pages/order-tracking.php"><i class="fas fa-truck"></i> Orders</a></li>
        <?php if (isManager()): ?>
          <li><a href="<?php echo SITE_URL; ?>/admin/index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <?php endif; ?>
        <li><a href="<?php echo SITE_URL; ?>/pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        <li class="nav-user"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?></li>
      <?php else: ?>
        <li><a href="<?php echo SITE_URL; ?>/pages/login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
        <li><a href="<?php echo SITE_URL; ?>/pages/signup.php" class="btn-nav-signup"><i class="fas fa-user-plus"></i> Sign Up</a></li>
      <?php endif; ?>
    </ul>
  </div>
</nav>

<main class="main-content">
