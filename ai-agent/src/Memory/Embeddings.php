<?php

/**
 * Embeddings helper for knowledge base and semantic operations
 * Handles OpenAI embeddings with caching and batch processing
 *
 * @package App\Memory
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App\Memory;

use App\Config;
use App\OpenAI;
use App\RedisClient;
use App\Logger;
use App\Util\Ids;

class Embeddings
{
    private const CACHE_TTL = 86400; // 24 hours
    private const BATCH_SIZE = 50; // Max embeddings per batch
    private const MIN_TEXT_LENGTH = 10;
    private const MAX_TEXT_LENGTH = 8000;

    /**
     * Generate embedding for text with caching
     */
    public function __construct(?OpenAI $openai = null, ?RedisClient $redis = null, ?Logger $logger = null)
    {
        // No instance state required; provided for DI compatibility
    }

    /**
     * Generate embedding for text with caching
     */
    public static function embed(string $text, ?string $model = null): array
    {
        $model = $model ?? Config::get('EMBEDDINGS_MODEL');

        // Validate text length
        $textLength = strlen($text);
        if ($textLength < self::MIN_TEXT_LENGTH) {
            throw new \InvalidArgumentException('Text too short for embedding');
        }

        if ($textLength > self::MAX_TEXT_LENGTH) {
            $text = substr($text, 0, self::MAX_TEXT_LENGTH);
            Logger::warning('Text truncated for embedding', [
                'original_length' => $textLength,
                'truncated_length' => strlen($text)
            ]);
        }

        // Check cache first
        $cacheKey = self::getCacheKey($text, $model);
        $cached = RedisClient::get($cacheKey);

        if ($cached && is_array($cached)) {
            Logger::debug('Embedding retrieved from cache', [
                'text_length' => strlen($text),
                'model' => $model
            ]);
            return $cached;
        }

        try {
            // Generate embedding via OpenAI
            $startTime = microtime(true);
            $embedding = OpenAI::createEmbedding($text, $model);
            $duration = (microtime(true) - $startTime) * 1000;

            if (empty($embedding)) {
                throw new \RuntimeException('Empty embedding returned from OpenAI');
            }

            // Validate embedding dimensions
            $dimensions = count($embedding);
            $expectedDimensions = self::getExpectedDimensions($model);

            if ($dimensions !== $expectedDimensions) {
                Logger::warning('Unexpected embedding dimensions', [
                    'model' => $model,
                    'expected' => $expectedDimensions,
                    'actual' => $dimensions
                ]);
            }

            // Cache the embedding
            RedisClient::set($cacheKey, $embedding, self::CACHE_TTL);

            Logger::info('Embedding generated', [
                'text_length' => strlen($text),
                'model' => $model,
                'dimensions' => $dimensions,
                'duration_ms' => (int)$duration
            ]);

            return $embedding;
        } catch (\Throwable $e) {
            Logger::error('Embedding generation failed', [
                'text_length' => strlen($text),
                'model' => $model,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Generate embeddings for multiple texts (batch processing)
     */
    public static function embedBatch(array $texts, ?string $model = null): array
    {
        $model = $model ?? Config::get('EMBEDDINGS_MODEL');
        $embeddings = [];
        $uncachedTexts = [];
        $textMap = [];

        // Check cache for each text
        foreach ($texts as $index => $text) {
            if (strlen($text) < self::MIN_TEXT_LENGTH) {
                Logger::warning('Skipping text too short for embedding', [
                    'index' => $index,
                    'length' => strlen($text)
                ]);
                continue;
            }

            $cacheKey = self::getCacheKey($text, $model);
            $cached = RedisClient::get($cacheKey);

            if ($cached && is_array($cached)) {
                $embeddings[$index] = $cached;
            } else {
                $uncachedTexts[] = $text;
                $textMap[count($uncachedTexts) - 1] = $index;
            }
        }

        // Process uncached texts in batches
        if (!empty($uncachedTexts)) {
            $batches = array_chunk($uncachedTexts, self::BATCH_SIZE, true);

            foreach ($batches as $batch) {
                foreach ($batch as $batchIndex => $text) {
                    try {
                        $embedding = self::embed($text, $model);
                        $originalIndex = $textMap[$batchIndex];
                        $embeddings[$originalIndex] = $embedding;
                    } catch (\Throwable $e) {
                        Logger::error('Batch embedding failed for text', [
                            'batch_index' => $batchIndex,
                            'error' => $e->getMessage()
                        ]);
                        // Continue with other texts
                    }
                }

                // Small delay between batches to avoid rate limits
                if (count($batches) > 1) {
                    usleep(100000); // 100ms
                }
            }
        }

        Logger::info('Batch embedding completed', [
            'total_texts' => count($texts),
            'cached_hits' => count($texts) - count($uncachedTexts),
            'new_embeddings' => count($uncachedTexts),
            'successful' => count($embeddings)
        ]);

        return $embeddings;
    }

    /**
     * Calculate cosine similarity between embeddings
     */
    public static function cosineSimilarity(array $embedding1, array $embedding2): float
    {
        if (count($embedding1) !== count($embedding2)) {
            throw new \InvalidArgumentException('Embeddings must have the same dimensions');
        }

        $dotProduct = 0;
        $norm1 = 0;
        $norm2 = 0;

        for ($i = 0; $i < count($embedding1); $i++) {
            $dotProduct += $embedding1[$i] * $embedding2[$i];
            $norm1 += $embedding1[$i] * $embedding1[$i];
            $norm2 += $embedding2[$i] * $embedding2[$i];
        }

        $norm1 = sqrt($norm1);
        $norm2 = sqrt($norm2);

        if ($norm1 == 0 || $norm2 == 0) {
            return 0;
        }

        return $dotProduct / ($norm1 * $norm2);
    }

    /**
     * Find most similar texts using embeddings
     */
    public static function findSimilar(array $queryEmbedding, array $candidateEmbeddings, int $topK = 5): array
    {
        $similarities = [];

        foreach ($candidateEmbeddings as $id => $embedding) {
            $similarity = self::cosineSimilarity($queryEmbedding, $embedding);
            $similarities[] = [
                'id' => $id,
                'similarity' => $similarity
            ];
        }

        // Sort by similarity (descending)
        usort($similarities, function ($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });

        return array_slice($similarities, 0, $topK);
    }

    /**
     * Normalize embedding vector
     */
    public static function normalize(array $embedding): array
    {
        $norm = sqrt(array_sum(array_map(fn($x) => $x * $x, $embedding)));

        if ($norm == 0) {
            return $embedding;
        }

        return array_map(fn($x) => $x / $norm, $embedding);
    }

    /**
     * Convert embedding to binary format for storage
     */
    public static function toBinary(array $embedding): string
    {
        return pack('f*', ...$embedding);
    }

    /**
     * Convert binary format back to embedding array
     */
    public static function fromBinary(string $binary): array
    {
        return array_values(unpack('f*', $binary));
    }

    /**
     * Get embedding cache statistics
     */
    public static function getCacheStats(): array
    {
        $pattern = Config::get('REDIS_PREFIX') . 'embedding:*';

        try {
            $keys = RedisClient::connection()->keys($pattern);
            $totalKeys = count($keys);

            // Sample some keys to estimate sizes
            $sampleSize = min(10, $totalKeys);
            $sampleKeys = array_slice($keys, 0, $sampleSize);
            $totalSize = 0;

            foreach ($sampleKeys as $key) {
                $data = RedisClient::get(str_replace(Config::get('REDIS_PREFIX'), '', $key));
                if ($data) {
                    $totalSize += strlen(json_encode($data));
                }
            }

            $avgSize = $sampleSize > 0 ? $totalSize / $sampleSize : 0;

            return [
                'total_embeddings' => $totalKeys,
                'estimated_size_mb' => round(($totalKeys * $avgSize) / 1024 / 1024, 2),
                'average_size_bytes' => (int)$avgSize
            ];
        } catch (\Throwable $e) {
            Logger::error('Failed to get embedding cache stats', [
                'error' => $e->getMessage()
            ]);

            return [
                'total_embeddings' => 0,
                'estimated_size_mb' => 0,
                'average_size_bytes' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Clear embedding cache
     */
    public static function clearCache(): int
    {
        $pattern = Config::get('REDIS_PREFIX') . 'embedding:*';

        try {
            $keys = RedisClient::connection()->keys($pattern);
            $deleted = 0;

            foreach ($keys as $key) {
                if (RedisClient::connection()->del($key)) {
                    $deleted++;
                }
            }

            Logger::info('Embedding cache cleared', [
                'keys_deleted' => $deleted
            ]);

            return $deleted;
        } catch (\Throwable $e) {
            Logger::error('Failed to clear embedding cache', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get cache key for text and model
     */
    private static function getCacheKey(string $text, string $model): string
    {
        $hash = hash('sha256', $text . $model);
        return "embedding:{$model}:{$hash}";
    }

    /**
     * Get expected dimensions for model
     */
    private static function getExpectedDimensions(string $model): int
    {
        return match ($model) {
            'text-embedding-3-small' => 1536,
            'text-embedding-3-large' => 3072,
            'text-embedding-ada-002' => 1536,
            default => 1536
        };
    }

    /**
     * Validate embedding array
     */
    public static function validate(array $embedding, ?string $model = null): bool
    {
        if (empty($embedding)) {
            return false;
        }

        // Check if all values are numeric
        foreach ($embedding as $value) {
            if (!is_numeric($value)) {
                return false;
            }
        }

        // Check dimensions if model specified
        if ($model) {
            $expectedDims = self::getExpectedDimensions($model);
            if (count($embedding) !== $expectedDims) {
                return false;
            }
        }

        return true;
    }
}
