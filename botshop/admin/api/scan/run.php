<?php
/**
 * Run Project Scan
 * Triggers a complete project scan
 *
 * @route POST /dashboard/api/scan/run
 * @param int $project_id Project ID (default: 1)
 * @return JSON Scan results
 */

header('Content-Type: application/json');

try {
    $projectId = $_POST['project_id'] ?? 1;
    $pdo = new PDO("mysql:host=localhost;dbname=hdgwrzntwa", "hdgwrzntwa", "bFUdRjh4Jx");

    // Update scan config with current timestamp
    $updateQuery = "
        UPDATE project_scan_config
        SET last_scan_date = NOW(), scan_status = 'running'
        WHERE project_id = ?
    ";

    $updateStmt = $pdo->prepare($updateQuery);
    $updateStmt->execute([(int)$projectId]);

    // Trigger scan in background (would normally call a CLI script)
    // For now, just return success
    exec('php /home/master/applications/hdgwrzntwa/public_html/_automation/scan-complete-hdgwrzntwa.php > /dev/null 2>&1 &');

    // Update status to completed
    $completeQuery = "
        UPDATE project_scan_config
        SET scan_status = 'completed', last_scan_date = NOW()
        WHERE project_id = ?
    ";

    $completeStmt = $pdo->prepare($completeQuery);
    $completeStmt->execute([(int)$projectId]);

    echo json_encode([
        'success' => true,
        'message' => 'Scan initiated',
        'data' => [
            'project_id' => $projectId,
            'status' => 'running',
            'started_at' => date('Y-m-d H:i:s')
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
