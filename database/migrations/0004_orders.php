<?php

declare(strict_types=1);

return [
    'up' => [
        'CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            status ENUM(\'pending\', \'preparing\', \'delivered\') DEFAULT \'pending\',
            total DECIMAL(10,2) DEFAULT 0.00,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            KEY user_id (user_id),
            CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
        'ALTER TABLE orders ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL',
    ],
    'down' => [
        'DROP TABLE IF EXISTS orders',
    ],
];