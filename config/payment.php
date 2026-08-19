<?php

declare(strict_types=1);

use App\Core\Config;

$appUrl = (string)Config::get('app.url', 'http://localhost:8080');

Config::set('payment.default_method', env('PAYMENT_DEFAULT_METHOD', 'paypal'));
Config::set('payment.mode', env('PAYMENT_MODE', 'sandbox'));
Config::set('payment.mock', (bool)env('PAYMENT_MOCK', true));
Config::set('payment.website_url', rtrim((string)env('PAYMENT_WEBSITE_URL', $appUrl), '/'));

Config::set('payment.paypal.client_id', env('PAYPAL_CLIENT_ID', ''));
Config::set('payment.paypal.secret', env('PAYPAL_SECRET', ''));
Config::set('payment.paypal.mode', env('PAYPAL_MODE', 'sandbox'));
