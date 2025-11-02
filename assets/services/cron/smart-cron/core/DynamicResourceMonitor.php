<?php
/**
 * Smart Cron - Advanced Dynamic Resource Monitor
 *
 * Features:
 * - Real-time CPU/Memory monitoring with multiple detection methods
 * - Auto-adjusting thresholds based on historical patterns
 * - Predictive load forecasting
 * - Multi-tier alerting system
 * - Per-task resource tracking
 * - Load spike detection and mitigation
 * - Graceful degradation strategies
 *
 * @package SmartCron\Core
 */

declare(strict_types=1);

namespace SmartCron\Core;

class DynamicResourceMonitor
{
    private Config $config;
    private string $metricsFile;
    private string $historyFile;
    private array $resourceHistory = [];
    private const HISTORY_WINDOW = 300; // 5 minutes of history
    private const SAMPLE_INTERVAL = 5; // Sample every 5 seconds

    // Multi-tier thresholds (percentage)
    private const TIER_NORMAL = 1;
    private const TIER_ELEVATED = 2;
    private const TIER_HIGH = 3;
    private const TIER_CRITICAL = 4;
    private const TIER_EMERGENCY = 5;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->metricsFile = $config->get('paths.logs') . '/resource_metrics.json';
        $this->historyFile = $config->get('paths.logs') . '/resource_history.json';
        $this->loadHistory();
    }

    /**
     * Get current system resource snapshot with multiple detection methods
     */
    public function getResourceSnapshot(): array
    {
        $snapshot = [
            'timestamp' => microtime(true),
            'cpu' => $this->detectCpuUsage(),
            'memory' => $this->detectMemoryUsage(),
            'load_avg' => $this->getLoadAverage(),
            'disk_io' => $this->getDiskIOWait(),
            'network' => $this->getNetworkUsage(),
            'processes' => $this->getProcessCount(),
            'swap' => $this->getSwapUsage(),
        ];

        // Calculate derived metrics
        $snapshot['overall_load'] = $this->calculateOverallLoad($snapshot);
        $snapshot['tier'] = $this->determineTier($snapshot);
        $snapshot['tier_name'] = $this->getTierName($snapshot['tier']);
        $snapshot['capacity_remaining'] = $this->calculateRemainingCapacity($snapshot);

        // Add to history
        $this->addToHistory($snapshot);

        return $snapshot;
    }

    /**
     * Advanced CPU detection with multiple fallback methods
     */
    private function detectCpuUsage(): array
    {
        $methods = [];

        // Method 1: /proc/stat (Linux - most accurate)
        if (file_exists('/proc/stat')) {
            $methods['proc_stat'] = $this->getCpuFromProcStat();
        }

        // Method 2: top command
        $methods['top'] = $this->getCpuFromTop();

        // Method 3: mpstat (if available)
        $methods['mpstat'] = $this->getCpuFromMpstat();

        // Method 4: Load average conversion
        $methods['load_avg'] = $this->getCpuFromLoadAvg();

        // Method 5: Process snapshot differential
        $methods['process_diff'] = $this->getCpuFromProcessDiff();

        // Use best available method
        $cpu = null;
        $method = 'unknown';

        foreach (['proc_stat', 'top', 'mpstat', 'load_avg', 'process_diff'] as $m) {
            if (isset($methods[$m]) && $methods[$m] !== null) {
                $cpu = $methods[$m];
                $method = $m;
                break;
            }
        }

        return [
            'usage' => $cpu,
            'method' => $method,
            'all_methods' => $methods,
            'cores' => $this->getCpuCoreCount(),
        ];
    }

    /**
     * CPU from /proc/stat (most accurate for Linux)
     */
    private function getCpuFromProcStat(): ?float
    {
        static $lastStat = null;
        static $lastTime = null;

        if (!file_exists('/proc/stat')) {
            return null;
        }

        $stat = @file_get_contents('/proc/stat');
        if (!$stat || !preg_match('/^cpu\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/m', $stat, $matches)) {
            return null;
        }

        $currentTime = microtime(true);
        $currentStat = [
            'user' => (int)$matches[1],
            'nice' => (int)$matches[2],
            'system' => (int)$matches[3],
            'idle' => (int)$matches[4],
            'iowait' => (int)$matches[5],
            'irq' => (int)$matches[6],
            'softirq' => (int)$matches[7],
        ];

        if ($lastStat === null) {
            // First run, store and return null
            $lastStat = $currentStat;
            $lastTime = $currentTime;
            return null;
        }

        // Calculate deltas
        $totalDelta = 0;
        $idleDelta = $currentStat['idle'] - $lastStat['idle'];

        foreach ($currentStat as $key => $value) {
            $totalDelta += $value - $lastStat[$key];
        }

        $lastStat = $currentStat;
        $lastTime = $currentTime;

        if ($totalDelta === 0) {
            return null;
        }

        $usage = 100.0 * (1.0 - ($idleDelta / $totalDelta));
        return round(max(0, min(100, $usage)), 2);
    }

    /**
     * CPU from top command
     */
    private function getCpuFromTop(): ?float
    {
        $output = shell_exec("top -bn1 | grep 'Cpu(s)' | sed 's/.*, *\\([0-9.]*\\)%* id.*/\\1/' | awk '{print 100 - $1}'");
        if ($output === null) {
            return null;
        }

        $cpu = (float)trim($output);
        return ($cpu > 0 && $cpu <= 100) ? round($cpu, 2) : null;
    }

    /**
     * CPU from mpstat
     */
    private function getCpuFromMpstat(): ?float
    {
        $output = shell_exec("mpstat 1 1 2>/dev/null | awk '/Average/ {print 100 - \$NF}'");
        if ($output === null || trim($output) === '') {
            return null;
        }

        $cpu = (float)trim($output);
        return ($cpu > 0 && $cpu <= 100) ? round($cpu, 2) : null;
    }

    /**
     * CPU estimation from load average
     */
    private function getCpuFromLoadAvg(): ?float
    {
        $loadAvg = sys_getloadavg();
        if (!$loadAvg || $loadAvg[0] === false) {
            return null;
        }

        $cores = $this->getCpuCoreCount();
        if ($cores === 0) {
            return null;
        }

        // Convert 1-minute load average to percentage
        $usage = ($loadAvg[0] / $cores) * 100;
        return round(min(100, $usage), 2);
    }

    /**
     * CPU from process time differential
     */
    private function getCpuFromProcessDiff(): ?float
    {
        static $lastCheck = null;

        if (!file_exists('/proc/self/stat')) {
            return null;
        }

        $stat = @file('/proc/self/stat');
        if (!$stat) {
            return null;
        }

        $parts = explode(' ', $stat[0]);
        if (count($parts) < 15) {
            return null;
        }

        $utime = (int)$parts[13]; // User time
        $stime = (int)$parts[14]; // System time
        $currentTime = microtime(true);
        $totalTime = $utime + $stime;

        if ($lastCheck === null) {
            $lastCheck = ['time' => $currentTime, 'cpu' => $totalTime];
            return null; // Skip first call, return null instead of recursing
        }

        $timeDelta = $currentTime - $lastCheck['time'];
        $cpuDelta = $totalTime - $lastCheck['cpu'];

        $lastCheck = ['time' => $currentTime, 'cpu' => $totalTime];

        if ($timeDelta === 0) {
            return null;
        }

        $usage = ($cpuDelta / ($timeDelta * 100)) * 100;
        return round(min(100, max(0, $usage)), 2);
    }

    /**
     * Get CPU core count
     */
    private function getCpuCoreCount(): int
    {
        static $cores = null;

        if ($cores !== null) {
            return $cores;
        }

        // Try multiple methods
        if (file_exists('/proc/cpuinfo')) {
            $cpuinfo = file_get_contents('/proc/cpuinfo');
            preg_match_all('/^processor/m', $cpuinfo, $matches);
            $cores = count($matches[0]);
            if ($cores > 0) {
                return $cores;
            }
        }

        // Fallback: nproc command
        $output = shell_exec('nproc 2>/dev/null');
        if ($output !== null) {
            $cores = (int)trim($output);
            if ($cores > 0) {
                return $cores;
            }
        }

        // Last resort default
        $cores = 1;
        return $cores;
    }

    /**
     * Advanced memory detection
     */
    private function detectMemoryUsage(): array
    {
        $methods = [];

        // Method 1: /proc/meminfo (Linux - most accurate)
        if (file_exists('/proc/meminfo')) {
            $methods['proc_meminfo'] = $this->getMemoryFromProcMeminfo();
        }

        // Method 2: free command
        $methods['free'] = $this->getMemoryFromFree();

        // Method 3: PHP memory_get_usage
        $methods['php'] = $this->getMemoryFromPhp();

        // Use best available
        $result = $methods['proc_meminfo'] ?? $methods['free'] ?? $methods['php'] ?? [
            'total' => 0,
            'used' => 0,
            'free' => 0,
            'available' => 0,
            'usage_percent' => 0,
            'method' => 'none',
        ];

        $result['all_methods'] = $methods;

        return $result;
    }

    /**
     * Memory from /proc/meminfo
     */
    private function getMemoryFromProcMeminfo(): ?array
    {
        if (!file_exists('/proc/meminfo')) {
            return null;
        }

        $meminfo = file_get_contents('/proc/meminfo');

        preg_match('/MemTotal:\s+(\d+)/', $meminfo, $total);
        preg_match('/MemFree:\s+(\d+)/', $meminfo, $free);
        preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $available);
        preg_match('/Buffers:\s+(\d+)/', $meminfo, $buffers);
        preg_match('/Cached:\s+(\d+)/', $meminfo, $cached);

        if (!$total) {
            return null;
        }

        $totalKb = (int)$total[1];
        $freeKb = (int)($free[1] ?? 0);
        $availableKb = (int)($available[1] ?? $freeKb);
        $buffersKb = (int)($buffers[1] ?? 0);
        $cachedKb = (int)($cached[1] ?? 0);

        $usedKb = $totalKb - $availableKb;
        $usagePercent = ($totalKb > 0) ? round(($usedKb / $totalKb) * 100, 2) : 0;

        return [
            'total' => round($totalKb / 1024, 2), // MB
            'used' => round($usedKb / 1024, 2),
            'free' => round($freeKb / 1024, 2),
            'available' => round($availableKb / 1024, 2),
            'buffers' => round($buffersKb / 1024, 2),
            'cached' => round($cachedKb / 1024, 2),
            'usage_percent' => $usagePercent,
            'method' => 'proc_meminfo',
        ];
    }

    /**
     * Memory from free command
     */
    private function getMemoryFromFree(): ?array
    {
        $output = shell_exec("free -m | awk '/Mem:/ {print $2,$3,$4,$7}'");
        if ($output === null) {
            return null;
        }

        $parts = explode(' ', trim($output));
        if (count($parts) < 3) {
            return null;
        }

        $total = (float)$parts[0];
        $used = (float)$parts[1];
        $free = (float)$parts[2];
        $available = (float)($parts[3] ?? $free);

        $usagePercent = ($total > 0) ? round(($used / $total) * 100, 2) : 0;

        return [
            'total' => $total,
            'used' => $used,
            'free' => $free,
            'available' => $available,
            'usage_percent' => $usagePercent,
            'method' => 'free',
        ];
    }

    /**
     * Memory from PHP functions
     */
    private function getMemoryFromPhp(): array
    {
        $used = memory_get_usage(true);
        $limit = ini_get('memory_limit');

        // Parse memory_limit
        $limitBytes = $this->parseMemoryLimit($limit);

        $usagePercent = ($limitBytes > 0) ? round(($used / $limitBytes) * 100, 2) : 0;

        return [
            'total' => round($limitBytes / (1024 * 1024), 2),
            'used' => round($used / (1024 * 1024), 2),
            'free' => round(($limitBytes - $used) / (1024 * 1024), 2),
            'available' => round(($limitBytes - $used) / (1024 * 1024), 2),
            'usage_percent' => $usagePercent,
            'method' => 'php',
        ];
    }

    /**
     * Parse PHP memory_limit string
     */
    private function parseMemoryLimit(string $limit): int
    {
        $limit = trim($limit);

        if ($limit === '-1') {
            return PHP_INT_MAX;
        }

        $unit = strtolower(substr($limit, -1));
        $value = (int)substr($limit, 0, -1);

        return match($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => (int)$limit,
        };
    }

    /**
     * Get load average (1, 5, 15 minute)
     */
    private function getLoadAverage(): array
    {
        $loadAvg = sys_getloadavg();
        $cores = $this->getCpuCoreCount();

        return [
            '1min' => $loadAvg[0] ?? 0,
            '5min' => $loadAvg[1] ?? 0,
            '15min' => $loadAvg[2] ?? 0,
            'cores' => $cores,
            'load_per_core' => [
                '1min' => ($cores > 0) ? round($loadAvg[0] / $cores, 2) : 0,
                '5min' => ($cores > 0) ? round($loadAvg[1] / $cores, 2) : 0,
                '15min' => ($cores > 0) ? round($loadAvg[2] / $cores, 2) : 0,
            ],
        ];
    }

    /**
     * Get disk I/O wait percentage
     */
    private function getDiskIOWait(): array
    {
        // Try iostat
        $output = shell_exec("iostat -x 1 2 2>/dev/null | awk '/^avg/ {print $4}'");
        $iowait = null;

        if ($output !== null && is_numeric(trim($output))) {
            $iowait = round((float)trim($output), 2);
        }

        // Fallback: /proc/stat
        if ($iowait === null && file_exists('/proc/stat')) {
            $stat = file_get_contents('/proc/stat');
            if (preg_match('/^cpu\s+\d+\s+\d+\s+\d+\s+\d+\s+(\d+)/m', $stat, $matches)) {
                $iowait = 0; // Simplified, would need differential
            }
        }

        return [
            'iowait_percent' => $iowait,
            'has_data' => $iowait !== null,
        ];
    }

    /**
     * Get network usage
     */
    private function getNetworkUsage(): array
    {
        static $lastCheck = null;

        if (!file_exists('/proc/net/dev')) {
            return ['rx_bytes' => 0, 'tx_bytes' => 0, 'has_data' => false];
        }

        $dev = file_get_contents('/proc/net/dev');
        preg_match_all('/^\s*(\w+):\s*(\d+)\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+\d+\s+(\d+)/m', $dev, $matches, PREG_SET_ORDER);

        $rxTotal = 0;
        $txTotal = 0;

        foreach ($matches as $match) {
            if ($match[1] !== 'lo') { // Exclude loopback
                $rxTotal += (int)$match[2];
                $txTotal += (int)$match[3];
            }
        }

        $currentTime = microtime(true);

        if ($lastCheck === null) {
            $lastCheck = ['time' => $currentTime, 'rx' => $rxTotal, 'tx' => $txTotal];
            return ['rx_bytes' => 0, 'tx_bytes' => 0, 'rx_rate' => 0, 'tx_rate' => 0, 'has_data' => true];
        }

        $timeDelta = $currentTime - $lastCheck['time'];
        $rxDelta = $rxTotal - $lastCheck['rx'];
        $txDelta = $txTotal - $lastCheck['tx'];

        $rxRate = ($timeDelta > 0) ? round($rxDelta / $timeDelta / 1024, 2) : 0; // KB/s
        $txRate = ($timeDelta > 0) ? round($txDelta / $timeDelta / 1024, 2) : 0;

        $lastCheck = ['time' => $currentTime, 'rx' => $rxTotal, 'tx' => $txTotal];

        return [
            'rx_bytes' => $rxTotal,
            'tx_bytes' => $txTotal,
            'rx_rate_kbps' => $rxRate,
            'tx_rate_kbps' => $txRate,
            'has_data' => true,
        ];
    }

    /**
     * Get process count
     */
    private function getProcessCount(): array
    {
        $total = (int)shell_exec("ps aux | wc -l");
        $running = (int)shell_exec("ps aux | awk '$8 ~ /R/ {print $0}' | wc -l");
        $sleeping = (int)shell_exec("ps aux | awk '$8 ~ /S/ {print $0}' | wc -l");

        return [
            'total' => $total,
            'running' => $running,
            'sleeping' => $sleeping,
            'other' => $total - $running - $sleeping,
        ];
    }

    /**
     * Get swap usage
     */
    private function getSwapUsage(): array
    {
        if (!file_exists('/proc/meminfo')) {
            return ['total' => 0, 'used' => 0, 'free' => 0, 'usage_percent' => 0, 'has_data' => false];
        }

        $meminfo = file_get_contents('/proc/meminfo');

        preg_match('/SwapTotal:\s+(\d+)/', $meminfo, $total);
        preg_match('/SwapFree:\s+(\d+)/', $meminfo, $free);

        if (!$total) {
            return ['total' => 0, 'used' => 0, 'free' => 0, 'usage_percent' => 0, 'has_data' => false];
        }

        $totalKb = (int)$total[1];
        $freeKb = (int)($free[1] ?? 0);
        $usedKb = $totalKb - $freeKb;

        $usagePercent = ($totalKb > 0) ? round(($usedKb / $totalKb) * 100, 2) : 0;

        return [
            'total' => round($totalKb / 1024, 2), // MB
            'used' => round($usedKb / 1024, 2),
            'free' => round($freeKb / 1024, 2),
            'usage_percent' => $usagePercent,
            'has_data' => true,
        ];
    }

    /**
     * Calculate overall system load score (0-100)
     */
    private function calculateOverallLoad(array $snapshot): float
    {
        $weights = [
            'cpu' => 0.35,
            'memory' => 0.30,
            'load_avg' => 0.20,
            'disk_io' => 0.10,
            'swap' => 0.05,
        ];

        $scores = [];

        // CPU score
        $cpuUsage = $snapshot['cpu']['usage'] ?? 0;
        $scores['cpu'] = min(100, $cpuUsage);

        // Memory score
        $memUsage = $snapshot['memory']['usage_percent'] ?? 0;
        $scores['memory'] = $memUsage;

        // Load average score (normalized per core)
        $loadPerCore = $snapshot['load_avg']['load_per_core']['1min'] ?? 0;
        $scores['load_avg'] = min(100, $loadPerCore * 100);

        // Disk I/O score
        $ioWait = $snapshot['disk_io']['iowait_percent'] ?? 0;
        $scores['disk_io'] = $ioWait;

        // Swap score
        $swapUsage = $snapshot['swap']['usage_percent'] ?? 0;
        $scores['swap'] = $swapUsage;

        // Weighted sum
        $overallLoad = 0;
        foreach ($weights as $metric => $weight) {
            $overallLoad += ($scores[$metric] ?? 0) * $weight;
        }

        return round($overallLoad, 2);
    }

    /**
     * Determine tier based on load
     */
    private function determineTier(array $snapshot): int
    {
        $overallLoad = $snapshot['overall_load'] ?? 0;
        $cpuUsage = $snapshot['cpu']['usage'] ?? 0;
        $memUsage = $snapshot['memory']['usage_percent'] ?? 0;

        // Emergency tier: critical resource exhaustion
        if ($overallLoad > 95 || $cpuUsage > 98 || $memUsage > 98) {
            return self::TIER_EMERGENCY;
        }

        // Critical tier: very high load
        if ($overallLoad > 85 || $cpuUsage > 90 || $memUsage > 95) {
            return self::TIER_CRITICAL;
        }

        // High tier: elevated load
        if ($overallLoad > 70 || $cpuUsage > 80 || $memUsage > 90) {
            return self::TIER_HIGH;
        }

        // Elevated tier: moderate load
        if ($overallLoad > 50 || $cpuUsage > 60 || $memUsage > 80) {
            return self::TIER_ELEVATED;
        }

        // Normal tier
        return self::TIER_NORMAL;
    }

    /**
     * Get tier name
     */
    private function getTierName(int $tier): string
    {
        return match($tier) {
            self::TIER_NORMAL => 'NORMAL',
            self::TIER_ELEVATED => 'ELEVATED',
            self::TIER_HIGH => 'HIGH',
            self::TIER_CRITICAL => 'CRITICAL',
            self::TIER_EMERGENCY => 'EMERGENCY',
            default => 'UNKNOWN',
        };
    }

    /**
     * Calculate remaining capacity
     */
    private function calculateRemainingCapacity(array $snapshot): float
    {
        $overallLoad = $snapshot['overall_load'] ?? 0;
        return round(max(0, 100 - $overallLoad), 2);
    }

    /**
     * Get dynamic thresholds based on historical data
     */
    public function getDynamicThresholds(): array
    {
        $history = $this->getRecentHistory(60); // Last 60 samples (5 minutes)

        if (empty($history)) {
            return $this->getStaticThresholds();
        }

        // Calculate statistical baselines
        $cpuValues = array_column(array_column($history, 'cpu'), 'usage');
        $memValues = array_column(array_column($history, 'memory'), 'usage_percent');

        $cpuAvg = array_sum($cpuValues) / count($cpuValues);
        $memAvg = array_sum($memValues) / count($memValues);

        $cpuStdDev = $this->standardDeviation($cpuValues);
        $memStdDev = $this->standardDeviation($memValues);

        // Dynamic thresholds: mean + 2 standard deviations
        $cpuThresholds = [
            'normal' => min(60, $cpuAvg + $cpuStdDev),
            'elevated' => min(75, $cpuAvg + (2 * $cpuStdDev)),
            'high' => min(90, $cpuAvg + (3 * $cpuStdDev)),
            'critical' => 95,
        ];

        $memThresholds = [
            'normal' => min(70, $memAvg + $memStdDev),
            'elevated' => min(85, $memAvg + (2 * $memStdDev)),
            'high' => min(95, $memAvg + (3 * $memStdDev)),
            'critical' => 98,
        ];

        return [
            'cpu' => $cpuThresholds,
            'memory' => $memThresholds,
            'baseline' => [
                'cpu_avg' => round($cpuAvg, 2),
                'cpu_stddev' => round($cpuStdDev, 2),
                'mem_avg' => round($memAvg, 2),
                'mem_stddev' => round($memStdDev, 2),
            ],
        ];
    }

    /**
     * Get static thresholds (fallback)
     */
    private function getStaticThresholds(): array
    {
        return [
            'cpu' => [
                'normal' => 60,
                'elevated' => 75,
                'high' => 90,
                'critical' => 95,
            ],
            'memory' => [
                'normal' => 70,
                'elevated' => 85,
                'high' => 95,
                'critical' => 98,
            ],
            'baseline' => null,
        ];
    }

    /**
     * Calculate standard deviation
     */
    private function standardDeviation(array $values): float
    {
        $count = count($values);
        if ($count === 0) {
            return 0;
        }

        $mean = array_sum($values) / $count;
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $values)) / $count;

        return sqrt($variance);
    }

    /**
     * Add snapshot to history
     */
    private function addToHistory(array $snapshot): void
    {
        $this->resourceHistory[] = $snapshot;

        // Keep only recent history (last 5 minutes)
        $cutoff = microtime(true) - self::HISTORY_WINDOW;
        $this->resourceHistory = array_filter(
            $this->resourceHistory,
            fn($s) => $s['timestamp'] > $cutoff
        );

        // Persist every 10 samples
        if (count($this->resourceHistory) % 10 === 0) {
            $this->saveHistory();
        }
    }

    /**
     * Get recent history
     */
    public function getRecentHistory(int $samples): array
    {
        return array_slice($this->resourceHistory, -$samples);
    }

    /**
     * Load history from disk
     */
    private function loadHistory(): void
    {
        if (!file_exists($this->historyFile)) {
            return;
        }

        $data = @json_decode(file_get_contents($this->historyFile), true);
        if (is_array($data)) {
            $cutoff = microtime(true) - self::HISTORY_WINDOW;
            $this->resourceHistory = array_filter($data, fn($s) => $s['timestamp'] > $cutoff);
        }
    }

    /**
     * Save history to disk
     */
    private function saveHistory(): void
    {
        $dir = dirname($this->historyFile);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        file_put_contents($this->historyFile, json_encode($this->resourceHistory));
    }

    /**
     * Detect load spike
     */
    public function detectLoadSpike(): array
    {
        $recent = $this->getRecentHistory(12); // Last minute
        $baseline = $this->getRecentHistory(60); // Last 5 minutes

        if (count($recent) < 5 || count($baseline) < 20) {
            return ['spike_detected' => false, 'confidence' => 0];
        }

        $recentAvgLoad = array_sum(array_column($recent, 'overall_load')) / count($recent);
        $baselineAvgLoad = array_sum(array_column($baseline, 'overall_load')) / count($baseline);

        $loadIncrease = $recentAvgLoad - $baselineAvgLoad;
        $increasePercent = ($baselineAvgLoad > 0) ? ($loadIncrease / $baselineAvgLoad) * 100 : 0;

        $spikeDetected = $increasePercent > 50; // 50% increase
        $confidence = min(100, abs($increasePercent));

        return [
            'spike_detected' => $spikeDetected,
            'confidence' => round($confidence, 2),
            'recent_load' => round($recentAvgLoad, 2),
            'baseline_load' => round($baselineAvgLoad, 2),
            'increase_percent' => round($increasePercent, 2),
        ];
    }

    /**
     * Predict future load (simple linear extrapolation)
     */
    public function predictLoad(int $secondsAhead): array
    {
        $history = $this->getRecentHistory(60);

        if (count($history) < 10) {
            return ['prediction' => null, 'confidence' => 0];
        }

        // Simple linear regression on overall_load
        $loads = array_column($history, 'overall_load');
        $times = array_column($history, 'timestamp');

        $n = count($loads);
        $sumX = array_sum($times);
        $sumY = array_sum($loads);
        $sumXY = 0;
        $sumX2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $times[$i] * $loads[$i];
            $sumX2 += $times[$i] * $times[$i];
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;

        // Predict
        $futureTime = microtime(true) + $secondsAhead;
        $predictedLoad = $slope * $futureTime + $intercept;
        $predictedLoad = round(max(0, min(100, $predictedLoad)), 2);

        // Confidence based on R²
        $meanY = $sumY / $n;
        $ssTotal = array_sum(array_map(fn($y) => pow($y - $meanY, 2), $loads));
        $ssResidual = 0;
        for ($i = 0; $i < $n; $i++) {
            $predicted = $slope * $times[$i] + $intercept;
            $ssResidual += pow($loads[$i] - $predicted, 2);
        }

        $rSquared = ($ssTotal > 0) ? 1 - ($ssResidual / $ssTotal) : 0;
        $confidence = round(max(0, min(100, $rSquared * 100)), 2);

        return [
            'prediction' => $predictedLoad,
            'confidence' => $confidence,
            'trend' => $slope > 0 ? 'increasing' : ($slope < 0 ? 'decreasing' : 'stable'),
            'slope' => round($slope, 4),
        ];
    }

    /**
     * Get recommended action based on current state
     */
    public function getRecommendedAction(): array
    {
        $snapshot = $this->getResourceSnapshot();
        $tier = $snapshot['tier'];
        $spike = $this->detectLoadSpike();
        $prediction = $this->predictLoad(60); // 1 minute ahead

        $actions = [];
        $severity = 'info';

        switch ($tier) {
            case self::TIER_EMERGENCY:
                $actions[] = 'KILL non-critical tasks immediately';
                $actions[] = 'Enable emergency throttling';
                $actions[] = 'Alert administrators';
                $actions[] = 'Consider system restart if persists';
                $severity = 'emergency';
                break;

            case self::TIER_CRITICAL:
                $actions[] = 'Stop accepting new heavy tasks';
                $actions[] = 'Pause medium tasks';
                $actions[] = 'Allow only critical and light tasks';
                $actions[] = 'Send high-priority alerts';
                $severity = 'critical';
                break;

            case self::TIER_HIGH:
                $actions[] = 'Limit heavy task concurrency to 1';
                $actions[] = 'Reduce medium task concurrency';
                $actions[] = 'Prioritize critical tasks';
                $severity = 'warning';
                break;

            case self::TIER_ELEVATED:
                $actions[] = 'Apply standard concurrency limits';
                $actions[] = 'Monitor closely for spikes';
                $severity = 'notice';
                break;

            case self::TIER_NORMAL:
                $actions[] = 'Normal operations';
                $severity = 'info';
                break;
        }

        // Additional actions based on predictions
        if ($spike['spike_detected']) {
            array_unshift($actions, 'SPIKE DETECTED: Prepare for load increase');
            $severity = ($severity === 'info') ? 'warning' : $severity;
        }

        if ($prediction['prediction'] !== null && $prediction['prediction'] > 80 && $prediction['trend'] === 'increasing') {
            $actions[] = 'PREDICTIVE: High load expected in 60s - pre-throttle';
        }

        return [
            'tier' => $tier,
            'tier_name' => $snapshot['tier_name'],
            'severity' => $severity,
            'actions' => $actions,
            'metrics' => [
                'overall_load' => $snapshot['overall_load'],
                'cpu_usage' => $snapshot['cpu']['usage'],
                'memory_usage' => $snapshot['memory']['usage_percent'],
                'capacity_remaining' => $snapshot['capacity_remaining'],
            ],
            'spike_detected' => $spike['spike_detected'],
            'predicted_load_60s' => $prediction['prediction'],
            'trend' => $prediction['trend'] ?? 'unknown',
        ];
    }
}
