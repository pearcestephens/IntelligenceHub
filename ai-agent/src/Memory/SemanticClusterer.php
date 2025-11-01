<?php

declare(strict_types=1);

namespace App\Memory;

use App\Logger;
use App\RedisClient;
use App\DB;
use Exception;

/**
 * Semantic Clusterer
 * 
 * Groups related memories by topic using semantic similarity:
 * - Cluster conversations by theme
 * - Hierarchical clustering (broad → specific)
 * - Fast cluster-aware retrieval
 * - Automatic tagging from clusters
 * 
 * Makes "Find all conversations about X" instant
 * 
 * @package App\Memory
 * @author Feature Enhancement Phase 3
 */
class SemanticClusterer
{
    private Logger $logger;
    private ?RedisClient $redis;
    private ?Embeddings $embeddings;
    private array $config;

    public function __construct(
        Logger $logger,
        ?RedisClient $redis = null,
        ?Embeddings $embeddings = null,
        array $config = []
    ) {
        $this->logger = $logger;
        $this->redis = $redis;
        $this->embeddings = $embeddings;
        $this->config = array_merge([
            'min_cluster_size' => 3,
            'max_clusters' => 50,
            'similarity_threshold' => 0.7,
            'auto_tag' => true
        ], $config);
    }

    /**
     * Cluster all conversations
     */
    public function clusterConversations(): ClusterResult
    {
        $startTime = microtime(true);

        $this->logger->info('Starting conversation clustering');

        try {
            // Get all conversations with embeddings
            $conversations = $this->getConversationsWithEmbeddings();

            if (count($conversations) < $this->config['min_cluster_size']) {
                return new ClusterResult(false, [], 'Not enough conversations to cluster');
            }

            // Perform clustering
            $clusters = $this->performClustering($conversations);

            // Label clusters
            $labeledClusters = $this->labelClusters($clusters);

            // Store clusters
            $this->storeClusters($labeledClusters);

            // Auto-tag conversations
            if ($this->config['auto_tag']) {
                $this->autoTagConversations($labeledClusters);
            }

            $executionTime = microtime(true) - $startTime;

            $this->logger->info('Clustering completed', [
                'clusters_found' => count($labeledClusters),
                'conversations_clustered' => count($conversations),
                'execution_time' => $executionTime
            ]);

            return new ClusterResult(
                true,
                $labeledClusters,
                'Clustering successful',
                [
                    'total_conversations' => count($conversations),
                    'total_clusters' => count($labeledClusters),
                    'execution_time' => $executionTime
                ]
            );

        } catch (Exception $e) {
            $this->logger->error('Clustering failed', [
                'error' => $e->getMessage()
            ]);

            return new ClusterResult(false, [], 'Clustering failed: ' . $e->getMessage());
        }
    }

    /**
     * Find similar conversations
     */
    public function findSimilar(string $conversationId, int $limit = 10): array
    {
        $sql = "SELECT embedding FROM conversations WHERE uuid = ?";
        $row = DB::selectOne($sql, [$conversationId]);

        if (!$row || empty($row['embedding'])) {
            return [];
        }

        $embedding = json_decode($row['embedding'], true);

        // Find similar using cosine similarity
        $similar = $this->findSimilarByEmbedding($embedding, $limit + 1);

        // Remove self
        return array_filter($similar, fn($item) => $item['id'] !== $conversationId);
    }

    /**
     * Get conversations by cluster
     */
    public function getConversationsByCluster(string $clusterLabel): array
    {
        $sql = "SELECT uuid, title, created_at 
                FROM conversations 
                WHERE cluster_id IN (
                    SELECT id FROM conversation_clusters WHERE label = ?
                )
                ORDER BY created_at DESC";

        return DB::select($sql, [$clusterLabel]);
    }

    /**
     * Get all clusters
     */
    public function getAllClusters(): array
    {
        $sql = "SELECT id, label, conversation_count, keywords, created_at 
                FROM conversation_clusters 
                ORDER BY conversation_count DESC";

        return DB::select($sql);
    }

    /**
     * Get conversations with embeddings
     */
    private function getConversationsWithEmbeddings(): array
    {
        $sql = "SELECT uuid, title, embedding, created_at 
                FROM conversations 
                WHERE embedding IS NOT NULL 
                ORDER BY created_at DESC";

        $rows = DB::select($sql);

        return array_map(function ($row) {
            $row['embedding'] = json_decode($row['embedding'], true);
            return $row;
        }, $rows);
    }

    /**
     * Perform K-means clustering
     */
    private function performClustering(array $conversations): array
    {
        $embeddings = array_column($conversations, 'embedding');
        $k = min($this->config['max_clusters'], (int)(count($conversations) / $this->config['min_cluster_size']));

        // Simple K-means implementation
        $centroids = $this->initializeCentroids($embeddings, $k);
        $clusters = [];
        $maxIterations = 100;
        $iteration = 0;

        do {
            $oldClusters = $clusters;
            $clusters = array_fill(0, $k, []);

            // Assign to nearest centroid
            foreach ($conversations as $idx => $conversation) {
                $embedding = $conversation['embedding'];
                $nearestCluster = $this->findNearestCentroid($embedding, $centroids);
                $clusters[$nearestCluster][] = $conversation;
            }

            // Update centroids
            for ($i = 0; $i < $k; $i++) {
                if (!empty($clusters[$i])) {
                    $centroids[$i] = $this->calculateCentroid(
                        array_column($clusters[$i], 'embedding')
                    );
                }
            }

            $iteration++;

        } while ($clusters !== $oldClusters && $iteration < $maxIterations);

        // Filter small clusters
        return array_filter($clusters, fn($cluster) => 
            count($cluster) >= $this->config['min_cluster_size']
        );
    }

    /**
     * Initialize centroids using k-means++
     */
    private function initializeCentroids(array $embeddings, int $k): array
    {
        $centroids = [];
        
        // First centroid: random
        $centroids[] = $embeddings[array_rand($embeddings)];

        // Remaining centroids: choose points far from existing centroids
        for ($i = 1; $i < $k; $i++) {
            $distances = [];
            
            foreach ($embeddings as $embedding) {
                $minDist = PHP_FLOAT_MAX;
                
                foreach ($centroids as $centroid) {
                    $dist = 1 - $this->cosineSimilarity($embedding, $centroid);
                    $minDist = min($minDist, $dist);
                }
                
                $distances[] = $minDist;
            }

            // Choose next centroid with probability proportional to distance
            $sum = array_sum($distances);
            $rand = mt_rand() / mt_getrandmax() * $sum;
            $cumSum = 0;

            foreach ($distances as $idx => $dist) {
                $cumSum += $dist;
                if ($cumSum >= $rand) {
                    $centroids[] = $embeddings[$idx];
                    break;
                }
            }
        }

        return $centroids;
    }

    /**
     * Find nearest centroid
     */
    private function findNearestCentroid(array $embedding, array $centroids): int
    {
        $maxSimilarity = -1;
        $nearest = 0;

        foreach ($centroids as $idx => $centroid) {
            $similarity = $this->cosineSimilarity($embedding, $centroid);
            if ($similarity > $maxSimilarity) {
                $maxSimilarity = $similarity;
                $nearest = $idx;
            }
        }

        return $nearest;
    }

    /**
     * Calculate centroid (mean of embeddings)
     */
    private function calculateCentroid(array $embeddings): array
    {
        $dimensions = count($embeddings[0]);
        $centroid = array_fill(0, $dimensions, 0);

        foreach ($embeddings as $embedding) {
            for ($i = 0; $i < $dimensions; $i++) {
                $centroid[$i] += $embedding[$i];
            }
        }

        $count = count($embeddings);
        for ($i = 0; $i < $dimensions; $i++) {
            $centroid[$i] /= $count;
        }

        return $centroid;
    }

    /**
     * Cosine similarity
     */
    private function cosineSimilarity(array $a, array $b): float
    {
        $dotProduct = 0;
        $magnitudeA = 0;
        $magnitudeB = 0;

        for ($i = 0; $i < count($a); $i++) {
            $dotProduct += $a[$i] * $b[$i];
            $magnitudeA += $a[$i] * $a[$i];
            $magnitudeB += $b[$i] * $b[$i];
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        if ($magnitudeA == 0 || $magnitudeB == 0) {
            return 0;
        }

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }

    /**
     * Label clusters with descriptive names
     */
    private function labelClusters(array $clusters): array
    {
        $labeled = [];

        foreach ($clusters as $idx => $cluster) {
            // Extract keywords from conversation titles
            $titles = array_column($cluster, 'title');
            $keywords = $this->extractKeywords($titles);
            $label = $this->generateLabel($keywords);

            $labeled[] = [
                'id' => $idx + 1,
                'label' => $label,
                'keywords' => $keywords,
                'conversations' => $cluster,
                'size' => count($cluster)
            ];
        }

        return $labeled;
    }

    /**
     * Extract keywords from titles
     */
    private function extractKeywords(array $titles): array
    {
        $words = [];

        foreach ($titles as $title) {
            $titleWords = preg_split('/\s+/', strtolower($title));
            foreach ($titleWords as $word) {
                $word = preg_replace('/[^a-z0-9]/', '', $word);
                if (strlen($word) > 3) { // Skip short words
                    $words[] = $word;
                }
            }
        }

        // Count frequencies
        $frequencies = array_count_values($words);
        arsort($frequencies);

        // Return top 5
        return array_slice(array_keys($frequencies), 0, 5);
    }

    /**
     * Generate cluster label
     */
    private function generateLabel(array $keywords): string
    {
        if (empty($keywords)) {
            return 'Miscellaneous';
        }

        return ucfirst($keywords[0]) . ' & ' . ucfirst($keywords[1] ?? 'related');
    }

    /**
     * Store clusters in database
     */
    private function storeClusters(array $clusters): void
    {
        // Clear old clusters
        DB::execute("DELETE FROM conversation_clusters");

        // Insert new clusters
        foreach ($clusters as $cluster) {
            $sql = "INSERT INTO conversation_clusters (id, label, conversation_count, keywords, created_at) 
                    VALUES (?, ?, ?, ?, NOW())";

            DB::execute($sql, [
                $cluster['id'],
                $cluster['label'],
                $cluster['size'],
                json_encode($cluster['keywords'])
            ]);

            // Update conversations with cluster_id
            foreach ($cluster['conversations'] as $conversation) {
                DB::execute(
                    "UPDATE conversations SET cluster_id = ? WHERE uuid = ?",
                    [$cluster['id'], $conversation['uuid']]
                );
            }
        }
    }

    /**
     * Auto-tag conversations based on clusters
     */
    private function autoTagConversations(array $clusters): void
    {
        foreach ($clusters as $cluster) {
            $tags = array_slice($cluster['keywords'], 0, 3);

            foreach ($cluster['conversations'] as $conversation) {
                $this->addTags($conversation['uuid'], $tags);
            }
        }
    }

    /**
     * Add tags to conversation
     */
    private function addTags(string $conversationId, array $tags): void
    {
        foreach ($tags as $tag) {
            $sql = "INSERT IGNORE INTO conversation_tags (conversation_id, tag) VALUES (?, ?)";
            DB::execute($sql, [$conversationId, $tag]);
        }
    }

    /**
     * Find similar by embedding
     */
    private function findSimilarByEmbedding(array $embedding, int $limit): array
    {
        $sql = "SELECT uuid AS id, title, embedding FROM conversations WHERE embedding IS NOT NULL";
        $rows = DB::select($sql);

        $similarities = [];

        foreach ($rows as $row) {
            $rowEmbedding = json_decode($row['embedding'], true);
            $similarity = $this->cosineSimilarity($embedding, $rowEmbedding);
            
            $similarities[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'similarity' => $similarity
            ];
        }

        // Sort by similarity
        usort($similarities, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        return array_slice($similarities, 0, $limit);
    }
}

/**
 * Cluster Result
 */
class ClusterResult
{
    private bool $success;
    private array $clusters;
    private string $message;
    private array $metadata;

    public function __construct(bool $success, array $clusters, string $message, array $metadata = [])
    {
        $this->success = $success;
        $this->clusters = $clusters;
        $this->message = $message;
        $this->metadata = $metadata;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getClusters(): array
    {
        return $this->clusters;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function getClusterCount(): int
    {
        return count($this->clusters);
    }
}
