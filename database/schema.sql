CREATE DATABASE IF NOT EXISTS bitezy;
USE bitezy;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('customer', 'manager', 'admin') DEFAULT 'customer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE restaurants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  location TEXT,
  image_url VARCHAR(255) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE menu_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  restaurant_id INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  rating DECIMAL(2,1) DEFAULT 4.5,
  image_url VARCHAR(255) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
);

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  status ENUM('pending', 'preparing', 'delivered') DEFAULT 'pending',
  total DECIMAL(10,2) DEFAULT 0.00,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  menu_item_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  price DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
);

CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  method VARCHAR(50) DEFAULT 'paypal',
  status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
  transaction_id VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

-- Demo users (password: password)
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@bitezy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Manager User', 'manager@bitezy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager');

-- Demo restaurants (Nepali)
INSERT INTO restaurants (name, location, image_url) VALUES
('Momo Hut', 'Thamel, Kathmandu', 'https://thumb.wikimedia.org/wikipedia/commons/thumb/c/ce/Momo_food.jpg/960px-Momo_food.jpg'),
('Newari Kitchen', 'Patan, Lalitpur', 'https://thumb.wikimedia.org/wikipedia/commons/thumb/4/49/Newari_Khaja_Set_2.jpg/960px-Newari_Khaja_Set_2.jpg'),
('Himalayan Bhojanalaya', 'New Baneshwor, Kathmandu', 'https://thumb.wikimedia.org/wikipedia/commons/thumb/c/cd/Dal_bhat.jpg/960px-Dal_bhat.jpg'),
('Thamel Street Food', 'Thamel, Kathmandu', 'https://thumb.wikimedia.org/wikipedia/commons/thumb/9/9a/Buff_Chowmein.jpg/960px-Buff_Chowmein.jpg'),
('Chiya & Sweets House', 'Bhaktapur Durbar Square, Bhaktapur', 'https://thumb.wikimedia.org/wikipedia/commons/thumb/b/b8/Chiya_Sel_Roti.jpg/960px-Chiya_Sel_Roti.jpg');

-- Demo menu items (Nepali dishes, prices in Nepali Rupees)
INSERT INTO menu_items (restaurant_id, name, description, price, image_url) VALUES
(1, 'Steam Buff Momo', 'Juicy buffalo momo steamed to perfection, served with tomato achar', 250.00, 'https://thumb.wikimedia.org/wikipedia/commons/thumb/4/4b/Buff_Momo_1.jpg/960px-Buff_Momo_1.jpg'),
(1, 'Jhol Momo', 'Momo swimming in spicy sesame-tomato jhol soup', 280.00, 'https://thumb.wikimedia.org/wikipedia/commons/thumb/d/de/Jhol_Momo.jpg/960px-Jhol_Momo.jpg'),
(1, 'Chilli Momo', 'Fried momo tossed in fiery chilli sauce with onions and capsicum', 300.00, 'https://thumb.wikimedia.org/wikipedia/commons/thumb/f/f0/Chilli_Momo.jpg/960px-Chilli_Momo.jpg'),
(2, 'Newari Khaja Set', 'Chiura, bhatmas, aloo, tama, achar and grilled meat — the classic Newari platter', 450.00, 'https://thumb.wikimedia.org/wikipedia/commons/thumb/d/d7/Newari_Khaja_Set_1.jpg/960px-Newari_Khaja_Set_1.jpg'),
(2, 'Chatamari', 'Newari rice crepe topped with minced meat, egg and spices', 180.00, 'https://thumb.wikimedia.org/wikipedia/commons/thumb/e/e9/Meat_Chatamari.jpg/960px-Meat_Chatamari.jpg'),
(2, 'Bara', 'Savory black lentil pancake, plain or with egg', 150.00, 'https://thumb.wikimedia.org/wikipedia/commons/thumb/1/16/Bara_%E2%80%93_Traditional_Newari_Lentil_Pancake_of_Nepal.jpg/960px-Bara_%E2%80%93_Traditional_Newari_Lentil_Pancake_of_Nepal.jpg'),
(3, 'Dal Bhat Tarkari', 'The national meal — steamed rice, lentil soup, seasonal curry, gundruk and achar', 350.00, 'https://thumb.wikimedia.org/wikipedia/commons/thumb/6/60/Nepali_dal-bhat-tarkari.jpg/960px-Nepali_dal-bhat-tarkari.jpg'),
(3, 'Gundruk ko Jhol', 'Fermented greens in a light, tangy broth — perfect with rice', 200.00, 'https://thumb.wikimedia.org/wikipedia/commons/thumb/f/f6/Gundruk_Jhol.jpg/960px-Gundruk_Jhol.jpg'),
(3, 'Dhido Set', 'Traditional buckwheat dhido with gundruk soup, ghee and pickle', 250.00, 'https://thumb.wikimedia.org/wikipedia/commons/thumb/a/aa/Dhido.jpg/960px-Dhido.jpg'),
(4, 'Veg Chowmein', 'Wok-tossed noodles with fresh vegetables and Nepali spices', 180.00, 'https://thumb.wikimedia.org/wikipedia/commons/thumb/8/8c/Veg_Chowmein.jpg/960px-Veg_Chowmein.jpg'),
(4, 'Chicken Sekuwa', 'Skewered chicken grilled over charcoal with timur and masala', 400.00, 'https://thumb.wikimedia.org/wikipedia/commons/thumb/4/45/Sekuwa_%28Nepalese_Roasted_Meat%29.jpg/960px-Sekuwa_%28Nepalese_Roasted_Meat%29.jpg'),
(4, 'Samosa (2 pcs)', 'Crispy fried pastry stuffed with spiced potatoes and peas', 80.00, 'https://thumb.wikimedia.org/wikipedia/commons/thumb/a/af/Samosa_Nepal.jpg/960px-Samosa_Nepal.jpg'),
(5, 'Masala Chiya', 'Hot milk tea brewed with ginger, cardamom and masala', 50.00, 'https://thumb.wikimedia.org/wikipedia/commons/thumb/8/89/Masala_Chiya.jpg/960px-Masala_Chiya.jpg'),
(5, 'Juju Dhau', 'The famous "King of Curd" from Bhaktapur — creamy, sweet hung curd', 150.00, 'https://thumb.wikimedia.org/wikipedia/commons/thumb/d/df/Juju_Dhau_1.jpg/960px-Juju_Dhau_1.jpg'),
(5, 'Sel Roti', 'Ring-shaped sweet rice bread, crisp outside and soft inside', 90.00, 'https://thumb.wikimedia.org/wikipedia/commons/thumb/7/7e/Sel_roti.jpg/960px-Sel_roti.jpg');
