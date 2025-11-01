<?php
/**
 * Get Metrics Dashboard Data
 * Returns project health metrics and trends
 *
 * @route GET /dashboard/api/metrics/dashboard
 * @param int $project_id Project ID
 * @return JSON Metrics data
 */

header('Content-Type: application/json');

try {
    $projectId = $_GET['project_id'] ?? 1;
    $pdo = new PDO("mysql:host=localhost;dbname=hdgwrzntwa", "hdgwrzntwa", "bFUdRjh4Jx");

    // Get current metrics
    $query = "
        SELECT
            health_score,
            technical_debt,
            lines_of_code,
            complexity_score,
            test_coverage,
            last_updated
        FROM project_metrics
        WHERE project_id = ?
        LIMIT 1
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([(int)$projectId]);
    $currentMetrics = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    // Get file counts
    $countQuery = "
        SELECT
            file_type,
            COUNT(*) as count
        FROM file_dependencies
        WHERE project_id = ?
        GROUP BY file_type
    ";

    $countStmt = $pdo->prepare($countQuery);
    $countStmt->execute([(int)$projectId]);
    $fileCounts = $countStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get violation summary
    $violQuery = "
        SELECT
            severity,
            COUNT(*) as count
        FROM project_rule_violations
        WHERE project_id = ?
        GROUP BY severity
    ";

    $violStmt = $pdo->prepare($violQuery);
    $violStmt->execute([(int)$projectId]);
    $violations = $violStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => [
            'metrics' => $currentMetrics,
            'file_counts' => $fileCounts,
            'violations' => $violations
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
