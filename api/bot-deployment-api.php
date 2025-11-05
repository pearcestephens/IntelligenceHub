<?php
/**
 * Bot Deployment Center - Simplified API Endpoint
 * Standalone API that works without complex dependencies
 *
 * @package IntelligenceHub\BotManagement
 * @version 2.0.0
 */

declare(strict_types=1);

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Database connection
function getDB(): mysqli {
    static $db = null;
    if ($db === null) {
        $db = new mysqli('127.0.0.1', 'hdgwrzntwa', 'bFUdRjh4Jx', 'hdgwrzntwa');
        if ($db->connect_error) {
            throw new Exception("Database connection failed: " . $db->connect_error);
        }
        $db->set_charset('utf8mb4');
    }
    return $db;
}

// Response helper
function sendResponse($success, $data = null, $message = '', $code = 200) {
    http_response_code($code);
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message,
        'timestamp' => date('c')
    ]);
    exit;
}

// Error handler
function handleError($message, $code = 500) {
    error_log("Bot API Error: $message");
    sendResponse(false, null, $message, $code);
}

try {
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    
    $action = $input['action'] ?? $_GET['action'] ?? null;
    
    if (!$action) {
        handleError('No action specified', 400);
    }
    
    $db = getDB();
    
    // ========================================================================
    // API ACTIONS
    // ========================================================================
    
    switch ($action) {
        
        // LIST ALL BOTS
        case 'listBots':
            $result = $db->query("
                SELECT 
                    bot_id,
                    bot_name,
                    bot_role,
                    system_prompt,
                    status,
                    config_json,
                    last_execution,
                    next_execution,
                    created_at
                FROM bot_deployments
                WHERE status != 'archived'
                ORDER BY bot_id ASC
            ");
            
            $bots = [];
            while ($row = $result->fetch_assoc()) {
                $row['config_json'] = json_decode($row['config_json'] ?? '{}', true);
                $bots[] = $row;
            }
            
            sendResponse(true, $bots, count($bots) . ' bots found');
            break;
        
        // DEPLOY NEW BOT
        case 'deployBot':
            $name = $input['name'] ?? null;
            $role = $input['role'] ?? null;
            $systemPrompt = $input['system_prompt'] ?? null;
            $schedule = $input['schedule'] ?? null;
            
            if (!$name || !$role || !$systemPrompt) {
                handleError('Missing required fields: name, role, system_prompt', 400);
            }
            
            $config = json_encode([
                'model' => $input['model'] ?? 'gpt-4-turbo-preview',
                'temperature' => $input['temperature'] ?? 0.7,
                'max_tokens' => $input['max_tokens'] ?? 2000,
                'enable_tools' => $input['enable_tools'] ?? true,
                'enable_rag' => $input['enable_rag'] ?? true
            ]);
            
            $stmt = $db->prepare("
                INSERT INTO bot_deployments 
                (bot_name, bot_role, system_prompt, schedule_cron, status, config_json)
                VALUES (?, ?, ?, ?, 'active', ?)
            ");
            $stmt->bind_param('sssss', $name, $role, $systemPrompt, $schedule, $config);
            $stmt->execute();
            
            $botId = $db->insert_id;
            
            sendResponse(true, [
                'bot_id' => $botId,
                'bot_name' => $name,
                'bot_role' => $role,
                'status' => 'active'
            ], 'Bot deployed successfully');
            break;
        
        // EXECUTE BOT MANUALLY
        case 'executeBot':
            $botId = $input['bot_id'] ?? null;
            $inputData = $input['input'] ?? null;
            
            if (!$botId || !$inputData) {
                handleError('Missing bot_id or input', 400);
            }
            
            // Get bot details
            $stmt = $db->prepare("SELECT * FROM bot_deployments WHERE bot_id = ?");
            $stmt->bind_param('i', $botId);
            $stmt->execute();
            $bot = $stmt->get_result()->fetch_assoc();
            
            if (!$bot) {
                handleError('Bot not found', 404);
            }
            
            $startTime = microtime(true);
            
            // Simulate bot execution (you can integrate with your AI agent here)
            $output = "Bot '{$bot['bot_name']}' processed: $inputData";
            
            $executionTime = round((microtime(true) - $startTime) * 1000);
            
            // Log execution
            $stmt = $db->prepare("
                INSERT INTO bot_execution_logs 
                (bot_id, execution_type, status, input_data, output_data, execution_time_ms)
                VALUES (?, 'manual', 'success', ?, ?, ?)
            ");
            $inputJson = json_encode(['input' => $inputData]);
            $outputJson = json_encode(['output' => $output]);
            $stmt->bind_param('issi', $botId, $inputJson, $outputJson, $executionTime);
            $stmt->execute();
            
            // Update last execution
            $db->query("UPDATE bot_deployments SET last_execution = NOW() WHERE bot_id = $botId");
            
            sendResponse(true, [
                'bot_id' => $botId,
                'output' => $output,
                'execution_time_ms' => $executionTime
            ], 'Bot executed successfully');
            break;
        
        // START MULTI-THREAD SESSION
        case 'startMultiThread':
            $topic = $input['topic'] ?? null;
            $threadCount = $input['thread_count'] ?? 4;
            $botAssignments = $input['bot_assignments'] ?? [];
            
            if (!$topic) {
                handleError('Topic is required', 400);
            }
            
            $sessionId = 'session_' . bin2hex(random_bytes(8));
            
            // Create session
            $stmt = $db->prepare("
                INSERT INTO multi_thread_sessions 
                (session_id, topic, thread_count, status, metadata)
                VALUES (?, ?, ?, 'active', ?)
            ");
            $metadata = json_encode(['bot_assignments' => $botAssignments]);
            $stmt->bind_param('ssis', $sessionId, $topic, $threadCount, $metadata);
            $stmt->execute();
            
            // Create threads
            $threads = [];
            for ($i = 1; $i <= $threadCount; $i++) {
                $threadId = $sessionId . '_thread_' . $i;
                $botId = $botAssignments[$i] ?? null;
                
                $stmt = $db->prepare("
                    INSERT INTO conversation_threads 
                    (thread_id, session_id, thread_number, topic, bot_id, status)
                    VALUES (?, ?, ?, ?, ?, 'active')
                ");
                $stmt->bind_param('ssisi', $threadId, $sessionId, $i, $topic, $botId);
                $stmt->execute();
                
                $threads[] = [
                    'thread_id' => $threadId,
                    'thread_number' => $i,
                    'bot_id' => $botId
                ];
            }
            
            sendResponse(true, [
                'session_id' => $sessionId,
                'topic' => $topic,
                'thread_count' => $threadCount,
                'threads' => $threads,
                'status' => 'active'
            ], 'Multi-thread session started');
            break;
        
        // GET SESSION STATUS
        case 'getSessionStatus':
            $sessionId = $input['session_id'] ?? null;
            
            if (!$sessionId) {
                handleError('session_id is required', 400);
            }
            
            // Get session
            $stmt = $db->prepare("SELECT * FROM multi_thread_sessions WHERE session_id = ?");
            $stmt->bind_param('s', $sessionId);
            $stmt->execute();
            $session = $stmt->get_result()->fetch_assoc();
            
            if (!$session) {
                handleError('Session not found', 404);
            }
            
            // Get threads with messages
            $stmt = $db->prepare("
                SELECT 
                    t.*,
                    b.bot_name,
                    (SELECT COUNT(*) FROM thread_messages WHERE thread_id = t.thread_id) as message_count
                FROM conversation_threads t
                LEFT JOIN bot_deployments b ON t.bot_id = b.bot_id
                WHERE t.session_id = ?
                ORDER BY t.thread_number ASC
            ");
            $stmt->bind_param('s', $sessionId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $threads = [];
            $completedThreads = 0;
            
            while ($thread = $result->fetch_assoc()) {
                // Get messages for this thread
                $stmt2 = $db->prepare("
                    SELECT * FROM thread_messages 
                    WHERE thread_id = ? 
                    ORDER BY created_at ASC
                ");
                $stmt2->bind_param('s', $thread['thread_id']);
                $stmt2->execute();
                $messages = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
                
                $thread['messages'] = $messages;
                $threads[] = $thread;
                
                if ($thread['status'] === 'completed') {
                    $completedThreads++;
                }
            }
            
            $session['threads'] = $threads;
            $session['completed_threads'] = $completedThreads;
            $session['metadata'] = json_decode($session['metadata'] ?? '{}', true);
            
            sendResponse(true, $session, 'Session status retrieved');
            break;
        
        // STOP SESSION
        case 'stopSession':
            $sessionId = $input['session_id'] ?? null;
            
            if (!$sessionId) {
                handleError('session_id is required', 400);
            }
            
            $stmt = $db->prepare("
                UPDATE multi_thread_sessions 
                SET status = 'abandoned', completed_at = NOW()
                WHERE session_id = ?
            ");
            $stmt->bind_param('s', $sessionId);
            $stmt->execute();
            
            sendResponse(true, ['session_id' => $sessionId], 'Session stopped');
            break;
        
        // PAUSE/RESUME BOT
        case 'pauseBot':
            $botId = $input['bot_id'] ?? null;
            
            if (!$botId) {
                handleError('bot_id is required', 400);
            }
            
            // Toggle status
            $stmt = $db->prepare("
                UPDATE bot_deployments 
                SET status = CASE 
                    WHEN status = 'active' THEN 'paused'
                    WHEN status = 'paused' THEN 'active'
                    ELSE status
                END
                WHERE bot_id = ?
            ");
            $stmt->bind_param('i', $botId);
            $stmt->execute();
            
            sendResponse(true, ['bot_id' => $botId], 'Bot status toggled');
            break;
        
        // GET BOT DETAILS
        case 'getBotDetails':
            $botId = $input['bot_id'] ?? null;
            
            if (!$botId) {
                handleError('bot_id is required', 400);
            }
            
            $stmt = $db->prepare("SELECT * FROM v_bot_performance WHERE bot_id = ?");
            $stmt->bind_param('i', $botId);
            $stmt->execute();
            $bot = $stmt->get_result()->fetch_assoc();
            
            if (!$bot) {
                handleError('Bot not found', 404);
            }
            
            $successRate = $bot['total_executions'] > 0 
                ? round(($bot['successful_runs'] / $bot['total_executions']) * 100, 2)
                : 0;
            
            $bot['success_rate'] = $successRate;
            
            sendResponse(true, $bot, 'Bot details retrieved');
            break;
        
        // GET METRICS
        case 'getMetrics':
            $metrics = [
                'active_bots' => 0,
                'active_sessions' => 0,
                'total_threads' => 0,
                'success_rate' => 0
            ];
            
            // Active bots
            $result = $db->query("SELECT COUNT(*) as count FROM bot_deployments WHERE status = 'active'");
            $metrics['active_bots'] = $result->fetch_assoc()['count'];
            
            // Active sessions
            $result = $db->query("SELECT COUNT(*) as count FROM multi_thread_sessions WHERE status = 'active'");
            $metrics['active_sessions'] = $result->fetch_assoc()['count'];
            
            // Total threads
            $result = $db->query("SELECT COUNT(*) as count FROM conversation_threads WHERE status = 'active'");
            $metrics['total_threads'] = $result->fetch_assoc()['count'];
            
            // Success rate
            $result = $db->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful
                FROM bot_execution_logs
                WHERE executed_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");
            $row = $result->fetch_assoc();
            $metrics['success_rate'] = $row['total'] > 0 
                ? round(($row['successful'] / $row['total']) * 100, 2)
                : 100;
            
            sendResponse(true, $metrics, 'Metrics retrieved');
            break;
        
        default:
            handleError('Unknown action: ' . $action, 400);
    }
    
} catch (Exception $e) {
    handleError($e->getMessage(), 500);
} catch (Error $e) {
    handleError($e->getMessage(), 500);
}
