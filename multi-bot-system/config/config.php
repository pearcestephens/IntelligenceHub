<?php
/**
 * Multi-Bot System - Configuration
 *
 * @package MultiBot
 * @version 1.0.0
 */

declare(strict_types=1);

return [
    // Application
    'app' => [
        'name' => 'Multi-Bot Management System',
        'version' => '1.0.0',
        'environment' => $_ENV['APP_ENV'] ?? 'production',
        'debug' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true',
        'timezone' => 'Pacific/Auckland',
        'url' => 'https://gpt.ecigdis.co.nz/multi-bot-system',
    ],

    // Database
    'database' => [
        'host' => $_ENV['DB_HOST'] ?? 'localhost',
        'name' => $_ENV['DB_NAME'] ?? 'hdgwrzntwa',
        'user' => $_ENV['DB_USER'] ?? 'hdgwrzntwa',
        'pass' => $_ENV['DB_PASS'] ?? '',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],

    // Redis Cache
    'redis' => [
        'enabled' => true,
        'host' => '127.0.0.1',
        'port' => 6379,
        'prefix' => 'multibot:',
        'ttl' => 3600, // 1 hour default
    ],

    // Multi-Bot Settings
    'bots' => [
        'max_concurrent_sessions' => 10,
        'max_participants_per_session' => 5,
        'session_timeout' => 3600, // 1 hour
        'message_retention_days' => 30,
        'max_message_length' => 4000,
    ],

    // AI Configuration
    'ai' => [
        'openai' => [
            'api_key' => $_ENV['OPENAI_API_KEY'] ?? '',
            'model' => 'gpt-4o',
            'temperature' => 0.7,
            'max_tokens' => 4096,
        ],
        'anthropic' => [
            'api_key' => $_ENV['ANTHROPIC_API_KEY'] ?? '',
            'model' => 'claude-3-5-sonnet-20241022',
            'max_tokens' => 4096,
        ],
    ],

    // Security
    'security' => [
        'api_key_header' => 'X-API-Key',
        'rate_limit' => [
            'enabled' => true,
            'max_requests' => 100,
            'per_minutes' => 15,
        ],
        'allowed_origins' => [
            'https://gpt.ecigdis.co.nz',
            'https://staff.vapeshed.co.nz',
        ],
    ],

    // Logging
    'logging' => [
        'enabled' => true,
        'level' => 'info', // debug, info, warning, error
        'path' => __DIR__ . '/../logs',
        'max_files' => 30,
    ],

    // Paths
    'paths' => [
        'root' => dirname(__DIR__),
        'public' => dirname(__DIR__) . '/public',
        'src' => dirname(__DIR__) . '/src',
        'logs' => dirname(__DIR__) . '/logs',
        'database' => dirname(__DIR__) . '/database',
    ],
];
