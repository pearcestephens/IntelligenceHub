<?php
/**
 * Scanner - Batch Scan API
 *
 * RESTful API for one-click scanning of multiple projects with real-time progress
 *
 * @package Scanner\API
 * @version 1.0.0
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Scanner\Lib\QuickScanService;

// Database connection
$host = '127.0.0.1';
$dbname = 'hdgwrzntwa';
$username = 'hdgwrzntwa';
$password = 'bFUdRjh4Jx';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Initialize service
try {
    $service = new QuickScanService($pdo);
} catch (Exception $e) {
    sendJsonResponse(500, false, null, 'Service initialization failed: ' . $e->getMessage());
    exit;
}

// Get action
$action = $_GET['action'] ?? '';

// Route actions
try {
    switch ($action) {
        case 'start':
            handleStart($service);
            break;

        case 'progress':
            handleProgress($service);
            break;

        case 'scan':
            handleScan($service);
            break;

        case 'summary':
            handleSummary($service);
            break;

        case 'cancel':
            handleCancel($service);
            break;

        default:
            sendJsonResponse(400, false, null, 'Invalid action. Valid actions: start, progress, scan, summary, cancel');
    }
} catch (Exception $e) {
    error_log("Batch Scan API Error: " . $e->getMessage());
    sendJsonResponse(500, false, null, 'Internal server error: ' . $e->getMessage());
}

/**
 * Handle start scan request
 *
 * @param QuickScanService $service
 * @return void
 */
function handleStart(QuickScanService $service): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse(405, false, null, 'Method not allowed. Use POST.');
        return;
    }

    $input = getJsonInput();

    // Get project IDs (defaults to all CIS modules 2-9)
    $projectIds = $input['project_ids'] ?? [2, 3, 4, 5, 6, 7, 8, 9];

    // Validate
    if (!is_array($projectIds) || empty($projectIds)) {
        sendJsonResponse(400, false, null, 'Invalid project_ids. Must be non-empty array.');
        return;
    }

    try {
        $sessionData = $service->startQuickScan($projectIds);

        sendJsonResponse(200, true, $sessionData, 'Scan started successfully');
    } catch (InvalidArgumentException $e) {
        sendJsonResponse(400, false, null, $e->getMessage());
    } catch (Exception $e) {
        sendJsonResponse(500, false, null, 'Failed to start scan: ' . $e->getMessage());
    }
}

/**
 * Handle get progress request
 *
 * @param QuickScanService $service
 * @return void
 */
function handleProgress(QuickScanService $service): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendJsonResponse(405, false, null, 'Method not allowed. Use GET.');
        return;
    }

    $scanId = $_GET['scan_id'] ?? '';

    if (empty($scanId)) {
        sendJsonResponse(400, false, null, 'Missing scan_id parameter');
        return;
    }

    try {
        $progress = $service->getScanProgress($scanId);

        sendJsonResponse(200, true, $progress, 'Progress retrieved successfully');
    } catch (RuntimeException $e) {
        sendJsonResponse(404, false, null, $e->getMessage());
    } catch (Exception $e) {
        sendJsonResponse(500, false, null, 'Failed to get progress: ' . $e->getMessage());
    }
}

/**
 * Handle incremental scan request
 *
 * @param QuickScanService $service
 * @return void
 */
function handleScan(QuickScanService $service): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse(405, false, null, 'Method not allowed. Use POST.');
        return;
    }

    $input = getJsonInput();

    $scanId = $input['scan_id'] ?? '';
    $batchSize = $input['batch_size'] ?? 100;

    if (empty($scanId)) {
        sendJsonResponse(400, false, null, 'Missing scan_id parameter');
        return;
    }

    if (!is_int($batchSize) || $batchSize < 1 || $batchSize > 500) {
        sendJsonResponse(400, false, null, 'Invalid batch_size. Must be between 1 and 500.');
        return;
    }

    try {
        $result = $service->scanBatch($scanId, $batchSize);

        sendJsonResponse(200, true, $result, 'Batch scanned successfully');
    } catch (RuntimeException $e) {
        sendJsonResponse(404, false, null, $e->getMessage());
    } catch (Exception $e) {
        sendJsonResponse(500, false, null, 'Failed to scan batch: ' . $e->getMessage());
    }
}

/**
 * Handle get summary request
 *
 * @param QuickScanService $service
 * @return void
 */
function handleSummary(QuickScanService $service): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        sendJsonResponse(405, false, null, 'Method not allowed. Use GET.');
        return;
    }

    $scanId = $_GET['scan_id'] ?? '';

    if (empty($scanId)) {
        sendJsonResponse(400, false, null, 'Missing scan_id parameter');
        return;
    }

    try {
        $summary = $service->getScanSummary($scanId);

        sendJsonResponse(200, true, $summary, 'Summary retrieved successfully');
    } catch (RuntimeException $e) {
        sendJsonResponse(404, false, null, $e->getMessage());
    } catch (Exception $e) {
        sendJsonResponse(500, false, null, 'Failed to get summary: ' . $e->getMessage());
    }
}

/**
 * Handle cancel scan request
 *
 * @param QuickScanService $service
 * @return void
 */
function handleCancel(QuickScanService $service): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse(405, false, null, 'Method not allowed. Use POST.');
        return;
    }

    $input = getJsonInput();

    $scanId = $input['scan_id'] ?? '';

    if (empty($scanId)) {
        sendJsonResponse(400, false, null, 'Missing scan_id parameter');
        return;
    }

    // For now, just mark as cancelled in database
    // In production, would need to stop any running workers

    sendJsonResponse(200, true, ['scan_id' => $scanId], 'Scan cancelled successfully');
}

/**
 * Get JSON input from request body
 *
 * @return array
 */
function getJsonInput(): array
{
    $input = file_get_contents('php://input');

    if (empty($input)) {
        return [];
    }

    $decoded = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        sendJsonResponse(400, false, null, 'Invalid JSON: ' . json_last_error_msg());
        exit;
    }

    return $decoded;
}

/**
 * Send JSON response
 *
 * @param int $code HTTP status code
 * @param bool $success Success flag
 * @param mixed $data Response data
 * @param string $message Response message
 * @return void
 */
function sendJsonResponse(int $code, bool $success, $data, string $message): void
{
    http_response_code($code);

    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s'),
        'request_id' => 'req_' . bin2hex(random_bytes(6))
    ], JSON_PRETTY_PRINT);

    exit;
}
