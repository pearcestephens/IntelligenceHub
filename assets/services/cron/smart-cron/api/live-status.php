<?php
/**
 * Smart Cron - Live Status API
 * 
 * Enterprise-grade real-time execution monitoring endpoint
 * Provides comprehensive system health and execution data
 * 
 * @package SmartCron\API
 * @version 2.0.0
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: 0');

// CORS headers for dashboard integration
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../core/Config.php';
require_once __DIR__ . '/../core/ExecutionMonitor.php';

use SmartCron\Core\Config;
use SmartCron\Core\ExecutionMonitor;

/**
 * API Response Handler
 */
class LiveStatusAPI
{
    private Config $config;
    private ExecutionMonitor $monitor;
    private \mysqli $db;
    
    public function __construct()
    {
        $this->config = new Config();
        $this->monitor = new ExecutionMonitor();
        $this->db = $this->config->getDbConnection();
        
        if (!$this->db) {
            $this->errorResponse('Database connection failed', 500);
        }
    }
    
    /**
     * Main execution handler
     */
    public function handle(): void
    {
        try {
            $action = $_GET['action'] ?? 'status';
            $timeRange = $_GET['range'] ?? '24h';
            
            switch ($action) {
                case 'status':
                    $this->getStatus();
                    break;
                case 'running':
                    $this->getRunningTasks();
                    break;
                case 'history':
                    $this->getExecutionHistory($timeRange);
                    break;
                case 'metrics':
                    $this->getMetrics($timeRange);
                    break;
                case 'health':
                    $this->getHealthScore();
                    break;
                case 'alerts':
                    $this->getActiveAlerts();
                    break;
                case 'task':
                    $taskName = $_GET['task'] ?? null;
                    if ($taskName) {
                        $this->getTaskDetails($taskName);
                    } else {
                        $this->errorResponse('Task name required', 400);
                    }
                    break;
                default:
                    $this->errorResponse('Invalid action', 400);
            }
        } catch (\Exception $e) {
            $this->errorResponse($e->getMessage(), 500);
        }
    }
    
    /**
     * Get comprehensive system status
     */
    private function getStatus(): void
    {
        $data = [
            'timestamp' => date('Y-m-d H:i:s'),
            'system' => $this->getSystemOverview(),
            'currently_running' => $this->getCurrentlyRunning(),
            'recent_executions' => $this->getRecentExecutions(10),
            'failure_rates' => $this->getFailureRates('24h'),
            'performance_summary' => $this->getPerformanceSummary(),
            'active_alerts' => $this->getAlertsSummary()
        ];
        
        $this->successResponse($data);
    }
    
    /**
     * Get system overview metrics
     */
    private function getSystemOverview(): array
    {
        $tasks = $this->config->getTasks();
        $enabledTasks = array_filter($tasks, fn($t) => $t['enabled'] ?? false);
        
        // Get script validation
        $projectRoot = '/home/master/applications/jcepnzzkmj/public_html';
        $validScripts = 0;
        foreach ($tasks as $task) {
            if (file_exists($projectRoot . '/' . $task['script'])) {
                $validScripts++;
            }
        }
        
        // Get currently running count
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as running_count 
            FROM smart_cron_executions 
            WHERE status = 'running'
        ");
        $stmt->execute();
        $runningCount = $stmt->get_result()->fetch_assoc()['running_count'];
        
        return [
            'total_tasks' => count($tasks),
            'enabled_tasks' => count($enabledTasks),
            'disabled_tasks' => count($tasks) - count($enabledTasks),
            'valid_scripts' => $validScripts,
            'missing_scripts' => count($tasks) - $validScripts,
            'currently_running' => $runningCount,
            'script_validation_rate' => round(($validScripts / count($tasks)) * 100, 2)
        ];
    }
    
    /**
     * Get currently running tasks with details
     */
    private function getCurrentlyRunning(): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                execution_uuid,
                task_name,
                started_at,
                TIMESTAMPDIFF(SECOND, started_at, NOW()) as running_for_seconds,
                pid,
                script_path
            FROM smart_cron_executions 
            WHERE status = 'running'
            ORDER BY started_at ASC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $running = [];
        while ($row = $result->fetch_assoc()) {
            $row['running_for_formatted'] = $this->formatDuration($row['running_for_seconds']);
            $running[] = $row;
        }
        
        return $running;
    }
    
    /**
     * Get recent executions
     */
    private function getRecentExecutions(int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                execution_uuid,
                task_name,
                status,
                started_at,
                completed_at,
                duration_seconds,
                memory_peak_mb,
                exit_code,
                error_message
            FROM smart_cron_executions 
            WHERE completed_at IS NOT NULL
            ORDER BY completed_at DESC
            LIMIT ?
        ");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $executions = [];
        while ($row = $result->fetch_assoc()) {
            $row['duration_formatted'] = $this->formatDuration($row['duration_seconds']);
            $executions[] = $row;
        }
        
        return $executions;
    }
    
    /**
     * Get failure rates for all tasks
     */
    private function getFailureRates(string $timeRange): array
    {
        $hours = $this->parseTimeRange($timeRange);
        
        $stmt = $this->db->prepare("
            SELECT 
                task_name,
                COUNT(*) as total_runs,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful_runs,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_runs,
                SUM(CASE WHEN status = 'timeout' THEN 1 ELSE 0 END) as timeout_runs,
                ROUND(100.0 * SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) / COUNT(*), 2) as success_rate,
                ROUND(100.0 * SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) / COUNT(*), 2) as failure_rate,
                AVG(duration_seconds) as avg_duration,
                MAX(duration_seconds) as max_duration,
                AVG(memory_peak_mb) as avg_memory
            FROM smart_cron_executions
            WHERE started_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
                AND completed_at IS NOT NULL
            GROUP BY task_name
            ORDER BY failure_rate DESC, total_runs DESC
        ");
        $stmt->bind_param('i', $hours);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $rates = [];
        while ($row = $result->fetch_assoc()) {
            $row['health_status'] = $this->calculateTaskHealth($row);
            $rates[] = $row;
        }
        
        return $rates;
    }
    
    /**
     * Get performance summary
     */
    private function getPerformanceSummary(): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_executions,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful_executions,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_executions,
                SUM(CASE WHEN status = 'timeout' THEN 1 ELSE 0 END) as timeout_executions,
                AVG(duration_seconds) as avg_duration,
                MAX(duration_seconds) as max_duration,
                MIN(duration_seconds) as min_duration,
                AVG(memory_peak_mb) as avg_memory,
                MAX(memory_peak_mb) as max_memory
            FROM smart_cron_executions
            WHERE started_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                AND completed_at IS NOT NULL
        ");
        $stmt->execute();
        $summary = $stmt->get_result()->fetch_assoc();
        
        if ($summary['total_executions'] > 0) {
            $summary['success_rate'] = round(
                ($summary['successful_executions'] / $summary['total_executions']) * 100, 
                2
            );
        } else {
            $summary['success_rate'] = 0;
        }
        
        return $summary;
    }
    
    /**
     * Get active alerts summary
     */
    private function getAlertsSummary(): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                severity,
                COUNT(*) as count
            FROM smart_cron_alerts
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                AND resolved_at IS NULL
            GROUP BY severity
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $summary = [
            'critical' => 0,
            'error' => 0,
            'warning' => 0,
            'info' => 0,
            'total' => 0
        ];
        
        while ($row = $result->fetch_assoc()) {
            $summary[$row['severity']] = (int)$row['count'];
            $summary['total'] += (int)$row['count'];
        }
        
        return $summary;
    }
    
    /**
     * Get running tasks endpoint
     */
    private function getRunningTasks(): void
    {
        $running = $this->getCurrentlyRunning();
        $this->successResponse([
            'count' => count($running),
            'tasks' => $running,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get execution history with filtering
     */
    private function getExecutionHistory(string $timeRange): void
    {
        $hours = $this->parseTimeRange($timeRange);
        $taskName = $_GET['task'] ?? null;
        $status = $_GET['status'] ?? null;
        $limit = min((int)($_GET['limit'] ?? 100), 500);
        
        $query = "
            SELECT 
                execution_uuid,
                task_name,
                status,
                started_at,
                completed_at,
                duration_seconds,
                memory_peak_mb,
                cpu_percent,
                exit_code,
                error_message
            FROM smart_cron_executions
            WHERE started_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
        ";
        
        $params = [$hours];
        $types = 'i';
        
        if ($taskName) {
            $query .= " AND task_name = ?";
            $params[] = $taskName;
            $types .= 's';
        }
        
        if ($status) {
            $query .= " AND status = ?";
            $params[] = $status;
            $types .= 's';
        }
        
        $query .= " ORDER BY started_at DESC LIMIT ?";
        $params[] = $limit;
        $types .= 'i';
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $history = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['duration_seconds']) {
                $row['duration_formatted'] = $this->formatDuration($row['duration_seconds']);
            }
            $history[] = $row;
        }
        
        $this->successResponse([
            'count' => count($history),
            'history' => $history,
            'filters' => [
                'time_range' => $timeRange,
                'task' => $taskName,
                'status' => $status
            ],
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get aggregated metrics
     */
    private function getMetrics(string $timeRange): void
    {
        $hours = $this->parseTimeRange($timeRange);
        
        // Hourly breakdown
        $stmt = $this->db->prepare("
            SELECT 
                DATE_FORMAT(started_at, '%Y-%m-%d %H:00:00') as hour,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as success,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                AVG(duration_seconds) as avg_duration,
                AVG(memory_peak_mb) as avg_memory
            FROM smart_cron_executions
            WHERE started_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
                AND completed_at IS NOT NULL
            GROUP BY hour
            ORDER BY hour ASC
        ");
        $stmt->bind_param('i', $hours);
        $stmt->execute();
        $hourly = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Top slowest tasks
        $stmt = $this->db->prepare("
            SELECT 
                task_name,
                AVG(duration_seconds) as avg_duration,
                MAX(duration_seconds) as max_duration,
                COUNT(*) as execution_count
            FROM smart_cron_executions
            WHERE started_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
                AND completed_at IS NOT NULL
            GROUP BY task_name
            ORDER BY avg_duration DESC
            LIMIT 10
        ");
        $stmt->bind_param('i', $hours);
        $stmt->execute();
        $slowest = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Top memory consumers
        $stmt = $this->db->prepare("
            SELECT 
                task_name,
                AVG(memory_peak_mb) as avg_memory,
                MAX(memory_peak_mb) as max_memory,
                COUNT(*) as execution_count
            FROM smart_cron_executions
            WHERE started_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
                AND completed_at IS NOT NULL
            GROUP BY task_name
            ORDER BY avg_memory DESC
            LIMIT 10
        ");
        $stmt->bind_param('i', $hours);
        $stmt->execute();
        $memoryHeavy = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $this->successResponse([
            'time_range' => $timeRange,
            'hourly_breakdown' => $hourly,
            'slowest_tasks' => $slowest,
            'memory_intensive_tasks' => $memoryHeavy,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get comprehensive health score
     */
    private function getHealthScore(): void
    {
        $health = $this->monitor->calculateHealthScore();
        $this->successResponse($health);
    }
    
    /**
     * Get active alerts
     */
    private function getActiveAlerts(): void
    {
        $stmt = $this->db->prepare("
            SELECT 
                a.id,
                a.task_name,
                a.alert_type,
                a.severity,
                a.message,
                a.created_at,
                e.execution_uuid,
                e.started_at as execution_started
            FROM smart_cron_alerts a
            LEFT JOIN smart_cron_executions e ON a.execution_id = e.id
            WHERE a.resolved_at IS NULL
            ORDER BY 
                FIELD(a.severity, 'critical', 'error', 'warning', 'info'),
                a.created_at DESC
            LIMIT 100
        ");
        $stmt->execute();
        $alerts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $this->successResponse([
            'count' => count($alerts),
            'alerts' => $alerts,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get detailed task information
     */
    private function getTaskDetails(string $taskName): void
    {
        // Get task configuration
        $task = $this->config->getTask($taskName);
        
        if (!$task) {
            $this->errorResponse('Task not found', 404);
            return;
        }
        
        // Get recent executions
        $stmt = $this->db->prepare("
            SELECT *
            FROM smart_cron_executions
            WHERE task_name = ?
            ORDER BY started_at DESC
            LIMIT 20
        ");
        $stmt->bind_param('s', $taskName);
        $stmt->execute();
        $recentExecutions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Get statistics
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_runs,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful_runs,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_runs,
                AVG(duration_seconds) as avg_duration,
                MIN(duration_seconds) as min_duration,
                MAX(duration_seconds) as max_duration,
                AVG(memory_peak_mb) as avg_memory,
                MAX(memory_peak_mb) as max_memory,
                MAX(started_at) as last_run
            FROM smart_cron_executions
            WHERE task_name = ?
                AND started_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        ");
        $stmt->bind_param('s', $taskName);
        $stmt->execute();
        $stats = $stmt->get_result()->fetch_assoc();
        
        $this->successResponse([
            'task' => $task,
            'recent_executions' => $recentExecutions,
            'statistics_7d' => $stats,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Helper: Parse time range to hours
     */
    private function parseTimeRange(string $range): int
    {
        return match($range) {
            '1h' => 1,
            '6h' => 6,
            '12h' => 12,
            '24h' => 24,
            '48h' => 48,
            '7d' => 168,
            '30d' => 720,
            default => 24
        };
    }
    
    /**
     * Helper: Calculate task health
     */
    private function calculateTaskHealth(array $stats): string
    {
        $successRate = (float)($stats['success_rate'] ?? 0);
        
        if ($successRate >= 99) return 'excellent';
        if ($successRate >= 95) return 'good';
        if ($successRate >= 85) return 'fair';
        if ($successRate >= 70) return 'poor';
        return 'critical';
    }
    
    /**
     * Helper: Format duration
     */
    private function formatDuration(?float $seconds): string
    {
        if ($seconds === null) return 'N/A';
        
        if ($seconds < 60) {
            return round($seconds, 2) . 's';
        } elseif ($seconds < 3600) {
            return round($seconds / 60, 1) . 'm';
        } else {
            return round($seconds / 3600, 2) . 'h';
        }
    }
    
    /**
     * Success response
     */
    private function successResponse(array $data): void
    {
        echo json_encode([
            'success' => true,
            'data' => $data,
            'timestamp' => date('c')
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Error response
     */
    private function errorResponse(string $message, int $code = 400): void
    {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $message,
            'timestamp' => date('c')
        ], JSON_PRETTY_PRINT);
        exit;
    }
}

// Execute API
$api = new LiveStatusAPI();
$api->handle();