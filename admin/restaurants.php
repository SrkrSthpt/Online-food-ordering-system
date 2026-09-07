<?php require_once __DIR__ . '/../includes/db.php';
if (!isManager()) redirect('/pages/login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
  if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
    $name = sanitize($_POST['name']);
    $location = sanitize($_POST['location']);
    $img = sanitize($_POST['image_url'] ?? '');
    if (!empty($name)) {
      if ($_POST['action'] === 'add') {
        dbInsert("INSERT INTO restaurants (name, location, image_url) VALUES (?, ?, ?)", [$name, $location, $img]);
      } else {
        $id = (int)$_POST['id'];
        dbQuery("UPDATE restaurants SET name=?, location=?, image_url=? WHERE id=?", [$name, $location, $img, $id]);
      }
    }
  }
  redirect('/admin/restaurants.php');
}

$restaurants = dbQuery("SELECT * FROM restaurants ORDER BY name");
$restaurantsList = $restaurants ? $restaurants->fetch_all(MYSQLI_ASSOC) : [];
include __DIR__ . '/../includes/header.php';
?>

<div class="admin-container">
  <div class="admin-sidebar">
    <h3><i class="fas fa-utensils"></i> Bitezy Admin</h3>
    <a href="index.php"><i class="fas fa-chart-pie"></i> Dashboard</a>
    <a href="restaurants.php" class="active"><i class="fas fa-store"></i> Restaurants</a>
    <a href="menu-items.php"><i class="fas fa-utensils"></i> Menu Items</a>
    <a href="orders.php"><i class="fas fa-truck"></i> Orders</a>
    <a href="<?php echo SITE_URL; ?>/index.php"><i class="fas fa-arrow-left"></i> Back to Site</a>
  </div>

  <div class="admin-content">
    <div class="admin-header">
      <h2>Manage Restaurants</h2>
      <button class="btn btn-primary" data-modal="restaurantModal" onclick="openRestaurantModal()"><i class="fas fa-plus"></i> Add Restaurant</button>
    </div>

    <div class="table-container">
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Location</th>
              <th>Items</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($restaurantsList as $r):
              $cnt = dbQuery("SELECT COUNT(*) as c FROM menu_items WHERE restaurant_id=?", [$r['id']]);
              $itemCount = $cnt ? $cnt->fetch_assoc()['c'] : 0;
            ?>
            <tr>
              <td>#<?php echo $r['id']; ?></td>
              <td><strong><?php echo htmlspecialchars($r['name']); ?></strong></td>
              <td><?php echo htmlspecialchars($r['location']); ?></td>
              <td><span class="badge badge-primary"><?php echo $itemCount; ?> items</span></td>
              <td>
                <div class="action-btns">
                  <button class="btn-edit" onclick="openRestaurantModal(<?php echo htmlspecialchars(json_encode($r)); ?>)"><i class="fas fa-edit"></i> Edit</button>
                  <button class="btn-delete" onclick="deleteItem('restaurant', <?php echo $r['id']; ?>)"><i class="fas fa-trash"></i> Delete</button>
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

<div class="modal-overlay" id="restaurantModal">
  <div class="modal">
    <h3 id="restaurantModalTitle">Add Restaurant</h3>
    <form method="POST">
      <input type="hidden" name="action" id="restaurantAction" value="add">
      <input type="hidden" name="id" id="restaurantId" value="">
      <div class="form-group">
        <label>Restaurant Name</label>
        <input type="text" name="name" id="restaurantName" required placeholder="e.g. Pizza Paradise">
      </div>
      <div class="form-group">
        <label>Location</label>
        <input type="text" name="location" id="restaurantLocation" placeholder="e.g. 123 Main Street">
      </div>
      <div class="form-group">
        <label>Image URL (optional)</label>
        <input type="text" name="image_url" id="restaurantImage" placeholder="https://...">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;"><i class="fas fa-save"></i> Save</button>
    </form>
  </div>
</div>

<script>
function openRestaurantModal(data) {
  const modal = document.getElementById('restaurantModal');
  const title = document.getElementById('restaurantModalTitle');
  const action = document.getElementById('restaurantAction');
  const id = document.getElementById('restaurantId');
  const name = document.getElementById('restaurantName');
  const location = document.getElementById('restaurantLocation');
  const image = document.getElementById('restaurantImage');

  if (data) {
    title.textContent = 'Edit Restaurant';
    action.value = 'edit';
    id.value = data.id;
    name.value = data.name;
    location.value = data.location || '';
    image.value = data.image_url || '';
  } else {
    title.textContent = 'Add Restaurant';
    action.value = 'add';
    id.value = '';
    name.value = '';
    location.value = '';
    image.value = '';
  }
  modal.classList.add('active');
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
