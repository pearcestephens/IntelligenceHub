#!/usr/bin/env php
<?php
/**
 * Smart Cron Load Balancer Health Check
 *
 * Diagnoses load balancer issues and provides recommendations
 *
 * Usage:
 *   php health-check.php              # Show current health status
 *   php health-check.php --fix        # Attempt to fix issues
 *   php health-check.php --reset      # Emergency reset (clear all locks)
 *   php health-check.php --disable    # Disable load balancer
 *   php health-check.php --enable     # Enable load balancer
 */

declare(strict_types=1);

// Bootstrap (loads app.php, database connection, and autoloader)
require_once dirname(__DIR__) . '/bootstrap.php';

use SmartCron\Core\Config;
use SmartCron\Core\MetricsCollector;
use SmartCron\Core\LoadBalancer;

// Parse arguments
$options = getopt('', ['fix', 'reset', 'disable', 'enable', 'json']);

try {
    // Initialize
    $config = new Config();
    $metrics = new MetricsCollector($config);
    $balancer = new LoadBalancer($config, $metrics);

    // Handle commands
    if (isset($options['reset'])) {
        echo "🚨 EMERGENCY RESET - Clearing all lock files...\n";
        $result = $balancer->emergencyReset();

        if ($result['success']) {
            echo "✅ Successfully cleared {$result['cleared']} lock files\n";
        } else {
            echo "⚠️ Cleared {$result['cleared']} files with errors:\n";
            foreach ($result['errors'] as $error) {
                echo "  ❌ {$error}\n";
            }
        }
        exit(0);
    }

    if (isset($options['disable'])) {
        echo "⚠️ Disabling load balancer...\n";
        $configFile = dirname(__DIR__) . '/config/config.json';
        $configData = json_decode(file_get_contents($configFile), true);
        $configData['load_balancer']['enabled'] = false;
        file_put_contents($configFile, json_encode($configData, JSON_PRETTY_PRINT));
        echo "✅ Load balancer disabled\n";
        echo "⚠️ WARNING: Tasks will run without resource checking!\n";
        exit(0);
    }

    if (isset($options['enable'])) {
        echo "✅ Enabling load balancer...\n";
        $configFile = dirname(__DIR__) . '/config/config.json';
        $configData = json_decode(file_get_contents($configFile), true);
        $configData['load_balancer']['enabled'] = true;
        file_put_contents($configFile, json_encode($configData, JSON_PRETTY_PRINT));
        echo "✅ Load balancer enabled\n";
        exit(0);
    }

    // Get health status
    $health = $balancer->getHealthStatus();

    // Output format
    if (isset($options['json'])) {
        echo json_encode($health, JSON_PRETTY_PRINT) . "\n";
        exit($health['overall_status'] === 'healthy' ? 0 : 1);
    }

    // Human-readable output
    echo "\n";
    echo "═══════════════════════════════════════════════════════════\n";
    echo "  🔧 SMART CRON LOAD BALANCER HEALTH CHECK\n";
    echo "═══════════════════════════════════════════════════════════\n";
    echo "\n";

    // Overall status
    $statusEmoji = match($health['overall_status']) {
        'healthy' => '✅',
        'warning' => '⚠️',
        'critical' => '🔴',
        default => '❓'
    };
    echo "Overall Status: {$statusEmoji} " . strtoupper($health['overall_status']) . "\n";
    echo "Timestamp: {$health['timestamp']}\n";
    echo "Load Balancer: " . (($health['enabled'] ?? false) ? '✅ ENABLED' : '❌ DISABLED') . "\n";
    echo "\n";

    // Resources
    echo "───────────────────────────────────────────────────────────\n";
    echo "📊 SYSTEM RESOURCES\n";
    echo "───────────────────────────────────────────────────────────\n";

    $cpuStatus = match($health['resources']['cpu']['status'] ?? 'unknown') {
        'ok' => '✅',
        'warning' => '⚠️',
        'critical' => '🔴',
        default => '❓'
    };
    $cpuUsage = $health['resources']['cpu']['usage'] ?? 'N/A';
    $cpuThreshold = $health['resources']['cpu']['threshold'] ?? 'N/A';
    echo "CPU Usage:    {$cpuStatus} {$cpuUsage}% (threshold: {$cpuThreshold}%)\n";

    $memStatus = match($health['resources']['memory']['status'] ?? 'unknown') {
        'ok' => '✅',
        'warning' => '⚠️',
        'critical' => '🔴',
        default => '❓'
    };
    $memUsage = $health['resources']['memory']['usage'] ?? 'N/A';
    $memThreshold = $health['resources']['memory']['threshold'] ?? 'N/A';
    echo "Memory Usage: {$memStatus} {$memUsage}% (threshold: {$memThreshold}%)\n";
    echo "\n";

    // Concurrent tasks
    echo "───────────────────────────────────────────────────────────\n";
    echo "🔄 CONCURRENT TASKS\n";
    echo "───────────────────────────────────────────────────────────\n";
    $totalRunning = $health['concurrent_tasks']['total_running'] ?? 0;
    echo "Total Running: {$totalRunning}\n";
    echo "\n";
    echo "By Type:\n";
    $byType = $health['concurrent_tasks']['by_type'] ?? [];
    foreach ($byType as $type => $count) {
        $limit = $health['concurrent_tasks']['limits'][$type] ?? '?';
        $icon = $count > 0 ? '▶️' : '⏸️';
        echo "  {$icon} {$type}: {$count}/{$limit}\n";
    }
    echo "\n";

    // Recommendations
    if (!empty($health['recommendations'])) {
        echo "───────────────────────────────────────────────────────────\n";
        echo "💡 RECOMMENDATIONS\n";
        echo "───────────────────────────────────────────────────────────\n";
        foreach ($health['recommendations'] as $recommendation) {
            echo "  • {$recommendation}\n";
        }
        echo "\n";
    }

    // Fix suggestions
    if (isset($options['fix'])) {
        echo "───────────────────────────────────────────────────────────\n";
        echo "🔧 ATTEMPTING FIXES\n";
        echo "───────────────────────────────────────────────────────────\n";

        $fixed = false;

        // Check for stale locks
        if ($health['concurrent_tasks']['total_running'] > 10) {
            echo "Found {$health['concurrent_tasks']['total_running']} running tasks - checking for stale locks...\n";
            $result = $balancer->emergencyReset();
            if ($result['cleared'] > 0) {
                echo "✅ Cleared {$result['cleared']} stale lock files\n";
                $fixed = true;
            }
        }

        // Check if load balancer should be disabled temporarily
        if ($health['resources']['cpu']['status'] === 'critical' ||
            $health['resources']['memory']['status'] === 'critical') {
            echo "⚠️ System resources critically high\n";
            echo "Consider: php health-check.php --disable (temporarily disable load balancer)\n";
        }

        if (!$fixed) {
            echo "ℹ️ No automatic fixes available\n";
            echo "Run with --reset to force clear all locks\n";
        }
        echo "\n";
    }

    echo "═══════════════════════════════════════════════════════════\n";
    echo "\n";

    // Exit with appropriate code
    exit($health['overall_status'] === 'healthy' ? 0 : 1);

} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
