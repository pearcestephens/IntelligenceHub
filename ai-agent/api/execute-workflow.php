<?php
/**
 * Execute Workflow API
 *
 * Executes frontend automation workflows via ToolChainOrchestrator
 *
 * @package AI Agent API
 * @version 1.0.0
 * @date 2025-11-04
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';

use App\Tools\ToolChainOrchestrator;
use App\Tools\ToolExecutor;
use App\Tools\Frontend\FrontendToolRegistry;
use App\Logger;
use App\RedisClient;

$logger = new Logger('workflow-execution');

// Get input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
    exit;
}

$nodes = $input['nodes'] ?? [];
$connections = $input['connections'] ?? [];
$workflowId = $input['workflow_id'] ?? null;
$workflowName = $input['workflow_name'] ?? 'Untitled Workflow';

if (empty($nodes)) {
    echo json_encode(['success' => false, 'error' => 'No nodes provided']);
    exit;
}

$logger->info('Starting workflow execution', [
    'workflow_id' => $workflowId,
    'nodes' => count($nodes),
    'connections' => count($connections)
]);

try {
    // Initialize orchestrator
    $redis = new RedisClient();
    $orchestrator = new ToolChainOrchestrator($logger, $redis);

    // Register frontend tools
    $toolRegistry = new FrontendToolRegistry($logger);
    $frontendTools = $toolRegistry->getTools();

    // Create execution record
    $executionId = 'exec_' . time() . '_' . substr(md5(json_encode($nodes)), 0, 8);

    $stmt = $db->prepare("
        INSERT INTO frontend_workflow_executions
        (workflow_id, execution_id, status, steps_total)
        VALUES (?, ?, 'running', ?)
    ");

    $stepsTotal = count($nodes);
    $stmt->bind_param('isi', $workflowId, $executionId, $stepsTotal);
    $stmt->execute();
    $executionDbId = $db->insert_id;

    // Create tool chain
    $chainId = $executionId;
    $chain = $orchestrator->createChain($chainId, [
        'parallel' => false,  // Sequential by default
        'cache_results' => true,
        'max_retries' => 1,
        'timeout' => 120  // 2 minutes per step
    ]);

    // Build dependency map from connections
    $dependencyMap = [];
    foreach ($connections as $conn) {
        $to = $conn['to'];
        $from = $conn['from'];

        if (!isset($dependencyMap[$to])) {
            $dependencyMap[$to] = [];
        }
        $dependencyMap[$to][] = $from;
    }

    // Add each node as a step
    foreach ($nodes as $node) {
        $nodeId = $node['id'];
        $nodeType = $node['type'];
        $nodeConfig = $node['config'] ?? [];

        // Map node type to tool name
        $toolName = 'frontend_' . $nodeType;

        // Special handling for condition nodes
        if ($nodeType === 'condition') {
            // Conditions are handled by ToolChainOrchestrator's conditional branching
            // Skip for now in simple implementation
            continue;
        }

        // Get dependencies for this node
        $dependencies = $dependencyMap[$nodeId] ?? [];

        // Add step to chain
        $chain->addStep($toolName, $nodeConfig, $nodeId, $dependencies);

        $logger->info('Added step to chain', [
            'node_id' => $nodeId,
            'tool' => $toolName,
            'dependencies' => $dependencies
        ]);
    }

    // Create tool executor with frontend tools
    $executor = new ToolExecutor($logger, $redis);

    // Register frontend tools with executor
    foreach ($frontendTools as $toolName => $tool) {
        $executor->registerTool($toolName, $tool);
    }

    // Execute chain
    $logger->info('Executing workflow chain', ['chain_id' => $chainId]);

    $startTime = microtime(true);
    $result = $orchestrator->executeChain($chainId, $executor);
    $durationMs = round((microtime(true) - $startTime) * 1000);

    // Update execution record
    $status = $result->success ? 'completed' : 'failed';
    $stepsCompleted = count($result->completed);
    $stepsFailed = count($result->failed);
    $resultJson = json_encode([
        'completed' => $result->completed,
        'failed' => $result->failed,
        'summary' => $result->getSummary()
    ]);
    $errorMessage = $result->success ? null : implode('; ', array_map(function($f) {
        return $f['error'] ?? 'Unknown error';
    }, $result->failed));

    $stmt = $db->prepare("
        UPDATE frontend_workflow_executions
        SET status = ?,
            completed_at = NOW(),
            duration_ms = ?,
            steps_completed = ?,
            steps_failed = ?,
            result_json = ?,
            error_message = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        'siiissi',
        $status,
        $durationMs,
        $stepsCompleted,
        $stepsFailed,
        $resultJson,
        $errorMessage,
        $executionDbId
    );
    $stmt->execute();

    // Update workflow execution count
    if ($workflowId) {
        $db->query("
            UPDATE frontend_workflows
            SET execution_count = execution_count + 1,
                last_executed = NOW()
            WHERE id = $workflowId
        ");
    }

    $logger->info('Workflow execution complete', [
        'execution_id' => $executionId,
        'success' => $result->success,
        'duration_ms' => $durationMs,
        'steps_completed' => $stepsCompleted,
        'steps_failed' => $stepsFailed
    ]);

    // Build response
    echo json_encode([
        'success' => $result->success,
        'execution_id' => $executionId,
        'duration_ms' => $durationMs,
        'steps_total' => $stepsTotal,
        'steps_completed' => $stepsCompleted,
        'steps_failed' => $stepsFailed,
        'summary' => $result->getSummary(),
        'results' => $result->completed,
        'errors' => $result->failed,
        'dashboard_url' => 'https://gpt.ecigdis.co.nz/ai-agent/public/dashboard/executions.php?id=' . $executionId
    ]);

} catch (Exception $e) {
    $logger->error('Workflow execution exception', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);

    // Update execution as failed
    if (isset($executionDbId)) {
        $db->query("
            UPDATE frontend_workflow_executions
            SET status = 'failed',
                completed_at = NOW(),
                error_message = '" . $db->real_escape_string($e->getMessage()) . "'
            WHERE id = $executionDbId
        ");
    }

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
