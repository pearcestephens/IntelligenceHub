<?php
/**
 * Smart Cron - Dynamic Resource Manager
 * 
 * Automatically adjusts cron job limits based on real-time system load
 * Monitors VSCode memory usage and dynamically scales cron capacity
 * 
 * @package SmartCron\Core
 */

declare(strict_types=1);

namespace SmartCron\Core;

class DynamicResourceManager
{
    private Config $config;
    private \mysqli $db;
    
    // System constraints
    private const TOTAL_SYSTEM_RAM_MB = 15360; // 15GB
    private const SAFETY_BUFFER_MB = 2048;     // 2GB safety buffer
    private const MIN_CRON_MEMORY_MB = 512;    // Always allow at least 512MB
    private const MAX_CRON_MEMORY_MB = 4096;   // Never exceed 4GB
    
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->db = $config->getDbConnection();
    }
    
    /**
     * Adjust execution slot limits based on current system state
     * Called every 1 minute by health-check job
     */
    public function adjustLimits(): array
    {
        $metrics = $this->gatherMetrics();
        $adjustments = $this->calculateAdjustments($metrics);
        $this->applyAdjustments($adjustments);
        
        return [
            'metrics' => $metrics,
            'adjustments' => $adjustments,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    /**
     * Gather real-time system metrics
     */
    private function gatherMetrics(): array
    {
        $metrics = [
            'timestamp' => time(),
            'system' => $this->getSystemMetrics(),
            'vscode' => $this->getVSCodeMetrics(),
            'mysql' => $this->getMySQLMetrics(),
            'cron' => $this->getCronMetrics(),
        ];
        
        // Calculate available memory for cron jobs
        $usedMemory = $metrics['vscode']['total_mb'] 
                    + $metrics['mysql']['memory_mb'] 
                    + $metrics['system']['other_mb'];
        
        $availableMemory = self::TOTAL_SYSTEM_RAM_MB - $usedMemory - self::SAFETY_BUFFER_MB;
        
        // Clamp to safe range
        $metrics['available_for_cron_mb'] = max(
            self::MIN_CRON_MEMORY_MB,
            min(self::MAX_CRON_MEMORY_MB, $availableMemory)
        );
        
        return $metrics;
    }
    
    /**
     * Get system-wide metrics
     */
    private function getSystemMetrics(): array
    {
        $meminfo = $this->parseMeminfo();
        
        return [
            'total_ram_mb' => self::TOTAL_SYSTEM_RAM_MB,
            'free_mb' => $meminfo['MemFree'] ?? 0,
            'available_mb' => $meminfo['MemAvailable'] ?? 0,
            'cached_mb' => $meminfo['Cached'] ?? 0,
            'swap_used_mb' => ($meminfo['SwapTotal'] ?? 0) - ($meminfo['SwapFree'] ?? 0),
            'load_avg' => sys_getloadavg()[0] ?? 0,
            'cpu_count' => $this->getCpuCount(),
            'other_mb' => 1024, // Estimate for system processes
        ];
    }
    
    /**
     * Get VSCode memory usage (all extension hosts)
     */
    private function getVSCodeMetrics(): array
    {
        exec("ps aux | grep -E 'extensionHost|vscode-server' | grep -v grep", $output);
        
        $totalMemory = 0;
        $processCount = 0;
        $processes = [];
        
        foreach ($output as $line) {
            if (preg_match('/\s+(\d+)\s+[\d.]+\s+[\d.]+\s+\d+\s+(\d+)/', $line, $matches)) {
                $pid = (int)$matches[1];
                $memKb = (int)$matches[2];
                $memMb = round($memKb / 1024, 2);
                
                $totalMemory += $memMb;
                $processCount++;
                
                $processes[] = [
                    'pid' => $pid,
                    'memory_mb' => $memMb
                ];
            }
        }
        
        return [
            'total_mb' => (int)$totalMemory,
            'process_count' => $processCount,
            'avg_per_process_mb' => $processCount > 0 ? round($totalMemory / $processCount, 2) : 0,
            'processes' => array_slice($processes, 0, 5), // Top 5
        ];
    }
    
    /**
     * Get MySQL memory usage
     */
    private function getMySQLMetrics(): array
    {
        exec("ps aux | grep mysql | grep -v grep | head -1", $output);
        
        $memoryMb = 0;
        $pid = 0;
        
        if (!empty($output)) {
            if (preg_match('/\s+(\d+)\s+[\d.]+\s+[\d.]+\s+\d+\s+(\d+)/', $output[0], $matches)) {
                $pid = (int)$matches[1];
                $memoryMb = (int)round((int)$matches[2] / 1024);
            }
        }
        
        return [
            'pid' => $pid,
            'memory_mb' => $memoryMb ?: 512, // Estimate 512MB if can't read
        ];
    }
    
    /**
     * Get current cron job metrics from execution slots
     */
    private function getCronMetrics(): array
    {
        $result = $this->db->query("
            SELECT 
                slot_name,
                max_concurrent_jobs,
                current_running_jobs,
                max_total_memory_mb,
                current_memory_mb,
                max_cpu_percent,
                current_cpu_percent
            FROM smart_cron_execution_slots
        ");
        
        $slots = [];
        $totalRunning = 0;
        $totalMemory = 0;
        
        while ($row = $result->fetch_assoc()) {
            $slots[] = $row;
            $totalRunning += $row['current_running_jobs'];
            $totalMemory += $row['current_memory_mb'];
        }
        
        return [
            'slots' => $slots,
            'total_running_jobs' => $totalRunning,
            'total_memory_mb' => $totalMemory,
        ];
    }
    
    /**
     * Calculate optimal adjustments based on metrics
     */
    private function calculateAdjustments(array $metrics): array
    {
        $availableMemory = $metrics['available_for_cron_mb'];
        $vscodeMemory = $metrics['vscode']['total_mb'];
        $freeMemory = $metrics['system']['free_mb'];
        $swapUsed = $metrics['system']['swap_used_mb'];
        $loadAvg = $metrics['system']['load_avg'];
        $cpuCount = $metrics['system']['cpu_count'];
        
        // Calculate system pressure score (0-100, higher = more stressed)
        $pressureScore = $this->calculatePressureScore(
            $vscodeMemory,
            $freeMemory,
            $swapUsed,
            $loadAvg,
            $cpuCount
        );
        
        // Select strategy based on comprehensive pressure analysis
        $strategy = $this->selectStrategy($pressureScore, $vscodeMemory, $freeMemory, $swapUsed);
        
        // Get slot configuration for selected strategy
        $adjustments = $this->getStrategyConfig($strategy, $availableMemory);
        
        return [
            'strategy' => $strategy,
            'pressure_score' => $pressureScore,
            'available_memory_mb' => $availableMemory,
            'vscode_memory_mb' => $vscodeMemory,
            'free_memory_mb' => $freeMemory,
            'swap_used_mb' => $swapUsed,
            'load_avg' => $loadAvg,
            'slots' => $adjustments,
        ];
    }
    
    /**
     * Calculate system pressure score (0-100)
     * Higher score = more stressed system
     */
    private function calculatePressureScore(
        int $vscodeMemory,
        int $freeMemory,
        int $swapUsed,
        float $loadAvg,
        int $cpuCount
    ): int {
        $score = 0;
        
        // VSCode memory pressure (0-40 points)
        if ($vscodeMemory > 6000) $score += 40;
        elseif ($vscodeMemory > 5000) $score += 30;
        elseif ($vscodeMemory > 4000) $score += 20;
        elseif ($vscodeMemory > 3000) $score += 10;
        
        // Free memory pressure (0-30 points)
        if ($freeMemory < 1000) $score += 30;
        elseif ($freeMemory < 2000) $score += 20;
        elseif ($freeMemory < 3000) $score += 10;
        
        // Swap usage pressure (0-20 points)
        if ($swapUsed > 1500) $score += 20;
        elseif ($swapUsed > 1000) $score += 15;
        elseif ($swapUsed > 500) $score += 10;
        
        // CPU load pressure (0-10 points)
        $loadPerCpu = $loadAvg / max(1, $cpuCount);
        if ($loadPerCpu > 0.8) $score += 10;
        elseif ($loadPerCpu > 0.6) $score += 7;
        elseif ($loadPerCpu > 0.4) $score += 5;
        
        return min(100, $score);
    }
    
    /**
     * Select strategy based on comprehensive pressure analysis
     */
    private function selectStrategy(
        int $pressureScore,
        int $vscodeMemory,
        int $freeMemory,
        int $swapUsed
    ): string {
        // Critical emergency - system about to crash
        if ($pressureScore >= 90 || $freeMemory < 500) {
            return 'emergency';
        }
        
        // Extreme pressure - barely holding on
        if ($pressureScore >= 75 || $swapUsed > 1500) {
            return 'critical';
        }
        
        // Very high pressure - VSCode + swap heavy
        if ($pressureScore >= 60 || $vscodeMemory > 6000) {
            return 'aggressive';
        }
        
        // High pressure - VSCode heavy or swap moderate
        if ($pressureScore >= 45 || $vscodeMemory > 5000) {
            return 'conservative';
        }
        
        // Moderate pressure - VSCode moderate
        if ($pressureScore >= 30 || $vscodeMemory > 4000) {
            return 'moderate';
        }
        
        // Light pressure - VSCode light usage
        if ($pressureScore >= 15 || $vscodeMemory > 2500) {
            return 'normal';
        }
        
        // Low pressure - VSCode minimal or idle
        if ($pressureScore >= 5 || $vscodeMemory > 1500) {
            return 'relaxed';
        }
        
        // Optimal conditions - VSCode closed or minimal
        return 'optimal';
    }
    
    /**
     * Get slot configuration for strategy
     */
    private function getStrategyConfig(string $strategy, int $availableMemory): array
    {
        return match($strategy) {
            // EMERGENCY: System about to crash - STOP EVERYTHING
            'emergency' => [
                'default' => [
                    'max_concurrent_jobs' => 1,
                    'max_total_memory_mb' => 256, // 1 job @ 256MB
                    'max_cpu_percent' => 10.0,
                ],
                'heavy' => [
                    'max_concurrent_jobs' => 0, // DISABLED
                    'max_total_memory_mb' => 0,
                    'max_cpu_percent' => 0.0,
                ],
                'light' => [
                    'max_concurrent_jobs' => 1,
                    'max_total_memory_mb' => 128, // 1 job @ 128MB
                    'max_cpu_percent' => 5.0,
                ],
            ],
            
            // CRITICAL: Barely holding on - minimal jobs
            'critical' => [
                'default' => [
                    'max_concurrent_jobs' => 2,
                    'max_total_memory_mb' => 512, // 2 jobs @ 256MB each
                    'max_cpu_percent' => 15.0,
                ],
                'heavy' => [
                    'max_concurrent_jobs' => 1,
                    'max_total_memory_mb' => 256, // 1 job @ 256MB
                    'max_cpu_percent' => 10.0,
                ],
                'light' => [
                    'max_concurrent_jobs' => 2,
                    'max_total_memory_mb' => 256, // 2 jobs @ 128MB each
                    'max_cpu_percent' => 10.0,
                ],
            ],
            
            // AGGRESSIVE: VSCode >6GB or high swap - tight limits
            'aggressive' => [
                'default' => [
                    'max_concurrent_jobs' => 4,
                    'max_total_memory_mb' => 1024, // 4 jobs @ 256MB each
                    'max_cpu_percent' => 30.0,
                ],
                'heavy' => [
                    'max_concurrent_jobs' => 2,
                    'max_total_memory_mb' => 512, // 2 jobs @ 256MB each
                    'max_cpu_percent' => 20.0,
                ],
                'light' => [
                    'max_concurrent_jobs' => 4,
                    'max_total_memory_mb' => 512, // 4 jobs @ 128MB each
                    'max_cpu_percent' => 15.0,
                ],
            ],
            'conservative' => [
                'default' => [
                    'max_concurrent_jobs' => 6,
                    'max_total_memory_mb' => 1536, // 6 jobs @ 256MB each
                    'max_cpu_percent' => 40.0,
                ],
                'heavy' => [
                    'max_concurrent_jobs' => 3,
                    'max_total_memory_mb' => 768, // 3 jobs @ 256MB each
                    'max_cpu_percent' => 25.0,
                ],
                'light' => [
                    'max_concurrent_jobs' => 6,
                    'max_total_memory_mb' => 768, // 6 jobs @ 128MB each
                    'max_cpu_percent' => 20.0,
                ],
            ],
            'moderate' => [
                'default' => [
                    'max_concurrent_jobs' => 8,
                    'max_total_memory_mb' => 2048, // 8 jobs @ 256MB each
                    'max_cpu_percent' => 50.0,
                ],
                'heavy' => [
                    'max_concurrent_jobs' => 4,
                    'max_total_memory_mb' => 1024, // 4 jobs @ 256MB each
                    'max_cpu_percent' => 30.0,
                ],
                'light' => [
                    'max_concurrent_jobs' => 8,
                    'max_total_memory_mb' => 1024, // 8 jobs @ 128MB each
                    'max_cpu_percent' => 25.0,
                ],
            ],
            'normal' => [
                'default' => [
                    'max_concurrent_jobs' => 12,
                    'max_total_memory_mb' => 3072, // 12 jobs @ 256MB each
                    'max_cpu_percent' => 60.0,
                ],
                'heavy' => [
                    'max_concurrent_jobs' => 6,
                    'max_total_memory_mb' => 1536, // 6 jobs @ 256MB each
                    'max_cpu_percent' => 40.0,
                ],
                'light' => [
                    'max_concurrent_jobs' => 16,
                    'max_total_memory_mb' => 2048, // 16 jobs @ 128MB each
                    'max_cpu_percent' => 30.0,
                ],
            ],
            
            // RELAXED: VSCode light, good free memory
            'relaxed' => [
                'default' => [
                    'max_concurrent_jobs' => 16,
                    'max_total_memory_mb' => 4096, // 16 jobs @ 256MB each
                    'max_cpu_percent' => 70.0,
                ],
                'heavy' => [
                    'max_concurrent_jobs' => 8,
                    'max_total_memory_mb' => 2048, // 8 jobs @ 256MB each
                    'max_cpu_percent' => 50.0,
                ],
                'light' => [
                    'max_concurrent_jobs' => 20,
                    'max_total_memory_mb' => 2560, // 20 jobs @ 128MB each
                    'max_cpu_percent' => 35.0,
                ],
            ],
            
            // OPTIMAL: VSCode closed/minimal, system idle
            'optimal' => [
                'default' => [
                    'max_concurrent_jobs' => 20,
                    'max_total_memory_mb' => 5120, // 20 jobs @ 256MB each
                    'max_cpu_percent' => 80.0,
                ],
                'heavy' => [
                    'max_concurrent_jobs' => 10,
                    'max_total_memory_mb' => 2560, // 10 jobs @ 256MB each
                    'max_cpu_percent' => 60.0,
                ],
                'light' => [
                    'max_concurrent_jobs' => 30,
                    'max_total_memory_mb' => 3840, // 30 jobs @ 128MB each
                    'max_cpu_percent' => 40.0,
                ],
            ],
        };
    }
    
    /**
     * Apply calculated adjustments to execution slots
     */
    private function applyAdjustments(array $adjustments): void
    {
        foreach ($adjustments['slots'] as $slotName => $limits) {
            $stmt = $this->db->prepare("
                UPDATE smart_cron_execution_slots
                SET 
                    max_concurrent_jobs = ?,
                    max_total_memory_mb = ?,
                    max_cpu_percent = ?
                WHERE slot_name = ?
            ");
            
            $stmt->bind_param(
                'iids',
                $limits['max_concurrent_jobs'],
                $limits['max_total_memory_mb'],
                $limits['max_cpu_percent'],
                $slotName
            );
            
            $stmt->execute();
            $stmt->close();
        }
        
        // Log adjustment
        $this->logAdjustment($adjustments);
    }
    
    /**
     * Log resource adjustment for monitoring
     */
    private function logAdjustment(array $adjustments): void
    {
        $logFile = $this->config->get('paths.logs') . '/dynamic-resources.log';
        
        $logEntry = sprintf(
            "[%s] Strategy: %s | VSCode: %dMB | Available: %dMB | Default: %d jobs/%dMB | Heavy: %d jobs/%dMB | Light: %d jobs/%dMB\n",
            date('Y-m-d H:i:s'),
            $adjustments['strategy'],
            $adjustments['vscode_memory_mb'],
            $adjustments['available_memory_mb'],
            $adjustments['slots']['default']['max_concurrent_jobs'],
            $adjustments['slots']['default']['max_total_memory_mb'],
            $adjustments['slots']['heavy']['max_concurrent_jobs'],
            $adjustments['slots']['heavy']['max_total_memory_mb'],
            $adjustments['slots']['light']['max_concurrent_jobs'],
            $adjustments['slots']['light']['max_total_memory_mb']
        );
        
        file_put_contents($logFile, $logEntry, FILE_APPEND);
    }
    
    /**
     * Parse /proc/meminfo
     */
    private function parseMeminfo(): array
    {
        if (!file_exists('/proc/meminfo')) {
            return [];
        }
        
        $meminfo = [];
        $lines = file('/proc/meminfo');
        
        foreach ($lines as $line) {
            if (preg_match('/^(\w+):\s+(\d+)\s+kB/', $line, $matches)) {
                $meminfo[$matches[1]] = (int)round((int)$matches[2] / 1024); // Convert to MB
            }
        }
        
        return $meminfo;
    }
    
    /**
     * Get CPU count
     */
    private function getCpuCount(): int
    {
        if (file_exists('/proc/cpuinfo')) {
            return (int)shell_exec('grep -c ^processor /proc/cpuinfo');
        }
        return 1;
    }
}
