<?php
/** @var \App\Models\Order[] $orders */
$steps = [
    ['key' => 'pending', 'icon' => 'fa-clipboard-list', 'label' => 'Order Placed'],
    ['key' => 'accepted', 'icon' => 'fa-store', 'label' => 'Accepted'],
    ['key' => 'preparing', 'icon' => 'fa-fire', 'label' => 'Preparing'],
    ['key' => 'prepared', 'icon' => 'fa-utensils', 'label' => 'Ready for Delivery'],
    ['key' => 'out_for_delivery', 'icon' => 'fa-motorcycle', 'label' => 'On the Way'],
    ['key' => 'delivered', 'icon' => 'fa-check-double', 'label' => 'Delivered'],
];
$labels = array_column($steps, 'label', 'key');
$icons = array_column($steps, 'icon', 'key');
?>
<div class="tracking-container">
  <h2 style="margin-bottom:24px;"><i class="fas fa-truck"></i> My Orders</h2>

  <?php if (empty($orders)) : ?>
    <div style="text-align:center;padding:80px 20px;color:var(--gray);">
      <i class="fas fa-box-open" style="font-size:4rem;margin-bottom:20px;color:var(--border);"></i>
      <h3>No orders yet</h3>
      <p>Place your first order to see it here.</p>
      <a href="<?= e(route('menu.index')) ?>" class="btn btn-primary" style="margin-top:20px">
        <i class="fas fa-utensils"></i> Order Now
      </a>
    </div>
  <?php else : ?>
      <?php foreach ($orders as $order) :
            $status = (string)($order->status ?? 'pending');
            $stepIndex = array_search($status, array_column($steps, 'key'), true);
            $stepIndex = $stepIndex === false ? 0 : $stepIndex;
            $eta = (string)($order->eta ?? '');
            $timeline = is_array($order->timeline) ? $order->timeline : [];
            ?>
    <div class="order-card" data-order-id="<?= (int)$order->id ?>" data-status="<?= e($status) ?>">
      <div class="order-header">
        <span class="order-id"><i class="fas fa-receipt"></i> Order #<?= (int)$order->id ?></span>
        <span class="order-status status-<?= e($status) ?> status-label">
            <?= e($labels[$status] ?? ucfirst($status)) ?>
        </span>
      </div>

      <div class="order-meta">
            <?php if (!empty($order->restaurant_name)) : ?>
          <span><i class="fas fa-store"></i> <?= e((string)$order->restaurant_name) ?></span>
            <?php endif; ?>
        <span><i class="fas fa-money-bill-wave"></i> Total: <?= e(money((float)$order->total)) ?></span>
            <?php if (!empty($order->delivery_name)) : ?>
          <span class="delivery-name"><i class="fas fa-motorcycle"></i> <?= e((string)$order->delivery_name) ?></span>
            <?php endif; ?>
            <?php if ($eta !== '' && $status !== 'delivered') : ?>
          <span class="eta-label" data-eta="<?= e($eta) ?>">
            <i class="fas fa-clock"></i> Est. arrival: <strong><?= e(date('h:i A', strtotime($eta))) ?></strong>
          </span>
            <?php endif; ?>
      </div>

      <div class="progress-container">
        <div class="progress-bar-fill" style="width:0%"></div>
            <?php foreach ($steps as $idx => $step) : ?>
          <div class="progress-step <?= $idx < $stepIndex ? 'completed' : ($idx === $stepIndex ? 'active' : '') ?>">
            <div class="step-icon"><i class="fas <?= e($step['icon']) ?>"></i></div>
            <span class="step-label"><?= e($step['label']) ?></span>
          </div>
            <?php endforeach; ?>
      </div>

            <?php if (!empty($timeline)) : ?>
      <div class="order-timeline">
        <h4 style="margin-bottom:10px;"><i class="fas fa-history"></i> Timeline</h4>
                <?php foreach ($timeline as $event) : ?>
          <div class="timeline-row">
            <i class="fas <?= e($icons[$event['status']] ?? 'fa-circle') ?>"></i>
            <span class="timeline-status"><?= e($labels[$event['status']] ?? ucfirst((string)$event['status'])) ?></span>
            <span class="timeline-time"><?= e(date('M d, h:i A', strtotime((string)$event['created_at']))) ?></span>
          </div>
                <?php endforeach; ?>
      </div>
            <?php endif; ?>

      <div style="font-size:0.85rem;color:var(--gray);display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;margin-top:16px;padding-top:16px;border-top:1px solid var(--border);">
        <span><i class="fas fa-calendar"></i> <?= e(date('M d, Y h:i A', strtotime((string)$order->created_at))) ?></span>
            <?php if ((string)$order->payment_method === 'cod') : ?>
          <span><i class="fas fa-money-bill-wave"></i> Cash on Delivery</span>
            <?php endif; ?>
      </div>
    </div>
      <?php endforeach; ?>
  <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.order-card[data-order-id]').forEach(function(card) {
    setInterval(function() { pollOrder(card); }, 15000);
  });
});
</script>