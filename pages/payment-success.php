<?php require_once __DIR__ . '/../includes/db.php';
if (!isAuthenticated()) redirect('/pages/login.php');

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$payment = null;
if ($orderId) {
  $pResult = dbQuery("SELECT p.*, o.total FROM payments p JOIN orders o ON p.order_id = o.id WHERE p.order_id = ?", [$orderId]);
  $payment = $pResult ? $pResult->fetch_assoc() : null;
}
include __DIR__ . '/../includes/header.php';
?>

<div style="max-width:600px;margin:60px auto;padding:40px;text-align:center;background:white;border-radius:var(--radius-lg);box-shadow:var(--shadow-lg);">
  <i class="fas fa-check-circle" style="font-size:5rem;color:var(--success);margin-bottom:20px;"></i>
  <h2>Payment Successful!</h2>
  <p style="color:var(--gray);margin:16px 0;">
    <?php if ($payment): ?>
      Your payment of <strong><?php echo CURRENCY; ?><?php echo number_format($payment['total'], 2); ?></strong>
      via <strong><?php echo ucfirst($payment['method']); ?></strong> has been completed.
      Transaction ID: <strong><?php echo htmlspecialchars($payment['transaction_id']); ?></strong>
    <?php else: ?>
      Your order has been placed successfully.
    <?php endif; ?>
  </p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:24px;">
    <a href="order-tracking.php" class="btn btn-primary"><i class="fas fa-truck"></i> Track Order</a>
    <a href="menu.php" class="btn btn-outline"><i class="fas fa-utensils"></i> Order More</a>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
