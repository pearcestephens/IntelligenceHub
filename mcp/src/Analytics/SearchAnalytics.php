<?php
/**
 * Search Analytics Dashboard
 *
 * Real-time analytics for search behavior, performance, and patterns
 *
 * Features:
 * - Popular queries tracking
 * - Search performance metrics
 * - User search patterns
 * - Cache hit rates
 * - Query suggestions analysis
 *
 * @package IntelligenceHub\MCP\Analytics
 * @version 1.0.0
 */

declare(strict_types=1);

namespace IntelligenceHub\MCP\Analytics;

use IntelligenceHub\MCP\Database\Connection;
use PDO;

class SearchAnalytics
{
    private PDO $pdo;
    private string $tableName = 'mcp_search_analytics';

    public function __construct()
    {
        $this->pdo = Connection::getInstance();
        $this->ensureTable();
    }

    /**
     * Log a search query
     *
     * @param array $data Search data
     * @return void
     */
    public function logSearch(array $data): void
    {
        $sql = "
            INSERT INTO {$this->tableName} (
                query_text,
                query_corrected,
                results_count,
                execution_time_ms,
                cache_hit,
                user_agent,
                ip_address,
                filters_used,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $data['query'] ?? '',
            $data['corrected_query'] ?? null,
            $data['results_count'] ?? 0,
            $data['execution_time_ms'] ?? 0,
            $data['cache_hit'] ?? false,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            json_encode($data['filters'] ?? []),
        ]);
    }

    /**
     * Get popular queries
     *
     * @param int $limit
     * @param string $timeframe 24h, 7d, 30d, all
     * @return array
     */
    public function getPopularQueries(int $limit = 20, string $timeframe = '24h'): array
    {
        $whereClause = $this->getTimeframeWhere($timeframe);

        $sql = "
            SELECT
                query_text,
                COUNT(*) as search_count,
                AVG(results_count) as avg_results,
                AVG(execution_time_ms) as avg_time_ms,
                SUM(CASE WHEN cache_hit = 1 THEN 1 ELSE 0 END) as cache_hits,
                COUNT(*) as total_searches,
                ROUND(SUM(CASE WHEN cache_hit = 1 THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as cache_hit_rate
            FROM {$this->tableName}
            {$whereClause}
            GROUP BY query_text
            ORDER BY search_count DESC
            LIMIT ?
        ";

        $stmt = $this->pdo->prepare($sql);

        if ($whereClause) {
            $stmt->execute([$limit]);
        } else {
            $stmt->execute([$limit]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get zero-result queries (need attention)
     *
     * @param int $limit
     * @param string $timeframe
     * @return array
     */
    public function getZeroResultQueries(int $limit = 20, string $timeframe = '7d'): array
    {
        $whereClause = $this->getTimeframeWhere($timeframe);

        $sql = "
            SELECT
                query_text,
                COUNT(*) as attempt_count,
                MAX(created_at) as last_attempt,
                AVG(execution_time_ms) as avg_time_ms
            FROM {$this->tableName}
            WHERE results_count = 0
            {$whereClause}
            GROUP BY query_text
            ORDER BY attempt_count DESC, last_attempt DESC
            LIMIT ?
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get performance metrics
     *
     * @param string $timeframe
     * @return array
     */
    public function getPerformanceMetrics(string $timeframe = '24h'): array
    {
        $whereClause = $this->getTimeframeWhere($timeframe);

        $sql = "
            SELECT
                COUNT(*) as total_searches,
                AVG(execution_time_ms) as avg_time_ms,
                MIN(execution_time_ms) as min_time_ms,
                MAX(execution_time_ms) as max_time_ms,
                AVG(results_count) as avg_results,
                SUM(CASE WHEN cache_hit = 1 THEN 1 ELSE 0 END) as cache_hits,
                SUM(CASE WHEN cache_hit = 0 THEN 1 ELSE 0 END) as cache_misses,
                ROUND(SUM(CASE WHEN cache_hit = 1 THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as cache_hit_rate,
                SUM(CASE WHEN results_count = 0 THEN 1 ELSE 0 END) as zero_result_searches,
                ROUND(SUM(CASE WHEN results_count = 0 THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as zero_result_rate
            FROM {$this->tableName}
            {$whereClause}
        ";

        $stmt = $this->pdo->query($sql);
        $metrics = $stmt->fetch(PDO::FETCH_ASSOC);

        // Add performance grade
        $metrics['performance_grade'] = $this->calculatePerformanceGrade($metrics);

        return $metrics;
    }

    /**
     * Get search trends over time
     *
     * @param string $timeframe
     * @param string $interval hour, day, week
     * @return array
     */
    public function getSearchTrends(string $timeframe = '7d', string $interval = 'day'): array
    {
        $whereClause = $this->getTimeframeWhere($timeframe);

        $dateFormat = match($interval) {
            'hour' => '%Y-%m-%d %H:00:00',
            'day' => '%Y-%m-%d',
            'week' => '%Y-W%u',
            default => '%Y-%m-%d',
        };

        $sql = "
            SELECT
                DATE_FORMAT(created_at, '{$dateFormat}') as time_bucket,
                COUNT(*) as search_count,
                AVG(execution_time_ms) as avg_time_ms,
                AVG(results_count) as avg_results,
                SUM(CASE WHEN cache_hit = 1 THEN 1 ELSE 0 END) as cache_hits
            FROM {$this->tableName}
            {$whereClause}
            GROUP BY time_bucket
            ORDER BY time_bucket ASC
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get query correction statistics
     *
     * @param string $timeframe
     * @return array
     */
    public function getCorrectionStats(string $timeframe = '7d'): array
    {
        $whereClause = $this->getTimeframeWhere($timeframe);

        $sql = "
            SELECT
                COUNT(*) as total_searches,
                SUM(CASE WHEN query_corrected IS NOT NULL THEN 1 ELSE 0 END) as corrected_searches,
                ROUND(SUM(CASE WHEN query_corrected IS NOT NULL THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as correction_rate
            FROM {$this->tableName}
            {$whereClause}
        ";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get most common search patterns
     *
     * @param int $limit
     * @return array
     */
    public function getSearchPatterns(int $limit = 10): array
    {
        $sql = "
            SELECT
                SUBSTRING_INDEX(query_text, ' ', 1) as first_word,
                COUNT(*) as frequency,
                AVG(results_count) as avg_results
            FROM {$this->tableName}
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY first_word
            ORDER BY frequency DESC
            LIMIT ?
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate dashboard summary
     *
     * @return array
     */
    public function getDashboardSummary(): array
    {
        return [
            'metrics_24h' => $this->getPerformanceMetrics('24h'),
            'metrics_7d' => $this->getPerformanceMetrics('7d'),
            'popular_queries' => $this->getPopularQueries(10, '24h'),
            'zero_results' => $this->getZeroResultQueries(10, '7d'),
            'trends' => $this->getSearchTrends('7d', 'day'),
            'corrections' => $this->getCorrectionStats('7d'),
            'patterns' => $this->getSearchPatterns(10),
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Calculate performance grade
     *
     * @param array $metrics
     * @return string A-F
     */
    private function calculatePerformanceGrade(array $metrics): string
    {
        $score = 0;

        // Cache hit rate (40 points)
        $cacheRate = $metrics['cache_hit_rate'] ?? 0;
        $score += ($cacheRate / 100) * 40;

        // Average response time (30 points)
        $avgTime = $metrics['avg_time_ms'] ?? 1000;
        if ($avgTime < 10) {
            $score += 30;
        } elseif ($avgTime < 50) {
            $score += 25;
        } elseif ($avgTime < 100) {
            $score += 20;
        } elseif ($avgTime < 500) {
            $score += 10;
        }

        // Zero result rate (30 points - inverse)
        $zeroRate = $metrics['zero_result_rate'] ?? 0;
        $score += (1 - min($zeroRate / 100, 1)) * 30;

        // Convert to grade
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'F';
    }

    /**
     * Get WHERE clause for timeframe
     *
     * @param string $timeframe
     * @return string
     */
    private function getTimeframeWhere(string $timeframe): string
    {
        return match($timeframe) {
            '1h' => 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)',
            '24h' => 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)',
            '7d' => 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)',
            '30d' => 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)',
            'all' => '',
            default => 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)',
        };
    }

    /**
     * Ensure analytics table exists
     *
     * @return void
     */
    private function getTimeInterval(string $timeframe): string
    {
        return match($timeframe) {
            '1h' => '1 HOUR',
            '24h' => '24 HOUR',
            '7d' => '7 DAY',
            '30d' => '30 DAY',
            default => '24 HOUR',
        };
    }

    private function ensureTable(): void
    {
        // Table should already exist from parent app
        // Just verify it has required columns
        $sql = "SHOW TABLES LIKE '{$this->tableName}'";
        $stmt = $this->pdo->query($sql);

        if ($stmt->rowCount() === 0) {
            error_log("Warning: {$this->tableName} table does not exist");
        }
    }

    /**
     * Get cache performance statistics
     *
     * @param string $timeframe Time period (e.g., '24h', '7d')
     * @return array Cache statistics
     */
    public function getCacheStats(string $timeframe = '24h'): array
    {
        $interval = $this->getTimeInterval($timeframe);

        $query = "
            SELECT 
                COUNT(*) as total_searches,
                SUM(CASE WHEN cache_hit = 1 THEN 1 ELSE 0 END) as cache_hits,
                SUM(CASE WHEN cache_hit = 0 OR cache_hit IS NULL THEN 1 ELSE 0 END) as cache_misses,
                ROUND(AVG(CASE WHEN cache_hit = 1 THEN execution_time_ms ELSE NULL END), 2) as avg_cached_time,
                ROUND(AVG(CASE WHEN cache_hit = 0 OR cache_hit IS NULL THEN execution_time_ms ELSE NULL END), 2) as avg_uncached_time
            FROM {$this->tableName}
            WHERE timestamp >= NOW() - INTERVAL {$interval}
        ";

        $stmt = $this->pdo->query($query);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        $hitRate = ($stats['total_searches'] > 0) 
            ? round(($stats['cache_hits'] / $stats['total_searches']) * 100, 2)
            : 0;

        return [
            'total_searches' => (int)$stats['total_searches'],
            'cache_hits' => (int)$stats['cache_hits'],
            'cache_misses' => (int)$stats['cache_misses'],
            'hit_rate_percent' => $hitRate,
            'avg_cached_time_ms' => $stats['avg_cached_time'] ?? 0,
            'avg_uncached_time_ms' => $stats['avg_uncached_time'] ?? 0,
        ];
    }

    /**
     * Get failed/zero-result searches
     *
     * @param int $limit Maximum number to return
     * @param string $timeframe Time period
     * @return array Failed searches
     */
    public function getFailedSearches(int $limit = 10, string $timeframe = '24h'): array
    {
        $interval = $this->getTimeInterval($timeframe);

        $query = "
            SELECT 
                query_text,
                COUNT(*) as attempt_count,
                MAX(timestamp) as last_attempted,
                query_corrected
            FROM {$this->tableName}
            WHERE results_count = 0
                AND timestamp >= NOW() - INTERVAL {$interval}
            GROUP BY query_text, query_corrected
            ORDER BY attempt_count DESC, last_attempted DESC
            LIMIT :limit
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
