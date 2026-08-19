<?php

declare(strict_types=1);

use App\Core\Config;

Config::set('database.connection', env('DB_CONNECTION', 'mysql'));
Config::set('database.host', env('DB_HOST', '127.0.0.1'));
Config::set('database.port', env('DB_PORT', '3306'));
Config::set('database.name', env('DB_DATABASE', env('APP_ENV', 'local') === 'testing' ? 'bitezy_test' : 'bitezy'));
Config::set('database.username', env('DB_USERNAME', 'root'));
Config::set('database.password', env('DB_PASSWORD', ''));
Config::set('database.charset', env('DB_CHARSET', 'utf8mb4'));
Config::set('database.collation', env('DB_COLLATION', 'utf8mb4_unicode_ci'));
