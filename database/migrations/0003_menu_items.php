<?php

declare(strict_types=1);

return [
    'up' => [
        'CREATE TABLE IF NOT EXISTS menu_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            restaurant_id INT NOT NULL,
            name VARCHAR(150) NOT NULL,
            description TEXT NULL,
            price DECIMAL(10,2) NOT NULL,
            image_url VARCHAR(255) DEFAULT \'assets/images/default-food.jpg\',
            rating DECIMAL(2,1) DEFAULT 4.0,
            review_count INT DEFAULT 0,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            KEY restaurant_id (restaurant_id),
            CONSTRAINT fk_menu_items_restaurant FOREIGN KEY (restaurant_id) REFERENCES restaurants (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
        'ALTER TABLE menu_items ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL',
    ],
    'down' => [
        'DROP TABLE IF EXISTS menu_items',
    ],
];