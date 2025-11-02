<?php
/**
 * Smart Cron - Metrics Collector
 *
 * Captures execution time, CPU usage, and memory usage for all cron tasks.
 * Stores metrics in database for analysis and optimization.
 *
 * @package SmartCron\Core
 */

declare(strict_types=1);

namespace SmartCron\Core;

class MetricsCollector
{
    private Config $config;
    private ?\mysqli $db;
    private ?CircuitBreaker $circuitBreaker = null;
    private ?AlertManager $alertManager = null;
    private array $lockHandles = [];
    private string $lockDir;

    public function __construct(Config $config, ?CircuitBreaker $circuitBreaker = null, ?AlertManager $alertManager = null)
    {
        $this->config = $config;
        $this->db = $config->getDbConnection();
        $this->circuitBreaker = $circuitBreaker;
        $this->alertManager = $alertManager;

        // Initialize lock directory
        $this->lockDir = $config->get('paths.lock_dir', __DIR__ . '/../../logs/locks');
        if (!is_dir($this->lockDir)) {
            mkdir($this->lockDir, 0755, true);
        }

        if ($this->db === null) {
            error_log('[MetricsCollector] Warning: No database connection available. Metrics will not be stored.');
        }

        // Table creation moved to migrations/001_create_cron_metrics.php
        // Run: php migrations/run_migrations.php
    }

    /**
     * Execute a task with retry logic and circuit breaker
     *
     * @param array $task Task configuration
     * @param bool $forceBypassCircuitBreaker Force execution even if circuit breaker is open
     * @return array Execution result with metrics
     */
    public function executeTask(array $task, bool $forceBypassCircuitBreaker = false): array
    {
        $taskName = $task['name'];

        // 🔒 CRITICAL FIX #1: TASK LOCKING - Prevent simultaneous execution
        $lockAcquired = $this->acquireTaskLock($taskName);
        if (!$lockAcquired) {
            error_log("[MetricsCollector] Task '{$taskName}' is already running. Skipping this execution.");
            return [
                'success' => false,
                'error' => 'Task already running (lock acquired by another process)',
                'duration' => 0,
                'memory_mb' => 0,
                'cpu_peak' => 0,
                'locked' => true,
                'skipped' => true
            ];
        }

        // Ensure lock is released on exit
        try {
            return $this->executeTaskInternal($task, $forceBypassCircuitBreaker);
        } finally {
            $this->releaseTaskLock($taskName);
        }
    }

    /**
     * Acquire exclusive lock for task execution
     */
    private function acquireTaskLock(string $taskName): bool
    {
        $lockFile = $this->lockDir . '/' . md5($taskName) . '.lock';
        $handle = fopen($lockFile, 'c');

        if ($handle === false) {
            error_log("[MetricsCollector] Failed to open lock file: {$lockFile}");
            return false;
        }

        // Try to acquire exclusive non-blocking lock
        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);
            return false;
        }

        // Write PID and timestamp to lock file for debugging
        ftruncate($handle, 0);
        fwrite($handle, sprintf("PID: %d\nTask: %s\nLocked: %s\n",
            getmypid(),
            $taskName,
            date('Y-m-d H:i:s')
        ));
        fflush($handle);

        $this->lockHandles[$taskName] = $handle;
        return true;
    }

    /**
     * Release task lock
     */
    private function releaseTaskLock(string $taskName): void
    {
        if (!isset($this->lockHandles[$taskName])) {
            return;
        }

        $handle = $this->lockHandles[$taskName];
        flock($handle, LOCK_UN);
        fclose($handle);
        unset($this->lockHandles[$taskName]);

        // Clean up lock file
        $lockFile = $this->lockDir . '/' . md5($taskName) . '.lock';
        @unlink($lockFile);
    }

    /**
     * Internal execution logic (after lock acquired)
     */
    private function executeTaskInternal(array $task, bool $forceBypassCircuitBreaker): array
    {
        $taskName = $task['name'];

        // 🔥 CRITICAL: Heartbeat tasks ALWAYS bypass circuit breakers
        // This ensures the dashboard always receives updates even when other tasks fail
        $isHeartbeat = (stripos($taskName, 'heartbeat') !== false) ||
                       (isset($task['bypass_circuit_breaker']) && $task['bypass_circuit_breaker'] === true);

        // 🚀 HIGH PRIORITY FIX #7: Force flag implementation
        $shouldBypassCircuitBreaker = $isHeartbeat || $forceBypassCircuitBreaker;

        // Check circuit breaker first (unless bypassed)
        if (!$shouldBypassCircuitBreaker && $this->circuitBreaker && !$this->circuitBreaker->canExecute($taskName)) {
            $circuit = $this->circuitBreaker->getCircuitState($taskName);
            $error = "Circuit breaker OPEN (failures: {$circuit['failures']}, opened at: " .
                     date('Y-m-d H:i:s', $circuit['opened_at'] ?? 0) . ")";

            error_log("[MetricsCollector] Task '{$taskName}' blocked by circuit breaker");
            $this->recordSkip($taskName, $error);

            return [
                'success' => false,
                'error' => $error,
                'duration' => 0,
                'memory_mb' => 0,
                'cpu_peak' => 0,
                'circuit_breaker_blocked' => true,
            ];
        }

        if ($shouldBypassCircuitBreaker) {
            error_log("[MetricsCollector] ❤️ Task '{$taskName}' bypassing circuit breaker " .
                     ($forceBypassCircuitBreaker ? "(FORCED)" : "(heartbeat)"));
        }

        // Determine retry settings based on task type
        $taskType = $task['type'] ?? 'medium';
        $maxAttempts = match($taskType) {
            'critical' => 3,  // Critical tasks get 3 attempts
            'heavy' => 2,     // Heavy tasks get 2 attempts
            'medium' => 2,    // Medium tasks get 2 attempts
            'light' => 1,     // Light tasks run once
            default => 1
        };

        // Enable retry from config
        $retryEnabled = (bool)$this->config->get('execution.retry.enabled', true);
        if (!$retryEnabled) {
            $maxAttempts = 1;
        }

        // Execute with retry
        $result = $this->executeWithRetry($task, $maxAttempts);

        // Update circuit breaker
        if ($this->circuitBreaker) {
            if ($result['success']) {
                $this->circuitBreaker->recordSuccess($taskName);
            } else {
                $this->circuitBreaker->recordFailure($taskName, $result['error'] ?? 'Unknown error');
            }
        }

        return $result;
    }

    /**
     * Execute task with exponential backoff retry
     */
    private function executeWithRetry(array $task, int $maxAttempts): array
    {
        $attempt = 0;
        $lastResult = null;

        while ($attempt < $maxAttempts) {
            $attempt++;

            if ($attempt > 1) {
                // Exponential backoff: 1s, 2s, 4s
                $sleepSeconds = pow(2, $attempt - 2);
                error_log("[MetricsCollector] Task '{$task['name']}' attempt {$attempt}/{$maxAttempts} after {$sleepSeconds}s backoff");
                sleep($sleepSeconds);
            }

            $result = $this->executeSingleAttempt($task, $attempt);
            $lastResult = $result;

            if ($result['success']) {
                if ($attempt > 1) {
                    error_log("[MetricsCollector] Task '{$task['name']}' succeeded on attempt {$attempt}");
                }
                return $result;
            }

            // Log failure
            error_log("[MetricsCollector] Task '{$task['name']}' attempt {$attempt} failed: " . ($result['error'] ?? 'unknown'));
        }

        // All attempts failed
        error_log("[MetricsCollector] Task '{$task['name']}' FAILED after {$maxAttempts} attempts");
        $lastResult['final_failure'] = true;
        $lastResult['attempts'] = $maxAttempts;

        // Send alert on final failure
        if ($this->alertManager !== null) {
            $severity = ($task['type'] ?? 'light') === 'critical' ? 'critical' : 'high';
            $this->alertManager->sendAlert(
                $severity,
                "Task '{$task['name']}' failed after {$maxAttempts} attempts",
                [
                    'task_name' => $task['name'],
                    'task_type' => $task['type'] ?? 'unknown',
                    'attempts' => $maxAttempts,
                    'exit_code' => $lastResult['exit_code'] ?? -1,
                    'error' => $lastResult['error'] ?? 'Unknown error',
                    'duration' => $lastResult['duration'] ?? 0
                ]
            );
        }

        return $lastResult;
    }

    /**
     * Execute a single attempt of the task
     */
    private function executeSingleAttempt(array $task, int $attemptNumber = 1): array
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        $taskName = $task['name'] ?? 'unknown_task';

        // Parse script path and extract embedded arguments
        $scriptField = $task['script'];
        $embeddedArgs = '';

        // Check if script field contains arguments (space-separated)
        if (strpos($scriptField, ' ') !== false) {
            $parts = explode(' ', $scriptField, 2);
            $scriptPath = $parts[0];
            $embeddedArgs = $parts[1];
            error_log("[TaskExecutor] 🔍 Detected embedded args in script field: '{$embeddedArgs}'");
        } else {
            $scriptPath = $scriptField;
        }

        // Resolve script path - supports absolute, relative, and symlinks
        $script = $this->resolveScriptPath($scriptPath);

        if (!$script) {
            return [
                'success' => false,
                'error' => "Script not found: {$scriptPath} (original: {$scriptField})",
                'duration' => 0,
                'memory_mb' => 0,
                'cpu_peak' => 0,
                'debug' => [
                    'script_field' => $scriptField,
                    'script_path' => $scriptPath,
                    'embedded_args' => $embeddedArgs,
                    'resolved_path' => null
                ]
            ];
        }

        error_log("[TaskExecutor] ✅ Script resolved: {$script}" . ($embeddedArgs ? " (args: {$embeddedArgs})" : ""));

        // Determine script type (PHP vs Bash)
        $extension = pathinfo($script, PATHINFO_EXTENSION);
        $isBash = in_array($extension, ['sh', 'bash']);
        $isPhp = in_array($extension, ['php']);

        // Add timeout if configured
        $timeout = $task['timeout'] ?? $this->config->get('execution.timeout', 300);

        // � CRITICAL FIX #4: TIMEOUT ENFORCEMENT - Kill process tree, not just parent
        // --kill-after ensures child processes are terminated too
        $killAfter = min(5, max(1, (int)($timeout * 0.1))); // 10% of timeout or 1-5 seconds

        // �🚨 OPERATIONAL STANDARD: DISABLE OPCACHE FOR ALL CRON JOBS
        // Ensures fresh code execution without cached bytecode
        $opcacheDisable = 'php -d opcache.enable=0 -d opcache.enable_cli=0';

        // Build command based on script type
        if ($isBash) {
            $command = "timeout --kill-after={$killAfter} {$timeout} /bin/bash " . escapeshellarg($script);
            error_log("[TaskExecutor] 🐚 Bash script detected");
        } elseif ($isPhp) {
            $command = "timeout --kill-after={$killAfter} {$timeout} {$opcacheDisable} " . escapeshellarg($script);
            error_log("[TaskExecutor] 🐘 PHP script detected (OPcache DISABLED)");
        } else {
            // Try to detect shebang
            $firstLine = file_exists($script) ? fgets(fopen($script, 'r')) : '';
            if (strpos($firstLine, '#!/') === 0) {
                // Check if it's a PHP shebang
                if (strpos($firstLine, 'php') !== false) {
                    $command = "timeout --kill-after={$killAfter} {$timeout} {$opcacheDisable} " . escapeshellarg($script);
                    error_log("[TaskExecutor] 🔧 PHP executable with shebang (OPcache DISABLED)");
                } else {
                    // Make executable and run directly (non-PHP)
                    chmod($script, 0755);
                    $command = "timeout --kill-after={$killAfter} {$timeout} " . escapeshellarg($script);
                    error_log("[TaskExecutor] 🔧 Executable script with shebang detected");
                }
            } else {
                // Default to PHP with OPcache disabled
                $command = "timeout --kill-after={$killAfter} {$timeout} {$opcacheDisable} " . escapeshellarg($script);
                error_log("[TaskExecutor] ⚠️ Unknown script type, defaulting to PHP (OPcache DISABLED)");
            }
        }

        // Add embedded arguments first (from script field)
        if (!empty($embeddedArgs)) {
            $command .= " " . $embeddedArgs;  // Don't escape - might be multiple args
        }

        // Add explicit args from task config (higher priority)
        if (isset($task['args']) && !empty($task['args'])) {
            $command .= " " . $task['args'];
        }

        // Execute with output capture
        error_log("[TaskExecutor] 📋 Executing: {$command}");

        // 🔒 CRITICAL FIX #9: ACCURATE MEMORY MEASUREMENT - Track child process memory
        $pidFile = sys_get_temp_dir() . '/smart_cron_' . md5($taskName) . '.pid';

        // Use process substitution to capture PID and monitor memory
        $fullCommand = sprintf(
            '((%s) & echo $! > %s; wait)',
            $command . " 2>&1",
            escapeshellarg($pidFile)
        );

        $output = [];
        $exitCode = 0;

        // Start execution
        $startExec = microtime(true);
        exec($fullCommand, $output, $exitCode);
        $execDuration = microtime(true) - $startExec;

        // Try to get actual child process memory usage
        $childMemoryMb = 0;
        if (file_exists($pidFile)) {
            $childPid = (int)trim(file_get_contents($pidFile));
            @unlink($pidFile);

            // Check if we captured memory stats (Linux /proc)
            $procStatm = "/proc/{$childPid}/statm";
            if (file_exists($procStatm)) {
                $statm = file_get_contents($procStatm);
                $parts = explode(' ', $statm);
                // VmRSS is in pages (second field), convert to MB
                $pageSize = 4096; // Standard Linux page size
                $childMemoryMb = round(($parts[1] ?? 0) * $pageSize / 1024 / 1024, 2);
                error_log("[TaskExecutor] 📊 Child process memory (RSS): {$childMemoryMb} MB");
            }
        }

        // Fallback to parent process memory if child tracking failed
        $memoryMb = $childMemoryMb;
        if ($childMemoryMb <= 0) {
            $memoryUsed = memory_get_usage(true) - $startMemory;
            $memoryMb = round($memoryUsed / 1024 / 1024, 2);
            error_log("[TaskExecutor] ⚠️ Using parent process memory (fallback): {$memoryMb} MB");
        }

        // Calculate duration
        $duration = round(microtime(true) - $startTime, 3);

        // Try to get CPU usage (Linux only)
        $cpuPeak = $this->getCpuUsage();

        $success = ($exitCode === 0);

        // Enhanced output logging
        $outputLineCount = count($output);
        error_log("[TaskExecutor] 📊 Output: {$outputLineCount} lines, Exit Code: {$exitCode}, Duration: {$duration}s");

        if ($outputLineCount > 0) {
            error_log("[TaskExecutor] 📝 First line: " . ($output[0] ?? '(empty)'));
            if ($outputLineCount > 1) {
                error_log("[TaskExecutor] 📝 Last line: " . ($output[$outputLineCount - 1] ?? '(empty)'));
            }
        } else {
            error_log("[TaskExecutor] ⚠️ WARNING: Script produced NO output (silent execution)");
        }

        if (!$success) {
            error_log("[TaskExecutor] ❌ FAILED with exit code {$exitCode}");
            if ($outputLineCount > 0) {
                error_log("[TaskExecutor] 🔍 Last 5 lines of output:");
                foreach (array_slice($output, -5) as $line) {
                    error_log("[TaskExecutor]    > " . $line);
                }
            }
        }

        $errorMessage = $success ? null : implode("\n", array_slice($output, -10)); // Last 10 lines

        // Store metrics (only on final attempt or success)
        if ($success || $attemptNumber === 1) {  // Store first attempt always
            $this->storeMetrics(
                $task['name'],
                $duration,
                $memoryMb,
                $cpuPeak,
                $exitCode,
                $success,
                $errorMessage,
                $output  // ← ADD: Store full output array
            );
        }

        return [
            'success' => $success,
            'duration' => $duration,
            'memory_mb' => $memoryMb,
            'cpu_peak' => $cpuPeak,
            'exit_code' => $exitCode,
            'error' => $errorMessage,
            'output' => $output,
            'attempt' => $attemptNumber,
        ];
    }

    /**
     * Store metrics in database
     */
    private function storeMetrics(
        string $taskName,
        float $duration,
        float $memoryMb,
        ?float $cpuPeak,
        int $exitCode,
        bool $success,
        ?string $errorMessage,
        array $output = []  // ← ADD: Accept output array
    ): void {
        if ($this->db === null) {
            // No DB connection, log to file instead
            error_log(sprintf(
                '[Metrics] %s: duration=%.3fs, memory=%.2fMB, exit=%d, success=%s, output_lines=%d',
                $taskName,
                $duration,
                $memoryMb,
                $exitCode,
                $success ? 'yes' : 'no',
                count($output)
            ));
            return;
        }

        // Convert output array to JSON string for storage
        $outputJson = !empty($output) ? json_encode($output) : null;

        $stmt = $this->db->prepare(
            "INSERT INTO cron_metrics
            (task_name, duration_seconds, memory_peak_mb, cpu_peak_percent, exit_code, success, error_message, output_json)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $successInt = $success ? 1 : 0;
        $stmt->bind_param(
            'sdddiiss',  // ← Changed: Added 's' for output_json
            $taskName,
            $duration,
            $memoryMb,
            $cpuPeak,
            $exitCode,
            $successInt,
            $errorMessage,
            $outputJson  // ← ADD: Store output as JSON
        );

        $stmt->execute();
        $stmt->close();
    }

    /**
     * Get current CPU usage (Linux only)
     */
    private function getCpuUsage(): ?float
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            return null;
        }

        $load = sys_getloadavg();
        if ($load === false) {
            return null;
        }

        // Convert load average to approximate percentage
        // Assumes single-core equivalent (adjust if needed)
        return round($load[0] * 100, 2);
    }

    /**
     * Record a skipped task
     */
    public function recordSkip(string $taskName, string $reason): void
    {
        // Store as 0-duration execution with special exit code
        $this->storeMetrics($taskName, 0, 0, null, 999, false, "Skipped: {$reason}");
    }

    /**
     * Get system status
     */
    public function getSystemStatus(): array
    {
        if ($this->db === null) {
            return [
                'healthy' => false,
                'total_tasks' => 0,
                'last_run' => 0,
                'executions_24h' => 0,
                'successes_24h' => 0,
                'failures_24h' => 0,
                'avg_duration_24h' => 0,
                'error' => 'No database connection'
            ];
        }

        // Get total task count
        $result = $this->db->query("SELECT COUNT(DISTINCT task_name) as total FROM cron_metrics");
        $totalTasks = $result->fetch_assoc()['total'] ?? 0;

        // Get last run time
        $result = $this->db->query("SELECT MAX(executed_at) as last_run FROM cron_metrics");
        $lastRun = $result->fetch_assoc()['last_run'] ?? null;

        // Get 24h stats
        $sql = "SELECT
                    COUNT(*) as executions,
                    SUM(success) as successes,
                    COUNT(*) - SUM(success) as failures,
                    AVG(duration_seconds) as avg_duration
                FROM cron_metrics
                WHERE executed_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                AND exit_code != 999"; // Exclude skipped tasks

        $result = $this->db->query($sql);
        $stats = $result->fetch_assoc();

        return [
            'healthy' => ($stats['failures'] ?? 0) < 5, // Healthy if < 5 failures in 24h
            'total_tasks' => $totalTasks,
            'last_run' => $lastRun ? strtotime($lastRun) : 0,
            'executions_24h' => (int)($stats['executions'] ?? 0),
            'successes_24h' => (int)($stats['successes'] ?? 0),
            'failures_24h' => (int)($stats['failures'] ?? 0),
            'avg_duration_24h' => round((float)($stats['avg_duration'] ?? 0), 2),
        ];
    }

    /**
     * Get recent failures
     */
    public function getRecentFailures(int $limit = 10): array
    {
        if ($this->db === null) {
            return [];
        }

        $stmt = $this->db->prepare(
            "SELECT task_name, executed_at, error_message
            FROM cron_metrics
            WHERE success = 0 AND exit_code != 999
            ORDER BY executed_at DESC
            LIMIT ?"
        );

        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $failures = [];
        while ($row = $result->fetch_assoc()) {
            $failures[] = [
                'task_name' => $row['task_name'],
                'executed_at' => $row['executed_at'],
                'error' => $row['error_message'] ? substr($row['error_message'], 0, 100) : '(no error message)', // Truncate
            ];
        }

        $stmt->close();
        return $failures;
    }

    /**
     * Get metrics for a specific task
     */
    public function getTaskMetrics(string $taskName, int $days = 30): array
    {
        if ($this->db === null) {
            return [
                'avg_duration' => 0,
                'max_duration' => 0,
                'avg_memory' => 0,
                'max_memory' => 0,
                'avg_cpu' => 0,
                'max_cpu' => 0,
                'executions' => 0,
                'success_rate' => 0
            ];
        }

        $stmt = $this->db->prepare(
            "SELECT
                AVG(duration_seconds) as avg_duration,
                MAX(duration_seconds) as max_duration,
                AVG(memory_peak_mb) as avg_memory,
                MAX(memory_peak_mb) as max_memory,
                AVG(cpu_peak_percent) as avg_cpu,
                MAX(cpu_peak_percent) as max_cpu,
                COUNT(*) as executions,
                SUM(success) as successes
            FROM cron_metrics
            WHERE task_name = ?
            AND executed_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            AND exit_code != 999"
        );

        $stmt->bind_param('si', $taskName, $days);
        $stmt->execute();
        $result = $stmt->get_result();
        $metrics = $result->fetch_assoc();
        $stmt->close();

        return [
            'avg_duration' => round((float)($metrics['avg_duration'] ?? 0), 3),
            'max_duration' => round((float)($metrics['max_duration'] ?? 0), 3),
            'avg_memory_mb' => round((float)($metrics['avg_memory'] ?? 0), 2),
            'max_memory_mb' => round((float)($metrics['max_memory'] ?? 0), 2),
            'avg_cpu' => round((float)($metrics['avg_cpu'] ?? 0), 2),
            'max_cpu' => round((float)($metrics['max_cpu'] ?? 0), 2),
            'executions' => (int)($metrics['executions'] ?? 0),
            'success_rate' => $metrics['executions'] > 0
                ? round(((float)$metrics['successes'] / (float)$metrics['executions']) * 100, 1)
                : 0,
        ];
    }

    /**
     * Validate script path for security
     * Prevents directory traversal, symlink attacks, and execution outside allowed directories
     *
     * @param string $resolvedPath The resolved absolute path to validate
     * @return bool True if path is safe to execute
     */
    private function validateScriptPath(string $resolvedPath): bool
    {
        // ✅ ABSOLUTE PATHS ONLY - NO SYMLINKS!
        $projectRoot = $this->config->getSetting('paths.project_root', '/home/129337.cloudwaysapps.com/jcepnzzkmj/public_html');

        // SECURITY: Reject any path containing symlinks
        if (strpos($resolvedPath, '/home/master/applications/') !== false) {
            error_log("[Security] ✗ REJECTED: Path contains symlink '/home/master/applications/'");
            return false;
        }

        // 1. File must exist (we already checked this in resolveScriptPath, but double-check)
        if (!file_exists($resolvedPath) || !is_file($resolvedPath)) {
            error_log("[Security] Script path validation FAILED: File doesn't exist - {$resolvedPath}");
            return false;
        }

        // 2. Check it's within project root (prevents directory traversal)
        // ✅ DO NOT USE realpath() - it follows symlinks!
        // Just do string comparison on absolute paths
        if (strpos($resolvedPath, $projectRoot) !== 0) {
            error_log("[Security] Script path validation FAILED: Outside project root");
            error_log("[Security]   Script: {$resolvedPath}");
            error_log("[Security]   Root: {$projectRoot}");
            return false;
        }

        // 3. Check against whitelist of allowed directories
        $allowedDirs = [
            'assets/services/queue/bin',
            'assets/services/queue-legacy-backup-20251004/bin',
            'assets/services/cron/scripts',
            'assets/services/cron/tasks',
            'assets/services/neuro/neuro_/cron_jobs',
            'assets/services/ai-agent/public/api',
            'neuro/scripts',
            'inventory/scripts',
            'webhooks/scripts',
            'xero-sdk/scripts',
            'transfer/scripts',
            'purchase_orders/scripts',
        ];

        $relativePath = str_replace($projectRoot . '/', '', $resolvedPath);

        $allowed = false;
        foreach ($allowedDirs as $dir) {
            if (strpos($relativePath, $dir) === 0) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            error_log("[Security] Script path validation FAILED: Not in allowed directories");
            error_log("[Security]   Script: {$relativePath}");
            error_log("[Security]   Allowed: " . implode(', ', $allowedDirs));
            return false;
        }

        // 4. Check file is readable
        if (!is_readable($resolvedPath)) {
            error_log("[Security] Script path validation FAILED: Not readable - {$resolvedPath}");
            return false;
        }

        // 5. Check it's a valid executable file type (PHP or Bash)
        $extension = pathinfo($resolvedPath, PATHINFO_EXTENSION);
        $allowedExtensions = ['php', 'sh', 'bash'];

        if (!in_array($extension, $allowedExtensions)) {
            error_log("[Security] Script path validation FAILED: Invalid file type '{$extension}' (allowed: " . implode(', ', $allowedExtensions) . ") - {$resolvedPath}");
            return false;
        }

        // All checks passed
        error_log("[Security] ✅ Script validation PASSED: {$resolvedPath} (type: {$extension})");
        return true;
    }

    /**
     * Resolve script path to absolute filesystem path
     *
     * SIMPLIFIED SINGLE STRATEGY: All paths are relative to project root (public_html)
     *
     * Example: "assets/services/cron/scripts/heartbeat.php"
     *   -> /home/master/applications/jcepnzzkmj/public_html/assets/services/cron/scripts/heartbeat.php
     *
     * @param string $scriptPath Path from task configuration (relative to public_html)
     * @return string|null Resolved absolute path or null if not found/invalid
     */
    private function resolveScriptPath(string $scriptPath): ?string
    {
        // ✅ ABSOLUTE PATHS ONLY - NO SYMLINKS!
        // Get project root from config (already absolute, no symlinks)
        $projectRoot = $this->config->getSetting('paths.project_root', '/home/129337.cloudwaysapps.com/jcepnzzkmj/public_html');

        // SECURITY: Reject any path containing /home/master/applications/ (it's a symlink!)
        if (strpos($scriptPath, '/home/master/applications/') !== false) {
            error_log("[ScriptResolver] ✗ REJECTED: Path contains symlink '/home/master/applications/'");
            error_log("[ScriptResolver]   Use absolute path instead: /home/129337.cloudwaysapps.com/jcepnzzkmj/public_html");
            return null;
        }

        // SINGLE STRATEGY: Relative to project root (public_html)
        // Strip leading slash if present, then prepend project root
        $scriptPath = ltrim($scriptPath, '/');
        $absolutePath = $projectRoot . '/' . $scriptPath;

        // Log what we're resolving
        error_log("[ScriptResolver] Path from config: {$scriptPath}");
        error_log("[ScriptResolver] Absolute path: {$absolutePath}");

        // Check if file exists - DO NOT USE realpath() (it follows symlinks!)
        if (!file_exists($absolutePath) || !is_file($absolutePath) || !is_readable($absolutePath)) {
            error_log("[ScriptResolver] ✗ NOT FOUND: {$absolutePath}");
            error_log("[ScriptResolver]   file_exists() = " . (file_exists($absolutePath) ? 'TRUE' : 'FALSE'));
            error_log("[ScriptResolver]   is_file() = " . (is_file($absolutePath) ? 'TRUE' : 'FALSE'));
            error_log("[ScriptResolver]   is_readable() = " . (is_readable($absolutePath) ? 'TRUE' : 'FALSE'));
            return null;
        }

        // ✅ DO NOT USE realpath() - it follows symlinks and causes path mismatches!
        // Just validate security directly on the absolute path
        if (!$this->validateScriptPath($absolutePath)) {
            error_log("[ScriptResolver] ✗ SECURITY VALIDATION FAILED: {$absolutePath}");
            return null;
        }

        error_log("[ScriptResolver] ✓ FOUND AND VALIDATED: {$absolutePath}");
        return $absolutePath;
    }
}
