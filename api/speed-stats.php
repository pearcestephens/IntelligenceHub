<?php
/**
 * Speed Stats API Endpoint
 * Returns real-time performance metrics
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../classes/RedisCache.php';
require_once __DIR__ . '/../classes/AsyncQueue.php';

$stats = [
    'redis' => [
        'status' => 'offline',
        'hitrate' => 0,
        'keys' => 0,
        'commands' => 0
    ],
    'queue' => [
        'pending' => 0,
        'processing' => 0,
        'count' => 0
    ],
    'system' => [
        'load' => '0.00',
        'memory' => '0 GB',
        'disk' => '0%'
    ],
    'php' => [
        'slow' => 0,
        'processes' => 0
    ]
];

// Redis stats
try {
    if (RedisCache::isEnabled()) {
        $redisStats = RedisCache::getStats();
        $stats['redis']['status'] = 'online';
        $stats['redis']['keys'] = $redisStats['total_keys'] ?? 0;

        $hits = $redisStats['redis_hits'] ?? 0;
        $misses = $redisStats['redis_misses'] ?? 0;
        $total = $hits + $misses;
        $stats['redis']['hitrate'] = $total > 0 ? round(($hits / $total) * 100, 1) : 0;
    }
} catch (Exception $e) {
    error_log('Redis stats error: ' . $e->getMessage());
}

// Queue stats
try {
    $queues = ['ai-requests', 'email', 'test'];
    foreach ($queues as $queue) {
        $qStats = AsyncQueue::getStats($queue);
        $stats['queue']['pending'] += $qStats['pending'] ?? 0;
        $stats['queue']['processing'] += $qStats['processing'] ?? 0;
        if (($qStats['total'] ?? 0) > 0) {
            $stats['queue']['count']++;
        }
    }
} catch (Exception $e) {
    error_log('Queue stats error: ' . $e->getMessage());
}

// System stats
$load = sys_getloadavg();
$stats['system']['load'] = number_format($load[0], 2);

// Memory
$meminfo = @file_get_contents('/proc/meminfo');
if ($meminfo) {
    preg_match('/MemTotal:\s+(\d+)/', $meminfo, $total);
    preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $available);
    if (isset($total[1]) && isset($available[1])) {
        $used = ($total[1] - $available[1]) / 1024 / 1024;
        $stats['system']['memory'] = number_format($used, 1) . ' GB';
    }
}

// PHP-FPM slow requests
$slowLog = '/home/129337.cloudwaysapps.com/hdgwrzntwa/logs/php-app.slow.log';
if (file_exists($slowLog)) {
    $lines = @file($slowLog);
    if ($lines) {
        // Count requests in last hour
        $oneHourAgo = time() - 3600;
        $count = 0;
        foreach ($lines as $line) {
            if (preg_match('/\[(.*?)\]/', $line, $matches)) {
                $timestamp = strtotime($matches[1]);
                if ($timestamp > $oneHourAgo) {
                    $count++;
                }
            }
        }
        $stats['php']['slow'] = $count;
    }
}

echo json_encode($stats, JSON_PRETTY_PRINT);
