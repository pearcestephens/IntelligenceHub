<?php
/**
 * Bot Orchestrator API
 * Enterprise Bot Management Backend
 *
 * Endpoints:
 * - GET  ?action=list_instances - List all bot instances
 * - POST ?action=create_instance - Create new bot instance
 * - POST ?action=start_instance - Start bot instance
 * - POST ?action=stop_instance - Stop bot instance
 * - POST ?action=restart_instance - Restart bot instance
 * - POST ?action=scale_instances - Scale instances up/down
 * - GET  ?action=get_metrics - Get performance metrics
 * - GET  ?action=stream_logs - Stream logs (SSE)
 * - GET  ?action=list_projects - List all projects
 * - POST ?action=create_project - Create new project
 * - POST ?action=assign_bot - Assign bot to project
 * - GET  ?action=list_servers - List all servers
 * - POST ?action=add_server - Add new server
 * - POST ?action=deploy - Deploy bot to server
 * - GET  ?action=deployment_status - Get deployment status
 * - POST ?action=rollback - Rollback deployment
 * - GET  ?action=list_chains - List event chains
 * - POST ?action=create_chain - Create event chain
 * - POST ?action=deploy_chain - Deploy event chain
 * - GET  ?action=get_stats - Get system statistics
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// Get database connection
try {
    $pdo = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
        $config['db_user'],
        $config['db_pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_response('Database connection failed: ' . $e->getMessage());
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list_instances':
        listBotInstances($pdo);
        break;

    case 'create_instance':
        createBotInstance($pdo);
        break;

    case 'start_instance':
        startBotInstance($pdo);
        break;

    case 'stop_instance':
        stopBotInstance($pdo);
        break;

    case 'restart_instance':
        restartBotInstance($pdo);
        break;

    case 'scale_instances':
        scaleInstances($pdo);
        break;

    case 'get_metrics':
        getBotMetrics($pdo);
        break;

    case 'stream_logs':
        streamBotLogs($pdo);
        break;

    case 'list_projects':
        listProjects($pdo);
        break;

    case 'create_project':
        createProject($pdo);
        break;

    case 'assign_bot':
        assignBotToProject($pdo);
        break;

    case 'list_servers':
        listServers($pdo);
        break;

    case 'add_server':
        addServer($pdo);
        break;

    case 'deploy':
        deployBot($pdo);
        break;

    case 'deployment_status':
        getDeploymentStatus($pdo);
        break;

    case 'rollback':
        rollbackDeployment($pdo);
        break;

    case 'list_chains':
        listEventChains($pdo);
        break;

    case 'create_chain':
        createEventChain($pdo);
        break;

    case 'deploy_chain':
        deployEventChain($pdo);
        break;

    case 'get_stats':
        getSystemStats($pdo);
        break;

    default:
        error_response('Invalid action specified');
}

// ============================================================================
// BOT INSTANCE MANAGEMENT
// ============================================================================

function listBotInstances($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT
                bi.*,
                bs.name as server_name,
                COUNT(DISTINCT bpa.project_id) as project_count,
                (SELECT COUNT(*) FROM bot_logs WHERE bot_instance_id = bi.id AND level IN ('error', 'critical') AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)) as error_count_24h
            FROM bot_instances bi
            LEFT JOIN bot_servers bs ON bi.server_id = bs.id
            LEFT JOIN bot_project_assignments bpa ON bi.id = bpa.bot_instance_id AND bpa.status = 'active'
            GROUP BY bi.id
            ORDER BY bi.created_at DESC
        ");

        $instances = $stmt->fetchAll();

        // Parse JSON fields
        foreach ($instances as &$instance) {
            $instance['config'] = json_decode($instance['config'] ?? '{}', true);
            $instance['resources'] = json_decode($instance['resources'] ?? '{}', true);
            $instance['performance_metrics'] = json_decode($instance['performance_metrics'] ?? '{}', true);
        }

        success_response([
            'instances' => $instances,
            'total_count' => count($instances),
            'online_count' => count(array_filter($instances, fn($i) => $i['status'] === 'online')),
            'offline_count' => count(array_filter($instances, fn($i) => $i['status'] === 'offline'))
        ]);
    } catch (PDOException $e) {
        error_response('Failed to list instances: ' . $e->getMessage());
    }
}

function createBotInstance($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);

    $required = ['bot_id', 'instance_name', 'bot_type', 'model'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            error_response("Missing required field: {$field}");
        }
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO bot_instances (
                bot_id, instance_name, display_name, bot_type, model,
                status, config, resources, created_at
            ) VALUES (
                :bot_id, :instance_name, :display_name, :bot_type, :model,
                'offline', :config, :resources, NOW()
            )
        ");

        $stmt->execute([
            'bot_id' => $data['bot_id'],
            'instance_name' => $data['instance_name'],
            'display_name' => $data['display_name'] ?? $data['instance_name'],
            'bot_type' => $data['bot_type'],
            'model' => $data['model'],
            'config' => json_encode($data['config'] ?? []),
            'resources' => json_encode($data['resources'] ?? [])
        ]);

        $instanceId = $pdo->lastInsertId();

        logBotAction($pdo, $instanceId, 'info', 'Bot instance created');

        success_response([
            'message' => 'Bot instance created successfully',
            'instance_id' => $instanceId
        ]);
    } catch (PDOException $e) {
        error_response('Failed to create instance: ' . $e->getMessage());
    }
}

function startBotInstance($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $instanceId = $data['instance_id'] ?? null;

    if (!$instanceId) {
        error_response('Missing instance_id');
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE bot_instances
            SET status = 'online', started_at = NOW(), last_ping = NOW()
            WHERE id = :id
        ");
        $stmt->execute(['id' => $instanceId]);

        logBotAction($pdo, $instanceId, 'info', 'Bot instance started');

        success_response(['message' => 'Bot instance started successfully']);
    } catch (PDOException $e) {
        error_response('Failed to start instance: ' . $e->getMessage());
    }
}

function stopBotInstance($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $instanceId = $data['instance_id'] ?? null;

    if (!$instanceId) {
        error_response('Missing instance_id');
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE bot_instances
            SET status = 'offline'
            WHERE id = :id
        ");
        $stmt->execute(['id' => $instanceId]);

        logBotAction($pdo, $instanceId, 'info', 'Bot instance stopped');

        success_response(['message' => 'Bot instance stopped successfully']);
    } catch (PDOException $e) {
        error_response('Failed to stop instance: ' . $e->getMessage());
    }
}

function restartBotInstance($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $instanceId = $data['instance_id'] ?? null;

    if (!$instanceId) {
        error_response('Missing instance_id');
    }

    try {
        // Stop first
        $stmt = $pdo->prepare("UPDATE bot_instances SET status = 'stopping' WHERE id = :id");
        $stmt->execute(['id' => $instanceId]);

        sleep(1); // Simulate restart delay

        // Start
        $stmt = $pdo->prepare("
            UPDATE bot_instances
            SET status = 'online', started_at = NOW(), last_ping = NOW()
            WHERE id = :id
        ");
        $stmt->execute(['id' => $instanceId]);

        logBotAction($pdo, $instanceId, 'info', 'Bot instance restarted');

        success_response(['message' => 'Bot instance restarted successfully']);
    } catch (PDOException $e) {
        error_response('Failed to restart instance: ' . $e->getMessage());
    }
}

function scaleInstances($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $botId = $data['bot_id'] ?? null;
    $targetCount = $data['target_count'] ?? null;

    if (!$botId || $targetCount === null) {
        error_response('Missing bot_id or target_count');
    }

    try {
        // Count current instances
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bot_instances WHERE bot_id = :bot_id AND status != 'error'");
        $stmt->execute(['bot_id' => $botId]);
        $currentCount = $stmt->fetchColumn();

        if ($targetCount > $currentCount) {
            // Scale up - create new instances
            $toCreate = $targetCount - $currentCount;
            for ($i = 1; $i <= $toCreate; $i++) {
                $instanceName = $botId . '-instance-' . ($currentCount + $i);
                $stmt = $pdo->prepare("
                    INSERT INTO bot_instances (bot_id, instance_name, display_name, bot_type, model, status)
                    VALUES (:bot_id, :instance_name, :display_name, 'custom', 'gpt-4', 'offline')
                ");
                $stmt->execute([
                    'bot_id' => $botId,
                    'instance_name' => $instanceName,
                    'display_name' => $instanceName
                ]);
            }
            $message = "Scaled up: created {$toCreate} new instances";
        } elseif ($targetCount < $currentCount) {
            // Scale down - stop and remove instances
            $toRemove = $currentCount - $targetCount;
            $stmt = $pdo->prepare("
                SELECT id FROM bot_instances
                WHERE bot_id = :bot_id AND status != 'error'
                ORDER BY created_at DESC LIMIT :limit
            ");
            $stmt->execute(['bot_id' => $botId, 'limit' => $toRemove]);
            $instances = $stmt->fetchAll(PDO::FETCH_COLUMN);

            foreach ($instances as $instanceId) {
                $stmt = $pdo->prepare("UPDATE bot_instances SET status = 'offline' WHERE id = :id");
                $stmt->execute(['id' => $instanceId]);
            }
            $message = "Scaled down: stopped {$toRemove} instances";
        } else {
            $message = "No scaling needed, already at target count";
        }

        success_response([
            'message' => $message,
            'current_count' => $targetCount
        ]);
    } catch (PDOException $e) {
        error_response('Failed to scale instances: ' . $e->getMessage());
    }
}

function getBotMetrics($pdo) {
    $instanceId = $_GET['instance_id'] ?? null;
    $timeframe = $_GET['timeframe'] ?? '24h';

    $interval = match($timeframe) {
        '1h' => 'INTERVAL 1 HOUR',
        '6h' => 'INTERVAL 6 HOUR',
        '24h' => 'INTERVAL 24 HOUR',
        '7d' => 'INTERVAL 7 DAY',
        default => 'INTERVAL 24 HOUR'
    };

    try {
        $query = "
            SELECT
                metric_type,
                metric_name,
                AVG(metric_value) as avg_value,
                MIN(metric_value) as min_value,
                MAX(metric_value) as max_value,
                COUNT(*) as data_points
            FROM bot_metrics
            WHERE recorded_at > DATE_SUB(NOW(), {$interval})
        ";

        if ($instanceId) {
            $query .= " AND bot_instance_id = :instance_id";
        }

        $query .= " GROUP BY metric_type, metric_name ORDER BY metric_type, metric_name";

        $stmt = $pdo->prepare($query);
        if ($instanceId) {
            $stmt->execute(['instance_id' => $instanceId]);
        } else {
            $stmt->execute();
        }

        $metrics = $stmt->fetchAll();

        success_response([
            'metrics' => $metrics,
            'timeframe' => $timeframe
        ]);
    } catch (PDOException $e) {
        error_response('Failed to get metrics: ' . $e->getMessage());
    }
}

function streamBotLogs($pdo) {
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');

    $instanceId = $_GET['instance_id'] ?? null;
    $lastId = $_GET['last_id'] ?? 0;

    try {
        while (true) {
            $query = "
                SELECT id, level, message, source, created_at
                FROM bot_logs
                WHERE id > :last_id
            ";

            if ($instanceId) {
                $query .= " AND bot_instance_id = :instance_id";
            }

            $query .= " ORDER BY id ASC LIMIT 50";

            $stmt = $pdo->prepare($query);
            $params = ['last_id' => $lastId];
            if ($instanceId) {
                $params['instance_id'] = $instanceId;
            }
            $stmt->execute($params);

            $logs = $stmt->fetchAll();

            if (!empty($logs)) {
                foreach ($logs as $log) {
                    echo "data: " . json_encode($log) . "\n\n";
                    $lastId = $log['id'];
                }
                flush();
            }

            sleep(2); // Poll every 2 seconds

            // Check if connection is still alive
            if (connection_aborted()) {
                break;
            }
        }
    } catch (PDOException $e) {
        echo "data: " . json_encode(['error' => $e->getMessage()]) . "\n\n";
    }
    exit;
}

// ============================================================================
// PROJECT MANAGEMENT
// ============================================================================

function listProjects($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT
                bp.*,
                COUNT(DISTINCT bpa.bot_instance_id) as assigned_bots,
                COUNT(DISTINCT bpt.id) as total_tasks,
                SUM(CASE WHEN bpt.status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
                SUM(CASE WHEN bpt.status = 'in_progress' THEN 1 ELSE 0 END) as active_tasks
            FROM bot_projects bp
            LEFT JOIN bot_project_assignments bpa ON bp.id = bpa.project_id AND bpa.status = 'active'
            LEFT JOIN bot_project_tasks bpt ON bp.id = bpt.project_id
            GROUP BY bp.id
            ORDER BY bp.created_at DESC
        ");

        $projects = $stmt->fetchAll();

        foreach ($projects as &$project) {
            $project['config'] = json_decode($project['config'] ?? '{}', true);
            $project['metadata'] = json_decode($project['metadata'] ?? '{}', true);
        }

        success_response(['projects' => $projects]);
    } catch (PDOException $e) {
        error_response('Failed to list projects: ' . $e->getMessage());
    }
}

function createProject($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['name'])) {
        error_response('Missing project name');
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO bot_projects (
                name, description, repository_url, project_type,
                status, priority, config, metadata, created_by
            ) VALUES (
                :name, :description, :repository_url, :project_type,
                'active', :priority, :config, :metadata, :created_by
            )
        ");

        $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'repository_url' => $data['repository_url'] ?? null,
            'project_type' => $data['project_type'] ?? 'web-app',
            'priority' => $data['priority'] ?? 'medium',
            'config' => json_encode($data['config'] ?? []),
            'metadata' => json_encode($data['metadata'] ?? []),
            'created_by' => $_SESSION['user_id'] ?? null
        ]);

        $projectId = $pdo->lastInsertId();

        success_response([
            'message' => 'Project created successfully',
            'project_id' => $projectId
        ]);
    } catch (PDOException $e) {
        error_response('Failed to create project: ' . $e->getMessage());
    }
}

function assignBotToProject($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);

    $projectId = $data['project_id'] ?? null;
    $botInstanceId = $data['bot_instance_id'] ?? null;

    if (!$projectId || !$botInstanceId) {
        error_response('Missing project_id or bot_instance_id');
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO bot_project_assignments (
                project_id, bot_instance_id, role, responsibilities, config, status
            ) VALUES (
                :project_id, :bot_instance_id, :role, :responsibilities, :config, 'active'
            )
        ");

        $stmt->execute([
            'project_id' => $projectId,
            'bot_instance_id' => $botInstanceId,
            'role' => $data['role'] ?? 'Developer',
            'responsibilities' => $data['responsibilities'] ?? '',
            'config' => json_encode($data['config'] ?? [])
        ]);

        logBotAction($pdo, $botInstanceId, 'info', "Assigned to project {$projectId}");

        success_response(['message' => 'Bot assigned to project successfully']);
    } catch (PDOException $e) {
        error_response('Failed to assign bot: ' . $e->getMessage());
    }
}

// ============================================================================
// SERVER & DEPLOYMENT MANAGEMENT
// ============================================================================

function listServers($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT
                bs.*,
                COUNT(DISTINCT bi.id) as deployed_bots,
                COUNT(DISTINCT bd.id) as total_deployments
            FROM bot_servers bs
            LEFT JOIN bot_instances bi ON bs.id = bi.server_id
            LEFT JOIN bot_deployments bd ON bs.id = bd.server_id
            GROUP BY bs.id
            ORDER BY bs.name
        ");

        $servers = $stmt->fetchAll();

        foreach ($servers as &$server) {
            $server['capabilities'] = json_decode($server['capabilities'] ?? '[]', true);
            $server['resources'] = json_decode($server['resources'] ?? '{}', true);
            $server['metadata'] = json_decode($server['metadata'] ?? '{}', true);
            // Don't expose encrypted credentials
            unset($server['credentials_encrypted']);
        }

        success_response(['servers' => $servers]);
    } catch (PDOException $e) {
        error_response('Failed to list servers: ' . $e->getMessage());
    }
}

function addServer($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['name']) || empty($data['hostname'])) {
        error_response('Missing name or hostname');
    }

    try {
        // Encrypt credentials (basic encryption - use proper encryption in production)
        $credentials = $data['credentials'] ?? [];
        $encryptedCredentials = base64_encode(json_encode($credentials));

        $stmt = $pdo->prepare("
            INSERT INTO bot_servers (
                name, hostname, ip_address, port, server_type,
                credentials_encrypted, auth_method, status, capabilities, metadata
            ) VALUES (
                :name, :hostname, :ip_address, :port, :server_type,
                :credentials_encrypted, :auth_method, 'offline', :capabilities, :metadata
            )
        ");

        $stmt->execute([
            'name' => $data['name'],
            'hostname' => $data['hostname'],
            'ip_address' => $data['ip_address'] ?? null,
            'port' => $data['port'] ?? 22,
            'server_type' => $data['server_type'] ?? 'development',
            'credentials_encrypted' => $encryptedCredentials,
            'auth_method' => $data['auth_method'] ?? 'ssh-key',
            'capabilities' => json_encode($data['capabilities'] ?? []),
            'metadata' => json_encode($data['metadata'] ?? [])
        ]);

        $serverId = $pdo->lastInsertId();

        success_response([
            'message' => 'Server added successfully',
            'server_id' => $serverId
        ]);
    } catch (PDOException $e) {
        error_response('Failed to add server: ' . $e->getMessage());
    }
}

function deployBot($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);

    $botInstanceId = $data['bot_instance_id'] ?? null;
    $serverId = $data['server_id'] ?? null;

    if (!$botInstanceId || !$serverId) {
        error_response('Missing bot_instance_id or server_id');
    }

    try {
        // Create deployment record
        $stmt = $pdo->prepare("
            INSERT INTO bot_deployments (
                bot_instance_id, server_id, deployment_type, status,
                version, config, deployed_by
            ) VALUES (
                :bot_instance_id, :server_id, :deployment_type, 'deploying',
                :version, :config, :deployed_by
            )
        ");

        $stmt->execute([
            'bot_instance_id' => $botInstanceId,
            'server_id' => $serverId,
            'deployment_type' => $data['deployment_type'] ?? 'new',
            'version' => $data['version'] ?? '1.0.0',
            'config' => json_encode($data['config'] ?? []),
            'deployed_by' => $_SESSION['user_id'] ?? null
        ]);

        $deploymentId = $pdo->lastInsertId();

        // Simulate deployment (in reality, this would trigger actual deployment)
        sleep(2);

        // Update status
        $stmt = $pdo->prepare("
            UPDATE bot_deployments
            SET status = 'deployed', deployed_at = NOW(), completed_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute(['id' => $deploymentId]);

        // Update bot instance server_id
        $stmt = $pdo->prepare("UPDATE bot_instances SET server_id = :server_id WHERE id = :id");
        $stmt->execute(['server_id' => $serverId, 'id' => $botInstanceId]);

        logBotAction($pdo, $botInstanceId, 'info', "Deployed to server {$serverId}");

        success_response([
            'message' => 'Bot deployed successfully',
            'deployment_id' => $deploymentId
        ]);
    } catch (PDOException $e) {
        error_response('Failed to deploy bot: ' . $e->getMessage());
    }
}

function getDeploymentStatus($pdo) {
    $deploymentId = $_GET['deployment_id'] ?? null;

    if (!$deploymentId) {
        error_response('Missing deployment_id');
    }

    try {
        $stmt = $pdo->prepare("
            SELECT
                bd.*,
                bi.display_name as bot_name,
                bs.name as server_name
            FROM bot_deployments bd
            JOIN bot_instances bi ON bd.bot_instance_id = bi.id
            JOIN bot_servers bs ON bd.server_id = bs.id
            WHERE bd.id = :id
        ");
        $stmt->execute(['id' => $deploymentId]);

        $deployment = $stmt->fetch();

        if (!$deployment) {
            error_response('Deployment not found');
        }

        $deployment['config'] = json_decode($deployment['config'] ?? '{}', true);

        success_response(['deployment' => $deployment]);
    } catch (PDOException $e) {
        error_response('Failed to get deployment status: ' . $e->getMessage());
    }
}

function rollbackDeployment($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $deploymentId = $data['deployment_id'] ?? null;

    if (!$deploymentId) {
        error_response('Missing deployment_id');
    }

    try {
        // Get deployment info
        $stmt = $pdo->prepare("SELECT * FROM bot_deployments WHERE id = :id");
        $stmt->execute(['id' => $deploymentId]);
        $deployment = $stmt->fetch();

        if (!$deployment) {
            error_response('Deployment not found');
        }

        // Mark as rolled back
        $stmt = $pdo->prepare("UPDATE bot_deployments SET status = 'rolled_back' WHERE id = :id");
        $stmt->execute(['id' => $deploymentId]);

        logBotAction($pdo, $deployment['bot_instance_id'], 'warning', "Deployment {$deploymentId} rolled back");

        success_response(['message' => 'Deployment rolled back successfully']);
    } catch (PDOException $e) {
        error_response('Failed to rollback deployment: ' . $e->getMessage());
    }
}

// ============================================================================
// EVENT CHAIN MANAGEMENT
// ============================================================================

function listEventChains($pdo) {
    try {
        $stmt = $pdo->query("
            SELECT
                bec.*,
                COUNT(DISTINCT bece.id) as total_executions,
                SUM(CASE WHEN bece.status = 'completed' THEN 1 ELSE 0 END) as successful_executions,
                SUM(CASE WHEN bece.status = 'failed' THEN 1 ELSE 0 END) as failed_executions
            FROM bot_event_chains bec
            LEFT JOIN bot_event_chain_executions bece ON bec.id = bece.chain_id
            GROUP BY bec.id
            ORDER BY bec.created_at DESC
        ");

        $chains = $stmt->fetchAll();

        foreach ($chains as &$chain) {
            $chain['trigger_config'] = json_decode($chain['trigger_config'] ?? '{}', true);
            $chain['workflow_json'] = json_decode($chain['workflow_json'] ?? '{}', true);
        }

        success_response(['chains' => $chains]);
    } catch (PDOException $e) {
        error_response('Failed to list event chains: ' . $e->getMessage());
    }
}

function createEventChain($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['name']) || empty($data['trigger_type'])) {
        error_response('Missing name or trigger_type');
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO bot_event_chains (
                name, description, trigger_type, trigger_config,
                workflow_json, status, created_by
            ) VALUES (
                :name, :description, :trigger_type, :trigger_config,
                :workflow_json, 'inactive', :created_by
            )
        ");

        $stmt->execute([
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'trigger_type' => $data['trigger_type'],
            'trigger_config' => json_encode($data['trigger_config'] ?? []),
            'workflow_json' => json_encode($data['workflow_json'] ?? []),
            'created_by' => $_SESSION['user_id'] ?? null
        ]);

        $chainId = $pdo->lastInsertId();

        success_response([
            'message' => 'Event chain created successfully',
            'chain_id' => $chainId
        ]);
    } catch (PDOException $e) {
        error_response('Failed to create event chain: ' . $e->getMessage());
    }
}

function deployEventChain($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    $chainId = $data['chain_id'] ?? null;

    if (!$chainId) {
        error_response('Missing chain_id');
    }

    try {
        $stmt = $pdo->prepare("UPDATE bot_event_chains SET status = 'active' WHERE id = :id");
        $stmt->execute(['id' => $chainId]);

        success_response(['message' => 'Event chain deployed successfully']);
    } catch (PDOException $e) {
        error_response('Failed to deploy event chain: ' . $e->getMessage());
    }
}

// ============================================================================
// SYSTEM STATISTICS
// ============================================================================

function getSystemStats($pdo) {
    try {
        $stats = [];

        // Bot instance stats
        $stmt = $pdo->query("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'online' THEN 1 ELSE 0 END) as online,
                SUM(CASE WHEN status = 'offline' THEN 1 ELSE 0 END) as offline,
                SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as error,
                SUM(total_tasks) as total_tasks,
                SUM(completed_tasks) as completed_tasks,
                AVG(uptime_percentage) as avg_uptime,
                AVG(avg_response_time) as avg_response
            FROM bot_instances
        ");
        $stats['instances'] = $stmt->fetch();

        // Project stats
        $stmt = $pdo->query("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                AVG(progress) as avg_progress
            FROM bot_projects
        ");
        $stats['projects'] = $stmt->fetch();

        // Deployment stats
        $stmt = $pdo->query("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'deployed' THEN 1 ELSE 0 END) as deployed,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
            FROM bot_deployments
        ");
        $stats['deployments'] = $stmt->fetch();

        // Server stats
        $stmt = $pdo->query("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'online' THEN 1 ELSE 0 END) as online
            FROM bot_servers
        ");
        $stats['servers'] = $stmt->fetch();

        // Alert stats
        $stmt = $pdo->query("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) as new_alerts,
                SUM(CASE WHEN severity = 'critical' THEN 1 ELSE 0 END) as critical
            FROM bot_alerts
            WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $stats['alerts'] = $stmt->fetch();

        success_response(['stats' => $stats]);
    } catch (PDOException $e) {
        error_response('Failed to get system stats: ' . $e->getMessage());
    }
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

function logBotAction($pdo, $botInstanceId, $level, $message, $context = []) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO bot_logs (bot_instance_id, level, message, context, source)
            VALUES (:bot_instance_id, :level, :message, :context, 'api')
        ");
        $stmt->execute([
            'bot_instance_id' => $botInstanceId,
            'level' => $level,
            'message' => $message,
            'context' => json_encode($context)
        ]);
    } catch (PDOException $e) {
        // Silent fail - don't break main operation
        error_log("Failed to log bot action: " . $e->getMessage());
    }
}

function success_response($data) {
    echo json_encode([
        'success' => true,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}

function error_response($message, $code = 400) {
    http_response_code($code);
    echo json_encode([
        'success' => false,
        'error' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    exit;
}
