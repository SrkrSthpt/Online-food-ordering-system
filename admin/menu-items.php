<?php require_once __DIR__ . '/../includes/db.php';
if (!isManager()) redirect('/pages/login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  $name = sanitize($_POST['name']);
  $restaurant_id = (int)$_POST['restaurant_id'];
  $description = sanitize($_POST['description']);
  $price = (float)$_POST['price'];
  $rating = isset($_POST['rating']) ? min(5, max(0, (float)$_POST['rating'])) : 4.5;

  if (!empty($name) && $restaurant_id > 0 && $price > 0) {
    if ($_POST['action'] === 'add') {
      dbInsert("INSERT INTO menu_items (restaurant_id, name, description, price, rating) VALUES (?, ?, ?, ?, ?)",
        [$restaurant_id, $name, $description, $price, $rating]);
    } else {
      $id = (int)$_POST['id'];
      dbQuery("UPDATE menu_items SET restaurant_id=?, name=?, description=?, price=?, rating=? WHERE id=?",
        [$restaurant_id, $name, $description, $price, $rating, $id]);
    }
  }
  redirect('/admin/menu-items.php');
}

$items = dbQuery("SELECT mi.*, r.name as restaurant_name FROM menu_items mi JOIN restaurants r ON mi.restaurant_id = r.id ORDER BY r.name, mi.name");
$itemsList = $items ? $items->fetch_all(MYSQLI_ASSOC) : [];

$restaurants = dbQuery("SELECT * FROM restaurants ORDER BY name");
$restaurantsList = $restaurants ? $restaurants->fetch_all(MYSQLI_ASSOC) : [];

include __DIR__ . '/../includes/header.php';
?>

<div class="admin-container">
  <div class="admin-sidebar">
    <h3><i class="fas fa-utensils"></i> Bitezy Admin</h3>
    <a href="index.php"><i class="fas fa-chart-pie"></i> Dashboard</a>
    <a href="restaurants.php"><i class="fas fa-store"></i> Restaurants</a>
    <a href="menu-items.php" class="active"><i class="fas fa-utensils"></i> Menu Items</a>
    <a href="orders.php"><i class="fas fa-truck"></i> Orders</a>
    <a href="<?php echo SITE_URL; ?>/index.php"><i class="fas fa-arrow-left"></i> Back to Site</a>
  </div>

  <div class="admin-content">
    <div class="admin-header">
      <h2>Manage Menu Items</h2>
      <button class="btn btn-primary" onclick="openMenuModal()"><i class="fas fa-plus"></i> Add Item</button>
    </div>

    <div class="table-container">
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Restaurant</th>
              <th>Rating</th>
              <th>Price</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($itemsList as $item): ?>
            <tr>
              <td>#<?php echo $item['id']; ?></td>
              <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
              <td><span class="badge badge-secondary"><?php echo htmlspecialchars($item['restaurant_name']); ?></span></td>
              <td><i class="fas fa-star" style="color:var(--warning);font-size:0.8rem;"></i> <?php echo number_format((float)($item['rating'] ?? 4.5), 1); ?></td>
              <td><?php echo CURRENCY; ?><?php echo number_format($item['price'], 2); ?></td>
              <td>
                <div class="action-btns">
                  <button class="btn-edit" onclick="openMenuModal(<?php echo htmlspecialchars(json_encode($item)); ?>)"><i class="fas fa-edit"></i> Edit</button>
                  <button class="btn-delete" onclick="deleteItem('menu', <?php echo $item['id']; ?>)"><i class="fas fa-trash"></i> Delete</button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="modal-overlay" id="menuModal">
  <div class="modal">
    <h3 id="menuModalTitle">Add Menu Item</h3>
    <form method="POST">
      <input type="hidden" name="action" id="menuAction" value="add">
      <input type="hidden" name="id" id="menuId" value="">
      <div class="form-group">
        <label>Item Name</label>
        <input type="text" name="name" id="menuName" required placeholder="e.g. Margherita Pizza">
      </div>
      <div class="form-group">
        <label>Restaurant</label>
        <select name="restaurant_id" id="menuRestaurant" required>
          <option value="">Select Restaurant</option>
          <?php foreach ($restaurantsList as $r): ?>
            <option value="<?php echo $r['id']; ?>"><?php echo htmlspecialchars($r['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Description</label>
        <input type="text" name="description" id="menuDescription" placeholder="Brief description">
      </div>
      <div class="form-group">
        <label>Price (rs.)</label>
        <input type="number" name="price" id="menuPrice" step="0.01" min="0" required placeholder="0.00">
      </div>
      <div class="form-group">
        <label>Rating (1-5)</label>
        <input type="number" name="rating" id="menuRating" step="0.1" min="0" max="5" placeholder="4.5">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;"><i class="fas fa-save"></i> Save</button>
    </form>
  </div>
</div>

<script>
function openMenuModal(data) {
  const modal = document.getElementById('menuModal');
  const title = document.getElementById('menuModalTitle');
  const action = document.getElementById('menuAction');
  const id = document.getElementById('menuId');
  document.getElementById('menuName').value = data ? data.name : '';
  document.getElementById('menuRestaurant').value = data ? data.restaurant_id : '';
  document.getElementById('menuDescription').value = data ? data.description : '';
  document.getElementById('menuPrice').value = data ? data.price : '';
  document.getElementById('menuRating').value = data ? data.rating : '';

  if (data) {
    title.textContent = 'Edit Menu Item';
    action.value = 'edit';
    id.value = data.id;
  } else {
    title.textContent = 'Add Menu Item';
    action.value = 'add';
    id.value = '';
  }
  modal.classList.add('active');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
