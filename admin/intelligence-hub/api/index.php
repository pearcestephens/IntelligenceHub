<?php

require_once __DIR__ . '/../config/bootstrap.php';

use IntelligenceHub\AI\DecisionEngine;
use IntelligenceHub\Data\VendService;
use IntelligenceHub\Config\Connection;
use IntelligenceHub\Services\Logger;

header('Content-Type: application/json');

$logger = new Logger('api');
$db = Connection::getInstance();

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = $_GET['endpoint'] ?? '';

try {
    // Route API requests
    switch ($path) {
        case 'ai/command':
            handleAICommand($db, $logger);
            break;

        case 'ai/approve':
            handleApproval($db, $logger);
            break;

        case 'dashboard/overview':
            handleDashboardOverview($db, $logger);
            break;

        case 'dashboard/metrics':
            handleDashboardMetrics($db, $logger);
            break;

        case 'agents/status':
            handleAgentStatus($db, $logger);
            break;

        case 'agents/tasks':
            handleAgentTasks($db, $logger);
            break;

        default:
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Endpoint not found'
            ]);
            break;
    }
} catch (Exception $e) {
    $logger->error("API error", [
        'endpoint' => $path,
        'error' => $e->getMessage()
    ]);

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error'
    ]);
}

/**
 * Handle AI command processing
 */
function handleAICommand($db, $logger) {
    $input = json_decode(file_get_contents('php://input'), true);
    $command = $input['command'] ?? '';

    if (empty($command)) {
        echo json_encode([
            'success' => false,
            'error' => 'Command is required'
        ]);
        return;
    }

    $logger->info("Processing AI command", ['command' => $command]);

    $engine = new DecisionEngine();
    $result = $engine->processCommand($command);

    echo json_encode($result);
}

/**
 * Handle decision approval
 */
function handleApproval($db, $logger) {
    $input = json_decode(file_get_contents('php://input'), true);
    $decisionId = $input['decision_id'] ?? 0;
    $approved = $input['approved'] ?? false;

    if (!$decisionId) {
        echo json_encode([
            'success' => false,
            'error' => 'Decision ID is required'
        ]);
        return;
    }

    $stmt = $db->prepare("
        UPDATE ai_decisions
        SET user_approved = ?, approved_by = ?, approved_at = NOW()
        WHERE id = ?
    ");

    $stmt->execute([
        $approved ? 1 : 0,
        $_SESSION['user_id'] ?? 1,
        $decisionId
    ]);

    $logger->info("Decision approval", [
        'decision_id' => $decisionId,
        'approved' => $approved
    ]);

    echo json_encode([
        'success' => true,
        'message' => $approved ? 'Decision approved' : 'Decision declined'
    ]);
}

/**
 * Get dashboard overview data
 */
function handleDashboardOverview($db, $logger) {
    // Get agent status
    $agentsStmt = $db->query("
        SELECT
            a.id,
            a.name,
            a.status,
            a.capabilities,
            COUNT(at.id) as tasks_today
        FROM agents a
        LEFT JOIN agent_tasks at ON a.id = at.agent_id
            AND at.created_at >= CURDATE()
        GROUP BY a.id
        ORDER BY a.name
    ");

    $agents = $agentsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get quick stats
    $statsStmt = $db->query("
        SELECT
            (SELECT COUNT(*) FROM agents WHERE status IN ('active', 'busy')) as active_agents,
            (SELECT COUNT(*) FROM agent_tasks WHERE status = 'completed' AND completed_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) as tasks_completed,
            (SELECT COUNT(*) FROM ai_decisions WHERE user_approved IS NULL AND confidence >= 0.7) as pending_approvals,
            0 as cost_savings
    ");

    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    // Get recommendations
    $recStmt = $db->query("
        SELECT
            d.id,
            d.input as title,
            JSON_UNQUOTE(JSON_EXTRACT(d.output, '$.response')) as description,
            d.confidence,
            CASE WHEN d.confidence >= 0.9 THEN 'high' ELSE 'normal' END as priority,
            CASE WHEN d.confidence < 0.9 THEN 1 ELSE 0 END as requires_approval,
            'AI Engine' as agent,
            'TBD' as expected_impact,
            d.created_at
        FROM ai_decisions d
        WHERE d.user_approved IS NULL
        AND d.confidence >= 0.5
        ORDER BY d.confidence DESC, d.created_at DESC
        LIMIT 5
    ");

    $recommendations = $recStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get recent activity
    $activityStmt = $db->query("
        SELECT
            at.task_id,
            JSON_UNQUOTE(JSON_EXTRACT(at.task_data, '$.title')) as title,
            JSON_UNQUOTE(JSON_EXTRACT(at.task_data, '$.description')) as description,
            a.name as agent,
            at.status,
            CONCAT(
                TIMESTAMPDIFF(MINUTE, at.completed_at, NOW()),
                ' minutes ago'
            ) as time_ago
        FROM agent_tasks at
        JOIN agents a ON at.agent_id = a.id
        WHERE at.status = 'completed'
        ORDER BY at.completed_at DESC
        LIMIT 10
    ");

    $activity = $activityStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get alerts
    $alertsStmt = $db->query("
        SELECT
            n.id,
            n.title,
            n.message,
            n.priority as severity,
            CONCAT(
                TIMESTAMPDIFF(MINUTE, n.created_at, NOW()),
                ' minutes ago'
            ) as time_ago
        FROM notifications n
        WHERE JSON_CONTAINS(n.read_by, '[]')
        OR n.read_by IS NULL
        ORDER BY n.created_at DESC
        LIMIT 10
    ");

    $alerts = $alertsStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'agents' => $agents,
        'recommendations' => $recommendations,
        'activity' => $activity,
        'alerts' => $alerts
    ]);
}

/**
 * Get dashboard metrics (for periodic updates)
 */
function handleDashboardMetrics($db, $logger) {
    $statsStmt = $db->query("
        SELECT
            (SELECT COUNT(*) FROM agents WHERE status IN ('active', 'busy')) as active_agents,
            (SELECT COUNT(*) FROM agent_tasks WHERE status = 'completed' AND completed_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)) as tasks_completed,
            (SELECT COUNT(*) FROM ai_decisions WHERE user_approved IS NULL AND confidence >= 0.7) as pending_approvals,
            0 as cost_savings
    ");

    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'stats' => $stats
    ]);
}

/**
 * Get agent status
 */
function handleAgentStatus($db, $logger) {
    $stmt = $db->query("
        SELECT
            a.*,
            COUNT(at.id) as total_tasks,
            SUM(CASE WHEN at.status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
            SUM(CASE WHEN at.status = 'failed' THEN 1 ELSE 0 END) as failed_tasks
        FROM agents a
        LEFT JOIN agent_tasks at ON a.id = at.agent_id
            AND at.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        GROUP BY a.id
        ORDER BY a.name
    ");

    $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'agents' => $agents
    ]);
}

/**
 * Get agent tasks
 */
function handleAgentTasks($db, $logger) {
    $agentId = $_GET['agent_id'] ?? null;
    $limit = $_GET['limit'] ?? 50;

    $sql = "
        SELECT
            at.*,
            a.name as agent_name
        FROM agent_tasks at
        JOIN agents a ON at.agent_id = a.id
    ";

    $params = [];

    if ($agentId) {
        $sql .= " WHERE at.agent_id = ?";
        $params[] = $agentId;
    }

    $sql .= " ORDER BY at.created_at DESC LIMIT ?";
    $params[] = $limit;

    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'tasks' => $tasks
    ]);
}
