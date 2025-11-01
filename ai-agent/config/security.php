<?php

/**
 * Security policy configuration
 * Centralizes CSRF, headers, and rate-limit knobs.
 *
 * @package Config
 */

declare(strict_types=1);

use App\Config;

return [
    'csrf' => [
        'token_key' => Config::get('CSRF_TOKEN_KEY', 'cis_csrf_token'),
        'ttl' => Config::get('CSRF_TTL_SECONDS', 7200),
    ],
    'headers' => [
        'Strict-Transport-Security' => 'max-age=63072000; includeSubDomains; preload',
        'X-Frame-Options' => 'DENY',
        'X-Content-Type-Options' => 'nosniff',
        'Referrer-Policy' => 'no-referrer-when-downgrade',
        'Permissions-Policy' => 'microphone=(), camera=()',
    ],
    'cors' => [
        'enabled' => Config::get('CORS_ENABLED', 'false') === 'true',
        'origins' => array_map('trim', explode(',', Config::get('CORS_ALLOWED_ORIGINS', 'same-origin'))),
        'methods' => ['GET', 'POST', 'OPTIONS'],
        'headers' => ['Content-Type', 'X-Requested-With', 'X-CSRF-Token'],
    ],
    'rate_limiter' => [
        'window_ms' => Config::get('RATE_LIMIT_WINDOW_MS', 60000),
        'max_requests' => Config::get('RATE_LIMIT_MAX', 120),
    ],
];
