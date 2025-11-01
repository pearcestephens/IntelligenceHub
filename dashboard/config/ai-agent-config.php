<?php
/**
 * AI Agent Configuration for Dashboard Integration
 *
 * Provides easy access to AI Agent functionality from the main dashboard
 *
 * @package CIS Intelligence Dashboard
 * @version 1.0.0
 */

if (!defined('DASHBOARD_ACCESS')) {
    die('Direct access not permitted');
}

// AI Agent paths
define('AI_AGENT_ROOT', dirname(__DIR__, 2) . '/ai-agent');
define('AI_AGENT_AUTOLOAD', AI_AGENT_ROOT . '/autoload.php');
define('AI_AGENT_CONFIG', AI_AGENT_ROOT . '/config/app.php');
define('AI_AGENT_ENV', AI_AGENT_ROOT . '/.env');

/**
 * Initialize AI Agent system
 *
 * @return bool Success status
 */
function initAIAgent(): bool {
    static $initialized = false;

    if ($initialized) {
        return true;
    }

    // Check if AI Agent exists
    if (!file_exists(AI_AGENT_ROOT)) {
        error_log('AI Agent not found at: ' . AI_AGENT_ROOT);
        return false;
    }

    // Load AI Agent autoloader
    if (file_exists(AI_AGENT_AUTOLOAD)) {
        require_once AI_AGENT_AUTOLOAD;
    } else {
        error_log('AI Agent autoloader not found');
        return false;
    }

    // Load environment variables
    if (file_exists(AI_AGENT_ENV)) {
        $envVars = @parse_ini_file(AI_AGENT_ENV, false, INI_SCANNER_RAW);
        if ($envVars === false) {
            // If parse fails, try loading manually (comments may have special chars)
            $lines = file(AI_AGENT_ENV, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $envVars = [];
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || $line[0] === '#') continue;
                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $envVars[trim($key)] = trim($value);
                }
            }
        }
        if ($envVars && is_array($envVars)) {
            foreach ($envVars as $key => $value) {
                if (!isset($_ENV[$key])) {
                    $_ENV[$key] = $value;
                    // Note: putenv() is disabled on this server, using $_ENV only
                }
            }
        }
    }

    $initialized = true;
    return true;
}

/**
 * Get AI Agent database connection
 *
 * @return PDO|null
 */
function getAIAgentDB(): ?PDO {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    try {
        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $dbname = $_ENV['DB_NAME'] ?? 'jcepnzzkmj';
        $user = $_ENV['DB_USER'] ?? 'jcepnzzkmj';
        $pass = $_ENV['DB_PASS'] ?? 'wprKh9Jq63';

        $pdo = new PDO(
            "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );

        return $pdo;
    } catch (PDOException $e) {
        error_log('AI Agent DB connection failed: ' . $e->getMessage());
        return null;
    }
}

/**
 * Get AI Agent statistics
 *
 * @return array
 */
function getAIAgentStats(): array {
    $stats = [
        'conversations' => 0,
        'messages' => 0,
        'kb_docs' => 0,
        'tool_calls' => 0,
        'status' => 'offline'
    ];

    $db = getAIAgentDB();
    if (!$db) {
        return $stats;
    }

    try {
        // Check if tables exist
        $tables = $db->query("SHOW TABLES LIKE 'agent_%'")->fetchAll(PDO::FETCH_COLUMN);
        if (empty($tables)) {
            $stats['status'] = 'not_configured';
            return $stats;
        }

        // Get statistics
        $stats['conversations'] = (int) $db->query("SELECT COUNT(*) FROM agent_conversations")->fetchColumn();
        $stats['messages'] = (int) $db->query("SELECT COUNT(*) FROM agent_messages")->fetchColumn();
        $stats['kb_docs'] = (int) $db->query("SELECT COUNT(*) FROM agent_kb_docs")->fetchColumn();
        $stats['tool_calls'] = (int) $db->query("SELECT COUNT(*) FROM agent_tool_calls")->fetchColumn();
        $stats['status'] = 'online';

        // Get today's activity
        $stats['conversations_today'] = (int) $db->query(
            "SELECT COUNT(*) FROM agent_conversations WHERE DATE(created_at) = CURDATE()"
        )->fetchColumn();

        $stats['messages_today'] = (int) $db->query(
            "SELECT COUNT(*) FROM agent_messages WHERE DATE(created_at) = CURDATE()"
        )->fetchColumn();

        // Get recent conversation
        $recent = $db->query(
            "SELECT * FROM agent_conversations ORDER BY updated_at DESC LIMIT 1"
        )->fetch();

        $stats['last_activity'] = $recent ? $recent['updated_at'] : null;

    } catch (PDOException $e) {
        error_log('AI Agent stats query failed: ' . $e->getMessage());
        $stats['status'] = 'error';
        $stats['error'] = $e->getMessage();
    }

    return $stats;
}

/**
 * Check if AI Agent is configured and ready
 *
 * @return bool
 */
function isAIAgentReady(): bool {
    $stats = getAIAgentStats();
    return $stats['status'] === 'online';
}

/**
 * Get AI Agent configuration status
 *
 * @return array
 */
function getAIAgentConfigStatus(): array {
    $status = [
        'directory_exists' => file_exists(AI_AGENT_ROOT),
        'autoload_exists' => file_exists(AI_AGENT_AUTOLOAD),
        'env_exists' => file_exists(AI_AGENT_ENV),
        'database_ready' => false,
        'tables_created' => false,
        'ready' => false
    ];

    if ($status['directory_exists'] && $status['autoload_exists'] && $status['env_exists']) {
        $db = getAIAgentDB();
        if ($db) {
            $status['database_ready'] = true;

            $tables = $db->query("SHOW TABLES LIKE 'agent_%'")->fetchAll(PDO::FETCH_COLUMN);
            if (count($tables) >= 5) {
                $status['tables_created'] = true;
                $status['ready'] = true;
            }
        }
    }

    return $status;
}

/**
 * Get recent AI Agent conversations
 *
 * @param int $limit
 * @return array
 */
function getRecentAIAgentConversations(int $limit = 10): array {
    $db = getAIAgentDB();
    if (!$db) {
        return [];
    }

    try {
        $stmt = $db->prepare("
            SELECT c.*, COUNT(m.id) as message_count
            FROM agent_conversations c
            LEFT JOIN agent_messages m ON c.id = m.conversation_id
            GROUP BY c.id
            ORDER BY c.updated_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Failed to fetch AI Agent conversations: ' . $e->getMessage());
        return [];
    }
}

/**
 * Get AI Agent tool usage statistics
 *
 * @param int $limit
 * @return array
 */
function getAIAgentToolStats(int $limit = 10): array {
    $db = getAIAgentDB();
    if (!$db) {
        return [];
    }

    try {
        $stmt = $db->prepare("
            SELECT
                tool_name,
                COUNT(*) as count,
                AVG(duration_ms) as avg_duration,
                MAX(duration_ms) as max_duration,
                SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as success_count
            FROM agent_tool_calls
            GROUP BY tool_name
            ORDER BY count DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Failed to fetch AI Agent tool stats: ' . $e->getMessage());
        return [];
    }
}

// Auto-initialize AI Agent when config is loaded
initAIAgent();
