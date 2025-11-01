<?php
/**
 * Real-Time Scanner API - Live Scanning with Progress Updates
 *
 * Features:
 * - Incremental file scanning
 * - Real-time progress updates
 * - Watch mode (auto-scan on file changes)
 * - Parallel processing support
 *
 * @package Scanner
 * @version 1.0.0
 */

declare(strict_types=1);

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/mcp-proxy.php';

session_start();

// Authentication check
if (!isset($_SESSION['current_project_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

$action = $_POST['action'] ?? '';
$projectId = (int)($_SESSION['current_project_id'] ?? 1);

/**
 * Real-Time Scanner Engine
 */
class RealTimeScanner
{
    private PDO $pdo;
    private MCPAgent $mcp;
    private string $scanId;
    private array $config;

    public function __construct(PDO $pdo, int $projectId)
    {
        $this->pdo = $pdo;
        $this->mcp = new MCPAgent();
        $this->scanId = uniqid('scan_', true);

        // Load scan configuration
        $stmt = $pdo->prepare("SELECT * FROM scan_config WHERE project_id = ?");
        $stmt->execute([$projectId]);
        $this->config = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Start real-time scan
     */
    public function startScan(int $projectId, array $options = []): array
    {
        // Get project details
        $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);
        $project = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$project) {
            return ['success' => false, 'error' => 'Project not found'];
        }

        // Create scan history record
        $stmt = $this->pdo->prepare("
            INSERT INTO scan_history
            (project_id, scan_type, status, started_at, scan_config)
            VALUES (?, ?, 'running', NOW(), ?)
        ");
        $stmt->execute([
            $projectId,
            $options['type'] ?? 'full',
            json_encode($this->config)
        ]);
        $scanHistoryId = (int)$this->pdo->lastInsertId();

        // Initialize progress tracking
        $progress = [
            'scan_id' => $this->scanId,
            'scan_history_id' => $scanHistoryId,
            'project_id' => $projectId,
            'status' => 'initializing',
            'progress' => 0,
            'total_files' => 0,
            'scanned_files' => 0,
            'violations_found' => 0,
            'started_at' => date('Y-m-d H:i:s'),
            'estimated_completion' => null
        ];

        // Store in session for progress tracking
        $_SESSION['scan_progress'][$this->scanId] = $progress;

        return [
            'success' => true,
            'scan_id' => $this->scanId,
            'scan_history_id' => $scanHistoryId,
            'progress' => $progress
        ];
    }

    /**
     * Get scan progress
     */
    public function getProgress(string $scanId): array
    {
        if (!isset($_SESSION['scan_progress'][$scanId])) {
            return [
                'success' => false,
                'error' => 'Scan not found',
                'scan_id' => $scanId
            ];
        }

        $progress = $_SESSION['scan_progress'][$scanId];

        // Calculate estimated completion
        if ($progress['scanned_files'] > 0 && $progress['total_files'] > 0) {
            $elapsed = time() - strtotime($progress['started_at']);
            $rate = $progress['scanned_files'] / $elapsed;
            $remaining = ($progress['total_files'] - $progress['scanned_files']) / $rate;
            $progress['estimated_completion'] = date('Y-m-d H:i:s', time() + (int)$remaining);
        }

        return [
            'success' => true,
            'scan_id' => $scanId,
            'progress' => $progress
        ];
    }

    /**
     * Scan files incrementally
     */
    public function scanFiles(int $projectId, array $fileIds = []): array
    {
        $results = [
            'success' => true,
            'scanned' => 0,
            'violations' => [],
            'errors' => []
        ];

        // Get files to scan
        if (empty($fileIds)) {
            // Scan all files
            $stmt = $this->pdo->prepare("
                SELECT file_id, file_path, file_type
                FROM intelligence_files
                WHERE project_id = ?
                LIMIT 100
            ");
            $stmt->execute([$projectId]);
        } else {
            // Scan specific files
            $placeholders = implode(',', array_fill(0, count($fileIds), '?'));
            $stmt = $this->pdo->prepare("
                SELECT file_id, file_path, file_type
                FROM intelligence_files
                WHERE file_id IN ($placeholders)
            ");
            $stmt->execute($fileIds);
        }

        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Scan each file
        foreach ($files as $file) {
            try {
                $violations = $this->scanFile($projectId, $file);
                $results['violations'] = array_merge($results['violations'], $violations);
                $results['scanned']++;

                // Update progress
                if (isset($_SESSION['scan_progress'][$this->scanId])) {
                    $_SESSION['scan_progress'][$this->scanId]['scanned_files']++;
                    $_SESSION['scan_progress'][$this->scanId]['violations_found'] += count($violations);
                    $_SESSION['scan_progress'][$this->scanId]['progress'] =
                        ($_SESSION['scan_progress'][$this->scanId]['scanned_files'] /
                         $_SESSION['scan_progress'][$this->scanId]['total_files']) * 100;
                }

            } catch (Exception $e) {
                $results['errors'][] = [
                    'file' => $file['file_path'],
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Scan single file
     */
    private function scanFile(int $projectId, array $file): array
    {
        $violations = [];

        // Use MCP to analyze file
        $analysis = $this->mcp->analyzeFile($file['file_path']);

        if (!$analysis['success']) {
            return $violations;
        }

        // Get all active rules
        $stmt = $this->pdo->prepare("
            SELECT * FROM rules
            WHERE is_active = 1
            ORDER BY severity DESC
        ");
        $stmt->execute();
        $rules = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Check each rule against file
        foreach ($rules as $rule) {
            $detected = $this->detectViolation($file, $rule, $analysis['data'] ?? []);

            if ($detected) {
                $violations[] = $detected;

                // Insert violation into database
                $this->recordViolation($projectId, $detected);
            }
        }

        return $violations;
    }

    /**
     * Detect violation based on rule
     */
    private function detectViolation(array $file, array $rule, array $analysis): ?array
    {
        // Pattern-based detection
        if ($rule['detection_pattern']) {
            // Get file content via MCP
            $contentResult = $this->mcp->call('get_file_content', [
                'file_path' => $file['file_path']
            ]);

            if ($contentResult['success']) {
                $content = $contentResult['data']['content'] ?? '';

                // Check for pattern match
                if (preg_match($rule['detection_pattern'], $content, $matches, PREG_OFFSET_CAPTURE)) {
                    // Calculate line number
                    $lineNumber = substr_count(substr($content, 0, $matches[0][1]), "\n") + 1;

                    return [
                        'rule_id' => $rule['id'],
                        'rule_code' => $rule['rule_code'],
                        'rule_name' => $rule['rule_name'],
                        'file_path' => $file['file_path'],
                        'line_number' => $lineNumber,
                        'severity' => $rule['severity'],
                        'description' => $rule['description'],
                        'matched_text' => substr($matches[0][0], 0, 100)
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Record violation in database
     */
    private function recordViolation(int $projectId, array $violation): void
    {
        // Check if violation already exists
        $stmt = $this->pdo->prepare("
            SELECT id FROM project_rule_violations
            WHERE project_id = ?
            AND rule_id = ?
            AND file_path = ?
            AND line_number = ?
            AND status = 'open'
        ");
        $stmt->execute([
            $projectId,
            $violation['rule_id'],
            $violation['file_path'],
            $violation['line_number']
        ]);

        if ($stmt->fetch()) {
            return; // Already exists
        }

        // Insert new violation
        $stmt = $this->pdo->prepare("
            INSERT INTO project_rule_violations
            (project_id, rule_id, file_path, line_number, violation_description, severity, status, detected_at)
            VALUES (?, ?, ?, ?, ?, ?, 'open', NOW())
        ");
        $stmt->execute([
            $projectId,
            $violation['rule_id'],
            $violation['file_path'],
            $violation['line_number'],
            $violation['description'],
            $violation['severity']
        ]);
    }

    /**
     * Watch mode - monitor file changes
     */
    public function watchMode(int $projectId): array
    {
        // Get last scan time
        $stmt = $this->pdo->prepare("
            SELECT MAX(completed_at) as last_scan
            FROM scan_history
            WHERE project_id = ?
            AND status = 'completed'
        ");
        $stmt->execute([$projectId]);
        $lastScan = $stmt->fetchColumn() ?: '2000-01-01 00:00:00';

        // Find changed files via MCP
        $result = $this->mcp->getAnalytics('file_changes', '24h');

        if (!$result['success']) {
            return ['success' => false, 'error' => 'Could not check for changes'];
        }

        $changedFiles = $result['data']['changed_files'] ?? [];

        if (empty($changedFiles)) {
            return [
                'success' => true,
                'changes_detected' => false,
                'message' => 'No changes since last scan'
            ];
        }

        // Scan changed files
        $scanResult = $this->scanFiles($projectId, array_column($changedFiles, 'file_id'));

        return [
            'success' => true,
            'changes_detected' => true,
            'files_scanned' => $scanResult['scanned'],
            'violations_found' => count($scanResult['violations']),
            'violations' => $scanResult['violations']
        ];
    }
}

// ============================================================================
// API ENDPOINTS
// ============================================================================

try {
    $scanner = new RealTimeScanner($pdo, $projectId);

    switch ($action) {
        case 'start_scan':
            $options = json_decode($_POST['options'] ?? '{}', true);
            $result = $scanner->startScan($projectId, $options);
            break;

        case 'progress':
            $scanId = $_POST['scan_id'] ?? '';
            $result = $scanner->getProgress($scanId);
            break;

        case 'scan_files':
            $fileIds = json_decode($_POST['file_ids'] ?? '[]', true);
            $result = $scanner->scanFiles($projectId, $fileIds);
            break;

        case 'watch':
            $result = $scanner->watchMode($projectId);
            break;

        default:
            http_response_code(400);
            $result = ['success' => false, 'error' => 'Invalid action'];
    }

    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
