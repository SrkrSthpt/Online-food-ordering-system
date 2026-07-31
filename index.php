<?php require_once __DIR__ . '/includes/db.php'; ?>
<?php include __DIR__ . '/includes/header.php'; ?>

<section class="hero">
  <div class="hero-slider">
    <div class="hero-slide active" style="background-image: url('https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=1600')"></div>
    <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=1600')"></div>
    <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=1600')"></div>
    <div class="hero-slide" style="background-image: url('https://images.unsplash.com/photo-1567620905732-2d1ec7ab7445?w=1600')"></div>
  </div>
  <div class="hero-overlay">
    <h1>Delicious Food, <span>Delivered Fast</span></h1>
    <p>Order from the best restaurants near you. Fresh food, fast delivery, great taste.</p>
    <div class="hero-search">
      <input type="text" id="searchInput" placeholder="Search for restaurants or dishes..." onkeyup="filterBySearch(event)">
      <button onclick="window.location.href='/bitezy/pages/menu.php'"><i class="fas fa-search"></i> Search</button>
    </div>
  </div>
</section>

<section class="section">
  <div class="section-header">
    <h2>How It <span>Works</span></h2>
    <p>Three simple steps to get your food delivered</p>
  </div>
  <div class="features-grid">
    <div class="feature-card">
      <div class="feature-icon"><i class="fas fa-store"></i></div>
      <h3>Choose a Restaurant</h3>
      <p>Browse through our curated list of top restaurants and cafes in your area.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon"><i class="fas fa-utensils"></i></div>
      <h3>Select Your Food</h3>
      <p>Pick from a wide variety of delicious dishes and customize your order.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon"><i class="fas fa-truck"></i></div>
      <h3>Fast Delivery</h3>
      <p>Your food is prepared fresh and delivered right to your doorstep.</p>
    </div>
  </div>
</section>

<section class="section" style="background: white; padding: 80px 20px; max-width: 100%;">
  <div class="section-header">
    <h2>Featured <span>Restaurants</span></h2>
    <p>Discover the most popular restaurants and cafes</p>
  </div>
  <div style="max-width: 1200px; margin: 0 auto;">
    <div class="restaurant-grid">
      <?php
      $restaurants = dbQuery("SELECT * FROM restaurants ORDER BY id DESC LIMIT 6");
      if ($restaurants && $restaurants->num_rows > 0) {
        while ($r = $restaurants->fetch_assoc()) {
          $itemCount = dbQuery("SELECT COUNT(*) as cnt FROM menu_items WHERE restaurant_id = ?", [$r['id']]);
          $count = $itemCount ? $itemCount->fetch_assoc()['cnt'] : 0;
          $img = !empty($r['image_url']) && $r['image_url'] !== 'assets/images/default-restaurant.jpg'
            ? $r['image_url']
            : 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=600';
          ?>
          <div class="restaurant-card" onclick="window.location.href='/bitezy/pages/menu.php?restaurant=<?php echo $r['id']; ?>'">
            <div class="restaurant-card-img" style="background-image: url('<?php echo $img; ?>')">
              <div class="overlay"></div>
              <div class="rating"><i class="fas fa-star"></i> 4.5</div>
            </div>
            <div class="restaurant-card-body">
              <h3><?php echo htmlspecialchars($r['name']); ?></h3>
              <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($r['location']); ?></p>
              <p style="color: var(--gray); font-size:0.8rem;"><i class="fas fa-utensils"></i> <?php echo $count; ?> items</p>
              <a href="/bitezy/pages/menu.php?restaurant=<?php echo $r['id']; ?>" class="btn">View Menu <i class="fas fa-arrow-right"></i></a>
            </div>
          </div>
          <?php
        }
      } else {
        echo '<p style="text-align:center;color:var(--gray);grid-column:1/-1;">No restaurants available yet.</p>';
      }
      ?>
    </div>
  </div>
</section>

<section class="section">
  <div class="section-header">
    <h2>What Our <span>Customers Say</span></h2>
    <p>Real reviews from real people</p>
  </div>
  <div class="testimonial-grid">
    <div class="testimonial-card">
      <p>"Bitezy is amazing! The food arrives hot and fresh every single time. Highly recommend!"</p>
      <div class="testimonial-author">
        <img src="https://i.pravatar.cc/100?img=1" alt="User">
        <div>
          <h4>Sarah Johnson</h4>
          <span>Regular Customer</span>
        </div>
      </div>
    </div>
    <div class="testimonial-card">
      <p>"Great selection of restaurants. I love being able to order from different cuisines all in one place."</p>
      <div class="testimonial-author">
        <img src="https://i.pravatar.cc/100?img=2" alt="User">
        <div>
          <h4>Mike Chen</h4>
          <span>Food Enthusiast</span>
        </div>
      </div>
    </div>
    <div class="testimonial-card">
      <p>"The delivery tracking is fantastic. I always know exactly when my food will arrive."</p>
      <div class="testimonial-author">
        <img src="https://i.pravatar.cc/100?img=3" alt="User">
        <div>
          <h4>Emily Rodriguez</h4>
          <span>Busy Professional</span>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
