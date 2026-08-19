<?php

declare(strict_types=1);

return [
    'up' => [
        'ALTER TABLE order_status_logs ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL',
    ],
    'down' => [
        'ALTER TABLE order_status_logs DROP COLUMN updated_at',
    ],
];
