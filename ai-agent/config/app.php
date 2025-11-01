<?php

/**
 * Application configuration bootstrap
 * Provides environment-aware settings consumed by kernel and router.
 *
 * @package Config
 * @author Ecigdis Limited
 */

declare(strict_types=1);

use App\Config;

return [
    'env' => Config::get('APP_ENV', 'production'),
    'debug' => Config::getBool('APP_DEBUG', false),
    'timezone' => Config::get('APP_TIMEZONE', 'Pacific/Auckland'),
    'base_url' => Config::get('APP_BASE_URL', 'https://staff.vapeshed.co.nz'),
    'admin_prefix' => Config::get('APP_ADMIN_PREFIX', '/admin'),
    'session_name' => Config::get('SESSION_NAME', 'cis_admin'),
    'csrf_token_key' => Config::get('CSRF_TOKEN_KEY', 'cis_csrf_token'),
    'rate_limit' => [
        'window_ms' => Config::get('RATE_LIMIT_WINDOW_MS', 60000),
        'max_requests' => Config::get('RATE_LIMIT_MAX', 120),
    ],
    'sse' => [
        'keepalive' => Config::get('SSE_KEEPALIVE_SEC', 20),
        'retry_ms' => 5000,
    ],
    'logging' => [
        'level' => Config::get('LOG_LEVEL', 'info'),
        'channel' => 'cis-admin',
    ],
];
