<?php
/**
 * Get Project Overview Data
 * Returns main project statistics for dashboard
 *
 * @route GET /dashboard/api/projects/get
 * @param int $id Project ID (default: 1)
 * @return JSON Project data with metrics
 */

header('Content-Type: application/json');

try {
    $projectId = $_GET['id'] ?? 1;
    $pdo = new PDO("mysql:host=localhost;dbname=hdgwrzntwa", "hdgwrzntwa", "bFUdRjh4Jx");

    $query = "
        SELECT
            p.id,
            p.name,
            p.path,
            p.project_type,
            p.status,
            pm.framework,
            pm.version,
            pm.description,
            pmr.health_score,
            pmr.technical_debt,
            pmr.lines_of_code,
            pmr.last_updated,
            psc.last_scan_date,
            psc.total_files_scanned
        FROM projects p
        LEFT JOIN project_metadata pm ON p.id = pm.project_id
        LEFT JOIN project_metrics pmr ON p.id = pmr.project_id
        LEFT JOIN project_scan_config psc ON p.id = psc.project_id
        WHERE p.id = ?
        LIMIT 1
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([(int)$projectId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        throw new Exception("Project not found");
    }

    // Get violation stats
    $violQuery = "
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN severity = 'critical' THEN 1 ELSE 0 END) as critical,
            SUM(CASE WHEN severity = 'high' THEN 1 ELSE 0 END) as high
        FROM project_rule_violations
        WHERE project_id = ?
    ";
    $violStmt = $pdo->prepare($violQuery);
    $violStmt->execute([(int)$projectId]);
    $violations = $violStmt->fetch(PDO::FETCH_ASSOC);

    $project['violations'] = $violations;

    echo json_encode([
        'success' => true,
        'data' => $project,
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
