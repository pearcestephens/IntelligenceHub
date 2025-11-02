#!/usr/bin/env php
<?php
/**
 * Quick Autoloader Test
 * Tests if the autoloader fix works for SmartCron\Core\Config
 */

echo "Testing autoloader fix...\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Load autoloader
require_once __DIR__ . '/autoloader.php';

echo "Step 1: Autoloader loaded ✓\n";

// Test class loading
echo "Step 2: Attempting to load SmartCron\\Core\\Config...\n";

try {
    if (class_exists('SmartCron\\Core\\Config')) {
        echo "✅ SUCCESS: SmartCron\\Core\\Config class loaded!\n";

        // Try to instantiate it
        echo "Step 3: Attempting to instantiate Config...\n";
        require_once __DIR__ . '/smart-cron/bootstrap.php';
        $config = new SmartCron\Core\Config();
        echo "✅ SUCCESS: Config object created!\n";

        echo "\n🎉 Autoloader is working correctly!\n";
        exit(0);
    } else {
        echo "❌ FAILED: Class not found\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ FAILED: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
