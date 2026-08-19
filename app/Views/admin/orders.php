<?php
/** @var array<int,array<string,mixed>> $orders */
$statusLabels = [
    'pending' => 'Pending',
    'accepted' => 'Accepted',
    'preparing' => 'Preparing',
    'prepared' => 'Ready for Delivery',
    'out_for_delivery' => 'On the Way',
    'delivered' => 'Delivered',
];
?>
<div class="table-container">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Order #</th>
          <th>Customer</th>
          <th>Restaurant</th>
          <th>Total</th>
          <th>Payment</th>
          <th>Status</th>
          <th>Courier</th>
          <th>Date</th>
          <th>Update</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $o) :
              $status = (string)($o['status'] ?? 'pending');
              $next = (string)($o['next_status'] ?? '');
            ?>
        <tr>
          <td><strong>#<?= (int)$o['id'] ?></strong></td>
          <td><?= e((string)($o['user_name'] ?? '')) ?></td>
          <td><?= e((string)($o['restaurant_name'] ?? '—')) ?></td>
          <td><?= e(money((float)$o['total'])) ?></td>
          <td><span class="badge badge-<?= ($o['payment_status'] ?? '') === 'completed' ? 'success' : 'warning' ?>"><?= ($o['payment_status'] ?? '') === 'completed' ? 'Paid' : 'Pending Payment' ?></span></td>
          <td><span class="badge badge-<?= $status === 'delivered' ? 'success' : ($status === 'preparing' || $status === 'accepted' ? 'secondary' : 'primary') ?>"><?= e($statusLabels[$status] ?? ucfirst($status)) ?></span></td>
          <td><?= e((string)($o['delivery_name'] ?? '—')) ?></td>
          <td><?= e(date('M d, h:i A', strtotime((string)($o['created_at'] ?? '')))) ?></td>
          <td>
            <?php if ($next !== '') : ?>
              <form method="POST" action="<?= e(route('admin.orders.update')) ?>" style="display:flex;gap:6px;">
                <?= csrf_field() ?>
                <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
                <input type="hidden" name="status" value="<?= e($next) ?>">
                <button type="submit" class="btn-edit">
                  <i class="fas fa-arrow-right"></i>
                  <?= e($statusLabels[$next] ?? ucfirst($next)) ?>
                </button>
              </form>
            <?php else : ?>
              <span style="color:var(--gray);font-size:0.8rem;">Complete</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>