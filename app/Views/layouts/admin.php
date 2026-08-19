<?php

/** @var string $title */
/** @var string $content */
use App\Services\CartService;

$user = auth()->user();
$siteName = config('app.name', 'Bitezy');
$active = $__active ?? 'dashboard';
$isDelivery = $user?->isDelivery() ?? false;
$nav = [];
if ($isDelivery) {
    $nav = ['dashboard' => ['Deliveries', route('delivery.index')]];
} else {
    $nav = [
        'dashboard' => ['Dashboard', route('admin.dashboard')],
        'restaurants' => ['Restaurants', route('admin.restaurants.index')],
        'menu-items' => ['Menu Items', route('admin.menu-items.index')],
        'orders' => ['Orders', route('admin.orders.index')],
    ];
    if ($user?->isAdmin()) {
        $nav['users'] = ['Users', route('admin.users.index')];
    }
}
$showOrderAlerts = !$isDelivery;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title) ?> - <?= e($siteName) ?> <?= $isDelivery ? 'Delivery' : 'Admin' ?></title>
  <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
</head>
<body>

<div class="admin-container">
  <div class="admin-sidebar">
    <h3><i class="fas fa-utensils"></i> <?= e($siteName) ?> <?= $isDelivery ? 'Delivery' : 'Admin' ?></h3>
    <?php foreach ($nav as $key => [$label, $href]) : ?>
      <a href="<?= e($href) ?>" class="<?= $active === $key ? 'active' : '' ?>">
        <i class="fas <?= match ($key) {
            'dashboard' => 'fa-chart-pie',
            'restaurants' => 'fa-store',
            'menu-items' => 'fa-utensils',
            'orders' => 'fa-truck',
            'users' => 'fa-users',
            default => 'fa-circle',
                      } ?>"></i> <?= e($label) ?>
        <?php if ($showOrderAlerts && $key === 'orders') : ?>
          <span class="nav-badge" id="pendingOrdersBadge" style="display:none;">0</span>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
    <a href="<?= e(url('/')) ?>"><i class="fas fa-arrow-left"></i> Back to Site</a>
  </div>

  <div class="admin-content">
    <div class="admin-header">
      <h2><?= e($title) ?></h2>
      <span style="color:var(--gray);">Welcome, <?= e($user?->name ?? '') ?></span>
    </div>

    <?php foreach (\App\Core\Session::getFlashed() as $type => $message) : ?>
      <div class="flash flash--<?= e($type) ?>" data-flash="<?= e($message) ?>" data-flash-type="<?= e($type) ?>"><?= e($message) ?></div>
    <?php endforeach; ?>

    <?= $content ?>
  </div>
</div>

<script src="<?= e(asset('js/script.js')) ?>"></script>
<?php if ($showOrderAlerts) : ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof pollAdminOrders === 'function') {
    pollAdminOrders();
    setInterval(pollAdminOrders, 15000);
  }
});
</script>
<?php endif; ?>
</body>
</html>