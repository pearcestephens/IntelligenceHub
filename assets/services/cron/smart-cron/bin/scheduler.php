#!/usr/bin/env php
<?php
/**
 * Smart Cron 2.0 - Integrated Job Scheduler
 * 
 * Main entry point for the Smart Cron scheduler system.
 * Runs every minute via Cloudways Control Panel cron.
 * 
 * Cron: * * * * * cd /home/.../smart-cron && php bin/scheduler.php
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

// Database connection (direct)
$db = new \mysqli('127.0.0.1', 'jcepnzzkmj', 'wprKh9Jq63', 'jcepnzzkmj');

if ($db->connect_error) {
    error_log("[Scheduler] Database connection failed: " . $db->connect_error);
    exit(1);
}

$db->set_charset('utf8mb4');
date_default_timezone_set('Pacific/Auckland');

try {
    $timestamp = date('Y-m-d H:i:s');
    
    // Simple query to get due jobs
    $query = "
        SELECT id, job_name, script_path, script_type, script_args, working_directory,
               priority, timeout_seconds, memory_limit_mb
        FROM smart_cron_integrated_jobs
        WHERE enabled = 1
            AND status = 'active'
            AND (next_scheduled_run IS NULL OR next_scheduled_run <= NOW())
        ORDER BY 
            FIELD(priority, 'critical', 'high', 'medium', 'low'),
            next_scheduled_run ASC
        LIMIT 10
    ";
    
    $result = $db->query($query);
    
    if (!$result) {
        error_log("[Scheduler] Query failed: " . $db->error);
        exit(1);
    }
    
    $dueJobs = [];
    while ($row = $result->fetch_assoc()) {
        $dueJobs[] = $row;
    }
    
    echo "{$timestamp} - Found " . count($dueJobs) . " jobs due for execution\n";
    
    if (empty($dueJobs)) {
        echo "No jobs due - scheduler idle\n";
        exit(0);
    }
    
    // Execute each job
    foreach ($dueJobs as $job) {
        echo "  → Running: {$job['job_name']} (priority: {$job['priority']})\n";
        
        $jobStartTime = microtime(true);
        $jobStarted = date('Y-m-d H:i:s');
        
        // Build command
        $scriptPath = $job['script_path'];
        $workingDir = $job['working_directory'] ?: dirname($scriptPath);
        
        // Prepare command based on script type
        $cmd = '';
        if ($job['script_type'] === 'php') {
            $cmd = "php " . escapeshellarg($scriptPath);
        } elseif ($job['script_type'] === 'bash') {
            $cmd = "bash " . escapeshellarg($scriptPath);
        } else {
            $cmd = escapeshellarg($scriptPath);
        }
        
        // Add arguments if present
        if (!empty($job['script_args'])) {
            $cmd .= " " . $job['script_args'];
        }
        
        // Change to working directory and execute
        $output = [];
        $exitCode = 0;
        
        $fullCmd = sprintf(
            'cd %s && %s 2>&1',
            escapeshellarg($workingDir),
            $cmd
        );
        
        exec($fullCmd, $output, $exitCode);
        
        $jobEndTime = microtime(true);
        $duration = round($jobEndTime - $jobStartTime, 2);
        $jobFinished = date('Y-m-d H:i:s');
        
        $outputText = implode("\n", $output);
        $success = ($exitCode === 0);
        
        echo "    " . ($success ? "✓" : "✗") . " Completed in {$duration}s (exit code: {$exitCode})\n";
        
        // Insert into job history (match actual table structure)
        $historyQuery = "
            INSERT INTO smart_cron_job_history 
            (job_id, executed_at, duration_seconds, memory_peak_mb, exit_code, success)
            VALUES (?, ?, ?, NULL, ?, ?)
        ";
        $stmt = $db->prepare($historyQuery);
        $stmt->bind_param('isdii', 
            $job['id'], 
            $jobFinished, 
            $duration, 
            $exitCode, 
            $success
        );
        $stmt->execute();
        
        // Update job record
        $updateQuery = "
            UPDATE smart_cron_integrated_jobs 
            SET 
                last_executed_at = ?,
                last_exit_code = ?,
                total_executions = total_executions + 1,
                successful_executions = successful_executions + ?,
                failed_executions = failed_executions + ?,
                consecutive_failures = IF(? = 0, 0, consecutive_failures + 1),
                last_failure_at = IF(? != 0, NOW(), last_failure_at),
                last_error_message = IF(? != 0, LEFT(?, 500), last_error_message),
                next_scheduled_run = DATE_ADD(NOW(), INTERVAL 15 MINUTE)
            WHERE id = ?
        ";
        
        $successInt = $success ? 1 : 0;
        $failInt = $success ? 0 : 1;
        
        $stmt = $db->prepare($updateQuery);
        $stmt->bind_param('siiiiiisi',
            $jobFinished,
            $exitCode,
            $successInt,
            $failInt,
            $exitCode,
            $exitCode,
            $exitCode,
            $outputText,
            $job['id']
        );
        $stmt->execute();
    }
    
    $totalDuration = round(microtime(true) - $startTime, 2);
    echo "Scheduler completed in {$totalDuration}s\n";
    exit(0);
    
} catch (\Throwable $e) {
    error_log("[Scheduler] FATAL: " . $e->getMessage());
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
