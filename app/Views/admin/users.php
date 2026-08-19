<?php
/** @var \App\Models\User[] $users */
/** @var \App\Models\Restaurant[] $restaurants */
$roles = ['customer', 'manager', 'delivery', 'admin'];
?>
<div class="admin-header">
  <h2>Manage Users</h2>
  <button class="btn btn-primary" data-modal="userModal" onclick="document.getElementById('userModal').classList.add('active')"><i class="fas fa-plus"></i> Add User</button>
</div>

<div class="table-container">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Restaurant</th>
          <th>Active</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u) : ?>
        <tr>
          <td>#<?= (int)$u->id ?></td>
          <td><strong><?= e((string)$u->name) ?></strong></td>
          <td><?= e((string)$u->email) ?></td>
          <td><span class="badge badge-secondary"><?= e((string)$u->role) ?></span></td>
          <td><?= e($u->restaurant()?->name ?? '—') ?></td>
          <td><span class="badge badge-<?= $u->is_active ? 'success' : 'warning' ?>"><?= $u->is_active ? 'Active' : 'Inactive' ?></span></td>
          <td>
            <form method="POST" action="<?= e(route('admin.users.update')) ?>" style="display:flex;gap:6px;align-items:center;">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$u->id ?>">
              <select name="role" style="padding:6px;border-radius:6px;border:1px solid var(--border);font-size:0.8rem;">
                <?php foreach ($roles as $r) : ?>
                  <option value="<?= e($r) ?>" <?= (string)$u->role === $r ? 'selected' : '' ?>><?= e($r) ?></option>
                <?php endforeach; ?>
              </select>
              <select name="restaurant_id" style="padding:6px;border-radius:6px;border:1px solid var(--border);font-size:0.8rem;">
                <option value="0">— no restaurant —</option>
                <?php foreach ($restaurants as $r) : ?>
                  <option value="<?= (int)$r->id ?>" <?= (int)$u->restaurant_id === (int)$r->id ? 'selected' : '' ?>><?= e((string)$r->name) ?></option>
                <?php endforeach; ?>
              </select>
              <select name="is_active" style="padding:6px;border-radius:6px;border:1px solid var(--border);font-size:0.8rem;">
                <option value="1" <?= $u->is_active ? 'selected' : '' ?>>Active</option>
                <option value="0" <?= !$u->is_active ? 'selected' : '' ?>>Inactive</option>
              </select>
              <button type="submit" class="btn-edit"><i class="fas fa-save"></i></button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal-overlay" id="userModal">
  <div class="modal">
    <h3>Add User</h3>
    <form method="POST" action="<?= e(route('admin.users.store')) ?>">
      <?= csrf_field() ?>
      <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" required placeholder="e.g. Ram Delivery">
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required placeholder="delivery@bitezy.com">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" required minlength="6" placeholder="min 6 characters">
      </div>
      <div class="form-group">
        <label>Role</label>
        <select name="role" onchange="document.getElementById('userRestaurantGroup').style.display = this.value === 'manager' ? 'block' : 'none';">
          <?php foreach ($roles as $r) : ?>
            <option value="<?= e($r) ?>"><?= e(ucfirst($r)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group" id="userRestaurantGroup" style="display:none;">
        <label>Restaurant</label>
        <select name="restaurant_id">
          <?php foreach ($restaurants as $r) : ?>
            <option value="<?= (int)$r->id ?>"><?= e((string)$r->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;"><i class="fas fa-save"></i> Save</button>
    </form>
  </div>
</div>