<?php
/** @var array<int,array<string,mixed>> $assigned */
/** @var array<int,array<string,mixed>> $available */
$statusLabels = [
    'prepared' => 'Ready for Delivery',
    'out_for_delivery' => 'On the Way',
];
?>
<div class="delivery-dashboard">
  <h2 style="margin-bottom:8px;"><i class="fas fa-motorcycle"></i> Delivery Dashboard</h2>
  <p style="color:var(--gray);margin-bottom:24px;">Pick up prepared orders and keep customers updated with the ETA.</p>

  <h3 style="margin:20px 0 12px;"><i class="fas fa-box"></i> My Deliveries</h3>
  <?php if (empty($assigned)) : ?>
    <div class="empty-box">No active deliveries. Grab one from the queue below.</div>
  <?php else : ?>
      <?php foreach ($assigned as $o) :
            $status = (string)($o['status'] ?? 'prepared');
            $eta = (string)($o['eta'] ?? '');
            ?>
    <div class="delivery-card" data-order-id="<?= (int)$o['id'] ?>" data-status="<?= e($status) ?>">
      <div class="delivery-card-head">
        <strong>Order #<?= (int)$o['id'] ?></strong>
        <span class="badge badge-<?= $status === 'out_for_delivery' ? 'secondary' : 'primary' ?>"><?= e($statusLabels[$status] ?? ucfirst($status)) ?></span>
      </div>
      <div class="delivery-card-meta">
        <span><i class="fas fa-store"></i> <?= e((string)($o['restaurant_name'] ?? '')) ?></span>
        <span><i class="fas fa-user"></i> <?= e((string)($o['user_name'] ?? '')) ?></span>
            <?php if ($eta !== '') : ?>
          <span class="eta-label" data-eta="<?= e($eta) ?>"><i class="fas fa-clock"></i> ETA <strong><?= e(date('h:i A', strtotime($eta))) ?></strong></span>
            <?php endif; ?>
      </div>

      <div class="delivery-eta-row">
        <input type="number" min="1" max="180" placeholder="ETA minutes" class="eta-input" id="eta-<?= (int)$o['id'] ?>">
        <button class="btn btn-secondary" onclick="updateEta(<?= (int)$o['id'] ?>)"><i class="fas fa-clock"></i> Update ETA</button>
      </div>

      <div class="delivery-actions">
            <?php if ($status === 'prepared') : ?>
          <button class="btn btn-primary" onclick="deliveryStatus(<?= (int)$o['id'] ?>, 'out_for_delivery')">
            <i class="fas fa-motorcycle"></i> Start Delivery
          </button>
            <?php endif; ?>
            <?php if ($status === 'out_for_delivery') : ?>
          <button class="btn btn-success" onclick="deliveryStatus(<?= (int)$o['id'] ?>, 'delivered')">
            <i class="fas fa-check-double"></i> Mark Delivered
          </button>
            <?php endif; ?>
      </div>
    </div>
      <?php endforeach; ?>
  <?php endif; ?>

  <h3 style="margin:28px 0 12px;"><i class="fas fa-inbox"></i> Available for Pickup</h3>
  <?php if (empty($available)) : ?>
    <div class="empty-box">No orders waiting for a courier right now.</div>
  <?php else : ?>
      <?php foreach ($available as $o) : ?>
    <div class="delivery-card available" data-order-id="<?= (int)$o['id'] ?>">
      <div class="delivery-card-head">
        <strong>Order #<?= (int)$o['id'] ?></strong>
        <span class="badge badge-primary">Ready</span>
      </div>
      <div class="delivery-card-meta">
        <span><i class="fas fa-store"></i> <?= e((string)($o['restaurant_name'] ?? '')) ?></span>
        <span><i class="fas fa-user"></i> <?= e((string)($o['user_name'] ?? '')) ?></span>
      </div>
      <div class="delivery-actions">
        <button class="btn btn-primary" onclick="acceptDelivery(<?= (int)$o['id'] ?>)">
          <i class="fas fa-handshake"></i> Accept Delivery
        </button>
      </div>
    </div>
      <?php endforeach; ?>
  <?php endif; ?>
</div>