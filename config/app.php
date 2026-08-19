<?php

declare(strict_types=1);

use App\Core\Config;

Config::set('app.name', env('APP_NAME', 'Bitezy'));
Config::set('app.env', env('APP_ENV', 'local'));
Config::set('app.debug', (bool)env('APP_DEBUG', true));
Config::set('app.url', rtrim((string)env('APP_URL', 'http://localhost:8080'), '/'));
Config::set('app.key', env('APP_KEY', ''));
Config::set('app.timezone', env('APP_TIMEZONE', 'Asia/Kathmandu'));
Config::set('app.locale', env('APP_LOCALE', 'en'));
Config::set('app.currency', env('APP_CURRENCY', 'NPR'));
Config::set('app.currency_symbol', env('APP_CURRENCY_SYMBOL', 'rs. '));
Config::set('app.delivery_fee', (float)env('DELIVERY_FEE', 99));
Config::set('app.delivery_eta_minutes', (int)env('DELIVERY_ETA_MINUTES', 30));
Config::set('app.asset_version', '1.0.0');
