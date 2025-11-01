<?php

/**
 * Knowledge Base Tool for semantic search and document management
 * Provides AI agent access to the knowledge base with semantic search
 *
 * @package App\Tools
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App\Tools;

use App\Memory\KnowledgeBase;
use App\Logger;
use App\Util\Validate;
use App\Tools\Contracts\ToolContract;

class KnowledgeTool implements ToolContract
{
    public static function run(array $parameters, array $context = []): array
    {
        // Default action is search
        $action = $parameters['action'] ?? 'search';
        return match ($action) {
            'search' => self::search($parameters, $context),
            'get_document' => self::getDocument($parameters, $context),
            'list_documents' => self::listDocuments($parameters, $context),
            'add_document' => self::addDocument($parameters, $context),
            'get_stats' => self::getStats($parameters, $context),
            'find_similar' => self::findSimilar($parameters, $context),
            default => ['error' => 'Unknown action', 'error_type' => 'InvalidAction', 'action' => $action]
        };
    }

    public static function spec(): array
    {
        return [
            'name' => 'knowledge_tool',
            'description' => 'Search and manage knowledge base content',
            'category' => 'knowledge',
            'internal' => false,
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'action' => ['type' => 'string', 'enum' => ['search','get_document','list_documents','add_document','get_stats','find_similar']],
                    'query' => ['type' => 'string'],
                    'limit' => ['type' => 'integer'],
                    'type' => ['type' => 'string'],
                    'document_id' => ['type' => 'string'],
                    'title' => ['type' => 'string'],
                    'content' => ['type' => 'string'],
                    'metadata' => ['type' => 'object'],
                    'source' => ['type' => 'string'],
                    'min_similarity' => ['type' => 'number']
                ],
                'required' => []
            ],
            'safety' => [
                'timeout' => 30,
                'rate_limit' => 20
            ]
        ];
    }
    /**
     * Search the knowledge base
     */
    public static function search(array $parameters, array $context = []): array
    {
        $query = $parameters['query'] ?? '';
        $limit = $parameters['limit'] ?? 5;
        $type = $parameters['type'] ?? null;
        $minSimilarity = $parameters['min_similarity'] ?? null;

        Validate::string($query, 1, 500);

        try {
            Logger::info('Knowledge base search initiated', [
                'query_length' => strlen($query),
                'limit' => $limit,
                'type_filter' => $type,
                'min_similarity' => $minSimilarity
            ]);

            $results = KnowledgeBase::search($query, $limit, $type, $minSimilarity);

            // Format results for AI consumption
            $formattedResults = array_map(function ($result) {
                return [
                    'content' => $result['content'],
                    'document_title' => $result['document_title'],
                    'document_type' => $result['document_type'],
                    'document_source' => $result['document_source'],
                    'similarity_score' => round($result['similarity'], 3),
                    'chunk_index' => $result['chunk_index'],
                    'relevance' => self::categorizeRelevance($result['similarity'])
                ];
            }, $results);

            return [
                'results' => $formattedResults,
                'total_found' => count($formattedResults),
                'search_query' => $query,
                'search_summary' => self::generateSearchSummary($query, $formattedResults)
            ];
        } catch (\Throwable $e) {
            Logger::error('Knowledge base search failed', [
                'query' => substr($query, 0, 100),
                'error' => $e->getMessage()
            ]);

            return [
                'error' => 'Knowledge base search failed: ' . $e->getMessage(),
                'error_type' => 'SearchError',
                'results' => [],
                'total_found' => 0
            ];
        }
    }

    /**
     * Get document by ID with full content
     */
    public static function getDocument(array $parameters, array $context = []): array
    {
        $documentId = $parameters['document_id'] ?? '';

        Validate::string($documentId, 1);

        try {
            $document = KnowledgeBase::getDocument($documentId);

            if (!$document) {
                return [
                    'error' => 'Document not found',
                    'error_type' => 'NotFound',
                    'document_id' => $documentId
                ];
            }

            return [
                'document' => [
                    'id' => $document['id'],
                    'title' => $document['title'],
                    'content' => $document['content'],
                    'type' => $document['type'],
                    'source' => $document['source'],
                    'metadata' => $document['metadata'],
                    'created_at' => $document['created_at'],
                    'updated_at' => $document['updated_at'],
                    'chunk_count' => count($document['chunks']),
                    'content_length' => strlen($document['content'])
                ]
            ];
        } catch (\Throwable $e) {
            Logger::error('Failed to get document', [
                'document_id' => $documentId,
                'error' => $e->getMessage()
            ]);

            return [
                'error' => 'Failed to retrieve document: ' . $e->getMessage(),
                'error_type' => 'DocumentError',
                'document_id' => $documentId
            ];
        }
    }

    /**
     * List documents with filtering
     */
    public static function listDocuments(array $parameters, array $context = []): array
    {
        $page = $parameters['page'] ?? 1;
        $limit = $parameters['limit'] ?? 20;
        $type = $parameters['type'] ?? null;
        $search = $parameters['search'] ?? null;

        try {
            $result = KnowledgeBase::listDocuments($page, $limit, $type, $search);

            // Format documents for AI consumption
            $formattedDocs = array_map(function ($doc) {
                return [
                    'id' => $doc['id'],
                    'title' => $doc['title'],
                    'type' => $doc['type'],
                    'source' => $doc['source'],
                    'created_at' => $doc['created_at'],
                    'updated_at' => $doc['updated_at'],
                    'content_length' => (int)$doc['content_length'],
                    'chunks_count' => (int)$doc['chunks_count']
                ];
            }, $result['documents']);

            return [
                'documents' => $formattedDocs,
                'pagination' => $result['pagination'],
                'filters_applied' => [
                    'type' => $type,
                    'search' => $search
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
                'error' => 'Failed to list documents: ' . $e->getMessage(),
                'error_type' => 'ListError',
                'documents' => [],
                'pagination' => ['page' => 1, 'limit' => $limit, 'total' => 0, 'pages' => 0]
            ];
        }
    }

    /**
     * Add new document to knowledge base
     */
    public static function addDocument(array $parameters, array $context = []): array
    {
        $title = $parameters['title'] ?? '';
        $content = $parameters['content'] ?? '';
        $type = $parameters['type'] ?? 'document';
        $metadata = $parameters['metadata'] ?? null;
        $source = $parameters['source'] ?? null;

        Validate::string($title, 1, 255);
        Validate::string($content, 50);
        Validate::string($type, 1, 50);

        try {
            $documentId = KnowledgeBase::addDocument($title, $content, $type, $metadata, $source);

            Logger::info('Document added via knowledge tool', [
                'document_id' => $documentId,
                'title' => $title,
                'type' => $type,
                'content_length' => strlen($content)
            ]);

            return [
                'document_id' => $documentId,
                'title' => $title,
                'type' => $type,
                'content_length' => strlen($content),
                'success' => true,
                'message' => 'Document successfully added to knowledge base'
            ];
        } catch (\Throwable $e) {
            Logger::error('Failed to add document via knowledge tool', [
                'title' => $title,
                'type' => $type,
                'content_length' => strlen($content),
                'error' => $e->getMessage()
            ]);

            return [
                'error' => 'Failed to add document: ' . $e->getMessage(),
                'error_type' => 'AddError',
                'success' => false
            ];
        }
    }

    /**
     * Get knowledge base statistics
     */
    public static function getStats(array $parameters, array $context = []): array
    {
        try {
            $stats = KnowledgeBase::getStats();

            return [
                'statistics' => $stats,
                'summary' => [
                    'total_documents' => $stats['documents']['total'],
                    'total_chunks' => $stats['chunks']['total'],
                    'embedding_coverage' => $stats['chunks']['total'] > 0 ?
                        round(($stats['chunks']['with_embeddings'] / $stats['chunks']['total']) * 100, 1) : 0,
                    'most_common_type' => self::getMostCommonDocumentType($stats['documents']['by_type']),
                    'avg_document_size' => $stats['documents']['content_length']['average'] ?? 0
                ]
            ];
        } catch (\Throwable $e) {
            Logger::error('Failed to get knowledge base stats', [
                'error' => $e->getMessage()
            ]);

            return [
                'error' => 'Failed to retrieve statistics: ' . $e->getMessage(),
                'error_type' => 'StatsError',
                'statistics' => []
            ];
        }
    }

    /**
     * Search for similar content (semantic similarity)
     */
    public static function findSimilar(array $parameters, array $context = []): array
    {
        $documentId = $parameters['document_id'] ?? '';
        $limit = $parameters['limit'] ?? 5;
        $minSimilarity = $parameters['min_similarity'] ?? 0.7;

        Validate::string($documentId, 1);

        try {
            // Get the source document
            $sourceDoc = KnowledgeBase::getDocument($documentId);
            if (!$sourceDoc) {
                return [
                    'error' => 'Source document not found',
                    'error_type' => 'NotFound',
                    'document_id' => $documentId
                ];
            }

            // Use the document content as search query
            $searchQuery = substr($sourceDoc['content'], 0, 500); // First 500 chars

            $results = KnowledgeBase::search($searchQuery, $limit + 1, null, $minSimilarity);

            // Remove the source document from results
            $similarDocs = array_filter($results, function ($result) use ($documentId) {
                return $result['document_id'] !== $documentId;
            });

            // Limit to requested number
            $similarDocs = array_slice($similarDocs, 0, $limit);

            return [
                'source_document' => [
                    'id' => $sourceDoc['id'],
                    'title' => $sourceDoc['title'],
                    'type' => $sourceDoc['type']
                ],
                'similar_documents' => array_map(function ($doc) {
                    return [
                        'document_id' => $doc['document_id'],
                        'document_title' => $doc['document_title'],
                        'document_type' => $doc['document_type'],
                        'similarity_score' => round($doc['similarity'], 3),
                        'content_preview' => substr($doc['content'], 0, 200) . '...'
                    ];
                }, $similarDocs),
                'total_similar' => count($similarDocs)
            ];
        } catch (\Throwable $e) {
            Logger::error('Failed to find similar documents', [
                'document_id' => $documentId,
                'error' => $e->getMessage()
            ]);

            return [
                'error' => 'Failed to find similar documents: ' . $e->getMessage(),
                'error_type' => 'SimilarityError',
                'similar_documents' => []
            ];
        }
    }

    /**
     * Categorize relevance based on similarity score
     */
    private static function categorizeRelevance(float $similarity): string
    {
        if ($similarity >= 0.9) {
            return 'very_high';
        }
        if ($similarity >= 0.8) {
            return 'high';
        }
        if ($similarity >= 0.7) {
            return 'medium';
        }
        if ($similarity >= 0.6) {
            return 'low';
        }
        return 'very_low';
    }

    /**
     * Generate a search summary
     */
    private static function generateSearchSummary(string $query, array $results): string
    {
        $count = count($results);

        if ($count === 0) {
            return "No relevant documents found for query: \"{$query}\"";
        }

        $typesRaw = array_column($results, 'document_type');
        $types = array_values(array_filter(array_unique(array_map('strval', $typesRaw)), static function ($v) {
            return $v !== '';
        }));
        $avgSimilarity = array_sum(array_column($results, 'similarity_score')) / $count;

        $summary = "Found {$count} relevant document" . ($count > 1 ? 's' : '') .
                  " for query: \"{$query}\". ";

        if ($types !== []) {
            $summary .= "Document types: " . implode(', ', $types) . ". ";
        }

        $summary .= "Average relevance: " . round($avgSimilarity, 2) . ".";

        return $summary;
    }

    /**
     * Get the most common document type
     */
    private static function getMostCommonDocumentType(array $typeStats): string
    {
        if (empty($typeStats)) {
            return 'none';
        }

        $maxCount = 0;
        $mostCommon = 'unknown';

        foreach ($typeStats as $type => $count) {
            if ($count > $maxCount) {
                $maxCount = $count;
                $mostCommon = $type;
            }
        }

        return $mostCommon;
    }
}
