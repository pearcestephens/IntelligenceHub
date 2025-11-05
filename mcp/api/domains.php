<?php

/**
 * Multi-Domain Management API
 *
 * Endpoints:
 * - GET /domains - List all domains
 * - GET /domains/{id}/stats - Get domain statistics
 * - POST /domains/switch - Switch conversation domain
 * - POST /domains/god-mode/enable - Enable GOD MODE
 * - POST /domains/god-mode/disable - Disable GOD MODE
 * - GET /domains/god-mode/overview - Get GOD MODE overview
 * - POST /domains/documents/add - Add document to domain
 * - DELETE /domains/documents/remove - Remove document from domain
 *
 * @package App\API
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Agent;
use App\Config;
use App\Logger;
use App\Memory\MultiDomain;
use App\Util\Validate;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

try {
    $config = new Config();
    $logger = new Logger($config);
    $agent = new Agent($config, $logger);
    $agent->initialize();

    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pathParts = explode('/', trim($path, '/'));

    // Get request body for POST/DELETE
    $input = [];
    if (in_array($method, ['POST', 'DELETE'])) {
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true) ?? [];
    }

    // Route handling
    switch ($method) {
        case 'GET':
            handleGet($pathParts, $agent, $logger);
            break;
        case 'POST':
            handlePost($pathParts, $input, $agent, $logger);
            break;
        case 'DELETE':
            handleDelete($pathParts, $input, $agent, $logger);
            break;
        default:
            sendError('Method not allowed', 405);
    }

} catch (\Throwable $e) {
    Logger::error('Domain API error', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    sendError($e->getMessage(), 500);
}

/**
 * Handle GET requests
 */
function handleGet(array $pathParts, Agent $agent, Logger $logger): void
{
    // GET /domains - List all domains
    if (end($pathParts) === 'domains' || end($pathParts) === 'api') {
        $domains = $agent->getAllDomains();
        sendSuccess([
            'domains' => $domains,
            'count' => count($domains)
        ]);
        return;
    }

    // GET /domains/{id}/stats - Domain statistics
    if (isset($pathParts[count($pathParts) - 1]) && $pathParts[count($pathParts) - 1] === 'stats') {
        $domainId = (int)$pathParts[count($pathParts) - 2];
        $stats = $agent->getDomainStats($domainId);
        sendSuccess(['stats' => $stats]);
        return;
    }

    // GET /domains/stats - All domain statistics
    if (end($pathParts) === 'stats') {
        $stats = $agent->getDomainStats();
        sendSuccess(['stats' => $stats]);
        return;
    }

    // GET /domains/god-mode/overview - GOD MODE overview
    if (in_array('god-mode', $pathParts) && end($pathParts) === 'overview') {
        $overview = $agent->getGodModeOverview();
        sendSuccess(['overview' => $overview]);
        return;
    }

    sendError('Endpoint not found', 404);
}

/**
 * Handle POST requests
 */
function handlePost(array $pathParts, array $input, Agent $agent, Logger $logger): void
{
    // POST /domains/switch - Switch domain
    if (end($pathParts) === 'switch') {
        Validate::required($input, ['conversation_id', 'domain_name']);

        $conversationId = $input['conversation_id'];
        $domainName = $input['domain_name'];

        Validate::uuid($conversationId);
        Validate::string($domainName, 1, 50);

        $success = $agent->switchDomain($conversationId, $domainName);

        if ($success) {
            $currentDomain = $agent->getCurrentDomain($conversationId);
            sendSuccess([
                'message' => "Switched to domain: {$domainName}",
                'current_domain' => $currentDomain
            ]);
        } else {
            sendError('Failed to switch domain', 400);
        }
        return;
    }

    // POST /domains/god-mode/enable - Enable GOD MODE
    if (in_array('god-mode', $pathParts) && end($pathParts) === 'enable') {
        Validate::required($input, ['conversation_id']);

        $conversationId = $input['conversation_id'];
        Validate::uuid($conversationId);

        $success = $agent->enableGodMode($conversationId);

        if ($success) {
            $currentDomain = $agent->getCurrentDomain($conversationId);
            sendSuccess([
                'message' => 'GOD MODE enabled - All 342 documents accessible',
                'current_domain' => $currentDomain,
                'security_note' => 'All documents now accessible at 100% relevance'
            ]);
        } else {
            sendError('Failed to enable GOD MODE', 400);
        }
        return;
    }

    // POST /domains/god-mode/disable - Disable GOD MODE
    if (in_array('god-mode', $pathParts) && end($pathParts) === 'disable') {
        Validate::required($input, ['conversation_id']);

        $conversationId = $input['conversation_id'];
        Validate::uuid($conversationId);

        $success = $agent->disableGodMode($conversationId);

        if ($success) {
            $currentDomain = $agent->getCurrentDomain($conversationId);
            sendSuccess([
                'message' => 'GOD MODE disabled',
                'current_domain' => $currentDomain
            ]);
        } else {
            sendError('Failed to disable GOD MODE', 400);
        }
        return;
    }

    // POST /domains/documents/add - Add document to domain
    if (in_array('documents', $pathParts) && end($pathParts) === 'add') {
        Validate::required($input, ['doc_id', 'domain_name']);

        $docId = $input['doc_id'];
        $domainName = $input['domain_name'];
        $relevanceScore = $input['relevance_score'] ?? 1.0;

        Validate::uuid($docId);
        Validate::string($domainName, 1, 50);

        $success = $agent->addDocumentToDomain($docId, $domainName, (float)$relevanceScore);

        if ($success) {
            sendSuccess([
                'message' => "Document added to domain: {$domainName}",
                'doc_id' => $docId,
                'domain_name' => $domainName,
                'relevance_score' => $relevanceScore
            ]);
        } else {
            sendError('Failed to add document to domain', 400);
        }
        return;
    }

    sendError('Endpoint not found', 404);
}

/**
 * Handle DELETE requests
 */
function handleDelete(array $pathParts, array $input, Agent $agent, Logger $logger): void
{
    // DELETE /domains/documents/remove - Remove document from domain
    if (in_array('documents', $pathParts) && end($pathParts) === 'remove') {
        Validate::required($input, ['doc_id', 'domain_name']);

        $docId = $input['doc_id'];
        $domainName = $input['domain_name'];

        Validate::uuid($docId);
        Validate::string($domainName, 1, 50);

        $domainId = MultiDomain::getDomainIdByName($domainName);
        if ($domainId === null) {
            sendError('Invalid domain name', 400);
            return;
        }

        $success = MultiDomain::removeDocumentFromDomain($docId, $domainId);

        if ($success) {
            sendSuccess([
                'message' => "Document removed from domain: {$domainName}",
                'doc_id' => $docId,
                'domain_name' => $domainName
            ]);
        } else {
            sendError('Failed to remove document from domain', 400);
        }
        return;
    }

    sendError('Endpoint not found', 404);
}

/**
 * Send success response
 */
function sendSuccess(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode([
        'success' => true,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    exit;
}

/**
 * Send error response
 */
function sendError(string $message, int $code = 400): void
{
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => [
            'message' => $message,
            'code' => $code
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT);
    exit;
}
