<?php

/**
 * Enterprise AI Agent Configuration
 * Production-grade configuration with Redis, advanced caching, and enterprise features
 *
 * @author Pearce Stephens - Ecigdis Limited
 * @package VapeShed Enterprise AI Platform
 * @version 2.0.0 Enterprise
 */

declare(strict_types=1);

namespace VapeShed\AI\Enterprise;

use Redis;
use PDO;
use Exception;

class EnterpriseConfig
{
    private static array $config = [];
    private static bool $initialized = false;
    private static ?Redis $redis = null;

    /**
     * Enterprise configuration initialization with Redis and advanced features
     */
    public static function initialize(): void
    {
        if (self::$initialized) {
            return;
        }

        // Load environment configuration
        self::loadEnvironmentConfig();

        // Initialize Redis connection pool
        self::initializeRedis();

        // Load cached configuration from Redis
        self::loadCachedConfig();

        // Validate enterprise requirements
        self::validateEnterpriseConfig();

        self::$initialized = true;
    }

    /**
     * Load environment configuration with enterprise defaults
     */
    private static function loadEnvironmentConfig(): void
    {
        // Core Enterprise Configuration
        self::$config = [
            // AI Model Configuration
            'openai' => [
                'api_key' => self::getEnv('OPENAI_API_KEY', ''),
                'model' => self::getEnv('OPENAI_MODEL', 'gpt-4o'),
                'temperature' => floatval(self::getEnv('AI_TEMPERATURE', '0.1')),
                'max_tokens' => intval(self::getEnv('AI_MAX_TOKENS', '4000')),
                'timeout' => intval(self::getEnv('AI_TIMEOUT', '120')),
                'rate_limit_rpm' => intval(self::getEnv('AI_RATE_LIMIT_RPM', '3500')),
                'rate_limit_tpm' => intval(self::getEnv('AI_RATE_LIMIT_TPM', '80000'))
            ],

            'anthropic' => [
                'api_key' => self::getEnv('ANTHROPIC_API_KEY', ''),
                'model' => self::getEnv('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022'),
                'temperature' => floatval(self::getEnv('CLAUDE_TEMPERATURE', '0.1')),
                'max_tokens' => intval(self::getEnv('CLAUDE_MAX_TOKENS', '4000')),
                'timeout' => intval(self::getEnv('CLAUDE_TIMEOUT', '120'))
            ],

            // Enterprise Database Configuration
            'database' => [
                'primary' => [
                    'host' => self::getEnv('DB_HOST', '127.0.0.1'),
                    'port' => intval(self::getEnv('DB_PORT', '3306')),
                    'database' => self::getEnv('DB_DATABASE', ''),
                    'username' => self::getEnv('DB_USERNAME', ''),
                    'password' => self::getEnv('DB_PASSWORD', ''),
                    'charset' => 'utf8mb4',
                    'timeout' => 30,
                    'pool_size' => 10
                ],
                'readonly' => [
                    'host' => self::getEnv('DB_READONLY_HOST', self::getEnv('DB_HOST', '127.0.0.1')),
                    'port' => intval(self::getEnv('DB_READONLY_PORT', '3306')),
                    'database' => self::getEnv('DB_DATABASE', ''),
                    'username' => self::getEnv('DB_READONLY_USERNAME', self::getEnv('DB_USERNAME', '')),
                    'password' => self::getEnv('DB_READONLY_PASSWORD', self::getEnv('DB_PASSWORD', '')),
                    'charset' => 'utf8mb4'
                ]
            ],

            // Redis Enterprise Configuration
            'redis' => [
                'host' => self::getEnv('REDIS_HOST', '127.0.0.1'),
                'port' => intval(self::getEnv('REDIS_PORT', '6379')),
                'password' => self::getEnv('REDIS_PASSWORD', ''),
                'database' => intval(self::getEnv('REDIS_DATABASE', '0')),
                'prefix' => self::getEnv('REDIS_PREFIX', 'vapeshed:ai:'),
                'timeout' => 5.0,
                'retry_interval' => 100,
                'max_retries' => 3,
                'persistent' => true,
                'cluster' => [
                    'enabled' => self::parseBool(self::getEnv('REDIS_CLUSTER_ENABLED', 'false')),
                    'nodes' => explode(',', self::getEnv('REDIS_CLUSTER_NODES', ''))
                ],
                'pools' => [
                    'cache' => ['database' => 0, 'prefix' => 'cache:'],
                    'sessions' => ['database' => 1, 'prefix' => 'session:'],
                    'analytics' => ['database' => 2, 'prefix' => 'analytics:'],
                    'realtime' => ['database' => 3, 'prefix' => 'realtime:'],
                    'intelligence' => ['database' => 4, 'prefix' => 'intelligence:']
                ]
            ],

            // Enterprise Security Configuration
            'security' => [
                'encryption_key' => self::getEnv('APP_ENCRYPTION_KEY', ''),
                'jwt_secret' => self::getEnv('JWT_SECRET', ''),
                'csrf_token_lifetime' => 3600,
                'session_lifetime' => 86400,
                'max_login_attempts' => 5,
                'lockout_duration' => 900,
                'password_min_length' => 12,
                'require_2fa' => self::parseBool(self::getEnv('REQUIRE_2FA', 'true')),
                'ip_whitelist' => explode(',', self::getEnv('IP_WHITELIST', '')),
                'rate_limiting' => [
                    'enabled' => true,
                    'max_requests_per_minute' => 100,
                    'max_requests_per_hour' => 1000,
                    'burst_allowance' => 20
                ]
            ],

            // Enterprise Performance Configuration
            'performance' => [
                'cache_ttl_default' => 3600,
                'cache_ttl_business_data' => 300,
                'cache_ttl_user_data' => 1800,
                'query_timeout' => 30,
                'max_concurrent_requests' => 100,
                'memory_limit' => '512M',
                'compression_enabled' => true,
                'cdn_enabled' => false
            ],

            // Enterprise Monitoring Configuration
            'monitoring' => [
                'enabled' => true,
                'error_reporting' => E_ALL,
                'log_level' => self::getEnv('LOG_LEVEL', 'info'),
                'metrics_enabled' => true,
                'profiling_enabled' => self::parseBool(self::getEnv('PROFILING_ENABLED', 'true')),
                'health_check_interval' => 60,
                'alert_thresholds' => [
                    'response_time_ms' => 5000,
                    'error_rate_percent' => 5,
                    'memory_usage_percent' => 80,
                    'cpu_usage_percent' => 80
                ]
            ],

            // Business Intelligence Configuration
            'business_intelligence' => [
                'enabled' => true,
                'real_time_analytics' => true,
                'predictive_analytics' => true,
                'data_retention_days' => 365,
                'aggregation_intervals' => ['1m', '5m', '15m', '1h', '1d', '1w'],
                'kpi_refresh_interval' => 300,
                'dashboard_refresh_interval' => 30
            ],

            // Enterprise Features
            'features' => [
                'ai_conversation_memory' => true,
                'multi_tenant_support' => false,
                'advanced_analytics' => true,
                'custom_integrations' => true,
                'audit_logging' => true,
                'data_export' => true,
                'api_versioning' => true,
                'webhook_support' => true
            ]
        ];
    }

    /**
     * Initialize Redis connection with enterprise features
     */
    private static function initializeRedis(): void
    {
        try {
            self::$redis = new Redis();

            $config = self::$config['redis'];

            if ($config['persistent']) {
                $connected = self::$redis->pconnect(
                    $config['host'],
                    $config['port'],
                    $config['timeout'],
                    'vapeshed_ai_persistent'
                );
            } else {
                $connected = self::$redis->connect(
                    $config['host'],
                    $config['port'],
                    $config['timeout']
                );
            }

            if (!$connected) {
                throw new Exception('Redis connection failed');
            }

            // Authenticate if password is set
            if (!empty($config['password'])) {
                self::$redis->auth($config['password']);
            }

            // Select database
            self::$redis->select($config['database']);

            // Set connection options
            self::$redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_JSON);
            self::$redis->setOption(Redis::OPT_COMPRESSION, Redis::COMPRESSION_LZ4);
            self::$redis->setOption(Redis::OPT_PREFIX, $config['prefix']);

            // Test connection
            if (!self::$redis->ping()) {
                throw new Exception('Redis ping failed');
            }
        } catch (Exception $e) {
            error_log("Redis initialization failed: " . $e->getMessage());
            self::$redis = null;
        }
    }

    /**
     * Load cached configuration from Redis
     */
    private static function loadCachedConfig(): void
    {
        if (!self::$redis) {
            return;
        }

        try {
            $cachedConfig = self::$redis->get('enterprise:config');
            if ($cachedConfig && is_array($cachedConfig)) {
                self::$config = array_merge_recursive(self::$config, $cachedConfig);
            }
        } catch (Exception $e) {
            error_log("Failed to load cached config: " . $e->getMessage());
        }
    }

    /**
     * Validate enterprise configuration requirements
     */
    private static function validateEnterpriseConfig(): void
    {
        $required = [
            'database.primary.host',
            'database.primary.database',
            'database.primary.username',
            'security.encryption_key',
            'security.jwt_secret'
        ];

        foreach ($required as $key) {
            if (empty(self::get($key))) {
                throw new Exception("Required enterprise configuration missing: $key");
            }
        }
    }

    /**
     * Get configuration value with dot notation support
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (!self::$initialized) {
            self::initialize();
        }

        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    /**
     * Set configuration value with Redis caching
     */
    public static function set(string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $config = &self::$config;

        foreach ($keys as $k) {
            if (!isset($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }

        $config = $value;

        // Cache in Redis
        if (self::$redis) {
            try {
                self::$redis->setex('enterprise:config', 3600, self::$config);
            } catch (Exception $e) {
                error_log("Failed to cache config: " . $e->getMessage());
            }
        }
    }

    /**
     * Get Redis connection
     */
    public static function getRedis(): ?Redis
    {
        if (!self::$initialized) {
            self::initialize();
        }

        return self::$redis;
    }

    /**
     * Get Redis connection for specific pool
     */
    public static function getRedisPool(string $pool): ?Redis
    {
        $redis = self::getRedis();
        if (!$redis) {
            return null;
        }

        $poolConfig = self::get("redis.pools.$pool");
        if (!$poolConfig) {
            return $redis;
        }

        try {
            $poolRedis = clone $redis;
            $poolRedis->select($poolConfig['database']);
            $poolRedis->setOption(Redis::OPT_PREFIX, $poolConfig['prefix']);
            return $poolRedis;
        } catch (Exception $e) {
            error_log("Failed to create Redis pool '$pool': " . $e->getMessage());
            return $redis;
        }
    }

    /**
     * Get environment variable with type casting
     */
    private static function getEnv(string $key, string $default = ''): string
    {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }

    /**
     * Parse boolean from environment
     */
    private static function parseBool(string $value): bool
    {
        return in_array(strtolower($value), ['true', '1', 'yes', 'on'], true);
    }

    /**
     * Get system health status
     */
    public static function getSystemHealth(): array
    {
        $health = [
            'status' => 'healthy',
            'checks' => [],
            'timestamp' => time()
        ];

        // Database check
        try {
            $dbConfig = self::get('database.primary');
            $pdo = new PDO(
                "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']}",
                $dbConfig['username'],
                $dbConfig['password'],
                [PDO::ATTR_TIMEOUT => 5]
            );
            $health['checks']['database'] = 'healthy';
        } catch (Exception $e) {
            $health['checks']['database'] = 'unhealthy';
            $health['status'] = 'degraded';
        }

        // Redis check
        if (self::$redis && self::$redis->ping()) {
            $health['checks']['redis'] = 'healthy';
        } else {
            $health['checks']['redis'] = 'unhealthy';
            $health['status'] = 'degraded';
        }

        // Memory check
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = self::parseBytes(ini_get('memory_limit'));
        $memoryPercent = ($memoryUsage / $memoryLimit) * 100;

        $health['checks']['memory'] = [
            'status' => $memoryPercent < 80 ? 'healthy' : 'warning',
            'usage_bytes' => $memoryUsage,
            'limit_bytes' => $memoryLimit,
            'usage_percent' => round($memoryPercent, 2)
        ];

        return $health;
    }

    /**
     * Parse bytes from PHP memory notation
     */
    private static function parseBytes(string $size): int
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);

        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }

        return round($size);
    }
}
