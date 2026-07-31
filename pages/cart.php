<?php require_once __DIR__ . '/../includes/db.php';
if (!isAuthenticated()) redirect('/pages/login.php');

$cart = $_SESSION['cart'] ?? [];
$items = [];
$total = 0;

if (!empty($cart)) {
  foreach ($cart as $itemId => $qty) {
    $result = dbQuery("SELECT * FROM menu_items WHERE id = ?", [(int)$itemId]);
    if ($result && $item = $result->fetch_assoc()) {
      $item['quantity'] = $qty;
      $item['subtotal'] = $item['price'] * $qty;
      $total += $item['subtotal'];
      $items[] = $item;
    }
  }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="cart-container">
  <h2><i class="fas fa-shopping-cart"></i> Your Cart</h2>

  <?php if (empty($items)): ?>
    <div class="cart-empty">
      <i class="fas fa-shopping-cart"></i>
      <h3>Your cart is empty</h3>
      <p>Looks like you haven't added anything yet.</p>
      <a href="menu.php" class="btn btn-primary" style="margin-top:20px">
        <i class="fas fa-utensils"></i> Browse Menu
      </a>
    </div>
  <?php else: ?>
    <div class="cart-items-container">
      <?php foreach ($items as $item): ?>
      <div class="cart-item" data-id="<?php echo $item['id']; ?>">
        <div class="cart-item-info">
          <h4><?php echo htmlspecialchars($item['name']); ?></h4>
        </div>
        <div class="cart-item-qty" data-id="<?php echo $item['id']; ?>">
          <button onclick="updateCartItem(<?php echo $item['id']; ?>, -1)">−</button>
          <span><?php echo $item['quantity']; ?></span>
          <button onclick="updateCartItem(<?php echo $item['id']; ?>, 1)">+</button>
        </div>
        <div class="cart-item-price"><?php echo CURRENCY; ?><?php echo number_format($item['subtotal'], 2); ?></div>
        <button class="cart-item-remove" onclick="removeCartItem(<?php echo $item['id']; ?>)">
          <i class="fas fa-trash-alt"></i>
        </button>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="cart-summary">
      <h3 style="margin-bottom:16px;">Order Summary</h3>
      <div class="cart-summary-row">
        <span>Subtotal</span>
        <span id="cart-subtotal"><?php echo CURRENCY; ?><?php echo number_format($total, 2); ?></span>
      </div>
      <div class="cart-summary-row">
        <span>Delivery Fee</span>
        <span><?php echo CURRENCY; ?><?php echo number_format(DELIVERY_FEE, 2); ?></span>
      </div>
      <div class="cart-summary-row total">
        <span>Total</span>
        <span id="cart-total"><?php echo CURRENCY; ?><?php echo number_format($total + DELIVERY_FEE, 2); ?></span>
      </div>
      <button class="btn btn-primary" style="width:100%;justify-content:center;margin-top:20px;" onclick="placeOrder()" id="place-order-btn">
        <i class="fas fa-check-circle"></i> Place Order
      </button>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
