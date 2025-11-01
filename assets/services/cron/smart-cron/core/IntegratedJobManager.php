<?php
/**
 * Smart Cron - Integrated Job Manager
 * 
 * Centralized management of ALL system cron jobs with full performance tracking,
 * auto-discovery, health monitoring, and intelligent load balancing.
 * 
 * @package SmartCron\Core
 */

declare(strict_types=1);

namespace SmartCron\Core;

class IntegratedJobManager
{
    private Config $config;
    private \mysqli $db;
    private MetricsCollector $metrics;
    private ?LoadBalancer $loadBalancer = null;
    
    public function __construct(Config $config, MetricsCollector $metrics, ?LoadBalancer $loadBalancer = null)
    {
        $this->config = $config;
        $this->db = $config->getDbConnection();
        $this->metrics = $metrics;
        $this->loadBalancer = $loadBalancer;
        
        if ($this->db === null) {
            throw new \RuntimeException('Database connection required for IntegratedJobManager');
        }
    }
    
    /**
     * Register a new cron job (or update if exists)
     */
    public function registerJob(array $jobData): int
    {
        $required = ['job_name', 'script_path', 'schedule_type'];
        foreach ($required as $field) {
            if (empty($jobData[$field])) {
                throw new \InvalidArgumentException("Missing required field: {$field}");
            }
        }
        
        $jobName = $jobData['job_name'];
        
        // Check if job exists
        $stmt = $this->db->prepare("SELECT id FROM smart_cron_integrated_jobs WHERE job_name = ?");
        $stmt->bind_param('s', $jobName);
        $stmt->execute();
        $result = $stmt->get_result();
        $existing = $result->fetch_assoc();
        $stmt->close();
        
        if ($existing) {
            // Update existing job
            return $this->updateJob($existing['id'], $jobData);
        }
        
        // Insert new job
        $stmt = $this->db->prepare("
            INSERT INTO smart_cron_integrated_jobs (
                job_name, job_category, description, script_path, script_type, script_args,
                working_directory, schedule_type, cron_expression, interval_seconds, timezone,
                timeout_seconds, memory_limit_mb, max_concurrent, retry_on_failure, max_retries,
                retry_delay_seconds, priority, cpu_weight, memory_weight, can_run_during_peak,
                enabled, status, alert_on_failure, alert_on_timeout, alert_threshold_failures,
                created_by, auto_discovered
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        // Prepare parameters (must be variables for bind_param by-reference)
        $p_job_name = $jobData['job_name'];
        $p_job_category = $jobData['job_category'] ?? 'business';
        $p_description = $jobData['description'] ?? null;
        $p_script_path = $jobData['script_path'];
        $p_script_type = $jobData['script_type'] ?? 'php';
        $p_script_args = $jobData['script_args'] ?? null;
        $p_working_directory = $jobData['working_directory'] ?? null;
        $p_schedule_type = $jobData['schedule_type'];
        $p_cron_expression = $jobData['cron_expression'] ?? null;
        $p_interval_seconds = $jobData['interval_seconds'] ?? null;
        $p_timezone = $jobData['timezone'] ?? 'Pacific/Auckland';
        $p_timeout_seconds = $jobData['timeout_seconds'] ?? 300;
        $p_memory_limit_mb = $jobData['memory_limit_mb'] ?? 512;
        $p_max_concurrent = $jobData['max_concurrent'] ?? 1;
        $p_retry_on_failure = (int)($jobData['retry_on_failure'] ?? true);
        $p_max_retries = $jobData['max_retries'] ?? 2;
        $p_retry_delay_seconds = $jobData['retry_delay_seconds'] ?? 60;
        $p_priority = $jobData['priority'] ?? 'medium';
        $p_cpu_weight = $jobData['cpu_weight'] ?? 1.00;
        $p_memory_weight = $jobData['memory_weight'] ?? 1.00;
        $p_can_run_during_peak = (int)($jobData['can_run_during_peak'] ?? true);
        $p_enabled = (int)($jobData['enabled'] ?? true);
        $p_status = $jobData['status'] ?? 'active';
        $p_alert_on_failure = (int)($jobData['alert_on_failure'] ?? true);
        $p_alert_on_timeout = (int)($jobData['alert_on_timeout'] ?? true);
        $p_alert_threshold_failures = $jobData['alert_threshold_failures'] ?? 3;
        $p_created_by = $jobData['created_by'] ?? 'system';
        $p_auto_discovered = (int)($jobData['auto_discovered'] ?? false);
        
        $stmt->bind_param(
            'sssssssssissiiiiisddssssiiss',
            $p_job_name, $p_job_category, $p_description, $p_script_path, $p_script_type,
            $p_script_args, $p_working_directory, $p_schedule_type, $p_cron_expression,
            $p_interval_seconds, $p_timezone, $p_timeout_seconds, $p_memory_limit_mb,
            $p_max_concurrent, $p_retry_on_failure, $p_max_retries, $p_retry_delay_seconds,
            $p_priority, $p_cpu_weight, $p_memory_weight, $p_can_run_during_peak,
            $p_enabled, $p_status, $p_alert_on_failure, $p_alert_on_timeout,
            $p_alert_threshold_failures, $p_created_by, $p_auto_discovered
        );
        
        $stmt->execute();
        $jobId = $stmt->insert_id;
        $stmt->close();
        
        error_log("[IntegratedJobManager] ✅ Registered new job: {$jobName} (ID: {$jobId})");
        
        // Calculate next scheduled run
        $this->calculateNextRun($jobId);
        
        return $jobId;
    }
    
    /**
     * Update existing job
     */
    private function updateJob(int $jobId, array $jobData): int
    {
        $updateFields = [];
        $params = [];
        $types = '';
        
        $allowedUpdates = [
            'description' => 's',
            'script_path' => 's',
            'script_type' => 's',
            'script_args' => 's',
            'working_directory' => 's',
            'schedule_type' => 's',
            'cron_expression' => 's',
            'interval_seconds' => 'i',
            'timeout_seconds' => 'i',
            'memory_limit_mb' => 'i',
            'max_concurrent' => 'i',
            'retry_on_failure' => 'i',
            'max_retries' => 'i',
            'retry_delay_seconds' => 'i',
            'priority' => 's',
            'cpu_weight' => 'd',
            'memory_weight' => 'd',
            'can_run_during_peak' => 'i',
            'enabled' => 'i',
            'status' => 's',
            'alert_on_failure' => 'i',
            'alert_on_timeout' => 'i',
            'alert_threshold_failures' => 'i'
        ];
        
        foreach ($allowedUpdates as $field => $type) {
            if (array_key_exists($field, $jobData)) {
                $updateFields[] = "{$field} = ?";
                $params[] = $jobData[$field];
                $types .= $type;
            }
        }
        
        if (empty($updateFields)) {
            return $jobId; // Nothing to update
        }
        
        $sql = "UPDATE smart_cron_integrated_jobs SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $params[] = $jobId;
        $types .= 'i';
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $stmt->close();
        
        error_log("[IntegratedJobManager] ✅ Updated job ID: {$jobId}");
        
        // Recalculate next scheduled run if schedule changed
        if (isset($jobData['schedule_type']) || isset($jobData['cron_expression']) || isset($jobData['interval_seconds'])) {
            $this->calculateNextRun($jobId);
        }
        
        return $jobId;
    }
    
    /**
     * Execute a job with full integration
     */
    public function executeJob(int $jobId, bool $forceBypassCircuitBreaker = false): array
    {
        // Get job details
        $stmt = $this->db->prepare("
            SELECT * FROM smart_cron_integrated_jobs 
            WHERE id = ? AND enabled = TRUE
        ");
        $stmt->bind_param('i', $jobId);
        $stmt->execute();
        $result = $stmt->get_result();
        $job = $result->fetch_assoc();
        $stmt->close();
        
        if (!$job) {
            return [
                'success' => false,
                'error' => 'Job not found or disabled',
                'job_id' => $jobId
            ];
        }
        
        // Check if we can run (load balancing)
        if ($this->loadBalancer && !$forceBypassCircuitBreaker) {
            $canRun = $this->loadBalancer->canExecuteTask([
                'name' => $job['job_name'],
                'cpu_weight' => $job['cpu_weight'],
                'memory_weight' => $job['memory_weight']
            ]);
            
            if (!$canRun) {
                error_log("[IntegratedJobManager] ⏸️ Job '{$job['job_name']}' deferred due to load balancing");
                return [
                    'success' => false,
                    'error' => 'Load balancer deferred execution',
                    'job_id' => $jobId,
                    'deferred' => true
                ];
            }
        }
        
        // Build task array for MetricsCollector
        $task = [
            'name' => $job['job_name'],
            'script' => $job['script_path'],
            'args' => $job['script_args'],
            'type' => $this->priorityToType($job['priority']),
            'timeout' => $job['timeout_seconds']
        ];
        
        // Record execution start
        $this->db->query("
            UPDATE smart_cron_integrated_jobs 
            SET last_executed_at = NOW(), total_executions = total_executions + 1
            WHERE id = {$jobId}
        ");
        
        // Execute through MetricsCollector
        $result = $this->metrics->executeTask($task, $forceBypassCircuitBreaker);
        
        // Record execution result
        $this->recordExecution($jobId, $result);
        
        // Update next scheduled run
        $this->calculateNextRun($jobId);
        
        // Update performance baselines (every 10 executions)
        if ($job['total_executions'] % 10 === 0) {
            $this->updateBaselines($jobId);
        }
        
        return array_merge($result, ['job_id' => $jobId]);
    }
    
    /**
     * Record execution results and update job stats
     */
    private function recordExecution(int $jobId, array $result): void
    {
        $success = $result['success'] ? 1 : 0;
        
        // Update job stats
        if ($success) {
            $this->db->query("
                UPDATE smart_cron_integrated_jobs SET
                    successful_executions = successful_executions + 1,
                    consecutive_successes = consecutive_successes + 1,
                    consecutive_failures = 0,
                    last_success_at = NOW(),
                    last_exit_code = {$result['exit_code']},
                    last_error_message = NULL
                WHERE id = {$jobId}
            ");
        } else {
            $errorMsg = $this->db->real_escape_string($result['error'] ?? 'Unknown error');
            $this->db->query("
                UPDATE smart_cron_integrated_jobs SET
                    failed_executions = failed_executions + 1,
                    consecutive_failures = consecutive_failures + 1,
                    consecutive_successes = 0,
                    last_failure_at = NOW(),
                    last_exit_code = {$result['exit_code']},
                    last_error_message = '{$errorMsg}'
                WHERE id = {$jobId}
            ");
        }
        
        // Update 24h rolling stats
        $this->update24hStats($jobId);
        
        // Insert into history table (fast query cache)
        $stmt = $this->db->prepare("
            INSERT INTO smart_cron_job_history (
                job_id, executed_at, duration_seconds, memory_peak_mb, 
                cpu_peak_percent, exit_code, success
            ) VALUES (?, NOW(), ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param(
            'idddii',
            $jobId,
            $result['duration'],
            $result['memory_mb'],
            $result['cpu_peak'],
            $result['exit_code'],
            $success
        );
        $stmt->execute();
        $stmt->close();
    }
    
    /**
     * Update 24-hour rolling statistics
     */
    private function update24hStats(int $jobId): void
    {
        $this->db->query("
            UPDATE smart_cron_integrated_jobs j
            LEFT JOIN (
                SELECT 
                    job_id,
                    AVG(duration_seconds) as avg_duration,
                    MAX(duration_seconds) as max_duration,
                    AVG(memory_peak_mb) as avg_memory,
                    MAX(memory_peak_mb) as max_memory,
                    COUNT(*) as executions,
                    SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) as failures
                FROM smart_cron_job_history
                WHERE job_id = {$jobId}
                AND executed_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY job_id
            ) stats ON j.id = stats.job_id
            SET
                j.avg_duration_24h = stats.avg_duration,
                j.max_duration_24h = stats.max_duration,
                j.avg_memory_24h = stats.avg_memory,
                j.max_memory_24h = stats.max_memory,
                j.executions_24h = stats.executions,
                j.failures_24h = stats.failures
            WHERE j.id = {$jobId}
        ");
    }
    
    /**
     * Update performance baselines
     */
    private function updateBaselines(int $jobId): void
    {
        // Calculate baselines from last 100 successful executions
        $this->db->query("
            UPDATE smart_cron_integrated_jobs j
            LEFT JOIN (
                SELECT 
                    job_id,
                    AVG(duration_seconds) as avg_duration,
                    AVG(memory_peak_mb) as avg_memory,
                    AVG(cpu_peak_percent) as avg_cpu
                FROM smart_cron_job_history
                WHERE job_id = {$jobId}
                AND success = 1
                AND executed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ORDER BY executed_at DESC
                LIMIT 100
            ) baseline ON j.id = baseline.job_id
            SET
                j.baseline_duration_seconds = baseline.avg_duration,
                j.baseline_memory_mb = baseline.avg_memory,
                j.baseline_cpu_percent = baseline.avg_cpu,
                j.baseline_calculated_at = NOW()
            WHERE j.id = {$jobId}
        ");
        
        error_log("[IntegratedJobManager] 📊 Updated baselines for job ID: {$jobId}");
    }
    
    /**
     * Calculate next scheduled run based on cron expression or interval
     */
    private function calculateNextRun(int $jobId): void
    {
        $stmt = $this->db->prepare("
            SELECT schedule_type, cron_expression, interval_seconds, timezone
            FROM smart_cron_integrated_jobs WHERE id = ?
        ");
        $stmt->bind_param('i', $jobId);
        $stmt->execute();
        $result = $stmt->get_result();
        $job = $result->fetch_assoc();
        $stmt->close();
        
        if (!$job) {
            return;
        }
        
        $nextRun = null;
        
        if ($job['schedule_type'] === 'interval' && $job['interval_seconds']) {
            // Simple interval-based
            $nextRun = date('Y-m-d H:i:s', time() + $job['interval_seconds']);
        } elseif ($job['schedule_type'] === 'cron' && $job['cron_expression']) {
            // Parse cron expression (basic implementation - could use Cron-Expression library)
            $nextRun = $this->parseCronExpression($job['cron_expression'], $job['timezone']);
        }
        
        if ($nextRun) {
            $this->db->query("
                UPDATE smart_cron_integrated_jobs 
                SET next_scheduled_run = '{$nextRun}'
                WHERE id = {$jobId}
            ");
        }
    }
    
    /**
     * Basic cron expression parser (simplified - use library for production)
     */
    private function parseCronExpression(string $expression, string $timezone): ?string
    {
        // This is a simplified version - in production, use:
        // composer require dragonmantank/cron-expression
        // $cron = new \Cron\CronExpression($expression);
        // return $cron->getNextRunDate()->format('Y-m-d H:i:s');
        
        // For now, return 1 hour from now as fallback
        return date('Y-m-d H:i:s', strtotime('+1 hour'));
    }
    
    /**
     * Get jobs due for execution
     */
    public function getJobsDueForExecution(): array
    {
        $result = $this->db->query("
            SELECT * FROM smart_cron_integrated_jobs
            WHERE enabled = TRUE
            AND status = 'active'
            AND next_scheduled_run IS NOT NULL
            AND next_scheduled_run <= NOW()
            ORDER BY priority DESC, next_scheduled_run ASC
        ");
        
        $jobs = [];
        while ($row = $result->fetch_assoc()) {
            $jobs[] = $row;
        }
        
        return $jobs;
    }
    
    /**
     * Get job health summary
     */
    public function getJobHealthSummary(): array
    {
        $result = $this->db->query("SELECT * FROM smart_cron_active_jobs_summary");
        
        $jobs = [];
        while ($row = $result->fetch_assoc()) {
            $jobs[] = $row;
        }
        
        return $jobs;
    }
    
    /**
     * Get performance trends
     */
    public function getPerformanceTrends(): array
    {
        $result = $this->db->query("SELECT * FROM smart_cron_job_performance_trends");
        
        $trends = [];
        while ($row = $result->fetch_assoc()) {
            $trends[] = $row;
        }
        
        return $trends;
    }
    
    /**
     * Convert priority to task type for MetricsCollector
     */
    private function priorityToType(string $priority): string
    {
        return match($priority) {
            'critical' => 'critical',
            'high' => 'medium',
            'medium' => 'medium',
            'low' => 'light',
            default => 'medium'
        };
    }
    
    /**
     * Enable/disable a job
     */
    public function setJobEnabled(int $jobId, bool $enabled, ?string $reason = null): bool
    {
        $enabledInt = $enabled ? 1 : 0;
        $field = $enabled ? 'last_enabled_at' : 'last_disabled_at';
        $reasonField = $enabled ? '' : ", disabled_reason = ?";
        
        $sql = "UPDATE smart_cron_integrated_jobs SET enabled = ?, {$field} = NOW() {$reasonField} WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        
        if ($reason && !$enabled) {
            $stmt->bind_param('issi', $enabledInt, $reason, $jobId);
        } else {
            $stmt->bind_param('ii', $enabledInt, $jobId);
        }
        
        $result = $stmt->execute();
        $stmt->close();
        
        error_log("[IntegratedJobManager] " . ($enabled ? '✅ Enabled' : '⏸️ Disabled') . " job ID: {$jobId}" . ($reason ? " (Reason: {$reason})" : ""));
        
        return $result;
    }
    
    /**
     * Get job by name
     */
    public function getJobByName(string $jobName): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM smart_cron_integrated_jobs WHERE job_name = ?");
        $stmt->bind_param('s', $jobName);
        $stmt->execute();
        $result = $stmt->get_result();
        $job = $result->fetch_assoc();
        $stmt->close();
        
        return $job ?: null;
    }
    
    /**
     * Get recent execution history for a job
     */
    public function getJobHistory(int $jobId, int $limit = 20): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM smart_cron_job_history
            WHERE job_id = ?
            ORDER BY executed_at DESC
            LIMIT ?
        ");
        $stmt->bind_param('ii', $jobId, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        $stmt->close();
        
        return $history;
    }
}
