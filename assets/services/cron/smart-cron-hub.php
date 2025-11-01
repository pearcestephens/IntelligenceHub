#!/usr/bin/env php
<?php
/**
 * Smart-Cron Central Hub - Main Executable
 * 
 * Manages satellite cron systems and local cron jobs
 * Integrates with Intelligence Hub database
 */

declare(strict_types=1);

// Safety limits
ini_set('memory_limit', '512M');
ini_set('max_execution_time', '300');

// Load bootstrap
require_once __DIR__ . '/smart-cron/bootstrap.php';

smart_cron_log("Smart-Cron Central Hub starting...");

// Basic heartbeat update
$db = getMysqliConnection();
if ($db) {
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
    $stmt->execute();
    $stmt->close();
    
    smart_cron_log("Heartbeat updated successfully");
    
    // Check and log current heartbeat status
    $result = $db->query("SELECT * FROM cron_heartbeat WHERE id = 1");
    if ($row = $result->fetch_assoc()) {
        smart_cron_log("Current heartbeat: {$row['last_beat']} on {$row['server_hostname']}");
    }
    
    $db->close();
} else {
    smart_cron_log("Failed to connect to database", 'ERROR');
    exit(1);
}

smart_cron_log("Smart-Cron Central Hub completed successfully");