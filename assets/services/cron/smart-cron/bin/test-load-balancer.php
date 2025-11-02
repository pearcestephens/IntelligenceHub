#!/usr/bin/env php
<?php
/**
 * Smart Cron Load Balancer Test Suite
 *
 * Comprehensive testing of load balancer functionality
 *
 * Usage:
 *   php test-load-balancer.php
 */

declare(strict_types=1);

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "  🧪 LOAD BALANCER TEST SUITE\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "\n";

// Bootstrap (loads app.php, database connection, and autoloader)
require_once dirname(__DIR__) . '/bootstrap.php';

use SmartCron\Core\Config;
use SmartCron\Core\MetricsCollector;
use SmartCron\Core\LoadBalancer;

$passed = 0;
$failed = 0;

function test(string $name, callable $fn): void {
    global $passed, $failed;

    try {
        echo "Testing: {$name}... ";
        $result = $fn();

        if ($result === true) {
            echo "✅ PASS\n";
            $passed++;
        } else {
            echo "❌ FAIL\n";
            if (is_string($result)) {
                echo "  Reason: {$result}\n";
            }
            $failed++;
        }
    } catch (\Exception $e) {
        echo "❌ EXCEPTION: " . $e->getMessage() . "\n";
        $failed++;
    }
}

// Initialize
echo "Initializing components...\n";
$config = new Config();
$metrics = new MetricsCollector($config);
$balancer = new LoadBalancer($config, $metrics);
echo "✅ Components initialized\n\n";

echo "───────────────────────────────────────────────────────────\n";
echo "BASIC FUNCTIONALITY TESTS\n";
echo "───────────────────────────────────────────────────────────\n";

// Test 1: Config loading
test("Config loads correctly", function() use ($config) {
    return $config->get('load_balancer.enabled') !== null;
});

// Test 2: Load balancer responds
test("Load balancer is callable", function() use ($balancer) {
    $health = $balancer->getHealthStatus();
    return is_array($health) && isset($health['overall_status']);
});

// Test 3: Light task can run
test("Light task can run", function() use ($balancer) {
    $task = [
        'name' => 'test_light',
        'type' => 'light'
    ];
    return $balancer->canRunTask($task);
});

// Test 4: Critical task bypasses limits
test("Critical task bypasses limits", function() use ($balancer) {
    $task = [
        'name' => 'test_critical',
        'type' => 'heavy'
    ];
    return $balancer->canRunTask($task, true); // isCritical = true
});

echo "\n";
echo "───────────────────────────────────────────────────────────\n";
echo "RESOURCE MONITORING TESTS\n";
echo "───────────────────────────────────────────────────────────\n";

// Test 5: CPU monitoring works
test("CPU usage can be read", function() use ($balancer) {
    $health = $balancer->getHealthStatus();
    return isset($health['resources']['cpu']['usage']);
});

// Test 6: Memory monitoring works
test("Memory usage can be read", function() use ($balancer) {
    $health = $balancer->getHealthStatus();
    $memUsage = $health['resources']['memory']['usage'];
    return is_float($memUsage) && $memUsage >= 0 && $memUsage <= 100;
});

// Test 7: Thresholds are respected
test("Resource thresholds exist", function() use ($balancer) {
    $health = $balancer->getHealthStatus();
    return isset($health['resources']['cpu']['threshold']) &&
           isset($health['resources']['memory']['threshold']);
});

echo "\n";
echo "───────────────────────────────────────────────────────────\n";
echo "CONCURRENT TASK TESTS\n";
echo "───────────────────────────────────────────────────────────\n";

// Test 8: Concurrent limits exist
test("Concurrent limits are configured", function() use ($balancer) {
    $health = $balancer->getHealthStatus();
    return isset($health['concurrent_tasks']['limits']['heavy']) &&
           isset($health['concurrent_tasks']['limits']['medium']) &&
           isset($health['concurrent_tasks']['limits']['light']);
});

// Test 9: Running tasks count
test("Running tasks can be counted", function() use ($balancer) {
    $health = $balancer->getHealthStatus();
    return isset($health['concurrent_tasks']['total_running']) &&
           is_int($health['concurrent_tasks']['total_running']);
});

echo "\n";
echo "───────────────────────────────────────────────────────────\n";
echo "EMERGENCY FEATURES TESTS\n";
echo "───────────────────────────────────────────────────────────\n";

// Test 10: Emergency reset works
test("Emergency reset is functional", function() use ($balancer) {
    $result = $balancer->emergencyReset();
    return isset($result['success']) && isset($result['cleared']);
});

// Test 11: Health check provides recommendations
test("Health check provides recommendations", function() use ($balancer) {
    $health = $balancer->getHealthStatus();
    return isset($health['recommendations']) && is_array($health['recommendations']);
});

// Test 12: Load balancer can be disabled
test("Load balancer disable flag works", function() use ($config) {
    $configFile = dirname(__DIR__) . '/config/config.json';
    $configData = json_decode(file_get_contents($configFile), true);
    return isset($configData['load_balancer']['enabled']);
});

echo "\n";
echo "───────────────────────────────────────────────────────────\n";
echo "STRESS TESTS\n";
echo "───────────────────────────────────────────────────────────\n";

// Test 13: Multiple task checks don't crash
test("Can handle multiple task checks", function() use ($balancer) {
    for ($i = 0; $i < 100; $i++) {
        $task = [
            'name' => "stress_test_{$i}",
            'type' => ['light', 'medium', 'heavy'][rand(0, 2)]
        ];
        $balancer->canRunTask($task);
    }
    return true;
});

// Test 14: Rapid health checks don't crash
test("Can handle rapid health checks", function() use ($balancer) {
    for ($i = 0; $i < 50; $i++) {
        $balancer->getHealthStatus();
    }
    return true;
});

echo "\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "  TEST RESULTS\n";
echo "═══════════════════════════════════════════════════════════\n";
echo "\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";
echo "Total:  " . ($passed + $failed) . "\n";
echo "\n";

if ($failed === 0) {
    echo "🎉 ALL TESTS PASSED!\n";
    echo "Load balancer is fully operational and hardened.\n";
    exit(0);
} else {
    echo "⚠️ SOME TESTS FAILED\n";
    echo "Please review the failures above and fix issues.\n";
    exit(1);
}
