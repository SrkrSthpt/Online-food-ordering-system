<?php require_once __DIR__ . '/../includes/db.php';
if (!isAuthenticated()) redirect('/pages/login.php');

$userId = $_SESSION['user_id'];
$orders = dbQuery("SELECT o.* FROM orders o JOIN payments p ON p.order_id = o.id WHERE o.user_id = ? AND p.status = 'completed' ORDER BY o.created_at DESC", [$userId]);
$ordersList = $orders ? $orders->fetch_all(MYSQLI_ASSOC) : [];
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="tracking-container">
  <h2 style="margin-bottom:24px;"><i class="fas fa-truck"></i> My Orders</h2>

  <?php if (empty($ordersList)): ?>
    <div style="text-align:center;padding:80px 20px;color:var(--gray);">
      <i class="fas fa-box-open" style="font-size:4rem;margin-bottom:20px;color:var(--border);"></i>
      <h3>No orders yet</h3>
      <p>Place your first order to see it here.</p>
      <a href="menu.php" class="btn btn-primary" style="margin-top:20px">
        <i class="fas fa-utensils"></i> Order Now
      </a>
    </div>
  <?php else: ?>
    <?php foreach ($ordersList as $order):
      $statusMap = ['pending' => 0, 'preparing' => 1, 'delivered' => 2];
      $currentStep = $statusMap[$order['status']] ?? 0;
      $steps = [
        ['icon' => 'fa-clock', 'label' => 'Pending'],
        ['icon' => 'fa-fire', 'label' => 'Preparing'],
        ['icon' => 'fa-check-circle', 'label' => 'Delivered']
      ];
    ?>
    <div class="order-card">
      <div class="order-header">
        <span class="order-id"><i class="fas fa-receipt"></i> Order #<?php echo $order['id']; ?></span>
        <span class="order-status status-<?php echo $order['status']; ?>">
          <?php echo ucfirst($order['status']); ?>
        </span>
      </div>

      <div class="progress-container">
        <div class="progress-bar-fill" style="width:0%"></div>
        <?php foreach ($steps as $idx => $step): ?>
          <div class="progress-step <?php
            echo $idx < $currentStep ? 'completed' : ($idx === $currentStep ? 'active' : '');
          ?>">
            <div class="step-icon"><i class="fas <?php echo $step['icon']; ?>"></i></div>
            <span class="step-label"><?php echo $step['label']; ?></span>
          </div>
        <?php endforeach; ?>
      </div>

      <div style="font-size:0.85rem;color:var(--gray);display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-top:16px;padding-top:16px;border-top:1px solid var(--border);">
        <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></span>
        <span><i class="fas fa-money-bill-wave"></i> Total: <?php echo CURRENCY; ?><?php echo number_format($order['total'], 2); ?></span>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
