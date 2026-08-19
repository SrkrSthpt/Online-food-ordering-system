<?php
/** @var \App\Models\User $user */
/** @var float $totalRevenue */
/** @var int $totalOrders */
/** @var int $activeOrders */
/** @var int $totalUsers */
/** @var int $totalRestaurants */
/** @var array<int,array<string,mixed>> $recentOrders */
/** @var array<int,array<string,mixed>> $dailySales */
/** @var array<int,array<string,mixed>> $monthlySales */
?>
<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(40,167,69,0.1);color:var(--success);"><i class="fas fa-money-bill-wave"></i></div>
    <div class="stat-number"><?= e(money($totalRevenue)) ?></div>
    <div class="stat-label">Total Revenue</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(255,107,53,0.1);color:var(--primary);"><i class="fas fa-shopping-bag"></i></div>
    <div class="stat-number"><?= (int)$totalOrders ?></div>
    <div class="stat-label">Total Orders</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(0,78,137,0.1);color:var(--secondary);"><i class="fas fa-spinner"></i></div>
    <div class="stat-number"><?= (int)$activeOrders ?></div>
    <div class="stat-label">Active Orders</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(255,193,7,0.1);color:var(--warning);"><i class="fas fa-users"></i></div>
    <div class="stat-number"><?= (int)$totalUsers ?></div>
    <div class="stat-label">Total Users</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:rgba(111,66,193,0.1);color:#6f42c1;"><i class="fas fa-store"></i></div>
    <div class="stat-number"><?= (int)$totalRestaurants ?></div>
    <div class="stat-label">Restaurants</div>
  </div>
</div>

<div class="charts-grid">
  <div class="chart-container">
    <h3>Weekly Sales</h3>
    <div style="display:flex;align-items:flex-end;gap:8px;height:200px;padding:20px 0;">
      <?php $maxVal = !empty($dailySales) ? max(array_column($dailySales, 'total')) : 1; ?>
      <?php foreach ($dailySales as $day) : ?>
            <?php $pct = ((float)$day['total'] / $maxVal) * 100; ?>
        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;">
          <span style="font-size:0.7rem;font-weight:600;color:var(--gray);"><?= e(money((float)$day['total'])) ?></span>
          <div style="width:100%;background:var(--border);border-radius:4px;height:160px;position:relative;overflow:hidden;">
            <div style="position:absolute;bottom:0;left:0;right:0;height:<?= (float)$pct ?>%;background:linear-gradient(to top, var(--primary), var(--accent));border-radius:4px;transition:height 1.5s ease;"></div>
          </div>
          <span style="font-size:0.65rem;color:var(--gray);"><?= e(date('D', strtotime((string)$day['date']))) ?></span>
        </div>
      <?php endforeach; ?>
      <?php if (empty($dailySales)) : ?>
        <p style="color:var(--gray);width:100%;text-align:center;">No sales data yet</p>
      <?php endif; ?>
    </div>
  </div>

  <div class="chart-container">
    <h3>Monthly Sales (6 Months)</h3>
    <div style="display:flex;align-items:flex-end;gap:8px;height:200px;padding:20px 0;">
      <?php $maxValM = !empty($monthlySales) ? max(array_column($monthlySales, 'total')) : 1; ?>
      <?php foreach ($monthlySales as $month) : ?>
            <?php $pctM = ((float)$month['total'] / $maxValM) * 100; ?>
        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;">
          <span style="font-size:0.7rem;font-weight:600;color:var(--gray);"><?= e(money((float)$month['total'])) ?></span>
          <div style="width:100%;background:var(--border);border-radius:4px;height:160px;position:relative;overflow:hidden;">
            <div style="position:absolute;bottom:0;left:0;right:0;height:<?= (float)$pctM ?>%;background:linear-gradient(to top, var(--secondary), #4db8ff);border-radius:4px;transition:height 1.5s ease;"></div>
          </div>
          <span style="font-size:0.65rem;color:var(--gray);"><?= e((string)$month['month']) ?></span>
        </div>
      <?php endforeach; ?>
      <?php if (empty($monthlySales)) : ?>
        <p style="color:var(--gray);width:100%;text-align:center;">No sales data yet</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="table-container">
  <h3>Recent Orders</h3>
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Order #</th>
          <th>Customer</th>
          <th>Total</th>
          <th>Payment</th>
          <th>Status</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentOrders as $o) : ?>
        <tr>
          <td><strong>#<?= (int)$o['id'] ?></strong></td>
          <td><?= e((string)($o['user_name'] ?? '')) ?></td>
          <td><?= e(money((float)$o['total'])) ?></td>
          <td><span class="badge badge-<?= ($o['payment_status'] ?? '') === 'completed' ? 'success' : 'warning' ?>"><?= ($o['payment_status'] ?? '') === 'completed' ? 'Paid' : 'Pending Payment' ?></span></td>
          <td><span class="badge badge-<?= ($o['status'] ?? '') === 'delivered' ? 'success' : (($o['status'] ?? '') === 'preparing' ? 'secondary' : 'primary') ?>"><?= e(ucfirst((string)($o['status'] ?? ''))) ?></span></td>
          <td><?= e(date('M d, h:i A', strtotime((string)($o['created_at'] ?? '')))) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($recentOrders)) : ?>
        <tr><td colspan="6" style="text-align:center;color:var(--gray);">No orders yet</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>