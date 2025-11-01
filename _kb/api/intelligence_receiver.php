<?php
/**
 * Intelligence Receiver API - Satellite Endpoint
 * 
 * Receives intelligence data pushed from Intelligence Hub
 * Deploy this on ANY satellite application (CIS, retail sites, etc.)
 * 
 * Features:
 * - Receives intelligence via HTTP POST
 * - API key authentication
 * - Checksum verification
 * - Stores intelligence locally
 * - Health check endpoint
 * - Works across any server/provider
 * 
 * @package KB\API
 * @version 1.0.0
 */

declare(strict_types=1);

class IntelligenceReceiver
{
    private string $storageDir;
    private string $logFile;
    private array $allowedSources = ['intelligence_hub'];
    private string $apiKey;
    
    public function __construct()
    {
        // Configure storage paths
        $this->storageDir = __DIR__ . '/../_kb/intelligence/';
        $this->logFile = __DIR__ . '/../_kb/logs/receiver.log';
        
        // Load API key from environment or config
        $this->apiKey = getenv('KB_API_KEY') ?: $this->loadApiKey();
        
        // Ensure directories exist
        $this->ensureDirectories();
    }
    
    /**
     * Handle incoming request
     */
    public function handle(): void
    {
        // Set JSON response header
        header('Content-Type: application/json');
        
        // Route request
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        
        if (strpos($requestUri, '/health') !== false) {
            $this->handleHealth();
        } elseif (strpos($requestUri, '/receive') !== false) {
            $this->handleReceive();
        } elseif (strpos($requestUri, '/status') !== false) {
            $this->handleStatus();
        } else {
            $this->sendError(404, 'Endpoint not found');
        }
    }
    
    /**
     * Handle health check request
     */
    private function handleHealth(): void
    {
        $this->sendSuccess([
            'status' => 'healthy',
            'service' => 'kb_intelligence_receiver',
            'version' => '1.0.0',
            'timestamp' => date('Y-m-d H:i:s'),
            'storage_writable' => is_writable($this->storageDir),
        ]);
    }
    
    /**
     * Handle intelligence receive request
     */
    private function handleReceive(): void
    {
        // Verify authentication
        if (!$this->authenticate()) {
            $this->sendError(401, 'Authentication failed');
            return;
        }
        
        // Parse request body
        $rawBody = file_get_contents('php://input');
        $payload = json_decode($rawBody, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->sendError(400, 'Invalid JSON: ' . json_last_error_msg());
            return;
        }
        
        // Validate payload
        $validation = $this->validatePayload($payload);
        if (!$validation['valid']) {
            $this->sendError(400, $validation['error']);
            return;
        }
        
        // Verify checksum
        $dataChecksum = md5(json_encode($payload['data']));
        if ($dataChecksum !== $payload['checksum']) {
            $this->sendError(400, 'Checksum mismatch - data corrupted in transit');
            return;
        }
        
        // Store intelligence
        $result = $this->storeIntelligence($payload);
        
        if ($result['success']) {
            $this->log("Intelligence received successfully from {$payload['source']}");
            $this->log("  Files written: {$result['files_written']}");
            $this->log("  Total size: {$result['total_size']}");
            
            $this->sendSuccess([
                'message' => 'Intelligence received and stored',
                'files_written' => $result['files_written'],
                'total_size' => $result['total_size'],
                'timestamp' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $this->log("ERROR: Failed to store intelligence - {$result['error']}");
            $this->sendError(500, 'Failed to store intelligence: ' . $result['error']);
        }
    }
    
    /**
     * Handle status request
     */
    private function handleStatus(): void
    {
        // Verify authentication
        if (!$this->authenticate()) {
            $this->sendError(401, 'Authentication required');
            return;
        }
        
        // Get last received intelligence info
        $lastReceiveFile = $this->storageDir . 'last_receive.json';
        $lastReceive = file_exists($lastReceiveFile) 
            ? json_decode(file_get_contents($lastReceiveFile), true)
            : null;
        
        // Get storage info
        $files = glob($this->storageDir . '*.json');
        $totalSize = 0;
        foreach ($files as $file) {
            $totalSize += filesize($file);
        }
        
        $this->sendSuccess([
            'status' => 'operational',
            'last_receive' => $lastReceive,
            'stored_files' => count($files),
            'total_size' => $this->formatBytes($totalSize),
            'storage_path' => $this->storageDir,
            'writable' => is_writable($this->storageDir),
        ]);
    }
    
    /**
     * Authenticate request
     * 
     * @return bool Authentication result
     */
    private function authenticate(): bool
    {
        // Get API key from header
        $providedKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
        
        if (empty($providedKey)) {
            $this->log("Authentication failed: No API key provided");
            return false;
        }
        
        if ($providedKey !== $this->apiKey) {
            $this->log("Authentication failed: Invalid API key");
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate payload structure
     * 
     * @param array|null $payload Payload to validate
     * @return array Validation result
     */
    private function validatePayload(?array $payload): array
    {
        if (!$payload) {
            return ['valid' => false, 'error' => 'Empty payload'];
        }
        
        $required = ['type', 'timestamp', 'source', 'data', 'checksum'];
        
        foreach ($required as $field) {
            if (!isset($payload[$field])) {
                return ['valid' => false, 'error' => "Missing required field: {$field}"];
            }
        }
        
        if (!in_array($payload['source'], $this->allowedSources)) {
            return ['valid' => false, 'error' => "Unknown source: {$payload['source']}"];
        }
        
        // Verify timestamp is recent (within 1 hour)
        $age = time() - $payload['timestamp'];
        if ($age > 3600) {
            return ['valid' => false, 'error' => "Payload too old: {$age}s"];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Store intelligence data locally
     * 
     * @param array $payload Intelligence payload
     * @return array Storage result
     */
    private function storeIntelligence(array $payload): array
    {
        try {
            $data = $payload['data'];
            $filesWritten = 0;
            $totalSize = 0;
            
            // Store summary
            if (isset($data['summary'])) {
                $file = $this->storageDir . 'SUMMARY.json';
                $content = json_encode($data['summary'], JSON_PRETTY_PRINT);
                file_put_contents($file, $content);
                $filesWritten++;
                $totalSize += strlen($content);
            }
            
            // Store call graph
            if (isset($data['call_graph'])) {
                $file = $this->storageDir . 'call_graph.json';
                $content = json_encode($data['call_graph'], JSON_PRETTY_PRINT);
                file_put_contents($file, $content);
                $filesWritten++;
                $totalSize += strlen($content);
            }
            
            // Store any additional data
            foreach ($data as $key => $value) {
                if (!in_array($key, ['summary', 'call_graph'])) {
                    $file = $this->storageDir . $key . '.json';
                    $content = json_encode($value, JSON_PRETTY_PRINT);
                    file_put_contents($file, $content);
                    $filesWritten++;
                    $totalSize += strlen($content);
                }
            }
            
            // Store receive metadata
            $metadata = [
                'received_at' => date('Y-m-d H:i:s'),
                'source' => $payload['source'],
                'files_written' => $filesWritten,
                'total_size' => $totalSize,
                'checksum' => $payload['checksum'],
            ];
            
            file_put_contents(
                $this->storageDir . 'last_receive.json',
                json_encode($metadata, JSON_PRETTY_PRINT)
            );
            
            return [
                'success' => true,
                'files_written' => $filesWritten,
                'total_size' => $this->formatBytes($totalSize),
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    /**
     * Load API key from config
     * 
     * @return string API key
     */
    private function loadApiKey(): string
    {
        $configFile = __DIR__ . '/../_kb/config/api_key.txt';
        
        if (file_exists($configFile)) {
            return trim(file_get_contents($configFile));
        }
        
        // Generate and store new key
        $newKey = bin2hex(random_bytes(32));
        
        $configDir = dirname($configFile);
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }
        
        file_put_contents($configFile, $newKey);
        chmod($configFile, 0600); // Secure permissions
        
        $this->log("Generated new API key: {$configFile}");
        
        return $newKey;
    }
    
    /**
     * Ensure required directories exist
     */
    private function ensureDirectories(): void
    {
        $dirs = [
            $this->storageDir,
            dirname($this->logFile),
        ];
        
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
    
    /**
     * Send success response
     * 
     * @param array $data Response data
     */
    private function sendSuccess(array $data): void
    {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'data' => $data,
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    /**
     * Send error response
     * 
     * @param int $code HTTP status code
     * @param string $message Error message
     */
    private function sendError(int $code, string $message): void
    {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], JSON_PRETTY_PRINT);
        
        $this->log("ERROR {$code}: {$message}");
        exit;
    }
    
    /**
     * Log message
     * 
     * @param string $message Message to log
     */
    private function log(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $logLine = "[{$timestamp}] {$message}\n";
        
        file_put_contents($this->logFile, $logLine, FILE_APPEND);
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
// Handle Request
// ============================================================================

$receiver = new IntelligenceReceiver();
$receiver->handle();
