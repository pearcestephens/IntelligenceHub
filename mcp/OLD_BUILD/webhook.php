<?php
/**
 * MCP Webhook Handler
 * 
 * Receives webhook notifications when code changes are committed
 * Triggers immediate KB refresh for affected files
 * 
 * @package CIS\MCP
 * @version 1.0.0
 */

declare(strict_types=1);

header('Content-Type: application/json');

// Log file
$logFile = '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/logs/mcp_webhook.log';

function logMessage(string $message): void
{
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[{$timestamp}] {$message}\n", FILE_APPEND);
}

// Get webhook payload
$payload = file_get_contents('php://input');
$data = json_decode($payload, true);

if (!$data) {
    logMessage("Invalid webhook payload");
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

logMessage("Webhook received: " . json_encode($data));

// Determine webhook source
$source = 'unknown';
if (isset($data['repository'])) {
    $source = 'github';
} elseif (isset($data['project'])) {
    $source = 'gitlab';
}

// Extract changed files
$changedFiles = [];

if ($source === 'github') {
    // GitHub webhook
    foreach ($data['commits'] ?? [] as $commit) {
        $changedFiles = array_merge(
            $changedFiles,
            $commit['added'] ?? [],
            $commit['modified'] ?? [],
            $commit['removed'] ?? []
        );
    }
} elseif ($source === 'gitlab') {
    // GitLab webhook
    foreach ($data['commits'] ?? [] as $commit) {
        $changedFiles = array_merge(
            $changedFiles,
            $commit['added'] ?? [],
            $commit['modified'] ?? [],
            $commit['removed'] ?? []
        );
    }
}

$changedFiles = array_unique($changedFiles);

logMessage("Changed files (" . count($changedFiles) . "): " . implode(', ', $changedFiles));

// Trigger KB refresh for changed files
if (!empty($changedFiles)) {
    $kbPipeline = '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/kb/run_verified_kb_pipeline.php';
    
    // Quick refresh
    $cmd = "php {$kbPipeline} --quick > /dev/null 2>&1 &";
    exec($cmd);
    
    logMessage("KB refresh triggered");
    
    // Also invalidate MCP cache
    $cacheFile = '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/.cache';
    if (file_exists($cacheFile)) {
        @unlink($cacheFile);
        logMessage("MCP cache invalidated");
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'KB refresh triggered',
        'files_processed' => count($changedFiles)
    ]);
} else {
    logMessage("No files to process");
    
    echo json_encode([
        'success' => true,
        'message' => 'No changes detected'
    ]);
}

exit(0);
