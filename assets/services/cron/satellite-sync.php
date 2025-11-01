#!/usr/bin/env php
<?php
/**
 * Intelligence Hub - Satellite Sync System
 * 
 * Manages synchronization and communication with all satellite systems.
 * This script is called by the main cron system to coordinate satellite operations.
 * 
 * Usage:
 *   php satellite-sync.php                     # Sync all satellites
 *   php satellite-sync.php --satellite=cis     # Sync specific satellite
 *   php satellite-sync.php --heartbeat         # Send heartbeat to all
 *   php satellite-sync.php --status            # Check satellite status
 * 
 * @package IntelligenceHub
 * @version 2.0.0
 */

declare(strict_types=1);

// CLI Safety Check
if (PHP_SAPI !== 'cli') {
    die("This script can only be run from command line.\n");
}

// Bootstrap
require_once __DIR__ . '/hub-cron-config.php';

class SatelliteSync
{
    private PDO $db;
    private HubCronLogger $logger;
    private array $config;
    
    public function __construct()
    {
        $this->db = HubCronConfig::getDatabase();
        $this->logger = new HubCronLogger();
        $this->config = HubCronConfig::getConfig();
    }
    
    /**
     * Run satellite sync operations
     */
    public function run(array $options): void
    {
        $this->logger->info("Starting satellite sync operations");
        
        if (isset($options['satellite'])) {
            $this->syncSpecificSatellite($options['satellite']);
        } elseif (isset($options['heartbeat'])) {
            $this->sendHeartbeatToAll();
        } elseif (isset($options['status'])) {
            $this->checkAllSatelliteStatus();
        } else {
            $this->syncAllSatellites();
        }
    }
    
    /**
     * Sync all enabled satellites
     */
    private function syncAllSatellites(): void
    {
        $stmt = $this->db->query("
            SELECT * FROM hub_cron_satellites 
            WHERE enabled = TRUE 
            ORDER BY priority ASC
        ");
        $satellites = $stmt->fetchAll();
        
        if (empty($satellites)) {
            $this->logger->warn("No enabled satellites found");
            return;
        }
        
        $this->logger->info("Syncing " . count($satellites) . " satellites");
        
        foreach ($satellites as $satellite) {
            $this->syncSatellite($satellite);
        }
        
        $this->logger->info("Satellite sync completed");
    }
    
    /**
     * Sync specific satellite by ID
     */
    private function syncSpecificSatellite(string $satelliteId): void
    {
        $stmt = $this->db->prepare("SELECT * FROM hub_cron_satellites WHERE id = ?");
        $stmt->execute([$satelliteId]);
        $satellite = $stmt->fetch();
        
        if (!$satellite) {
            $this->logger->error("Satellite not found: {$satelliteId}");
            return;
        }
        
        $this->logger->info("Syncing specific satellite: {$satelliteId}");
        $this->syncSatellite($satellite);
    }
    
    /**
     * Sync individual satellite
     */
    private function syncSatellite(array $satellite): void
    {
        $startTime = microtime(true);
        $satelliteId = $satellite['id'];
        
        $this->logger->info("Syncing satellite: {$satelliteId} ({$satellite['name']})");
        
        try {
            // Update satellite status to syncing
            $this->updateSatelliteStatus($satelliteId, 'syncing');
            
            // 1. Send heartbeat
            $heartbeatSuccess = $this->sendHeartbeat($satellite);
            
            // 2. Get satellite status
            $statusData = $this->getSatelliteStatus($satellite);
            
            // 3. Sync cron jobs if available
            $jobsData = $this->getSatelliteJobs($satellite);
            
            // 4. Send hub commands if any
            $this->sendHubCommands($satellite);
            
            // Calculate response time
            $responseTime = round((microtime(true) - $startTime) * 1000, 3);
            
            // Update satellite record
            $this->updateSatelliteRecord($satellite, $statusData, $responseTime, $heartbeatSuccess);
            
            // Record metrics
            $this->recordSatelliteMetrics($satelliteId, [
                'response_time' => $responseTime,
                'heartbeat_success' => $heartbeatSuccess,
                'jobs_count' => count($jobsData),
                'status_data' => $statusData
            ]);
            
            $this->logger->info("Satellite {$satelliteId} synced successfully in {$responseTime}ms");
            
        } catch (Exception $e) {
            $this->logger->error("Failed to sync satellite {$satelliteId}: " . $e->getMessage());
            
            // Update satellite status to error
            $this->updateSatelliteStatus($satelliteId, 'error', $e->getMessage());
            
            // Create alert
            $this->createAlert('satellite_down', 'high', 
                "Satellite sync failed: {$satellite['name']}", 
                $e->getMessage(), null, $satelliteId);
        }
    }
    
    /**
     * Send heartbeat to satellite
     */
    private function sendHeartbeat(array $satellite): bool
    {
        $url = rtrim($satellite['url'], '/') . '/api/hub-heartbeat.php';
        
        $data = [
            'hub_id' => $this->config['hub_id'],
            'timestamp' => time(),
            'version' => $this->config['version']
        ];
        
        try {
            $response = $this->makeHttpRequest($url, 'POST', $data, 5); // 5 second timeout
            
            if ($response['http_code'] === 200) {
                $this->logger->debug("Heartbeat sent successfully to {$satellite['id']}");
                return true;
            } else {
                $this->logger->warn("Heartbeat failed for {$satellite['id']}: HTTP {$response['http_code']}");
                return false;
            }
            
        } catch (Exception $e) {
            $this->logger->warn("Heartbeat error for {$satellite['id']}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get satellite status
     */
    private function getSatelliteStatus(array $satellite): array
    {
        $url = rtrim($satellite['url'], '/') . '/api/hub-status.php';
        
        try {
            $response = $this->makeHttpRequest($url, 'GET', null, 10);
            
            if ($response['http_code'] === 200) {
                $data = json_decode($response['body'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $data;
                }
            }
            
        } catch (Exception $e) {
            $this->logger->debug("Status request failed for {$satellite['id']}: " . $e->getMessage());
        }
        
        return [];
    }
    
    /**
     * Get satellite cron jobs
     */
    private function getSatelliteJobs(array $satellite): array
    {
        $url = rtrim($satellite['url'], '/') . '/api/hub-jobs.php';
        
        try {
            $response = $this->makeHttpRequest($url, 'GET', null, 15);
            
            if ($response['http_code'] === 200) {
                $data = json_decode($response['body'], true);
                if (json_last_error() === JSON_ERROR_NONE && isset($data['jobs'])) {
                    return $data['jobs'];
                }
            }
            
        } catch (Exception $e) {
            $this->logger->debug("Jobs request failed for {$satellite['id']}: " . $e->getMessage());
        }
        
        return [];
    }
    
    /**
     * Send hub commands to satellite
     */
    private function sendHubCommands(array $satellite): void
    {
        // Check for pending commands for this satellite
        $stmt = $this->db->prepare("
            SELECT * FROM hub_cron_commands 
            WHERE satellite_id = ? AND status = 'pending' 
            ORDER BY created_at ASC 
            LIMIT 10
        ");
        $stmt->execute([$satellite['id']]);
        $commands = $stmt->fetchAll();
        
        if (empty($commands)) {
            return;
        }
        
        $this->logger->info("Sending " . count($commands) . " commands to {$satellite['id']}");
        
        foreach ($commands as $command) {
            try {
                $this->sendCommand($satellite, $command);
            } catch (Exception $e) {
                $this->logger->error("Failed to send command {$command['id']}: " . $e->getMessage());
            }
        }
    }
    
    /**
     * Send individual command to satellite
     */
    private function sendCommand(array $satellite, array $command): void
    {
        $url = rtrim($satellite['url'], '/') . '/api/hub-command.php';
        
        $data = [
            'command_id' => $command['id'],
            'action' => $command['action'],
            'parameters' => json_decode($command['parameters'], true),
            'hub_id' => $this->config['hub_id']
        ];
        
        // Mark command as sent
        $stmt = $this->db->prepare("
            UPDATE hub_cron_commands 
            SET status = 'sent', sent_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$command['id']]);
        
        try {
            $response = $this->makeHttpRequest($url, 'POST', $data, 30);
            
            if ($response['http_code'] === 200) {
                $responseData = json_decode($response['body'], true);
                
                // Update command status
                $stmt = $this->db->prepare("
                    UPDATE hub_cron_commands 
                    SET status = 'completed', completed_at = NOW(), response = ? 
                    WHERE id = ?
                ");
                $stmt->execute([json_encode($responseData), $command['id']]);
                
                $this->logger->debug("Command {$command['id']} completed successfully");
                
            } else {
                throw new Exception("HTTP {$response['http_code']}: {$response['body']}");
            }
            
        } catch (Exception $e) {
            // Mark command as failed
            $stmt = $this->db->prepare("
                UPDATE hub_cron_commands 
                SET status = 'failed', error = ?, completed_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$e->getMessage(), $command['id']]);
            
            throw $e;
        }
    }
    
    /**
     * Make HTTP request to satellite
     */
    private function makeHttpRequest(string $url, string $method = 'GET', ?array $data = null, int $timeout = 10): array
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_USERAGENT => 'IntelligenceHub/2.0 SatelliteSync',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-Hub-ID: ' . $this->config['hub_id'],
                'X-Hub-Version: ' . $this->config['version']
            ]
        ]);
        
        if ($method === 'POST' && $data !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($body === false) {
            throw new Exception("CURL error: {$error}");
        }
        
        return [
            'http_code' => $httpCode,
            'body' => $body
        ];
    }
    
    /**
     * Update satellite status
     */
    private function updateSatelliteStatus(string $satelliteId, string $status, ?string $error = null): void
    {
        $stmt = $this->db->prepare("
            UPDATE hub_cron_satellites 
            SET status = ?, last_error = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$status, $error, $satelliteId]);
    }
    
    /**
     * Update satellite record with sync results
     */
    private function updateSatelliteRecord(array $satellite, array $statusData, float $responseTime, bool $heartbeatSuccess): void
    {
        $satelliteId = $satellite['id'];
        
        // Calculate success rate
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_syncs,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as successful_syncs
            FROM hub_cron_executions e
            JOIN hub_cron_jobs j ON e.job_id = j.id
            WHERE j.name = 'satellite_sync' AND j.satellite_id = ?
            AND e.started_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $stmt->execute([$satelliteId]);
        $syncStats = $stmt->fetch();
        
        $successRate = $syncStats['total_syncs'] > 0 
            ? round(($syncStats['successful_syncs'] / $syncStats['total_syncs']) * 100, 2)
            : 100.0;
        
        // Update satellite record
        $status = $heartbeatSuccess ? 'online' : 'offline';
        $jobCount = isset($statusData['job_count']) ? $statusData['job_count'] : 0;
        
        // Debug the values before executing
        $this->logger->info("About to update satellite {$satelliteId}: status={$status}, jobCount={$jobCount}, responseTime={$responseTime}, successRate={$successRate}");
        
        $stmt = $this->db->prepare("
            UPDATE hub_cron_satellites 
            SET 
                last_sync = NOW(),
                last_heartbeat = ?,
                status = ?,
                job_count = ?,
                avg_response_time = (avg_response_time + ?) / 2,
                success_rate = ?,
                last_error = NULL,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $heartbeatSuccess ? date('Y-m-d H:i:s') : null,
            $status,
            $jobCount,
            $responseTime,
            $successRate,
            $satelliteId
        ]);
        
        // Debug logging
        $this->logger->info("Updated satellite {$satelliteId} with status: {$status}");
    }
    
    /**
     * Record satellite metrics
     */
    private function recordSatelliteMetrics(string $satelliteId, array $metrics): void
    {
        foreach ($metrics as $name => $value) {
            if (is_numeric($value)) {
                $stmt = $this->db->prepare("
                    INSERT INTO hub_cron_metrics (metric_type, metric_name, metric_value, satellite_id, metadata)
                    VALUES ('performance', ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $name,
                    (float)$value,
                    $satelliteId,
                    json_encode(['satellite_id' => $satelliteId, 'timestamp' => time()])
                ]);
            }
        }
    }
    
    /**
     * Create alert
     */
    private function createAlert(string $type, string $severity, string $title, string $message, ?int $jobId = null, ?string $satelliteId = null): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO hub_cron_alerts (alert_type, severity, title, message, job_id, satellite_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$type, $severity, $title, $message, $jobId, $satelliteId]);
    }
    
    /**
     * Send heartbeat to all satellites
     */
    private function sendHeartbeatToAll(): void
    {
        $stmt = $this->db->query("SELECT * FROM hub_cron_satellites WHERE enabled = TRUE");
        $satellites = $stmt->fetchAll();
        
        $this->logger->info("Sending heartbeat to " . count($satellites) . " satellites");
        
        foreach ($satellites as $satellite) {
            $success = $this->sendHeartbeat($satellite);
            $this->updateSatelliteStatus($satellite['id'], $success ? 'online' : 'offline');
        }
    }
    
    /**
     * Check status of all satellites
     */
    private function checkAllSatelliteStatus(): void
    {
        $stmt = $this->db->query("
            SELECT *, 
                TIMESTAMPDIFF(MINUTE, last_heartbeat, NOW()) as minutes_since_heartbeat,
                TIMESTAMPDIFF(MINUTE, last_sync, NOW()) as minutes_since_sync
            FROM hub_cron_satellites 
            ORDER BY priority ASC
        ");
        $satellites = $stmt->fetchAll();
        
        echo "=== Satellite Status Report ===\n\n";
        
        foreach ($satellites as $satellite) {
            $status = $satellite['status'];
            $enabled = $satellite['enabled'] ? 'enabled' : 'disabled';
            $lastHeartbeat = $satellite['minutes_since_heartbeat'] ? 
                $satellite['minutes_since_heartbeat'] . ' min ago' : 'never';
            $lastSync = $satellite['minutes_since_sync'] ? 
                $satellite['minutes_since_sync'] . ' min ago' : 'never';
            
            echo "{$satellite['name']} ({$satellite['id']}):\n";
            echo "  Status: {$status} ({$enabled})\n";
            echo "  URL: {$satellite['url']}\n";
            echo "  Last Heartbeat: {$lastHeartbeat}\n";
            echo "  Last Sync: {$lastSync}\n";
            echo "  Jobs: {$satellite['job_count']}\n";
            echo "  Response Time: {$satellite['avg_response_time']}ms\n";
            echo "  Success Rate: {$satellite['success_rate']}%\n";
            
            if ($satellite['last_error']) {
                echo "  Last Error: {$satellite['last_error']}\n";
            }
            
            echo "\n";
        }
    }
}

// Parse CLI arguments
$options = getopt('', [
    'satellite:',
    'heartbeat',
    'status',
    'help'
]);

if (isset($options['help'])) {
    echo "Intelligence Hub Satellite Sync v2.0\n\n";
    echo "Usage:\n";
    echo "  php satellite-sync.php                     Sync all satellites\n";
    echo "  php satellite-sync.php --satellite=id      Sync specific satellite\n";
    echo "  php satellite-sync.php --heartbeat         Send heartbeat to all\n";
    echo "  php satellite-sync.php --status            Check satellite status\n";
    echo "  php satellite-sync.php --help              Show this help\n";
    exit(0);
}

try {
    $sync = new SatelliteSync();
    $sync->run($options);
    exit(0);
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}