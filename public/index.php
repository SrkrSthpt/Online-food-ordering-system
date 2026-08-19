<?php

declare(strict_types=1);

/**
 * Bitezy front controller — all requests route through here.
 */

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    http_response_code(500);
    echo 'Dependencies are not installed. Run: composer install';
    exit(1);
}

require $autoload;

$app = App\Core\Application::create($root);
$app->bootstrap();
$app->registerRoutes();

$response = $app->dispatch();
$response->send();