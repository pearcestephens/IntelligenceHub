<?php

declare(strict_types=1);

namespace App\Analytics;

use App\Logger;
use App\RedisClient;
use App\DB;

/**
 * Metrics Collector
 * 
 * Collects and aggregates performance metrics:
 * - Response times (p50, p95, p99)
 * - Tool execution times
 * - Token usage and costs
 * - Cache hit rates
 * - Error rates by type
 * 
 * @package App\Analytics
 * @author Feature Enhancement Phase 5
 */
class MetricsCollector
{
    private Logger $logger;
    private ?RedisClient $redis;
    private string $metricsPrefix = 'metrics:';

    public function __construct(Logger $logger, ?RedisClient $redis = null)
    {
        $this->logger = $logger;
        $this->redis = $redis;
    }

    /**
     * Record response time
     */
    public function recordResponseTime(string $endpoint, float $time): void
    {
        $key = $this->metricsPrefix . 'response_times:' . date('Y-m-d-H');
        
        if ($this->redis) {
            $this->redis->lpush($key, (string)$time);
            $this->redis->expire($key, 86400 * 7); // Keep 7 days
        }

        // Also store in DB for long-term analysis
        $sql = "INSERT INTO metrics (metric_type, metric_name, value, timestamp) 
                VALUES ('response_time', ?, ?, NOW())";
        DB::execute($sql, [$endpoint, $time]);
    }

    /**
     * Record tool execution
     */
    public function recordToolExecution(string $toolName, float $time, bool $success): void
    {
        $key = $this->metricsPrefix . 'tool:' . $toolName . ':' . date('Y-m-d');
        
        if ($this->redis) {
            $this->redis->hincrby($key, 'executions', 1);
            $this->redis->hincrbyfloat($key, 'total_time', $time);
            $this->redis->hincrby($key, $success ? 'successes' : 'failures', 1);
            $this->redis->expire($key, 86400 * 30); // Keep 30 days
        }
    }

    /**
     * Record token usage
     */
    public function recordTokenUsage(string $model, int $inputTokens, int $outputTokens, float $cost): void
    {
        $key = $this->metricsPrefix . 'tokens:' . date('Y-m-d');
        
        if ($this->redis) {
            $this->redis->hincrby($key, $model . ':input', $inputTokens);
            $this->redis->hincrby($key, $model . ':output', $outputTokens);
            $this->redis->hincrbyfloat($key, $model . ':cost', $cost);
            $this->redis->expire($key, 86400 * 90); // Keep 90 days
        }

        // Store in DB
        $sql = "INSERT INTO metrics (metric_type, metric_name, value, metadata, timestamp) 
                VALUES ('token_usage', ?, ?, ?, NOW())";
        DB::execute($sql, [
            $model,
            $inputTokens + $outputTokens,
            json_encode(['input' => $inputTokens, 'output' => $outputTokens, 'cost' => $cost])
        ]);
    }

    /**
     * Record cache hit/miss
     */
    public function recordCacheHit(string $cacheType, bool $hit): void
    {
        $key = $this->metricsPrefix . 'cache:' . $cacheType . ':' . date('Y-m-d');
        
        if ($this->redis) {
            $this->redis->hincrby($key, $hit ? 'hits' : 'misses', 1);
            $this->redis->expire($key, 86400 * 7);
        }
    }

    /**
     * Record error
     */
    public function recordError(string $errorType, string $message): void
    {
        $key = $this->metricsPrefix . 'errors:' . date('Y-m-d-H');
        
        if ($this->redis) {
            $this->redis->hincrby($key, $errorType, 1);
            $this->redis->expire($key, 86400 * 30);
        }

        // Store in DB
        $sql = "INSERT INTO metrics (metric_type, metric_name, value, metadata, timestamp) 
                VALUES ('error', ?, 1, ?, NOW())";
        DB::execute($sql, [$errorType, json_encode(['message' => substr($message, 0, 500)])]);
    }

    /**
     * Get response time percentiles
     */
    public function getResponseTimePercentiles(string $endpoint, int $hours = 24): array
    {
        $times = [];
        
        for ($i = 0; $i < $hours; $i++) {
            $key = $this->metricsPrefix . 'response_times:' . date('Y-m-d-H', strtotime("-{$i} hours"));
            if ($this->redis) {
                $hourTimes = $this->redis->lrange($key, 0, -1);
                $times = array_merge($times, array_map('floatval', $hourTimes));
            }
        }

        if (empty($times)) {
            return ['p50' => 0, 'p95' => 0, 'p99' => 0, 'count' => 0];
        }

        sort($times);
        $count = count($times);

        return [
            'p50' => $times[(int)($count * 0.50)],
            'p95' => $times[(int)($count * 0.95)],
            'p99' => $times[(int)($count * 0.99)],
            'min' => $times[0],
            'max' => $times[$count - 1],
            'avg' => array_sum($times) / $count,
            'count' => $count
        ];
    }

    /**
     * Get tool statistics
     */
    public function getToolStatistics(string $toolName, int $days = 7): array
    {
        $stats = [
            'executions' => 0,
            'successes' => 0,
            'failures' => 0,
            'total_time' => 0,
            'avg_time' => 0,
            'success_rate' => 0
        ];

        for ($i = 0; $i < $days; $i++) {
            $key = $this->metricsPrefix . 'tool:' . $toolName . ':' . date('Y-m-d', strtotime("-{$i} days"));
            
            if ($this->redis) {
                $dayStats = $this->redis->hgetall($key);
                $stats['executions'] += (int)($dayStats['executions'] ?? 0);
                $stats['successes'] += (int)($dayStats['successes'] ?? 0);
                $stats['failures'] += (int)($dayStats['failures'] ?? 0);
                $stats['total_time'] += (float)($dayStats['total_time'] ?? 0);
            }
        }

        if ($stats['executions'] > 0) {
            $stats['avg_time'] = $stats['total_time'] / $stats['executions'];
            $stats['success_rate'] = ($stats['successes'] / $stats['executions']) * 100;
        }

        return $stats;
    }

    /**
     * Get token usage summary
     */
    public function getTokenUsageSummary(int $days = 30): array
    {
        $summary = [];

        for ($i = 0; $i < $days; $i++) {
            $key = $this->metricsPrefix . 'tokens:' . date('Y-m-d', strtotime("-{$i} days"));
            
            if ($this->redis) {
                $dayData = $this->redis->hgetall($key);
                
                foreach ($dayData as $field => $value) {
                    [$model, $type] = explode(':', $field);
                    
                    if (!isset($summary[$model])) {
                        $summary[$model] = ['input' => 0, 'output' => 0, 'cost' => 0];
                    }
                    
                    $summary[$model][$type] += (float)$value;
                }
            }
        }

        return $summary;
    }

    /**
     * Get cache hit rate
     */
    public function getCacheHitRate(string $cacheType, int $days = 7): float
    {
        $totalHits = 0;
        $totalMisses = 0;

        for ($i = 0; $i < $days; $i++) {
            $key = $this->metricsPrefix . 'cache:' . $cacheType . ':' . date('Y-m-d', strtotime("-{$i} days"));
            
            if ($this->redis) {
                $stats = $this->redis->hgetall($key);
                $totalHits += (int)($stats['hits'] ?? 0);
                $totalMisses += (int)($stats['misses'] ?? 0);
            }
        }

        $total = $totalHits + $totalMisses;
        return $total > 0 ? ($totalHits / $total) * 100 : 0;
    }

    /**
     * Get error summary
     */
    public function getErrorSummary(int $hours = 24): array
    {
        $errors = [];

        for ($i = 0; $i < $hours; $i++) {
            $key = $this->metricsPrefix . 'errors:' . date('Y-m-d-H', strtotime("-{$i} hours"));
            
            if ($this->redis) {
                $hourErrors = $this->redis->hgetall($key);
                
                foreach ($hourErrors as $type => $count) {
                    $errors[$type] = ($errors[$type] ?? 0) + (int)$count;
                }
            }
        }

        arsort($errors);
        return $errors;
    }

    /**
     * Get real-time metrics
     */
    public function getRealTimeMetrics(): array
    {
        return [
            'response_times' => $this->getResponseTimePercentiles('all', 1),
            'active_conversations' => $this->getActiveConversations(),
            'requests_per_minute' => $this->getRequestsPerMinute(),
            'cache_hit_rate' => $this->getCacheHitRate('all', 1),
            'error_rate' => $this->getErrorRate(60),
            'timestamp' => time()
        ];
    }

    /**
     * Get active conversations
     */
    private function getActiveConversations(): int
    {
        $sql = "SELECT COUNT(DISTINCT conversation_id) 
                FROM messages 
                WHERE created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
        
        return (int)DB::selectValue($sql);
    }

    /**
     * Get requests per minute
     */
    private function getRequestsPerMinute(): float
    {
        $key = $this->metricsPrefix . 'response_times:' . date('Y-m-d-H');
        
        if ($this->redis) {
            $count = $this->redis->llen($key);
            return $count / 60;
        }
        
        return 0;
    }

    /**
     * Get error rate
     */
    private function getErrorRate(int $minutes): float
    {
        $errorKey = $this->metricsPrefix . 'errors:' . date('Y-m-d-H');
        $requestKey = $this->metricsPrefix . 'response_times:' . date('Y-m-d-H');
        
        if ($this->redis) {
            $errors = array_sum($this->redis->hvals($errorKey) ?: [0]);
            $requests = $this->redis->llen($requestKey);
            
            return $requests > 0 ? ($errors / $requests) * 100 : 0;
        }
        
        return 0;
    }
}
