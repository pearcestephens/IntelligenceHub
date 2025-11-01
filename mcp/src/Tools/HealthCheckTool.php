<?php
/**
 * Health Check Tool - Unified system health monitoring
 * Compatible with existing health.php and health_v2.php
 *
 * @package IntelligenceHub\MCP\Tools
 */

declare(strict_types=1);

namespace IntelligenceHub\MCP\Tools;

use IntelligenceHub\MCP\Database\Connection;
use IntelligenceHub\MCP\Cache\CacheManager;
use IntelligenceHub\MCP\Config\Config;

class HealthCheckTool
{
    private CacheManager $cache;
    private array $checks = [];

    public function __construct()
    {
        $this->cache = new CacheManager();
    }

    /**
     * Execute health check
     */
    public function execute(): array
    {
        $startTime = microtime(true);

        $this->checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'disk' => $this->checkDisk(),
            'memory' => $this->checkMemory(),
            'content_index' => $this->checkContentIndex(),
        ];

        $duration = round((microtime(true) - $startTime) * 1000, 2);

        // Determine overall health
        $status = 'healthy';
        $issues = [];

        foreach ($this->checks as $name => $check) {
            if ($check['status'] === 'error') {
                $status = 'unhealthy';
                $issues[] = $name;
            } elseif ($check['status'] === 'warning' && $status === 'healthy') {
                $status = 'degraded';
                $issues[] = $name;
            }
        }

        $healthData = [
            'status' => $status,
            'timestamp' => date('Y-m-d H:i:s'),
            'duration_ms' => $duration,
            'checks' => $this->checks,
            'issues' => $issues,
            'summary' => $this->generateSummary(),
        ];

        // Return in dispatcher-compatible format
        return [
            'success' => true,
            'data' => $healthData,
        ];
    }

    /**
     * Check database health
     */
    private function checkDatabase(): array
    {
        try {
            $pdo = Connection::getInstance();
            $start = microtime(true);
            $pdo->query('SELECT 1');
            $duration = round((microtime(true) - $start) * 1000, 2);

            $stats = Connection::getStats();

            return [
                'status' => 'ok',
                'response_time_ms' => $duration,
                'connected' => true,
                'database' => $stats['database'],
                'threads' => $stats['threads_connected'],
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'connected' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache health
     */
    private function checkCache(): array
    {
        try {
            $stats = $this->cache->getStats();
            $backends = $this->cache->getAvailableBackends();

            // Test cache operation
            $testKey = '_health_check_' . time();
            $this->cache->set($testKey, ['test' => true], 10);
            $value = $this->cache->get($testKey);
            $this->cache->delete($testKey);

            $status = empty($backends) ? 'warning' : 'ok';

            return [
                'status' => $status,
                'backends' => $backends,
                'hit_rate' => $stats['hit_rate'] ?? 0,
                'operational' => $value !== null,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'warning',
                'error' => $e->getMessage(),
                'backends' => [],
            ];
        }
    }

    /**
     * Check disk space
     */
    private function checkDisk(): array
    {
        $basePath = dirname(__DIR__, 2);
        $freeSpace = disk_free_space($basePath);
        $totalSpace = disk_total_space($basePath);
        $usedPercent = round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2);

        $status = 'ok';
        if ($usedPercent > 90) {
            $status = 'error';
        } elseif ($usedPercent > 80) {
            $status = 'warning';
        }

        return [
            'status' => $status,
            'free_mb' => round($freeSpace / 1024 / 1024, 2),
            'total_mb' => round($totalSpace / 1024 / 1024, 2),
            'used_percent' => $usedPercent,
        ];
    }

    /**
     * Check memory usage
     */
    private function checkMemory(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');

        // Convert memory limit to bytes
        $limitBytes = $this->convertToBytes($memoryLimit);
        $usedPercent = $limitBytes > 0 ? round(($memoryUsage / $limitBytes) * 100, 2) : 0;

        $status = 'ok';
        if ($usedPercent > 90) {
            $status = 'warning';
        }

        return [
            'status' => $status,
            'used_mb' => round($memoryUsage / 1024 / 1024, 2),
            'limit' => $memoryLimit,
            'used_percent' => $usedPercent,
        ];
    }

    /**
     * Check content index health
     */
    private function checkContentIndex(): array
    {
        try {
            $pdo = Connection::getInstance();

            // Count indexed files
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM intelligence_content");
            $total = $stmt->fetch()['count'];

            // Count by unit
            $stmt = $pdo->query("
                SELECT unit_id, COUNT(*) as count
                FROM intelligence_content
                GROUP BY unit_id
            ");
            $byUnit = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

            // Get last update
            $stmt = $pdo->query("
                SELECT MAX(updated_at) as last_update
                FROM intelligence_content
            ");
            $lastUpdate = $stmt->fetch()['last_update'];

            $status = $total > 0 ? 'ok' : 'warning';

            return [
                'status' => $status,
                'total_files' => (int)$total,
                'by_unit' => $byUnit,
                'last_update' => $lastUpdate,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate summary message
     */
    private function generateSummary(): string
    {
        $healthy = 0;
        $warnings = 0;
        $errors = 0;

        foreach ($this->checks as $check) {
            switch ($check['status']) {
                case 'ok':
                    $healthy++;
                    break;
                case 'warning':
                    $warnings++;
                    break;
                case 'error':
                    $errors++;
                    break;
            }
        }

        $total = count($this->checks);

        if ($errors > 0) {
            return "{$errors} critical issue(s), {$warnings} warning(s), {$healthy}/{$total} checks healthy";
        } elseif ($warnings > 0) {
            return "{$warnings} warning(s), {$healthy}/{$total} checks healthy";
        } else {
            return "All {$total} checks healthy";
        }
    }

    /**
     * Convert memory limit string to bytes
     */
    private function convertToBytes(string $value): int
    {
        $value = trim($value);
        $unit = strtolower($value[strlen($value) - 1]);
        $value = (int)$value;

        switch ($unit) {
            case 'g':
                $value *= 1024;
                // fallthrough
            case 'm':
                $value *= 1024;
                // fallthrough
            case 'k':
                $value *= 1024;
        }

        return $value;
    }
}
