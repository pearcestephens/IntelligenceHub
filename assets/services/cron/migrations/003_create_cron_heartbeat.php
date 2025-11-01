<?php
/**
 * Migration: Create cron_heartbeat table
 * 
 * Stores system heartbeat to monitor if cron system is alive
 * 
 * @version 1.0.0
 * @created 2025-10-21
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/smart-cron/bootstrap.php';

// Connect to database
$db = getMysqliConnection();

if (!$db) {
    die("ERROR: Could not connect to database\n");
}

echo "Creating cron_heartbeat table...\n";

$sql = "CREATE TABLE IF NOT EXISTS cron_heartbeat (
    id INT PRIMARY KEY DEFAULT 1,
    last_beat TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    server_hostname VARCHAR(255) DEFAULT NULL,
    php_version VARCHAR(50) DEFAULT NULL,
    memory_usage_mb DECIMAL(10,2) DEFAULT NULL,
    CHECK (id = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='System heartbeat monitor'";

if ($db->query($sql)) {
    echo "✓ cron_heartbeat table created successfully\n";
    
    // Insert initial heartbeat
    $hostname = gethostname();
    $phpVersion = PHP_VERSION;
    $memoryMb = round(memory_get_usage(true) / 1024 / 1024, 2);
    
    $stmt = $db->prepare(
        "INSERT INTO cron_heartbeat (id, server_hostname, php_version, memory_usage_mb) 
         VALUES (1, ?, ?, ?)
         ON DUPLICATE KEY UPDATE 
         last_beat = CURRENT_TIMESTAMP,
         server_hostname = VALUES(server_hostname),
         php_version = VALUES(php_version),
         memory_usage_mb = VALUES(memory_usage_mb)"
    );
    
    $stmt->bind_param('ssd', $hostname, $phpVersion, $memoryMb);
    
    if ($stmt->execute()) {
        echo "✓ Initial heartbeat inserted\n";
    }
    $stmt->close();
    
} else {
    echo "✗ ERROR: " . $db->error . "\n";
    exit(1);
}

// Show current heartbeat
$result = $db->query("SELECT * FROM cron_heartbeat WHERE id = 1");
if ($row = $result->fetch_assoc()) {
    echo "  Current heartbeat: {$row['last_beat']}\n";
    echo "  Server: {$row['server_hostname']}\n";
    echo "  PHP: {$row['php_version']}\n";
}

$db->close();
echo "\nMigration completed successfully!\n";
