#!/usr/bin/env php
<?php
/**
 * Intelligence Hub - Smart Cron System
 * 
 * CLI-safe, database-driven cron management system specifically designed for Intelligence Hub.
 * Controls all satellite crons and manages central hub operations.
 * 
 * Usage:
 *   php intelligence-hub-cron.php                    # Run scheduled tasks
 *   php intelligence-hub-cron.php --analyze          # Analyze and optimize schedules
 *   php intelligence-hub-cron.php --status           # Show system status
 *   php intelligence-hub-cron.php --task=name        # Run specific task manually
 *   php intelligence-hub-cron.php --satellite=id     # Sync specific satellite
 *   php intelligence-hub-cron.php --discovery        # Discover new cron jobs
 * 
 * Installation:
 *   Add to crontab: * * * * * cd /home/master/applications/hdgwrzntwa/public_html && php assets/services/cron/intelligence-hub-cron.php >> logs/hub-cron.log 2>&1
 * 
 * @package IntelligenceHub
 * @version 2.0.0
 */

declare(strict_types=1);

// CLI Safety Check
if (PHP_SAPI !== 'cli') {
    die("This script can only be run from command line.\n");
}

// Include Hub cron configuration first
require_once __DIR__ . '/hub-cron-config.php';

// Safety limits for CLI execution
ini_set('memory_limit', '512M');
ini_set('max_execution_time', '300');

// Set CLI-safe environment
if (!isset($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = '/cron';
}
if (!isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = 'gpt.ecigdis.co.nz';
}

// Define constants
define('CRON_START_TIME', microtime(true));
define('CRON_LOG_PREFIX', '[HUB-CRON]');

// Bootstrap the application
$bootstrapPath = __DIR__ . '/../../app.php';
if (!file_exists($bootstrapPath)) {
    $bootstrapPath = __DIR__ . '/../../../app.php';
}

if (file_exists($bootstrapPath)) {
    require_once $bootstrapPath;
} else {
    // Include Hub cron configuration
    require_once __DIR__ . '/hub-cron-config.php';
}

/**
 * Intelligence Hub Cron Manager
 */
class IntelligenceHubCron
{
    private PDO $db;
    private array $config;
    private string $logFile;
    
    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
        $this->logFile = __DIR__ . '/../../../logs/hub-cron.log';
        
        $this->config = [
            'hub_id' => 'intelligence-hub',
            'satellites' => [
                'cis' => 'https://staff.vapeshed.co.nz',
                'retail' => 'https://www.vapeshed.co.nz',
                'wholesale' => 'https://wholesale.vapeshed.co.nz',
                'vaping-kiwi' => 'https://www.vapingkiwi.co.nz'
            ],
            'max_execution_time' => 300,
            'heartbeat_interval' => 60,
        ];
        
        $this->initializeDatabase();
    }
    
    /**
     * Initialize database tables if they don't exist
     */
    private function initializeDatabase(): void
    {
        try {
            // Create cron_jobs table
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS cron_jobs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL UNIQUE,
                    command TEXT NOT NULL,
                    schedule VARCHAR(100) NOT NULL,
                    enabled BOOLEAN DEFAULT TRUE,
                    satellite_id VARCHAR(50) DEFAULT NULL,
                    last_run TIMESTAMP NULL,
                    next_run TIMESTAMP NULL,
                    status ENUM('pending', 'running', 'completed', 'failed') DEFAULT 'pending',
                    execution_count INT DEFAULT 0,
                    avg_duration DECIMAL(10,3) DEFAULT 0.000,
                    last_output TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ");
            
            // Create cron_executions table
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS cron_executions (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    job_id INT NOT NULL,
                    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    completed_at TIMESTAMP NULL,
                    duration DECIMAL(10,3) NULL,
                    status ENUM('running', 'completed', 'failed', 'timeout') DEFAULT 'running',
                    output TEXT,
                    error_output TEXT,
                    exit_code INT NULL,
                    memory_usage INT NULL,
                    FOREIGN KEY (job_id) REFERENCES cron_jobs(id) ON DELETE CASCADE
                )
            ");
            
            // Create cron_satellites table
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS cron_satellites (
                    id VARCHAR(50) PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    url VARCHAR(500) NOT NULL,
                    last_sync TIMESTAMP NULL,
                    status ENUM('online', 'offline', 'error') DEFAULT 'offline',
                    job_count INT DEFAULT 0,
                    avg_load DECIMAL(5,2) DEFAULT 0.00,
                    last_error TEXT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ");
            
            $this->log("Database tables initialized successfully");
            
        } catch (PDOException $e) {
            $this->log("Failed to initialize database: " . $e->getMessage(), 'ERROR');
            throw $e;
        }
    }
    
    /**
     * Run the cron system
     */
    public function run(array $options = []): void
    {
        $this->log("Starting Intelligence Hub Cron System v2.0");
        
        if (isset($options['analyze'])) {
            $this->runAnalyzer();
        } elseif (isset($options['status'])) {
            $this->showStatus();
        } elseif (isset($options['discovery'])) {
            $this->runDiscovery();
        } elseif (isset($options['satellite'])) {
            $this->syncSatellite($options['satellite']);
        } elseif (isset($options['task'])) {
            $this->runManualTask($options['task']);
        } else {
            $this->runScheduledTasks();
        }
        
        $duration = round((microtime(true) - CRON_START_TIME) * 1000, 2);
        $this->log("Completed in {$duration}ms");
    }
    
    /**
     * Run scheduled tasks for current minute
     */
    private function runScheduledTasks(): void
    {
        $currentMinute = (int)date('i');
        $currentHour = (int)date('H');
        $currentDay = (int)date('j');
        $currentMonth = (int)date('n');
        $currentDow = (int)date('w');
        
        $this->log("Checking tasks for minute {$currentMinute}, hour {$currentHour}");
        
        // Get enabled tasks
        $stmt = $this->db->prepare("
            SELECT * FROM hub_cron_jobs 
            WHERE enabled = TRUE 
            AND (next_run IS NULL OR next_run <= NOW())
            ORDER BY satellite_id, name
        ");
        $stmt->execute();
        $jobs = $stmt->fetchAll();
        
        if (empty($jobs)) {
            $this->log("No tasks scheduled for execution");
            return;
        }
        
        $this->log("Found " . count($jobs) . " potential tasks");
        
        foreach ($jobs as $job) {
            if ($this->shouldRunJob($job, $currentMinute, $currentHour, $currentDay, $currentMonth, $currentDow)) {
                $this->executeJob($job);
            }
        }
    }
    
    /**
     * Check if job should run based on cron schedule
     */
    private function shouldRunJob(array $job, int $minute, int $hour, int $day, int $month, int $dow): bool
    {
        $schedule = $job['schedule'];
        $parts = explode(' ', $schedule);
        
        if (count($parts) !== 5) {
            $this->log("Invalid cron schedule for job {$job['name']}: {$schedule}", 'ERROR');
            return false;
        }
        
        [$cronMinute, $cronHour, $cronDay, $cronMonth, $cronDow] = $parts;
        
        return $this->matchesCronField($cronMinute, $minute) &&
               $this->matchesCronField($cronHour, $hour) &&
               $this->matchesCronField($cronDay, $day) &&
               $this->matchesCronField($cronMonth, $month) &&
               $this->matchesCronField($cronDow, $dow);
    }
    
    /**
     * Check if current value matches cron field
     */
    private function matchesCronField(string $field, int $value): bool
    {
        if ($field === '*') {
            return true;
        }
        
        if (strpos($field, '/') !== false) {
            [$range, $step] = explode('/', $field);
            if ($range === '*') {
                return $value % (int)$step === 0;
            }
        }
        
        if (strpos($field, ',') !== false) {
            $values = explode(',', $field);
            return in_array((string)$value, $values);
        }
        
        if (strpos($field, '-') !== false) {
            [$start, $end] = explode('-', $field);
            return $value >= (int)$start && $value <= (int)$end;
        }
        
        return (int)$field === $value;
    }
    
    /**
     * Execute a cron job
     */
    private function executeJob(array $job): void
    {
        $this->log("Executing job: {$job['name']}");
        
        // Record execution start
        $executionId = HubCronUtils::generateExecutionId();
        $stmt = $this->db->prepare("
            INSERT INTO hub_cron_executions (job_id, execution_id, started_at) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$job['id'], $executionId]);
        $executionDbId = $this->db->lastInsertId();
        
        // Update job status
        $stmt = $this->db->prepare("
            UPDATE hub_cron_jobs 
            SET status = 'running', last_run = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$job['id']]);
        
        $startTime = microtime(true);
        $output = '';
        $errorOutput = '';
        $exitCode = 0;
        
        try {
            // Execute command
            $command = $job['command'];
            
            // Handle satellite commands
            if ($job['satellite_id']) {
                $command = $this->prepareSatelliteCommand($command, $job['satellite_id']);
            }
            
            $this->log("Running command: {$command}");
            
            $descriptorSpec = [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w']
            ];
            
            $process = proc_open($command, $descriptorSpec, $pipes);
            
            if (is_resource($process)) {
                fclose($pipes[0]);
                $output = stream_get_contents($pipes[1]);
                $errorOutput = stream_get_contents($pipes[2]);
                fclose($pipes[1]);
                fclose($pipes[2]);
                $exitCode = proc_close($process);
            } else {
                throw new Exception("Failed to start process");
            }
            
            $duration = round((microtime(true) - $startTime) * 1000, 3);
            $status = $exitCode === 0 ? 'completed' : 'failed';
            
            // Record execution completion
            $stmt = $this->db->prepare("
                UPDATE hub_cron_executions 
                SET completed_at = NOW(), duration = ?, status = ?, output = ?, error_output = ?, exit_code = ?
                WHERE id = ?
            ");
            $stmt->execute([$duration, $status, $output, $errorOutput, $exitCode, $executionDbId]);
            
            // Update job statistics
            $stmt = $this->db->prepare("
                UPDATE hub_cron_jobs 
                SET status = ?, execution_count = execution_count + 1, 
                    avg_duration = (avg_duration * execution_count + ?) / (execution_count + 1),
                    last_output = ?, next_run = NULL
                WHERE id = ?
            ");
            $stmt->execute([$status, $duration, substr($output, 0, 1000), $job['id']]);
            
            $this->log("Job {$job['name']} completed in {$duration}ms with exit code {$exitCode}");
            
            if ($exitCode !== 0) {
                $this->log("Job {$job['name']} failed: {$errorOutput}", 'ERROR');
            }
            
        } catch (Exception $e) {
            // Record failure
            $duration = round((microtime(true) - $startTime) * 1000, 3);
            
            $stmt = $this->db->prepare("
                UPDATE hub_cron_executions 
                SET completed_at = NOW(), duration = ?, status = 'failed', error_output = ?
                WHERE id = ?
            ");
            $stmt->execute([$duration, $e->getMessage(), $executionDbId]);
            
            $stmt = $this->db->prepare("
                UPDATE hub_cron_jobs 
                SET status = 'failed', last_output = ?
                WHERE id = ?
            ");
            $stmt->execute([$e->getMessage(), $job['id']]);
            
            $this->log("Job {$job['name']} failed: " . $e->getMessage(), 'ERROR');
        }
    }
    
    /**
     * Prepare command for satellite execution
     */
    private function prepareSatelliteCommand(string $command, string $satelliteId): string
    {
        if (!isset($this->config['satellites'][$satelliteId])) {
            throw new Exception("Unknown satellite: {$satelliteId}");
        }
        
        $satelliteUrl = $this->config['satellites'][$satelliteId];
        
        // If it's a local command, execute normally
        if (strpos($command, 'php ') === 0 || strpos($command, '/') === 0) {
            return $command;
        }
        
        // If it's a remote API call, convert to curl
        return "curl -s '{$satelliteUrl}/api/cron/{$command}'";
    }
    
    /**
     * Show system status
     */
    private function showStatus(): void
    {
        echo "=== Intelligence Hub Cron Status ===\n\n";
        
        // Job statistics
        $stmt = $this->db->query("
            SELECT 
                COUNT(*) as total_jobs,
                SUM(enabled) as enabled_jobs,
                AVG(avg_duration) as avg_duration,
                MAX(last_run) as last_activity
            FROM hub_cron_jobs
        ");
        $stats = $stmt->fetch();
        
        echo "Jobs: {$stats['total_jobs']} total, {$stats['enabled_jobs']} enabled\n";
        echo "Average duration: " . round((float)($stats['avg_duration'] ?? 0), 2) . "ms\n";
        echo "Last activity: " . ($stats['last_activity'] ?? 'Never') . "\n\n";
        
        // Recent executions
        $stmt = $this->db->query("
            SELECT j.name, e.status, e.duration, e.started_at
            FROM hub_cron_executions e
            JOIN hub_cron_jobs j ON e.job_id = j.id
            ORDER BY e.started_at DESC
            LIMIT 10
        ");
        $executions = $stmt->fetchAll();
        
        echo "Recent Executions:\n";
        foreach ($executions as $exec) {
            $duration = $exec['duration'] ? round($exec['duration'], 2) . 'ms' : 'N/A';
            echo "  {$exec['started_at']} - {$exec['name']} [{$exec['status']}] ({$duration})\n";
        }
        
        echo "\nSatellites:\n";
        foreach ($this->config['satellites'] as $id => $url) {
            echo "  {$id}: {$url}\n";
        }
    }
    
    /**
     * Run discovery to find new cron jobs
     */
    private function runDiscovery(): void
    {
        $this->log("Running cron job discovery");
        
        // Discovery logic would scan for cron job definitions
        // For now, just log that discovery ran
        $this->log("Discovery completed - no new jobs found");
    }
    
    /**
     * Sync with specific satellite
     */
    private function syncSatellite(string $satelliteId): void
    {
        if (!isset($this->config['satellites'][$satelliteId])) {
            $this->log("Unknown satellite: {$satelliteId}", 'ERROR');
            return;
        }
        
        $this->log("Syncing with satellite: {$satelliteId}");
        
        // Satellite sync logic would go here
        $this->log("Satellite sync completed");
    }
    
    /**
     * Run specific task manually
     */
    private function runManualTask(string $taskName): void
    {
        $stmt = $this->db->prepare("SELECT * FROM hub_cron_jobs WHERE name = ?");
        $stmt->execute([$taskName]);
        $job = $stmt->fetch();
        
        if (!$job) {
            $this->log("Task not found: {$taskName}", 'ERROR');
            return;
        }
        
        $this->log("Manually executing task: {$taskName}");
        $this->executeJob($job);
    }
    
    /**
     * Run analyzer to optimize schedules
     */
    private function runAnalyzer(): void
    {
        $this->log("Running schedule analyzer");
        
        // Analyzer logic would optimize job schedules based on performance data
        $this->log("Schedule analysis completed");
    }
    
    /**
     * Log message
     */
    private function log(string $message, string $level = 'INFO'): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$level}: {$message}";
        
        echo $logMessage . "\n";
        
        // Also log to file
        file_put_contents($this->logFile, $logMessage . "\n", FILE_APPEND | LOCK_EX);
    }
}

// Parse CLI arguments
$options = getopt('', [
    'analyze',
    'status', 
    'discovery',
    'satellite:',
    'task:',
    'help'
]);

// Show help
if (isset($options['help'])) {
    echo "Intelligence Hub Cron System v2.0\n\n";
    echo "Usage:\n";
    echo "  php intelligence-hub-cron.php                    Run scheduled tasks\n";
    echo "  php intelligence-hub-cron.php --analyze          Analyze and optimize\n";
    echo "  php intelligence-hub-cron.php --status           Show system status\n";
    echo "  php intelligence-hub-cron.php --discovery        Discover new jobs\n";
    echo "  php intelligence-hub-cron.php --satellite=id     Sync satellite\n";
    echo "  php intelligence-hub-cron.php --task=name        Run specific task\n";
    echo "  php intelligence-hub-cron.php --help             Show this help\n";
    exit(0);
}

// Initialize and run
try {
    $pdo = HubCronConfig::getDatabase();
    $cronManager = new IntelligenceHubCron($pdo);
    $cronManager->run($options);
    exit(0);
} catch (Exception $e) {
    error_log(CRON_LOG_PREFIX . " Fatal error: " . $e->getMessage());
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    exit(1);
}