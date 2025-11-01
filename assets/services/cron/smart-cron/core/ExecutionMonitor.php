<?php
/**
 * Smart Cron - Execution Monitor
 * 
 * Enterprise-grade execution monitoring and health scoring
 * 
 * @package SmartCron\Core
 * @version 2.0.0
 */

declare(strict_types=1);

namespace SmartCron\Core;

class ExecutionMonitor
{
    private Config $config;
    private ?\mysqli $db;
    
    // Health score weights
    private const WEIGHT_SCRIPT_AVAILABILITY = 0.20;
    private const WEIGHT_EXECUTION_SUCCESS = 0.35;
    private const WEIGHT_PERFORMANCE = 0.25;
    private const WEIGHT_RELIABILITY = 0.20;
    
    public function __construct()
    {
        $this->config = new Config();
        $this->db = $this->config->getDbConnection();
    }
    
    /**
     * Calculate comprehensive system health score
     */
    public function calculateHealthScore(): array
    {
        $scriptAvailability = $this->calculateScriptAvailability();
        $executionSuccess = $this->calculateExecutionSuccessRate();
        $performance = $this->calculatePerformanceScore();
        $reliability = $this->calculateReliabilityScore();
        
        $overallHealth = (
            ($scriptAvailability * self::WEIGHT_SCRIPT_AVAILABILITY) +
            ($executionSuccess * self::WEIGHT_EXECUTION_SUCCESS) +
            ($performance * self::WEIGHT_PERFORMANCE) +
            ($reliability * self::WEIGHT_RELIABILITY)
        );
        
        return [
            'overall_health' => round($overallHealth, 2),
            'components' => [
                'script_availability' => round($scriptAvailability, 2),
                'execution_success_rate' => round($executionSuccess, 2),
                'performance_score' => round($performance, 2),
                'reliability_score' => round($reliability, 2)
            ],
            'weights' => [
                'script_availability' => self::WEIGHT_SCRIPT_AVAILABILITY * 100,
                'execution_success' => self::WEIGHT_EXECUTION_SUCCESS * 100,
                'performance' => self::WEIGHT_PERFORMANCE * 100,
                'reliability' => self::WEIGHT_RELIABILITY * 100
            ],
            'status' => $this->getHealthStatus($overallHealth),
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Calculate script availability score
     */
    private function calculateScriptAvailability(): float
    {
        $tasks = $this->config->getTasks();
        if (empty($tasks)) {
            return 0;
        }
        
        $projectRoot = '/home/master/applications/jcepnzzkmj/public_html';
        $validScripts = 0;
        
        foreach ($tasks as $task) {
            if (file_exists($projectRoot . '/' . $task['script'])) {
                $validScripts++;
            }
        }
        
        return ($validScripts / count($tasks)) * 100;
    }
    
    /**
     * Calculate execution success rate (last 24 hours)
     */
    private function calculateExecutionSuccessRate(): float
    {
        if (!$this->db) {
            return 0;
        }
        
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful
            FROM smart_cron_executions
            WHERE started_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                AND completed_at IS NOT NULL
        ");
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['total'] == 0) {
            return 100; // No executions = assume healthy
        }
        
        return ($result['successful'] / $result['total']) * 100;
    }
    
    /**
     * Calculate performance score
     */
    private function calculatePerformanceScore(): float
    {
        if (!$this->db) {
            return 0;
        }
        
        // Get average performance vs baselines
        $stmt = $this->db->prepare("
            SELECT 
                e.task_name,
                AVG(e.duration_seconds) as avg_duration,
                t.baseline_duration_seconds
            FROM smart_cron_executions e
            JOIN smart_cron_tasks t ON e.task_name = t.task_name
            WHERE e.started_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                AND e.completed_at IS NOT NULL
                AND e.status = 'success'
                AND t.baseline_duration_seconds IS NOT NULL
            GROUP BY e.task_name, t.baseline_duration_seconds
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $scores = [];
        while ($row = $result->fetch_assoc()) {
            $ratio = $row['avg_duration'] / $row['baseline_duration_seconds'];
            
            // Score based on how close to baseline
            if ($ratio <= 1.0) {
                $scores[] = 100; // At or better than baseline
            } elseif ($ratio <= 1.5) {
                $scores[] = 85; // Within 50% of baseline
            } elseif ($ratio <= 2.0) {
                $scores[] = 70; // Within 100% of baseline
            } else {
                $scores[] = 50; // Significantly slower
            }
        }
        
        if (empty($scores)) {
            return 90; // No baselines = assume good
        }
        
        return array_sum($scores) / count($scores);
    }
    
    /**
     * Calculate reliability score (failure patterns)
     */
    private function calculateReliabilityScore(): float
    {
        if (!$this->db) {
            return 0;
        }
        
        // Check for consecutive failures
        $stmt = $this->db->prepare("
            SELECT 
                task_name,
                consecutive_failures,
                alert_threshold_failures
            FROM smart_cron_tasks
            WHERE enabled = TRUE
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $scores = [];
        while ($row = $result->fetch_assoc()) {
            $failures = (int)$row['consecutive_failures'];
            $threshold = (int)$row['alert_threshold_failures'];
            
            if ($failures == 0) {
                $scores[] = 100;
            } elseif ($failures < $threshold) {
                $scores[] = 75;
            } else {
                $scores[] = 40; // Threshold exceeded
            }
        }
        
        if (empty($scores)) {
            return 100;
        }
        
        return array_sum($scores) / count($scores);
    }
    
    /**
     * Get health status label
     */
    private function getHealthStatus(float $score): string
    {
        if ($score >= 95) return 'excellent';
        if ($score >= 85) return 'good';
        if ($score >= 70) return 'fair';
        if ($score >= 50) return 'poor';
        return 'critical';
    }
    
    /**
     * Get task execution summary
     */
    public function getTaskSummary(string $taskName, int $hours = 24): array
    {
        if (!$this->db) {
            return [];
        }
        
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_executions,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                SUM(CASE WHEN status = 'timeout' THEN 1 ELSE 0 END) as timeout,
                AVG(duration_seconds) as avg_duration,
                MAX(duration_seconds) as max_duration,
                MIN(duration_seconds) as min_duration,
                AVG(memory_peak_mb) as avg_memory,
                MAX(memory_peak_mb) as max_memory,
                MAX(completed_at) as last_execution
            FROM smart_cron_executions
            WHERE task_name = ?
                AND started_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)
                AND completed_at IS NOT NULL
        ");
        $stmt->bind_param('si', $taskName, $hours);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['total_executions'] > 0) {
            $result['success_rate'] = round(
                ($result['successful'] / $result['total_executions']) * 100,
                2
            );
        } else {
            $result['success_rate'] = 0;
        }
        
        return $result;
    }
    
    /**
     * Detect performance anomalies
     */
    public function detectAnomalies(): array
    {
        if (!$this->db) {
            return [];
        }
        
        $anomalies = [];
        
        // Check for tasks with high failure rates
        $stmt = $this->db->prepare("
            SELECT 
                task_name,
                COUNT(*) as total,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failures,
                ROUND(100.0 * SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) / COUNT(*), 2) as failure_rate
            FROM smart_cron_executions
            WHERE started_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                AND completed_at IS NOT NULL
            GROUP BY task_name
            HAVING failure_rate > 20
            ORDER BY failure_rate DESC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $anomalies[] = [
                'type' => 'high_failure_rate',
                'severity' => $row['failure_rate'] > 50 ? 'critical' : 'warning',
                'task_name' => $row['task_name'],
                'details' => $row
            ];
        }
        
        // Check for slow tasks (2x baseline)
        $stmt = $this->db->prepare("
            SELECT 
                e.task_name,
                AVG(e.duration_seconds) as current_avg,
                t.baseline_duration_seconds,
                ROUND(AVG(e.duration_seconds) / t.baseline_duration_seconds, 2) as slowdown_factor
            FROM smart_cron_executions e
            JOIN smart_cron_tasks t ON e.task_name = t.task_name
            WHERE e.started_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                AND e.completed_at IS NOT NULL
                AND e.status = 'success'
                AND t.baseline_duration_seconds IS NOT NULL
            GROUP BY e.task_name, t.baseline_duration_seconds
            HAVING slowdown_factor > 2.0
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $anomalies[] = [
                'type' => 'performance_degradation',
                'severity' => 'warning',
                'task_name' => $row['task_name'],
                'details' => $row
            ];
        }
        
        return $anomalies;
    }
    
    /**
     * Get system-wide trends
     */
    public function getTrends(int $days = 7): array
    {
        if (!$this->db) {
            return [];
        }
        
        $stmt = $this->db->prepare("
            SELECT 
                DATE(started_at) as date,
                COUNT(*) as total_executions,
                SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
                AVG(duration_seconds) as avg_duration,
                AVG(memory_peak_mb) as avg_memory
            FROM smart_cron_executions
            WHERE started_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                AND completed_at IS NOT NULL
            GROUP BY DATE(started_at)
            ORDER BY date ASC
        ");
        $stmt->bind_param('i', $days);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $trends = [];
        while ($row = $result->fetch_assoc()) {
            $row['success_rate'] = $row['total_executions'] > 0
                ? round(($row['successful'] / $row['total_executions']) * 100, 2)
                : 0;
            $trends[] = $row;
        }
        
        return $trends;
    }
    
    /**
     * Update task baselines automatically
     */
    public function updateBaselines(): void
    {
        if (!$this->db) {
            return;
        }
        
        // Update baselines for tasks with sufficient data
        $stmt = $this->db->prepare("
            UPDATE smart_cron_tasks t
            JOIN (
                SELECT 
                    task_name,
                    AVG(duration_seconds) as avg_duration,
                    AVG(memory_peak_mb) as avg_memory,
                    COUNT(*) as sample_size
                FROM smart_cron_executions
                WHERE started_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                    AND status = 'success'
                    AND completed_at IS NOT NULL
                GROUP BY task_name
                HAVING sample_size >= 20
            ) e ON t.task_name = e.task_name
            SET 
                t.baseline_duration_seconds = e.avg_duration,
                t.baseline_memory_mb = e.avg_memory,
                t.updated_at = NOW()
        ");
        $stmt->execute();
    }
}