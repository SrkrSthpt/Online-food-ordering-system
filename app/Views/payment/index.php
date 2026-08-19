<?php

/** @var \App\Models\Order $order */
use App\Services\PaymentService;

$methods = (new PaymentService())->methods();
?>
<div class="payment-container">
  <div class="payment-card">
    <i class="fas fa-money-bill-wave" style="font-size:3rem;color:var(--success);margin-bottom:16px;"></i>
    <h2>Confirm Your Order</h2>
    <p style="color:var(--gray);">Order #<?= (int)$order->id ?></p>
    <div class="amount"><?= e(money((float)$order->total)) ?></div>

    <h4 style="margin-bottom:16px;">Payment Method</h4>
    <div class="payment-methods">
      <?php foreach ($methods as $m) : ?>
            <?php if ($m['available']) : ?>
          <div class="payment-method active" data-method="<?= e($m['code']) ?>">
            <i class="fas fa-money-bill-wave"></i>
            <span><?= e($m['label']) ?></span>
            <small style="display:block;color:var(--success);margin-top:6px;font-size:0.75rem;font-weight:600;">
              <i class="fas fa-check-circle"></i> Available
            </small>
          </div>
            <?php else : ?>
          <div class="payment-method unavailable" title="Not available yet">
            <i class="fas <?= $m['code'] === 'esewa' ? 'fa-wallet' : 'fab fa-paypal' ?>"></i>
            <span><?= e($m['label']) ?></span>
            <small style="display:block;margin-top:6px;font-size:0.75rem;font-weight:600;color:var(--gray);">
              <i class="fas fa-ban"></i> Not Available
            </small>
          </div>
            <?php endif; ?>
      <?php endforeach; ?>
    </div>

    <p style="font-size:0.85rem;color:var(--gray);margin-bottom:20px;">
      <i class="fas fa-truck"></i> Pay in cash when your order arrives.
    </p>

    <button class="btn btn-success" style="width:100%;justify-content:center;font-size:1.1rem;padding:16px;" onclick="confirmPayment(<?= (int)$order->id ?>, 'cod')" id="pay-btn">
      <i class="fas fa-check-circle"></i> Confirm Order — Pay on Delivery
    </button>
  </div>
</div>