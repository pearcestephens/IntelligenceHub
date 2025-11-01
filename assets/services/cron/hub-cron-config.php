<?php
/**
 * Intelligence Hub - Cron Configuration & Management
 * 
 * Provides configuration, database setup, and utility functions
 * for the Intelligence Hub cron system.
 * 
 * @package IntelligenceHub
 * @version 2.0.0
 */

declare(strict_types=1);

/**
 * Hub Cron Configuration
 */
class HubCronConfig
{
    public static function getConfig(): array
    {
        return [
            'hub_id' => 'intelligence-hub',
            'version' => '2.0.0',
            
            // Satellite configurations
            'satellites' => [
                'cis' => [
                    'id' => 'cis',
                    'name' => 'CIS Staff Portal',
                    'url' => 'https://staff.vapeshed.co.nz',
                    'cron_endpoint' => '/api/hub-cron.php',
                    'enabled' => true,
                    'priority' => 1
                ],
                'retail' => [
                    'id' => 'retail',
                    'name' => 'Retail Website',
                    'url' => 'https://www.vapeshed.co.nz',
                    'cron_endpoint' => '/api/cron.php',
                    'enabled' => true,
                    'priority' => 2
                ],
                'wholesale' => [
                    'id' => 'wholesale',
                    'name' => 'Wholesale Portal',
                    'url' => 'https://wholesale.vapeshed.co.nz',
                    'cron_endpoint' => '/api/cron.php',
                    'enabled' => false,
                    'priority' => 3
                ],
                'vaping-kiwi' => [
                    'id' => 'vaping-kiwi',
                    'name' => 'Vaping Kiwi',
                    'url' => 'https://www.vapingkiwi.co.nz',
                    'cron_endpoint' => '/api/cron.php',
                    'enabled' => true,
                    'priority' => 4
                ]
            ],
            
            // System limits
            'limits' => [
                'max_execution_time' => 300, // 5 minutes
                'memory_limit' => '512M',
                'max_concurrent_jobs' => 5,
                'job_timeout' => 180, // 3 minutes
                'heartbeat_interval' => 60, // 1 minute
            ],
            
            // Logging configuration
            'logging' => [
                'level' => 'INFO', // DEBUG, INFO, WARN, ERROR
                'file' => '/logs/hub-cron.log',
                'max_size' => '10M',
                'rotate_count' => 5,
                'include_microseconds' => true,
            ],
            
            // Database configuration (will be loaded from config/database.php)
            'database' => [
                'prefix' => 'cron_',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ],
            
            // Notification configuration
            'notifications' => [
                'enabled' => true,
                'channels' => ['log', 'webhook'],
                'webhook_url' => null, // Set in environment
                'failure_threshold' => 3, // Send alert after 3 failures
            ],
            
            // Hub-specific job definitions
            'hub_jobs' => [
                'mcp_health_check' => [
                    'name' => 'MCP Tools Health Check',
                    'command' => 'php mcp/health-check.php',
                    'schedule' => '*/5 * * * *', // Every 5 minutes
                    'enabled' => true,
                    'timeout' => 30,
                    'description' => 'Check health of all 13 MCP tools'
                ],
                'intelligence_refresh' => [
                    'name' => 'Intelligence Content Refresh',
                    'command' => 'php scripts/refresh-intelligence.php',
                    'schedule' => '0 */4 * * *', // Every 4 hours
                    'enabled' => true,
                    'timeout' => 600, // 10 minutes
                    'description' => 'Refresh intelligence content and indexes'
                ],
                'satellite_sync' => [
                    'name' => 'Satellite Synchronization',
                    'command' => 'php assets/services/cron/satellite-sync.php',
                    'schedule' => '*/15 * * * *', // Every 15 minutes
                    'enabled' => true,
                    'timeout' => 120,
                    'description' => 'Sync with all satellite systems'
                ],
                'cleanup_logs' => [
                    'name' => 'Log Cleanup',
                    'command' => 'php scripts/cleanup-logs.php',
                    'schedule' => '0 2 * * *', // Daily at 2 AM
                    'enabled' => true,
                    'timeout' => 300,
                    'description' => 'Clean up old log files and rotate'
                ],
                'backup_database' => [
                    'name' => 'Database Backup',
                    'command' => 'php scripts/backup-database.php',
                    'schedule' => '0 3 * * *', // Daily at 3 AM
                    'enabled' => true,
                    'timeout' => 900, // 15 minutes
                    'description' => 'Create daily database backup'
                ],
                'optimize_database' => [
                    'name' => 'Database Optimization',
                    'command' => 'php scripts/optimize-database.php',
                    'schedule' => '0 4 * * 0', // Weekly on Sunday at 4 AM
                    'enabled' => true,
                    'timeout' => 1800, // 30 minutes
                    'description' => 'Optimize database tables and indexes'
                ]
            ]
        ];
    }
    
    /**
     * Get database connection for Hub cron system
     */
    public static function getDatabase(): PDO
    {
        // Load database config
        $configPath = __DIR__ . '/../../../config/database.php';
        if (!file_exists($configPath)) {
            throw new Exception("Database configuration not found at: {$configPath}");
        }
        
        $dbConfig = require $configPath;
        
        try {
            $pdo = new PDO(
                "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset=utf8mb4",
                $dbConfig['username'],
                $dbConfig['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]
            );
            
            return $pdo;
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Initialize database tables for Hub cron system
     */
    public static function initializeDatabase(PDO $pdo): void
    {
        $queries = [
            // Hub cron jobs table
            "CREATE TABLE IF NOT EXISTS hub_cron_jobs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL UNIQUE,
                command TEXT NOT NULL,
                schedule VARCHAR(100) NOT NULL,
                enabled BOOLEAN DEFAULT TRUE,
                satellite_id VARCHAR(50) DEFAULT NULL,
                timeout INT DEFAULT 300,
                last_run TIMESTAMP NULL,
                next_run TIMESTAMP NULL,
                status ENUM('pending', 'running', 'completed', 'failed', 'timeout') DEFAULT 'pending',
                execution_count INT DEFAULT 0,
                failure_count INT DEFAULT 0,
                avg_duration DECIMAL(10,3) DEFAULT 0.000,
                last_output TEXT,
                last_error TEXT,
                priority INT DEFAULT 5,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_schedule (enabled, next_run),
                INDEX idx_satellite (satellite_id),
                INDEX idx_status (status, last_run)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Hub cron executions table
            "CREATE TABLE IF NOT EXISTS hub_cron_executions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                job_id INT NOT NULL,
                execution_id VARCHAR(36) NOT NULL,
                started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                completed_at TIMESTAMP NULL,
                duration DECIMAL(10,3) NULL,
                status ENUM('running', 'completed', 'failed', 'timeout', 'killed') DEFAULT 'running',
                output LONGTEXT,
                error_output TEXT,
                exit_code INT NULL,
                memory_usage INT NULL,
                cpu_usage DECIMAL(5,2) NULL,
                server_load DECIMAL(5,2) NULL,
                FOREIGN KEY (job_id) REFERENCES hub_cron_jobs(id) ON DELETE CASCADE,
                INDEX idx_job_execution (job_id, started_at),
                INDEX idx_status (status, started_at),
                INDEX idx_duration (duration)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Hub satellites table
            "CREATE TABLE IF NOT EXISTS hub_cron_satellites (
                id VARCHAR(50) PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                url VARCHAR(500) NOT NULL,
                cron_endpoint VARCHAR(255) DEFAULT '/api/cron.php',
                enabled BOOLEAN DEFAULT TRUE,
                priority INT DEFAULT 5,
                last_sync TIMESTAMP NULL,
                last_heartbeat TIMESTAMP NULL,
                status ENUM('online', 'offline', 'error', 'maintenance') DEFAULT 'offline',
                job_count INT DEFAULT 0,
                avg_response_time DECIMAL(10,3) DEFAULT 0.000,
                success_rate DECIMAL(5,2) DEFAULT 100.00,
                last_error TEXT NULL,
                config JSON,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_status (status, last_heartbeat),
                INDEX idx_priority (priority, enabled)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Hub cron metrics table
            "CREATE TABLE IF NOT EXISTS hub_cron_metrics (
                id INT AUTO_INCREMENT PRIMARY KEY,
                metric_type ENUM('execution', 'performance', 'error', 'heartbeat') NOT NULL,
                metric_name VARCHAR(100) NOT NULL,
                metric_value DECIMAL(15,6) NOT NULL,
                job_id INT NULL,
                satellite_id VARCHAR(50) NULL,
                recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                metadata JSON,
                FOREIGN KEY (job_id) REFERENCES hub_cron_jobs(id) ON DELETE SET NULL,
                FOREIGN KEY (satellite_id) REFERENCES hub_cron_satellites(id) ON DELETE SET NULL,
                INDEX idx_type_time (metric_type, recorded_at),
                INDEX idx_job_metrics (job_id, recorded_at),
                INDEX idx_satellite_metrics (satellite_id, recorded_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Hub cron alerts table
            "CREATE TABLE IF NOT EXISTS hub_cron_alerts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                alert_type ENUM('job_failure', 'satellite_down', 'performance', 'system') NOT NULL,
                severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
                title VARCHAR(255) NOT NULL,
                message TEXT NOT NULL,
                job_id INT NULL,
                satellite_id VARCHAR(50) NULL,
                resolved BOOLEAN DEFAULT FALSE,
                resolved_at TIMESTAMP NULL,
                resolved_by VARCHAR(100) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (job_id) REFERENCES hub_cron_jobs(id) ON DELETE SET NULL,
                FOREIGN KEY (satellite_id) REFERENCES hub_cron_satellites(id) ON DELETE SET NULL,
                INDEX idx_type_severity (alert_type, severity, resolved),
                INDEX idx_created (created_at, resolved)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Hub cron commands table (for satellite command queuing)
            "CREATE TABLE IF NOT EXISTS hub_cron_commands (
                id INT AUTO_INCREMENT PRIMARY KEY,
                satellite_id VARCHAR(50) NOT NULL,
                command_type VARCHAR(100) NOT NULL,
                command_data LONGTEXT,
                status ENUM('pending', 'sent', 'completed', 'failed') DEFAULT 'pending',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                sent_at TIMESTAMP NULL,
                completed_at TIMESTAMP NULL,
                response LONGTEXT,
                error TEXT,
                INDEX idx_satellite_status (satellite_id, status),
                INDEX idx_created_at (created_at),
                FOREIGN KEY (satellite_id) REFERENCES hub_cron_satellites(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        ];
        
        foreach ($queries as $query) {
            $pdo->exec($query);
        }
        
        // Insert default satellites
        self::insertDefaultSatellites($pdo);
        
        // Insert default jobs
        self::insertDefaultJobs($pdo);
    }
    
    /**
     * Insert default satellite configurations
     */
    private static function insertDefaultSatellites(PDO $pdo): void
    {
        $config = self::getConfig();
        
        $stmt = $pdo->prepare("
            INSERT INTO hub_cron_satellites (id, name, url, cron_endpoint, enabled, priority, config)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                name = VALUES(name),
                url = VALUES(url),
                cron_endpoint = VALUES(cron_endpoint),
                priority = VALUES(priority),
                config = VALUES(config),
                updated_at = CURRENT_TIMESTAMP
        ");
        
        foreach ($config['satellites'] as $satellite) {
            $stmt->execute([
                $satellite['id'],
                $satellite['name'],
                $satellite['url'],
                $satellite['cron_endpoint'],
                $satellite['enabled'] ? 1 : 0,
                $satellite['priority'],
                json_encode($satellite)
            ]);
        }
    }
    
    /**
     * Insert default hub jobs
     */
    private static function insertDefaultJobs(PDO $pdo): void
    {
        $config = self::getConfig();
        
        $stmt = $pdo->prepare("
            INSERT INTO hub_cron_jobs (name, command, schedule, enabled, timeout, priority, description)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                command = VALUES(command),
                schedule = VALUES(schedule),
                timeout = VALUES(timeout),
                priority = VALUES(priority),
                description = VALUES(description),
                updated_at = CURRENT_TIMESTAMP
        ");
        
        foreach ($config['hub_jobs'] as $job) {
            $stmt->execute([
                $job['name'],
                $job['command'],
                $job['schedule'],
                $job['enabled'] ? 1 : 0,
                $job['timeout'],
                5, // default priority
                $job['description']
            ]);
        }
    }
}

/**
 * Hub Cron Logger
 */
class HubCronLogger
{
    private string $logFile;
    private string $level;
    private bool $includeMicroseconds;
    
    public function __construct(string $logFile = null, string $level = 'INFO')
    {
        $this->logFile = $logFile ?? __DIR__ . '/../../../logs/hub-cron.log';
        $this->level = $level;
        $this->includeMicroseconds = true;
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    public function debug(string $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context);
    }
    
    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }
    
    public function warn(string $message, array $context = []): void
    {
        $this->log('WARN', $message, $context);
    }
    
    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }
    
    public function critical(string $message, array $context = []): void
    {
        $this->log('CRITICAL', $message, $context);
    }
    
    private function log(string $level, string $message, array $context = []): void
    {
        $timestamp = $this->includeMicroseconds 
            ? date('Y-m-d H:i:s.') . sprintf('%03d', microtime(true) * 1000 % 1000)
            : date('Y-m-d H:i:s');
        
        $contextStr = empty($context) ? '' : ' ' . json_encode($context);
        $logMessage = "[{$timestamp}] [{$level}] [HUB-CRON] {$message}{$contextStr}";
        
        // Output to console if running in CLI
        if (PHP_SAPI === 'cli') {
            echo $logMessage . "\n";
        }
        
        // Write to log file
        file_put_contents($this->logFile, $logMessage . "\n", FILE_APPEND | LOCK_EX);
    }
}

/**
 * Hub Cron Utilities
 */
class HubCronUtils
{
    /**
     * Generate unique execution ID
     */
    public static function generateExecutionId(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    /**
     * Get system load
     */
    public static function getSystemLoad(): float
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            return round($load[0], 2);
        }
        return 0.0;
    }
    
    /**
     * Get memory usage in MB
     */
    public static function getMemoryUsage(): int
    {
        return round(memory_get_usage(true) / 1024 / 1024);
    }
    
    /**
     * Format duration in human readable format
     */
    public static function formatDuration(float $seconds): string
    {
        if ($seconds < 1) {
            return round($seconds * 1000) . 'ms';
        } elseif ($seconds < 60) {
            return round($seconds, 2) . 's';
        } elseif ($seconds < 3600) {
            return round($seconds / 60, 1) . 'm';
        } else {
            return round($seconds / 3600, 1) . 'h';
        }
    }
    
    /**
     * Parse cron expression to determine next run time
     */
    public static function getNextRunTime(string $cronExpression): ?DateTime
    {
        // This is a simplified implementation
        // In production, use a proper cron expression parser like mtdowling/cron-expression
        
        $parts = explode(' ', $cronExpression);
        if (count($parts) !== 5) {
            return null;
        }
        
        $now = new DateTime();
        $nextRun = clone $now;
        $nextRun->add(new DateInterval('PT1M')); // Add 1 minute as default
        
        return $nextRun;
    }
    
    /**
     * Validate cron expression
     */
    public static function isValidCronExpression(string $expression): bool
    {
        $parts = explode(' ', trim($expression));
        
        if (count($parts) !== 5) {
            return false;
        }
        
        $ranges = [
            [0, 59], // minute
            [0, 23], // hour
            [1, 31], // day
            [1, 12], // month
            [0, 6]   // day of week
        ];
        
        foreach ($parts as $index => $part) {
            if (!self::isValidCronField($part, $ranges[$index])) {
                return false;
            }
        }
        
        return true;
    }
    
    private static function isValidCronField(string $field, array $range): bool
    {
        if ($field === '*') {
            return true;
        }
        
        // Handle step values (*/5)
        if (strpos($field, '/') !== false) {
            [$range_part, $step] = explode('/', $field, 2);
            if (!is_numeric($step) || $step <= 0) {
                return false;
            }
            return self::isValidCronField($range_part, $range);
        }
        
        // Handle ranges (1-5)
        if (strpos($field, '-') !== false) {
            [$start, $end] = explode('-', $field, 2);
            return is_numeric($start) && is_numeric($end) && 
                   $start >= $range[0] && $end <= $range[1] && $start <= $end;
        }
        
        // Handle lists (1,3,5)
        if (strpos($field, ',') !== false) {
            $values = explode(',', $field);
            foreach ($values as $value) {
                if (!self::isValidCronField(trim($value), $range)) {
                    return false;
                }
            }
            return true;
        }
        
        // Handle single values
        return is_numeric($field) && $field >= $range[0] && $field <= $range[1];
    }
}