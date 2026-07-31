<?php require_once __DIR__ . '/../includes/db.php';
if (!isManager()) redirect('/pages/login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
  $orderId = (int)$_POST['order_id'];
  $status = sanitize($_POST['status']);
  dbQuery("UPDATE orders SET status = ? WHERE id = ?", [$status, $orderId]);
  redirect('/admin/orders.php');
}

$orders = dbQuery("SELECT o.*, u.name as user_name, p.status as payment_status FROM orders o JOIN users u ON o.user_id = u.id LEFT JOIN payments p ON p.order_id = o.id ORDER BY o.created_at DESC");
$ordersList = $orders ? $orders->fetch_all(MYSQLI_ASSOC) : [];

include __DIR__ . '/../includes/header.php';
?>

<div class="admin-container">
  <div class="admin-sidebar">
    <h3><i class="fas fa-utensils"></i> Bitezy Admin</h3>
    <a href="index.php"><i class="fas fa-chart-pie"></i> Dashboard</a>
    <a href="restaurants.php"><i class="fas fa-store"></i> Restaurants</a>
    <a href="menu-items.php"><i class="fas fa-utensils"></i> Menu Items</a>
    <a href="orders.php" class="active"><i class="fas fa-truck"></i> Orders</a>
    <a href="/bitezy/index.php"><i class="fas fa-arrow-left"></i> Back to Site</a>
  </div>

  <div class="admin-content">
    <div class="admin-header">
      <h2>Manage Orders</h2>
    </div>

    <div class="table-container">
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
              <th>Update</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($ordersList as $o): ?>
            <tr>
              <td><strong>#<?php echo $o['id']; ?></strong></td>
              <td><?php echo htmlspecialchars($o['user_name']); ?></td>
              <td><?php echo CURRENCY; ?><?php echo number_format($o['total'], 2); ?></td>
              <td><span class="badge badge-<?php echo $o['payment_status'] === 'completed' ? 'success' : 'warning'; ?>"><?php echo $o['payment_status'] === 'completed' ? 'Paid' : 'Pending Payment'; ?></span></td>
              <td><span class="badge badge-<?php echo $o['status'] === 'delivered' ? 'success' : ($o['status'] === 'preparing' ? 'secondary' : 'primary'); ?>"><?php echo ucfirst($o['status']); ?></span></td>
              <td><?php echo date('M d, h:i A', strtotime($o['created_at'])); ?></td>
              <td>
                <form method="POST" style="display:flex;gap:6px;">
                  <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                  <select name="status" style="padding:6px;border-radius:6px;border:1px solid var(--border);font-family:inherit;font-size:0.8rem;">
                    <option value="pending" <?php echo $o['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="preparing" <?php echo $o['status'] === 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                    <option value="delivered" <?php echo $o['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                  </select>
                  <button type="submit" name="update_status" class="btn-edit"><i class="fas fa-check"></i></button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
