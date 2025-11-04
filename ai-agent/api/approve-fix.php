<?php
/**
 * Approve/Reject Fix API
 *
 * Handles approval and rejection of AI-generated fixes
 *
 * @package AI Agent API
 * @version 1.0.0
 * @date 2025-11-04
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../src/Logger.php';

$logger = new App\Logger('approve-fix');

// Get request data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
    exit;
}

$fixId = $input['fix_id'] ?? null;
$action = $input['action'] ?? null;
$reason = $input['reason'] ?? null;

// Validate
if (!$fixId || !in_array($action, ['approve', 'reject'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Missing or invalid parameters (fix_id, action required)'
    ]);
    exit;
}

// Get fix details
$stmt = $db->prepare("SELECT * FROM frontend_pending_fixes WHERE id = ?");
$stmt->bind_param('i', $fixId);
$stmt->execute();
$fix = $stmt->get_result()->fetch_assoc();

if (!$fix) {
    echo json_encode(['success' => false, 'error' => 'Fix not found']);
    exit;
}

if ($fix['status'] !== 'pending') {
    echo json_encode([
        'success' => false,
        'error' => 'Fix already processed (status: ' . $fix['status'] . ')'
    ]);
    exit;
}

// Process action
if ($action === 'approve') {

    $logger->info('Approving fix', ['fix_id' => $fixId, 'file' => $fix['file_path']]);

    // Apply the fix
    $applyResult = applyFix($fix, $logger, $db);

    if ($applyResult['success']) {
        // Update status to approved + applied
        $stmt = $db->prepare("
            UPDATE frontend_pending_fixes
            SET status = 'applied',
                reviewed_at = NOW(),
                reviewed_by = 'system',
                applied_at = NOW(),
                notes = ?
            WHERE id = ?
        ");

        $notes = 'Applied successfully: ' . ($applyResult['message'] ?? '');
        $stmt->bind_param('si', $notes, $fixId);
        $stmt->execute();

        echo json_encode([
            'success' => true,
            'message' => 'Fix applied successfully',
            'details' => $applyResult
        ]);

    } else {
        // Update status to failed
        $stmt = $db->prepare("
            UPDATE frontend_pending_fixes
            SET status = 'failed',
                reviewed_at = NOW(),
                reviewed_by = 'system',
                notes = ?
            WHERE id = ?
        ");

        $notes = 'Failed to apply: ' . ($applyResult['error'] ?? 'Unknown error');
        $stmt->bind_param('si', $notes, $fixId);
        $stmt->execute();

        echo json_encode([
            'success' => false,
            'error' => $applyResult['error'] ?? 'Failed to apply fix'
        ]);
    }

} elseif ($action === 'reject') {

    $logger->info('Rejecting fix', ['fix_id' => $fixId, 'reason' => $reason]);

    // Update status to rejected
    $stmt = $db->prepare("
        UPDATE frontend_pending_fixes
        SET status = 'rejected',
            reviewed_at = NOW(),
            reviewed_by = 'system',
            notes = ?
        WHERE id = ?
    ");

    $notes = 'Rejected by user' . ($reason ? ': ' . $reason : '');
    $stmt->bind_param('si', $notes, $fixId);
    $stmt->execute();

    echo json_encode([
        'success' => true,
        'message' => 'Fix rejected'
    ]);
}

/**
 * Apply a fix to the codebase
 */
function applyFix(array $fix, $logger, $db): array
{
    $filePath = $fix['file_path'];
    $lineNumber = $fix['line_number'];
    $originalCode = $fix['original_code'];
    $fixedCode = $fix['fixed_code'];

    // Validate file path
    if (empty($filePath) || !file_exists($filePath)) {
        return [
            'success' => false,
            'error' => 'File not found: ' . $filePath
        ];
    }

    // Create backup
    $backupPath = createBackup($filePath);

    if (!$backupPath) {
        return [
            'success' => false,
            'error' => 'Failed to create backup'
        ];
    }

    try {
        // Read file
        $fileContent = file_get_contents($filePath);

        if ($fileContent === false) {
            return [
                'success' => false,
                'error' => 'Failed to read file'
            ];
        }

        // Apply fix
        if ($originalCode && $fixedCode) {
            // String replacement
            $newContent = str_replace($originalCode, $fixedCode, $fileContent, $count);

            if ($count === 0) {
                return [
                    'success' => false,
                    'error' => 'Original code not found in file (may have changed)'
                ];
            }

        } elseif ($lineNumber > 0 && $fixedCode) {
            // Line-based replacement
            $lines = explode("\n", $fileContent);

            if (!isset($lines[$lineNumber - 1])) {
                return [
                    'success' => false,
                    'error' => 'Line number out of bounds'
                ];
            }

            $lines[$lineNumber - 1] = $fixedCode;
            $newContent = implode("\n", $lines);

        } else {
            return [
                'success' => false,
                'error' => 'Insufficient fix data (need original_code + fixed_code OR line_number + fixed_code)'
            ];
        }

        // Write file
        $written = file_put_contents($filePath, $newContent);

        if ($written === false) {
            return [
                'success' => false,
                'error' => 'Failed to write file'
            ];
        }

        // Log deployment
        logDeployment($fix['id'], $filePath, $backupPath, $db);

        $logger->info('Fix applied successfully', [
            'fix_id' => $fix['id'],
            'file' => $filePath,
            'backup' => $backupPath
        ]);

        return [
            'success' => true,
            'message' => 'File updated successfully',
            'backup_path' => $backupPath,
            'bytes_written' => $written
        ];

    } catch (Exception $e) {
        $logger->error('Error applying fix', [
            'fix_id' => $fix['id'],
            'error' => $e->getMessage()
        ]);

        return [
            'success' => false,
            'error' => 'Exception: ' . $e->getMessage()
        ];
    }
}

/**
 * Create backup of file before modifying
 */
function createBackup(string $filePath): ?string
{
    $backupDir = '/home/129337.cloudwaysapps.com/hdgwrzntwa/private_html/backups/frontend-fixes';

    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
    }

    $timestamp = date('Y-m-d_H-i-s');
    $filename = basename($filePath);
    $backupPath = $backupDir . '/' . $filename . '.' . $timestamp . '.backup';

    if (copy($filePath, $backupPath)) {
        return $backupPath;
    }

    return null;
}

/**
 * Log deployment to database
 */
function logDeployment(int $fixId, string $filePath, string $backupPath, $db): void
{
    $stmt = $db->prepare("
        INSERT INTO frontend_deployment_log
        (deployment_id, fix_id, file_path, action, backup_path, deployed_by)
        VALUES (?, ?, ?, 'update', ?, 'system')
    ");

    $deploymentId = 'deploy_' . time() . '_' . $fixId;

    $stmt->bind_param('siss', $deploymentId, $fixId, $filePath, $backupPath);
    $stmt->execute();
}
