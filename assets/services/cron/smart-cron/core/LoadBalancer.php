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
    
    public function __construct(Config $config, MetricsCollector $metrics)
    {
        $this->config = $config;
        $this->metrics = $metrics;
    }
    
    /**
     * Check if we can run a task right now
     */
    public function canRunTask(array $task): bool
    {
        // Check concurrent task limits
        if (!$this->checkConcurrentLimits($task['type'])) {
            return false;
        }
        
        // Check system resources
        if (!$this->checkSystemResources()) {
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
            default => $this->config->get('load_balancer.max_concurrent_light', 10),
        };
        
        $runningOfType = array_filter($running, fn($t) => $t['type'] === $taskType);
        
        return count($runningOfType) < $maxConcurrent;
    }
    
    /**
     * Check system resources
     */
    private function checkSystemResources(): bool
    {
        // Check CPU (default: 80% threshold)
        // Note: CPU checking is disabled as sys_getloadavg() is unreliable
        // It returns load average, not CPU %, and can exceed 100% on multi-core systems
        $cpuUsage = $this->getCurrentCpuUsage();
        $cpuThreshold = $this->config->get('load_balancer.cpu_threshold', 80);
        
        if ($cpuUsage !== null && $cpuUsage > $cpuThreshold) {
            return false;
        }
        
        // Check memory (default: 85% threshold)
        $memoryUsage = $this->getCurrentMemoryUsage();
        $memoryThreshold = $this->config->get('load_balancer.memory_threshold', 85);
        
        if ($memoryUsage > $memoryThreshold) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get currently running tasks
     */
    private function getRunningTasks(): array
    {
        // Check for lock files (simple implementation)
        $lockDir = $this->config->get('paths.logs') . '/locks';
        if (!is_dir($lockDir)) {
            return [];
        }
        
        $running = [];
        $files = glob($lockDir . '/*.lock');
        
        foreach ($files as $file) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && $this->isProcessRunning($data['pid'])) {
                $running[] = $data;
            } else {
                // Clean up stale lock
                unlink($file);
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
        if (PHP_OS_FAMILY === 'Linux') {
            $memInfo = file_get_contents('/proc/meminfo');
            preg_match('/MemTotal:\s+(\d+)/', $memInfo, $total);
            preg_match('/MemAvailable:\s+(\d+)/', $memInfo, $available);
            
            if ($total && $available) {
                $used = $total[1] - $available[1];
                return round(($used / $total[1]) * 100, 2);
            }
        }
        
        // Fallback: PHP memory usage
        return round((memory_get_usage(true) / 1024 / 1024 / 1024) * 100, 2);
    }
    
    /**
     * Get task configuration
     */
    public function getTaskConfig(string $taskName): ?array
    {
        $tasksFile = $this->config->get('paths.tasks_config');
        if (!file_exists($tasksFile)) {
            return null;
        }
        
        $tasksData = json_decode(file_get_contents($tasksFile), true);
        
        // Handle both old format (array of tasks) and new format (object with 'tasks' key)
        $tasks = isset($tasksData['tasks']) ? $tasksData['tasks'] : $tasksData;
        
        foreach ($tasks as $task) {
            if (isset($task['name']) && $task['name'] === $taskName) {
                return $task;
            }
        }
        
        return null;
    }
}
