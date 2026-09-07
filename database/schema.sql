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

-- Demo restaurants
INSERT INTO restaurants (name, location, image_url) VALUES
('Pizza Paradise', '123 Main Street, Downtown', 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=600'),
('Burger Barn', '456 Oak Avenue, Midtown', 'https://images.unsplash.com/photo-1466978913421-dad2ebd01d17?w=600'),
('Sushi World', '789 Elm Boulevard, Uptown', 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?w=600'),
('Taco Fiesta', '321 Pine Road, Eastside', 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=600'),
('Pasta Italia', '654 Maple Drive, Westend', 'https://images.unsplash.com/photo-1551183053-bf91a1d81141?w=600');

-- Demo menu items
INSERT INTO menu_items (restaurant_id, name, description, price, image_url) VALUES
(1, 'Margherita Pizza', 'Classic tomato, mozzarella, and basil', 12.99, 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=400'),
(1, 'Pepperoni Pizza', 'Loaded with pepperoni and mozzarella', 14.99, 'https://images.unsplash.com/photo-1628840042765-356cda07504e?w=400'),
(1, 'BBQ Chicken Pizza', 'Grilled chicken, BBQ sauce, red onions', 16.99, 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=400'),
(2, 'Classic Burger', 'Beef patty, lettuce, tomato, special sauce', 10.99, 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400'),
(2, 'Cheese Burger', 'Double cheese, beef patty, pickles', 12.99, 'https://images.unsplash.com/photo-1553979459-d2229ba7433b?w=400'),
(2, 'Bacon Burger', 'Crispy bacon, cheddar, BBQ sauce', 14.99, 'https://images.unsplash.com/photo-1551615593-ef5fe247e8f7?w=400'),
(3, 'California Roll', 'Crab, avocado, cucumber', 8.99, 'https://images.unsplash.com/photo-1579584425555-c3ce17fd4351?w=400'),
(3, 'Salmon Nigiri', 'Fresh salmon over seasoned rice', 12.99, 'https://images.unsplash.com/photo-1583623025817-d180a2221d0a?w=400'),
(3, 'Dragon Roll', 'Shrimp tempura, eel sauce, avocado', 14.99, 'https://images.unsplash.com/photo-1617196034796-73dfa7b1fd56?w=400'),
(4, 'Chicken Taco', 'Grilled chicken, salsa, sour cream', 5.99, 'https://images.unsplash.com/photo-1551504734-5ee1c4a1479b?w=400'),
(4, 'Beef Burrito', 'Seasoned beef, beans, cheese, rice', 9.99, 'https://images.unsplash.com/photo-1589302168068-964664d93dc0?w=400'),
(4, 'Nachos Supreme', 'Tortilla chips, cheese, jalapenos, guacamole', 8.99, 'https://images.unsplash.com/photo-1513456852971-30c0b8199d4d?w=400'),
(5, 'Spaghetti Carbonara', 'Creamy egg sauce, pancetta, parmesan', 13.99, 'https://images.unsplash.com/photo-1612874742237-6526221588e3?w=400'),
(5, 'Lasagna', 'Layers of pasta, beef ragu, bechamel', 15.99, 'https://images.unsplash.com/photo-1574894709920-11b28e7367e3?w=400'),
(5, 'Tiramisu', 'Classic Italian coffee dessert', 7.99, 'https://images.unsplash.com/photo-1571877227200-a0d98ea607e9?w=400');
