<?php

declare(strict_types=1);

use App\Core\Config;

Config::set('security.hash_cost', (int)env('HASH_COST', 12));
Config::set('security.csrf_token_name', '_token');
Config::set('security.rate_limit.max_attempts', (int)env('RATE_LIMIT_MAX_ATTEMPTS', 60));
Config::set('security.rate_limit.window', (int)env('RATE_LIMIT_WINDOW', 60));
Config::set('security.session_timeout', (int)env('SESSION_LIFETIME', 120) * 60);
Config::set('security.password_min', 6);
