<?php require_once __DIR__ . '/../includes/db.php';
if (!isManager()) redirect('/pages/login.php');

$totalRevenue = 0;
$revResult = dbQuery("SELECT COALESCE(SUM(total), 0) as rev FROM orders WHERE status = 'delivered'");
if ($revResult) $totalRevenue = $revResult->fetch_assoc()['rev'];

$totalOrders = 0;
$ordResult = dbQuery("SELECT COUNT(*) as cnt FROM orders");
if ($ordResult) $totalOrders = $ordResult->fetch_assoc()['cnt'];

$activeOrders = 0;
$actResult = dbQuery("SELECT COUNT(*) as cnt FROM orders WHERE status != 'delivered'");
if ($actResult) $activeOrders = $actResult->fetch_assoc()['cnt'];

$totalUsers = 0;
$usrResult = dbQuery("SELECT COUNT(*) as cnt FROM users");
if ($usrResult) $totalUsers = $usrResult->fetch_assoc()['cnt'];

$totalRestaurants = 0;
$resResult = dbQuery("SELECT COUNT(*) as cnt FROM restaurants");
if ($resResult) $totalRestaurants = $resResult->fetch_assoc()['cnt'];

$recentOrders = dbQuery("SELECT o.*, u.name as user_name, p.status as payment_status FROM orders o JOIN users u ON o.user_id = u.id LEFT JOIN payments p ON p.order_id = o.id ORDER BY o.created_at DESC LIMIT 5");
$recentOrdersList = $recentOrders ? $recentOrders->fetch_all(MYSQLI_ASSOC) : [];

$dailySales = [];
$salesResult = dbQuery("SELECT DATE(created_at) as date, SUM(total) as total FROM orders WHERE status = 'delivered' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY date");
if ($salesResult) $dailySales = $salesResult->fetch_all(MYSQLI_ASSOC);

$monthlySales = [];
$monthResult = dbQuery("SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total) as total FROM orders WHERE status = 'delivered' AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY month ORDER BY month");
if ($monthResult) $monthlySales = $monthResult->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../includes/header.php';
?>

<div class="admin-container">
  <div class="admin-sidebar">
    <h3><i class="fas fa-utensils"></i> Bitezy Admin</h3>
    <a href="index.php" class="active"><i class="fas fa-chart-pie"></i> Dashboard</a>
    <a href="restaurants.php"><i class="fas fa-store"></i> Restaurants</a>
    <a href="menu-items.php"><i class="fas fa-utensils"></i> Menu Items</a>
    <a href="orders.php"><i class="fas fa-truck"></i> Orders</a>
    <a href="<?php echo SITE_URL; ?>/index.php"><i class="fas fa-arrow-left"></i> Back to Site</a>
  </div>

  <div class="admin-content">
    <div class="admin-header">
      <h2>Dashboard</h2>
      <span style="color:var(--gray);">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
    </div>

    <div class="stat-grid">
      <div class="stat-card">
        <div class="stat-icon" style="background:rgba(40,167,69,0.1);color:var(--success);"><i class="fas fa-money-bill-wave"></i></div>
        <div class="stat-number"><?php echo CURRENCY; ?><?php echo number_format($totalRevenue, 2); ?></div>
        <div class="stat-label">Total Revenue</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:rgba(255,107,53,0.1);color:var(--primary);"><i class="fas fa-shopping-bag"></i></div>
        <div class="stat-number"><?php echo $totalOrders; ?></div>
        <div class="stat-label">Total Orders</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:rgba(0,78,137,0.1);color:var(--secondary);"><i class="fas fa-spinner"></i></div>
        <div class="stat-number"><?php echo $activeOrders; ?></div>
        <div class="stat-label">Active Orders</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:rgba(255,193,7,0.1);color:var(--warning);"><i class="fas fa-users"></i></div>
        <div class="stat-number"><?php echo $totalUsers; ?></div>
        <div class="stat-label">Total Users</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background:rgba(111,66,193,0.1);color:#6f42c1;"><i class="fas fa-store"></i></div>
        <div class="stat-number"><?php echo $totalRestaurants; ?></div>
        <div class="stat-label">Restaurants</div>
      </div>
    </div>

    <div class="charts-grid">
      <div class="chart-container">
        <h3>Weekly Sales</h3>
        <div style="display:flex;align-items:flex-end;gap:8px;height:200px;padding:20px 0;">
          <?php
          $maxVal = max(array_column($dailySales, 'total')) ?: 1;
          foreach ($dailySales as $day):
            $pct = ($day['total'] / $maxVal) * 100;
          ?>
          <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;">
            <span style="font-size:0.7rem;font-weight:600;color:var(--gray);"><?php echo CURRENCY; ?><?php echo round($day['total']); ?></span>
            <div style="width:100%;background:var(--border);border-radius:4px;height:160px;position:relative;overflow:hidden;">
              <div style="position:absolute;bottom:0;left:0;right:0;height:<?php echo $pct; ?>%;background:linear-gradient(to top, var(--primary), var(--accent));border-radius:4px;transition:height 1.5s ease;"></div>
            </div>
            <span style="font-size:0.65rem;color:var(--gray);"><?php echo date('D', strtotime($day['date'])); ?></span>
          </div>
          <?php endforeach; ?>
          <?php if (empty($dailySales)): ?>
            <p style="color:var(--gray);width:100%;text-align:center;">No sales data yet</p>
          <?php endif; ?>
        </div>
      </div>

      <div class="chart-container">
        <h3>Monthly Sales (6 Months)</h3>
        <div style="display:flex;align-items:flex-end;gap:8px;height:200px;padding:20px 0;">
          <?php
          $maxVal = max(array_column($monthlySales, 'total')) ?: 1;
          foreach ($monthlySales as $month):
            $pct = ($month['total'] / $maxVal) * 100;
          ?>
          <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;">
            <span style="font-size:0.7rem;font-weight:600;color:var(--gray);"><?php echo CURRENCY; ?><?php echo round($month['total']); ?></span>
            <div style="width:100%;background:var(--border);border-radius:4px;height:160px;position:relative;overflow:hidden;">
              <div style="position:absolute;bottom:0;left:0;right:0;height:<?php echo $pct; ?>%;background:linear-gradient(to top, var(--secondary), #4db8ff);border-radius:4px;transition:height 1.5s ease;"></div>
            </div>
            <span style="font-size:0.65rem;color:var(--gray);"><?php echo $month['month']; ?></span>
          </div>
          <?php endforeach; ?>
          <?php if (empty($monthlySales)): ?>
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
            <?php foreach ($recentOrdersList as $o): ?>
            <tr>
              <td><strong>#<?php echo $o['id']; ?></strong></td>
              <td><?php echo htmlspecialchars($o['user_name']); ?></td>
              <td><?php echo CURRENCY; ?><?php echo number_format($o['total'], 2); ?></td>
              <td><span class="badge badge-<?php echo $o['payment_status'] === 'completed' ? 'success' : 'warning'; ?>"><?php echo $o['payment_status'] === 'completed' ? 'Paid' : 'Pending Payment'; ?></span></td>
              <td><span class="badge badge-<?php echo $o['status'] === 'delivered' ? 'success' : ($o['status'] === 'preparing' ? 'secondary' : 'primary'); ?>"><?php echo ucfirst($o['status']); ?></span></td>
              <td><?php echo date('M d, h:i A', strtotime($o['created_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($recentOrdersList)): ?>
            <tr><td colspan="6" style="text-align:center;color:var(--gray);">No orders yet</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
