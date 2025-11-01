<?php
/**
 * Quick utility to update Hub cron job commands
 */

require_once __DIR__ . '/hub-cron-config.php';

try {
    $db = HubCronConfig::getDatabase();
    
    // Update MCP Tools Health Check with correct path
    $stmt = $db->prepare("UPDATE hub_cron_jobs SET command = ? WHERE name = ?");
    $stmt->execute([
        'php /home/master/applications/hdgwrzntwa/public_html/mcp/health.php',
        'MCP Tools Health Check'
    ]);
    
    // Update Content Index Refresh with correct path  
    $stmt->execute([
        'php /home/master/applications/hdgwrzntwa/public_html/mcp/auto_refresh.php',
        'Content Index Refresh'
    ]);
    
    // Update Satellite Sync with correct path
    $stmt->execute([
        'php /home/master/applications/hdgwrzntwa/public_html/assets/services/cron/satellite-sync.php --sync-all',
        'Satellite Sync'
    ]);
    
    // Update Log Cleanup with correct path
    $stmt->execute([
        'php /home/master/applications/hdgwrzntwa/public_html/scripts/cleanup-logs.php',
        'Log Cleanup'
    ]);
    
    echo "Updated job commands with full paths\n";
    
    // Show updated jobs
    $stmt = $db->query("SELECT name, command FROM hub_cron_jobs ORDER BY name");
    echo "\nUpdated jobs:\n";
    while ($job = $stmt->fetch()) {
        echo "- {$job['name']}: {$job['command']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}