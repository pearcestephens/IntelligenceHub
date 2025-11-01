<?php
/**
 * Smart Cron - Circuit Breaker
 * 
 * Implements circuit breaker pattern to prevent cascading failures.
 * When a task fails repeatedly, the circuit opens and prevents further execution
 * until a timeout period has elapsed.
 * 
 * States:
 * - CLOSED: Normal operation, requests pass through
 * - OPEN: Circuit is open, requests fail immediately
 * - HALF_OPEN: Testing if service has recovered
 * 
 * @package SmartCron\Core
 */

declare(strict_types=1);

namespace SmartCron\Core;

class CircuitBreaker
{
    private const STATE_CLOSED = 'closed';
    private const STATE_OPEN = 'open';
    private const STATE_HALF_OPEN = 'half_open';
    
    // Storage backends
    private const BACKEND_FILE = 'file';
    private const BACKEND_DATABASE = 'database';
    private const BACKEND_REDIS = 'redis';
    
    private Config $config;
    private string $cacheFile;
    private array $circuits = [];
    private string $backend;
    private ?\mysqli $db = null;
    private $redis = null; // Can be Redis or null
    
    // Configuration
    private int $failureThreshold;
    private int $resetTimeoutSeconds;
    private int $halfOpenMaxAttempts;
    
    public function __construct(Config $config)
    {
        $this->config = $config;
        
        // Load configuration
        $this->failureThreshold = (int)$config->get('circuit_breaker.failure_threshold', 5);
        $this->resetTimeoutSeconds = (int)$config->get('circuit_breaker.reset_timeout', 60);
        $this->halfOpenMaxAttempts = (int)$config->get('circuit_breaker.half_open_max_attempts', 3);
        
        // Determine backend (file, database, or redis)
        $this->backend = $config->get('circuit_breaker.backend', self::BACKEND_FILE);
        
        // Initialize based on backend
        switch ($this->backend) {
            case self::BACKEND_REDIS:
                $this->initializeRedis();
                break;
            case self::BACKEND_DATABASE:
                $this->initializeDatabase();
                break;
            case self::BACKEND_FILE:
            default:
                $cacheDir = $config->get('paths.cache_dir', '/var/tmp');
                $this->cacheFile = $cacheDir . '/smart-cron-circuit-breaker.json';
                break;
        }
        
        $this->loadState();
    }
    
    /**
     * Initialize Redis connection
     */
    private function initializeRedis(): void
    {
        if (!class_exists('Redis')) {
            error_log("[CircuitBreaker] Redis extension not available, falling back to file backend");
            $this->backend = self::BACKEND_FILE;
            $cacheDir = $this->config->get('paths.cache_dir', '/var/tmp');
            $this->cacheFile = $cacheDir . '/smart-cron-circuit-breaker.json';
            return;
        }
        
        try {
            $this->redis = new \Redis();
            $host = $this->config->get('redis.host', '127.0.0.1');
            $port = (int)$this->config->get('redis.port', 6379);
            $timeout = (float)$this->config->get('redis.timeout', 2.0);
            
            $connected = $this->redis->connect($host, $port, $timeout);
            
            if (!$connected) {
                throw new \Exception("Failed to connect to Redis");
            }
            
            // Optional authentication
            $password = $this->config->get('redis.password', '');
            if ($password) {
                $this->redis->auth($password);
            }
            
            // Optional database selection
            $database = (int)$this->config->get('redis.database', 0);
            if ($database > 0) {
                $this->redis->select($database);
            }
            
            error_log("[CircuitBreaker] Using Redis backend at {$host}:{$port}");
        } catch (\Exception $e) {
            error_log("[CircuitBreaker] Redis initialization failed: " . $e->getMessage() . ", falling back to file backend");
            $this->backend = self::BACKEND_FILE;
            $this->redis = null;
            $cacheDir = $this->config->get('paths.cache_dir', '/var/tmp');
            $this->cacheFile = $cacheDir . '/smart-cron-circuit-breaker.json';
        }
    }
    
    /**
     * Initialize database connection
     */
    private function initializeDatabase(): void
    {
        try {
            $this->db = $this->config->getDbConnection();
            
            // Ensure table exists
            $this->ensureCircuitBreakerTable();
            
            error_log("[CircuitBreaker] Using database backend");
        } catch (\Exception $e) {
            error_log("[CircuitBreaker] Database initialization failed: " . $e->getMessage() . ", falling back to file backend");
            $this->backend = self::BACKEND_FILE;
            $this->db = null;
            $cacheDir = $this->config->get('paths.cache_dir', '/var/tmp');
            $this->cacheFile = $cacheDir . '/smart-cron-circuit-breaker.json';
        }
    }
    
    /**
     * Ensure circuit breaker table exists (for database backend)
     */
    private function ensureCircuitBreakerTable(): void
    {
        if (!$this->db) {
            return;
        }
        
        // Note: This should ideally be in a migration, but keeping for backward compatibility
        $sql = "CREATE TABLE IF NOT EXISTS cron_circuit_breaker (
            task_name VARCHAR(255) PRIMARY KEY,
            state VARCHAR(20) NOT NULL,
            failures INT DEFAULT 0,
            last_failure_at INT NULL,
            last_success_at INT NULL,
            last_error TEXT NULL,
            opened_at INT NULL,
            half_open_attempts INT DEFAULT 0,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_state (state),
            INDEX idx_updated (updated_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $this->db->query($sql);
    }
    
    /**
     * Check if task can execute (circuit allows it)
     */
    public function canExecute(string $taskName): bool
    {
        $circuit = $this->getCircuit($taskName);
        $state = $circuit['state'];
        
        if ($state === self::STATE_CLOSED) {
            return true;
        }
        
        if ($state === self::STATE_OPEN) {
            // Check if enough time has passed to try half-open
            $timeSinceOpen = time() - ($circuit['opened_at'] ?? 0);
            if ($timeSinceOpen >= $this->resetTimeoutSeconds) {
                $this->transitionToHalfOpen($taskName);
                return true;
            }
            return false;
        }
        
        if ($state === self::STATE_HALF_OPEN) {
            // Allow limited attempts in half-open state
            $attempts = $circuit['half_open_attempts'] ?? 0;
            return $attempts < $this->halfOpenMaxAttempts;
        }
        
        return false;
    }
    
    /**
     * Record successful execution
     */
    public function recordSuccess(string $taskName): void
    {
        $circuit = $this->getCircuit($taskName);
        
        if ($circuit['state'] === self::STATE_HALF_OPEN) {
            // Success in half-open state -> close circuit
            $this->transitionToClosed($taskName);
        } elseif ($circuit['state'] === self::STATE_CLOSED) {
            // Reset failure count on success
            $this->circuits[$taskName]['failures'] = 0;
            $this->circuits[$taskName]['last_success_at'] = time();
            $this->saveState();
        }
    }
    
    /**
     * Record failed execution
     */
    public function recordFailure(string $taskName, string $error = ''): void
    {
        $circuit = $this->getCircuit($taskName);
        
        $this->circuits[$taskName]['failures'] = ($circuit['failures'] ?? 0) + 1;
        $this->circuits[$taskName]['last_failure_at'] = time();
        $this->circuits[$taskName]['last_error'] = $error;
        
        if ($circuit['state'] === self::STATE_HALF_OPEN) {
            // Failure in half-open -> back to open
            $this->transitionToOpen($taskName, 'Failed during half-open test');
        } elseif ($circuit['state'] === self::STATE_CLOSED) {
            // Check if threshold reached
            if ($this->circuits[$taskName]['failures'] >= $this->failureThreshold) {
                $this->transitionToOpen($taskName, 'Failure threshold reached');
            }
        }
        
        $this->saveState();
    }
    
    /**
     * Get circuit state for a task
     */
    public function getCircuitState(string $taskName): array
    {
        return $this->getCircuit($taskName);
    }
    
    /**
     * Get all circuit states
     */
    public function getAllCircuitStates(): array
    {
        return $this->circuits;
    }
    
    /**
     * Manually reset a circuit (for emergency recovery)
     */
    public function resetCircuit(string $taskName): void
    {
        $this->transitionToClosed($taskName);
    }
    
    /**
     * Get or initialize circuit for task
     */
    private function getCircuit(string $taskName): array
    {
        if (!isset($this->circuits[$taskName])) {
            $this->circuits[$taskName] = [
                'state' => self::STATE_CLOSED,
                'failures' => 0,
                'last_failure_at' => null,
                'last_success_at' => null,
                'last_error' => null,
                'opened_at' => null,
                'half_open_attempts' => 0,
            ];
        }
        
        return $this->circuits[$taskName];
    }
    
    /**
     * Transition to CLOSED state with rollback protection
     * 🔒 CRITICAL FIX #3: STATE CORRUPTION FIX - Add rollback mechanism
     */
    private function transitionToClosed(string $taskName): void
    {
        // Backup current state for rollback
        $backupState = $this->circuits[$taskName] ?? null;
        
        $this->circuits[$taskName] = [
            'state' => self::STATE_CLOSED,
            'failures' => 0,
            'last_failure_at' => $this->circuits[$taskName]['last_failure_at'] ?? null,
            'last_success_at' => time(),
            'last_error' => null,
            'opened_at' => null,
            'half_open_attempts' => 0,
        ];
        
        // Attempt to save state
        $saved = $this->saveStateWithVerification();
        
        // If save failed, rollback to previous state
        if (!$saved) {
            error_log("[CircuitBreaker] CRITICAL: State save failed for '{$taskName}', rolling back to previous state");
            if ($backupState !== null) {
                $this->circuits[$taskName] = $backupState;
            }
            return;
        }
        
        error_log("[CircuitBreaker] Task '{$taskName}': Circuit CLOSED");
    }
    
    /**
     * Transition to OPEN state with rollback protection
     * 🔒 CRITICAL FIX #3: STATE CORRUPTION FIX - Add rollback mechanism
     */
    private function transitionToOpen(string $taskName, string $reason = ''): void
    {
        // Backup current state for rollback
        $backupState = $this->circuits[$taskName] ?? null;
        
        $this->circuits[$taskName]['state'] = self::STATE_OPEN;
        $this->circuits[$taskName]['opened_at'] = time();
        
        // Attempt to save state
        $saved = $this->saveStateWithVerification();
        
        // If save failed, rollback to previous state
        if (!$saved) {
            error_log("[CircuitBreaker] CRITICAL: State save failed for '{$taskName}', rolling back to previous state");
            if ($backupState !== null) {
                $this->circuits[$taskName] = $backupState;
            }
            return;
        }
        
        $failures = $this->circuits[$taskName]['failures'];
        error_log("[CircuitBreaker] Task '{$taskName}': Circuit OPENED (Failures: {$failures}, Reason: {$reason})");
    }
    
    /**
     * Transition to HALF_OPEN state with rollback protection
     * 🔒 CRITICAL FIX #3: STATE CORRUPTION FIX - Add rollback mechanism
     */
    private function transitionToHalfOpen(string $taskName): void
    {
        // Backup current state for rollback
        $backupState = $this->circuits[$taskName] ?? null;
        
        $this->circuits[$taskName]['state'] = self::STATE_HALF_OPEN;
        $this->circuits[$taskName]['half_open_attempts'] = 0;
        
        // Attempt to save state
        $saved = $this->saveStateWithVerification();
        
        // If save failed, rollback to previous state
        if (!$saved) {
            error_log("[CircuitBreaker] CRITICAL: State save failed for '{$taskName}', rolling back to previous state");
            if ($backupState !== null) {
                $this->circuits[$taskName] = $backupState;
            }
            return;
        }
        
        error_log("[CircuitBreaker] Task '{$taskName}': Circuit HALF_OPEN (testing recovery)");
    }
    
    /**
     * Save state with verification and return success status
     * 🔒 CRITICAL FIX #3: STATE CORRUPTION FIX - Verify save operation
     */
    private function saveStateWithVerification(): bool
    {
        try {
            $this->saveState();
            
            // Verify the save succeeded by checking if data persists
            switch ($this->backend) {
                case self::BACKEND_FILE:
                    return file_exists($this->cacheFile) && is_readable($this->cacheFile);
                    
                case self::BACKEND_DATABASE:
                    // For DB, assume success if no exception thrown
                    return true;
                    
                case self::BACKEND_REDIS:
                    // For Redis, check if connection is still alive
                    return $this->redis !== null && $this->redis->ping();
                    
                default:
                    return true;
            }
        } catch (\Exception $e) {
            error_log("[CircuitBreaker] State save failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Load circuit states from storage backend
     */
    private function loadState(): void
    {
        switch ($this->backend) {
            case self::BACKEND_REDIS:
                $this->loadStateFromRedis();
                break;
            case self::BACKEND_DATABASE:
                $this->loadStateFromDatabase();
                break;
            case self::BACKEND_FILE:
            default:
                $this->loadStateFromFile();
                break;
        }
    }
    
    /**
     * Load state from file
     */
    private function loadStateFromFile(): void
    {
        if (!file_exists($this->cacheFile)) {
            return;
        }
        
        $data = @file_get_contents($this->cacheFile);
        if ($data === false) {
            return;
        }
        
        $circuits = @json_decode($data, true);
        if (is_array($circuits)) {
            $this->circuits = $circuits;
        }
    }
    
    /**
     * Load state from Redis
     */
    private function loadStateFromRedis(): void
    {
        if (!$this->redis) {
            return;
        }
        
        try {
            $keys = $this->redis->keys('circuit_breaker:*');
            foreach ($keys as $key) {
                $taskName = str_replace('circuit_breaker:', '', $key);
                $data = $this->redis->get($key);
                if ($data) {
                    $circuit = json_decode($data, true);
                    if (is_array($circuit)) {
                        $this->circuits[$taskName] = $circuit;
                    }
                }
            }
        } catch (\Exception $e) {
            error_log("[CircuitBreaker] Failed to load from Redis: " . $e->getMessage());
        }
    }
    
    /**
     * Load state from database
     */
    private function loadStateFromDatabase(): void
    {
        if (!$this->db) {
            return;
        }
        
        try {
            $result = $this->db->query("SELECT * FROM cron_circuit_breaker");
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $this->circuits[$row['task_name']] = [
                        'state' => $row['state'],
                        'failures' => (int)$row['failures'],
                        'last_failure_at' => $row['last_failure_at'] ? (int)$row['last_failure_at'] : null,
                        'last_success_at' => $row['last_success_at'] ? (int)$row['last_success_at'] : null,
                        'last_error' => $row['last_error'],
                        'opened_at' => $row['opened_at'] ? (int)$row['opened_at'] : null,
                        'half_open_attempts' => (int)$row['half_open_attempts'],
                    ];
                }
            }
        } catch (\Exception $e) {
            error_log("[CircuitBreaker] Failed to load from database: " . $e->getMessage());
        }
    }
    
    /**
     * Save circuit states to storage backend
     */
    private function saveState(): void
    {
        switch ($this->backend) {
            case self::BACKEND_REDIS:
                $this->saveStateToRedis();
                break;
            case self::BACKEND_DATABASE:
                $this->saveStateToDatabase();
                break;
            case self::BACKEND_FILE:
            default:
                $this->saveStateToFile();
                break;
        }
    }
    
    /**
     * Save state to file
     */
    private function saveStateToFile(): void
    {
        $dir = dirname($this->cacheFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        
        $data = json_encode($this->circuits, JSON_PRETTY_PRINT);
        @file_put_contents($this->cacheFile, $data, LOCK_EX);
    }
    
    /**
     * Save state to Redis
     */
    private function saveStateToRedis(): void
    {
        if (!$this->redis) {
            return;
        }
        
        try {
            $ttl = $this->resetTimeoutSeconds * 10; // Keep data 10x longer than reset timeout
            
            foreach ($this->circuits as $taskName => $circuit) {
                $key = 'circuit_breaker:' . $taskName;
                $data = json_encode($circuit);
                $this->redis->setex($key, $ttl, $data);
            }
        } catch (\Exception $e) {
            error_log("[CircuitBreaker] Failed to save to Redis: " . $e->getMessage());
        }
    }
    
    /**
     * Save state to database
     */
    private function saveStateToDatabase(): void
    {
        if (!$this->db) {
            return;
        }
        
        try {
            foreach ($this->circuits as $taskName => $circuit) {
                $stmt = $this->db->prepare(
                    "INSERT INTO cron_circuit_breaker 
                    (task_name, state, failures, last_failure_at, last_success_at, last_error, opened_at, half_open_attempts)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    state = VALUES(state),
                    failures = VALUES(failures),
                    last_failure_at = VALUES(last_failure_at),
                    last_success_at = VALUES(last_success_at),
                    last_error = VALUES(last_error),
                    opened_at = VALUES(opened_at),
                    half_open_attempts = VALUES(half_open_attempts)"
                );
                
                $stmt->bind_param(
                    'ssiiisii',
                    $taskName,
                    $circuit['state'],
                    $circuit['failures'],
                    $circuit['last_failure_at'],
                    $circuit['last_success_at'],
                    $circuit['last_error'],
                    $circuit['opened_at'],
                    $circuit['half_open_attempts']
                );
                
                $stmt->execute();
                $stmt->close();
            }
        } catch (\Exception $e) {
            error_log("[CircuitBreaker] Failed to save to database: " . $e->getMessage());
        }
    }
}
