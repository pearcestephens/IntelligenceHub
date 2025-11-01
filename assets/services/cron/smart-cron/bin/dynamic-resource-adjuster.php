#!/usr/bin/env php
<?php
/**
 * Dynamic Resource Adjuster
 * 
 * Runs every 1 minute to dynamically adjust cron job limits
 * based on VSCode memory usage and system load
 * 
 * Usage: php dynamic-resource-adjuster.php [--verbose]
 */

declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';

use SmartCron\Core\Config;
use SmartCron\Core\DynamicResourceManager;

// Parse arguments
$verbose = in_array('--verbose', $argv);

try {
    $config = new Config();
    $manager = new DynamicResourceManager($config);
    
    // Perform dynamic adjustment
    $result = $manager->adjustLimits();
    
    if ($verbose) {
        echo "Dynamic Resource Adjustment\n";
        echo "============================\n\n";
        
        echo "Strategy: {$result['adjustments']['strategy']}\n";
        echo "Timestamp: {$result['timestamp']}\n\n";
        
        echo "System Metrics:\n";
        echo "  VSCode Memory: {$result['metrics']['vscode']['total_mb']} MB ({$result['metrics']['vscode']['process_count']} processes)\n";
        echo "  MySQL Memory: {$result['metrics']['mysql']['memory_mb']} MB\n";
        echo "  Available for Cron: {$result['metrics']['available_for_cron_mb']} MB\n";
        echo "  System Load: " . number_format($result['metrics']['system']['load_avg'], 2) . "\n";
        echo "  Free RAM: {$result['metrics']['system']['free_mb']} MB\n\n";
        
        echo "Adjusted Limits:\n";
        foreach ($result['adjustments']['slots'] as $slot => $limits) {
            echo "  {$slot}: {$limits['max_concurrent_jobs']} jobs, {$limits['max_total_memory_mb']} MB, {$limits['max_cpu_percent']}% CPU\n";
        }
        echo "\n";
    }
    
    // Output JSON for monitoring
    echo json_encode([
        'success' => true,
        'strategy' => $result['adjustments']['strategy'],
        'timestamp' => $result['timestamp']
    ]) . "\n";
    
    exit(0);
    
} catch (Exception $e) {
    if ($verbose) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]) . "\n";
    
    exit(1);
}
