<?php
/**
 * AI Control Dashboard API
 * 
 * Provides REST API endpoints for complete control over AI automation system
 * 
 * Features:
 * - Real-time settings management
 * - Budget control and monitoring
 * - Instance management
 * - Usage tracking
 * - Emergency controls
 * 
 * @package UniversalCopilotAutomation
 * @version 3.2.0
 */

declare(strict_types=1);

// Headers for API responses
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS requests for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

class AIControlAPI
{
    private array $config;
    private \PDO $db;
    private string $configFile;
    private string $logDir;
    
    public function __construct()
    {
        // Use dynamic path resolution instead of hardcoded paths
        $basePath = dirname(__DIR__);
        $this->configFile = $basePath . '/config/automation.json';
        $this->logDir = $basePath . '/logs';
        $this->config = $this->loadConfig();
        $this->initializeDatabase();
    }
    
    /**
     * Main API router
     */
    public function handleRequest(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = $_GET['endpoint'] ?? '';
        
        try {
            switch ($path) {
                // Settings endpoints
                case 'get-settings':
                    $this->getSettings();
                    break;
                    
                case 'save-settings':
                    $this->saveSettings();
                    break;
                    
                case 'reset-settings':
                    $this->resetSettings();
                    break;
                    
                // Control endpoints
                case 'emergency-stop':
                    $this->emergencyStop();
                    break;
                    
                case 'run-analysis':
                    $this->runAnalysis();
                    break;
                    
                case 'sync-settings':
                    $this->syncSettings();
                    break;
                    
                // Budget endpoints
                case 'reset-budgets':
                    $this->resetBudgets();
                    break;
                    
                case 'get-budget-status':
                    $this->getBudgetStatus();
                    break;
                    
                // Monitoring endpoints
                case 'get-metrics':
                    $this->getMetrics();
                    break;
                    
                case 'get-logs':
                    $this->getLogs();
                    break;
                    
                case 'clear-logs':
                    $this->clearLogs();
                    break;
                    
                case 'download-logs':
                    $this->downloadLogs();
                    break;
                    
                // Instance management
                case 'get-instances':
                    $this->getInstances();
                    break;
                    
                case 'restart-instance':
                    $this->restartInstance();
                    break;
                    
                case 'scale-instances':
                    $this->scaleInstances();
                    break;
                    
                // Usage tracking
                case 'get-usage-stats':
                    $this->getUsageStats();
                    break;
                    
                case 'export-usage':
                    $this->exportUsage();
                    break;
                    
                default:
                    $this->sendError('Unknown endpoint: ' . $path, 404);
            }
            
        } catch (Exception $e) {
            $this->sendError('API Error: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get current settings
     */
    private function getSettings(): void
    {
        $this->sendResponse([
            'success' => true,
            'settings' => $this->config,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Save settings from dashboard
     */
    private function saveSettings(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $this->sendError('Invalid JSON input', 400);
            return;
        }
        
        // Validate settings
        $validationResult = $this->validateSettings($input);
        if (!$validationResult['valid']) {
            $this->sendError('Validation failed: ' . $validationResult['error'], 400);
            return;
        }
        
        // Merge with current config
        $this->config = $this->mergeSettings($this->config, $input);
        
        // Save to file
        if ($this->saveConfig()) {
            $this->logAction('Settings updated via dashboard');
            $this->sendResponse([
                'success' => true,
                'message' => 'Settings saved successfully',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } else {
            $this->sendError('Failed to save settings', 500);
        }
    }
    
    /**
     * Reset settings to defaults
     */
    private function resetSettings(): void
    {
        $defaults = $this->getDefaultSettings();
        $this->config = $defaults;
        
        if ($this->saveConfig()) {
            $this->logAction('Settings reset to defaults');
            $this->sendResponse([
                'success' => true,
                'message' => 'Settings reset to defaults',
                'settings' => $this->config
            ]);
        } else {
            $this->sendError('Failed to reset settings', 500);
        }
    }
    
    /**
     * Emergency stop all AI processing
     */
    private function emergencyStop(): void
    {
        // Create emergency stop flag
        $stopFile = $this->logDir . '/emergency_stop.flag';
        file_put_contents($stopFile, json_encode([
            'timestamp' => date('Y-m-d H:i:s'),
            'reason' => 'Dashboard emergency stop',
            'user' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]));
        
        // Kill any running AI processes
        $this->killAIProcesses();
        
        // Log the emergency stop
        $this->logAction('EMERGENCY STOP activated', 'CRITICAL');
        
        $this->sendResponse([
            'success' => true,
            'message' => 'Emergency stop activated - all AI processing stopped',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Run AI analysis manually
     */
    private function runAnalysis(): void
    {
        // Check if emergency stop is active
        if (file_exists($this->logDir . '/emergency_stop.flag')) {
            $this->sendError('Cannot run analysis: Emergency stop is active', 423);
            return;
        }
        
        // Check budget limits
        if (!$this->checkBudgetLimits()) {
            $this->sendError('Cannot run analysis: Budget limits exceeded', 423);
            return;
        }
        
        // Start AI batch processor
        $command = "cd /home/master/applications/hdgwrzntwa/public_html && php ai-batch-processor.php > /dev/null 2>&1 &";
        exec($command);
        
        $this->logAction('Manual AI analysis started');
        
        $this->sendResponse([
            'success' => true,
            'message' => 'AI analysis started',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Sync settings across all systems
     */
    private function syncSettings(): void
    {
        // Trigger universal automation sync
        $command = "cd /home/master/applications/hdgwrzntwa/public_html && php universal-copilot-automation.php --sync-only > /dev/null 2>&1 &";
        exec($command);
        
        $this->logAction('Settings sync triggered');
        
        $this->sendResponse([
            'success' => true,
            'message' => 'Settings sync initiated',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Reset budget counters
     */
    private function resetBudgets(): void
    {
        try {
            // Clear usage tracking for current period
            $this->db->exec("DELETE FROM ai_usage_tracking WHERE DATE(timestamp) = DATE('now')");
            
            // Reset budget status
            $resetData = [
                'daily_spent' => 0.00,
                'weekly_spent' => 0.00,
                'monthly_spent' => 0.00,
                'reset_timestamp' => date('Y-m-d H:i:s'),
                'reset_by' => $_SERVER['REMOTE_ADDR'] ?? 'dashboard'
            ];
            
            file_put_contents($this->logDir . '/budget_reset.json', json_encode($resetData));
            
            $this->logAction('Budget counters reset');
            
            $this->sendResponse([
                'success' => true,
                'message' => 'Budget counters reset successfully',
                'data' => $resetData
            ]);
            
        } catch (Exception $e) {
            $this->sendError('Failed to reset budgets: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get budget status
     */
    private function getBudgetStatus(): void
    {
        $budgetData = [
            'daily_spent' => $this->getDailySpending(),
            'weekly_spent' => $this->getWeeklySpending(),
            'monthly_spent' => $this->getMonthlySpending(),
            'daily_limit' => $this->config['ai_analysis']['spending_controls']['budget_management']['daily_budget_usd'],
            'weekly_limit' => $this->config['ai_analysis']['spending_controls']['budget_management']['weekly_budget_usd'],
            'monthly_limit' => $this->config['ai_analysis']['spending_controls']['budget_management']['monthly_budget_usd'],
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Calculate percentages
        $budgetData['daily_percentage'] = ($budgetData['daily_spent'] / $budgetData['daily_limit']) * 100;
        $budgetData['weekly_percentage'] = ($budgetData['weekly_spent'] / $budgetData['weekly_limit']) * 100;
        $budgetData['monthly_percentage'] = ($budgetData['monthly_spent'] / $budgetData['monthly_limit']) * 100;
        
        $this->sendResponse([
            'success' => true,
            'budget' => $budgetData
        ]);
    }
    
    /**
     * Get real-time metrics
     */
    private function getMetrics(): void
    {
        $metrics = [
            'totalProcessed' => $this->getTodayProcessedCount(),
            'activeInstances' => $this->getActiveInstanceCount(),
            'dailySpend' => $this->getDailySpending(),
            'tokensUsed' => round($this->getTodayTokenCount() / 1000, 1), // In thousands
            'successRate' => $this->getSuccessRate(),
            'errorRate' => $this->getErrorRate(),
            'avgProcessingTime' => $this->getAverageProcessingTime(),
            'queueLength' => $this->getQueueLength(),
            'cpuUsage' => $this->getCPUUsage(),
            'memoryUsage' => $this->getMemoryUsage(),
            'filesPerHour' => $this->getFilesPerHour(),
            'avgTokensPerFile' => $this->getAverageTokensPerFile(),
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        $this->sendResponse([
            'success' => true,
            'metrics' => $metrics
        ]);
    }
    
    /**
     * Get logs
     */
    private function getLogs(): void
    {
        $level = $_GET['level'] ?? 'all';
        $limit = (int)($_GET['limit'] ?? 100);
        
        $logs = $this->getLogsFromDatabase($level, $limit);
        
        $this->sendResponse([
            'success' => true,
            'logs' => $logs,
            'count' => count($logs)
        ]);
    }
    
    /**
     * Clear logs
     */
    private function clearLogs(): void
    {
        try {
            // Clear database logs
            $this->db->exec("DELETE FROM ai_usage_tracking WHERE timestamp < datetime('now', '-7 days')");
            
            // Clear log files
            $logFiles = glob($this->logDir . '/*.log');
            foreach ($logFiles as $logFile) {
                file_put_contents($logFile, '');
            }
            
            $this->logAction('Log files cleared');
            
            $this->sendResponse([
                'success' => true,
                'message' => 'Logs cleared successfully'
            ]);
            
        } catch (Exception $e) {
            $this->sendError('Failed to clear logs: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Download logs as ZIP file
     */
    private function downloadLogs(): void
    {
        $zipFile = $this->logDir . '/logs_export_' . date('Y-m-d_H-i-s') . '.zip';
        
        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE) === TRUE) {
            // Add log files
            $logFiles = glob($this->logDir . '/*.log');
            foreach ($logFiles as $logFile) {
                $zip->addFile($logFile, basename($logFile));
            }
            
            // Add usage data as JSON
            $usageData = $this->exportUsageData();
            $zip->addFromString('usage_data.json', json_encode($usageData, JSON_PRETTY_PRINT));
            
            $zip->close();
            
            // Send file for download
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="ai_logs_' . date('Y-m-d') . '.zip"');
            header('Content-Length: ' . filesize($zipFile));
            readfile($zipFile);
            
            // Clean up
            unlink($zipFile);
            exit();
        } else {
            $this->sendError('Failed to create log archive', 500);
        }
    }
    
    /**
     * Get active instances
     */
    private function getInstances(): void
    {
        $instances = $this->getActiveInstances();
        
        $this->sendResponse([
            'success' => true,
            'instances' => $instances,
            'count' => count($instances)
        ]);
    }
    
    /**
     * Get usage statistics
     */
    private function getUsageStats(): void
    {
        $timeframe = $_GET['timeframe'] ?? '24h';
        $stats = $this->calculateUsageStats($timeframe);
        
        $this->sendResponse([
            'success' => true,
            'stats' => $stats,
            'timeframe' => $timeframe
        ]);
    }
    
    // Helper methods
    
    private function validateSettings(array $settings): array
    {
        // Validate numeric ranges
        if (isset($settings['instances']['maxInstances'])) {
            $max = (int)$settings['instances']['maxInstances'];
            if ($max < 1 || $max > 20) {
                return ['valid' => false, 'error' => 'Max instances must be between 1 and 20'];
            }
        }
        
        if (isset($settings['budgets']['daily'])) {
            $daily = (float)$settings['budgets']['daily'];
            if ($daily < 0 || $daily > 10000) {
                return ['valid' => false, 'error' => 'Daily budget must be between $0 and $10,000'];
            }
        }
        
        // Validate token limits
        if (isset($settings['tokens'])) {
            foreach ($settings['tokens'] as $type => $limit) {
                $limit = (int)$limit;
                if ($limit < 10 || $limit > 20000) {
                    return ['valid' => false, 'error' => "Token limit for {$type} must be between 10 and 20,000"];
                }
            }
        }
        
        return ['valid' => true, 'error' => null];
    }
    
    private function mergeSettings(array $config, array $newSettings): array
    {
        // Deep merge settings
        foreach ($newSettings as $section => $values) {
            if (!isset($config['ai_analysis'][$section])) {
                $config['ai_analysis'][$section] = [];
            }
            
            foreach ($values as $key => $value) {
                switch ($section) {
                    case 'instances':
                        $config['ai_analysis']['multi_instance_analysis']['batch_processing']['batch_size'] = (int)$value['batchSize'];
                        $config['ai_analysis']['multi_instance_analysis']['rate_limiting']['max_concurrent_instances'] = (int)$value['maxInstances'];
                        break;
                        
                    case 'tokens':
                        $config['ai_analysis']['multi_instance_analysis']['token_optimization']['token_limits']['summary_1_sentence'] = (int)$value['summary'];
                        $config['ai_analysis']['multi_instance_analysis']['token_optimization']['token_limits']['summary_1_paragraph'] = (int)$value['paragraph'];
                        $config['ai_analysis']['multi_instance_analysis']['token_optimization']['token_limits']['detailed_analysis'] = (int)$value['detailed'];
                        $config['ai_analysis']['multi_instance_analysis']['token_optimization']['token_limits']['comprehensive_review'] = (int)$value['comprehensive'];
                        break;
                        
                    case 'budgets':
                        $config['ai_analysis']['spending_controls']['budget_management']['daily_budget_usd'] = (float)$value['daily'];
                        $config['ai_analysis']['spending_controls']['budget_management']['weekly_budget_usd'] = (float)$value['weekly'];
                        $config['ai_analysis']['spending_controls']['budget_management']['monthly_budget_usd'] = (float)$value['monthly'];
                        break;
                        
                    case 'rateLimiting':
                        $config['ai_analysis']['spending_controls']['rate_limiting']['max_requests_per_minute'] = (int)$value['requestsPerMinute'];
                        $config['ai_analysis']['spending_controls']['rate_limiting']['max_requests_per_hour'] = (int)$value['requestsPerHour'];
                        $config['ai_analysis']['spending_controls']['rate_limiting']['max_concurrent_requests'] = (int)$value['maxConcurrent'];
                        break;
                }
            }
        }
        
        return $config;
    }
    
    private function getDefaultSettings(): array
    {
        return [
            "ai_analysis" => [
                "multi_instance_analysis" => [
                    "enabled" => true,
                    "batch_processing" => [
                        "batch_size" => 10,
                        "max_concurrent_batches" => 3,
                        "batch_timeout_seconds" => 300
                    ],
                    "rate_limiting" => [
                        "max_concurrent_instances" => 5,
                        "cooldown_between_batches_ms" => 500,
                        "burst_allowance" => 2
                    ],
                    "token_optimization" => [
                        "adaptive_token_limits" => true,
                        "token_limits" => [
                            "summary_1_sentence" => 50,
                            "summary_1_paragraph" => 200,
                            "detailed_analysis" => 1000,
                            "comprehensive_review" => 4000
                        ]
                    ]
                ],
                "spending_controls" => [
                    "budget_management" => [
                        "daily_budget_usd" => 50.0,
                        "weekly_budget_usd" => 300.0,
                        "monthly_budget_usd" => 1000.0,
                        "emergency_stop_at_90_percent" => true
                    ],
                    "rate_limiting" => [
                        "max_requests_per_minute" => 60,
                        "max_requests_per_hour" => 1000,
                        "max_concurrent_requests" => 5,
                        "cooldown_period_seconds" => 1
                    ]
                ]
            ]
        ];
    }
    
    private function checkBudgetLimits(): bool
    {
        $dailySpent = $this->getDailySpending();
        $dailyLimit = $this->config['ai_analysis']['spending_controls']['budget_management']['daily_budget_usd'];
        
        return $dailySpent < $dailyLimit;
    }
    
    private function killAIProcesses(): void
    {
        // Kill any running AI batch processors
        exec("pkill -f 'ai-batch-processor.php'");
        exec("pkill -f 'ai-activity-analyzer.php'");
        
        // Stop cron jobs temporarily
        $stopFile = $this->logDir . '/cron_stopped.flag';
        file_put_contents($stopFile, date('Y-m-d H:i:s'));
    }
    
    // Database helper methods
    
    private function getDailySpending(): float
    {
        $stmt = $this->db->prepare("SELECT SUM(cost_usd) FROM ai_usage_tracking WHERE DATE(timestamp) = DATE('now')");
        $stmt->execute();
        return (float)$stmt->fetchColumn();
    }
    
    private function getWeeklySpending(): float
    {
        $stmt = $this->db->prepare("SELECT SUM(cost_usd) FROM ai_usage_tracking WHERE timestamp >= datetime('now', '-7 days')");
        $stmt->execute();
        return (float)$stmt->fetchColumn();
    }
    
    private function getMonthlySpending(): float
    {
        $stmt = $this->db->prepare("SELECT SUM(cost_usd) FROM ai_usage_tracking WHERE timestamp >= datetime('now', '-30 days')");
        $stmt->execute();
        return (float)$stmt->fetchColumn();
    }
    
    private function getTodayProcessedCount(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM ai_usage_tracking WHERE DATE(timestamp) = DATE('now')");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
    
    private function getTodayTokenCount(): int
    {
        $stmt = $this->db->prepare("SELECT SUM(tokens_used) FROM ai_usage_tracking WHERE DATE(timestamp) = DATE('now')");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
    
    private function getSuccessRate(): float
    {
        $stmt = $this->db->prepare("
            SELECT 
                (COUNT(CASE WHEN type != 'error' THEN 1 END) * 100.0 / COUNT(*)) as success_rate
            FROM ai_usage_tracking 
            WHERE DATE(timestamp) = DATE('now')
        ");
        $stmt->execute();
        return round((float)$stmt->fetchColumn(), 1);
    }
    
    private function getErrorRate(): float
    {
        return 100.0 - $this->getSuccessRate();
    }
    
    private function getAverageProcessingTime(): float
    {
        $stmt = $this->db->prepare("SELECT AVG(duration_seconds) FROM ai_usage_tracking WHERE DATE(timestamp) = DATE('now')");
        $stmt->execute();
        return round((float)$stmt->fetchColumn(), 1);
    }
    
    private function getActiveInstanceCount(): int
    {
        // Count running AI processes
        $output = [];
        exec("pgrep -f 'ai-batch-processor.php' | wc -l", $output);
        return (int)($output[0] ?? 0);
    }
    
    private function getQueueLength(): int
    {
        // This would be implemented based on your queuing system
        return 0;
    }
    
    private function getCPUUsage(): string
    {
        $output = [];
        exec("top -bn1 | grep 'Cpu(s)' | awk '{print $2}' | cut -d'%' -f1", $output);
        return ($output[0] ?? '0') . '%';
    }
    
    private function getMemoryUsage(): string
    {
        $output = [];
        exec("free -h | grep Mem | awk '{print $3}'", $output);
        return $output[0] ?? '0MB';
    }
    
    private function getFilesPerHour(): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM ai_usage_tracking 
            WHERE timestamp >= datetime('now', '-1 hour')
        ");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
    
    private function getAverageTokensPerFile(): int
    {
        $stmt = $this->db->prepare("
            SELECT AVG(tokens_used) 
            FROM ai_usage_tracking 
            WHERE DATE(timestamp) = DATE('now')
        ");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }
    
    private function getLogsFromDatabase(string $level, int $limit): array
    {
        $logFile = $this->logDir . '/ai-batch-processor.log';
        $logs = [];
        
        if (file_exists($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $lines = array_slice($lines, -$limit); // Get last N lines
            
            foreach ($lines as $line) {
                if (preg_match('/\[([^\]]+)\] \[([^\]]+)\] (.+)/', $line, $matches)) {
                    $logs[] = [
                        'timestamp' => $matches[1],
                        'level' => strtolower($matches[2]),
                        'message' => $matches[3]
                    ];
                }
            }
        }
        
        return array_reverse($logs); // Most recent first
    }
    
    private function getActiveInstances(): array
    {
        $instances = [];
        
        // Get running AI processes
        $output = [];
        exec("ps aux | grep 'ai-batch-processor.php' | grep -v grep", $output);
        
        foreach ($output as $line) {
            if (preg_match('/^\S+\s+(\d+).*?(\d{2}:\d{2}:\d{2}).*ai-batch-processor\.php/', $line, $matches)) {
                $instances[] = [
                    'pid' => (int)$matches[1],
                    'runtime' => $matches[2],
                    'status' => 'running',
                    'memory' => $this->getProcessMemory((int)$matches[1])
                ];
            }
        }
        
        return $instances;
    }
    
    private function getProcessMemory(int $pid): string
    {
        $output = [];
        exec("ps -o rss= -p {$pid}", $output);
        $kb = (int)($output[0] ?? 0);
        return round($kb / 1024, 1) . 'MB';
    }
    
    private function calculateUsageStats(string $timeframe): array
    {
        $where = match($timeframe) {
            '1h' => "timestamp >= datetime('now', '-1 hour')",
            '24h' => "timestamp >= datetime('now', '-1 day')",
            '7d' => "timestamp >= datetime('now', '-7 days')",
            '30d' => "timestamp >= datetime('now', '-30 days')",
            default => "timestamp >= datetime('now', '-1 day')"
        };
        
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_requests,
                SUM(tokens_used) as total_tokens,
                SUM(cost_usd) as total_cost,
                AVG(duration_seconds) as avg_duration,
                COUNT(DISTINCT project) as projects_analyzed
            FROM ai_usage_tracking 
            WHERE {$where}
        ");
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function exportUsageData(): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM ai_usage_tracking 
            ORDER BY timestamp DESC 
            LIMIT 10000
        ");
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Utility methods
    
    private function loadConfig(): array
    {
        if (!file_exists($this->configFile)) {
            return $this->getDefaultSettings();
        }
        
        $config = json_decode(file_get_contents($this->configFile), true);
        return $config ?: $this->getDefaultSettings();
    }
    
    private function saveConfig(): bool
    {
        $backup = $this->configFile . '.backup.' . date('Y-m-d-H-i-s');
        copy($this->configFile, $backup);
        
        return file_put_contents($this->configFile, json_encode($this->config, JSON_PRETTY_PRINT)) !== false;
    }
    
    private function initializeDatabase(): void
    {
        try {
            $this->db = new PDO('sqlite:' . $this->logDir . '/ai_usage.db');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create tables if they don't exist
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS ai_usage_tracking (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    timestamp TEXT NOT NULL,
                    type TEXT NOT NULL,
                    project TEXT NOT NULL,
                    file_path TEXT,
                    analysis_type TEXT,
                    tokens_used INTEGER,
                    cost_usd REAL,
                    duration_seconds REAL
                )
            ");
            
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_timestamp ON ai_usage_tracking(timestamp)");
            $this->db->exec("CREATE INDEX IF NOT EXISTS idx_project ON ai_usage_tracking(project)");
            
        } catch (PDOException $e) {
            error_log("Database initialization failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    private function logAction(string $message, string $level = 'INFO'): void
    {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $level,
            'message' => $message,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        $logFile = $this->logDir . '/dashboard-api.log';
        file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND);
    }
    
    private function sendResponse(array $data): void
    {
        echo json_encode($data);
        exit();
    }
    
    private function sendError(string $message, int $code = 400): void
    {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit();
    }
}

// Initialize and handle request
try {
    $api = new AIControlAPI();
    $api->handleRequest();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal server error: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}