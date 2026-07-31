<?php require_once __DIR__ . '/../includes/db.php';
if (!isAuthenticated()) redirect('/pages/login.php');

$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
if (!$orderId) redirect('/pages/cart.php');

$order = dbQuery("SELECT * FROM orders WHERE id = ? AND user_id = ?", [$orderId, $_SESSION['user_id']]);
$order = $order ? $order->fetch_assoc() : null;
if (!$order) redirect('/pages/cart.php');

$payment = dbQuery("SELECT * FROM payments WHERE order_id = ?", [$orderId]);
$payment = $payment ? $payment->fetch_assoc() : null;
if ($payment && $payment['status'] === 'completed') redirect('/pages/order-tracking.php');
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="payment-container">
  <div class="payment-card">
    <i class="fas fa-credit-card" style="font-size:3rem;color:var(--primary);margin-bottom:16px;"></i>
    <h2>Complete Payment</h2>
    <p style="color:var(--gray);">Order #<?php echo $orderId; ?></p>
    <div class="amount"><?php echo CURRENCY; ?><?php echo number_format($order['total'], 2); ?></div>

    <h4 style="margin-bottom:16px;">Select Payment Method</h4>
    <div class="payment-methods">
      <div class="payment-method active" data-method="paypal" onclick="this.parentElement.querySelectorAll('.payment-method').forEach(p=>p.classList.remove('active'));this.classList.add('active');">
        <i class="fab fa-paypal"></i>
        <span>PayPal</span>
      </div>
      <div class="payment-method" data-method="stripe" onclick="this.parentElement.querySelectorAll('.payment-method').forEach(p=>p.classList.remove('active'));this.classList.add('active');">
        <i class="fab fa-cc-stripe"></i>
        <span>Stripe</span>
      </div>
      <div class="payment-method" data-method="cod" onclick="this.parentElement.querySelectorAll('.payment-method').forEach(p=>p.classList.remove('active'));this.classList.add('active');">
        <i class="fas fa-money-bill-wave"></i>
        <span>Cash on Delivery</span>
      </div>
    </div>

    <p style="font-size:0.85rem;color:var(--gray);margin-bottom:20px;">
      <i class="fas fa-lock"></i> Your payment is secure and encrypted
    </p>

    <button class="btn btn-success" style="width:100%;justify-content:center;font-size:1.1rem;padding:16px;" onclick="confirmPayment(<?php echo $orderId; ?>)" id="pay-btn">
      <i class="fas fa-lock"></i> Pay <?php echo CURRENCY; ?><?php echo number_format($order['total'], 2); ?>
    </button>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
