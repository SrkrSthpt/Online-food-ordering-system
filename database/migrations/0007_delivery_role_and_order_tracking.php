<?php

declare(strict_types=1);

return [
    'up' => [
        // New delivery role for the order-delivery pipeline.
        'ALTER TABLE users MODIFY role ENUM(\'customer\', \'manager\', \'delivery\', \'admin\') DEFAULT \'customer\'',
        'ALTER TABLE users ADD COLUMN restaurant_id INT NULL',
        'ALTER TABLE users ADD KEY idx_users_restaurant (restaurant_id)',
        'ALTER TABLE users ADD CONSTRAINT fk_users_restaurant FOREIGN KEY (restaurant_id) REFERENCES restaurants (id) ON DELETE SET NULL',

        // Orders now track the originating restaurant, the assigned courier and
        // the system-computed estimated arrival time.
        'ALTER TABLE orders MODIFY status ENUM(\'pending\', \'accepted\', \'preparing\', \'prepared\', \'out_for_delivery\', \'delivered\') DEFAULT \'pending\'',
        'ALTER TABLE orders ADD COLUMN restaurant_id INT NULL',
        'ALTER TABLE orders ADD COLUMN delivery_person_id INT NULL',
        'ALTER TABLE orders ADD COLUMN eta DATETIME NULL',
        'ALTER TABLE orders ADD KEY idx_orders_restaurant (restaurant_id)',
        'ALTER TABLE orders ADD CONSTRAINT fk_orders_restaurant FOREIGN KEY (restaurant_id) REFERENCES restaurants (id) ON DELETE SET NULL',
        'ALTER TABLE orders ADD KEY idx_orders_delivery_person (delivery_person_id)',
        'ALTER TABLE orders ADD CONSTRAINT fk_orders_delivery_person FOREIGN KEY (delivery_person_id) REFERENCES users (id) ON DELETE SET NULL',
    ],
    'down' => [
        'ALTER TABLE users DROP FOREIGN KEY fk_users_restaurant',
        'ALTER TABLE users DROP KEY idx_users_restaurant',
        'ALTER TABLE users DROP COLUMN restaurant_id',
        'ALTER TABLE users MODIFY role ENUM(\'customer\', \'manager\', \'admin\') DEFAULT \'customer\'',
        'ALTER TABLE orders DROP FOREIGN KEY fk_orders_delivery_person',
        'ALTER TABLE orders DROP KEY idx_orders_delivery_person',
        'ALTER TABLE orders DROP COLUMN delivery_person_id',
        'ALTER TABLE orders DROP FOREIGN KEY fk_orders_restaurant',
        'ALTER TABLE orders DROP KEY idx_orders_restaurant',
        'ALTER TABLE orders DROP COLUMN restaurant_id',
        'ALTER TABLE orders DROP COLUMN eta',
        'ALTER TABLE orders MODIFY status ENUM(\'pending\', \'preparing\', \'delivered\') DEFAULT \'pending\'',
    ],
];
