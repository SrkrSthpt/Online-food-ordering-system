<?php require_once __DIR__ . '/../includes/db.php'; ?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<?php
$restaurantId = isset($_GET['restaurant']) ? (int)$_GET['restaurant'] : 0;
$restaurant = null;
$menuItems = [];

if ($restaurantId > 0) {
  $restResult = dbQuery("SELECT * FROM restaurants WHERE id = ?", [$restaurantId]);
  $restaurant = $restResult ? $restResult->fetch_assoc() : null;
  if ($restaurant) {
    $itemsResult = dbQuery("SELECT * FROM menu_items WHERE restaurant_id = ? ORDER BY name", [$restaurantId]);
    if ($itemsResult) $menuItems = $itemsResult->fetch_all(MYSQLI_ASSOC);
  }
}

$allRestaurants = dbQuery("SELECT * FROM restaurants ORDER BY name");
$allRestaurantsList = $allRestaurants ? $allRestaurants->fetch_all(MYSQLI_ASSOC) : [];

$allItems = [];
if (!$restaurant) {
  $allItemsResult = dbQuery("SELECT mi.*, r.name as restaurant_name FROM menu_items mi JOIN restaurants r ON mi.restaurant_id = r.id ORDER BY r.name, mi.name");
  if ($allItemsResult) $allItems = $allItemsResult->fetch_all(MYSQLI_ASSOC);
}
?>

<?php if ($restaurant): ?>
<div class="restaurant-detail-header">
  <h1><?php echo htmlspecialchars($restaurant['name']); ?></h1>
  <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($restaurant['location']); ?></p>
  <p><i class="fas fa-utensils"></i> <?php echo count($menuItems); ?> items available</p>
</div>
<?php else: ?>
<section class="section">
  <div class="section-header">
    <h2>All <span>Restaurants</span></h2>
    <p>Choose a restaurant to view its menu</p>
  </div>
  <div class="category-pills">
    <button class="pill active" onclick="window.location.href='/bitezy/pages/menu.php'">All</button>
    <?php foreach ($allRestaurantsList as $r): ?>
      <button class="pill" onclick="window.location.href='/bitezy/pages/menu.php?restaurant=<?php echo $r['id']; ?>'">
        <?php echo htmlspecialchars($r['name']); ?>
      </button>
    <?php endforeach; ?>
  </div>
  <?php if (count($allRestaurantsList) === 0): ?>
    <p style="text-align:center;color:var(--gray);">No restaurants available yet.</p>
  <?php endif; ?>
</section>
<?php endif; ?>

<section class="section" style="padding-top: <?php echo $restaurant ? '20px' : '0'; ?>">
  <?php if ($restaurant): ?>
    <div style="margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
      <h2 style="font-size:1.5rem;">Menu Items</h2>
      <a href="/bitezy/pages/menu.php" class="btn btn-outline" style="padding:8px 20px;font-size:0.85rem;">
        <i class="fas fa-arrow-left"></i> All Restaurants
      </a>
    </div>
  <?php endif; ?>

  <div class="menu-grid" id="menuGrid">
    <?php
    $items = $restaurant ? $menuItems : $allItems;
    if (count($items) > 0):
      foreach ($items as $item):
        $rating = (float)($item['rating'] ?? 4.5);
      ?>
      <div class="menu-item-card">
        <div class="menu-item-body">
          <h3><?php echo htmlspecialchars($item['name']); ?></h3>
          <div class="menu-item-rating">
            <i class="fas fa-star"></i> <?php echo number_format($rating, 1); ?>
          </div>
          <div class="menu-item-footer">
            <span class="menu-item-price"><?php echo CURRENCY; ?><?php echo number_format($item['price'], 2); ?></span>
            <button class="btn-add-cart" onclick="addToCart(<?php echo $item['id']; ?>, this)">
              <i class="fas fa-shopping-cart"></i> Add
            </button>
          </div>
        </div>
      </div>
    <?php
      endforeach;
    else:
    ?>
    <p style="grid-column:1/-1;text-align:center;color:var(--gray);padding:60px 0;">
      <i class="fas fa-utensils" style="font-size:3rem;display:block;margin-bottom:16px;color:var(--border);"></i>
      No menu items available for this restaurant yet.
    </p>
    <?php endif; ?>
  </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
