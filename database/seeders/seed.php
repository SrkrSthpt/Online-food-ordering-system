<?php

/**
 * Bitezy database seeder.
 *
 * Idempotent: only seeds demo data when tables are empty so existing live
 * data (restaurants, menu items, customers) is never overwritten.
 *
 * Usage:  php bin/bitezy db:seed [--env=testing]
 */

declare(strict_types=1);

use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\User;

$now = date('Y-m-d H:i:s');

// ---------------------------------------------------------------------------
// Users (password: 'password')
// ---------------------------------------------------------------------------
if (User::count() === 0) {
    $hash = password_hash('password', PASSWORD_ARGON2ID);
    User::create(['name' => 'Admin User', 'email' => 'admin@bitezy.com', 'password' => $hash, 'role' => 'admin']);
    User::create(['name' => 'Manager User', 'email' => 'manager@bitezy.com', 'password' => $hash, 'role' => 'manager']);
    User::create(['name' => 'Demo Customer', 'email' => 'customer@bitezy.com', 'password' => $hash, 'role' => 'customer']);
    User::create(['name' => 'Demo Delivery', 'email' => 'delivery@bitezy.com', 'password' => $hash, 'role' => 'delivery']);
    echo "Seeded 4 demo users (admin / manager / delivery / customer — password: password)\n";
} else {
    echo "Users table not empty — skipped.\n";
}

// ---------------------------------------------------------------------------
// Restaurants
// ---------------------------------------------------------------------------
if (Restaurant::count() === 0) {
    $restaurants = [
        ['Pizza Paradise', '123 Main Street, Downtown', 4.5, 320],
        ['Burger Barn', '456 Oak Avenue, Midtown', 4.2, 540],
        ['Sushi World', '789 Elm Boulevard, Uptown', 4.7, 210],
        ['Taco Fiesta', '321 Pine Road, Eastside', 4.1, 780],
        ['Pasta Italia', '654 Maple Drive, Westend', 4.6, 390],
    ];
    foreach ($restaurants as [$name, $location, $rating, $reviews]) {
        Restaurant::create([
            'name' => $name,
            'location' => $location,
            'rating' => $rating,
            'review_count' => $reviews,
            'image_url' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=600',
        ]);
    }
    echo "Seeded 5 demo restaurants.\n";
} else {
    echo "Restaurants table not empty — skipped.\n";
}

// ---------------------------------------------------------------------------
// Menu items
// ---------------------------------------------------------------------------
if (MenuItem::count() === 0) {
    $restaurantId = Restaurant::db()->scalar('SELECT MIN(id) FROM restaurants');
    $menus = [
        ['Margherita Pizza', 'Classic tomato, mozzarella and basil.', 12.99, 4.6],
        ['Pepperoni Pizza', 'Loaded with pepperoni and mozzarella.', 14.99, 4.8],
        ['BBQ Chicken Pizza', 'Grilled chicken, BBQ sauce, red onions.', 16.99, 4.4],
        ['Classic Burger', 'Beef patty, lettuce, tomato, special sauce.', 10.99, 4.3],
        ['Cheese Burger', 'Double cheese, beef patty, pickles.', 12.99, 4.7],
        ['California Roll', 'Crab, avocado and cucumber.', 8.99, 4.5],
    ];
    foreach ($menus as [$name, $desc, $price, $rating]) {
        MenuItem::create([
            'restaurant_id' => (int)$restaurantId,
            'name' => $name,
            'description' => $desc,
            'price' => $price,
            'rating' => $rating,
            'review_count' => rand(50, 400),
        ]);
    }
    echo "Seeded demo menu items.\n";
} else {
    echo "menu_items table not empty — skipped.\n";
}

echo 'Seeding complete.' . PHP_EOL;
echo 'Demo logins: admin@bitezy.com / manager@bitezy.com / delivery@bitezy.com / customer@bitezy.com — password: password' . PHP_EOL;
