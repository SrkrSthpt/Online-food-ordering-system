<?php

declare(strict_types=1);

return [
    'up' => [
        'CREATE TABLE IF NOT EXISTS payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            method VARCHAR(50) DEFAULT \'paypal\',
            status ENUM(\'pending\', \'completed\', \'failed\') DEFAULT \'pending\',
            transaction_id VARCHAR(255) NULL,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            KEY order_id (order_id),
            CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
        'ALTER TABLE payments ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL',
    ],
    'down' => [
        'DROP TABLE IF EXISTS payments',
    ],
];