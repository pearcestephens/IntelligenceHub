<?php
/**
 * Bot Deployment Management Center - Configuration
 *
 * Enterprise-grade configuration management with environment support
 * and security best practices.
 *
 * @package BotDeployment
 * @version 2.0.0
 * @author Ecigdis Limited
 */

namespace BotDeployment\Config;

class Config
{
    /**
     * Configuration cache
     * @var array
     */
    private static $cache = [];

    /**
     * Environment variables loaded flag
     * @var bool
     */
    private static $envLoaded = false;

    /**
     * Database configuration
     * @return array
     */
    public static function database(): array
    {
        return [
            'host' => self::env('DB_HOST', '127.0.0.1'),
            'port' => self::env('DB_PORT', 3306),
            'database' => self::env('DB_NAME', 'hdgwrzntwa'),
            'username' => self::env('DB_USER', 'hdgwrzntwa'),
            'password' => self::env('DB_PASS', 'bFUdRjh4Jx'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'options' => [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_PERSISTENT => true,
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]
        ];
    }

    /**
     * AI Agent configuration
     * @return array
     */
    public static function aiAgent(): array
    {
        return [
            'endpoint' => self::env('AI_AGENT_ENDPOINT', 'https://gpt.ecigdis.co.nz/ai-agent/api/chat.php'),
            'api_key' => self::env('AI_AGENT_API_KEY', '31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35'),
            'timeout' => (int) self::env('AI_AGENT_TIMEOUT', 45),
            'retry_attempts' => (int) self::env('AI_AGENT_RETRY', 3),
            'retry_delay' => (int) self::env('AI_AGENT_RETRY_DELAY', 1000),
            'models' => [
                'default' => 'gpt-5-turbo',
                'fast' => 'gpt-4-turbo',
                'deep' => 'gpt-5-turbo-deep'
            ]
        ];
    }

    /**
     * MCP Server configuration
     * @return array
     */
    public static function mcpServer(): array
    {
        return [
            'endpoint' => self::env('MCP_ENDPOINT', 'https://gpt.ecigdis.co.nz/mcp/server_v3.php'),
            'api_key' => self::env('MCP_API_KEY', '31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35'),
            'version' => '3.0.0',
            'timeout' => 30
        ];
    }

    /**
     * Bot execution configuration
     * @return array
     */
    public static function botExecution(): array
    {
        return [
            'max_concurrent' => (int) self::env('BOT_MAX_CONCURRENT', 10),
            'default_timeout' => (int) self::env('BOT_TIMEOUT', 300),
            'memory_limit' => self::env('BOT_MEMORY_LIMIT', '512M'),
            'max_retries' => (int) self::env('BOT_MAX_RETRIES', 3),
            'retry_backoff' => [100, 500, 2000], // milliseconds
            'enable_queue' => self::env('BOT_ENABLE_QUEUE', true),
            'queue_driver' => self::env('BOT_QUEUE_DRIVER', 'database')
        ];
    }

    /**
     * Multi-threading configuration
     * @return array
     */
    public static function multiThread(): array
    {
        return [
            'min_threads' => 2,
            'max_threads' => 6,
            'default_threads' => 4,
            'thread_timeout' => (int) self::env('THREAD_TIMEOUT', 600),
            'merge_strategies' => [
                'sequential' => 'Merge in thread order',
                'priority' => 'Merge by priority/importance',
                'consensus' => 'Merge by consensus scoring',
                'hybrid' => 'Adaptive merge strategy'
            ]
        ];
    }

    /**
     * Security configuration
     * @return array
     */
    public static function security(): array
    {
        return [
            'csrf_enabled' => true,
            'csrf_token_name' => '_token',
            'rate_limit_enabled' => true,
            'rate_limit_requests' => 100,
            'rate_limit_window' => 60, // seconds
            'allowed_ips' => self::env('ALLOWED_IPS', ''),
            'require_auth' => true,
            'session_timeout' => 3600,
            'secure_headers' => true
        ];
    }

    /**
     * Logging configuration
     * @return array
     */
    public static function logging(): array
    {
        return [
            'enabled' => true,
            'level' => self::env('LOG_LEVEL', 'info'),
            'path' => self::env('LOG_PATH', __DIR__ . '/../../../logs/bot-deployment'),
            'max_files' => 30,
            'channels' => [
                'execution' => 'bot-execution.log',
                'api' => 'bot-api.log',
                'error' => 'bot-error.log',
                'security' => 'bot-security.log',
                'performance' => 'bot-performance.log'
            ]
        ];
    }

    /**
     * Monitoring and metrics configuration
     * @return array
     */
    public static function monitoring(): array
    {
        return [
            'enabled' => true,
            'metrics_retention_days' => 90,
            'performance_tracking' => true,
            'error_tracking' => true,
            'alert_thresholds' => [
                'error_rate' => 0.05, // 5% error rate triggers alert
                'avg_execution_time' => 30000, // 30s average triggers alert
                'queue_depth' => 100 // 100 pending jobs triggers alert
            ]
        ];
    }

    /**
     * Cache configuration
     * @return array
     */
    public static function cache(): array
    {
        return [
            'enabled' => self::env('CACHE_ENABLED', true),
            'driver' => self::env('CACHE_DRIVER', 'redis'),
            'redis' => [
                'host' => self::env('REDIS_HOST', '127.0.0.1'),
                'port' => (int) self::env('REDIS_PORT', 6379),
                'password' => self::env('REDIS_PASSWORD', null),
                'database' => (int) self::env('REDIS_DB', 0)
            ],
            'ttl' => [
                'bot_config' => 3600,
                'metrics' => 300,
                'session' => 1800
            ]
        ];
    }

    /**
     * Get environment variable
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function env(string $key, $default = null)
    {
        self::loadEnv();

        // Check environment
        $value = getenv($key);
        if ($value !== false) {
            return self::parseEnvValue($value);
        }

        // Check $_ENV
        if (isset($_ENV[$key])) {
            return self::parseEnvValue($_ENV[$key]);
        }

        return $default;
    }

    /**
     * Load environment variables from .env file
     */
    private static function loadEnv(): void
    {
        if (self::$envLoaded) {
            return;
        }

        $envFile = __DIR__ . '/../../../../.env';
        if (!file_exists($envFile)) {
            self::$envLoaded = true;
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($key, $value) = explode('=', $line, 2) + [null, null];
            if ($key !== null && $value !== null) {
                $key = trim($key);
                $value = trim($value);

                if (!getenv($key) && !isset($_ENV[$key])) {
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                }
            }
        }

        self::$envLoaded = true;
    }

    /**
     * Parse environment value
     * @param string $value
     * @return mixed
     */
    private static function parseEnvValue(string $value)
    {
        // Remove quotes
        if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
            $value = $matches[2];
        }

        // Parse boolean
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
        }

        return $value;
    }

    /**
     * Get all configuration
     * @return array
     */
    public static function all(): array
    {
        return [
            'database' => self::database(),
            'ai_agent' => self::aiAgent(),
            'mcp_server' => self::mcpServer(),
            'bot_execution' => self::botExecution(),
            'multi_thread' => self::multiThread(),
            'security' => self::security(),
            'logging' => self::logging(),
            'monitoring' => self::monitoring(),
            'cache' => self::cache()
        ];
    }

    /**
     * Get configuration value by dot notation
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $config = self::all();

        foreach ($keys as $segment) {
            if (!isset($config[$segment])) {
                return $default;
            }
            $config = $config[$segment];
        }

        return $config;
    }
}
