<?php
/**
 * Intelligence Distribution API - Push System
 * 
 * Pushes intelligence data to satellite applications via HTTP/HTTPS
 * No SSH, no file transfer - pure REST API communication
 * 
 * Features:
 * - Push intelligence to multiple satellites
 * - Retry logic with exponential backoff
 * - Verify delivery with checksums
 * - Monitor push status
 * - Scalable across any server/provider
 * 
 * @package KB\API
 * @version 1.0.0
 */

declare(strict_types=1);

class IntelligenceDistributor
{
    private array $satellites = [];
    private string $logFile;
    private int $maxRetries = 3;
    private int $timeout = 30;
    
    public function __construct()
    {
        $this->logFile = __DIR__ . '/../logs/distribution.log';
        $this->loadSatellites();
    }
    
    /**
     * Load satellite configurations
     */
    private function loadSatellites(): void
    {
        $configFile = __DIR__ . '/../config/satellites.json';
        
        if (file_exists($configFile)) {
            $config = json_decode(file_get_contents($configFile), true);
            $this->satellites = $config['satellites'] ?? [];
            
            // Expand environment variables in API keys
            foreach ($this->satellites as &$satellite) {
                if (isset($satellite['api_key'])) {
                    $satellite['api_key'] = $this->expandEnvVars($satellite['api_key']);
                }
            }
        } else {
            // Default satellites
            $this->satellites = [
                [
                    'id' => 'cis_portal',
                    'name' => 'CIS Main Portal',
                    'url' => 'https://staff.vapeshed.co.nz/api/kb/receive',
                    'api_key' => getenv('CIS_API_KEY') ?: 'default_key_change_me',
                    'enabled' => true,
                    'priority' => 1,
                ],
                // Add more satellites as needed
            ];
        }
    }
    
    /**
     * Expand environment variables in strings
     * Supports ${VAR_NAME} format
     * 
     * @param string $value Value to expand
     * @return string Expanded value
     */
    private function expandEnvVars(string $value): string
    {
        return preg_replace_callback('/\$\{([A-Z_]+)\}/', function($matches) {
            $envVar = $matches[1];
            $envValue = getenv($envVar);
            
            if ($envValue === false) {
                // Environment variable not set, keep original
                return $matches[0];
            }
            
            return $envValue;
        }, $value);
    }
    
    /**
     * Push intelligence to all satellites
     * 
     * @param array $intelligenceData Intelligence package to distribute
     * @return array Results for each satellite
     */
    public function pushToAll(array $intelligenceData): array
    {
        $results = [];
        $timestamp = date('Y-m-d H:i:s');
        
        $this->log("=== Starting intelligence push at {$timestamp} ===");
        $this->log("Package size: " . $this->formatBytes(strlen(json_encode($intelligenceData))));
        
        foreach ($this->satellites as $satellite) {
            if (!($satellite['enabled'] ?? true)) {
                $this->log("Skipping disabled satellite: {$satellite['name']}");
                continue;
            }
            
            $result = $this->pushToSatellite($satellite, $intelligenceData);
            $results[$satellite['id']] = $result;
        }
        
        $this->log("=== Push complete at " . date('Y-m-d H:i:s') . " ===\n");
        
        return $results;
    }
    
    /**
     * Push intelligence to a single satellite
     * 
     * @param array $satellite Satellite configuration
     * @param array $data Intelligence data
     * @return array Push result
     */
    private function pushToSatellite(array $satellite, array $data): array
    {
        $startTime = microtime(true);
        $satelliteName = $satellite['name'] ?? $satellite['id'];
        
        $this->log("Pushing to: {$satelliteName}");
        
        // Prepare payload
        $payload = [
            'type' => 'intelligence_update',
            'timestamp' => time(),
            'source' => 'intelligence_hub',
            'data' => $data,
            'checksum' => md5(json_encode($data)),
        ];
        
        // Attempt push with retries
        $attempt = 0;
        $success = false;
        $error = null;
        
        while ($attempt < $this->maxRetries && !$success) {
            $attempt++;
            
            try {
                $response = $this->sendRequest(
                    $satellite['url'],
                    $payload,
                    $satellite['api_key']
                );
                
                if ($response['success']) {
                    $success = true;
                    $duration = round(microtime(true) - $startTime, 2);
                    $this->log("  ✓ Success in {$duration}s (attempt {$attempt})");
                } else {
                    $error = $response['error'] ?? 'Unknown error';
                    $this->log("  ✗ Failed: {$error}");
                    
                    if ($attempt < $this->maxRetries) {
                        $delay = pow(2, $attempt); // Exponential backoff
                        $this->log("  ⏳ Retrying in {$delay}s...");
                        sleep($delay);
                    }
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
                $this->log("  ✗ Exception: {$error}");
                
                if ($attempt < $this->maxRetries) {
                    $delay = pow(2, $attempt);
                    sleep($delay);
                }
            }
        }
        
        return [
            'satellite' => $satelliteName,
            'success' => $success,
            'attempts' => $attempt,
            'duration' => round(microtime(true) - $startTime, 2),
            'error' => $error,
            'timestamp' => date('Y-m-d H:i:s'),
        ];
    }
    
    /**
     * Send HTTP request via cURL
     * 
     * @param string $url Target URL
     * @param array $payload Data to send
     * @param string $apiKey Authentication key
     * @return array Response
     */
    private function sendRequest(string $url, array $payload, string $apiKey): array
    {
        $ch = curl_init($url);
        
        $jsonPayload = json_encode($payload);
        
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonPayload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-API-Key: ' . $apiKey,
                'X-Intelligence-Hub: hdgwrzntwa',
                'Content-Length: ' . strlen($jsonPayload),
            ],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        
        curl_close($ch);
        
        if ($curlError) {
            throw new Exception("cURL error: {$curlError}");
        }
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP {$httpCode}: " . substr($response, 0, 100));
        }
        
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response: " . json_last_error_msg());
        }
        
        return $decoded;
    }
    
    /**
     * Get push status for all satellites
     * 
     * @return array Status information
     */
    public function getStatus(): array
    {
        $status = [];
        
        foreach ($this->satellites as $satellite) {
            if (!($satellite['enabled'] ?? true)) {
                continue;
            }
            
            $status[] = [
                'id' => $satellite['id'],
                'name' => $satellite['name'],
                'url' => $satellite['url'],
                'enabled' => $satellite['enabled'] ?? true,
                'priority' => $satellite['priority'] ?? 99,
            ];
        }
        
        return $status;
    }
    
    /**
     * Test connectivity to all satellites
     * 
     * @return array Test results
     */
    public function testConnectivity(): array
    {
        $results = [];
        
        foreach ($this->satellites as $satellite) {
            if (!($satellite['enabled'] ?? true)) {
                continue;
            }
            
            $testUrl = str_replace('/receive', '/health', $satellite['url']);
            
            $ch = curl_init($testUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_HTTPHEADER => [
                    'X-API-Key: ' . $satellite['api_key'],
                ],
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
            curl_close($ch);
            
            $results[$satellite['id']] = [
                'name' => $satellite['name'],
                'reachable' => ($httpCode === 200),
                'http_code' => $httpCode,
                'response_time' => round($totalTime, 3),
            ];
        }
        
        return $results;
    }
    
    /**
     * Log message to file
     * 
     * @param string $message Message to log
     */
    private function log(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logLine = "[{$timestamp}] {$message}\n";
        
        // Ensure log directory exists
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        file_put_contents($this->logFile, $logLine, FILE_APPEND);
        
        // Also output to stdout if running from CLI
        if (php_sapi_name() === 'cli') {
            echo $logLine;
        }
    }
    
    /**
     * Format bytes for human reading
     * 
     * @param int $bytes Bytes
     * @return string Formatted size
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

// ============================================================================
// CLI Interface
// ============================================================================

if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    echo "Intelligence Distribution System - Push Mode\n";
    echo "============================================\n\n";
    
    $distributor = new IntelligenceDistributor();
    
    $command = $argv[1] ?? 'help';
    
    switch ($command) {
        case 'push':
            echo "Loading intelligence data...\n";
            
            // Load intelligence files
            $intelligenceDir = __DIR__ . '/../intelligence/';
            
            $data = [
                'summary' => json_decode(file_get_contents($intelligenceDir . 'SUMMARY.json'), true),
                'call_graph' => json_decode(file_get_contents($intelligenceDir . 'call_graph.json'), true),
                'file_index' => [
                    'count' => 3616,
                    'sample' => 'Full file index available via dedicated endpoint',
                ],
            ];
            
            echo "Pushing intelligence to satellites...\n\n";
            $results = $distributor->pushToAll($data);
            
            echo "\n=== PUSH RESULTS ===\n";
            foreach ($results as $id => $result) {
                $status = $result['success'] ? '✓' : '✗';
                echo "{$status} {$result['satellite']}: ";
                if ($result['success']) {
                    echo "Success in {$result['duration']}s ({$result['attempts']} attempts)\n";
                } else {
                    echo "Failed - {$result['error']}\n";
                }
            }
            break;
            
        case 'status':
            echo "Satellite Status:\n";
            $status = $distributor->getStatus();
            foreach ($status as $sat) {
                $enabled = $sat['enabled'] ? '✓' : '✗';
                echo "{$enabled} [{$sat['id']}] {$sat['name']}\n";
                echo "   URL: {$sat['url']}\n";
                echo "   Priority: {$sat['priority']}\n\n";
            }
            break;
            
        case 'test':
            echo "Testing connectivity to satellites...\n\n";
            $results = $distributor->testConnectivity();
            
            foreach ($results as $id => $result) {
                $status = $result['reachable'] ? '✓' : '✗';
                echo "{$status} {$result['name']}\n";
                echo "   HTTP {$result['http_code']}\n";
                echo "   Response time: {$result['response_time']}s\n\n";
            }
            break;
            
        default:
            echo "Usage: php intelligence_distributor.php [command]\n\n";
            echo "Commands:\n";
            echo "  push    - Push intelligence to all satellites\n";
            echo "  status  - Show satellite status\n";
            echo "  test    - Test connectivity to satellites\n";
            echo "  help    - Show this help\n";
            break;
    }
}
