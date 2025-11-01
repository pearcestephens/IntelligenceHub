<?php

/**
 * Knowledge Base Manager
 * Auto-indexes documentation and code with vector embeddings for semantic search
 *
 * @author Pearce Stephens - Ecigdis Limited
 * @package VapeShed Enterprise AI Platform
 * @version 2.0.0 - Knowledge Intelligence
 */

declare(strict_types=1);

namespace App\Knowledge;

use App\RedisClient;
use App\Memory\Embeddings;
use App\Intelligence\SophisticatedVectorSearch;
use App\Logger;
use App\Instrumentation\RequestMetrics;
use Exception;

class KnowledgeBase
{
    private const KB_DOCS_KEY = 'kb:documents';
    private const KB_INDEX_KEY = 'kb:index';
    private const KB_METRICS_KEY = 'kb:metrics';
    private const KB_CACHE_TTL = 3600; // 1 hour
    
    private const CHUNK_SIZE = 1000; // characters per chunk
    private const CHUNK_OVERLAP = 200; // overlap for context continuity
    
    /**
     * Index a document into the knowledge base
     */
    public static function indexDocument(string $filePath, array $metadata = []): array
    {
        $startTime = microtime(true);
        
        try {
            // Validate file exists and is readable
            if (!file_exists($filePath)) {
                throw new Exception("File not found: {$filePath}");
            }
            
            if (!is_readable($filePath)) {
                throw new Exception("File not readable: {$filePath}");
            }
            
            // Read and validate content
            $content = file_get_contents($filePath);
            if ($content === false) {
                throw new Exception("Failed to read file: {$filePath}");
            }
            
            if (strlen($content) === 0) {
                throw new Exception("File is empty: {$filePath}");
            }
            
            // Extract metadata from file
            $fileMetadata = [
                'file_path' => $filePath,
                'file_name' => basename($filePath),
                'file_size' => strlen($content),
                'file_type' => pathinfo($filePath, PATHINFO_EXTENSION),
                'indexed_at' => time(),
                'last_modified' => filemtime($filePath)
            ];
            
            // Merge with provided metadata
            $metadata = array_merge($fileMetadata, $metadata);
            
            // Split content into chunks for better retrieval
            $chunks = self::splitIntoChunks($content);
            
            $indexedChunks = [];
            $embeddingCount = 0;
            
            foreach ($chunks as $chunkIndex => $chunk) {
                // Generate unique ID for this chunk
                $chunkId = md5($filePath . ':' . $chunkIndex);
                
                // Generate embedding for chunk
                $embedding = Embeddings::embed($chunk);
                
                // Store chunk with embedding
                $chunkData = [
                    'id' => $chunkId,
                    'content' => $chunk,
                    'embedding' => $embedding,
                    'metadata' => array_merge($metadata, [
                        'chunk_index' => $chunkIndex,
                        'chunk_count' => count($chunks),
                        'chunk_size' => strlen($chunk)
                    ])
                ];
                
                // Store in Redis
                RedisClient::hset(self::KB_DOCS_KEY, $chunkId, json_encode($chunkData));
                
                // Add to search index
                RedisClient::zadd(self::KB_INDEX_KEY, time(), $chunkId);
                
                $indexedChunks[] = $chunkId;
                $embeddingCount++;
            }
            
            $duration = (microtime(true) - $startTime) * 1000;
            
            // Update metrics
            self::updateMetrics([
                'documents_indexed' => 1,
                'chunks_indexed' => count($indexedChunks),
                'embeddings_generated' => $embeddingCount,
                'total_characters' => strlen($content),
                'indexing_duration_ms' => $duration
            ]);
            
            Logger::info('Document indexed successfully', [
                'file_path' => $filePath,
                'chunks' => count($indexedChunks),
                'duration_ms' => round($duration, 2)
            ]);
            
            return [
                'success' => true,
                'file_path' => $filePath,
                'chunks_indexed' => count($indexedChunks),
                'chunk_ids' => $indexedChunks,
                'duration_ms' => round($duration, 2),
                'metadata' => $metadata
            ];
        } catch (Exception $e) {
            Logger::error('Document indexing failed', [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Index entire directory recursively
     */
    public static function indexDirectory(string $dirPath, array $options = []): array
    {
        $startTime = microtime(true);
        $results = [
            'success' => [],
            'failed' => [],
            'skipped' => []
        ];
        
        try {
            if (!is_dir($dirPath)) {
                throw new Exception("Directory not found: {$dirPath}");
            }
            
            // Allowed file extensions
            $allowedExtensions = $options['extensions'] ?? ['md', 'txt', 'php', 'json'];
            $recursive = $options['recursive'] ?? true;
            
            // Get all files
            $files = $recursive
                ? new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($dirPath, \RecursiveDirectoryIterator::SKIP_DOTS)
                )
                : new \DirectoryIterator($dirPath);
            
            foreach ($files as $file) {
                if ($file->isFile()) {
                    $filePath = $file->getPathname();
                    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                    
                    // Check if extension is allowed
                    if (!in_array($extension, $allowedExtensions)) {
                        $results['skipped'][] = [
                            'file' => $filePath,
                            'reason' => 'Extension not allowed'
                        ];
                        continue;
                    }
                    
                    // Index the document
                    $result = self::indexDocument($filePath);
                    
                    if ($result['success']) {
                        $results['success'][] = $result;
                    } else {
                        $results['failed'][] = $result;
                    }
                }
            }
            
            $duration = (microtime(true) - $startTime) * 1000;
            
            Logger::info('Directory indexing complete', [
                'directory' => $dirPath,
                'success_count' => count($results['success']),
                'failed_count' => count($results['failed']),
                'skipped_count' => count($results['skipped']),
                'duration_ms' => round($duration, 2)
            ]);
            
            return [
                'success' => true,
                'directory' => $dirPath,
                'results' => $results,
                'summary' => [
                    'indexed' => count($results['success']),
                    'failed' => count($results['failed']),
                    'skipped' => count($results['skipped']),
                    'duration_ms' => round($duration, 2)
                ]
            ];
        } catch (Exception $e) {
            Logger::error('Directory indexing failed', [
                'directory' => $dirPath,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'directory' => $dirPath,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Search knowledge base with relevance tuning
     */
    public static function search(string $query, array $options = []): array
    {
        $startTime = microtime(true);
        
        try {
            // Generate query embedding
            $queryEmbedding = Embeddings::embed($query);
            
            // Get all documents from KB
            $allDocs = RedisClient::hgetall(self::KB_DOCS_KEY);
            
            if (empty($allDocs)) {
                return [
                    'success' => true,
                    'query' => $query,
                    'results' => [],
                    'total_found' => 0,
                    'duration_ms' => 0
                ];
            }
            
            // Calculate similarities
            $similarities = [];
            foreach ($allDocs as $chunkId => $chunkJson) {
                $chunk = json_decode($chunkJson, true);
                
                if (!isset($chunk['embedding'])) {
                    continue;
                }
                
                // Calculate cosine similarity
                $similarity = self::cosineSimilarity($queryEmbedding, $chunk['embedding']);
                
                // Apply relevance boosting
                $boostedScore = self::applyRelevanceBoosts($similarity, $chunk, $query, $options);
                
                $similarities[] = [
                    'chunk_id' => $chunkId,
                    'content' => $chunk['content'],
                    'metadata' => $chunk['metadata'] ?? [],
                    'raw_similarity' => $similarity,
                    'boosted_score' => $boostedScore
                ];
            }
            
            // Sort by boosted score
            usort($similarities, function ($a, $b) {
                return $b['boosted_score'] <=> $a['boosted_score'];
            });
            
            // Limit results
            $maxResults = $options['max_results'] ?? 10;
            $threshold = $options['threshold'] ?? 0.7;
            
            $results = [];
            foreach (array_slice($similarities, 0, $maxResults) as $result) {
                if ($result['boosted_score'] >= $threshold) {
                    $results[] = $result;
                }
            }
            
            $duration = (microtime(true) - $startTime) * 1000;
            
            // Update search metrics
            self::updateSearchMetrics($query, count($results), $duration);
            
            return [
                'success' => true,
                'query' => $query,
                'results' => $results,
                'total_found' => count($results),
                'duration_ms' => round($duration, 2)
            ];
        } catch (Exception $e) {
            Logger::error('Knowledge base search failed', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'query' => $query,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Split content into overlapping chunks
     */
    private static function splitIntoChunks(string $content): array
    {
        $chunks = [];
        $length = strlen($content);
        $position = 0;
        
        while ($position < $length) {
            $chunkEnd = min($position + self::CHUNK_SIZE, $length);
            
            // Try to break at sentence boundary
            if ($chunkEnd < $length) {
                $lastPeriod = strrpos(substr($content, $position, self::CHUNK_SIZE), '.');
                if ($lastPeriod !== false && $lastPeriod > self::CHUNK_SIZE * 0.7) {
                    $chunkEnd = $position + $lastPeriod + 1;
                }
            }
            
            $chunk = substr($content, $position, $chunkEnd - $position);
            $chunks[] = trim($chunk);
            
            // Move position with overlap
            $position = $chunkEnd - self::CHUNK_OVERLAP;
        }
        
        return array_filter($chunks, fn($c) => strlen($c) >= 50);
    }
    
    /**
     * Calculate cosine similarity between two vectors
     */
    private static function cosineSimilarity(array $a, array $b): float
    {
        if (count($a) !== count($b)) {
            throw new Exception('Vector dimensions must match');
        }
        
        $dotProduct = 0.0;
        $magnitudeA = 0.0;
        $magnitudeB = 0.0;
        
        for ($i = 0; $i < count($a); $i++) {
            $dotProduct += $a[$i] * $b[$i];
            $magnitudeA += $a[$i] * $a[$i];
            $magnitudeB += $b[$i] * $b[$i];
        }
        
        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);
        
        if ($magnitudeA == 0 || $magnitudeB == 0) {
            return 0.0;
        }
        
        return $dotProduct / ($magnitudeA * $magnitudeB);
    }
    
    /**
     * Apply relevance boosting based on various factors
     */
    private static function applyRelevanceBoosts(float $baseSimilarity, array $chunk, string $query, array $options): float
    {
        $score = $baseSimilarity;
        
        // Keyword match boost
        $content = strtolower($chunk['content']);
        $queryLower = strtolower($query);
        $queryWords = preg_split('/\s+/', $queryLower);
        
        $matchCount = 0;
        foreach ($queryWords as $word) {
            if (strlen($word) > 3 && strpos($content, $word) !== false) {
                $matchCount++;
            }
        }
        
        if ($matchCount > 0) {
            $score *= (1 + 0.1 * $matchCount); // 10% boost per keyword match
        }
        
        // Recency boost (if metadata has timestamp)
        if (isset($chunk['metadata']['indexed_at'])) {
            $ageInDays = (time() - $chunk['metadata']['indexed_at']) / 86400;
            if ($ageInDays < 30) {
                $score *= 1.05; // 5% boost for recent documents
            }
        }
        
        // File type boost
        if (isset($chunk['metadata']['file_type'])) {
            $fileType = $chunk['metadata']['file_type'];
            $boosts = [
                'md' => 1.1,   // 10% boost for markdown docs
                'php' => 1.05, // 5% boost for code
                'txt' => 1.0
            ];
            $score *= $boosts[$fileType] ?? 1.0;
        }
        
        // Custom boosts from options
        if (isset($options['boost_fields'])) {
            foreach ($options['boost_fields'] as $field => $boost) {
                if (isset($chunk['metadata'][$field])) {
                    $score *= $boost;
                }
            }
        }
        
        return $score;
    }
    
    /**
     * Update knowledge base metrics
     */
    private static function updateMetrics(array $metrics): void
    {
        $currentMetrics = RedisClient::get(self::KB_METRICS_KEY) ?? [
            'kb_index_docs_total' => 0,
            'kb_index_chunks_total' => 0,
            'kb_embeddings_total' => 0,
            'kb_index_characters_total' => 0,
            'kb_index_duration_total_ms' => 0,
            'last_updated' => time()
        ];
        
        $currentMetrics['kb_index_docs_total'] += $metrics['documents_indexed'] ?? 0;
        $currentMetrics['kb_index_chunks_total'] += $metrics['chunks_indexed'] ?? 0;
        $currentMetrics['kb_embeddings_total'] += $metrics['embeddings_generated'] ?? 0;
        $currentMetrics['kb_index_characters_total'] += $metrics['total_characters'] ?? 0;
        $currentMetrics['kb_index_duration_total_ms'] += $metrics['indexing_duration_ms'] ?? 0;
        $currentMetrics['last_updated'] = time();
        
        RedisClient::set(self::KB_METRICS_KEY, $currentMetrics, 0);
    }
    
    /**
     * Update search metrics
     */
    private static function updateSearchMetrics(string $query, int $resultsCount, float $durationMs): void
    {
        $searchMetricsKey = 'kb:search_metrics';
        
        $metrics = RedisClient::get($searchMetricsKey) ?? [
            'total_searches' => 0,
            'total_results' => 0,
            'total_duration_ms' => 0,
            'recent_queries' => []
        ];
        
        $metrics['total_searches']++;
        $metrics['total_results'] += $resultsCount;
        $metrics['total_duration_ms'] += $durationMs;
        
        // Keep last 100 queries
        $metrics['recent_queries'][] = [
            'query' => $query,
            'results' => $resultsCount,
            'duration_ms' => round($durationMs, 2),
            'timestamp' => time()
        ];
        
        if (count($metrics['recent_queries']) > 100) {
            array_shift($metrics['recent_queries']);
        }
        
        RedisClient::set($searchMetricsKey, $metrics, 0);
    }
    
    /**
     * Get knowledge base metrics
     */
    public static function getMetrics(): array
    {
        $indexMetrics = RedisClient::get(self::KB_METRICS_KEY) ?? [];
        $searchMetrics = RedisClient::get('kb:search_metrics') ?? [];
        
        return [
            'index' => $indexMetrics,
            'search' => $searchMetrics,
            'timestamp' => time()
        ];
    }
    
    /**
     * Clear knowledge base (for re-indexing)
     */
    public static function clear(): bool
    {
        try {
            RedisClient::del(self::KB_DOCS_KEY);
            RedisClient::del(self::KB_INDEX_KEY);
            
            Logger::info('Knowledge base cleared');
            
            return true;
        } catch (Exception $e) {
            Logger::error('Failed to clear knowledge base', [
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
}
