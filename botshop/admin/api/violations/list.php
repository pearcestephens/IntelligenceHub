<?php
/**
 * Get Violations List
 * Returns project rule violations with filtering
 *
 * @route GET /dashboard/api/violations/list
 * @param int $project_id Project ID
 * @param string $severity Filter by severity (critical, high, medium, low)
 * @param int $limit Pagination limit
 * @param int $offset Pagination offset
 * @return JSON Violations list
 */

header('Content-Type: application/json');

try {
    $projectId = $_GET['project_id'] ?? 1;
    $severity = $_GET['severity'] ?? '';
    $limit = (int)($_GET['limit'] ?? 20);
    $offset = (int)($_GET['offset'] ?? 0);

    $pdo = new PDO("mysql:host=localhost;dbname=hdgwrzntwa", "hdgwrzntwa", "bFUdRjh4Jx");

    $query = "SELECT * FROM project_rule_violations WHERE project_id = ?";
    $params = [(int)$projectId];

    if ($severity) {
        $query .= " AND severity = ?";
        $params[] = $severity;
    }

    $countStmt = $pdo->prepare(str_replace("SELECT *", "SELECT COUNT(*) as total", $query));
    $countStmt->execute($params);
    $countData = $countStmt->fetch(PDO::FETCH_ASSOC);
    $total = $countData['total'] ?? 0;

    $query .= " ORDER BY severity DESC, created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $violations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $violations,
        'meta' => [
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
            'pages' => ceil($total / $limit)
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
