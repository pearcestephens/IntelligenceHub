<?php

namespace BotDeployment\Services;

use PDO;

/**
 * MetricsCollector - Performance & Business Metrics Service
 *
 * Features:
 * - Counters (increment/decrement)
 * - Gauges (set absolute values)
 * - Timers (measure duration)
 * - Histograms (distribution)
 * - Business metrics tracking
 * - Aggregation and reporting
 */
class MetricsCollector
{
    private PDO $pdo;
    private array $config;
    private array $buffer = [];
    private int $bufferSize;

    // Metric types
    const TYPE_COUNTER = 'counter';
    const TYPE_GAUGE = 'gauge';
    const TYPE_TIMER = 'timer';
    const TYPE_HISTOGRAM = 'histogram';

    /**
     * Constructor
     *
     * @param PDO $pdo Database connection
     * @param array $config Configuration
     */
    public function __construct(PDO $pdo, array $config = [])
    {
        $this->pdo = $pdo;
        $this->config = array_merge([
            'buffer_size' => 100,        // Batch insert size
            'retention_days' => 30,      // Data retention period
            'aggregation_interval' => 60 // Seconds between aggregations
        ], $config);

        $this->bufferSize = $this->config['buffer_size'];
        $this->createTables();
    }

    /**
     * Create metrics tables
     */
    private function createTables(): void
    {
        // Raw metrics table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS metrics (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                metric_name VARCHAR(255) NOT NULL,
                metric_type ENUM('counter', 'gauge', 'timer', 'histogram') NOT NULL,
                value DECIMAL(20, 4) NOT NULL,
                tags JSON,
                timestamp INT NOT NULL,
                INDEX idx_name (metric_name),
                INDEX idx_type (metric_type),
                INDEX idx_timestamp (timestamp)
            ) ENGINE=InnoDB
        ");

        // Aggregated metrics table
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS metrics_aggregated (
                id INT AUTO_INCREMENT PRIMARY KEY,
                metric_name VARCHAR(255) NOT NULL,
                metric_type VARCHAR(20) NOT NULL,
                interval_start INT NOT NULL,
                interval_end INT NOT NULL,
                count INT NOT NULL,
                sum DECIMAL(20, 4),
                min DECIMAL(20, 4),
                max DECIMAL(20, 4),
                avg DECIMAL(20, 4),
                p50 DECIMAL(20, 4),
                p95 DECIMAL(20, 4),
                p99 DECIMAL(20, 4),
                tags JSON,
                UNIQUE KEY idx_unique (metric_name, interval_start, tags(255)),
                INDEX idx_interval (interval_start, interval_end)
            ) ENGINE=InnoDB
        ");
    }

    /**
     * Increment counter
     *
     * @param string $name Metric name
     * @param float $value Amount to increment (default: 1)
     * @param array $tags Optional tags
     */
    public function increment(string $name, float $value = 1, array $tags = []): void
    {
        $this->record($name, self::TYPE_COUNTER, $value, $tags);
    }

    /**
     * Decrement counter
     *
     * @param string $name Metric name
     * @param float $value Amount to decrement (default: 1)
     * @param array $tags Optional tags
     */
    public function decrement(string $name, float $value = 1, array $tags = []): void
    {
        $this->record($name, self::TYPE_COUNTER, -$value, $tags);
    }

    /**
     * Set gauge value
     *
     * @param string $name Metric name
     * @param float $value Absolute value
     * @param array $tags Optional tags
     */
    public function gauge(string $name, float $value, array $tags = []): void
    {
        $this->record($name, self::TYPE_GAUGE, $value, $tags);
    }

    /**
     * Record timing
     *
     * @param string $name Metric name
     * @param float $milliseconds Duration in milliseconds
     * @param array $tags Optional tags
     */
    public function timing(string $name, float $milliseconds, array $tags = []): void
    {
        $this->record($name, self::TYPE_TIMER, $milliseconds, $tags);
    }

    /**
     * Record histogram value
     *
     * @param string $name Metric name
     * @param float $value Value to record
     * @param array $tags Optional tags
     */
    public function histogram(string $name, float $value, array $tags = []): void
    {
        $this->record($name, self::TYPE_HISTOGRAM, $value, $tags);
    }

    /**
     * Time a callable
     *
     * @param string $name Metric name
     * @param callable $callback Function to time
     * @param array $tags Optional tags
     * @return mixed Callback return value
     */
    public function time(string $name, callable $callback, array $tags = [])
    {
        $start = microtime(true);

        try {
            $result = $callback();
            $duration = (microtime(true) - $start) * 1000;
            $this->timing($name, $duration, array_merge($tags, ['success' => true]));
            return $result;
        } catch (\Exception $e) {
            $duration = (microtime(true) - $start) * 1000;
            $this->timing($name, $duration, array_merge($tags, ['success' => false]));
            throw $e;
        }
    }

    /**
     * Record a metric
     *
     * @param string $name Metric name
     * @param string $type Metric type
     * @param float $value Metric value
     * @param array $tags Optional tags
     */
    private function record(string $name, string $type, float $value, array $tags = []): void
    {
        $this->buffer[] = [
            'metric_name' => $name,
            'metric_type' => $type,
            'value' => $value,
            'tags' => !empty($tags) ? json_encode($tags) : null,
            'timestamp' => time()
        ];

        // Flush buffer if full
        if (count($this->buffer) >= $this->bufferSize) {
            $this->flush();
        }
    }

    /**
     * Flush buffer to database
     */
    public function flush(): void
    {
        if (empty($this->buffer)) {
            return;
        }

        $sql = "INSERT INTO metrics (metric_name, metric_type, value, tags, timestamp) VALUES ";
        $values = [];
        $params = [];

        foreach ($this->buffer as $metric) {
            $values[] = "(?, ?, ?, ?, ?)";
            $params[] = $metric['metric_name'];
            $params[] = $metric['metric_type'];
            $params[] = $metric['value'];
            $params[] = $metric['tags'];
            $params[] = $metric['timestamp'];
        }

        $sql .= implode(', ', $values);

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $this->buffer = [];
    }

    /**
     * Get metric values
     *
     * @param string $name Metric name
     * @param int $start Start timestamp
     * @param int $end End timestamp
     * @param array $tags Filter by tags
     * @return array Metric values
     */
    public function get(string $name, int $start, int $end, array $tags = []): array
    {
        $sql = "
            SELECT value, tags, timestamp
            FROM metrics
            WHERE metric_name = ?
            AND timestamp BETWEEN ? AND ?
        ";
        $params = [$name, $start, $end];

        if (!empty($tags)) {
            foreach ($tags as $key => $value) {
                $sql .= " AND JSON_EXTRACT(tags, '$.{$key}') = ?";
                $params[] = $value;
            }
        }

        $sql .= " ORDER BY timestamp ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get aggregated statistics
     *
     * @param string $name Metric name
     * @param int $start Start timestamp
     * @param int $end End timestamp
     * @param array $tags Filter by tags
     * @return array Statistics
     */
    public function getStats(string $name, int $start, int $end, array $tags = []): array
    {
        // Try aggregated table first
        $sql = "
            SELECT
                count,
                sum,
                min,
                max,
                avg,
                p50,
                p95,
                p99
            FROM metrics_aggregated
            WHERE metric_name = ?
            AND interval_start >= ?
            AND interval_end <= ?
        ";
        $params = [$name, $start, $end];

        if (!empty($tags)) {
            $sql .= " AND tags = ?";
            $params[] = json_encode($tags);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $aggregated = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($aggregated)) {
            // Combine aggregated results
            return $this->combineAggregates($aggregated);
        }

        // Fall back to raw calculation
        $values = $this->get($name, $start, $end, $tags);

        if (empty($values)) {
            return [
                'count' => 0,
                'sum' => 0,
                'min' => null,
                'max' => null,
                'avg' => null,
                'p50' => null,
                'p95' => null,
                'p99' => null
            ];
        }

        $nums = array_column($values, 'value');
        sort($nums);

        return [
            'count' => count($nums),
            'sum' => array_sum($nums),
            'min' => min($nums),
            'max' => max($nums),
            'avg' => round(array_sum($nums) / count($nums), 4),
            'p50' => $this->percentile($nums, 50),
            'p95' => $this->percentile($nums, 95),
            'p99' => $this->percentile($nums, 99)
        ];
    }

    /**
     * Calculate percentile
     *
     * @param array $values Sorted array of values
     * @param int $percentile Percentile (0-100)
     * @return float Percentile value
     */
    private function percentile(array $values, int $percentile): float
    {
        $count = count($values);
        if ($count === 0) {
            return 0;
        }

        $index = ($percentile / 100) * ($count - 1);
        $lower = floor($index);
        $upper = ceil($index);

        if ($lower === $upper) {
            return $values[$lower];
        }

        $fraction = $index - $lower;
        return $values[$lower] + ($fraction * ($values[$upper] - $values[$lower]));
    }

    /**
     * Combine multiple aggregated results
     *
     * @param array $aggregates Array of aggregate rows
     * @return array Combined statistics
     */
    private function combineAggregates(array $aggregates): array
    {
        $totalCount = 0;
        $totalSum = 0;
        $mins = [];
        $maxs = [];
        $avgs = [];

        foreach ($aggregates as $agg) {
            $totalCount += $agg['count'];
            $totalSum += $agg['sum'];
            if ($agg['min'] !== null) $mins[] = $agg['min'];
            if ($agg['max'] !== null) $maxs[] = $agg['max'];
            if ($agg['avg'] !== null) $avgs[] = $agg['avg'];
        }

        return [
            'count' => $totalCount,
            'sum' => $totalSum,
            'min' => !empty($mins) ? min($mins) : null,
            'max' => !empty($maxs) ? max($maxs) : null,
            'avg' => $totalCount > 0 ? round($totalSum / $totalCount, 4) : null,
            'p50' => $aggregates[0]['p50'] ?? null, // Approximation
            'p95' => $aggregates[0]['p95'] ?? null,
            'p99' => $aggregates[0]['p99'] ?? null
        ];
    }

    /**
     * Aggregate metrics
     *
     * @param int $intervalStart Start of interval
     * @param int $intervalEnd End of interval
     * @return int Number of metrics aggregated
     */
    public function aggregate(int $intervalStart, int $intervalEnd): int
    {
        // Get distinct metric names in interval
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT metric_name, metric_type
            FROM metrics
            WHERE timestamp BETWEEN ? AND ?
        ");
        $stmt->execute([$intervalStart, $intervalEnd]);
        $metrics = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $count = 0;

        foreach ($metrics as $metric) {
            $values = $this->get($metric['metric_name'], $intervalStart, $intervalEnd);

            if (empty($values)) {
                continue;
            }

            $nums = array_column($values, 'value');
            sort($nums);

            $stats = [
                'metric_name' => $metric['metric_name'],
                'metric_type' => $metric['metric_type'],
                'interval_start' => $intervalStart,
                'interval_end' => $intervalEnd,
                'count' => count($nums),
                'sum' => array_sum($nums),
                'min' => min($nums),
                'max' => max($nums),
                'avg' => array_sum($nums) / count($nums),
                'p50' => $this->percentile($nums, 50),
                'p95' => $this->percentile($nums, 95),
                'p99' => $this->percentile($nums, 99),
                'tags' => null
            ];

            $stmt = $this->pdo->prepare("
                INSERT INTO metrics_aggregated
                (metric_name, metric_type, interval_start, interval_end, count, sum, min, max, avg, p50, p95, p99, tags)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    count = VALUES(count),
                    sum = VALUES(sum),
                    min = VALUES(min),
                    max = VALUES(max),
                    avg = VALUES(avg),
                    p50 = VALUES(p50),
                    p95 = VALUES(p95),
                    p99 = VALUES(p99)
            ");

            $stmt->execute([
                $stats['metric_name'],
                $stats['metric_type'],
                $stats['interval_start'],
                $stats['interval_end'],
                $stats['count'],
                $stats['sum'],
                $stats['min'],
                $stats['max'],
                $stats['avg'],
                $stats['p50'],
                $stats['p95'],
                $stats['p99'],
                $stats['tags']
            ]);

            $count++;
        }

        return $count;
    }

    /**
     * Get all metric names
     *
     * @return array Metric names with types
     */
    public function getMetricNames(): array
    {
        $stmt = $this->pdo->query("
            SELECT DISTINCT metric_name, metric_type
            FROM metrics
            ORDER BY metric_name
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Clean up old metrics
     *
     * @param int $days Number of days to keep
     * @return array Cleanup stats
     */
    public function cleanup(int $days = null): array
    {
        $days = $days ?? $this->config['retention_days'];
        $cutoff = time() - ($days * 86400);

        $stmt = $this->pdo->prepare("DELETE FROM metrics WHERE timestamp < ?");
        $stmt->execute([$cutoff]);
        $rawDeleted = $stmt->rowCount();

        $stmt = $this->pdo->prepare("DELETE FROM metrics_aggregated WHERE interval_end < ?");
        $stmt->execute([$cutoff]);
        $aggDeleted = $stmt->rowCount();

        return [
            'raw_deleted' => $rawDeleted,
            'aggregated_deleted' => $aggDeleted
        ];
    }

    /**
     * Destructor - flush remaining buffer
     */
    public function __destruct()
    {
        $this->flush();
    }
}
