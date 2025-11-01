<?php

declare(strict_types=1);

/**
 * Auto-Fix API Endpoint
 *
 * Provides REST API for automated vulnerability fixes
 *
 * Endpoints:
 * - POST /preview - Generate fix preview without applying
 * - POST /apply - Apply single fix with backup
 * - POST /batch - Apply multiple fixes
 * - GET /history - Get fix history for violation
 * - GET /stats - Get fix statistics
 *
 * @package Scanner\API
 * @version 1.0.0
 */

declare(strict_types=1);

// Set headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Load database config
require_once __DIR__ . '/../config/database.php';

// Start session
session_start();

// Authentication check (simplified for internal use)
// TODO: Add proper authentication for production
// if (!isset($_SESSION['user_id'])) {
//     http_response_code(401);
//     echo json_encode(['success' => false, 'error' => 'Authentication required']);
//     exit;
// }

// Load Scanner classes
require_once __DIR__ . '/../lib/AutoFixService.php';
require_once __DIR__ . '/../lib/AIAssistant.php';

use Scanner\Lib\AutoFixService;
use Scanner\Lib\AIAssistant;

// Initialize services
try {
    // Use database config from scanner
    $pdo = getDbConnection();
} catch (PDOException $e) {
    sendJsonResponse(500, false, null, 'Database connection failed: ' . $e->getMessage());
    exit;
}

// Initialize AI Assistant
$aiAssistant = new AIAssistant(
    $pdo,
    'https://gpt.ecigdis.co.nz/mcp/server_v2_complete.php',
    30
);

// Initialize Auto-Fix Service
$backupDir = dirname(__DIR__) . '/../../private_html/backups/auto-fix';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

$autoFixService = new AutoFixService($pdo, $aiAssistant, $backupDir);

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$requestUri = $_SERVER['REQUEST_URI'];

// Parse action from query string or path
$action = $_GET['action'] ?? 'unknown';

// Rate limiting check
if (!checkRateLimit()) {
    sendJsonResponse(429, false, null, 'Rate limit exceeded. Maximum 100 fixes per hour.');
    exit;
}

// Route request
try {
    switch ($action) {
        case 'preview':
            if ($method !== 'POST') {
                sendJsonResponse(405, false, null, 'Method not allowed. Use POST.');
                exit;
            }
            handlePreview($autoFixService);
            break;

        case 'apply':
            if ($method !== 'POST') {
                sendJsonResponse(405, false, null, 'Method not allowed. Use POST.');
                exit;
            }
            handleApply($autoFixService);
            break;

        case 'batch':
            if ($method !== 'POST') {
                sendJsonResponse(405, false, null, 'Method not allowed. Use POST.');
                exit;
            }
            handleBatch($autoFixService);
            break;

        case 'history':
            if ($method !== 'GET') {
                sendJsonResponse(405, false, null, 'Method not allowed. Use GET.');
                exit;
            }
            handleHistory($pdo);
            break;

        case 'stats':
            if ($method !== 'GET') {
                sendJsonResponse(405, false, null, 'Method not allowed. Use GET.');
                exit;
            }
            handleStats($pdo);
            break;

        default:
            sendJsonResponse(400, false, null, 'Invalid action. Use: preview, apply, batch, history, or stats');
            break;
    }
} catch (Exception $e) {
    sendJsonResponse(500, false, null, $e->getMessage());
}

// ============================================================================
// ENDPOINT HANDLERS
// ============================================================================

/**
 * Handle fix preview request
 *
 * @param AutoFixService $service
 * @return void
 */
function handlePreview(AutoFixService $service): void
{
    $input = getJsonInput();

    // Validate input
    if (!isset($input['violation_id'])) {
        sendJsonResponse(400, false, null, 'Missing required field: violation_id');
        return;
    }

    $violationId = filter_var($input['violation_id'], FILTER_VALIDATE_INT);
    if ($violationId === false || $violationId <= 0) {
        sendJsonResponse(400, false, null, 'Invalid violation_id. Must be a positive integer.');
        return;
    }

    try {
        $result = $service->generateFixPreview($violationId);
        sendJsonResponse(200, true, $result, 'Fix preview generated successfully');
    } catch (RuntimeException $e) {
        sendJsonResponse(404, false, null, $e->getMessage());
    }
}

/**
 * Handle apply fix request
 *
 * @param AutoFixService $service
 * @return void
 */
function handleApply(AutoFixService $service): void
{
    $input = getJsonInput();

    // Validate input
    if (!isset($input['violation_id'])) {
        sendJsonResponse(400, false, null, 'Missing required field: violation_id');
        return;
    }

    $violationId = filter_var($input['violation_id'], FILTER_VALIDATE_INT);
    if ($violationId === false || $violationId <= 0) {
        sendJsonResponse(400, false, null, 'Invalid violation_id. Must be a positive integer.');
        return;
    }

    $createBackup = $input['create_backup'] ?? true;

    // Increment rate limit counter
    incrementRateLimitCounter();

    try {
        $result = $service->applyAutoFix($violationId, (bool)$createBackup);
        sendJsonResponse(200, true, $result, 'Fix applied successfully');
    } catch (RuntimeException $e) {
        sendJsonResponse(400, false, null, $e->getMessage());
    } catch (InvalidArgumentException $e) {
        sendJsonResponse(400, false, null, $e->getMessage());
    }
}

/**
 * Handle batch apply request
 *
 * @param AutoFixService $service
 * @return void
 */
function handleBatch(AutoFixService $service): void
{
    $input = getJsonInput();

    // Validate input
    if (!isset($input['violation_ids']) || !is_array($input['violation_ids'])) {
        sendJsonResponse(400, false, null, 'Missing or invalid field: violation_ids (must be array)');
        return;
    }

    // Validate each violation ID
    $violationIds = [];
    foreach ($input['violation_ids'] as $id) {
        $validId = filter_var($id, FILTER_VALIDATE_INT);
        if ($validId === false || $validId <= 0) {
            sendJsonResponse(400, false, null, 'Invalid violation_id in array: ' . $id);
            return;
        }
        $violationIds[] = $validId;
    }

    // Check batch size limit
    if (count($violationIds) > 50) {
        sendJsonResponse(400, false, null, 'Batch size exceeds maximum of 50 violations');
        return;
    }

    if (count($violationIds) === 0) {
        sendJsonResponse(400, false, null, 'Empty violation_ids array');
        return;
    }

    $stopOnError = $input['stop_on_error'] ?? false;

    // Increment rate limit counter for batch
    incrementRateLimitCounter(count($violationIds));

    try {
        $result = $service->batchApplyFixes($violationIds, (bool)$stopOnError);
        sendJsonResponse(200, true, $result, 'Batch processing completed');
    } catch (Exception $e) {
        sendJsonResponse(500, false, null, $e->getMessage());
    }
}

/**
 * Handle history request
 *
 * @param PDO $pdo
 * @return void
 */
function handleHistory(PDO $pdo): void
{
    // Get violation ID from query string
    $violationId = filter_input(INPUT_GET, 'violation_id', FILTER_VALIDATE_INT);

    if ($violationId === false || $violationId === null) {
        sendJsonResponse(400, false, null, 'Missing or invalid violation_id parameter');
        return;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT
                afl.*,
                v.rule_id,
                v.severity,
                f.file_path
            FROM auto_fix_log afl
            JOIN violations v ON afl.violation_id = v.id
            JOIN intelligence_files f ON v.file_id = f.file_id
            WHERE afl.violation_id = ?
            ORDER BY afl.applied_at DESC
        ");

        $stmt->execute([$violationId]);
        $history = $stmt->fetchAll();

        sendJsonResponse(200, true, [
            'violation_id' => $violationId,
            'history' => $history,
            'count' => count($history)
        ], 'History retrieved successfully');
    } catch (PDOException $e) {
        sendJsonResponse(500, false, null, 'Database error: ' . $e->getMessage());
    }
}

/**
 * Handle statistics request
 *
 * @param PDO $pdo
 * @return void
 */
function handleStats(PDO $pdo): void
{
    try {
        // Total fixes applied
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM auto_fix_log");
        $totalFixes = $stmt->fetch()['total'];

        // Fixes by rule
        $stmt = $pdo->query("
            SELECT
                v.rule_id,
                COUNT(*) as fix_count,
                AVG(afl.confidence) as avg_confidence,
                AVG(afl.lines_changed) as avg_lines_changed
            FROM auto_fix_log afl
            JOIN violations v ON afl.violation_id = v.id
            GROUP BY v.rule_id
            ORDER BY fix_count DESC
        ");
        $byRule = $stmt->fetchAll();

        // Recent fixes (last 24 hours)
        $stmt = $pdo->query("
            SELECT COUNT(*) as count
            FROM auto_fix_log
            WHERE applied_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $last24h = $stmt->fetch()['count'];

        // Success rate (assuming we track failures)
        $stmt = $pdo->query("
            SELECT
                SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successes,
                COUNT(*) as total
            FROM auto_fix_log
        ");
        $successData = $stmt->fetch();
        $successRate = $successData['total'] > 0
            ? round(($successData['successes'] / $successData['total']) * 100, 2)
            : 0;

        sendJsonResponse(200, true, [
            'total_fixes' => $totalFixes,
            'last_24_hours' => $last24h,
            'success_rate' => $successRate,
            'by_rule' => $byRule
        ], 'Statistics retrieved successfully');
    } catch (PDOException $e) {
        sendJsonResponse(500, false, null, 'Database error: ' . $e->getMessage());
    }
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Get JSON input from request body
 *
 * @return array
 */
function getJsonInput(): array
{
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        sendJsonResponse(400, false, null, 'Invalid JSON: ' . json_last_error_msg());
        exit;
    }

    return $data ?? [];
}

/**
 * Send JSON response
 *
 * @param int $httpCode HTTP status code
 * @param bool $success Success flag
 * @param mixed $data Response data
 * @param string|null $message Response message
 * @return void
 */
function sendJsonResponse(int $httpCode, bool $success, $data, ?string $message): void
{
    http_response_code($httpCode);
    header('Content-Type: application/json');

    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s'),
        'request_id' => uniqid('req_', true)
    ], JSON_PRETTY_PRINT);
}

/**
 * Check rate limit
 *
 * Maximum 100 fixes per hour per IP
 *
 * @return bool
 */
function checkRateLimit(): bool
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $cacheFile = sys_get_temp_dir() . '/scanner_ratelimit_' . md5($ip) . '.json';

    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true);

        // Reset if older than 1 hour
        if (time() - $data['timestamp'] > 3600) {
            $data = ['count' => 0, 'timestamp' => time()];
        }

        // Check limit
        if ($data['count'] >= 100) {
            return false;
        }
    }

    return true;
}

/**
 * Increment rate limit counter
 *
 * @param int $increment Amount to increment (default 1)
 * @return void
 */
function incrementRateLimitCounter(int $increment = 1): void
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $cacheFile = sys_get_temp_dir() . '/scanner_ratelimit_' . md5($ip) . '.json';

    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true);

        // Reset if older than 1 hour
        if (time() - $data['timestamp'] > 3600) {
            $data = ['count' => 0, 'timestamp' => time()];
        }
    } else {
        $data = ['count' => 0, 'timestamp' => time()];
    }

    $data['count'] += $increment;
    file_put_contents($cacheFile, json_encode($data));
}
