#!/usr/bin/env php
<?php
/**
 * Smart Cron 2.0 - Performance Analyzer
 * 
 * Weekly analysis and optimization of job performance.
 * Runs Sunday 3 AM via Cloudways Control Panel cron.
 * 
 * Analyzes:
 * - Job execution patterns and timing
 * - Resource usage trends (memory, CPU, duration)
 * - Failure patterns and reliability metrics
 * - Schedule optimization opportunities
 * - Dead/deprecated job detection
 * 
 * Cron: 0 3 * * 0 cd /home/.../smart-cron && php bin/analyzer.php
 * 
 * @package SmartCron
 * @version 2.0.0
 */

declare(strict_types=1);

// Ensure CLI execution only
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    die("This script must be run from the command line.\n");
}

// Change to smart-cron directory
$smartCronDir = dirname(__DIR__);
chdir($smartCronDir);

// Start timing
$startTime = microtime(true);
$timestamp = date('Y-m-d H:i:s');

// Bootstrap the system
require_once $smartCronDir . '/core/Bootstrap.php';
require_once $smartCronDir . '/core/Logger.php';
require_once $smartCronDir . '/core/IntegratedJobManager.php';
require_once $smartCronDir . '/core/Config.php';

use SmartCron\Core\Logger;
use SmartCron\Core\IntegratedJobManager;
use SmartCron\Core\Config;

try {
    // Initialize logger
    $logger = new Logger($smartCronDir . '/logs/analyzer.log');
    $logger->info("=== Smart Cron 2.0 Weekly Analyzer Started ===", [
        'timestamp' => $timestamp,
        'pid' => getmypid()
    ]);
    
    // Get database connection
    $config = Config::getInstance();
    $db = $config->getDb();
    
    $jobManager = new IntegratedJobManager();
    
    // ========================================================================
    // SECTION 1: JOB INVENTORY ANALYSIS
    // ========================================================================
    $logger->info("Analyzing job inventory...");
    
    $inventoryQuery = "
        SELECT 
            status,
            enabled,
            priority,
            COUNT(*) as count,
            SUM(total_executions) as total_executions,
            SUM(successful_executions) as successful_executions,
            SUM(failed_executions) as failed_executions
        FROM smart_cron_integrated_jobs
        GROUP BY status, enabled, priority
        ORDER BY status, enabled DESC, priority
    ";
    
    $result = $db->query($inventoryQuery);
    $inventory = [];
    while ($row = $result->fetch_assoc()) {
        $inventory[] = $row;
    }
    
    $logger->info("Job Inventory:", ['breakdown' => $inventory]);
    
    // ========================================================================
    // SECTION 2: PERFORMANCE METRICS (Last 7 Days)
    // ========================================================================
    $logger->info("Analyzing performance metrics (7 days)...");
    
    $perfQuery = "
        SELECT 
            j.job_name,
            j.priority,
            COUNT(h.id) as executions_7d,
            AVG(h.duration_seconds) as avg_duration,
            MAX(h.duration_seconds) as max_duration,
            AVG(h.peak_memory_mb) as avg_memory,
            MAX(h.peak_memory_mb) as max_memory,
            SUM(CASE WHEN h.exit_code = 0 THEN 1 ELSE 0 END) as successes,
            SUM(CASE WHEN h.exit_code != 0 THEN 1 ELSE 0 END) as failures,
            ROUND(100.0 * SUM(CASE WHEN h.exit_code = 0 THEN 1 ELSE 0 END) / COUNT(h.id), 2) as success_rate
        FROM smart_cron_integrated_jobs j
        INNER JOIN smart_cron_job_history h ON j.id = h.job_id
        WHERE h.started_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            AND j.enabled = 1
        GROUP BY j.id, j.job_name, j.priority
        HAVING executions_7d > 0
        ORDER BY failures DESC, avg_duration DESC
        LIMIT 20
    ";
    
    $result = $db->query($perfQuery);
    $topJobs = [];
    while ($row = $result->fetch_assoc()) {
        $topJobs[] = $row;
    }
    
    $logger->info("Top 20 Jobs by Activity (7 days):", ['jobs' => $topJobs]);
    
    // ========================================================================
    // SECTION 3: FAILURE ANALYSIS
    // ========================================================================
    $logger->info("Analyzing failure patterns...");
    
    $failureQuery = "
        SELECT 
            j.job_name,
            j.consecutive_failures,
            j.last_failure_at,
            j.last_error_message,
            COUNT(h.id) as failures_7d
        FROM smart_cron_integrated_jobs j
        LEFT JOIN smart_cron_job_history h ON j.id = h.job_id 
            AND h.exit_code != 0 
            AND h.started_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        WHERE j.enabled = 1 
            AND (j.consecutive_failures > 0 OR j.last_failure_at >= DATE_SUB(NOW(), INTERVAL 7 DAY))
        GROUP BY j.id, j.job_name, j.consecutive_failures, j.last_failure_at, j.last_error_message
        ORDER BY j.consecutive_failures DESC, failures_7d DESC
        LIMIT 10
    ";
    
    $result = $db->query($failureQuery);
    $failingJobs = [];
    while ($row = $result->fetch_assoc()) {
        $failingJobs[] = $row;
    }
    
    if (!empty($failingJobs)) {
        $logger->warning("Jobs with Recent Failures:", ['jobs' => $failingJobs]);
    } else {
        $logger->info("No failing jobs detected - excellent!");
    }
    
    // ========================================================================
    // SECTION 4: RESOURCE USAGE ANALYSIS
    // ========================================================================
    $logger->info("Analyzing resource usage patterns...");
    
    $resourceQuery = "
        SELECT 
            j.job_name,
            j.memory_limit_mb,
            j.timeout_seconds,
            ROUND(AVG(h.peak_memory_mb), 2) as avg_memory_used,
            ROUND(MAX(h.peak_memory_mb), 2) as max_memory_used,
            ROUND(AVG(h.duration_seconds), 2) as avg_duration,
            ROUND(MAX(h.duration_seconds), 2) as max_duration,
            ROUND(100.0 * AVG(h.peak_memory_mb) / j.memory_limit_mb, 1) as memory_utilization_pct,
            ROUND(100.0 * AVG(h.duration_seconds) / j.timeout_seconds, 1) as time_utilization_pct
        FROM smart_cron_integrated_jobs j
        INNER JOIN smart_cron_job_history h ON j.id = h.job_id
        WHERE h.started_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            AND j.enabled = 1
        GROUP BY j.id, j.job_name, j.memory_limit_mb, j.timeout_seconds
        HAVING memory_utilization_pct > 80 OR time_utilization_pct > 80
        ORDER BY memory_utilization_pct DESC, time_utilization_pct DESC
    ";
    
    $result = $db->query($resourceQuery);
    $resourceHogs = [];
    while ($row = $result->fetch_assoc()) {
        $resourceHogs[] = $row;
    }
    
    if (!empty($resourceHogs)) {
        $logger->warning("Jobs approaching resource limits (>80%):", ['jobs' => $resourceHogs]);
    } else {
        $logger->info("All jobs operating within resource limits");
    }
    
    // ========================================================================
    // SECTION 5: SCHEDULE OPTIMIZATION OPPORTUNITIES
    // ========================================================================
    $logger->info("Analyzing schedule optimization opportunities...");
    
    // Find jobs that always run at the same time (could cause contention)
    $contentionQuery = "
        SELECT 
            cron_expression,
            COUNT(*) as job_count,
            GROUP_CONCAT(job_name ORDER BY priority DESC SEPARATOR ', ') as jobs
        FROM smart_cron_integrated_jobs
        WHERE enabled = 1 
            AND schedule_type = 'cron'
            AND cron_expression IS NOT NULL
        GROUP BY cron_expression
        HAVING job_count > 3
        ORDER BY job_count DESC
    ";
    
    $result = $db->query($contentionQuery);
    $contention = [];
    while ($row = $result->fetch_assoc()) {
        $contention[] = $row;
    }
    
    if (!empty($contention)) {
        $logger->warning("Schedule contention detected (>3 jobs same time):", ['schedules' => $contention]);
    }
    
    // ========================================================================
    // SECTION 6: DEAD/INACTIVE JOB DETECTION
    // ========================================================================
    $logger->info("Detecting inactive/dead jobs...");
    
    $deadJobsQuery = "
        SELECT 
            job_name,
            enabled,
            status,
            last_executed_at,
            DATEDIFF(NOW(), last_executed_at) as days_since_last_run,
            total_executions
        FROM smart_cron_integrated_jobs
        WHERE enabled = 1
            AND (
                last_executed_at IS NULL 
                OR last_executed_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
            )
        ORDER BY last_executed_at ASC NULLS FIRST
    ";
    
    $result = $db->query($deadJobsQuery);
    $deadJobs = [];
    while ($row = $result->fetch_assoc()) {
        $deadJobs[] = $row;
    }
    
    if (!empty($deadJobs)) {
        $logger->warning("Potentially dead/inactive jobs (enabled but not running):", [
            'count' => count($deadJobs),
            'jobs' => $deadJobs
        ]);
    }
    
    // ========================================================================
    // SECTION 7: UPDATE BASELINE METRICS
    // ========================================================================
    $logger->info("Updating baseline performance metrics...");
    
    $baselineQuery = "
        UPDATE smart_cron_integrated_jobs j
        INNER JOIN (
            SELECT 
                job_id,
                AVG(duration_seconds) as avg_duration,
                AVG(peak_memory_mb) as avg_memory,
                AVG(cpu_percent) as avg_cpu
            FROM smart_cron_job_history
            WHERE started_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                AND exit_code = 0
            GROUP BY job_id
            HAVING COUNT(*) >= 5
        ) h ON j.id = h.job_id
        SET 
            j.baseline_duration_seconds = h.avg_duration,
            j.baseline_memory_mb = h.avg_memory,
            j.baseline_cpu_percent = h.avg_cpu,
            j.baseline_calculated_at = NOW()
    ";
    
    $db->query($baselineQuery);
    $baselineUpdates = $db->affected_rows;
    $logger->info("Updated baseline metrics for {count} jobs", ['count' => $baselineUpdates]);
    
    // ========================================================================
    // SECTION 8: GENERATE RECOMMENDATIONS
    // ========================================================================
    $logger->info("Generating optimization recommendations...");
    
    $recommendations = [];
    
    // Recommendation 1: Resource limit adjustments
    foreach ($resourceHogs as $job) {
        if ($job['memory_utilization_pct'] > 90) {
            $recommendations[] = [
                'type' => 'increase_memory',
                'job' => $job['job_name'],
                'current' => $job['memory_limit_mb'] . 'MB',
                'recommended' => ceil($job['max_memory_used'] * 1.2) . 'MB',
                'reason' => 'Memory usage at ' . $job['memory_utilization_pct'] . '%'
            ];
        }
        
        if ($job['time_utilization_pct'] > 90) {
            $recommendations[] = [
                'type' => 'increase_timeout',
                'job' => $job['job_name'],
                'current' => $job['timeout_seconds'] . 's',
                'recommended' => ceil($job['max_duration'] * 1.2) . 's',
                'reason' => 'Time usage at ' . $job['time_utilization_pct'] . '%'
            ];
        }
    }
    
    // Recommendation 2: Disable dead jobs
    foreach ($deadJobs as $job) {
        if ($job['days_since_last_run'] > 60) {
            $recommendations[] = [
                'type' => 'disable_dead_job',
                'job' => $job['job_name'],
                'reason' => 'No executions in ' . $job['days_since_last_run'] . ' days'
            ];
        }
    }
    
    // Recommendation 3: Fix failing jobs
    foreach ($failingJobs as $job) {
        if ($job['consecutive_failures'] >= 3) {
            $recommendations[] = [
                'type' => 'fix_failing_job',
                'job' => $job['job_name'],
                'consecutive_failures' => $job['consecutive_failures'],
                'last_error' => substr($job['last_error_message'], 0, 100)
            ];
        }
    }
    
    if (!empty($recommendations)) {
        $logger->info("Optimization Recommendations:", ['recommendations' => $recommendations]);
    } else {
        $logger->info("No optimization recommendations - system running optimally!");
    }
    
    // ========================================================================
    // SECTION 9: SUMMARY REPORT
    // ========================================================================
    $summaryQuery = "
        SELECT 
            (SELECT COUNT(*) FROM smart_cron_integrated_jobs WHERE enabled = 1) as enabled_jobs,
            (SELECT COUNT(*) FROM smart_cron_integrated_jobs WHERE enabled = 0) as disabled_jobs,
            (SELECT COUNT(*) FROM smart_cron_job_history WHERE started_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as executions_7d,
            (SELECT COUNT(*) FROM smart_cron_job_history WHERE started_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND exit_code = 0) as successes_7d,
            (SELECT COUNT(*) FROM smart_cron_job_history WHERE started_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND exit_code != 0) as failures_7d,
            (SELECT AVG(duration_seconds) FROM smart_cron_job_history WHERE started_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as avg_duration_7d,
            (SELECT AVG(peak_memory_mb) FROM smart_cron_job_history WHERE started_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as avg_memory_7d
    ";
    
    $result = $db->query($summaryQuery);
    $summary = $result->fetch_assoc();
    
    $duration = round((microtime(true) - $startTime), 2);
    
    $logger->info("=== Weekly Analysis Complete ===", [
        'duration_seconds' => $duration,
        'summary' => $summary,
        'recommendations_generated' => count($recommendations)
    ]);
    
    echo "\n";
    echo "╔══════════════════════════════════════════════════════════════════════╗\n";
    echo "║          SMART CRON 2.0 - WEEKLY ANALYSIS COMPLETE                  ║\n";
    echo "╚══════════════════════════════════════════════════════════════════════╝\n";
    echo "\n";
    echo "  Enabled Jobs:        " . $summary['enabled_jobs'] . "\n";
    echo "  Disabled Jobs:       " . $summary['disabled_jobs'] . "\n";
    echo "  Executions (7d):     " . $summary['executions_7d'] . "\n";
    echo "  Success Rate:        " . round(100 * $summary['successes_7d'] / max($summary['executions_7d'], 1), 1) . "%\n";
    echo "  Avg Duration:        " . round($summary['avg_duration_7d'], 2) . "s\n";
    echo "  Avg Memory:          " . round($summary['avg_memory_7d'], 2) . "MB\n";
    echo "  Recommendations:     " . count($recommendations) . "\n";
    echo "\n";
    echo "  Analysis Time:       {$duration}s\n";
    echo "  Timestamp:           {$timestamp}\n";
    echo "\n";
    echo "  Full report: logs/analyzer.log\n";
    echo "\n";
    
    exit(0);
    
} catch (\Throwable $e) {
    if (isset($logger)) {
        $logger->error("FATAL: Analyzer crashed", [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    } else {
        error_log("[Smart Cron Analyzer] FATAL: " . $e->getMessage());
    }
    exit(1);
}
