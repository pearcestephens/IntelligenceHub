<?php

/**
 * Enterprise Performance Monitor
 * Real-time performance analytics with predictive optimization
 *
 * @author Pearce Stephens - Ecigdis Limited
 * @package VapeShed Enterprise AI Platform
 * @version 2.0.0 - Performance Intelligence
 */

declare(strict_types=1);

namespace App\Intelligence;

use App\RedisClient;
use App\DB;
use App\Logger;
use Exception;

class EnterprisePerformanceMonitor
{
    private const PERFORMANCE_CACHE_TTL = 300; // 5 minutes
    private const ALERT_THRESHOLDS = [
        'response_time' => 2.0,     // seconds
        'error_rate' => 0.05,       // 5%
        'memory_usage' => 0.8,      // 80%
        'cpu_usage' => 0.9,         // 90%
        'disk_usage' => 0.85        // 85%
    ];

    /**
     * Real-time performance analytics dashboard
     */
    public static function getPerformanceAnalytics(array $options = []): array
    {
        $cacheKey = "performance_analytics:" . md5(serialize($options));
        $cached = RedisClient::get($cacheKey);

        if ($cached && time() - $cached['timestamp'] < self::PERFORMANCE_CACHE_TTL) {
            return $cached;
        }

        try {
            $analytics = [
                'system_health' => self::getSystemHealth(),
                'application_performance' => self::getApplicationPerformance(),
                'database_performance' => self::getDatabasePerformance(),
                'user_experience' => self::getUserExperienceMetrics(),
                'business_metrics' => self::getBusinessMetrics(),
                'predictive_insights' => self::getPredictiveInsights(),
                'error_budget' => self::errorBudgetStatus(),
                'alerts' => self::generateAlerts(),
                'recommendations' => self::generateRecommendations()
            ];

            // Calculate overall health score
            $analytics['overall_health_score'] = self::calculateOverallHealthScore($analytics);

            // Add trend analysis
            $analytics['trends'] = self::analyzeTrends($analytics);

            // Performance optimization suggestions
            $analytics['optimizations'] = self::generateOptimizationSuggestions($analytics);

            $result = [
                'analytics' => $analytics,
                'timestamp' => time(),
                'cache_ttl' => self::PERFORMANCE_CACHE_TTL,
                'alert_count' => count($analytics['alerts']),
                'health_status' => self::getHealthStatus($analytics['overall_health_score'])
            ];

            RedisClient::set($cacheKey, $result, self::PERFORMANCE_CACHE_TTL);

            Logger::info('Performance analytics generated', [
                'health_score' => $analytics['overall_health_score'],
                'alert_count' => count($analytics['alerts'])
            ]);

            return $result;
        } catch (Exception $e) {
            Logger::error('Performance analytics failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'error' => 'Performance analytics failed',
                'timestamp' => time()
            ];
        }
    }

    /**
     * Record performance metric with intelligent aggregation
     */
    public static function recordMetric(string $metric, float $value, array $tags = []): bool
    {
        try {
            $timestamp = time();
            $metricKey = "metric:{$metric}";

            // Route discovery (lightweight uniqueness guard)
            if (isset($tags['route'])) {
                $route = (string)$tags['route'];
                $seenKey = 'route:seen:' . $route;
                if (!RedisClient::get($seenKey)) {
                    RedisClient::set($seenKey, 1, 3600); // 1h TTL to reduce churn
                    RedisClient::listPush('routes:observed', $route);
                    RedisClient::listTrim('routes:observed', 0, 199);
                }
            }

            // Store raw metric
            $metricData = [
                'value' => $value,
                'timestamp' => $timestamp,
                'tags' => $tags,
                'hour' => date('Y-m-d-H', $timestamp),
                'day' => date('Y-m-d', $timestamp)
            ];

            // Add to time series
            RedisClient::zadd("{$metricKey}:timeseries", $timestamp, json_encode($metricData));

            // Update aggregations
            self::updateMetricAggregations($metric, $value, $timestamp, $tags);

            // Check for alerts
            self::checkMetricAlerts($metric, $value, $tags);

            // Cleanup old data (keep 30 days)
            $cutoff = $timestamp - (30 * 24 * 3600);
            RedisClient::zremrangebyscore("{$metricKey}:timeseries", 0, $cutoff);

            return true;
        } catch (Exception $e) {
            Logger::error('Failed to record metric', [
                'metric' => $metric,
                'value' => $value,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Sophisticated anomaly detection
     */
    public static function detectAnomalies(array $options = []): array
    {
        $cacheKey = "anomaly_detection:" . md5(serialize($options));
        $cached = RedisClient::get($cacheKey);

        if ($cached && time() - $cached['timestamp'] < 600) { // 10 minutes
            return $cached;
        }

        try {
            $anomalies = [];
            $metrics = $options['metrics'] ?? ['response_time', 'error_rate', 'cpu_usage', 'memory_usage'];

            foreach ($metrics as $metric) {
                $metricAnomalies = self::detectMetricAnomalies($metric, $options);
                if (!empty($metricAnomalies)) {
                    $anomalies[$metric] = $metricAnomalies;
                }
            }

            // Apply machine learning anomaly detection
            $mlAnomalies = self::applyMLAnomalyDetection($anomalies);

            // Calculate anomaly severity
            foreach ($anomalies as $metric => &$anomalyList) {
                foreach ($anomalyList as &$anomaly) {
                    $anomaly['severity'] = self::calculateAnomalySeverity($anomaly);
                    $anomaly['impact_assessment'] = self::assessAnomalyImpact($anomaly);
                }
            }

            $result = [
                'anomalies' => $anomalies,
                'ml_anomalies' => $mlAnomalies,
                'summary' => self::generateAnomalySummary($anomalies),
                'recommendations' => self::generateAnomalyRecommendations($anomalies),
                'timestamp' => time()
            ];

            RedisClient::set($cacheKey, $result, 600);

            return $result;
        } catch (Exception $e) {
            Logger::error('Anomaly detection failed', [
                'error' => $e->getMessage()
            ]);

            return ['error' => 'Anomaly detection failed'];
        }
    }

    /**
     * Advanced capacity planning
     */
    public static function performCapacityPlanning(array $options = []): array
    {
        try {
            $planningHorizon = $options['horizon_days'] ?? 90;
            $currentMetrics = self::getCurrentCapacityMetrics();
            $historicalTrends = self::getHistoricalTrends($planningHorizon);

            // Predict future capacity needs
            $predictions = [
                'cpu_capacity' => self::predictCapacityNeeds('cpu_usage', $historicalTrends, $planningHorizon),
                'memory_capacity' => self::predictCapacityNeeds('memory_usage', $historicalTrends, $planningHorizon),
                'storage_capacity' => self::predictCapacityNeeds('disk_usage', $historicalTrends, $planningHorizon),
                'network_capacity' => self::predictCapacityNeeds('network_usage', $historicalTrends, $planningHorizon),
                'database_capacity' => self::predictDatabaseCapacity($historicalTrends, $planningHorizon)
            ];

            // Generate capacity recommendations
            $recommendations = self::generateCapacityRecommendations($currentMetrics, $predictions);

            // Calculate cost implications
            $costAnalysis = self::calculateCapacityCosts($predictions, $recommendations);

            return [
                'current_metrics' => $currentMetrics,
                'predictions' => $predictions,
                'recommendations' => $recommendations,
                'cost_analysis' => $costAnalysis,
                'planning_horizon' => $planningHorizon,
                'confidence_scores' => self::calculatePredictionConfidence($predictions),
                'timestamp' => time()
            ];
        } catch (Exception $e) {
            Logger::error('Capacity planning failed', [
                'error' => $e->getMessage()
            ]);

            return ['error' => 'Capacity planning failed'];
        }
    }

    /**
     * Snapshot current capacity related metrics (lightweight wrappers around existing probes)
     */
    private static function getCurrentCapacityMetrics(): array
    {
        return [
            'cpu_usage' => self::getCpuUsage(),
            'memory_usage' => self::getMemoryUsage(),
            'disk_usage' => self::getDiskUsage(),
            'network_usage' => self::getNetworkUsage(),
            'db_connections' => self::getDatabaseConnectionUsage(),
            'buffer_pool_usage' => self::getBufferPoolUsage()
        ];
    }

    /**
     * Retrieve historical metric trends for capacity forecasting (simplified aggregation)
     */
    private static function getHistoricalTrends(int $days): array
    {
        $metrics = ['cpu_usage','memory_usage','disk_usage','network_usage'];
        $out = [];
        $now = time();
        $window = $days * 86400;
        foreach ($metrics as $m) {
            $seriesKey = "metric:$m:timeseries";
            // Fetch last N points (cap at 1000 for performance)
            $raw = RedisClient::zrevrange($seriesKey, 0, 999);
            $points = [];
            foreach ($raw as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $ts = $row['timestamp'] ?? null;
                $val = $row['value'] ?? null;
                if ($ts && $val !== null && ($now - $ts) <= $window) {
                    $points[] = ['t' => $ts,'v' => (float)$val];
                }
            }
            $out[$m] = $points;
        }
        return $out;
    }

    /**
     * Simple linear projection based on last N points (fallback heuristic).
     */
    private static function predictCapacityNeeds(string $metric, array $historical, int $horizonDays): array
    {
        $points = $historical[$metric] ?? [];
        $n = count($points);
        if ($n < 3) {
            $current = $points[0]['v'] ?? 0.0;
            return [
                'metric' => $metric,
                'projected' => $current,
                'horizon_days' => $horizonDays,
                'growth_rate' => 0.0,
                'confidence' => 0.2
            ];
        }
        // Use first and last for rough slope
        usort($points, fn($a, $b)=>$a['t'] <=> $b['t']);
        $first = $points[0];
        $last = $points[$n - 1];
        $dt = max(1, $last['t'] - $first['t']);
        $slopePerSec = ($last['v'] - $first['v']) / $dt;
        $projection = $last['v'] + ($slopePerSec * $horizonDays * 86400);
        return [
            'metric' => $metric,
            'projected' => max(0, $projection),
            'horizon_days' => $horizonDays,
            'growth_rate' => $slopePerSec * 86400, // per day
            'confidence' => min(0.95, 0.3 + min(0.4, $n / 200))
        ];
    }

    private static function predictDatabaseCapacity(array $historical, int $horizonDays): array
    {
        // Placeholder: leverage connection usage & buffer pool usage metrics if available
        $conn = self::getDatabaseConnectionUsage();
        $buf = self::getBufferPoolUsage();
        $projectedConn = min(1.0, $conn + 0.05 * ($horizonDays / 30));
        $projectedBuf = min(1.0, $buf + 0.03 * ($horizonDays / 30));
        return [
            'connections_current' => $conn,
            'connections_projected' => $projectedConn,
            'buffer_pool_current' => $buf,
            'buffer_pool_projected' => $projectedBuf,
            'horizon_days' => $horizonDays,
            'confidence' => 0.4
        ];
    }

    /**
     * Get system health metrics
     */
    private static function getSystemHealth(): array
    {
        return [
            'cpu_usage' => self::getCPUUsage(),
            'memory_usage' => self::getMemoryUsage(),
            'disk_usage' => self::getDiskUsage(),
            'network_usage' => self::getNetworkUsage(),
            'load_average' => self::getLoadAverage(),
            'uptime' => self::getSystemUptime(),
            'process_count' => self::getProcessCount(),
            'temperature' => self::getSystemTemperature()
        ];
    }

    /**
     * Get application performance metrics
     */
    private static function getApplicationPerformance(): array
    {
        return [
            'response_time' => self::getAverageResponseTime(),
            'throughput' => self::getCurrentThroughput(),
            'error_rate' => self::getErrorRate(),
            'active_sessions' => self::getActiveSessions(),
            'cache_hit_rate' => self::getCacheHitRate(),
            'api_performance' => self::getAPIPerformance(),
            'page_load_times' => self::getPageLoadTimes(),
            'user_satisfaction' => self::getUserSatisfactionScore()
        ];
    }

    /**
     * Get database performance metrics
     */
    private static function getDatabasePerformance(): array
    {
        try {
            // Get basic database stats
                $pdo = DB::connection();
                $queries = (int)($pdo->query("SHOW STATUS LIKE 'Queries'")->fetch()['Value'] ?? 0);
                $uptime  = (int)($pdo->query("SHOW STATUS LIKE 'Uptime'")->fetch()['Value'] ?? 1);
                $connections = (int)($pdo->query("SHOW STATUS LIKE 'Threads_connected'")->fetch()['Value'] ?? 0);
                $slowQueries = (int)($pdo->query("SHOW STATUS LIKE 'Slow_queries'")->fetch()['Value'] ?? 0);

            return [
                'queries_per_second' => $uptime > 0 ? $queries / $uptime : 0,
                'active_connections' => intval($connections),
                'slow_queries' => intval($slowQueries),
                'connection_usage' => self::getDatabaseConnectionUsage(),
                'buffer_pool_usage' => self::getBufferPoolUsage(),
                'lock_waits' => self::getLockWaits(),
                'deadlocks' => self::getDeadlockCount(),
                'replication_lag' => self::getReplicationLag()
            ];
        } catch (Exception $e) {
            Logger::error('Failed to get database performance', [
                'error' => $e->getMessage()
            ]);

            return [
                'error' => 'Database metrics unavailable',
                'queries_per_second' => 0,
                'active_connections' => 0
            ];
        }
    }

    /**
     * Get user experience metrics
     */
    private static function getUserExperienceMetrics(): array
    {
        return [
            'page_load_time' => self::getAveragePageLoadTime(),
            'time_to_first_byte' => self::getTimeToFirstByte(),
            'largest_contentful_paint' => self::getLargestContentfulPaint(),
            'cumulative_layout_shift' => self::getCumulativeLayoutShift(),
            'first_input_delay' => self::getFirstInputDelay(),
            'bounce_rate' => self::getBounceRate(),
            'conversion_rate' => self::getConversionRate(),
            'user_engagement' => self::getUserEngagementScore()
        ];
    }

    /**
     * Get business metrics
     */
    private static function getBusinessMetrics(): array
    {
        try {
            // Sales metrics
            $pdo = DB::connection();
            $salesStmt = $pdo->query("SELECT COUNT(*) as count, SUM(total_price) as revenue FROM vend_sales WHERE DATE(sale_date) = CURDATE() AND status != 'CANCELLED'");
            $todaySales = $salesStmt->fetch() ?: ['count' => 0, 'revenue' => 0];

            // Active outlets
            $activeStmt = $pdo->query("SELECT COUNT(DISTINCT outlet_id) as count FROM vend_sales WHERE sale_date >= DATE_SUB(NOW(), INTERVAL 1 DAY)");
            $activeOutlets = $activeStmt->fetch() ?: ['count' => 0];

            return [
                'daily_sales_count' => intval($todaySales['count'] ?? 0),
                'daily_revenue' => floatval($todaySales['revenue'] ?? 0),
                'active_outlets' => intval($activeOutlets['count'] ?? 0),
                'inventory_turnover' => self::getInventoryTurnover(),
                'customer_acquisition_rate' => self::getCustomerAcquisitionRate(),
                'customer_retention_rate' => self::getCustomerRetentionRate(),
                'average_order_value' => self::getAverageOrderValue(),
                'profit_margin' => self::getProfitMargin()
            ];
        } catch (Exception $e) {
            Logger::error('Failed to get business metrics', [
                'error' => $e->getMessage()
            ]);

            return ['error' => 'Business metrics unavailable'];
        }
    }

    /**
     * Update metric aggregations
     */
    private static function updateMetricAggregations(string $metric, float $value, int $timestamp, array $tags): void
    {
        $hour = date('Y-m-d-H', $timestamp);
        $day = date('Y-m-d', $timestamp);

        // Hourly aggregations
        RedisClient::zadd("agg:hourly:{$metric}", $timestamp, $value);
        RedisClient::incr("agg:hourly:{$metric}:count");

        // Daily aggregations
        RedisClient::zadd("agg:daily:{$metric}", $timestamp, $value);
        RedisClient::incr("agg:daily:{$metric}:count");

        // Calculate running averages
        $hourlyValues = RedisClient::zrange("agg:hourly:{$metric}", -60, -1); // Last 60 values
        if (!empty($hourlyValues)) {
            $avgValue = array_sum($hourlyValues) / count($hourlyValues);
            RedisClient::set("avg:hourly:{$metric}", $avgValue);
        }
    }

    /**
     * Check metric alerts
     */
    private static function checkMetricAlerts(string $metric, float $value, array $tags): void
    {
        if (!isset(self::ALERT_THRESHOLDS[$metric])) {
            return;
        }

        $threshold = self::ALERT_THRESHOLDS[$metric];

        if ($value > $threshold) {
            $alertData = [
                'metric' => $metric,
                'value' => $value,
                'threshold' => $threshold,
                'severity' => self::calculateAlertSeverity($value, $threshold),
                'tags' => $tags,
                'timestamp' => time()
            ];

            // Store alert
            $alertKey = "alert:" . md5($metric . serialize($tags));
            RedisClient::set($alertKey, $alertData, 3600); // 1 hour TTL

            // Add to alert queue
            RedisClient::listPush("alert_queue", json_encode($alertData));

            Logger::warning('Performance alert triggered', $alertData);
        }
    }

    /**
     * Calculate overall health score
     */
    private static function calculateOverallHealthScore(array $analytics): float
    {
        $scores = [];

        // System health score (weight: 30%)
        $systemHealth = $analytics['system_health'];
        $systemScore = (
            (1 - ($systemHealth['cpu_usage'] ?? 0)) * 0.3 +
            (1 - ($systemHealth['memory_usage'] ?? 0)) * 0.3 +
            (1 - ($systemHealth['disk_usage'] ?? 0)) * 0.2 +
            min(1, ($systemHealth['uptime'] ?? 0) / (24 * 3600)) * 0.2
        );
        $scores[] = $systemScore * 0.3;

        // Application performance score (weight: 40%)
        $appPerf = $analytics['application_performance'];
        $appScore = (
            min(1, 3.0 / max(0.1, $appPerf['response_time'] ?? 1)) * 0.4 +
            (1 - ($appPerf['error_rate'] ?? 0)) * 0.3 +
            min(1, ($appPerf['cache_hit_rate'] ?? 0)) * 0.3
        );
        $scores[] = $appScore * 0.4;

        // Database performance score (weight: 20%)
        $dbPerf = $analytics['database_performance'];
        $dbScore = (
            min(1, 100 / max(1, $dbPerf['slow_queries'] ?? 1)) * 0.5 +
            min(1, ($dbPerf['connection_usage'] ?? 0.5)) * 0.5
        );
        $scores[] = $dbScore * 0.2;

        // User experience score (weight: 10%)
        $uxMetrics = $analytics['user_experience'];
        $uxScore = (
            min(1, 3.0 / max(0.1, $uxMetrics['page_load_time'] ?? 1)) * 0.6 +
            (1 - ($uxMetrics['bounce_rate'] ?? 0.5)) * 0.4
        );
        $scores[] = $uxScore * 0.1;

        return max(0, min(1, array_sum($scores)));
    }

    /**
     * Placeholder methods for comprehensive monitoring
     */
    private static function getCPUUsage(): float
    {
        // Cache for a few seconds to avoid excessive /proc reads
        static $cache = null;
        $now = microtime(true);
        if ($cache && ($now - $cache['ts']) < 5) {
            return $cache['value'];
        }
        try {
            $stat1 = @file('/proc/stat');
            if (!$stat1) {
                throw new \RuntimeException('stat unreadable');
            }
            $cpuLine1 = explode(' ', preg_replace('/ +/', ' ', trim($stat1[0])));
            // cpu user nice system idle iowait irq softirq steal guest guest_nice
            $vals1 = array_slice($cpuLine1, 1);
            $total1 = array_sum($vals1);
            $idle1 = (int)$vals1[3];
            usleep(50000); // 50ms sample window
            $stat2 = @file('/proc/stat');
            if (!$stat2) {
                throw new \RuntimeException('stat2 unreadable');
            }
            $cpuLine2 = explode(' ', preg_replace('/ +/', ' ', trim($stat2[0])));
            $vals2 = array_slice($cpuLine2, 1);
            $total2 = array_sum($vals2);
            $idle2 = (int)$vals2[3];
            $totalDiff = max(1, $total2 - $total1);
            $idleDiff = $idle2 - $idle1;
            $usage = 1 - ($idleDiff / $totalDiff);
            $cache = ['value' => max(0, min(1, $usage)), 'ts' => $now];
            return $cache['value'];
        } catch (\Throwable $e) {
            // Fallback: derive from 1-min load average / CPU cores
            $load = sys_getloadavg();
            $cores = (int)trim((string)@shell_exec('grep -c ^processor /proc/cpuinfo'));
            if ($cores <= 0) {
                $cores = 1;
            }
            $approx = min(1, ($load[0] ?? 0) / $cores);
            $cache = ['value' => $approx, 'ts' => $now];
            return $approx;
        }
    }

    private static function getMemoryUsage(): float
    {
        static $cache = null;
        $now = time();
        if ($cache && ($now - $cache['ts']) < 10) {
            return $cache['value'];
        }
        $meminfo = @file('/proc/meminfo');
        $data = [];
        if ($meminfo) {
            foreach ($meminfo as $line) {
                if (preg_match('/^(\w+):\s+(\d+)/', $line, $m)) {
                    $data[$m[1]] = (int)$m[2] * 1024; // bytes
                }
            }
        }
        if (!isset($data['MemTotal'])) {
            return 0.0;
        }
        // Prefer MemAvailable if present
        if (isset($data['MemAvailable'])) {
            $used = $data['MemTotal'] - $data['MemAvailable'];
        } else {
            $used = ($data['MemTotal'] - ($data['MemFree'] + ($data['Buffers'] ?? 0) + ($data['Cached'] ?? 0)));
        }
        $usage = $used / max(1, $data['MemTotal']);
        $cache = ['value' => max(0, min(1, $usage)), 'ts' => $now];
        return $cache['value'];
    }

    private static function getDiskUsage(): float
    {
        static $cache = null;
        $now = time();
        if ($cache && ($now - $cache['ts']) < 30) {
            return $cache['value'];
        }
        $root = '/';
        try {
            $total = @disk_total_space($root);
            $free = @disk_free_space($root);
            if ($total <= 0 || $free < 0) {
                return 0.0;
            }
            $usage = 1 - ($free / $total);
            $cache = ['value' => max(0, min(1, $usage)), 'ts' => $now];
            return $cache['value'];
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    private static function getNetworkUsage(): float
    {
        // Provide fraction of assumed max bandwidth (env SYS_MAX_NETWORK_BPS or 1 Gbps default)
        $maxBps = (float)(getenv('SYS_MAX_NETWORK_BPS') ?: 1000000000); // 1 Gbps default
        try {
            $lines = @file('/proc/net/dev');
            if (!$lines) {
                return 0.0;
            }
            $totalBytes = 0;
            foreach ($lines as $line) {
                if (strpos($line, ':') === false) {
                    continue;
                }
                [$iface, $rest] = array_map('trim', explode(':', trim($line), 2));
                if ($iface === 'lo') {
                    continue;
                }
                if (!preg_match('/^(eth|en|wl)/', $iface)) {
                    continue; // likely physical
                }
                $parts = preg_split('/\s+/', trim($rest));
                if (count($parts) < 9) {
                    continue;
                }
                $rx = (int)$parts[0];
                $tx = (int)$parts[8];
                $totalBytes += ($rx + $tx);
            }
            $redisKey = 'sys:net:last';
            $prev = RedisClient::get($redisKey);
            $now = microtime(true);
            if ($prev && isset($prev['bytes'], $prev['ts'])) {
                $deltaBytes = $totalBytes - (int)$prev['bytes'];
                $deltaTime = max(0.001, $now - (float)$prev['ts']);
                $bps = ($deltaBytes * 8) / $deltaTime;
                $usage = max(0, min(1, $bps / $maxBps));
                RedisClient::set($redisKey, ['bytes' => $totalBytes, 'ts' => $now], 120);
                return $usage;
            }
            RedisClient::set($redisKey, ['bytes' => $totalBytes, 'ts' => $now], 120);
            return 0.0; // first sample can't compute
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    private static function getLoadAverage(): array
    {
        $load = sys_getloadavg();
        if (!$load) {
            return [0.0, 0.0, 0.0];
        }
        return [round($load[0], 2), round($load[1], 2), round($load[2], 2)];
    }

    private static function getSystemUptime(): int
    {
        $u = @file_get_contents('/proc/uptime');
        if ($u && preg_match('/^(\d+\.\d+)/', $u, $m)) {
            return (int)$m[1];
        }
        // Fallback using boot time
        $stat = @file('/proc/stat');
        if ($stat) {
            foreach ($stat as $line) {
                if (strpos($line, 'btime') === 0) {
                    $parts = explode(' ', trim($line));
                    $btime = (int)($parts[1] ?? 0);
                    if ($btime > 0) {
                        return time() - $btime;
                    }
                }
            }
        }
        return 0;
    }

    private static function getProcessCount(): int
    {
        try {
            $dirs = glob('/proc/[0-9]*', GLOB_ONLYDIR);
            if ($dirs !== false) {
                return count($dirs);
            }
            $out = @shell_exec('ps -e | wc -l');
            return (int)trim((string)$out);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private static function getSystemTemperature(): float
    {
        // Try thermal zones
        foreach (glob('/sys/class/thermal/thermal_zone*/temp') as $f) {
            $raw = @file_get_contents($f);
            if ($raw !== false) {
                $v = (int)trim($raw);
                if ($v > 0) {
                    return round($v / 1000, 1);
                }
            }
        }
        // Fallback: try sensors command if installed (optional, ignore errors)
        $sensors = @shell_exec('command -v sensors >/dev/null 2>&1 && sensors 2>/dev/null | grep -E "(Package id 0|Tdie|Tctl)" | head -n1');
        if ($sensors && preg_match('/([0-9]+\.?[0-9]?)°?C/', $sensors, $m)) {
            return (float)$m[1];
        }
        return 0.0; // Unknown
    }

    // --- Application-level dynamic metrics ---

    private static function getAverageResponseTime(): float
    {
        $stats = self::getMetricStats('response_time');
        return $stats['avg'] ?? 0.0;
    }

    private static function getCurrentThroughput(): float
    {
        // Throughput: requests per second over last 60s
        $stats = self::getMetricStats('response_time', 60); // use response_time samples as proxy if request_count absent
        $count = $stats['count'] ?? 0;
        if ($count > 0) {
            return $count / 60.0;
        }
        $req = self::getMetricStats('request_count', 60);
        if (($req['count'] ?? 0) > 0) {
            return ($req['sum'] ?? 0) / 60.0;
        }
        return 0.0;
    }

    private static function getErrorRate(): float
    {
        // Prefer explicit error metrics
        $errors = self::getMetricStats('request_errors', 300);
        $total = self::getMetricStats('request_total', 300);
        if (($total['sum'] ?? 0) > 0) {
            return min(1, ($errors['sum'] ?? 0) / max(1, $total['sum']));
        }
        $stat = self::getMetricStats('error_rate', 300);
        return $stat['avg'] ?? 0.0;
    }

    private static function getActiveSessions(): int
    {
        $path = session_save_path();
        if (!$path) {
            return 0;
        }
        $count = 0;
        $now = time();
        foreach (glob(rtrim($path, '/') . '/sess_*') as $file) {
            $mtime = @filemtime($file);
            if ($mtime && ($now - $mtime) <= 1800) { // active within 30m
                $count++;
            }
        }
        return $count;
    }

    private static function getCacheHitRate(): float
    {
        $hits = self::getMetricStats('cache_hit', 300);
        $miss = self::getMetricStats('cache_miss', 300);
        $h = $hits['sum'] ?? 0;
        $m = $miss['sum'] ?? 0;
        $tot = $h + $m;
        if ($tot === 0) {
            return 0.0;
        }
        return $h / $tot;
    }

    private static function getAPIPerformance(): array
    {
        $stats = self::getMetricStats('api_response_time');
        return [
            'avg_response' => $stats['avg'] ?? 0.0,
            'p95' => $stats['p95'] ?? null,
            'count' => $stats['count'] ?? 0
        ];
    }

    private static function getPageLoadTimes(): array
    {
        $stats = self::getMetricStats('page_load_time');
        return [
            'avg' => $stats['avg'] ?? 0.0,
            'p95' => $stats['p95'] ?? null,
            'count' => $stats['count'] ?? 0
        ];
    }

    private static function getUserSatisfactionScore(): float
    {
        $stats = self::getMetricStats('user_satisfaction');
        if (isset($stats['avg'])) {
            return $stats['avg'];
        }
        // Heuristic fallback: inverse of error rate weighted by performance
        $er = self::getErrorRate();
        $rt = self::getAverageResponseTime();
        $perfFactor = min(1, 2.0 / max(0.1, $rt));
        return max(0, min(1, (1 - $er) * 0.7 + $perfFactor * 0.3));
    }

    private static function getDatabaseConnectionUsage(): float
    {
        try {
            $pdo = DB::connection();
            $threads = (int)($pdo->query("SHOW STATUS LIKE 'Threads_connected'")->fetch()['Value'] ?? 0);
            $maxConn = (int)($pdo->query("SHOW VARIABLES LIKE 'max_connections'")->fetch()['Value'] ?? 151);
            if ($maxConn <= 0) {
                return 0.0;
            }
            return max(0, min(1, $threads / $maxConn));
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    private static function getBufferPoolUsage(): float
    {
        try {
            $pdo = DB::connection();
            $dataPages = (int)($pdo->query("SHOW STATUS LIKE 'Innodb_buffer_pool_pages_data'")->fetch()['Value'] ?? 0);
            $totalPages = (int)($pdo->query("SHOW STATUS LIKE 'Innodb_buffer_pool_pages_total'")->fetch()['Value'] ?? 0);
            if ($totalPages <= 0) {
                return 0.0;
            }
            return max(0, min(1, $dataPages / $totalPages));
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    private static function getLockWaits(): int
    {
        try {
            $pdo = DB::connection();
            return (int)($pdo->query("SHOW STATUS LIKE 'Innodb_row_lock_waits'")->fetch()['Value'] ?? 0);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private static function getDeadlockCount(): int
    {
        try {
            $pdo = DB::connection();
            $row = $pdo->query("SHOW STATUS LIKE 'Innodb_deadlocks'")->fetch();
            return (is_array($row) && isset($row['Value'])) ? (int)$row['Value'] : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /* ------------------------------------------------------------
     * CAPACITY PLANNING SUPPORT HELPERS (STABILIZATION PLACEHOLDERS)
     * ------------------------------------------------------------ */
    private static function generateCapacityRecommendations(array $current, array $predictions): array
    {
        $recs = [];
        foreach ($predictions as $key => $pred) {
            if (!is_array($pred)) {
                continue;
            }
            $metric = $pred['metric'] ?? $key;
            $projected = $pred['projected'] ?? ($pred['connections_projected'] ?? null);
            if ($projected === null) {
                continue;
            }
            $currentVal = $current[$metric] ?? ($pred['connections_current'] ?? 0);
            $breach = $projected >= 0.85; // approaching saturation
            $urgency = $breach ? ($projected >= 0.95 ? 'immediate' : 'soon') : 'monitor';
            $action = 'monitor';
            $details = 'Current capacity sufficient';
            if ($breach) {
                if (strpos($metric, 'cpu') !== false) {
                    $action = 'scale_up_cpu';
                    $details = 'Projected CPU utilisation ' . round($projected * 100, 1) . '% within horizon';
                } elseif (strpos($metric, 'memory') !== false) {
                    $action = 'add_memory';
                    $details = 'Projected memory utilisation ' . round($projected * 100, 1) . '%';
                } elseif (strpos($metric, 'storage') !== false || strpos($metric, 'disk') !== false) {
                    $action = 'expand_storage';
                    $details = 'Disk/storage trending high (' . round($projected * 100, 1) . '%)';
                } elseif (strpos($metric, 'network') !== false) {
                    $action = 'increase_bandwidth';
                    $details = 'Network saturation risk at ' . round($projected * 100, 1) . '%';
                } elseif (strpos($metric, 'database') !== false || strpos($metric, 'connections') !== false) {
                    $action = 'tune_db_or_scale_read_replicas';
                    $details = 'DB connections or buffer pool nearing limit';
                }
            }
            $recs[] = [
                'metric' => $metric,
                'current' => $currentVal,
                'projected' => $projected,
                'growth_rate' => $pred['growth_rate'] ?? null,
                'action' => $action,
                'urgency' => $urgency,
                'details' => $details,
                'confidence' => $pred['confidence'] ?? 0.3
            ];
        }
        return $recs;
    }

    private static function calculateCapacityCosts(array $predictions, array $recommendations): array
    {
        // Heuristic placeholder: assign nominal monthly cost estimates per action
        $actionCost = [
            'scale_up_cpu' => 120.0,
            'add_memory' => 90.0,
            'expand_storage' => 70.0,
            'increase_bandwidth' => 60.0,
            'tune_db_or_scale_read_replicas' => 150.0,
            'monitor' => 0.0
        ];
        $costs = [];
        foreach ($recommendations as $rec) {
            $action = $rec['action'] ?? 'monitor';
            $costs[] = [
                'metric' => $rec['metric'] ?? 'unknown',
                'action' => $action,
                'estimated_monthly_cost' => $actionCost[$action] ?? 0.0,
                'projected_utilisation' => $rec['projected'] ?? null,
                'confidence' => $rec['confidence'] ?? 0.3
            ];
        }
        return [
            'items' => $costs,
            'total_estimated_monthly' => array_sum(array_column($costs, 'estimated_monthly_cost'))
        ];
    }

    private static function calculatePredictionConfidence(array $predictions): array
    {
        $out = [];
        foreach ($predictions as $k => $pred) {
            if (isset($pred['confidence'])) {
                $out[$k] = $pred['confidence'];
            } elseif (is_array($pred) && isset($pred['connections_projected'])) {
                $out[$k] = $pred['confidence'] ?? 0.4;
            } else {
                $out[$k] = 0.25; // conservative default
            }
        }
        return $out;
    }

    /* ------------------------------------------------------------
     * METRIC & AGGREGATION UTILITIES
     * ------------------------------------------------------------ */
    private static function getMetricStats(string $metric, int $windowSeconds = 300): array
    {
        $key = "metric:{$metric}:timeseries";
        $raw = RedisClient::zrevrange($key, 0, 499); // latest up to 500 samples
        if (empty($raw)) {
            return [];
        }
        $now = time();
        $values = [];
        foreach ($raw as $row) {
            // Row may be already decoded array via RedisClient wrapper
            if (is_array($row)) {
                $ts = $row['timestamp'] ?? null;
                $val = $row['value'] ?? null;
                if ($ts === null || $val === null) {
                    continue;
                }
                if ($windowSeconds > 0 && ($now - $ts) > $windowSeconds) {
                    continue;
                }
                $values[] = (float)$val;
            } elseif (is_numeric($row)) {
                // Aggregation sets may have pure numeric values
                $values[] = (float)$row;
            }
        }
        if (!$values) {
            return [];
        }
        $count = count($values);
        sort($values);
        $sum = array_sum($values);
        $avg = $sum / $count;
        $p95Index = (int)floor(0.95 * ($count - 1));
        $p95 = $values[$p95Index] ?? $values[$count - 1];
        return [
            'count' => $count,
            'sum' => $sum,
            'avg' => $avg,
            'min' => $values[0],
            'max' => $values[$count - 1],
            'p95' => $p95
        ];
    }

    /* ------------------------------------------------------------
     * ALERTING & RECOMMENDATIONS
     * ------------------------------------------------------------ */
    private static function calculateAlertSeverity(float $value, float $threshold): string
    {
        $ratio = $threshold > 0 ? $value / $threshold : 0;
        if ($ratio >= 1.5) {
            return 'critical';
        }
        if ($ratio >= 1.2) {
            return 'high';
        }
        if ($ratio >= 1.0) {
            return 'warning';
        }
        return 'info';
    }

    private static function generateAlerts(): array
    {
        $alerts = [];
        foreach (self::ALERT_THRESHOLDS as $metric => $threshold) {
            $stats = self::getMetricStats($metric, 120);
            if (!$stats) {
                continue;
            }
            $current = $stats['avg'];
            if ($current > $threshold) {
                $alerts[] = [
                    'metric' => $metric,
                    'value' => $current,
                    'threshold' => $threshold,
                    'severity' => self::calculateAlertSeverity($current, $threshold),
                    'timestamp' => time()
                ];
            }
        }
        return $alerts;
    }

    private static function generateRecommendations(): array
    {
        // Leverage capacity planning fast path (short horizon) and anomaly scan
        $capacity = self::performCapacityPlanning(['horizon_days' => 30]);
        $anoms = self::detectAnomalies(['metrics' => array_keys(self::ALERT_THRESHOLDS)]);
        $recs = [];
        foreach ($capacity['recommendations'] ?? [] as $rec) {
            if (($rec['action'] ?? '') !== 'monitor') {
                $recs[] = [
                    'type' => 'capacity',
                    'metric' => $rec['metric'] ?? 'unknown',
                    'action' => $rec['action'],
                    'urgency' => $rec['urgency'] ?? 'monitor',
                    'details' => $rec['details'] ?? ''
                ];
            }
        }
        foreach (($anoms['anomalies'] ?? []) as $metric => $items) {
            foreach ($items as $anom) {
                $recs[] = [
                    'type' => 'anomaly',
                    'metric' => $metric,
                    'action' => 'investigate',
                    'urgency' => 'high',
                    'details' => 'Anomalous spike detected: deviation ' . round(($anom['deviation'] ?? 0) * 100, 1) . '%'
                ];
            }
        }
        return $recs;
    }

    private static function errorBudgetStatus(): array
    {
        $erStats = self::getMetricStats('error_rate', 86400);
        $avgError = $erStats['avg'] ?? 0.0;
        $sloTarget = 0.01; // 99% success target
        $budget = max(0, $sloTarget - $avgError);
        $status = $budget > ($sloTarget * 0.5) ? 'healthy' : ($budget > ($sloTarget * 0.2) ? 'at_risk' : 'critical');
        return [
            'slo_target_error_rate' => $sloTarget,
            'current_error_rate' => $avgError,
            'remaining_error_budget' => $budget,
            'status' => $status
        ];
    }

    private static function analyzeTrends(array $analytics): array
    {
        // Simple directional indicators using last vs earlier samples
        $trends = [];
        foreach (['cpu_usage','memory_usage','disk_usage','response_time','error_rate'] as $metric) {
            $stats = self::getMetricStats($metric, 1800); // 30m window
            if (!$stats || ($stats['count'] ?? 0) < 5) {
                continue;
            }
            $trend = $stats['avg'] > ($stats['p95'] * 0.6) ? 'rising' : 'stable';
            $trends[$metric] = [
                'status' => $trend,
                'avg' => $stats['avg'],
                'p95' => $stats['p95']
            ];
        }
        return $trends;
    }

    private static function generateOptimizationSuggestions(array $analytics): array
    {
        $sugs = [];
        $app = $analytics['application_performance'] ?? [];
        if (($app['response_time'] ?? 0) > 1.5) {
            $sugs[] = 'Investigate slow endpoints (response_time > 1.5s). Add query/index profiling.';
        }
        if (($app['error_rate'] ?? 0) > 0.02) {
            $sugs[] = 'Error rate elevated. Review recent deploys & error logs.';
        }
        $db = $analytics['database_performance'] ?? [];
        if (($db['slow_queries'] ?? 0) > 50) {
            $sugs[] = 'High slow query count. Enable slow query log tuning.';
        }
        return $sugs;
    }

    /* ------------------------------------------------------------
     * ANOMALY DETECTION (LIGHTWEIGHT PLACEHOLDERS)
     * ------------------------------------------------------------ */
    private static function detectMetricAnomalies(string $metric, array $options): array
    {
        $stats = self::getMetricStats($metric, $options['window'] ?? 3600);
        if (!$stats || ($stats['count'] ?? 0) < 10) {
            return [];
        }
        $current = $stats['avg'];
        $p95 = $stats['p95'] ?? $current;
        $baseline = ($stats['sum'] - $p95) / max(1, ($stats['count'] - 1));
        if ($baseline <= 0) {
            return [];
        }
        $ratio = $current / $baseline;
        if ($ratio < 1.3) {
            return [];
        }
        return [[
            'metric' => $metric,
            'current' => $current,
            'baseline' => $baseline,
            'ratio' => $ratio,
            'deviation' => $ratio - 1
        ]];
    }

    private static function applyMLAnomalyDetection(array $anomalies): array
    {
        // Placeholder: In future integrate statistical / ML model. For now echo structure.
        return ['model' => 'heuristic-v1', 'anomaly_sets' => count($anomalies)];
    }

    private static function calculateAnomalySeverity(array $anomaly): string
    {
        $dev = $anomaly['deviation'] ?? 0;
        if ($dev >= 1.0) {
            return 'critical';
        }
        if ($dev >= 0.5) {
            return 'high';
        }
        if ($dev >= 0.25) {
            return 'moderate';
        }
        return 'low';
    }

    private static function assessAnomalyImpact(array $anomaly): array
    {
        $severity = self::calculateAnomalySeverity($anomaly);
        $impact = 'low';
        if (in_array($severity, ['critical','high'], true)) {
            $impact = 'elevated';
        }
        return [
            'severity' => $severity,
            'impact_level' => $impact,
            'recommended_action' => $impact === 'elevated' ? 'investigate_immediately' : 'monitor'
        ];
    }

    private static function generateAnomalySummary(array $anomalies): array
    {
        $summary = [];
        foreach ($anomalies as $metric => $items) {
            $summary[$metric] = count($items);
        }
        return $summary;
    }

    private static function generateAnomalyRecommendations(array $anomalies): array
    {
        $recs = [];
        foreach ($anomalies as $metric => $items) {
            foreach ($items as $anom) {
                $recs[] = [
                    'metric' => $metric,
                    'action' => 'investigate_metric_spike',
                    'details' => 'Deviation ' . round(($anom['deviation'] ?? 0) * 100, 1) . '%'
                ];
            }
        }
        return $recs;
    }

    /* ------------------------------------------------------------
     * PREDICTIVE INSIGHTS STUB
     * ------------------------------------------------------------ */
    private static function getPredictiveInsights(): array
    {
        $capacity = self::performCapacityPlanning(['horizon_days' => 60]);
        return [
            'capacity_summary' => [
                'at_risk' => array_values(array_filter($capacity['recommendations'] ?? [], fn($r) => ($r['action'] ?? 'monitor') !== 'monitor')),
                'horizon_days' => $capacity['planning_horizon'] ?? 60
            ],
            'confidence' => $capacity['confidence_scores'] ?? []
        ];
    }

    /* ------------------------------------------------------------
     * USER EXPERIENCE / BUSINESS METRIC STUBS
     * ------------------------------------------------------------ */
    private static function getAveragePageLoadTime(): float
    {
        return self::getMetricStats('page_load_time')['avg'] ?? 0.0;
    }
    private static function getTimeToFirstByte(): float
    {
        return self::getMetricStats('ttfb')['avg'] ?? 0.0;
    }
    private static function getLargestContentfulPaint(): float
    {
        return self::getMetricStats('lcp')['avg'] ?? 0.0;
    }
    private static function getCumulativeLayoutShift(): float
    {
        return self::getMetricStats('cls')['avg'] ?? 0.0;
    }
    private static function getFirstInputDelay(): float
    {
        return self::getMetricStats('fid')['avg'] ?? 0.0;
    }
    private static function getBounceRate(): float
    {
        return self::getMetricStats('bounce_rate')['avg'] ?? 0.0;
    }
    private static function getConversionRate(): float
    {
        return self::getMetricStats('conversion_rate')['avg'] ?? 0.0;
    }
    private static function getUserEngagementScore(): float
    {
        return self::getMetricStats('engagement_score')['avg'] ?? 0.0;
    }
    private static function getInventoryTurnover(): float
    {
        return self::getMetricStats('inventory_turnover')['avg'] ?? 0.0;
    }
    private static function getCustomerAcquisitionRate(): float
    {
        return self::getMetricStats('customer_acquisition')['avg'] ?? 0.0;
    }
    private static function getCustomerRetentionRate(): float
    {
        return self::getMetricStats('customer_retention')['avg'] ?? 0.0;
    }
    private static function getAverageOrderValue(): float
    {
        return self::getMetricStats('average_order_value')['avg'] ?? 0.0;
    }
    private static function getProfitMargin(): float
    {
        return self::getMetricStats('profit_margin')['avg'] ?? 0.0;
    }
}
