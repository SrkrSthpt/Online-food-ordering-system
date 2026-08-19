<?php
/** @var \App\Models\MenuItem[] $items */
/** @var \App\Models\Restaurant[] $restaurants */
?>
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
        <?php foreach ($items as $item) : ?>
        <tr>
          <td>#<?= (int)$item->id ?></td>
          <td><strong><?= e((string)$item->name) ?></strong></td>
          <td><span class="badge badge-secondary"><?= e((string)($item->restaurant_name ?? '')) ?></span></td>
          <td><i class="fas fa-star" style="color:var(--warning);font-size:0.8rem;"></i> <?= number_format((float)($item->rating ?? 4.5), 1) ?></td>
          <td><?= e(money((float)$item->price)) ?></td>
          <td>
            <div class="action-btns">
              <button class="btn-edit" onclick="openMenuModal(<?= e(json_encode([
                  'id' => (int)$item->id,
                  'restaurant_id' => (int)$item->restaurant_id,
                  'name' => (string)$item->name,
                  'description' => (string)$item->description,
                  'price' => (float)$item->price,
                  'rating' => (float)$item->rating,
              ])) ?>)"><i class="fas fa-edit"></i> Edit</button>
              <button class="btn-delete" onclick="deleteItem('menu', <?= (int)$item->id ?>)"><i class="fas fa-trash"></i> Delete</button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal-overlay" id="menuModal">
  <div class="modal">
    <h3 id="menuModalTitle">Add Menu Item</h3>
    <form method="POST" action="<?= e(route('admin.menu-items.store')) ?>">
      <?= csrf_field() ?>
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
          <?php foreach ($restaurants as $r) : ?>
            <option value="<?= (int)$r->id ?>"><?= e((string)$r->name) ?></option>
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
  const form = modal.querySelector('form');

  document.getElementById('menuName').value = data ? data.name : '';
  document.getElementById('menuRestaurant').value = data ? data.restaurant_id : '';
  document.getElementById('menuDescription').value = data ? data.description : '';
  document.getElementById('menuPrice').value = data ? data.price : '';
  document.getElementById('menuRating').value = data ? data.rating : '';

  if (data) {
    title.textContent = 'Edit Menu Item';
    action.value = 'edit';
    id.value = data.id;
    form.action = '<?= e(route('admin.menu-items.update')) ?>';
  } else {
    title.textContent = 'Add Menu Item';
    action.value = 'add';
    id.value = '';
    form.action = '<?= e(route('admin.menu-items.store')) ?>';
  }
  modal.classList.add('active');
}
</script>