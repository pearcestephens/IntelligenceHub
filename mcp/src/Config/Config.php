<?php
/**
 * Configuration Manager
 *
 * @package IntelligenceHub\MCP\Config
 */

declare(strict_types=1);

namespace IntelligenceHub\MCP\Config;

class Config
{
    private static ?array $config = null;
    private static string $basePath;

    /**
     * Initialize configuration
     */
    public static function init(string $basePath): void
    {
        self::$basePath = $basePath;
        self::load();
    }

    /**
     * Load configuration files
     */
    private static function load(): void
    {
        self::$config = [
            'database' => [
                'host' => '127.0.0.1',
                'name' => 'hdgwrzntwa',
                'user' => 'hdgwrzntwa',
                'pass' => 'bFUdRjh4Jx',
                'charset' => 'utf8mb4',
            ],
            'cache' => [
                'enable_redis' => true,
                'enable_apcu' => true,
                'enable_file' => true,
                'redis_host' => '127.0.0.1',
                'redis_port' => 6379,
                'ttl' => 3600,
            ],
            'search' => [
                'min_relevance' => 0.1,
                'max_results' => 50,
                'enable_synonyms' => true,
            ],
            'satellites' => self::loadSatellites(),
            'categories' => self::loadCategories(),
        ];
    }

    /**
     * Get configuration value
     */
    public static function get(string $key, $default = null)
    {
        if (self::$config === null) {
            throw new \RuntimeException('Configuration not initialized. Call Config::init() first.');
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
     * Load satellites configuration
     */
    private static function loadSatellites(): array
    {
        return [
            1 => [
                'name' => 'Intelligence Hub',
                'url' => 'https://gpt.ecigdis.co.nz/api/scan_and_return.php',
                'auth' => 'bFUdRjh4Jx',
                'active' => true,
            ],
            2 => [
                'name' => 'CIS',
                'url' => 'https://staff.vapeshed.co.nz/api/scan_and_return.php',
                'auth' => 'bFUdRjh4Jx',
                'active' => true,
            ],
            3 => [
                'name' => 'VapeShed',
                'url' => 'https://vapeshed.co.nz/api/scan_and_return.php',
                'auth' => 'bFUdRjh4Jx',
                'active' => true,
            ],
            4 => [
                'name' => 'Wholesale',
                'url' => 'https://wholesale.ecigdis.co.nz/api/scan_and_return.php',
                'auth' => 'bFUdRjh4Jx',
                'active' => true,
            ],
        ];
    }

    /**
     * Load categories configuration
     */
    private static function loadCategories(): array
    {
        return [
            'System Architecture' => ['priority' => 2.0, 'parent' => null],
            'Database & Models' => ['priority' => 1.8, 'parent' => null],
            'API & Endpoints' => ['priority' => 1.7, 'parent' => null],
            'UI Components' => ['priority' => 1.5, 'parent' => null],
            'Authentication' => ['priority' => 1.9, 'parent' => 'System Architecture'],
            'Inventory Management' => ['priority' => 1.6, 'parent' => null],
            'Sales & Orders' => ['priority' => 1.6, 'parent' => null],
            'Reporting' => ['priority' => 1.4, 'parent' => null],
            'Webhooks & Integration' => ['priority' => 1.5, 'parent' => null],
        ];
    }
}
