<?php
/** @var array<string,mixed>|null $payment */
?>
<div style="max-width:600px;margin:60px auto;padding:40px;text-align:center;background:white;border-radius:var(--radius-lg);box-shadow:var(--shadow-lg);">
  <i class="fas fa-check-circle" style="font-size:5rem;color:var(--success);margin-bottom:20px;"></i>
  <h2>Payment Successful!</h2>
  <p style="color:var(--gray);margin:16px 0;">
    <?php if ($payment) : ?>
      Your payment of <strong><?= e(money((float)$payment['total'] ?? 0)) ?></strong>
      via <strong><?= e(ucfirst((string)($payment['method'] ?? ''))) ?></strong> has been completed.
      Transaction ID: <strong><?= e((string)($payment['transaction_id'] ?? '')) ?></strong>
    <?php else : ?>
      Your order has been placed successfully.
    <?php endif; ?>
  </p>
  <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:24px;">
    <a href="<?= e(route('orders.index')) ?>" class="btn btn-primary"><i class="fas fa-truck"></i> Track Order</a>
    <a href="<?= e(route('menu.index')) ?>" class="btn btn-outline"><i class="fas fa-utensils"></i> Order More</a>
  </div>
</div>