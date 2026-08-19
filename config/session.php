<?php

declare(strict_types=1);

use App\Core\Config;

Config::set('session.driver', env('SESSION_DRIVER', 'file'));
Config::set('session.lifetime', (int)env('SESSION_LIFETIME', 120));
Config::set('session.cookie', env('SESSION_COOKIE', 'bitezy_session'));
Config::set('session.secure', (bool)env('SESSION_SECURE_COOKIE', false));
Config::set('session.path', storage_path('session'));
Config::set('session.same_site', 'Lax');
