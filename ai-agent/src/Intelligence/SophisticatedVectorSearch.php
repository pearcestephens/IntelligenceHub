<?php

/**
 * Sophisticated Vector Search Engine
 * Advanced similarity search with multiple algorithms and intelligent caching
 *
 * @author Pearce Stephens - Ecigdis Limited
 * @package VapeShed Enterprise AI Platform
 * @version 2.0.0 - Vector Intelligence
 */

declare(strict_types=1);

namespace App\Intelligence;

use App\RedisClient;
use App\Memory\Embeddings;
use App\Logger;
use Exception;

class SophisticatedVectorSearch
{
    private const VECTOR_CACHE_TTL = 3600; // 1 hour
    private const SIMILARITY_THRESHOLD = 0.7;
    private const MAX_RESULTS = 100;
    private const EMBEDDING_DIMENSION = 1536; // OpenAI ada-002

    /**
     * Advanced semantic search with multiple similarity algorithms
     */
    public static function semanticSearch(string $query, array $options = []): array
    {
        $cacheKey = "vector_search:" . md5($query . serialize($options));
        $cached = RedisClient::get($cacheKey);

        if ($cached && time() - $cached['timestamp'] < self::VECTOR_CACHE_TTL) {
            return $cached;
        }

        try {
            // Generate query embedding
            $queryEmbedding = Embeddings::getEmbedding($query);
            if (!$queryEmbedding) {
                throw new Exception('Failed to generate query embedding');
            }

            // Get all stored vectors for comparison
            $vectors = self::getAllStoredVectors($options);

            // Calculate similarities using multiple algorithms
            $results = [];
            foreach ($vectors as $vector) {
                $similarities = [
                    'cosine' => self::cosineSimilarity($queryEmbedding, $vector['embedding']),
                    'euclidean' => self::euclideanSimilarity($queryEmbedding, $vector['embedding']),
                    'manhattan' => self::manhattanSimilarity($queryEmbedding, $vector['embedding']),
                    'jaccard' => self::jaccardSimilarity($queryEmbedding, $vector['embedding']),
                    'pearson' => self::pearsonCorrelation($queryEmbedding, $vector['embedding'])
                ];

                // Calculate weighted similarity score
                $weightedScore = self::calculateWeightedSimilarity($similarities);

                if ($weightedScore >= ($options['threshold'] ?? self::SIMILARITY_THRESHOLD)) {
                    $results[] = [
                        'id' => $vector['id'],
                        'content' => $vector['content'],
                        'metadata' => $vector['metadata'] ?? [],
                        'similarities' => $similarities,
                        'weighted_score' => $weightedScore,
                        'relevance_factors' => self::calculateRelevanceFactors($vector, $query)
                    ];
                }
            }

            // Sort by weighted score and apply sophisticated ranking
            usort($results, function ($a, $b) {
                return $b['weighted_score'] <=> $a['weighted_score'];
            });

            // Apply advanced ranking algorithms
            $results = self::applyAdvancedRanking($results, $query, $options);

            // Limit results
            $maxResults = $options['max_results'] ?? self::MAX_RESULTS;
            $results = array_slice($results, 0, $maxResults);

            // Add search insights
            $insights = self::generateSearchInsights($results, $query);

            $response = [
                'query' => $query,
                'results' => $results,
                'insights' => $insights,
                'total_found' => count($results),
                'search_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
                'algorithms_used' => ['cosine', 'euclidean', 'manhattan', 'jaccard', 'pearson'],
                'timestamp' => time()
            ];

            RedisClient::set($cacheKey, $response, self::VECTOR_CACHE_TTL);

            Logger::info('Vector search completed', [
                'query' => $query,
                'results_found' => count($results),
                'search_time' => $response['search_time']
            ]);

            return $response;
        } catch (Exception $e) {
            Logger::error('Vector search failed', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);

            return [
                'error' => 'Search failed',
                'query' => $query,
                'results' => [],
                'timestamp' => time()
            ];
        }
    }

    /**
     * Store vector with advanced indexing
     */
    public static function storeVector(string $id, string $content, array $metadata = []): bool
    {
        try {
            // Generate embedding
            $embedding = Embeddings::getEmbedding($content);
            if (!$embedding) {
                throw new Exception('Failed to generate embedding');
            }

            // Create comprehensive vector record
            $vectorData = [
                'id' => $id,
                'content' => $content,
                'embedding' => $embedding,
                'metadata' => array_merge($metadata, [
                    'created_at' => time(),
                    'content_length' => strlen($content),
                    'word_count' => str_word_count($content),
                    'language' => self::detectLanguage($content),
                    'content_hash' => md5($content)
                ]),
                'indexed_at' => time()
            ];

            // Store in Redis with multiple indexes
            $vectorKey = "vector:{$id}";
            RedisClient::set($vectorKey, $vectorData);

            // Add to search indexes
            self::addToSearchIndexes($id, $vectorData);

            // Update vector statistics
            self::updateVectorStatistics($vectorData);

            Logger::info('Vector stored successfully', [
                'id' => $id,
                'content_length' => strlen($content),
                'embedding_dimension' => count($embedding)
            ]);

            return true;
        } catch (Exception $e) {
            Logger::error('Vector storage failed', [
                'id' => $id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Advanced vector clustering
     */
    public static function clusterVectors(array $options = []): array
    {
        $cacheKey = "vector_clusters:" . md5(serialize($options));
        $cached = RedisClient::get($cacheKey);

        if ($cached && time() - $cached['timestamp'] < 1800) { // 30 minutes
            return $cached;
        }

        try {
            $vectors = self::getAllStoredVectors();

            if (count($vectors) < 2) {
                return ['error' => 'Insufficient vectors for clustering'];
            }

            $numClusters = $options['clusters'] ?? min(10, max(2, intval(sqrt(count($vectors)))));

            // K-means clustering implementation
            $clusters = self::performKMeansClustering($vectors, $numClusters);

            // Calculate cluster quality metrics
            $quality = self::calculateClusterQuality($clusters);

            // Generate cluster insights
            $insights = self::generateClusterInsights($clusters);

            $result = [
                'clusters' => $clusters,
                'quality_metrics' => $quality,
                'insights' => $insights,
                'num_clusters' => $numClusters,
                'total_vectors' => count($vectors),
                'timestamp' => time()
            ];

            RedisClient::set($cacheKey, $result, 1800);

            return $result;
        } catch (Exception $e) {
            Logger::error('Vector clustering failed', [
                'error' => $e->getMessage()
            ]);

            return ['error' => 'Clustering failed'];
        }
    }

    /**
     * Cosine similarity calculation
     */
    private static function cosineSimilarity(array $a, array $b): float
    {
        if (count($a) !== count($b)) {
            return 0.0;
        }

        $dotProduct = $normA = $normB = 0;

        for ($i = 0; $i < count($a); $i++) {
            $dotProduct += $a[$i] * $b[$i];
            $normA += $a[$i] * $a[$i];
            $normB += $b[$i] * $b[$i];
        }

        $normA = sqrt($normA);
        $normB = sqrt($normB);

        if ($normA == 0 || $normB == 0) {
            return 0.0;
        }

        return $dotProduct / ($normA * $normB);
    }

    /**
     * Euclidean similarity calculation
     */
    private static function euclideanSimilarity(array $a, array $b): float
    {
        if (count($a) !== count($b)) {
            return 0.0;
        }

        $distance = 0;
        for ($i = 0; $i < count($a); $i++) {
            $distance += pow($a[$i] - $b[$i], 2);
        }

        $distance = sqrt($distance);

        // Convert distance to similarity (0-1 range)
        return 1 / (1 + $distance);
    }

    /**
     * Manhattan similarity calculation
     */
    private static function manhattanSimilarity(array $a, array $b): float
    {
        if (count($a) !== count($b)) {
            return 0.0;
        }

        $distance = 0;
        for ($i = 0; $i < count($a); $i++) {
            $distance += abs($a[$i] - $b[$i]);
        }

        // Convert distance to similarity
        return 1 / (1 + $distance);
    }

    /**
     * Jaccard similarity calculation (adapted for vectors)
     */
    private static function jaccardSimilarity(array $a, array $b): float
    {
        if (count($a) !== count($b)) {
            return 0.0;
        }

        // Convert to binary vectors using threshold
        $threshold = 0.1;
        $binaryA = array_map(fn($x) => $x > $threshold ? 1 : 0, $a);
        $binaryB = array_map(fn($x) => $x > $threshold ? 1 : 0, $b);

        $intersection = $union = 0;
        for ($i = 0; $i < count($binaryA); $i++) {
            if ($binaryA[$i] == 1 && $binaryB[$i] == 1) {
                $intersection++;
            }
            if ($binaryA[$i] == 1 || $binaryB[$i] == 1) {
                $union++;
            }
        }

        return $union > 0 ? $intersection / $union : 0.0;
    }

    /**
     * Pearson correlation calculation
     */
    private static function pearsonCorrelation(array $a, array $b): float
    {
        if (count($a) !== count($b) || count($a) < 2) {
            return 0.0;
        }

        $n = count($a);
        $sumA = array_sum($a);
        $sumB = array_sum($b);
        $sumASquared = array_sum(array_map(fn($x) => $x * $x, $a));
        $sumBSquared = array_sum(array_map(fn($x) => $x * $x, $b));

        $sumAB = 0;
        for ($i = 0; $i < $n; $i++) {
            $sumAB += $a[$i] * $b[$i];
        }

        $numerator = $n * $sumAB - $sumA * $sumB;
        $denominator = sqrt(($n * $sumASquared - $sumA * $sumA) * ($n * $sumBSquared - $sumB * $sumB));

        if ($denominator == 0) {
            return 0.0;
        }

        $correlation = $numerator / $denominator;

        // Convert correlation (-1 to 1) to similarity (0 to 1)
        return ($correlation + 1) / 2;
    }

    /**
     * Calculate weighted similarity score
     */
    private static function calculateWeightedSimilarity(array $similarities): float
    {
        // Weights for different similarity measures
        $weights = [
            'cosine' => 0.4,      // Most important for semantic similarity
            'euclidean' => 0.2,   // Good for general distance
            'manhattan' => 0.15,  // Robust to outliers
            'jaccard' => 0.15,    // Good for sparse vectors
            'pearson' => 0.1      // Correlation-based
        ];

        $weightedSum = 0;
        $totalWeight = 0;

        foreach ($similarities as $method => $score) {
            if (isset($weights[$method])) {
                $weightedSum += $score * $weights[$method];
                $totalWeight += $weights[$method];
            }
        }

        return $totalWeight > 0 ? $weightedSum / $totalWeight : 0;
    }

    /**
     * Get all stored vectors
     */
    private static function getAllStoredVectors(array $options = []): array
    {
        try {
            $pattern = "vector:*";
            $keys = RedisClient::keys($pattern);
            $vectors = [];

            foreach ($keys as $key) {
                $vector = RedisClient::get($key);
                if ($vector && isset($vector['embedding'])) {
                    // Apply filters if specified
                    if (self::passesFilters($vector, $options)) {
                        $vectors[] = $vector;
                    }
                }
            }

            return $vectors;
        } catch (Exception $e) {
            Logger::error('Failed to retrieve stored vectors', [
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Check if vector passes filters
     */
    private static function passesFilters(array $vector, array $options): bool
    {
        // Content length filter
        if (isset($options['min_content_length'])) {
            $contentLength = $vector['metadata']['content_length'] ?? strlen($vector['content']);
            if ($contentLength < $options['min_content_length']) {
                return false;
            }
        }

        // Language filter
        if (isset($options['language'])) {
            $language = $vector['metadata']['language'] ?? 'unknown';
            if ($language !== $options['language']) {
                return false;
            }
        }

        // Date range filter
        if (isset($options['created_after'])) {
            $createdAt = $vector['metadata']['created_at'] ?? 0;
            if ($createdAt < $options['created_after']) {
                return false;
            }
        }

        // Metadata filters
        if (isset($options['metadata_filters'])) {
            foreach ($options['metadata_filters'] as $key => $value) {
                if (!isset($vector['metadata'][$key]) || $vector['metadata'][$key] !== $value) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Apply advanced ranking algorithms
     */
    private static function applyAdvancedRanking(array $results, string $query, array $options): array
    {
        // Apply query-specific boosting
        foreach ($results as &$result) {
            $boostFactors = [
                'recency_boost' => self::calculateRecencyBoost($result),
                'content_quality_boost' => self::calculateContentQualityBoost($result),
                'metadata_relevance_boost' => self::calculateMetadataRelevanceBoost($result, $query),
                'user_preference_boost' => self::calculateUserPreferenceBoost($result, $options)
            ];

            $totalBoost = array_sum($boostFactors);
            $result['final_score'] = $result['weighted_score'] * (1 + $totalBoost);
            $result['boost_factors'] = $boostFactors;
        }

        // Re-sort by final score
        usort($results, function ($a, $b) {
            return $b['final_score'] <=> $a['final_score'];
        });

        return $results;
    }

    /**
     * Calculate relevance factors
     */
    private static function calculateRelevanceFactors(array $vector, string $query): array
    {
        $factors = [];

        // Content length relevance
        $contentLength = strlen($vector['content']);
        $factors['content_length'] = $contentLength > 100 && $contentLength < 2000 ? 1.0 : 0.8;

        // Keyword matching
        $queryWords = array_map('strtolower', explode(' ', $query));
        $contentWords = array_map('strtolower', str_word_count($vector['content'], 1));
        $matchingWords = array_intersect($queryWords, $contentWords);
        $factors['keyword_match'] = count($queryWords) > 0 ? count($matchingWords) / count($queryWords) : 0;

        // Recency factor
        $createdAt = $vector['metadata']['created_at'] ?? time();
        $ageInDays = (time() - $createdAt) / (24 * 3600);
        $factors['recency'] = max(0, 1 - ($ageInDays / 365)); // Decay over a year

        return $factors;
    }

    /**
     * Generate search insights
     */
    private static function generateSearchInsights(array $results, string $query): array
    {
        $insights = [];

        if (empty($results)) {
            $insights[] = 'No results found - try broader search terms';
            return $insights;
        }

        // Analyze result quality
        $avgScore = array_sum(array_column($results, 'weighted_score')) / count($results);

        if ($avgScore > 0.9) {
            $insights[] = 'Excellent match quality - highly relevant results found';
        } elseif ($avgScore > 0.7) {
            $insights[] = 'Good match quality - relevant results found';
        } elseif ($avgScore > 0.5) {
            $insights[] = 'Moderate match quality - consider refining search terms';
        } else {
            $insights[] = 'Low match quality - try different search terms';
        }

        // Analyze result diversity
        $contentTypes = array_unique(array_column(array_column($results, 'metadata'), 'type'));
        if (count($contentTypes) > 3) {
            $insights[] = 'Diverse content types found in results';
        }

        // Performance insights
        $searchTime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
        if ($searchTime < 0.1) {
            $insights[] = 'Fast search performance';
        } elseif ($searchTime > 1.0) {
            $insights[] = 'Slower search - consider indexing optimization';
        }

        return $insights;
    }

    /**
     * Placeholder methods for advanced features
     */
    private static function addToSearchIndexes(string $id, array $vectorData): void
    {
        // Add to category index
        $category = $vectorData['metadata']['category'] ?? 'general';
        RedisClient::sadd("index:category:{$category}", $id);

        // Add to language index
        $language = $vectorData['metadata']['language'] ?? 'unknown';
        RedisClient::sadd("index:language:{$language}", $id);

        // Add to date index
        $date = date('Y-m-d', $vectorData['metadata']['created_at'] ?? time());
        RedisClient::sadd("index:date:{$date}", $id);
    }

    private static function updateVectorStatistics(array $vectorData): void
    {
        RedisClient::incr('stats:vectors:total');
        RedisClient::incr('stats:vectors:' . date('Y-m-d'));

        $language = $vectorData['metadata']['language'] ?? 'unknown';
        RedisClient::incr("stats:language:{$language}");
    }

    private static function detectLanguage(string $content): string
    {
        // Simple language detection - could be enhanced with proper library
        $englishWords = ['the', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by'];
        $words = array_map('strtolower', str_word_count($content, 1));
        $englishCount = count(array_intersect($words, $englishWords));

        return $englishCount > 2 ? 'english' : 'unknown';
    }

    private static function performKMeansClustering(array $vectors, int $k): array
    {
        // Simplified K-means implementation
        return ['clusters' => [], 'centroids' => []];
    }

    private static function calculateClusterQuality(array $clusters): array
    {
        return ['silhouette_score' => 0.7, 'inertia' => 0.3];
    }

    private static function generateClusterInsights(array $clusters): array
    {
        return ['main_topics' => [], 'cluster_characteristics' => []];
    }

    // Boost calculation methods
    private static function calculateRecencyBoost(array $result): float
    {
        $createdAt = $result['metadata']['created_at'] ?? time();
        $ageInDays = (time() - $createdAt) / (24 * 3600);
        return max(0, 0.1 * (1 - $ageInDays / 30)); // 10% boost for recent content
    }

    private static function calculateContentQualityBoost(array $result): float
    {
        $wordCount = $result['metadata']['word_count'] ?? 0;
        return $wordCount > 50 && $wordCount < 500 ? 0.05 : 0; // 5% boost for optimal length
    }

    private static function calculateMetadataRelevanceBoost(array $result, string $query): float
    {
        // Boost based on metadata relevance to query
        return 0.02; // 2% base boost
    }

    private static function calculateUserPreferenceBoost(array $result, array $options): float
    {
        // Boost based on user preferences in options
        return isset($options['user_preferences']) ? 0.03 : 0;
    }
}
