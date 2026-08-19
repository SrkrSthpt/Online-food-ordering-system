<?php
/** @var \App\Models\Restaurant|null $restaurant */
/** @var \App\Models\MenuItem[] $items */
/** @var \App\Models\Restaurant[] $restaurants */
?>
<?php if ($restaurant) : ?>
<div class="restaurant-detail-header">
  <h1><?= e($restaurant->name) ?></h1>
  <p><i class="fas fa-map-marker-alt"></i> <?= e((string)$restaurant->location) ?></p>
  <p><i class="fas fa-utensils"></i> <?= count($items) ?> items available</p>
</div>
<?php else : ?>
<section class="section">
  <div class="section-header">
    <h2>All <span>Restaurants</span></h2>
    <p>Choose a restaurant to view its menu</p>
  </div>
  <div class="category-pills">
    <button class="pill active" onclick="window.location.href='<?= e(route('menu.index')) ?>'">All</button>
    <?php foreach ($restaurants as $r) : ?>
      <button class="pill" onclick="window.location.href='<?= e(route('menu.index')) ?>?restaurant=<?= (int)$r->id ?>'">
        <?= e($r->name) ?>
      </button>
    <?php endforeach; ?>
  </div>
    <?php if (count($restaurants) === 0) : ?>
    <p style="text-align:center;color:var(--gray);">No restaurants available yet.</p>
    <?php endif; ?>
</section>
<?php endif; ?>

<section class="section" style="padding-top: <?= $restaurant ? '20px' : '0' ?>">
  <?php if ($restaurant) : ?>
    <div style="margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
      <h2 style="font-size:1.5rem;">Menu Items</h2>
      <a href="<?= e(route('menu.index')) ?>" class="btn btn-outline" style="padding:8px 20px;font-size:0.85rem;">
        <i class="fas fa-arrow-left"></i> All Restaurants
      </a>
    </div>
  <?php endif; ?>

  <div class="menu-grid" id="menuGrid">
    <?php if (count($items) > 0) : ?>
        <?php foreach ($items as $item) : ?>
            <?php $rating = (float)($item->rating ?? 4.5); ?>
        <div class="menu-item-card">
          <div class="menu-item-body">
            <h3><?= e($item->name) ?></h3>
            <?php if (!empty($item->description)) : ?>
              <p class="menu-item-desc"><?= e((string)$item->description) ?></p>
            <?php endif; ?>
            <div class="menu-item-rating">
              <i class="fas fa-star"></i> <?= number_format($rating, 1) ?>
            </div>
            <div class="menu-item-footer">
              <span class="menu-item-price"><?= e(money((float)$item->price)) ?></span>
              <button class="btn-add-cart" onclick="addToCart(<?= (int)$item->id ?>, this)">
                <i class="fas fa-shopping-cart"></i> Add
              </button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
    <?php else : ?>
      <p style="grid-column:1/-1;text-align:center;color:var(--gray);padding:60px 0;">
        <i class="fas fa-utensils" style="font-size:3rem;display:block;margin-bottom:16px;color:var(--border);"></i>
        No menu items available for this restaurant yet.
      </p>
    <?php endif; ?>
  </div>
</section>