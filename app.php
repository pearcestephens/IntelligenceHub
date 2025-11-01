<?php
/**
 * Intelligence System Bootstrap File
 *
 * This file initializes the hdgwrzntwa application environment
 * and provides necessary configuration for KB scripts.
 */

// Set error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define application constants
define('APP_ROOT', __DIR__);
define('INTELLIGENCE_MODE', true);

// Database configuration for hdgwrzntwa
$db_config = [
    'host' => 'localhost',
    'username' => 'hdgwrzntwa',
    'password' => 'bFUdRjh4Jx',
    'database' => 'hdgwrzntwa',
    'charset' => 'utf8mb4'
];

// Global database connection function
function getIntelligenceDB() {
    global $db_config;
    static $connection = null;

    if ($connection === null) {
        try {
            $dsn = "mysql:host={$db_config['host']};dbname={$db_config['database']};charset={$db_config['charset']}";
            $connection = new PDO($dsn, $db_config['username'], $db_config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Unable to connect to intelligence database");
        }
    }

    return $connection;
}

// Initialize basic configuration
$GLOBALS['intelligence_config'] = [
    'app_root' => APP_ROOT,
    'scripts_dir' => APP_ROOT . '/scripts',
    'logs_dir' => APP_ROOT . '/logs',
    'docs_dir' => APP_ROOT . '/docs',
    'kb_dir' => APP_ROOT . '/kb',
    'db_config' => $db_config
];

// Auto-create essential directories
$essential_dirs = [
    APP_ROOT . '/logs',
    APP_ROOT . '/scripts',
    APP_ROOT . '/docs',
    APP_ROOT . '/kb'
];

foreach ($essential_dirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Log application bootstrap
error_log("[" . date('Y-m-d H:i:s') . "] Intelligence System Bootstrap: " . ($_SERVER['REQUEST_URI'] ?? 'CLI'));

// Initialize global $pdo for compatibility
try {
    $pdo = getIntelligenceDB();
} catch (Exception $e) {
    $pdo = null;
    error_log("Failed to initialize global PDO: " . $e->getMessage());
}

// ============================================================================
// VS CODE SYNC SYSTEM INTEGRATION
// ============================================================================

/**
 * Initialize VS Code Sync System
 * Creates necessary database tables and directories
 */
function initializeVSCodeSync() {
    try {
        $db = getIntelligenceDB();

        // Create vscode_sync_config table
        $db->exec("
            CREATE TABLE IF NOT EXISTS vscode_sync_config (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT DEFAULT 1,
                config JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create vscode_sync_history table
        $db->exec("
            CREATE TABLE IF NOT EXISTS vscode_sync_history (
                id INT PRIMARY KEY AUTO_INCREMENT,
                filename VARCHAR(255) NOT NULL,
                content LONGTEXT,
                metadata JSON,
                version INT DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_filename (filename),
                INDEX idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create cron_job_stats table (for automated backups)
        $db->exec("
            CREATE TABLE IF NOT EXISTS cron_job_stats (
                job_name VARCHAR(100) PRIMARY KEY,
                last_run TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                total_runs INT DEFAULT 0,
                last_status VARCHAR(20),
                last_message TEXT,
                INDEX idx_last_run (last_run)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Create backup directory
        $backup_dir = APP_ROOT . '/private_html/backups/vscode-prompts';
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }

        // Create log directory
        $log_dir = APP_ROOT . '/assets/services/cron/smart-cron/logs';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }

        return true;
    } catch (Exception $e) {
        error_log("VS Code Sync initialization error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get VS Code Sync configuration
 *
 * @param int $userId User ID (default: 1)
 * @return array|null Configuration array or null
 */
function getVSCodeSyncConfig($userId = 1) {
    try {
        $db = getIntelligenceDB();
        $stmt = $db->prepare("SELECT config FROM vscode_sync_config WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? json_decode($result['config'], true) : null;
    } catch (Exception $e) {
        error_log("Error fetching VS Code config: " . $e->getMessage());
        return null;
    }
}

/**
 * Save VS Code Sync configuration
 *
 * @param array $config Configuration array
 * @param int $userId User ID (default: 1)
 * @return bool Success status
 */
function saveVSCodeSyncConfig($config, $userId = 1) {
    try {
        $db = getIntelligenceDB();

        // Check if config exists
        $stmt = $db->prepare("SELECT id FROM vscode_sync_config WHERE user_id = ?");
        $stmt->execute([$userId]);
        $exists = $stmt->fetch();

        if ($exists) {
            $stmt = $db->prepare("UPDATE vscode_sync_config SET config = ? WHERE user_id = ?");
            $stmt->execute([json_encode($config), $userId]);
        } else {
            $stmt = $db->prepare("INSERT INTO vscode_sync_config (user_id, config) VALUES (?, ?)");
            $stmt->execute([$userId, json_encode($config)]);
        }

        return true;
    } catch (Exception $e) {
        error_log("Error saving VS Code config: " . $e->getMessage());
        return false;
    }
}

/**
 * Log VS Code sync operation
 *
 * @param string $filename Filename
 * @param string $content File content
 * @param array $metadata Metadata array
 * @param int $version Version number
 * @return int|false Last insert ID or false on error
 */
function logVSCodeSync($filename, $content, $metadata = [], $version = 1) {
    try {
        $db = getIntelligenceDB();
        $stmt = $db->prepare("
            INSERT INTO vscode_sync_history (filename, content, metadata, version)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $filename,
            $content,
            json_encode($metadata),
            $version
        ]);

        return $db->lastInsertId();
    } catch (Exception $e) {
        error_log("Error logging VS Code sync: " . $e->getMessage());
        return false;
    }
}

/**
 * Get VS Code sync history
 *
 * @param int $limit Number of records to return
 * @return array Array of sync history records
 */
function getVSCodeSyncHistory($limit = 50) {
    try {
        $db = getIntelligenceDB();
        $stmt = $db->prepare("
            SELECT id, filename, metadata, version, created_at
            FROM vscode_sync_history
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching VS Code history: " . $e->getMessage());
        return [];
    }
}

/**
 * Get VS Code sync statistics
 *
 * @return array Statistics array
 */
function getVSCodeSyncStats() {
    try {
        $db = getIntelligenceDB();

        // Get total syncs
        $stmt = $db->query("SELECT COUNT(*) as total FROM vscode_sync_history");
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Get last sync time
        $stmt = $db->query("SELECT MAX(created_at) as last_sync FROM vscode_sync_history");
        $lastSync = $stmt->fetch(PDO::FETCH_ASSOC)['last_sync'];

        // Get cron job status
        $stmt = $db->prepare("SELECT * FROM cron_job_stats WHERE job_name = ?");
        $stmt->execute(['vscode-sync-daily']);
        $cronStatus = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_syncs' => $total,
            'last_sync' => $lastSync,
            'cron_status' => $cronStatus,
            'backup_dir' => APP_ROOT . '/private_html/backups/vscode-prompts',
            'system_active' => true
        ];
    } catch (Exception $e) {
        error_log("Error fetching VS Code stats: " . $e->getMessage());
        return [
            'total_syncs' => 0,
            'last_sync' => null,
            'cron_status' => null,
            'backup_dir' => APP_ROOT . '/private_html/backups/vscode-prompts',
            'system_active' => false
        ];
    }
}

// Initialize VS Code Sync on bootstrap
initializeVSCodeSync();

// Add VS Code sync paths to global config
$GLOBALS['intelligence_config']['vscode_sync'] = [
    'enabled' => true,
    'backup_dir' => APP_ROOT . '/private_html/backups/vscode-prompts',
    'log_dir' => APP_ROOT . '/assets/services/cron/smart-cron/logs',
    'api_endpoint' => '/dashboard/api/vscode-sync.php',
    'dashboard_page' => '/dashboard/pages/ai-control-center.php',
    'cron_schedule' => '0 2 * * *', // Daily at 2:00 AM
    'retention_days' => 30
];

// ============================================================================

?>
