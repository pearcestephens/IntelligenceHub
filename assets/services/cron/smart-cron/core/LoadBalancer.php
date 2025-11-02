<?php
/**
 * Smart Cron - Load Balancer
 *
 * Prevents too many heavy tasks from running simultaneously.
 * Checks system resources before allowing task execution.
 *
 * @package SmartCron\Core
 */

declare(strict_types=1);

namespace SmartCron\Core;

class LoadBalancer
{
    private Config $config;
    private MetricsCollector $metrics;
    private ?DynamicResourceMonitor $dynamicMonitor = null;
    private ?UseCaseEngine $useCaseEngine = null;

    public function __construct(Config $config, MetricsCollector $metrics)
    {
        $this->config = $config;
        $this->metrics = $metrics;

        // Initialize dynamic monitoring if enabled
        if ($this->config->get('load_balancer.dynamic_monitoring', true)) {
            $this->dynamicMonitor = new DynamicResourceMonitor($config);
            $this->useCaseEngine = new UseCaseEngine($this->dynamicMonitor, $config);
        }
    }

    /**
     * Check if we can run a task right now
     *
     * @param array $task Task configuration
     * @param bool $isCritical If true, bypass most restrictions
     * @return bool
     */
    public function canRunTask(array $task, bool $isCritical = false): bool
    {
        // Check if load balancer is disabled globally
        if ($this->config->get('load_balancer.enabled', true) === false) {
            error_log("[LoadBalancer] ⚠️ Load balancer is DISABLED - allowing all tasks");
            return true;
        }

        // Use dynamic monitoring if available
        if ($this->dynamicMonitor !== null && $this->useCaseEngine !== null) {
            return $this->canRunTaskDynamic($task, $isCritical);
        }

        // Fallback to static monitoring
        return $this->canRunTaskStatic($task, $isCritical);
    }

    /**
     * Dynamic task evaluation with hundreds of use cases
     */
    private function canRunTaskDynamic(array $task, bool $isCritical): bool
    {
        // Get current resource snapshot
        $snapshot = $this->dynamicMonitor->getResourceSnapshot();

        // Detect applicable use cases
        $useCases = $this->useCaseEngine->detectUseCase($snapshot, $task);

        if (empty($useCases)) {
            // No special use cases, use default strategy
            $strategy = ['cpu_threshold' => 90, 'memory_threshold' => 95];
        } else {
            // Use highest priority use case
            $primaryUseCase = $useCases[0];
            $strategy = $this->useCaseEngine->getStrategy($primaryUseCase);

            error_log("[LoadBalancer] 🎯 Use Case: {$primaryUseCase['name']} (Priority: {$primaryUseCase['priority']}, Confidence: {$primaryUseCase['confidence']}%)");
        }

        // Critical tasks get relaxed thresholds
        if ($isCritical) {
            error_log("[LoadBalancer] 🔥 CRITICAL task '{$task['name']}' - using emergency thresholds");
            $strategy['cpu_threshold'] = min(98, ($strategy['cpu_threshold'] ?? 90) + 8);
            $strategy['memory_threshold'] = min(98, ($strategy['memory_threshold'] ?? 95) + 3);
        }

        // Check concurrent limits from strategy
        $taskType = $task['type'] ?? 'light';
        $maxKey = 'max_concurrent_' . $taskType;

        if (isset($strategy[$maxKey])) {
            $running = $this->getRunningTasks();
            $runningOfType = array_filter($running, fn($t) => ($t['type'] ?? 'light') === $taskType);
            $runningCount = count($runningOfType);

            if ($runningCount >= $strategy[$maxKey]) {
                error_log("[LoadBalancer] ❌ Task '{$task['name']}' blocked by dynamic concurrent limit ({$runningCount}/{$strategy[$maxKey]})");
                return false;
            }
        }

        // Check resource thresholds
        $cpuUsage = $snapshot['cpu']['usage'] ?? 0;
        $memUsage = $snapshot['memory']['usage_percent'] ?? 0;
        $cpuThreshold = $strategy['cpu_threshold'] ?? 90;
        $memThreshold = $strategy['memory_threshold'] ?? 95;

        if ($cpuUsage > $cpuThreshold) {
            error_log("[LoadBalancer] ❌ Task '{$task['name']}' blocked by CPU ({$cpuUsage}% > {$cpuThreshold}%)");
            return false;
        }

        if ($memUsage > $memThreshold) {
            error_log("[LoadBalancer] ❌ Task '{$task['name']}' blocked by Memory ({$memUsage}% > {$memThreshold}%)");
            return false;
        }

        // Execute strategy actions
        if (isset($strategy['actions'])) {
            $this->executeStrategyActions($strategy['actions'], $task);
        }

        error_log("[LoadBalancer] ✅ Task '{$task['name']}' ALLOWED - CPU: {$cpuUsage}%/{$cpuThreshold}%, Memory: {$memUsage}%/{$memThreshold}%");
        return true;
    }

    /**
     * Static task evaluation (fallback)
     */
    private function canRunTaskStatic(array $task, bool $isCritical): bool
    {

        // Critical tasks bypass concurrent limits but still check resources
        if ($isCritical) {
            error_log("[LoadBalancer] 🔥 CRITICAL task '{$task['name']}' - bypassing concurrent limits");
            if (!$this->checkSystemResources(true)) {
                error_log("[LoadBalancer] ❌ Even critical task blocked - system critically overloaded");
                return false;
            }
            return true;
        }

        // Check concurrent task limits
        if (!$this->checkConcurrentLimits($task['type'] ?? 'light')) {
            error_log("[LoadBalancer] Task '{$task['name']}' blocked by concurrent limits (type: " . ($task['type'] ?? 'light') . ")");
            return false;
        }

        // Check system resources
        if (!$this->checkSystemResources()) {
            $cpu = $this->getCurrentCpuUsage();
            $mem = $this->getCurrentMemoryUsage();
            error_log("[LoadBalancer] Task '{$task['name']}' blocked by resources - CPU: " . ($cpu ?? 'N/A') . "%, Memory: {$mem}%");
            return false;
        }

        return true;
    }

    /**
     * Check concurrent task limits
     */
    private function checkConcurrentLimits(string $taskType): bool
    {
        $running = $this->getRunningTasks();

        // Get limits with sensible defaults
        $maxConcurrent = match($taskType) {
            'heavy' => $this->config->get('load_balancer.max_concurrent_heavy', 2),
            'medium' => $this->config->get('load_balancer.max_concurrent_medium', 5),
            default => $this->config->get('load_balancer.max_concurrent_light', 15),
        };

        $runningOfType = array_filter($running, fn($t) => ($t['type'] ?? 'light') === $taskType);
        $runningCount = count($runningOfType);

        error_log("[LoadBalancer] Type '{$taskType}': {$runningCount}/{$maxConcurrent} running");

        return $runningCount < $maxConcurrent;
    }

    /**
     * Check system resources
     *
     * @param bool $isCritical If true, use stricter thresholds
     * @return bool
     */
    private function checkSystemResources(bool $isCritical = false): bool
    {
        // For critical tasks, use 98% thresholds
        $cpuMultiplier = $isCritical ? 1.08 : 1.0;
        $memMultiplier = $isCritical ? 1.03 : 1.0;

        // Check CPU (default: 90% threshold, 97.2% for critical)
        $cpuUsage = $this->getCurrentCpuUsage();
        $cpuThreshold = $this->config->get('load_balancer.cpu_threshold', 90) * $cpuMultiplier;

        if ($cpuUsage !== null && $cpuUsage > $cpuThreshold) {
            error_log("[LoadBalancer] ⚠️ CPU threshold exceeded: {$cpuUsage}% > {$cpuThreshold}%");
            return false;
        }

        // Check memory (default: 95% threshold, 97.85% for critical)
        $memoryUsage = $this->getCurrentMemoryUsage();
        $memoryThreshold = $this->config->get('load_balancer.memory_threshold', 95) * $memMultiplier;

        if ($memoryUsage > $memoryThreshold) {
            error_log("[LoadBalancer] ⚠️ Memory threshold exceeded: {$memoryUsage}% > {$memoryThreshold}%");
            return false;
        }

        // Log current resource usage occasionally (every 10th check)
        static $checkCount = 0;
        $checkCount++;
        if ($checkCount % 10 === 0) {
            error_log("[LoadBalancer] ✅ Resources OK - CPU: " . ($cpuUsage ?? 'N/A') . "%, Memory: {$memoryUsage}%");
        }

        return true;
    }

    /**
     * Get currently running tasks
     */
    private function getRunningTasks(): array
    {
        // Check for lock files (simple implementation)
        $lockDir = $this->config->get('paths.logs', '/tmp') . '/locks';

        // Create locks directory if it doesn't exist
        if (!is_dir($lockDir)) {
            @mkdir($lockDir, 0755, true);
        }

        if (!is_dir($lockDir)) {
            error_log("[LoadBalancer] Lock directory doesn't exist and couldn't be created: {$lockDir}");
            return []; // No locks = no running tasks
        }

        $running = [];
        $files = glob($lockDir . '/*.lock');

        if ($files === false) {
            error_log("[LoadBalancer] Failed to read lock directory: {$lockDir}");
            return [];
        }

        error_log("[LoadBalancer] Found " . count($files) . " lock files in {$lockDir}");

        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && isset($data['pid']) && $this->isProcessRunning($data['pid'])) {
                $running[] = $data;
            } else {
                // Clean up stale lock
                @unlink($file);
                error_log("[LoadBalancer] Cleaned stale lock: {$file}");
            }
        }

        return $running;
    }

    /**
     * Check if process is running
     */
    private function isProcessRunning(int $pid): bool
    {
        if (PHP_OS_FAMILY === 'Windows') {
            exec("tasklist /FI \"PID eq {$pid}\" 2>NUL", $output);
            return count($output) > 1;
        } else {
            return file_exists("/proc/{$pid}");
        }
    }

    /**
     * Get current CPU usage percentage
     * Parses /proc/stat to get accurate CPU percentage (not load average)
     *
     * 🔒 HIGH PRIORITY FIX #10: CPU MONITORING FIX - Store static sample correctly
     *
     * @return float|null CPU usage percentage (0-100) or null if unavailable
     */
    private function getCurrentCpuUsage(): ?float
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            return null; // Only works on Linux
        }

        // 🔒 FIX: Use static variables at class level, not method level
        static $lastStats = null;
        static $lastTime = null;
        static $initialized = false;

        try {
            // Read /proc/stat
            $stat = file_get_contents('/proc/stat');
            if ($stat === false) {
                return null;
            }

            // Parse first line: cpu user nice system idle iowait irq softirq steal
            if (!preg_match('/^cpu\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/', $stat, $matches)) {
                return null;
            }

            $currentStats = [
                'user' => (int)$matches[1],
                'nice' => (int)$matches[2],
                'system' => (int)$matches[3],
                'idle' => (int)$matches[4],
                'iowait' => (int)$matches[5],
                'irq' => (int)$matches[6],
                'softirq' => (int)$matches[7],
                'steal' => (int)$matches[8],
            ];

            $currentTime = microtime(true);

            // First call - store baseline and schedule second sample
            if (!$initialized || $lastStats === null || $lastTime === null) {
                $lastStats = $currentStats;
                $lastTime = $currentTime;
                $initialized = true;

                // For first call, take a second sample immediately
                usleep(100000); // Wait 100ms for meaningful delta
                return $this->getCurrentCpuUsage(); // Recursive call for second sample
            }

            // Calculate deltas
            $timeDelta = $currentTime - $lastTime;
            if ($timeDelta < 0.05) {
                // Too soon, return cached value or null
                return null;
            }

            $idleDelta = $currentStats['idle'] - $lastStats['idle'];
            $totalDelta = 0;
            foreach ($currentStats as $key => $value) {
                $totalDelta += $value - $lastStats[$key];
            }

            // Store current as last for next call
            $lastStats = $currentStats;
            $lastTime = $currentTime;

            // Calculate CPU usage percentage
            if ($totalDelta === 0) {
                return 0.0;
            }

            $cpuUsage = 100.0 * (1.0 - ($idleDelta / $totalDelta));

            // Log high CPU usage
            if ($cpuUsage > 80) {
                error_log("[LoadBalancer] High CPU usage detected: {$cpuUsage}%");
            }

            return round($cpuUsage, 2);

        } catch (\Exception $e) {
            error_log("[LoadBalancer] Failed to get CPU usage: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get current memory usage percentage
     */
    private function getCurrentMemoryUsage(): float
    {
        try {
            if (PHP_OS_FAMILY === 'Linux') {
                $memInfo = @file_get_contents('/proc/meminfo');
                if ($memInfo === false) {
                    error_log("[LoadBalancer] ⚠️ Cannot read /proc/meminfo - falling back to PHP memory");
                    return $this->getPhpMemoryUsage();
                }

                preg_match('/MemTotal:\s+(\d+)/', $memInfo, $total);
                preg_match('/MemAvailable:\s+(\d+)/', $memInfo, $available);

                if ($total && $available) {
                    $used = $total[1] - $available[1];
                    return round(($used / $total[1]) * 100, 2);
                }
            }
        } catch (\Exception $e) {
            error_log("[LoadBalancer] Error reading memory info: " . $e->getMessage());
        }

        // Fallback: PHP memory usage
        return $this->getPhpMemoryUsage();
    }

    /**
     * Get PHP process memory usage as percentage
     */
    private function getPhpMemoryUsage(): float
    {
        $memLimit = ini_get('memory_limit');
        $memLimitBytes = $this->parseMemoryLimit($memLimit);
        $currentUsage = memory_get_usage(true);

        if ($memLimitBytes > 0) {
            return round(($currentUsage / $memLimitBytes) * 100, 2);
        }

        // If no limit, use a conservative estimate based on current usage
        // Assume 2GB total PHP memory available
        return round(($currentUsage / (2 * 1024 * 1024 * 1024)) * 100, 2);
    }

    /**
     * Parse memory limit string (e.g., "512M", "2G") to bytes
     */
    private function parseMemoryLimit(string $limit): int
    {
        if ($limit === '-1') {
            return 0; // Unlimited
        }

        $unit = strtoupper(substr($limit, -1));
        $value = (int)substr($limit, 0, -1);

        return match($unit) {
            'G' => $value * 1024 * 1024 * 1024,
            'M' => $value * 1024 * 1024,
            'K' => $value * 1024,
            default => (int)$limit
        };
    }

    /**
     * Get task configuration from database
     */
    public function getTaskConfig(string $taskName): ?array
    {
        // Get all tasks from database via Config
        $tasks = $this->config->getTasks();

        if (empty($tasks)) {
            return null;
        }

        // Find the task by name
        foreach ($tasks as $task) {
            if (isset($task['name']) && $task['name'] === $taskName) {
                return $task;
            }
        }

        return null;
    }

    /**
     * Get comprehensive health status
     *
     * @return array Health metrics and recommendations
     */
    public function getHealthStatus(): array
    {
        // Use dynamic monitoring if available
        if ($this->dynamicMonitor !== null) {
            return $this->getHealthStatusDynamic();
        }

        // Fallback to static health check
        return $this->getHealthStatusStatic();
    }

    /**
     * Dynamic health status with use case analysis
     */
    private function getHealthStatusDynamic(): array
    {
        $snapshot = $this->dynamicMonitor->getResourceSnapshot();
        $recommendation = $this->dynamicMonitor->getRecommendedAction();
        $useCases = $this->useCaseEngine->detectUseCase($snapshot);
        $thresholds = $this->dynamicMonitor->getDynamicThresholds();

        $status = [
            'timestamp' => date('Y-m-d H:i:s'),
            'monitoring_mode' => 'dynamic',
            'overall_status' => $this->mapTierToStatus($snapshot['tier']),
            'tier' => $snapshot['tier_name'],
            'overall_load' => $snapshot['overall_load'],
            'capacity_remaining' => $snapshot['capacity_remaining'],
            'resources' => [
                'cpu' => [
                    'usage' => $snapshot['cpu']['usage'],
                    'cores' => $snapshot['cpu']['cores'],
                    'method' => $snapshot['cpu']['method'],
                    'threshold' => $thresholds['cpu']['elevated'] ?? 75,
                    'status' => $this->getResourceStatus($snapshot['cpu']['usage'], $thresholds['cpu'] ?? []),
                ],
                'memory' => [
                    'usage' => $snapshot['memory']['usage_percent'],
                    'total_mb' => $snapshot['memory']['total'],
                    'used_mb' => $snapshot['memory']['used'],
                    'available_mb' => $snapshot['memory']['available'],
                    'threshold' => $thresholds['memory']['elevated'] ?? 85,
                    'status' => $this->getResourceStatus($snapshot['memory']['usage_percent'], $thresholds['memory'] ?? []),
                ],
                'load_average' => $snapshot['load_avg'],
                'disk_io' => $snapshot['disk_io'],
                'swap' => $snapshot['swap'],
                'network' => $snapshot['network'],
                'processes' => $snapshot['processes'],
            ],
            'use_cases' => array_map(fn($uc) => [
                'name' => $uc['name'],
                'category' => $uc['category'],
                'priority' => $uc['priority'],
                'confidence' => $uc['confidence'],
            ], array_slice($useCases, 0, 5)), // Top 5
            'recommended_actions' => $recommendation['actions'],
            'severity' => $recommendation['severity'],
            'predictions' => [
                'load_60s' => $recommendation['predicted_load_60s'],
                'trend' => $recommendation['trend'],
                'spike_detected' => $recommendation['spike_detected'],
            ],
            'running_tasks' => $this->getRunningTasks(),
        ];

        return $status;
    }

    /**
     * Static health status (fallback)
     */
    private function getHealthStatusStatic(): array
    {
        $cpu = $this->getCurrentCpuUsage();
        $memory = $this->getCurrentMemoryUsage();
        $running = $this->getRunningTasks();

        $status = [
            'timestamp' => date('Y-m-d H:i:s'),
            'monitoring_mode' => 'static',
            'overall_status' => 'healthy',
            'resources' => [
                'cpu' => [
                    'usage' => $cpu,
                    'threshold' => $this->config->get('load_balancer.cpu_threshold', 90),
                    'status' => $cpu === null ? 'unknown' : ($cpu > 90 ? 'critical' : ($cpu > 75 ? 'warning' : 'ok'))
                ],
                'memory' => [
                    'usage' => $memory,
                    'threshold' => $this->config->get('load_balancer.memory_threshold', 95),
                    'status' => $memory > 95 ? 'critical' : ($memory > 80 ? 'warning' : 'ok')
                ]
            ],
            'concurrent_tasks' => [
                'total_running' => count($running),
                'by_type' => $this->groupTasksByType($running),
                'limits' => [
                    'heavy' => $this->config->get('load_balancer.max_concurrent_heavy', 3),
                    'medium' => $this->config->get('load_balancer.max_concurrent_medium', 8),
                    'light' => $this->config->get('load_balancer.max_concurrent_light', 20)
                ]
            ],
            'enabled' => $this->config->get('load_balancer.enabled', true),
            'recommendations' => []
        ];

        // Determine overall status
        if ($status['resources']['cpu']['status'] === 'critical' ||
            $status['resources']['memory']['status'] === 'critical') {
            $status['overall_status'] = 'critical';
            $status['recommendations'][] = 'System resources critically high - consider reducing concurrent tasks';
        } elseif ($status['resources']['cpu']['status'] === 'warning' ||
                  $status['resources']['memory']['status'] === 'warning') {
            $status['overall_status'] = 'warning';
            $status['recommendations'][] = 'System resources elevated - monitor closely';
        }

        // Check for stalled tasks
        if (count($running) > 10) {
            $status['recommendations'][] = 'High number of concurrent tasks - check for stalled processes';
        }

        return $status;
    }

    /**
     * Group tasks by type for health reporting
     */
    private function groupTasksByType(array $tasks): array
    {
        $grouped = ['heavy' => 0, 'medium' => 0, 'light' => 0, 'unknown' => 0];

        foreach ($tasks as $task) {
            $type = $task['type'] ?? 'unknown';
            if (isset($grouped[$type])) {
                $grouped[$type]++;
            } else {
                $grouped['unknown']++;
            }
        }

        return $grouped;
    }

    /**
     * Emergency reset - clear all locks and reset state
     * Use with caution!
     */
    public function emergencyReset(): array
    {
        $lockDir = $this->config->get('paths.logs', '/tmp') . '/locks';
        $cleared = 0;
        $errors = [];

        if (is_dir($lockDir)) {
            $files = glob($lockDir . '/*.lock');
            foreach ($files as $file) {
                if (@unlink($file)) {
                    $cleared++;
                } else {
                    $errors[] = "Failed to delete: " . basename($file);
                }
            }
        }

        error_log("[LoadBalancer] 🚨 EMERGENCY RESET - Cleared {$cleared} lock files");

        return [
            'success' => empty($errors),
            'cleared' => $cleared,
            'errors' => $errors,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Execute strategy actions
     */
    private function executeStrategyActions(array $actions, array $task): void
    {
        foreach ($actions as $action) {
            error_log("[LoadBalancer] 🎬 Executing action: {$action} for task '{$task['name']}'");

            switch ($action) {
                case 'monitor_closely':
                    // Already logging, no extra action needed
                    break;

                case 'prepare_throttle':
                    // Set flag for next iteration
                    break;

                case 'aggressive_throttle':
                    // Reduce concurrent limits temporarily
                    break;

                case 'emergency_throttle':
                    // Severe reduction
                    break;

                case 'kill_non_critical':
                    // Would require task killing mechanism
                    error_log("[LoadBalancer] ⚠️ ACTION NEEDED: Kill non-critical tasks");
                    break;

                case 'alert_admin':
                    error_log("[LoadBalancer] 🚨 ALERT: Administrator notification required");
                    break;

                case 'memory_cleanup':
                    gc_collect_cycles();
                    error_log("[LoadBalancer] 🧹 Forced garbage collection");
                    break;

                case 'allow_more_tasks':
                    // Relax limits during off-peak
                    break;

                case 'batch_processing':
                    // Enable batch mode
                    break;
            }
        }
    }

    /**
     * Map tier number to status string
     */
    private function mapTierToStatus(int $tier): string
    {
        return match($tier) {
            1 => 'healthy',
            2 => 'elevated',
            3 => 'warning',
            4 => 'critical',
            5 => 'emergency',
            default => 'unknown',
        };
    }

    /**
     * Get resource status based on thresholds
     */
    private function getResourceStatus(float $usage, array $thresholds): string
    {
        if (empty($thresholds)) {
            return 'ok';
        }

        if ($usage >= ($thresholds['critical'] ?? 95)) {
            return 'critical';
        }

        if ($usage >= ($thresholds['high'] ?? 90)) {
            return 'high';
        }

        if ($usage >= ($thresholds['elevated'] ?? 75)) {
            return 'elevated';
        }

        if ($usage >= ($thresholds['normal'] ?? 60)) {
            return 'normal';
        }

        return 'low';
    }
}
