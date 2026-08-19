<?php

declare(strict_types=1);

return [
    'up' => [
        'CREATE TABLE IF NOT EXISTS restaurants (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            location TEXT NULL,
            rating DECIMAL(2,1) DEFAULT 4.0,
            review_count INT DEFAULT 0,
            image_url VARCHAR(255) DEFAULT \'assets/images/default-restaurant.jpg\',
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
        'ALTER TABLE restaurants ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL',
    ],
    'down' => [
        'DROP TABLE IF EXISTS restaurants',
    ],
];