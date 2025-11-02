#!/usr/bin/env php
<?php
/**
 * Smart Cron System - Main Executable
 *
 * Single entry point for intelligent, self-optimizing cron management.
 *
 * Usage:
 *   php smart-cron.php                    # Run scheduled tasks
 *   php smart-cron.php --analyze          # Analyze metrics and optimize
 *   php smart-cron.php --status           # Show system status
 *   php smart-cron.php --dashboard        # Open web dashboard
 *   php smart-cron.php --task=name        # Run specific task manually
 *
 * Installation:
 *   Add to crontab: * * * * * php /path/to/smart-cron.php >> /path/to/logs/cron.log 2>&1
 *
 * @package SmartCron
 * @version 1.0.0
 * @author CIS Development Team
 */

declare(strict_types=1);

// ============================================================================
// SAFETY LIMITS - Prevent runaway cron jobs from consuming all system memory
// ============================================================================
// PHP CLI defaults to unlimited memory (-1) and unlimited execution (0).
// This is dangerous for cron jobs that could leak memory or hang indefinitely.
// We set reasonable limits to protect the system while allowing normal operations.
// ============================================================================

ini_set('memory_limit', '512M');        // Max 512 MB per cron execution
ini_set('max_execution_time', '300');   // Max 5 minutes execution time

// Log the limits for debugging (can be removed after verification)
error_log(sprintf(
    '[SMART-CRON] Safety limits enforced: memory_limit=%s, max_execution_time=%s',
    ini_get('memory_limit'),
    ini_get('max_execution_time')
));

// Bootstrap
define('SMART_CRON_ROOT', __DIR__ . '/smart-cron');
define('SMART_CRON_START', microtime(true));
define('SMART_CRON_VERSION', '1.0.0');

// Load bootstrap (handles database, app.php, and environment + autoloader)
require_once SMART_CRON_ROOT . '/bootstrap.php';

// Timezone already set by app.php
// Database already connected via getMysqliConnection()
// Autoloader loaded by bootstrap.php

use SmartCron\Core\Config;
use SmartCron\Core\CircuitBreaker;
use SmartCron\Core\AlertManager;
use SmartCron\Core\MetricsCollector;
use SmartCron\Core\TaskAnalyzer;
use SmartCron\Core\ScheduleOptimizer;
use SmartCron\Core\LoadBalancer;

// Parse CLI arguments
$options = getopt('', [
    'analyze',
    'status',
    'dashboard',
    'task:',
    'force',
    'help'
]);

// Show help
if (isset($options['help'])) {
    showHelp();
    exit(0);
}

// Initialize system
$config = new Config();
$circuitBreaker = new CircuitBreaker($config);
$alertManager = new AlertManager($config);
$metrics = new MetricsCollector($config, $circuitBreaker, $alertManager);
$analyzer = new TaskAnalyzer($config, $metrics);
$optimizer = new ScheduleOptimizer($config, $analyzer);
$balancer = new LoadBalancer($config, $metrics);

// Route command
try {
    if (isset($options['analyze'])) {
        handleAnalyze($analyzer, $optimizer);
    } elseif (isset($options['status'])) {
        handleStatus($metrics, $analyzer);
    } elseif (isset($options['dashboard'])) {
        handleDashboard($config);
    } elseif (isset($options['task'])) {
        // Pass force flag to manual task handler
        $forceExecution = isset($options['force']);
        handleManualTask($options['task'], $metrics, $balancer, $forceExecution);
    } else {
        handleScheduledTasks($optimizer, $balancer, $metrics);
    }

    exit(0);

} catch (\Exception $e) {
    error_log("Smart Cron Error: " . $e->getMessage());
    exit(1);
}

// ============================================================================
// COMMAND HANDLERS
// ============================================================================

/**
 * Handle scheduled task execution (default mode)
 */
function handleScheduledTasks(ScheduleOptimizer $optimizer, LoadBalancer $balancer, MetricsCollector $metrics): void
{
    $currentMinute = (int)date('i');
    $currentHour = (int)date('H');

    echo "=== Smart Cron: " . date('Y-m-d H:i:s') . " ===\n";
    echo "Current minute: {$currentMinute}, Current hour: {$currentHour}\n";

    // Get tasks scheduled for this minute
    $tasks = $optimizer->getTasksForMinute($currentMinute, $currentHour);

    echo "Tasks found for this minute: " . count($tasks) . "\n";

    if (empty($tasks)) {
        echo "No tasks scheduled for this minute.\n";
        echo "Run 'php smart-cron.php --analyze' to generate schedule.\n";
        return;
    }

    echo "Tasks scheduled: " . count($tasks) . "\n\n";

    foreach ($tasks as $task) {
        // 🔒 CRITICAL: Check if task is explicitly disabled
        if (isset($task['enabled']) && $task['enabled'] === false) {
            echo "🔒 DISABLED: {$task['name']} (task is disabled in config)\n";
            $metrics->recordSkip($task['name'], 'disabled');
            continue;
        }

        // Check if we can run this task (load balancing)
        if (!$balancer->canRunTask($task)) {
            echo "⏸️  SKIPPED: {$task['name']} (load balancer: too much concurrent load)\n";
            $metrics->recordSkip($task['name'], 'load_balancer');
            continue;
        }

        // Run task with metrics collection
        echo "▶️  RUNNING: {$task['name']}\n";
        $result = $metrics->executeTask($task);

        if ($result['success']) {
            echo "✅ SUCCESS: {$task['name']} ({$result['duration']}s, {$result['memory_mb']}MB)\n";
        } else {
            echo "❌ FAILED: {$task['name']} - {$result['error']}\n";
        }

        echo "\n";
    }

    $totalDuration = round(microtime(true) - SMART_CRON_START, 2);
    echo "Completed in {$totalDuration}s\n";
}

/**
 * Handle analysis and optimization
 */
function handleAnalyze(TaskAnalyzer $analyzer, ScheduleOptimizer $optimizer): void
{
    echo "=== Smart Cron Analysis ===\n\n";

    // Analyze task metrics
    echo "📊 Analyzing task metrics...\n";
    $analysis = $analyzer->analyzeAll();

    echo "\nTask Classification:\n";
    echo "  Heavy tasks: " . count($analysis['heavy']) . "\n";
    echo "  Medium tasks: " . count($analysis['medium']) . "\n";
    echo "  Light tasks: " . count($analysis['light']) . "\n";

    // Generate optimized schedule
    echo "\n🔧 Generating optimized schedule...\n";
    $schedule = $optimizer->generateSchedule($analysis);

    // Save schedule
    $optimizer->saveSchedule($schedule);

    // Count tasks in schedule
    $totalTasks = 0;
    foreach ($schedule as $minute => $tasks) {
        $totalTasks += count($tasks);
    }

    echo "\nOptimization Results:\n";
    echo "  Total tasks scheduled: {$totalTasks}\n";
    echo "  Heavy tasks: " . count($analysis['heavy']) . " (will run 2-4 AM after metrics collected)\n";
    echo "  Medium tasks: " . count($analysis['medium']) . " (off-peak hours)\n";
    echo "  Light tasks: " . count($analysis['light']) . " (distributed across all minutes)\n";
    echo "  Schedule saved to: smart-cron/config/schedule.json\n";

    echo "\n✅ Analysis complete. New schedule activated.\n";
}

/**
 * Handle status display
 */
function handleStatus(MetricsCollector $metrics, TaskAnalyzer $analyzer): void
{
    echo "=== Smart Cron Status ===\n\n";

    $status = $metrics->getSystemStatus();

    echo "System Health: " . ($status['healthy'] ? '✅ HEALTHY' : '⚠️  WARNING') . "\n";
    echo "Total Tasks: {$status['total_tasks']}\n";
    echo "Last Run: " . date('Y-m-d H:i:s', $status['last_run']) . "\n";
    echo "\nLast 24 Hours:\n";
    echo "  Executions: {$status['executions_24h']}\n";
    echo "  Successes: {$status['successes_24h']}\n";
    echo "  Failures: {$status['failures_24h']}\n";
    echo "  Avg Duration: {$status['avg_duration_24h']}s\n";

    // Top 5 heaviest tasks
    echo "\n🐘 Top 5 Heaviest Tasks:\n";
    $heavy = $analyzer->getHeaviestTasks(5);
    foreach ($heavy as $i => $task) {
        $num = $i + 1;
        echo "  {$num}. {$task['name']} - {$task['avg_duration']}s, {$task['avg_memory_mb']}MB\n";
    }

    // Recent failures
    $failures = $metrics->getRecentFailures(5);
    if (!empty($failures)) {
        echo "\n❌ Recent Failures:\n";
        foreach ($failures as $failure) {
            echo "  {$failure['task_name']} - " . date('H:i:s', strtotime($failure['executed_at'])) . " - {$failure['error']}\n";
        }
    }
}

/**
 * Handle manual task execution
 */
function handleManualTask(string $taskName, MetricsCollector $metrics, LoadBalancer $balancer, bool $force = false): void
{
    echo "=== Manual Task Execution ===\n\n";
    echo "Task: {$taskName}\n";

    if ($force) {
        echo "⚡ Force mode: ON (bypassing cooldowns and circuit breakers)\n";
    }

    // Find task configuration
    $task = $balancer->getTaskConfig($taskName);

    if (!$task) {
        echo "❌ ERROR: Task '{$taskName}' not found\n";
        exit(1);
    }

    echo "Script: {$task['script']}\n";
    echo "\n▶️  Starting execution...\n\n";

    // Execute with metrics
    // If force mode, bypass cooldowns and circuit breakers
    if ($force) {
        // Set a flag in the task config to bypass checks
        $task['_force_execution'] = true;
    }

    $result = $metrics->executeTask($task);

    if ($result['success']) {
        echo "\n✅ SUCCESS\n";
        echo "Duration: {$result['duration']}s\n";
        echo "Memory: {$result['memory_mb']}MB\n";
        echo "CPU Peak: {$result['cpu_peak']}%\n";
    } else {
        echo "\n❌ FAILED\n";
        echo "Error: {$result['error']}\n";
        exit(1);
    }
}

/**
 * Handle dashboard opening
 */
function handleDashboard(Config $config): void
{
    $dashboardUrl = $config->get('dashboard_url', 'http://localhost/assets/cron/dashboard/');

    echo "=== Smart Cron Dashboard ===\n\n";
    echo "Opening dashboard at: {$dashboardUrl}\n";

    // Try to open browser
    if (PHP_OS_FAMILY === 'Darwin') {
        exec("open {$dashboardUrl}");
    } elseif (PHP_OS_FAMILY === 'Windows') {
        exec("start {$dashboardUrl}");
    } else {
        exec("xdg-open {$dashboardUrl} 2>/dev/null &");
    }

    echo "\nIf browser didn't open, visit: {$dashboardUrl}\n";
}

/**
 * Show help
 */
function showHelp(): void
{
    echo <<<HELP
Smart Cron System - Intelligent Task Scheduler

Usage:
  php smart-cron.php [OPTIONS]

Options:
  (no options)         Run scheduled tasks for current minute (default)
  --analyze            Analyze metrics and regenerate optimized schedule
  --status             Show system status and recent activity
  --dashboard          Open web dashboard in browser
  --task=NAME          Run specific task manually (with metrics)
  --force              Force execution even if load balancer says no
  --help               Show this help message

Examples:
  # Normal cron execution (add to crontab):
  * * * * * php /path/to/smart-cron.php >> /var/log/cron.log 2>&1

  # Weekly optimization (add to crontab):
  0 3 * * 0 php /path/to/smart-cron.php --analyze

  # Check system status:
  php smart-cron.php --status

  # Run specific task manually:
  php smart-cron.php --task=queue_reap_stale

Directory Structure:
  /assets/cron/
  ├── smart-cron.php              # This file (entry point)
  ├── scripts/                     # Your task scripts
  │   ├── queue-reap-stale.php
  │   ├── xero-sync.php
  │   └── ... (all your tasks)
  └── core/                        # Smart Cron engine
      ├── Config.php
      ├── MetricsCollector.php
      ├── TaskAnalyzer.php
      ├── ScheduleOptimizer.php
      └── LoadBalancer.php

Setup:
  1. Place all your task scripts in scripts/ directory
  2. Configure database connection in core/Config.php
  3. Run: php smart-cron.php --analyze (first time setup)
  4. Add to crontab: * * * * * php /path/to/smart-cron.php

Documentation:
  See README.md in this directory for full documentation.

HELP;
}
