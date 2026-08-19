<?php
/** @var array<int,array<string,mixed>> $items */
/** @var float $subtotal */
/** @var float $delivery */
/** @var float $total */
?>
<div class="cart-container">
  <h2><i class="fas fa-shopping-cart"></i> Your Cart</h2>

  <?php if (empty($items)) : ?>
    <div class="cart-empty">
      <i class="fas fa-shopping-cart"></i>
      <h3>Your cart is empty</h3>
      <p>Looks like you haven't added anything yet.</p>
      <a href="<?= e(route('menu.index')) ?>" class="btn btn-primary" style="margin-top:20px">
        <i class="fas fa-utensils"></i> Browse Menu
      </a>
    </div>
  <?php else : ?>
    <div class="cart-items-container">
      <?php foreach ($items as $line) : ?>
            <?php $item = $line['item']; ?>
      <div class="cart-item" data-id="<?= (int)$item->id ?>">
        <div class="cart-item-info">
          <h4><?= e((string)$item->name) ?></h4>
        </div>
        <div class="cart-item-qty" data-id="<?= (int)$item->id ?>">
          <button onclick="updateCartItem(<?= (int)$item->id ?>, -1)">−</button>
          <span><?= (int)$line['quantity'] ?></span>
          <button onclick="updateCartItem(<?= (int)$item->id ?>, 1)">+</button>
        </div>
        <div class="cart-item-price"><?= e(money((float)$line['subtotal'])) ?></div>
        <button class="cart-item-remove" onclick="removeCartItem(<?= (int)$item->id ?>)">
          <i class="fas fa-trash-alt"></i>
        </button>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="cart-summary">
      <h3 style="margin-bottom:16px;">Order Summary</h3>
      <div class="cart-summary-row">
        <span>Subtotal</span>
        <span id="cart-subtotal"><?= e(money($subtotal)) ?></span>
      </div>
      <div class="cart-summary-row">
        <span>Delivery Fee</span>
        <span><?= e(money($delivery)) ?></span>
      </div>
      <div class="cart-summary-row total">
        <span>Total</span>
        <span id="cart-total"><?= e(money($total)) ?></span>
      </div>
      <button class="btn btn-primary" style="width:100%;justify-content:center;margin-top:20px;" onclick="placeOrder()" id="place-order-btn">
        <i class="fas fa-check-circle"></i> Place Order
      </button>
    </div>
  <?php endif; ?>
</div>