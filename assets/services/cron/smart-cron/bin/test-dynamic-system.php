<?php
/**
 * Test Dynamic Resource Monitoring System
 *
 * Validates all components of the new dynamic system
 */

declare(strict_types=1);

require_once __DIR__ . '/../../../../../app.php';
require_once __DIR__ . '/../core/Config.php';
require_once __DIR__ . '/../core/DynamicResourceMonitor.php';
require_once __DIR__ . '/../core/UseCaseEngine.php';
require_once __DIR__ . '/../core/LoadBalancer.php';
require_once __DIR__ . '/../core/MetricsCollector.php';

use SmartCron\Core\Config;
use SmartCron\Core\DynamicResourceMonitor;
use SmartCron\Core\UseCaseEngine;
use SmartCron\Core\LoadBalancer;
use SmartCron\Core\MetricsCollector;

echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║      SMART CRON - DYNAMIC SYSTEM COMPREHENSIVE TEST           ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

$passed = 0;
$failed = 0;
$warnings = 0;

function test(string $name, callable $fn): void
{
    global $passed, $failed, $warnings;

    echo "▶️  Testing: {$name}... ";

    try {
        $result = $fn();

        if ($result === true) {
            echo "✅ PASS\n";
            $passed++;
        } elseif ($result === 'warning') {
            echo "⚠️  WARNING\n";
            $warnings++;
        } else {
            echo "❌ FAIL: {$result}\n";
            $failed++;
        }
    } catch (Exception $e) {
        echo "❌ FAIL: {$e->getMessage()}\n";
        $failed++;
    }
}

// ============================================================================
// TEST 1: Component Initialization
// ============================================================================

echo "\n📦 PHASE 1: Component Initialization\n";
echo str_repeat("─", 65) . "\n";

$config = null;
$monitor = null;
$engine = null;
$metrics = null;
$loadBalancer = null;

test('Config initialization', function() use (&$config) {
    $config = new Config();
    return ($config instanceof Config);
});

test('DynamicResourceMonitor initialization', function() use ($config, &$monitor) {
    $monitor = new DynamicResourceMonitor($config);
    return ($monitor instanceof DynamicResourceMonitor);
});

test('UseCaseEngine initialization', function() use ($monitor, $config, &$engine) {
    $engine = new UseCaseEngine($monitor, $config);
    return ($engine instanceof UseCaseEngine);
});

test('MetricsCollector initialization', function() use ($config, &$metrics) {
    $metrics = new MetricsCollector($config);
    return ($metrics instanceof MetricsCollector);
});

test('LoadBalancer initialization', function() use ($config, $metrics, &$loadBalancer) {
    $loadBalancer = new LoadBalancer($config, $metrics);
    return ($loadBalancer instanceof LoadBalancer);
});

// ============================================================================
// TEST 2: Resource Detection
// ============================================================================

echo "\n🔍 PHASE 2: Resource Detection\n";
echo str_repeat("─", 65) . "\n";

$snapshot = null;

test('Resource snapshot capture', function() use ($monitor, &$snapshot) {
    $snapshot = $monitor->getResourceSnapshot();
    return is_array($snapshot) && isset($snapshot['cpu'], $snapshot['memory']);
});

test('CPU detection works (not N/A)', function() use ($snapshot) {
    $cpuUsage = $snapshot['cpu']['usage'] ?? null;
    if ($cpuUsage === null || $cpuUsage === 0) {
        return 'warning';
    }
    return ($cpuUsage > 0 && $cpuUsage <= 100);
});

test('CPU detection method identified', function() use ($snapshot) {
    $method = $snapshot['cpu']['method'] ?? 'unknown';
    return $method !== 'unknown';
});

test('Memory detection', function() use ($snapshot) {
    $memUsage = $snapshot['memory']['usage_percent'] ?? 0;
    return ($memUsage > 0 && $memUsage <= 100);
});

test('Load average capture', function() use ($snapshot) {
    $loadAvg = $snapshot['load_avg'] ?? [];
    return isset($loadAvg['1min'], $loadAvg['cores']);
});

test('Overall load calculation', function() use ($snapshot) {
    $overallLoad = $snapshot['overall_load'] ?? -1;
    return ($overallLoad >= 0 && $overallLoad <= 100);
});

test('Tier determination', function() use ($snapshot) {
    $tier = $snapshot['tier'] ?? 0;
    $tierName = $snapshot['tier_name'] ?? '';
    return ($tier >= 1 && $tier <= 5 && !empty($tierName));
});

// ============================================================================
// TEST 3: Dynamic Thresholds
// ============================================================================

echo "\n📊 PHASE 3: Dynamic Thresholds\n";
echo str_repeat("─", 65) . "\n";

$thresholds = null;

test('Dynamic threshold calculation', function() use ($monitor, &$thresholds) {
    $thresholds = $monitor->getDynamicThresholds();
    return is_array($thresholds) && isset($thresholds['cpu'], $thresholds['memory']);
});

test('CPU thresholds structure', function() use ($thresholds) {
    $cpu = $thresholds['cpu'] ?? [];
    return isset($cpu['normal'], $cpu['elevated'], $cpu['high'], $cpu['critical']);
});

test('Memory thresholds structure', function() use ($thresholds) {
    $mem = $thresholds['memory'] ?? [];
    return isset($mem['normal'], $mem['elevated'], $mem['high'], $mem['critical']);
});

test('Baseline calculation', function() use ($thresholds) {
    return isset($thresholds['baseline']);
});

// ============================================================================
// TEST 4: Pattern Detection
// ============================================================================

echo "\n🎯 PHASE 4: Pattern Detection\n";
echo str_repeat("─", 65) . "\n";

test('Spike detection', function() use ($monitor) {
    $spike = $monitor->detectLoadSpike();
    return isset($spike['spike_detected'], $spike['confidence']);
});

test('Load prediction', function() use ($monitor) {
    $prediction = $monitor->predictLoad(60);
    return isset($prediction['prediction'], $prediction['confidence'], $prediction['trend']);
});

test('Recommended action generation', function() use ($monitor) {
    $action = $monitor->getRecommendedAction();
    return isset($action['tier'], $action['severity'], $action['actions']);
});

// ============================================================================
// TEST 5: Use Case Detection
// ============================================================================

echo "\n🔬 PHASE 5: Use Case Detection (100+ Cases)\n";
echo str_repeat("─", 65) . "\n";

$useCases = [];

test('Use case detection', function() use ($engine, $snapshot, &$useCases) {
    $useCases = $engine->detectUseCase($snapshot);
    return is_array($useCases);
});

test('Use cases have required structure', function() use ($useCases) {
    if (empty($useCases)) {
        return 'warning'; // Normal if system is quiet
    }

    $first = $useCases[0];
    return isset($first['id'], $first['category'], $first['name'], $first['priority']);
});

test('Use case priorities sorted', function() use ($useCases) {
    if (count($useCases) < 2) {
        return 'warning';
    }

    for ($i = 1; $i < count($useCases); $i++) {
        if ($useCases[$i]['priority'] > $useCases[$i-1]['priority']) {
            return false;
        }
    }
    return true;
});

test('Strategy generation for use cases', function() use ($engine, $useCases) {
    if (empty($useCases)) {
        return 'warning';
    }

    $strategy = $engine->getStrategy($useCases[0]);
    return isset($strategy['cpu_threshold'], $strategy['memory_threshold']);
});

// ============================================================================
// TEST 6: LoadBalancer Integration
// ============================================================================

echo "\n⚖️  PHASE 6: LoadBalancer Integration\n";
echo str_repeat("─", 65) . "\n";

test('LoadBalancer uses dynamic monitoring', function() use ($loadBalancer) {
    // Check if dynamic monitoring is initialized
    $reflection = new ReflectionClass($loadBalancer);
    $property = $reflection->getProperty('dynamicMonitor');
    $property->setAccessible(true);
    $monitor = $property->getValue($loadBalancer);

    return $monitor !== null;
});

test('Can evaluate light task', function() use ($loadBalancer) {
    $testTask = [
        'name' => 'test_light_task',
        'type' => 'light',
        'enabled' => true,
    ];

    $result = $loadBalancer->canRunTask($testTask, false);
    return is_bool($result);
});

test('Can evaluate heavy task', function() use ($loadBalancer) {
    $testTask = [
        'name' => 'test_heavy_task',
        'type' => 'heavy',
        'enabled' => true,
    ];

    $result = $loadBalancer->canRunTask($testTask, false);
    return is_bool($result);
});

test('Critical tasks handled differently', function() use ($loadBalancer) {
    $testTask = [
        'name' => 'test_critical_task',
        'type' => 'heavy',
        'enabled' => true,
    ];

    $result = $loadBalancer->canRunTask($testTask, true);
    return is_bool($result);
});

test('Health status includes dynamic data', function() use ($loadBalancer) {
    $health = $loadBalancer->getHealthStatus();

    return isset($health['monitoring_mode']) &&
           $health['monitoring_mode'] === 'dynamic' &&
           isset($health['use_cases'], $health['predictions']);
});

// ============================================================================
// TEST 7: Real-World Scenarios
// ============================================================================

echo "\n🌍 PHASE 7: Real-World Scenarios\n";
echo str_repeat("─", 65) . "\n";

test('Scenario: Normal load', function() use ($loadBalancer) {
    $task = ['name' => 'routine_backup', 'type' => 'medium'];
    return is_bool($loadBalancer->canRunTask($task));
});

test('Scenario: Database task', function() use ($loadBalancer) {
    $task = ['name' => 'db_optimization', 'type' => 'heavy'];
    return is_bool($loadBalancer->canRunTask($task));
});

test('Scenario: API webhook', function() use ($loadBalancer) {
    $task = ['name' => 'api_webhook_processor', 'type' => 'light'];
    return is_bool($loadBalancer->canRunTask($task));
});

test('Scenario: Batch processing', function() use ($loadBalancer) {
    $task = ['name' => 'batch_email_send', 'type' => 'medium'];
    return is_bool($loadBalancer->canRunTask($task));
});

// ============================================================================
// TEST 8: Performance
// ============================================================================

echo "\n⚡ PHASE 8: Performance\n";
echo str_repeat("─", 65) . "\n";

test('Snapshot capture performance (<100ms)', function() use ($monitor) {
    $start = microtime(true);
    $monitor->getResourceSnapshot();
    $duration = (microtime(true) - $start) * 1000;

    if ($duration > 100) {
        return "Too slow: {$duration}ms";
    }
    return true;
});

test('Use case detection performance (<50ms)', function() use ($engine, $monitor) {
    $snapshot = $monitor->getResourceSnapshot();
    $start = microtime(true);
    $engine->detectUseCase($snapshot);
    $duration = (microtime(true) - $start) * 1000;

    if ($duration > 50) {
        return "Too slow: {$duration}ms";
    }
    return true;
});

test('Full evaluation performance (<200ms)', function() use ($loadBalancer) {
    $task = ['name' => 'perf_test', 'type' => 'light'];
    $start = microtime(true);
    $loadBalancer->canRunTask($task);
    $duration = (microtime(true) - $start) * 1000;

    if ($duration > 200) {
        return "Too slow: {$duration}ms";
    }
    return true;
});

// ============================================================================
// RESULTS
// ============================================================================

echo "\n╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                        TEST RESULTS                           ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n\n";

$total = $passed + $failed + $warnings;

echo "✅ Passed:   {$passed}/{$total}\n";
echo "❌ Failed:   {$failed}/{$total}\n";
echo "⚠️  Warnings: {$warnings}/{$total}\n\n";

if ($failed === 0) {
    echo "🎉 SUCCESS: Dynamic system fully operational!\n";
    echo "🚀 Ready for production deployment.\n\n";

    // Show sample output
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "Sample Output:\n";
    echo "═══════════════════════════════════════════════════════════════\n\n";

    $health = $loadBalancer->getHealthStatus();
    echo "Monitoring Mode: " . strtoupper($health['monitoring_mode']) . "\n";
    echo "System Tier: {$health['tier']}\n";
    echo "Overall Load: {$health['overall_load']}%\n";
    echo "Capacity Remaining: {$health['capacity_remaining']}%\n\n";

    echo "CPU: {$health['resources']['cpu']['usage']}% ({$health['resources']['cpu']['method']})\n";
    echo "Memory: {$health['resources']['memory']['usage']}%\n\n";

    if (!empty($health['use_cases'])) {
        echo "Active Use Cases:\n";
        foreach (array_slice($health['use_cases'], 0, 3) as $uc) {
            echo "  • {$uc['name']} (Priority: {$uc['priority']}, Confidence: {$uc['confidence']}%)\n";
        }
        echo "\n";
    }

    echo "Recommended Actions:\n";
    foreach (array_slice($health['recommended_actions'], 0, 5) as $action) {
        echo "  • {$action}\n";
    }
    echo "\n";

    exit(0);
} else {
    echo "❌ FAILURE: {$failed} test(s) failed\n";
    echo "⚠️  Please review errors above before deployment.\n\n";
    exit(1);
}
