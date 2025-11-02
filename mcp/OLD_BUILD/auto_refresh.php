<?php
/**
 * MCP KB Auto-Refresh Service
 * 
 * Keeps knowledge base synchronized with file system changes
 * Runs as background service or cron job
 * 
 * @package CIS\MCP
 * @version 1.0.0
 */

declare(strict_types=1);

// Configuration
$config = [
    'watch_directories' => [
        '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/api',
        '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/app',
        '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/assets',
        '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/dashboard',
        '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/integrations'
    ],
    'kb_pipeline' => '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/kb/run_verified_kb_pipeline.php',
    'check_interval' => 300, // 5 minutes
    'log_file' => '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/logs/mcp_auto_refresh.log'
];

// Get command line mode
$mode = $argv[1] ?? 'check';

function logMessage(string $message, string $logFile): void
{
    $timestamp = date('Y-m-d H:i:s');
    $logLine = "[{$timestamp}] {$message}\n";
    echo $logLine;
    file_put_contents($logFile, $logLine, FILE_APPEND);
}

function getLastModifiedTime(array $directories): int
{
    $latestTime = 0;
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) continue;
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && preg_match('/\.(php|js|html|css|sql|md)$/', $file->getFilename())) {
                $mtime = $file->getMTime();
                if ($mtime > $latestTime) {
                    $latestTime = $mtime;
                }
            }
        }
    }
    
    return $latestTime;
}

function needsRefresh(array $config): bool
{
    $stateFile = '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/.last_refresh';
    
    if (!file_exists($stateFile)) {
        return true;
    }
    
    $lastRefresh = (int)file_get_contents($stateFile);
    $currentModTime = getLastModifiedTime($config['watch_directories']);
    
    return $currentModTime > $lastRefresh;
}

function runRefresh(array $config): bool
{
    logMessage("Running KB refresh...", $config['log_file']);
    
    $cmd = "php {$config['kb_pipeline']} --quick 2>&1";
    exec($cmd, $output, $returnVar);
    
    if ($returnVar === 0) {
        // Update state file
        $stateFile = '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/.last_refresh';
        file_put_contents($stateFile, (string)time());
        
        logMessage("KB refresh completed successfully", $config['log_file']);
        return true;
    } else {
        logMessage("KB refresh failed: " . implode("\n", $output), $config['log_file']);
        return false;
    }
}

// Execute based on mode
switch ($mode) {
    case 'check':
        // Check if refresh is needed and run if so
        if (needsRefresh($config)) {
            logMessage("Changes detected, triggering refresh", $config['log_file']);
            runRefresh($config);
        } else {
            logMessage("No changes detected, skipping refresh", $config['log_file']);
        }
        break;
        
    case 'force':
        // Force refresh regardless
        logMessage("Forcing KB refresh", $config['log_file']);
        runRefresh($config);
        break;
        
    case 'watch':
        // Continuous watch mode
        logMessage("Starting watch mode (checking every {$config['check_interval']}s)", $config['log_file']);
        
        while (true) {
            if (needsRefresh($config)) {
                logMessage("Changes detected in watch mode", $config['log_file']);
                runRefresh($config);
            }
            
            sleep($config['check_interval']);
        }
        break;
        
    default:
        echo "Usage: php auto_refresh.php [check|force|watch]\n";
        echo "  check - Check for changes and refresh if needed (default)\n";
        echo "  force - Force refresh immediately\n";
        echo "  watch - Continuous watch mode\n";
        exit(1);
}

exit(0);
