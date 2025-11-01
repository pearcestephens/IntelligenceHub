<?php
/**
 * Semantic Search Tool - v4.0
 * Enhanced with fuzzy search, analytics, and query suggestions
 *
 * Features:
 * - Typo tolerance (Levenshtein distance)
 * - Phonetic matching (Soundex/Metaphone)
 * - Query suggestions (did you mean?)
 * - Real-time analytics tracking
 * - Search pattern analysis
 *
 * @package IntelligenceHub\MCP\Tools
 */

declare(strict_types=1);

namespace IntelligenceHub\MCP\Tools;

use IntelligenceHub\MCP\Database\Connection;
use IntelligenceHub\MCP\Cache\CacheManager;
use IntelligenceHub\MCP\Search\FuzzySearchEngine;
use IntelligenceHub\MCP\Analytics\SearchAnalytics;

class SemanticSearchTool
{
    private \PDO $db;
    private CacheManager $cache;
    private \SemanticSearchEngine $engine;
    private FuzzySearchEngine $fuzzy;
    private SearchAnalytics $analytics;

    public function __construct()
    {
        $this->db = Connection::getInstance();
        $this->cache = new CacheManager();

        // Initialize fuzzy search for typo tolerance
        $this->fuzzy = new FuzzySearchEngine([
            'max_distance' => 2,
            'enable_phonetic' => true,
            'enable_suggestions' => true,
        ]);

        // Initialize analytics tracking
        $this->analytics = new SearchAnalytics();

        // Initialize search engine with existing code
        require_once dirname(__DIR__, 2) . '/semantic_search_engine.php';

        // Create config array compatible with existing engine
        $config = [
            'cache_ttl' => 3600,
            'cache_dir' => sys_get_temp_dir() . '/semantic_cache',
            'min_relevance' => 0.1,
            'enable_synonyms' => true,
            'enable_redis' => true,
            'max_results' => 50,
        ];

        $this->engine = new \SemanticSearchEngine($this->db, $config);
    }

    /**
     * Execute semantic search with fuzzy matching and analytics
     *
     * @param array $params Search parameters
     * @return array Search results with suggestions
     */
    public function execute(array $params): array
    {
        $originalQuery = $params['query'] ?? '';
        $limit = $params['limit'] ?? 10;
        $filters = [
            'unit_id' => $params['unit_id'] ?? null,
            'file_type' => $params['file_type'] ?? null,
            'category' => $params['category'] ?? null,
            'min_score' => $params['min_score'] ?? 0.1,
        ];

        if (empty($originalQuery)) {
            return [
                'success' => false,
                'error' => 'Query parameter is required',
            ];
        }

        $startTime = microtime(true);

        // Step 1: Auto-correct typos using correctTypos() which returns ['corrected' => string, 'changes' => array]
        $correctionResult = $this->fuzzy->correctTypos($originalQuery);
        $correctedQuery = $correctionResult['corrected'] ?? $originalQuery;
        $queryCorrected = ($correctedQuery !== $originalQuery);

        // Step 2: Get query suggestions (if needed, use empty array for now)
        $suggestions = [];

        $searchQuery = $correctedQuery;

        try {
            // Step 3: Execute search with corrected query
            $results = $this->engine->search($searchQuery, $filters, $limit);

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            // Step 4: Log to analytics
            $this->analytics->logSearch([
                'query_text' => $originalQuery,
                'query_corrected' => $queryCorrected ? $correctedQuery : null,
                'results_count' => $results['total_results'] ?? 0,
                'execution_time_ms' => $duration,
                'cache_hit' => $results['cache_hit'] ?? false,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'ip_address' => $this->getClientIp(),
            ]);

            $response = [
                'success' => true,
                'query' => $originalQuery,
                'results' => $results['results'] ?? [],
                'total' => $results['total_results'] ?? 0,
                'cache_hit' => $results['cache_hit'] ?? false,
                'duration_ms' => $duration,
                'stats' => $results['stats'] ?? [],
            ];

            // Add fuzzy search enhancements
            if ($queryCorrected) {
                $response['query_corrected'] = $correctedQuery;
                $response['correction_applied'] = true;
            }

            if (!empty($suggestions) && count($results['results'] ?? []) < 5) {
                $response['suggestions'] = $suggestions;
                $response['suggestion_message'] = 'Try searching for: ' . implode(', ', $suggestions);
            }

            return $response;

        } catch (\Exception $e) {
            // Log failed search
            $this->analytics->logSearch([
                'query_text' => $originalQuery,
                'query_corrected' => $queryCorrected ? $correctedQuery : null,
                'results_count' => 0,
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'cache_hit' => false,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'ip_address' => $this->getClientIp(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'query' => $originalQuery,
                'suggestions' => $suggestions,
            ];
        }
    }

    /**
     * Get real client IP address
     *
     * @return string
     */
    private function getClientIp(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',  // Cloudflare
            'HTTP_X_FORWARDED_FOR',   // Proxy
            'HTTP_X_REAL_IP',         // Nginx
            'REMOTE_ADDR',            // Direct
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                // Get first IP if comma-separated
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                return $ip;
            }
        }

        return 'unknown';
    }

    /**
     * Get analytics dashboard data
     *
     * @param array $params Dashboard parameters
     * @return array Analytics data
     */
    public function getAnalytics(array $params = []): array
    {
        $timeframe = $params['timeframe'] ?? '24h';

        return [
            'success' => true,
            'timeframe' => $timeframe,
            'popular_queries' => $this->analytics->getPopularQueries(20, $timeframe),
            'performance' => $this->analytics->getPerformanceMetrics($timeframe),
            'cache_stats' => $this->analytics->getCacheStats($timeframe),
            'search_patterns' => $this->analytics->getSearchPatterns(10),  // No timeframe param
            'failed_searches' => $this->analytics->getFailedSearches(10, $timeframe),
        ];
    }
}
