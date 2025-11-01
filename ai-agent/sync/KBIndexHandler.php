<?php
/**
 * KB Indexer Job Handler
 * 
 * Queue job handler for kb.index and kb.cleanup job types
 * Integrates with your existing queue system
 * 
 * This file should be registered in your queue worker job handler registry
 * 
 * @package CIS\AI\Queue\Handlers
 * @version 1.0.0
 */

declare(strict_types=1);

namespace Queue\Handlers;

class KBIndexHandler
{
    private $logger;
    
    public function __construct($logger = null)
    {
        $this->logger = $logger;
    }
    
    /**
     * Handle kb.index job type
     */
    public function handleIndex(array $payload): array
    {
        $this->log("Starting KB index job", $payload);
        
        $domain = $payload['domain'] ?? 'global';
        $mode = $payload['mode'] ?? 'incremental';
        $script = $payload['script'] ?? '/home/master/applications/jcepnzzkmj/public_html/ai-agent/sync/kb-auto-indexer.php';
        
        // Validate script exists
        if (!file_exists($script)) {
            throw new \Exception("KB indexer script not found: {$script}");
        }
        
        // Build command
        $args = $payload['args'] ?? "--domain={$domain} --{$mode}";
        $logFile = "/home/master/applications/jcepnzzkmj/public_html/logs/kb-{$domain}-{$mode}.log";
        
        $command = sprintf(
            '/usr/bin/php %s %s >> %s 2>&1',
            escapeshellarg($script),
            $args,
            escapeshellarg($logFile)
        );
        
        $this->log("Executing command: {$command}");
        
        // Execute
        $startTime = microtime(true);
        exec($command, $output, $returnCode);
        $duration = round(microtime(true) - $startTime, 2);
        
        if ($returnCode !== 0) {
            throw new \Exception("KB indexer failed with exit code {$returnCode}. Check log: {$logFile}");
        }
        
        $this->log("KB index completed successfully", [
            'domain' => $domain,
            'mode' => $mode,
            'duration' => $duration,
            'log_file' => $logFile
        ]);
        
        return [
            'success' => true,
            'domain' => $domain,
            'mode' => $mode,
            'duration' => $duration,
            'log_file' => $logFile,
            'output_lines' => count($output)
        ];
    }
    
    /**
     * Handle kb.cleanup job type
     */
    public function handleCleanup(array $payload): array
    {
        $this->log("Starting KB cleanup job", $payload);
        
        $script = $payload['script'] ?? '/home/master/applications/jcepnzzkmj/public_html/ai-agent/sync/kb-cleanup.php';
        $args = $payload['args'] ?? '--older-than=90';
        
        // Validate script exists
        if (!file_exists($script)) {
            throw new \Exception("KB cleanup script not found: {$script}");
        }
        
        $logFile = "/home/master/applications/jcepnzzkmj/public_html/logs/kb-cleanup.log";
        
        $command = sprintf(
            '/usr/bin/php %s %s >> %s 2>&1',
            escapeshellarg($script),
            $args,
            escapeshellarg($logFile)
        );
        
        $this->log("Executing cleanup: {$command}");
        
        $startTime = microtime(true);
        exec($command, $output, $returnCode);
        $duration = round(microtime(true) - $startTime, 2);
        
        if ($returnCode !== 0) {
            throw new \Exception("KB cleanup failed with exit code {$returnCode}");
        }
        
        $this->log("KB cleanup completed", ['duration' => $duration]);
        
        return [
            'success' => true,
            'duration' => $duration,
            'log_file' => $logFile
        ];
    }
    
    /**
     * Route job to correct handler based on job_type
     */
    public function handle(string $jobType, array $payload): array
    {
        switch ($jobType) {
            case 'kb.index':
                return $this->handleIndex($payload);
                
            case 'kb.cleanup':
                return $this->handleCleanup($payload);
                
            default:
                throw new \Exception("Unknown KB job type: {$jobType}");
        }
    }
    
    private function log(string $message, $context = []): void
    {
        if ($this->logger) {
            $this->logger->info($message, $context);
        } else {
            $timestamp = date('Y-m-d H:i:s');
            $contextStr = empty($context) ? '' : ' ' . json_encode($context);
            echo "[{$timestamp}] {$message}{$contextStr}\n";
        }
    }
}

// ============================================================================
// Standalone execution (for testing)
// ============================================================================

if (PHP_SAPI === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    // Test the handler
    $handler = new KBIndexHandler();
    
    echo "Testing KB Index Handler...\n\n";
    
    // Test kb.index job
    $testPayload = [
        'domain' => 'staff',
        'mode' => 'incremental',
        'script' => '/home/master/applications/jcepnzzkmj/public_html/ai-agent/sync/kb-auto-indexer.php',
        'args' => '--domain=staff --incremental'
    ];
    
    try {
        $result = $handler->handle('kb.index', $testPayload);
        echo "✅ SUCCESS: KB index handler working\n";
        print_r($result);
    } catch (\Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
    }
}
