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
  image_url VARCHAR(255) DEFAULT 'assets/images/default-restaurant.jpg',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE menu_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  restaurant_id INT NOT NULL,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  rating DECIMAL(2,1) DEFAULT 4.5,
  image_url VARCHAR(255) DEFAULT 'assets/images/default-food.jpg',
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

INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@bitezy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Manager User', 'manager@bitezy.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager');

INSERT INTO restaurants (name, location, image_url) VALUES
('Pizza Paradise', '123 Main Street, Downtown', 'assets/images/restaurant1.jpg'),
('Burger Barn', '456 Oak Avenue, Midtown', 'assets/images/restaurant2.jpg'),
('Sushi World', '789 Elm Boulevard, Uptown', 'assets/images/restaurant3.jpg'),
('Taco Fiesta', '321 Pine Road, Eastside', 'assets/images/restaurant4.jpg'),
('Pasta Italia', '654 Maple Drive, Westend', 'assets/images/restaurant5.jpg');

INSERT INTO menu_items (restaurant_id, name, description, price, image_url) VALUES
(1, 'Margherita Pizza', 'Classic tomato, mozzarella, and basil', 12.99, 'assets/images/food1.jpg'),
(1, 'Pepperoni Pizza', 'Loaded with pepperoni and mozzarella', 14.99, 'assets/images/food2.jpg'),
(1, 'BBQ Chicken Pizza', 'Grilled chicken, BBQ sauce, red onions', 16.99, 'assets/images/food3.jpg'),
(2, 'Classic Burger', 'Beef patty, lettuce, tomato, special sauce', 10.99, 'assets/images/food4.jpg'),
(2, 'Cheese Burger', 'Double cheese, beef patty, pickles', 12.99, 'assets/images/food5.jpg'),
(2, 'Bacon Burger', 'Crispy bacon, cheddar, BBQ sauce', 14.99, 'assets/images/food6.jpg'),
(3, 'California Roll', 'Crab, avocado, cucumber', 8.99, 'assets/images/food7.jpg'),
(3, 'Salmon Nigiri', 'Fresh salmon over seasoned rice', 12.99, 'assets/images/food8.jpg'),
(3, 'Dragon Roll', 'Shrimp tempura, eel sauce, avocado', 14.99, 'assets/images/food9.jpg'),
(4, 'Chicken Taco', 'Grilled chicken, salsa, sour cream', 5.99, 'assets/images/food10.jpg'),
(4, 'Beef Burrito', 'Seasoned beef, beans, cheese, rice', 9.99, 'assets/images/food11.jpg'),
(4, 'Nachos Supreme', 'Tortilla chips, cheese, jalapenos, guacamole', 8.99, 'assets/images/food12.jpg'),
(5, 'Spaghetti Carbonara', 'Creamy egg sauce, pancetta, parmesan', 13.99, 'assets/images/food13.jpg'),
(5, 'Lasagna', 'Layers of pasta, beef ragu, béchamel', 15.99, 'assets/images/food14.jpg'),
(5, 'Tiramisu', 'Classic Italian coffee dessert', 7.99, 'assets/images/food15.jpg');
