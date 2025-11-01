<?php

/**
 * Knowledge Base manager with document ingestion, chunking, and vector search
 * Provides semantic search capabilities for the AI agent
 *
 * @package App\Memory
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App\Memory;

use App\DB;
use App\RedisClient;
use App\Logger;
use App\Util\Ids;
use App\Util\Validate;

class KnowledgeBase
{
    private const MAX_CHUNK_SIZE = 1000;
    private const CHUNK_OVERLAP = 100;
    private const MIN_CHUNK_SIZE = 50;
    private const MAX_SEARCH_RESULTS = 20;
    private const SIMILARITY_THRESHOLD = 0.7;

    /**
     * Ensure Redis vector index exists for KB chunks
     */
    public function __construct(...$args)
    {
        // Static methods class; constructor included for DI compatibility
    }

    /**
     * Ensure Redis vector index exists for KB chunks
     */
    public static function ensureVectorIndex(): bool
    {
        try {
            return RedisClient::createVectorIndex('kb_chunks_idx', [
                'id'      => ['type' => 'TEXT'],
                'text'    => ['type' => 'TEXT'],
                'doc_id'  => ['type' => 'TEXT'],
                'title'   => ['type' => 'TEXT'],
                'uri'     => ['type' => 'TEXT'],
                'embedding' => [
                    'type' => 'VECTOR',
                    'options' => [
                        'FLAT', '6',
                        'TYPE', 'FLOAT32',
                        'DIM', '1536',
                        'DISTANCE_METRIC', 'COSINE'
                    ]
                ]
            ]);
        } catch (\Throwable $e) {
            Logger::error('Failed to ensure vector index', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Add document to knowledge base
     */
    public static function addDocument(
        string $title,
        string $content,
        string $type = 'document',
        ?array $metadata = null,
        ?string $source = null
    ): string {
        Validate::string($title, 1, 255);
        Validate::string($content, self::MIN_CHUNK_SIZE);
        Validate::string($type, 1, 50);

        $documentId = Ids::uuid();
        $createdAt = date('Y-m-d H:i:s');

        try {
            DB::transaction(function () use ($documentId, $title, $content, $type, $metadata, $source, $createdAt) {
                // Insert document record
                DB::execute(
                    'INSERT INTO kb_docs (id, title, content, type, metadata, source, created_at) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)',
                    [
                        $documentId,
                        $title,
                        $content,
                        $type,
                        $metadata ? json_encode($metadata) : null,
                        $source,
                        $createdAt
                    ]
                );

                // Generate and store chunks
                $chunks = self::createChunks($content);
                self::storeChunks($documentId, $chunks);

                Logger::info('Document added to knowledge base', [
                    'document_id' => $documentId,
                    'title' => $title,
                    'type' => $type,
                    'content_length' => strlen($content),
                    'chunks_count' => count($chunks)
                ]);
            });

            return $documentId;
        } catch (\Throwable $e) {
            Logger::error('Failed to add document to knowledge base', [
                'title' => $title,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Search knowledge base for relevant content
     */
    public static function search(
        string $query,
        int $limit = 5,
        ?string $type = null,
        ?float $minSimilarity = null
    ): array {
        Validate::string($query, 1);
        $minSimilarity = $minSimilarity ?? self::SIMILARITY_THRESHOLD;
        $limit = min($limit, self::MAX_SEARCH_RESULTS);

        try {
            // Generate query embedding
            $queryEmbedding = Embeddings::embed($query);

            // Perform vector search in Redis
            $vectorResults = self::performVectorSearch($queryEmbedding, $limit * 2, $minSimilarity);

            if (empty($vectorResults)) {
                return [];
            }

            // Get chunk details from database
            $chunkIds = array_column($vectorResults, 'id');
            $chunks = self::getChunksByIds($chunkIds, $type);

            // Merge results with similarity scores
            $results = [];
            foreach ($vectorResults as $vectorResult) {
                $chunkId = $vectorResult['id'];
                if (isset($chunks[$chunkId])) {
                    $chunk = $chunks[$chunkId];
                    $chunk['similarity'] = $vectorResult['similarity'];
                    $results[] = $chunk;
                }
            }

            // Sort by similarity and limit
            usort($results, fn($a, $b) => $b['similarity'] <=> $a['similarity']);
            $results = array_slice($results, 0, $limit);

            Logger::info('Knowledge base search completed', [
                'query_length' => strlen($query),
                'results_count' => count($results),
                'min_similarity' => $minSimilarity,
                'type_filter' => $type
            ]);

            return $results;
        } catch (\Throwable $e) {
            Logger::error('Knowledge base search failed', [
                'query' => substr($query, 0, 100),
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get document by ID
     */
    public static function getDocument(string $documentId): ?array
    {
        try {
            $document = DB::selectOne(
                'SELECT * FROM kb_docs WHERE id = ? AND deleted_at IS NULL',
                [$documentId]
            );

            if (!$document) {
                return null;
            }

            // Get chunks for this document
            $chunks = DB::select(
                'SELECT id, content, chunk_index, embedding_generated 
                 FROM kb_chunks 
                 WHERE document_id = ? AND deleted_at IS NULL 
                 ORDER BY chunk_index',
                [$documentId]
            );

            $document['chunks'] = $chunks;
            $document['metadata'] = $document['metadata'] ? json_decode($document['metadata'], true) : null;

            return $document;
        } catch (\Throwable $e) {
            Logger::error('Failed to get document', [
                'document_id' => $documentId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Update document
     */
    public static function updateDocument(
        string $documentId,
        ?string $title = null,
        ?string $content = null,
        ?string $type = null,
        ?array $metadata = null,
        ?string $source = null
    ): bool {
        try {
            $updates = [];
            $params = [];

            if ($title !== null) {
                Validate::string($title, 1, 255);
                $updates[] = 'title = ?';
                $params[] = $title;
            }

            if ($type !== null) {
                Validate::string($type, 1, 50);
                $updates[] = 'type = ?';
                $params[] = $type;
            }

            if ($metadata !== null) {
                $updates[] = 'metadata = ?';
                $params[] = json_encode($metadata);
            }

            if ($source !== null) {
                $updates[] = 'source = ?';
                $params[] = $source;
            }

            $contentUpdated = false;
            if ($content !== null) {
                Validate::string($content, self::MIN_CHUNK_SIZE);
                $updates[] = 'content = ?';
                $params[] = $content;
                $contentUpdated = true;
            }

            if (empty($updates)) {
                return true; // Nothing to update
            }

            $updates[] = 'updated_at = ?';
            $params[] = date('Y-m-d H:i:s');
            $params[] = $documentId;

            return DB::transaction(function () use ($documentId, $updates, $params, $content, $contentUpdated) {
                // Update document
                $result = DB::execute(
                    'UPDATE kb_docs SET ' . implode(', ', $updates) . ' WHERE id = ?',
                    $params
                );

                // If content was updated, regenerate chunks
                if ($contentUpdated && $content) {
                    // Delete old chunks
                    DB::execute(
                        'UPDATE kb_chunks SET deleted_at = ? WHERE document_id = ?',
                        [date('Y-m-d H:i:s'), $documentId]
                    );

                    // Remove from vector index
                    self::removeFromVectorIndex($documentId);

                    // Create new chunks
                    $chunks = self::createChunks($content);
                    self::storeChunks($documentId, $chunks);
                }

                Logger::info('Document updated', [
                    'document_id' => $documentId,
                    'content_updated' => $contentUpdated,
                    'fields_updated' => count($updates) - 1 // -1 for updated_at
                ]);

                return $result;
            });
        } catch (\Throwable $e) {
            Logger::error('Failed to update document', [
                'document_id' => $documentId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Delete document (soft delete)
     */
    public static function deleteDocument(string $documentId): bool
    {
        try {
            return DB::transaction(function () use ($documentId) {
                $now = date('Y-m-d H:i:s');

                // Soft delete document
                $docResult = DB::execute(
                    'UPDATE kb_docs SET deleted_at = ? WHERE id = ?',
                    [$now, $documentId]
                );

                // Soft delete chunks
                $chunkResult = DB::execute(
                    'UPDATE kb_chunks SET deleted_at = ? WHERE document_id = ?',
                    [$now, $documentId]
                );

                // Remove from vector index
                self::removeFromVectorIndex($documentId);

                Logger::info('Document deleted', [
                    'document_id' => $documentId
                ]);

                return $docResult && $chunkResult;
            });
        } catch (\Throwable $e) {
            Logger::error('Failed to delete document', [
                'document_id' => $documentId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * List documents with pagination
     */
    public static function listDocuments(
        int $page = 1,
        int $limit = 20,
        ?string $type = null,
        ?string $search = null
    ): array {
        $limit = min($limit, 100); // Max 100 per page
        $offset = ($page - 1) * $limit;

        try {
            $whereConditions = ['deleted_at IS NULL'];
            $params = [];

            if ($type) {
                $whereConditions[] = 'type = ?';
                $params[] = $type;
            }

            if ($search) {
                $whereConditions[] = '(title LIKE ? OR content LIKE ?)';
                $searchTerm = "%{$search}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }

            $whereClause = implode(' AND ', $whereConditions);

            // Get total count
            $totalQuery = "SELECT COUNT(*) as total FROM kb_docs WHERE {$whereClause}";
            $totalResult = DB::selectOne($totalQuery, $params);
            $total = $totalResult['total'] ?? 0;

            // Get documents
            $documentsQuery = "
                SELECT id, title, type, source, created_at, updated_at,
                       CHAR_LENGTH(content) as content_length
                FROM kb_docs 
                WHERE {$whereClause}
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?
            ";

            $params[] = $limit;
            $params[] = $offset;
            $documents = DB::select($documentsQuery, $params);

            // Get chunk counts for each document
            foreach ($documents as &$doc) {
                $chunkCount = DB::selectOne(
                    'SELECT COUNT(*) as count FROM kb_chunks WHERE document_id = ? AND deleted_at IS NULL',
                    [$doc['id']]
                );
                $doc['chunks_count'] = $chunkCount['count'] ?? 0;
            }

            return [
                'documents' => $documents,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'pages' => ceil($total / $limit)
                ]
            ];
        } catch (\Throwable $e) {
            Logger::error('Failed to list documents', [
                'page' => $page,
                'limit' => $limit,
                'type' => $type,
                'search' => $search,
                'error' => $e->getMessage()
            ]);

            return [
                'documents' => [],
                'pagination' => ['page' => 1, 'limit' => $limit, 'total' => 0, 'pages' => 0]
            ];
        }
    }

    /**
     * Get knowledge base statistics
     */
    public static function getStats(): array
    {
        try {
            $stats = DB::selectOne('
                SELECT 
                    COUNT(*) as total_documents,
                    COUNT(CASE WHEN type = "document" THEN 1 END) as documents,
                    COUNT(CASE WHEN type = "faq" THEN 1 END) as faqs,
                    COUNT(CASE WHEN type = "procedure" THEN 1 END) as procedures,
                    SUM(CHAR_LENGTH(content)) as total_content_length,
                    AVG(CHAR_LENGTH(content)) as avg_content_length
                FROM kb_docs 
                WHERE deleted_at IS NULL
            ');

            $chunkStats = DB::selectOne('
                SELECT 
                    COUNT(*) as total_chunks,
                    COUNT(CASE WHEN embedding_generated = 1 THEN 1 END) as chunks_with_embeddings,
                    AVG(CHAR_LENGTH(content)) as avg_chunk_length
                FROM kb_chunks 
                WHERE deleted_at IS NULL
            ');

            $embeddingStats = Embeddings::getCacheStats();

            return [
                'documents' => [
                    'total' => (int)($stats['total_documents'] ?? 0),
                    'by_type' => [
                        'document' => (int)($stats['documents'] ?? 0),
                        'faq' => (int)($stats['faqs'] ?? 0),
                        'procedure' => (int)($stats['procedures'] ?? 0)
                    ],
                    'content_length' => [
                        'total' => (int)($stats['total_content_length'] ?? 0),
                        'average' => (int)($stats['avg_content_length'] ?? 0)
                    ]
                ],
                'chunks' => [
                    'total' => (int)($chunkStats['total_chunks'] ?? 0),
                    'with_embeddings' => (int)($chunkStats['chunks_with_embeddings'] ?? 0),
                    'average_length' => (int)($chunkStats['avg_chunk_length'] ?? 0)
                ],
                'embeddings' => $embeddingStats
            ];
        } catch (\Throwable $e) {
            Logger::error('Failed to get knowledge base stats', [
                'error' => $e->getMessage()
            ]);

            return [
                'documents' => ['total' => 0, 'by_type' => [], 'content_length' => []],
                'chunks' => ['total' => 0, 'with_embeddings' => 0, 'average_length' => 0],
                'embeddings' => ['total_embeddings' => 0, 'estimated_size_mb' => 0]
            ];
        }
    }

    /**
     * Create text chunks from content
     */
    private static function createChunks(string $content): array
    {
        // Split by paragraphs first
        $paragraphs = preg_split('/\n\s*\n/', $content, -1, PREG_SPLIT_NO_EMPTY);

        $chunks = [];
        $currentChunk = '';

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);

            // If paragraph alone exceeds max size, split it
            if (strlen($paragraph) > self::MAX_CHUNK_SIZE) {
                // Add current chunk if not empty
                if (!empty($currentChunk)) {
                    $chunks[] = trim($currentChunk);
                    $currentChunk = '';
                }

                // Split long paragraph by sentences
                $sentences = preg_split('/[.!?]+/', $paragraph, -1, PREG_SPLIT_NO_EMPTY);

                foreach ($sentences as $sentence) {
                    $sentence = trim($sentence);
                    if (empty($sentence)) {
                        continue;
                    }

                    if (strlen($currentChunk . ' ' . $sentence) > self::MAX_CHUNK_SIZE) {
                        if (!empty($currentChunk)) {
                            $chunks[] = trim($currentChunk);
                            $currentChunk = $sentence;
                        } else {
                            // Single sentence too long, force split by words
                            $words = explode(' ', $sentence);
                            $wordChunk = '';

                            foreach ($words as $word) {
                                if (strlen($wordChunk . ' ' . $word) > self::MAX_CHUNK_SIZE) {
                                    if (!empty($wordChunk)) {
                                        $chunks[] = trim($wordChunk);
                                        $wordChunk = $word;
                                    } else {
                                        // Single word too long, take first part
                                        $chunks[] = substr($word, 0, self::MAX_CHUNK_SIZE);
                                        $wordChunk = '';
                                    }
                                } else {
                                    $wordChunk = empty($wordChunk) ? $word : $wordChunk . ' ' . $word;
                                }
                            }

                            if (!empty($wordChunk)) {
                                $currentChunk = $wordChunk;
                            }
                        }
                    } else {
                        $currentChunk = empty($currentChunk) ? $sentence : $currentChunk . ' ' . $sentence;
                    }
                }
            } else {
                // Normal paragraph, check if it fits in current chunk
                if (strlen($currentChunk . "\n\n" . $paragraph) > self::MAX_CHUNK_SIZE) {
                    if (!empty($currentChunk)) {
                        $chunks[] = trim($currentChunk);
                        $currentChunk = $paragraph;
                    } else {
                        $currentChunk = $paragraph;
                    }
                } else {
                    $currentChunk = empty($currentChunk) ? $paragraph : $currentChunk . "\n\n" . $paragraph;
                }
            }
        }

        // Add final chunk
        if (!empty($currentChunk)) {
            $chunks[] = trim($currentChunk);
        }

        // Filter out chunks that are too small
        $chunks = array_filter($chunks, fn($chunk) => strlen($chunk) >= self::MIN_CHUNK_SIZE);

        return array_values($chunks);
    }

    /**
     * Store chunks in database and generate embeddings
     */
    private static function storeChunks(string $documentId, array $chunks): void
    {
        foreach ($chunks as $index => $chunkContent) {
            $chunkId = Ids::uuid();

            // Insert chunk record
            DB::execute(
                'INSERT INTO kb_chunks (id, document_id, content, chunk_index, created_at) VALUES (?, ?, ?, ?, ?)',
                [$chunkId, $documentId, $chunkContent, $index, date('Y-m-d H:i:s')]
            );

            // Generate embedding asynchronously (or immediately based on config)
            try {
                $embedding = Embeddings::embed($chunkContent);

                // Store in Redis vector index
                self::addToVectorIndex($chunkId, $embedding, $documentId, $chunkContent);

                // Mark as generated
                DB::execute(
                    'UPDATE kb_chunks SET embedding_generated = 1 WHERE id = ?',
                    [$chunkId]
                );
            } catch (\Throwable $e) {
                Logger::error('Failed to generate embedding for chunk', [
                    'document_id' => $documentId,
                    'chunk_id' => $chunkId,
                    'chunk_index' => $index,
                    'error' => $e->getMessage()
                ]);
                // Continue with other chunks
            }
        }
    }

    /**
     * Perform vector search in Redis
     */
    private static function performVectorSearch(array $queryEmbedding, int $limit, float $minSimilarity): array
    {
        try {
            // Ensure vector index exists
            self::ensureVectorIndex();

            $results = RedisClient::vectorSearch(
                'kb_chunks_idx',
                $queryEmbedding,
                $limit,
                $minSimilarity
            );

            return array_map(function ($result) {
                return [
                    'id' => $result['id'],
                    'similarity' => $result['score']
                ];
            }, $results);
        } catch (\Throwable $e) {
            Logger::error('Vector search failed', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Add chunk to vector index
     */
    private static function addToVectorIndex(string $chunkId, array $embedding, string $documentId, string $content): void
    {
        try {
            RedisClient::vectorAdd('kb_chunks_idx', $chunkId, $embedding, [
                'doc_id' => $documentId,
                'text' => substr($content, 0, 200) // First 200 chars for UI display
            ]);
        } catch (\Throwable $e) {
            Logger::error('Failed to add to vector index', [
                'chunk_id' => $chunkId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove document chunks from vector index
     */
    private static function removeFromVectorIndex(string $documentId): void
    {
        try {
            // Get all chunk IDs for this document
            $chunks = DB::select(
                'SELECT id FROM kb_chunks WHERE document_id = ?',
                [$documentId]
            );

            foreach ($chunks as $chunk) {
                RedisClient::vectorDelete('kb_chunks_idx', $chunk['id']);
            }
        } catch (\Throwable $e) {
            Logger::error('Failed to remove from vector index', [
                'document_id' => $documentId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get chunks by IDs with document information
     */
    private static function getChunksByIds(array $chunkIds, ?string $typeFilter = null): array
    {
        if (empty($chunkIds)) {
            return [];
        }

        $placeholders = str_repeat('?,', count($chunkIds) - 1) . '?';
        $params = $chunkIds;

        $typeCondition = '';
        if ($typeFilter) {
            $typeCondition = 'AND d.type = ?';
            $params[] = $typeFilter;
        }

        $chunks = DB::select("
            SELECT 
                c.id,
                c.content,
                c.chunk_index,
                d.id as document_id,
                d.title as document_title,
                d.type as document_type,
                d.source as document_source
            FROM kb_chunks c
            JOIN kb_docs d ON c.document_id = d.id
            WHERE c.id IN ({$placeholders}) 
            AND c.deleted_at IS NULL 
            AND d.deleted_at IS NULL
            {$typeCondition}
        ", $params);

        // Index by chunk ID
        $result = [];
        foreach ($chunks as $chunk) {
            $result[$chunk['id']] = $chunk;
        }

        return $result;
    }
}
