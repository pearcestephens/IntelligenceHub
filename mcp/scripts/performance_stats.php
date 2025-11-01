<?php
/**
 * MCP Performance Statistics Dashboard
 *
 * Displays real-time performance metrics for the MCP Intelligence Hub
 *
 * Usage: php scripts/performance_stats.php
 *
 * @package IntelligenceHub\MCP
 * @version 1.0.0
 */

require_once __DIR__ . '/../vendor/autoload.php';

use IntelligenceHub\MCP\Cache\CacheManager;

// Initialize cache with production configuration
$cache = new CacheManager([
    'redis' => [
        'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
        'port' => (int)(getenv('REDIS_PORT') ?: 6379),
        'timeout' => (float)(getenv('REDIS_TIMEOUT') ?: 2.5),
    ],
    'file' => [
        'path' => __DIR__ . '/../cache',
    ],
]);

echo "=================================================================\n";
echo "  MCP INTELLIGENCE HUB - PERFORMANCE STATISTICS\n";
echo "=================================================================\n\n";

// Cache backend status
echo "CACHE BACKENDS:\n";
$backends = $cache->getAvailableBackends();
foreach ($backends as $name => $available) {
    $status = $available ? '✅ Available' : '❌ Unavailable';
    echo "  - " . ucfirst($name) . ": $status\n";
}
echo "\n";

// Cache statistics
echo "CACHE STATISTICS:\n";
$stats = $cache->getStats();
echo "  Hits:    " . number_format($stats['hits']) . "\n";
echo "  Misses:  " . number_format($stats['misses']) . "\n";
echo "  Sets:    " . number_format($stats['sets']) . "\n";
echo "  Deletes: " . number_format($stats['deletes']) . "\n";

$total = $stats['hits'] + $stats['misses'];
if ($total > 0) {
    $hitRate = ($stats['hits'] / $total * 100);
    $missRate = ($stats['misses'] / $total * 100);
    echo "  Hit Rate:  " . number_format($hitRate, 2) . "%\n";
    echo "  Miss Rate: " . number_format($missRate, 2) . "%\n";

    // Performance indicator
    if ($hitRate >= 90) {
        echo "  Status: 🟢 Excellent\n";
    } elseif ($hitRate >= 80) {
        echo "  Status: 🟡 Good\n";
    } elseif ($hitRate >= 70) {
        echo "  Status: 🟠 Fair\n";
    } else {
        echo "  Status: 🔴 Needs Attention\n";
    }
} else {
    echo "  Hit Rate: N/A (no operations recorded)\n";
    echo "  Status: 🆕 New instance\n";
}
echo "\n";

// Redis-specific info (if available)
if ($backends['redis'] ?? false) {
    try {
        $redisHost = getenv('REDIS_HOST') ?: '127.0.0.1';
        $redisPort = (int)(getenv('REDIS_PORT') ?: 6379);
        $redisTimeout = (float)(getenv('REDIS_TIMEOUT') ?: 2.5);

        $redis = new Redis();
        $redis->connect($redisHost, $redisPort, $redisTimeout);

        // Get Redis memory info
        $info = $redis->info('memory');
        echo "REDIS MEMORY:\n";
        echo "  Used Memory: " . ($info['used_memory_human'] ?? 'N/A') . "\n";
        echo "  Peak Memory: " . ($info['used_memory_peak_human'] ?? 'N/A') . "\n";
        echo "  Fragmentation: " . number_format($info['mem_fragmentation_ratio'] ?? 0, 2) . "\n";

        // Get Redis stats
        $statsInfo = $redis->info('stats');
        echo "  Total Connections: " . number_format($statsInfo['total_connections_received'] ?? 0) . "\n";
        echo "  Total Commands: " . number_format($statsInfo['total_commands_processed'] ?? 0) . "\n";
        echo "\n";

        // Get keyspace info
        $keyspaceInfo = $redis->info('keyspace');
        if (isset($keyspaceInfo['db0'])) {
            preg_match('/keys=(\d+)/', $keyspaceInfo['db0'], $matches);
            $keyCount = $matches[1] ?? 0;
            echo "  Cached Keys: " . number_format($keyCount) . "\n";
        }
        echo "\n";

        $redis->close();
    } catch (Exception $e) {
        echo "REDIS INFO:\n";
        echo "  ⚠️  Redis info unavailable: " . $e->getMessage() . "\n\n";
    }
}

// Database statistics
try {
    $config = require __DIR__ . '/../config/database.php';
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "DATABASE STATISTICS:\n";

    // Indexed files count
    $stmt = $pdo->query('SELECT COUNT(*) FROM intelligence_content WHERE is_active = 1');
    $totalFiles = $stmt->fetchColumn();
    echo "  Indexed Files: " . number_format($totalFiles) . "\n";

    // Content text entries count
    $stmt = $pdo->query('SELECT COUNT(*) FROM intelligence_content_text');
    $totalText = $stmt->fetchColumn();
    echo "  Text Entries: " . number_format($totalText) . "\n";

    // Recent indexing activity (last 24 hours)
    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM intelligence_content
        WHERE last_analyzed >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
    $recentFiles = $stmt->fetchColumn();
    echo "  Indexed (24h): " . number_format($recentFiles) . "\n";

    // Average file size
    if ($totalFiles > 0) {
        $stmt = $pdo->query('SELECT AVG(file_size) FROM intelligence_content WHERE file_size > 0');
        $avgSize = $stmt->fetchColumn();
        echo "  Avg File Size: " . number_format($avgSize / 1024, 2) . " KB\n";
    }

    // Database size
    $stmt = $pdo->query("
        SELECT
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size_mb
        FROM information_schema.tables
        WHERE table_schema = '{$config['database']}'
        AND table_name IN ('intelligence_content', 'intelligence_content_text', 'intelligence_metrics')
    ");
    $dbSize = $stmt->fetchColumn();
    echo "  Database Size: " . $dbSize . " MB\n";
    echo "\n";

} catch (PDOException $e) {
    echo "DATABASE STATISTICS:\n";
    echo "  ⚠️  Database stats unavailable: " . $e->getMessage() . "\n\n";
}

// File system cache info
$cachePath = __DIR__ . '/../cache';
if (is_dir($cachePath)) {
    echo "FILE CACHE:\n";

    // Count cache files
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($cachePath, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    $fileCount = 0;
    $totalSize = 0;
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $fileCount++;
            $totalSize += $file->getSize();
        }
    }

    echo "  Cached Files: " . number_format($fileCount) . "\n";
    echo "  Total Size: " . number_format($totalSize / 1024 / 1024, 2) . " MB\n";

    // Oldest file
    if ($fileCount > 0) {
        $oldestFile = null;
        $oldestTime = time();
        foreach (new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($cachePath, RecursiveDirectoryIterator::SKIP_DOTS)
        ) as $file) {
            if ($file->isFile() && $file->getMTime() < $oldestTime) {
                $oldestTime = $file->getMTime();
            }
        }
        $age = time() - $oldestTime;
        $days = floor($age / 86400);
        $hours = floor(($age % 86400) / 3600);
        echo "  Oldest Entry: {$days}d {$hours}h ago\n";
    }
    echo "\n";
}

// System info
echo "SYSTEM INFO:\n";
echo "  PHP Version: " . phpversion() . "\n";
echo "  Memory Limit: " . ini_get('memory_limit') . "\n";
echo "  Memory Usage: " . number_format(memory_get_usage(true) / 1024 / 1024, 2) . " MB\n";
echo "  Peak Memory: " . number_format(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB\n";
echo "\n";

echo "=================================================================\n";
echo "Generated: " . date('Y-m-d H:i:s') . " (" . date_default_timezone_get() . ")\n";
echo "=================================================================\n";
