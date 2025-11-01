<?php

declare(strict_types=1);

/**
 * Knowledge API Endpoint - Manage knowledge base documents
 * 
 * Provides knowledge base operations:
 * - POST /search: Semantic search through documents
 * - POST /documents: Add new document
 * - GET /documents: List documents with pagination
 * - GET /documents/{id}: Get specific document
 * - DELETE /documents/{id}: Delete document
 * 
 * @package App
 * @author Production AI Agent System
 * @version 1.0.0
 */

require_once __DIR__ . '/../../../src/bootstrap.php';

use App\Agent;
use App\Config;
use App\Logger;
use App\Util\Validate;
use App\Util\Errors;
use App\Util\RateLimit;
use App\Util\SecurityHeaders;

// Set headers only in web context
if (php_sapi_name() !== 'cli') {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    SecurityHeaders::applyJson();
}

// Handle preflight OPTIONS request
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    // Initialize components
    $config = new Config();
    $logger = new Logger($config);
    $agent = new Agent($config, $logger);
    $agent->initialize();
    
    // Global rate limiting (per client/IP)
    if (RateLimit::shouldRateLimit()) {
        RateLimit::middleware();
    }
    
    // Determine method (CLI-friendly)
    $method = $_SERVER['REQUEST_METHOD'] ?? '';
    if (!$method) {
        $envMethod = getenv('METHOD');
        $method = ($envMethod && is_string($envMethod) && $envMethod !== '') ? strtoupper($envMethod) : (php_sapi_name() === 'cli' ? 'GET' : 'UNKNOWN');
    }

    // Parse action and document id from URL path or query parameters
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $pathParts = $requestUri ? explode('/', trim($requestUri, '/')) : [];
    $documentId = null;
    $action = null;

    if ($requestUri) {
        $apiIndex = array_search('knowledge.php', $pathParts);
        if ($apiIndex !== false) {
            if (isset($pathParts[$apiIndex + 1])) {
                $action = strtok($pathParts[$apiIndex + 1], '?'); // Remove query string
            }
            if (isset($pathParts[$apiIndex + 2])) {
                $documentId = strtok($pathParts[$apiIndex + 2], '?'); // Remove query string
            }
        }
        
        // If not found in path, check query parameters
        if (!$action && isset($_GET['action'])) {
            $action = $_GET['action'];
        }
        if (!$documentId && isset($_GET['id'])) {
            $documentId = $_GET['id'];
        }
    } elseif (php_sapi_name() === 'cli') {
        $action = getenv('ACTION') ?: null;
        $documentId = getenv('DOCUMENT_ID') ?: null;
    }

    $logger->info('Knowledge API request', [
        'method' => $method,
        'action' => $action,
        'document_id' => $documentId,
        'client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
    
    switch ($method) {
        case 'POST':
            // Read JSON body (supports CLI via STDIN/ENV)
            $raw = file_get_contents('php://input');
            if (($raw === '' || $raw === false) && php_sapi_name() === 'cli') {
                $raw = stream_get_contents(STDIN);
                if ($raw === '' || $raw === false) {
                    $raw = getenv('JSON') ?: '';
                }
            }
            $input = json_decode($raw, true);
            if (!is_array($input)) {
                throw Errors::validationError('Invalid JSON input');
            }
            
            if ($action === 'search') {
                // Search knowledge base
                $query = Validate::string($input['query'] ?? '', 'query', 1, 1000);
                $limit = (int)($input['limit'] ?? 10);
                $limit = Validate::integer($limit, 'limit', 1, 100);
                
                $result = $agent->searchKnowledge($query, $limit);
                
            } else {
                // Add new document
                $title = Validate::string($input['title'] ?? '', 'title', 1, 200);
                $content = Validate::string($input['content'] ?? '', 'content', 1, 1000000);
                $metadata = Validate::array($input['metadata'] ?? [], 'metadata');
                
                $result = $agent->addDocument($title, $content, $metadata);
            }
            break;
            
        case 'GET':
            if ($action === 'documents') {
                if ($documentId) {
                    // Get specific document
                    $document = $agent->getKnowledgeBase()->getDocument($documentId);
                    
                    if (!$document) {
                        throw Errors::validationError('Document not found: ' . $documentId);
                    }
                    
                    $result = [
                        'success' => true,
                        'document' => $document
                    ];
                    
                } else {
                    // List documents with pagination
                    // Support CLI env overrides
                    $limit = (int)($_GET['limit'] ?? getenv('LIMIT') ?: 20);
                    $offset = (int)($_GET['offset'] ?? getenv('OFFSET') ?: 0);

                    $limit = Validate::integer($limit, 'limit', 1, 100);
                    $offset = Validate::integer($offset, 'offset', 0, PHP_INT_MAX);
                    
                    $documents = $agent->getKnowledgeBase()->listDocuments($limit, $offset);
                    
                    $result = [
                        'success' => true,
                        'documents' => $documents,
                        'pagination' => [
                            'limit' => $limit,
                            'offset' => $offset,
                            'count' => count($documents)
                        ]
                    ];
                }
                
            } else {
                // Get knowledge base statistics
                $stats = $agent->getKnowledgeBase()->getStatistics();
                
                $result = [
                    'success' => true,
                    'statistics' => $stats
                ];
            }
            break;
            
        case 'DELETE':
            if ($action === 'documents' && $documentId) {
                // Delete document
                $success = $agent->getKnowledgeBase()->deleteDocument($documentId);
                
                if (!$success) {
                    throw Errors::processingError('Failed to delete document');
                }
                
                $result = [
                    'success' => true,
                    'message' => 'Document deleted successfully'
                ];
                
            } else {
                throw Errors::validationError('Document ID required for deletion');
            }
            break;
            
        default:
            throw Errors::validationError('Method not allowed: ' . $method);
    }
    
    http_response_code(200);
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
    // Clean up
    $agent->shutdown();
    
} catch (Exception $e) {
    $errorCode = 500;
    $errorType = 'internal_error';
    
    // Determine appropriate error code
    if (strpos($e->getMessage(), 'validation') !== false) {
        $errorCode = 400;
        $errorType = 'validation_error';
    } elseif (strpos($e->getMessage(), 'not found') !== false) {
        $errorCode = 404;
        $errorType = 'not_found_error';
    } elseif (strpos($e->getMessage(), 'Method not allowed') !== false) {
        $errorCode = 405;
        $errorType = 'method_not_allowed';
    } elseif (strpos($e->getMessage(), 'Knowledge base is disabled') !== false) {
        $errorCode = 501;
        $errorType = 'feature_disabled';
    }
    
    // Log error
    if (isset($logger)) {
        $logger->error('Knowledge API error', [
            'error' => $e->getMessage(),
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
            'action' => $action ?? null,
            'document_id' => $documentId ?? null,
            'type' => $errorType,
            'code' => $errorCode
        ]);
    }
    
    // Return error response
    http_response_code($errorCode);
    
    $errorResponse = [
        'success' => false,
        'error' => [
            'type' => $errorType,
            'message' => $e->getMessage(),
            'code' => $errorCode
        ],
        'timestamp' => date('c')
    ];
    
    echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

// Ensure output is flushed
if (ob_get_level()) {
    ob_end_flush();
}
flush();