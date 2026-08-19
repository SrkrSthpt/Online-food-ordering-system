<?php
/** @var \App\Models\Restaurant[] $restaurants */
?>
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
        <?php foreach ($restaurants as $r) : ?>
        <tr>
          <td>#<?= (int)$r->id ?></td>
          <td><strong><?= e((string)$r->name) ?></strong></td>
          <td><?= e((string)$r->location) ?></td>
          <td><span class="badge badge-primary"><?= (int)$r->itemCount() ?> items</span></td>
          <td>
            <div class="action-btns">
              <button class="btn-edit" onclick="openRestaurantModal(<?= e(json_encode([
                  'id' => (int)$r->id,
                  'name' => (string)$r->name,
                  'location' => (string)$r->location,
                  'image_url' => (string)$r->image_url,
              ])) ?>)"><i class="fas fa-edit"></i> Edit</button>
              <button class="btn-delete" onclick="deleteItem('restaurant', <?= (int)$r->id ?>)"><i class="fas fa-trash"></i> Delete</button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal-overlay" id="restaurantModal">
  <div class="modal">
    <h3 id="restaurantModalTitle">Add Restaurant</h3>
    <form method="POST" action="<?= e(route('admin.restaurants.store')) ?>">
      <?= csrf_field() ?>
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
  const form = modal.querySelector('form');

  if (data) {
    title.textContent = 'Edit Restaurant';
    action.value = 'edit';
    id.value = data.id;
    name.value = data.name;
    location.value = data.location || '';
    image.value = data.image_url || '';
    form.action = '<?= e(route('admin.restaurants.update')) ?>';
  } else {
    title.textContent = 'Add Restaurant';
    action.value = 'add';
    id.value = '';
    name.value = '';
    location.value = '';
    image.value = '';
    form.action = '<?= e(route('admin.restaurants.store')) ?>';
  }
  modal.classList.add('active');
}
</script>