#!/usr/bin/env php
<?php
/**
 * Test service runner before installing as systemd service
 */

echo "Testing Intelligence Hub Service Runner...\n\n";

// Test environment loading
$envFile = '/home/129337.cloudwaysapps.com/hdgwrzntwa/private_html/config/.env';
if (!file_exists($envFile)) {
    echo "❌ .env file not found\n";
    exit(1);
}
echo "✓ .env file found\n";

// Test database connection
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=hdgwrzntwa', 'hdgwrzntwa', 'bFUdRjh4Jx');
    echo "✓ Database connected\n";
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test agent files
$agents = [
    'InventoryAgent' => __DIR__ . '/agents/InventoryAgent.php',
    'WebMonitorAgent' => __DIR__ . '/agents/WebMonitorAgent.php',
    'SecurityAgent' => __DIR__ . '/agents/SecurityAgent.php'
];

foreach ($agents as $name => $file) {
    if (file_exists($file)) {
        echo "✓ {$name} found\n";
    } else {
        echo "❌ {$name} not found: {$file}\n";
        exit(1);
    }
}

// Test pcntl extension
if (function_exists('pcntl_signal')) {
    echo "✓ pcntl extension loaded\n";
} else {
    echo "⚠ pcntl extension not loaded (signal handling disabled)\n";
}

echo "\n✓ All checks passed - ready to install service\n\n";
echo "To install:\n";
echo "  sudo bash /home/129337.cloudwaysapps.com/hdgwrzntwa/install-service.sh\n\n";
