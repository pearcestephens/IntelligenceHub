<?php
/**
 * Smart Cron - Configuration Manager
 * 
 * Manages all configuration settings for Smart Cron system.
 * 
 * @package SmartCron\Core
 */

declare(strict_types=1);

namespace SmartCron\Core;

class Config
{
    private array $config = [];
    private array $settings = [];
    private string $configFile;
    private string $settingsFile;
    private static ?array $configCache = null;
    private static ?int $cacheTimestamp = null;
    private const CACHE_TTL = 300; // 5 minutes
    
    public function __construct()
    {
        $this->configFile = dirname(__DIR__) . '/config/config.json';
        $this->settingsFile = dirname(__DIR__) . '/config/settings.json';
        $this->load();
        $this->loadSettings();
    }
    
    /**
     * Load configuration from file
     */
    private function load(): void
    {
        // Check cache first
        if (self::$configCache !== null && self::$cacheTimestamp !== null) {
            if ((time() - self::$cacheTimestamp) < self::CACHE_TTL) {
                $this->config = self::$configCache;
                return;
            }
        }
        
        if (file_exists($this->configFile)) {
            $json = file_get_contents($this->configFile);
            $this->config = json_decode($json, true);
            
            if ($this->config === null && json_last_error() !== JSON_ERROR_NONE) {
                error_log('[Config] JSON decode error: ' . json_last_error_msg());
                $this->config = [];
            }
        }
        
        // Set defaults if not configured
        $this->setDefaults();
        $this->validateConfig();
        
        // Cache the config
        self::$configCache = $this->config;
        self::$cacheTimestamp = time();
    }
    
    /**
     * Load settings from settings.json
     */
    private function loadSettings(): void
    {
        if (file_exists($this->settingsFile)) {
            $this->settings = json_decode(file_get_contents($this->settingsFile), true) ?? [];
        }
    }
    
    /**
     * Get a setting value by dot notation path
     * Example: getSetting('logging.level') returns 'info'
     */
    public function getSetting(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->settings;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
    
    /**
     * Get all settings
     */
    public function getAllSettings(): array
    {
        return $this->settings;
    }
    
    /**
     * Update settings and save to file
     */
    public function saveSettings(array $newSettings): bool
    {
        $this->settings = $newSettings;
        $this->settings['metadata']['updated_at'] = date('c');
        
        $json = json_encode($this->settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        return file_put_contents($this->settingsFile, $json) !== false;
    }
    
    /**
     * Set default configuration values
     */
    private function setDefaults(): void
    {
        // Simplified credential detection: .env > constants > defaults
        $getCredential = function(string $envKey, string $constantKey, string $default) {
            // Priority 1: Environment variable
            $value = getenv($envKey);
            if ($value !== false && $value !== '') {
                return $value;
            }
            
            // Priority 2: PHP constant
            if (defined($constantKey)) {
                return constant($constantKey);
            }
            
            // Priority 3: Default
            return $default;
        };
        
        $host = $getCredential('DB_HOST', 'DB_HOST', '127.0.0.1');
        $user = $getCredential('DB_USER', 'DB_USER', 'jcepnzzkmj');
        $pass = $getCredential('DB_PASS', 'DB_PASS', '');
        $db = $getCredential('DB_NAME', 'DB_NAME', 'jcepnzzkmj');
        
        $defaults = [
            'db' => [
                'host' => $host,
                'database' => $db,
                'username' => $user,
                'password' => $pass,
                'charset' => 'utf8mb4',
            ],
            'metrics' => [
                'retention_days' => 90,
                'collection_enabled' => true,
            ],
            'scheduling' => [
                'heavy_task_window' => ['02:00', '04:00'],  // Heavy tasks run 2-4 AM
                'medium_task_window' => ['00:00', '06:00'], // Medium tasks off-peak
                'auto_optimize_frequency' => 'weekly',       // How often to re-optimize
            ],
            'load_balancer' => [
                'max_concurrent_heavy' => 2,    // Up to 2 heavy tasks at once
                'max_concurrent_medium' => 5,   // Up to 5 medium tasks
                'max_concurrent_light' => 15,   // Up to 15 light tasks
                'cpu_threshold' => 80,          // Pause if CPU > 80%
                'memory_threshold' => 85,       // Pause if memory > 85%
            ],
            'thresholds' => [
                'heavy_duration_seconds' => 60,   // Tasks > 60s are "heavy"
                'heavy_memory_mb' => 512,         // Tasks > 512MB are "heavy"
                'medium_duration_seconds' => 10,  // Tasks > 10s are "medium"
                'medium_memory_mb' => 100,        // Tasks > 100MB are "medium"
            ],
            'paths' => [
                // ✅ ABSOLUTE PATHS ONLY - NO SYMLINKS!
                // NEVER use /home/master/applications/ (it's a symlink to /home/129337.cloudwaysapps.com/)
                // ALWAYS use the REAL absolute path: /home/129337.cloudwaysapps.com/jcepnzzkmj/public_html
                'root' => $_SERVER['DOCUMENT_ROOT'] ?? '/home/129337.cloudwaysapps.com/jcepnzzkmj/public_html',
                'project_root' => $_SERVER['DOCUMENT_ROOT'] ?? '/home/129337.cloudwaysapps.com/jcepnzzkmj/public_html',
                'logs' => dirname(__DIR__) . '/logs',
                'tasks_config' => dirname(__DIR__) . '/config/tasks.json',
            ],
            'alerts' => [
                'email_from' => getenv('ALERT_EMAIL_FROM') ?: 'smart-cron@vapeshed.co.nz',
            ],
            'business_hours' => [
                'start' => (int)(getenv('BUSINESS_HOURS_START') ?: 8),
                'end' => (int)(getenv('BUSINESS_HOURS_END') ?: 23),
            ],
        ];
        
        // Merge with existing config (existing values take precedence)
        $this->config = array_replace_recursive($defaults, $this->config);
    }
    
    /**
     * Validate required configuration
     */
    private function validateConfig(): void
    {
        $required = [
            'db.host',
            'db.database',
            'db.username',
            'paths.root',
            'paths.logs',
        ];
        
        foreach ($required as $key) {
            $value = $this->get($key);
            if ($value === null || $value === '') {
                error_log("[Config] Warning: Required config '{$key}' is not set");
            }
        }
        
        // Validate numeric ranges
        $numericValidations = [
            'load_balancer.cpu_threshold' => [0, 100],
            'load_balancer.memory_threshold' => [0, 100],
            'business_hours.start' => [0, 23],
            'business_hours.end' => [0, 23],
        ];
        
        foreach ($numericValidations as $key => $range) {
            $value = $this->get($key);
            if ($value !== null && ($value < $range[0] || $value > $range[1])) {
                error_log("[Config] Warning: '{$key}' value {$value} outside valid range [{$range[0]}, {$range[1]}]");
            }
        }
    }
    
    /**
     * Get configuration value
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }
        
        return $value;
    }
    
    /**
     * Set configuration value
     */
    public function set(string $key, $value): void
    {
        $keys = explode('.', $key);
        $config = &$this->config;
        
        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                $config[$k] = $value;
            } else {
                if (!isset($config[$k]) || !is_array($config[$k])) {
                    $config[$k] = [];
                }
                $config = &$config[$k];
            }
        }
    }
    
    /**
     * Save configuration to file
     */
    public function save(): void
    {
        $dir = dirname($this->configFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents(
            $this->configFile,
            json_encode($this->config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }
    
    /**
     * Get database connection - Enhanced with health checking
     * Uses constants defined in smart-cron.php
     * 
     * 🔒 CRITICAL FIX #2: CONNECTION LEAK FIX - Add ping, reconnect, and health checks
     */
    public function getDbConnection(): ?\mysqli
    {
        static $connection = null;
        static $lastPingTime = null;
        static $connectionAttempts = 0;
        static $maxAttempts = 3;
        
        // Health check: ping every 60 seconds
        if ($connection !== null && $lastPingTime !== null) {
            $timeSinceLastPing = time() - $lastPingTime;
            
            if ($timeSinceLastPing > 60) {
                try {
                    if (!$connection->ping()) {
                        error_log('[Config] DB connection lost (ping failed), reconnecting...');
                        $connection->close();
                        $connection = null;
                        $lastPingTime = null;
                    } else {
                        $lastPingTime = time();
                        error_log('[Config] DB connection healthy (ping successful)');
                    }
                } catch (\Exception $e) {
                    error_log('[Config] DB ping error: ' . $e->getMessage() . ', reconnecting...');
                    $connection = null;
                    $lastPingTime = null;
                }
            }
        }
        
        // Return cached connection if still valid
        if ($connection !== null) {
            return $connection;
        }
        
        // Prevent infinite reconnection attempts
        if ($connectionAttempts >= $maxAttempts) {
            error_log('[Config] Max DB connection attempts reached, giving up');
            return null;
        }
        
        $connectionAttempts++;
        
        // Use hardcoded constants from smart-cron.php
        $host = defined('DB_HOST') ? DB_HOST : 'localhost';
        $user = defined('DB_USER') ? DB_USER : 'jcepnzzkmj';
        $pass = defined('DB_PASS') ? DB_PASS : 'wprKh9Jq63';
        $dbName = defined('DB_NAME') ? DB_NAME : 'jcepnzzkmj';
        $port = defined('DB_PORT') ? (int)DB_PORT : 3306;
        
        // Try direct mysqli connection with socket support
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            
            // First try with 'localhost' which will use socket if available
            $connection = new \mysqli($host, $user, $pass, $dbName, $port);
            $connection->set_charset('utf8mb4');
            
            // Set connection timeout and wait_timeout to prevent stale connections
            $connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
            $connection->query("SET SESSION wait_timeout = 28800"); // 8 hours
            $connection->query("SET SESSION interactive_timeout = 28800"); // 8 hours
            
            // Test the connection
            $connection->query("SELECT 1");
            
            $lastPingTime = time();
            $connectionAttempts = 0; // Reset counter on success
            
            error_log('[Config] DB connection established successfully');
            
            return $connection;
            
        } catch (\mysqli_sql_exception $e) {
            error_log('[Config] DB connection failed (attempt ' . $connectionAttempts . '/' . $maxAttempts . '): ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Close database connection (for graceful shutdown)
     */
    public function closeDbConnection(): void
    {
        // Access the static connection via reflection or add a getter
        $reflection = new \ReflectionMethod($this, 'getDbConnection');
        $reflection->setAccessible(true);
        
        // Get static variables
        $staticVars = $reflection->getStaticVariables();
        if (isset($staticVars['connection']) && $staticVars['connection'] !== null) {
            try {
                $staticVars['connection']->close();
                error_log('[Config] DB connection closed gracefully');
            } catch (\Exception $e) {
                error_log('[Config] Error closing DB connection: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Get all tasks from tasks.json
     */
    public function getTasks(): array
    {
        $tasksFile = dirname(__DIR__) . '/config/tasks.json';
        
        if (!file_exists($tasksFile)) {
            error_log('[Config] Tasks file not found: ' . $tasksFile);
            return [];
        }
        
        $json = file_get_contents($tasksFile);
        $data = json_decode($json, true);
        
        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            error_log('[Config] Tasks JSON decode error: ' . json_last_error_msg());
            return [];
        }
        
        return $data['tasks'] ?? [];
    }
    
    /**
     * Get enabled tasks only
     */
    public function getEnabledTasks(): array
    {
        return array_filter($this->getTasks(), function($task) {
            return isset($task['enabled']) && $task['enabled'] === true;
        });
    }
    
    /**
     * Get task by name
     */
    public function getTask(string $name): ?array
    {
        $tasks = $this->getTasks();
        foreach ($tasks as $task) {
            if ($task['name'] === $name) {
                return $task;
            }
        }
        return null;
    }
}
