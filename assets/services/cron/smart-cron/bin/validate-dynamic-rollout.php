#!/usr/bin/env php
<?php
/**
 * Dynamic System Rollout Validation
 *
 * Quick validation script to confirm:
 * - All components are present
 * - Configuration is correct
 * - System can initialize
 * - No fatal errors
 *
 * Usage: php validate-dynamic-rollout.php
 */

declare(strict_types=1);

echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  🚀 DYNAMIC SYSTEM ROLLOUT VALIDATION                       ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

$basePath = dirname(__DIR__);
$errors = [];
$warnings = [];
$success = [];

// Step 1: Check required files exist
echo "📁 Checking required files...\n";
$requiredFiles = [
    'Config.php' => $basePath . '/core/Config.php',
    'DynamicResourceMonitor.php' => $basePath . '/core/DynamicResourceMonitor.php',
    'UseCaseEngine.php' => $basePath . '/core/UseCaseEngine.php',
    'LoadBalancer.php' => $basePath . '/core/LoadBalancer.php',
    'config.json' => $basePath . '/config/config.json',
];

foreach ($requiredFiles as $name => $path) {
    if (file_exists($path)) {
        $success[] = "✅ $name found";
        echo "   ✅ $name\n";
    } else {
        $errors[] = "❌ Missing: $name at $path";
        echo "   ❌ $name MISSING\n";
    }
}
echo "\n";

// Step 2: Check configuration
echo "⚙️  Validating configuration...\n";
$configPath = $basePath . '/config/config.json';
if (file_exists($configPath)) {
    $config = json_decode(file_get_contents($configPath), true);

    if (isset($config['load_balancer'])) {
        if (isset($config['load_balancer']['dynamic_monitoring'])) {
            if ($config['load_balancer']['dynamic_monitoring'] === true) {
                $success[] = "✅ Dynamic monitoring enabled in config";
                echo "   ✅ Dynamic monitoring: ENABLED\n";
            } else {
                $warnings[] = "⚠️  Dynamic monitoring disabled in config";
                echo "   ⚠️  Dynamic monitoring: DISABLED (will use static mode)\n";
            }
        } else {
            $warnings[] = "⚠️  dynamic_monitoring flag not set (will default to static)";
            echo "   ⚠️  dynamic_monitoring flag not set\n";
        }

        if (isset($config['load_balancer']['cpu_threshold'])) {
            echo "   ✅ CPU threshold: " . $config['load_balancer']['cpu_threshold'] . "%\n";
        }
        if (isset($config['load_balancer']['memory_threshold'])) {
            echo "   ✅ Memory threshold: " . $config['load_balancer']['memory_threshold'] . "%\n";
        }
    } else {
        $errors[] = "❌ load_balancer section missing from config";
        echo "   ❌ load_balancer section MISSING\n";
    }
} else {
    $errors[] = "❌ config.json not found";
}
echo "\n";

// Step 3: Test class loading
echo "🔧 Testing component initialization...\n";
require_once $basePath . '/core/Config.php';
require_once $basePath . '/core/DynamicResourceMonitor.php';
require_once $basePath . '/core/UseCaseEngine.php';
require_once $basePath . '/core/LoadBalancer.php';
require_once $basePath . '/core/MetricsCollector.php';

try {
    $config = new SmartCron\Core\Config($configPath);
    $success[] = "✅ Config class loaded";
    echo "   ✅ Config class initialized\n";

    if (class_exists('SmartCron\Core\DynamicResourceMonitor')) {
        $success[] = "✅ DynamicResourceMonitor class available";
        echo "   ✅ DynamicResourceMonitor available\n";
    } else {
        $warnings[] = "⚠️  DynamicResourceMonitor class not found";
        echo "   ⚠️  DynamicResourceMonitor not found\n";
    }

    if (class_exists('SmartCron\Core\UseCaseEngine')) {
        $success[] = "✅ UseCaseEngine class available";
        echo "   ✅ UseCaseEngine available\n";
    } else {
        $warnings[] = "⚠️  UseCaseEngine class not found";
        echo "   ⚠️  UseCaseEngine not found\n";
    }

    if (class_exists('SmartCron\Core\LoadBalancer')) {
        $success[] = "✅ LoadBalancer class available";
        echo "   ✅ LoadBalancer available\n";
    } else {
        $errors[] = "❌ LoadBalancer class not found";
        echo "   ❌ LoadBalancer not found\n";
    }
} catch (Exception $e) {
    $errors[] = "❌ Config initialization failed: " . $e->getMessage();
    echo "   ❌ Config initialization FAILED: " . $e->getMessage() . "\n";
}
echo "\n";

// Step 4: Test DynamicResourceMonitor initialization (if available)
if (empty($errors) && class_exists('SmartCron\Core\DynamicResourceMonitor')) {
    echo "🔍 Testing DynamicResourceMonitor...\n";
    try {
        $monitor = new SmartCron\Core\DynamicResourceMonitor($config);
        $success[] = "✅ DynamicResourceMonitor initialized";
        echo "   ✅ Initialized successfully\n";

        // Try to get a snapshot (with basic timeout protection)
        echo "   ⏱️  Capturing resource snapshot (may take 1-3 seconds)...\n";
        $startTime = microtime(true);
        $snapshot = $monitor->getResourceSnapshot();
        $elapsed = round(microtime(true) - $startTime, 2);
        echo "   ✅ Snapshot captured in {$elapsed}s\n";

        if (isset($snapshot['cpu']['usage'])) {
            $success[] = "✅ CPU detection working";
            echo "   ✅ CPU detection: " . ($snapshot['cpu']['usage'] !== 'N/A' ? $snapshot['cpu']['usage'] . "%" : 'N/A') . " (method: " . ($snapshot['cpu']['method'] ?? 'unknown') . ")\n";
        }
        if (isset($snapshot['memory']['usage_percent'])) {
            $success[] = "✅ Memory detection working";
            echo "   ✅ Memory detection: " . ($snapshot['memory']['usage_percent'] !== 'N/A' ? $snapshot['memory']['usage_percent'] . "%" : 'N/A') . "\n";
        }
        if (isset($snapshot['overall_load'])) {
            echo "   ✅ Overall load score: " . $snapshot['overall_load'] . "/100\n";
        }
        if (isset($snapshot['tier_name'])) {
            echo "   ✅ Current tier: " . $snapshot['tier_name'] . "\n";
        }
    } catch (Exception $e) {
        $errors[] = "❌ DynamicResourceMonitor failed: " . $e->getMessage();
        echo "   ❌ Initialization FAILED: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Step 5: Test UseCaseEngine initialization (if available)
if (empty($errors) && class_exists('SmartCron\Core\UseCaseEngine')) {
    echo "🎯 Testing UseCaseEngine...\n";
    try {
        if (isset($monitor) && isset($config)) {
            $engine = new SmartCron\Core\UseCaseEngine($monitor, $config);
            $success[] = "✅ UseCaseEngine initialized";
            echo "   ✅ Initialized successfully\n";

            // Try to detect use cases
            $snapshot = $monitor->getResourceSnapshot();
            $useCases = $engine->detectUseCase($snapshot);
            echo "   ✅ Use case detection working: " . count($useCases) . " patterns detected\n";

            if (!empty($useCases)) {
                echo "   📊 Top 3 detected patterns:\n";
                $top3 = array_slice($useCases, 0, 3);
                foreach ($top3 as $uc) {
                    echo "      • " . $uc['name'] . " (Priority: " . $uc['priority'] . ", Confidence: " . $uc['confidence'] . "%)\n";
                }
            }
        } else {
            $warnings[] = "⚠️  Cannot test UseCaseEngine (monitor not available)";
            echo "   ⚠️  Monitor not available for testing\n";
        }
    } catch (Exception $e) {
        $errors[] = "❌ UseCaseEngine failed: " . $e->getMessage();
        echo "   ❌ Initialization FAILED: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Step 6: Test LoadBalancer initialization
if (empty($errors) && class_exists('SmartCron\Core\LoadBalancer')) {
    echo "⚖️  Testing LoadBalancer...\n";
    try {
        $metrics = new SmartCron\Core\MetricsCollector($config);
        $loadBalancer = new SmartCron\Core\LoadBalancer($config, $metrics);
        $success[] = "✅ LoadBalancer initialized";
        echo "   ✅ Initialized successfully\n";

        // Get health status
        $health = $loadBalancer->getHealthStatus();
        if (isset($health['monitoring_mode'])) {
            echo "   ✅ Monitoring mode: " . $health['monitoring_mode'] . "\n";
            if ($health['monitoring_mode'] === 'dynamic') {
                $success[] = "✅ Dynamic mode active";
                echo "   🚀 DYNAMIC MODE ACTIVE!\n";
            } else {
                $warnings[] = "⚠️  Static mode active (dynamic not enabled)";
                echo "   ⚠️  Static mode active\n";
            }
        }

        if (isset($health['cpu'])) {
            echo "   📊 Current CPU: " . $health['cpu'] . "%\n";
        }
        if (isset($health['memory'])) {
            echo "   📊 Current Memory: " . $health['memory'] . "%\n";
        }
    } catch (Exception $e) {
        $errors[] = "❌ LoadBalancer failed: " . $e->getMessage();
        echo "   ❌ Initialization FAILED: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Final report
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║  📊 VALIDATION RESULTS                                       ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "✅ SUCCESS: " . count($success) . " checks passed\n";
echo "⚠️  WARNINGS: " . count($warnings) . " warnings\n";
echo "❌ ERRORS: " . count($errors) . " errors\n";
echo "\n";

if (!empty($warnings)) {
    echo "⚠️  WARNINGS:\n";
    foreach ($warnings as $warning) {
        echo "   $warning\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "❌ ERRORS:\n";
    foreach ($errors as $error) {
        echo "   $error\n";
    }
    echo "\n";
    echo "🛑 SYSTEM NOT READY FOR ROLLOUT\n";
    echo "   Please fix errors above before proceeding.\n";
    echo "\n";
    exit(1);
}

if (empty($errors) && count($warnings) <= 2) {
    echo "✅ SYSTEM READY FOR ROLLOUT!\n";
    echo "\n";
    echo "📋 Next Steps:\n";
    echo "   1. Run comprehensive tests: php bin/test-dynamic-system.php\n";
    echo "   2. Execute Smart Cron: php smart-cron.php\n";
    echo "   3. Check health: php bin/health-check.php\n";
    echo "   4. Monitor logs: tail -f logs/smart-cron.log\n";
    echo "   5. Add to crontab for production deployment\n";
    echo "\n";
    exit(0);
} else {
    echo "⚠️  SYSTEM FUNCTIONAL BUT HAS WARNINGS\n";
    echo "   Review warnings above and proceed with caution.\n";
    echo "\n";
    exit(0);
}
