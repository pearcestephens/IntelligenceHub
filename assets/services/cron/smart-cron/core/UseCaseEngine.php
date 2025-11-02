<?php
/**
 * Smart Cron - Use Case Engine
 *
 * Handles hundreds of different load scenarios with intelligent strategies
 *
 * @package SmartCron\Core
 */

declare(strict_types=1);

namespace SmartCron\Core;

class UseCaseEngine
{
    private DynamicResourceMonitor $monitor;
    private Config $config;
    private array $useCaseHistory = [];

    // Use case categories
    private const CATEGORY_SPIKE = 'spike';
    private const CATEGORY_SUSTAINED = 'sustained';
    private const CATEGORY_GRADUAL = 'gradual';
    private const CATEGORY_BURSTY = 'bursty';
    private const CATEGORY_CYCLIC = 'cyclic';
    private const CATEGORY_TEMPORAL = 'temporal';
    private const CATEGORY_RESOURCE_SPECIFIC = 'resource_specific';
    private const CATEGORY_TASK_SPECIFIC = 'task_specific';
    private const CATEGORY_RECOVERY = 'recovery';
    private const CATEGORY_PREDICTIVE = 'predictive';

    public function __construct(DynamicResourceMonitor $monitor, Config $config)
    {
        $this->monitor = $monitor;
        $this->config = $config;
    }

    /**
     * Analyze current situation and determine use case
     */
    public function detectUseCase(array $snapshot, array $task = []): array
    {
        $useCases = [];

        // Detect all applicable use cases
        $useCases = array_merge($useCases, $this->detectSpikeUseCases($snapshot));
        $useCases = array_merge($useCases, $this->detectSustainedUseCases($snapshot));
        $useCases = array_merge($useCases, $this->detectGradualUseCases($snapshot));
        $useCases = array_merge($useCases, $this->detectBurstyUseCases($snapshot));
        $useCases = array_merge($useCases, $this->detectCyclicUseCases($snapshot));
        $useCases = array_merge($useCases, $this->detectTemporalUseCases($snapshot));
        $useCases = array_merge($useCases, $this->detectResourceSpecificUseCases($snapshot));
        $useCases = array_merge($useCases, $this->detectTaskSpecificUseCases($snapshot, $task));
        $useCases = array_merge($useCases, $this->detectRecoveryUseCases($snapshot));
        $useCases = array_merge($useCases, $this->detectPredictiveUseCases($snapshot));

        // Sort by priority (highest first)
        usort($useCases, fn($a, $b) => $b['priority'] <=> $a['priority']);

        return $useCases;
    }

    /**
     * Get strategy for use case
     */
    public function getStrategy(array $useCase): array
    {
        $strategyMethod = 'strategy' . str_replace(' ', '', ucwords(str_replace('_', ' ', $useCase['id'])));

        if (method_exists($this, $strategyMethod)) {
            return $this->$strategyMethod($useCase);
        }

        return $this->strategyDefault($useCase);
    }

    // ============================================================================
    // SPIKE USE CASES (Sudden Load Increases)
    // ============================================================================

    private function detectSpikeUseCases(array $snapshot): array
    {
        $cases = [];
        $spike = $this->monitor->detectLoadSpike();

        if (!$spike['spike_detected']) {
            return $cases;
        }

        $increasePercent = $spike['increase_percent'];

        // UC001: Minor Spike (50-100% increase)
        if ($increasePercent >= 50 && $increasePercent < 100) {
            $cases[] = [
                'id' => 'spike_minor',
                'category' => self::CATEGORY_SPIKE,
                'name' => 'Minor Load Spike',
                'priority' => 60,
                'confidence' => $spike['confidence'],
                'metrics' => $spike,
            ];
        }

        // UC002: Major Spike (100-200% increase)
        if ($increasePercent >= 100 && $increasePercent < 200) {
            $cases[] = [
                'id' => 'spike_major',
                'category' => self::CATEGORY_SPIKE,
                'name' => 'Major Load Spike',
                'priority' => 80,
                'confidence' => $spike['confidence'],
                'metrics' => $spike,
            ];
        }

        // UC003: Extreme Spike (>200% increase)
        if ($increasePercent >= 200) {
            $cases[] = [
                'id' => 'spike_extreme',
                'category' => self::CATEGORY_SPIKE,
                'name' => 'Extreme Load Spike',
                'priority' => 95,
                'confidence' => $spike['confidence'],
                'metrics' => $spike,
            ];
        }

        // UC004: CPU-Specific Spike
        $cpuIncrease = $this->calculateResourceIncrease('cpu', $snapshot);
        if ($cpuIncrease > 50) {
            $cases[] = [
                'id' => 'spike_cpu',
                'category' => self::CATEGORY_SPIKE,
                'name' => 'CPU Spike',
                'priority' => 70,
                'confidence' => min(100, $cpuIncrease),
                'metrics' => ['cpu_increase' => $cpuIncrease],
            ];
        }

        // UC005: Memory-Specific Spike
        $memIncrease = $this->calculateResourceIncrease('memory', $snapshot);
        if ($memIncrease > 50) {
            $cases[] = [
                'id' => 'spike_memory',
                'category' => self::CATEGORY_SPIKE,
                'name' => 'Memory Spike',
                'priority' => 75,
                'confidence' => min(100, $memIncrease),
                'metrics' => ['memory_increase' => $memIncrease],
            ];
        }

        // UC006: Dual Spike (CPU + Memory)
        if ($cpuIncrease > 50 && $memIncrease > 50) {
            $cases[] = [
                'id' => 'spike_dual',
                'category' => self::CATEGORY_SPIKE,
                'name' => 'Dual Resource Spike',
                'priority' => 85,
                'confidence' => min(100, ($cpuIncrease + $memIncrease) / 2),
                'metrics' => ['cpu_increase' => $cpuIncrease, 'memory_increase' => $memIncrease],
            ];
        }

        // UC007: Flash Spike (very rapid, <10 seconds)
        if ($spike['spike_detected'] && $this->isFlashSpike($snapshot)) {
            $cases[] = [
                'id' => 'spike_flash',
                'category' => self::CATEGORY_SPIKE,
                'name' => 'Flash Spike',
                'priority' => 65,
                'confidence' => $spike['confidence'],
                'metrics' => $spike,
            ];
        }

        // UC008: Cascading Spike (spike causing more spikes)
        if ($this->isCascadingSpike($snapshot)) {
            $cases[] = [
                'id' => 'spike_cascading',
                'category' => self::CATEGORY_SPIKE,
                'name' => 'Cascading Spike',
                'priority' => 90,
                'confidence' => 85,
                'metrics' => $spike,
            ];
        }

        return $cases;
    }

    // ============================================================================
    // SUSTAINED USE CASES (Steady High Load)
    // ============================================================================

    private function detectSustainedUseCases(array $snapshot): array
    {
        $cases = [];
        $overallLoad = $snapshot['overall_load'] ?? 0;
        $duration = $this->getHighLoadDuration($snapshot);

        // UC010: Short Sustained (2-5 minutes at >80%)
        if ($overallLoad > 80 && $duration >= 120 && $duration < 300) {
            $cases[] = [
                'id' => 'sustained_short',
                'category' => self::CATEGORY_SUSTAINED,
                'name' => 'Short Sustained Load',
                'priority' => 55,
                'confidence' => 80,
                'metrics' => ['duration' => $duration, 'load' => $overallLoad],
            ];
        }

        // UC011: Medium Sustained (5-15 minutes)
        if ($overallLoad > 80 && $duration >= 300 && $duration < 900) {
            $cases[] = [
                'id' => 'sustained_medium',
                'category' => self::CATEGORY_SUSTAINED,
                'name' => 'Medium Sustained Load',
                'priority' => 70,
                'confidence' => 85,
                'metrics' => ['duration' => $duration, 'load' => $overallLoad],
            ];
        }

        // UC012: Long Sustained (>15 minutes)
        if ($overallLoad > 80 && $duration >= 900) {
            $cases[] = [
                'id' => 'sustained_long',
                'category' => self::CATEGORY_SUSTAINED,
                'name' => 'Long Sustained Load',
                'priority' => 85,
                'confidence' => 90,
                'metrics' => ['duration' => $duration, 'load' => $overallLoad],
            ];
        }

        // UC013: Plateau (steady at specific level)
        if ($this->isPlateau($snapshot)) {
            $cases[] = [
                'id' => 'sustained_plateau',
                'category' => self::CATEGORY_SUSTAINED,
                'name' => 'Load Plateau',
                'priority' => 60,
                'confidence' => 75,
                'metrics' => ['duration' => $duration, 'load' => $overallLoad],
            ];
        }

        // UC014: Near-Capacity Sustained (>95%)
        if ($overallLoad > 95 && $duration >= 60) {
            $cases[] = [
                'id' => 'sustained_near_capacity',
                'category' => self::CATEGORY_SUSTAINED,
                'name' => 'Near-Capacity Sustained',
                'priority' => 95,
                'confidence' => 95,
                'metrics' => ['duration' => $duration, 'load' => $overallLoad],
            ];
        }

        return $cases;
    }

    // ============================================================================
    // GRADUAL USE CASES (Slow Changes Over Time)
    // ============================================================================

    private function detectGradualUseCases(array $snapshot): array
    {
        $cases = [];
        $prediction = $this->monitor->predictLoad(300); // 5 min ahead
        $trend = $prediction['trend'] ?? 'stable';

        // UC020: Gradual Increase
        if ($trend === 'increasing' && $prediction['slope'] > 0.01) {
            $severity = $this->getGradualSeverity($prediction['slope']);
            $cases[] = [
                'id' => 'gradual_increase_' . $severity,
                'category' => self::CATEGORY_GRADUAL,
                'name' => 'Gradual Load Increase (' . ucfirst($severity) . ')',
                'priority' => match($severity) {
                    'slow' => 40,
                    'moderate' => 60,
                    'fast' => 75,
                    default => 50,
                },
                'confidence' => $prediction['confidence'],
                'metrics' => $prediction,
            ];
        }

        // UC021: Gradual Decrease
        if ($trend === 'decreasing' && $prediction['slope'] < -0.01) {
            $cases[] = [
                'id' => 'gradual_decrease',
                'category' => self::CATEGORY_GRADUAL,
                'name' => 'Gradual Load Decrease',
                'priority' => 30,
                'confidence' => $prediction['confidence'],
                'metrics' => $prediction,
            ];
        }

        // UC022: Creeping Load (very slow increase)
        if ($this->isCreepingLoad($snapshot)) {
            $cases[] = [
                'id' => 'gradual_creeping',
                'category' => self::CATEGORY_GRADUAL,
                'name' => 'Creeping Load',
                'priority' => 50,
                'confidence' => 70,
                'metrics' => $prediction,
            ];
        }

        // UC023: Approaching Threshold
        if ($this->isApproachingThreshold($snapshot, $prediction)) {
            $cases[] = [
                'id' => 'gradual_approaching_threshold',
                'category' => self::CATEGORY_GRADUAL,
                'name' => 'Approaching Threshold',
                'priority' => 80,
                'confidence' => $prediction['confidence'],
                'metrics' => $prediction,
            ];
        }

        return $cases;
    }

    // ============================================================================
    // BURSTY USE CASES (Irregular Patterns)
    // ============================================================================

    private function detectBurstyUseCases(array $snapshot): array
    {
        $cases = [];
        $volatility = $this->calculateVolatility($snapshot);

        // UC030: High Volatility
        if ($volatility > 20) {
            $cases[] = [
                'id' => 'bursty_high_volatility',
                'category' => self::CATEGORY_BURSTY,
                'name' => 'High Load Volatility',
                'priority' => 65,
                'confidence' => min(100, $volatility),
                'metrics' => ['volatility' => $volatility],
            ];
        }

        // UC031: Rapid Oscillation (quick up/down cycles)
        if ($this->isRapidOscillation($snapshot)) {
            $cases[] = [
                'id' => 'bursty_oscillation',
                'category' => self::CATEGORY_BURSTY,
                'name' => 'Rapid Load Oscillation',
                'priority' => 70,
                'confidence' => 80,
                'metrics' => ['volatility' => $volatility],
            ];
        }

        // UC032: Irregular Bursts
        if ($this->hasIrregularBursts($snapshot)) {
            $cases[] = [
                'id' => 'bursty_irregular',
                'category' => self::CATEGORY_BURSTY,
                'name' => 'Irregular Bursts',
                'priority' => 60,
                'confidence' => 75,
                'metrics' => ['volatility' => $volatility],
            ];
        }

        return $cases;
    }

    // ============================================================================
    // CYCLIC USE CASES (Predictable Patterns)
    // ============================================================================

    private function detectCyclicUseCases(array $snapshot): array
    {
        $cases = [];

        // UC040: Hourly Cycle
        if ($this->detectCycle('hourly', $snapshot)) {
            $cases[] = [
                'id' => 'cyclic_hourly',
                'category' => self::CATEGORY_CYCLIC,
                'name' => 'Hourly Load Cycle',
                'priority' => 45,
                'confidence' => 70,
                'metrics' => ['cycle_type' => 'hourly'],
            ];
        }

        // UC041: Daily Cycle
        if ($this->detectCycle('daily', $snapshot)) {
            $cases[] = [
                'id' => 'cyclic_daily',
                'category' => self::CATEGORY_CYCLIC,
                'name' => 'Daily Load Cycle',
                'priority' => 40,
                'confidence' => 75,
                'metrics' => ['cycle_type' => 'daily'],
            ];
        }

        // UC042: Weekly Cycle
        if ($this->detectCycle('weekly', $snapshot)) {
            $cases[] = [
                'id' => 'cyclic_weekly',
                'category' => self::CATEGORY_CYCLIC,
                'name' => 'Weekly Load Cycle',
                'priority' => 35,
                'confidence' => 70,
                'metrics' => ['cycle_type' => 'weekly'],
            ];
        }

        return $cases;
    }

    // ============================================================================
    // TEMPORAL USE CASES (Time-Based)
    // ============================================================================

    private function detectTemporalUseCases(array $snapshot): array
    {
        $cases = [];
        $hour = (int)date('H');
        $dayOfWeek = (int)date('N'); // 1=Monday, 7=Sunday

        // UC050: Peak Hours (9 AM - 5 PM)
        if ($hour >= 9 && $hour < 17) {
            $cases[] = [
                'id' => 'temporal_peak_hours',
                'category' => self::CATEGORY_TEMPORAL,
                'name' => 'Peak Business Hours',
                'priority' => 50,
                'confidence' => 100,
                'metrics' => ['hour' => $hour],
            ];
        }

        // UC051: Off-Peak Hours (10 PM - 6 AM)
        if ($hour >= 22 || $hour < 6) {
            $cases[] = [
                'id' => 'temporal_off_peak',
                'category' => self::CATEGORY_TEMPORAL,
                'name' => 'Off-Peak Hours',
                'priority' => 20,
                'confidence' => 100,
                'metrics' => ['hour' => $hour],
            ];
        }

        // UC052: Lunch Hour (12 PM - 2 PM)
        if ($hour >= 12 && $hour < 14) {
            $cases[] = [
                'id' => 'temporal_lunch',
                'category' => self::CATEGORY_TEMPORAL,
                'name' => 'Lunch Hour',
                'priority' => 45,
                'confidence' => 100,
                'metrics' => ['hour' => $hour],
            ];
        }

        // UC053: Weekend
        if ($dayOfWeek >= 6) {
            $cases[] = [
                'id' => 'temporal_weekend',
                'category' => self::CATEGORY_TEMPORAL,
                'name' => 'Weekend Period',
                'priority' => 25,
                'confidence' => 100,
                'metrics' => ['day' => $dayOfWeek],
            ];
        }

        // UC054: Monday Morning (high load typical)
        if ($dayOfWeek === 1 && $hour >= 8 && $hour < 12) {
            $cases[] = [
                'id' => 'temporal_monday_morning',
                'category' => self::CATEGORY_TEMPORAL,
                'name' => 'Monday Morning Rush',
                'priority' => 65,
                'confidence' => 100,
                'metrics' => ['day' => $dayOfWeek, 'hour' => $hour],
            ];
        }

        // UC055: Friday Afternoon (winding down)
        if ($dayOfWeek === 5 && $hour >= 15) {
            $cases[] = [
                'id' => 'temporal_friday_afternoon',
                'category' => self::CATEGORY_TEMPORAL,
                'name' => 'Friday Afternoon',
                'priority' => 35,
                'confidence' => 100,
                'metrics' => ['day' => $dayOfWeek, 'hour' => $hour],
            ];
        }

        return $cases;
    }

    // ============================================================================
    // RESOURCE-SPECIFIC USE CASES
    // ============================================================================

    private function detectResourceSpecificUseCases(array $snapshot): array
    {
        $cases = [];
        $cpu = $snapshot['cpu']['usage'] ?? 0;
        $mem = $snapshot['memory']['usage_percent'] ?? 0;
        $swap = $snapshot['swap']['usage_percent'] ?? 0;
        $io = $snapshot['disk_io']['iowait_percent'] ?? 0;

        // UC060: CPU-Bound
        if ($cpu > 80 && $mem < 70) {
            $cases[] = [
                'id' => 'resource_cpu_bound',
                'category' => self::CATEGORY_RESOURCE_SPECIFIC,
                'name' => 'CPU-Bound Load',
                'priority' => 70,
                'confidence' => 85,
                'metrics' => ['cpu' => $cpu, 'memory' => $mem],
            ];
        }

        // UC061: Memory-Bound
        if ($mem > 85 && $cpu < 70) {
            $cases[] = [
                'id' => 'resource_memory_bound',
                'category' => self::CATEGORY_RESOURCE_SPECIFIC,
                'name' => 'Memory-Bound Load',
                'priority' => 75,
                'confidence' => 85,
                'metrics' => ['cpu' => $cpu, 'memory' => $mem],
            ];
        }

        // UC062: Swap Thrashing
        if ($swap > 50) {
            $cases[] = [
                'id' => 'resource_swap_thrashing',
                'category' => self::CATEGORY_RESOURCE_SPECIFIC,
                'name' => 'Swap Thrashing',
                'priority' => 90,
                'confidence' => 95,
                'metrics' => ['swap' => $swap],
            ];
        }

        // UC063: I/O Wait High
        if ($io > 30) {
            $cases[] = [
                'id' => 'resource_io_wait',
                'category' => self::CATEGORY_RESOURCE_SPECIFIC,
                'name' => 'High I/O Wait',
                'priority' => 65,
                'confidence' => 80,
                'metrics' => ['iowait' => $io],
            ];
        }

        // UC064: Balanced High Load
        if ($cpu > 75 && $mem > 75 && abs($cpu - $mem) < 15) {
            $cases[] = [
                'id' => 'resource_balanced_high',
                'category' => self::CATEGORY_RESOURCE_SPECIFIC,
                'name' => 'Balanced High Load',
                'priority' => 80,
                'confidence' => 90,
                'metrics' => ['cpu' => $cpu, 'memory' => $mem],
            ];
        }

        // UC065: Memory Leak Pattern
        if ($this->detectMemoryLeak($snapshot)) {
            $cases[] = [
                'id' => 'resource_memory_leak',
                'category' => self::CATEGORY_RESOURCE_SPECIFIC,
                'name' => 'Possible Memory Leak',
                'priority' => 85,
                'confidence' => 70,
                'metrics' => ['memory' => $mem],
            ];
        }

        return $cases;
    }

    // ============================================================================
    // TASK-SPECIFIC USE CASES
    // ============================================================================

    private function detectTaskSpecificUseCases(array $snapshot, array $task): array
    {
        $cases = [];

        if (empty($task)) {
            return $cases;
        }

        $taskType = $task['type'] ?? 'light';
        $taskName = $task['name'] ?? 'unknown';

        // UC070: Heavy Task During High Load
        if ($taskType === 'heavy' && $snapshot['overall_load'] > 70) {
            $cases[] = [
                'id' => 'task_heavy_high_load',
                'category' => self::CATEGORY_TASK_SPECIFIC,
                'name' => 'Heavy Task During High Load',
                'priority' => 85,
                'confidence' => 90,
                'metrics' => ['task' => $taskName, 'load' => $snapshot['overall_load']],
            ];
        }

        // UC071: Database Task (check I/O)
        if (str_contains(strtolower($taskName), 'db') || str_contains(strtolower($taskName), 'database')) {
            $io = $snapshot['disk_io']['iowait_percent'] ?? 0;
            if ($io > 20) {
                $cases[] = [
                    'id' => 'task_database_high_io',
                    'category' => self::CATEGORY_TASK_SPECIFIC,
                    'name' => 'Database Task With High I/O',
                    'priority' => 75,
                    'confidence' => 85,
                    'metrics' => ['task' => $taskName, 'iowait' => $io],
                ];
            }
        }

        // UC072: API Task (network-sensitive)
        if (str_contains(strtolower($taskName), 'api') || str_contains(strtolower($taskName), 'webhook')) {
            $cases[] = [
                'id' => 'task_api_network_sensitive',
                'category' => self::CATEGORY_TASK_SPECIFIC,
                'name' => 'Network-Sensitive API Task',
                'priority' => 60,
                'confidence' => 80,
                'metrics' => ['task' => $taskName],
            ];
        }

        // UC073: Batch Processing Task
        if (str_contains(strtolower($taskName), 'batch') || str_contains(strtolower($taskName), 'bulk')) {
            $cases[] = [
                'id' => 'task_batch_processing',
                'category' => self::CATEGORY_TASK_SPECIFIC,
                'name' => 'Batch Processing Task',
                'priority' => 55,
                'confidence' => 85,
                'metrics' => ['task' => $taskName],
            ];
        }

        // UC074: Real-Time Task (time-sensitive)
        if (str_contains(strtolower($taskName), 'realtime') || str_contains(strtolower($taskName), 'urgent')) {
            $cases[] = [
                'id' => 'task_realtime',
                'category' => self::CATEGORY_TASK_SPECIFIC,
                'name' => 'Real-Time Critical Task',
                'priority' => 95,
                'confidence' => 100,
                'metrics' => ['task' => $taskName],
            ];
        }

        return $cases;
    }

    // ============================================================================
    // RECOVERY USE CASES
    // ============================================================================

    private function detectRecoveryUseCases(array $snapshot): array
    {
        $cases = [];
        $overallLoad = $snapshot['overall_load'] ?? 0;

        // UC080: Recovering From Spike
        if ($this->isRecoveringFromSpike($snapshot)) {
            $cases[] = [
                'id' => 'recovery_from_spike',
                'category' => self::CATEGORY_RECOVERY,
                'name' => 'Recovering From Spike',
                'priority' => 50,
                'confidence' => 75,
                'metrics' => ['load' => $overallLoad],
            ];
        }

        // UC081: Post-Critical Stabilization
        if ($this->isPostCriticalStabilization($snapshot)) {
            $cases[] = [
                'id' => 'recovery_post_critical',
                'category' => self::CATEGORY_RECOVERY,
                'name' => 'Post-Critical Stabilization',
                'priority' => 70,
                'confidence' => 80,
                'metrics' => ['load' => $overallLoad],
            ];
        }

        // UC082: Rapid Recovery (back to normal quickly)
        if ($this->isRapidRecovery($snapshot)) {
            $cases[] = [
                'id' => 'recovery_rapid',
                'category' => self::CATEGORY_RECOVERY,
                'name' => 'Rapid Recovery',
                'priority' => 30,
                'confidence' => 85,
                'metrics' => ['load' => $overallLoad],
            ];
        }

        return $cases;
    }

    // ============================================================================
    // PREDICTIVE USE CASES
    // ============================================================================

    private function detectPredictiveUseCases(array $snapshot): array
    {
        $cases = [];
        $prediction = $this->monitor->predictLoad(60);

        // UC090: Will Hit Critical Soon
        if ($prediction['prediction'] > 90 && $snapshot['overall_load'] < 80) {
            $cases[] = [
                'id' => 'predictive_approaching_critical',
                'category' => self::CATEGORY_PREDICTIVE,
                'name' => 'Approaching Critical (Predicted)',
                'priority' => 85,
                'confidence' => $prediction['confidence'],
                'metrics' => $prediction,
            ];
        }

        // UC091: Safe Window Ahead
        if ($prediction['prediction'] < 50 && $prediction['confidence'] > 70) {
            $cases[] = [
                'id' => 'predictive_safe_window',
                'category' => self::CATEGORY_PREDICTIVE,
                'name' => 'Safe Window Predicted',
                'priority' => 10,
                'confidence' => $prediction['confidence'],
                'metrics' => $prediction,
            ];
        }

        // UC092: Capacity Warning
        if ($this->isPredictedCapacityIssue($prediction, $snapshot)) {
            $cases[] = [
                'id' => 'predictive_capacity_warning',
                'category' => self::CATEGORY_PREDICTIVE,
                'name' => 'Capacity Warning (Predicted)',
                'priority' => 80,
                'confidence' => $prediction['confidence'],
                'metrics' => $prediction,
            ];
        }

        return $cases;
    }

    // ============================================================================
    // HELPER METHODS
    // ============================================================================

    private function calculateResourceIncrease(string $resource, array $snapshot): float
    {
        $history = $this->monitor->getRecentHistory(12);
        if (count($history) < 5) {
            return 0;
        }

        $recent = array_slice($history, -3);
        $baseline = array_slice($history, 0, count($history) - 3);

        if ($resource === 'cpu') {
            $recentAvg = array_sum(array_column(array_column($recent, 'cpu'), 'usage')) / count($recent);
            $baselineAvg = array_sum(array_column(array_column($baseline, 'cpu'), 'usage')) / count($baseline);
        } else {
            $recentAvg = array_sum(array_column(array_column($recent, 'memory'), 'usage_percent')) / count($recent);
            $baselineAvg = array_sum(array_column(array_column($baseline, 'memory'), 'usage_percent')) / count($baseline);
        }

        if ($baselineAvg === 0) {
            return 0;
        }

        return (($recentAvg - $baselineAvg) / $baselineAvg) * 100;
    }

    private function isFlashSpike(array $snapshot): bool
    {
        $history = $this->monitor->getRecentHistory(3);
        return count($history) >= 3;
    }

    private function isCascadingSpike(array $snapshot): bool
    {
        return false; // Simplified
    }

    private function getHighLoadDuration(array $snapshot): int
    {
        $history = $this->monitor->getRecentHistory(60);
        $duration = 0;

        foreach (array_reverse($history) as $h) {
            if (($h['overall_load'] ?? 0) > 80) {
                $duration += 5; // Sample interval
            } else {
                break;
            }
        }

        return $duration;
    }

    private function isPlateau(array $snapshot): bool
    {
        $history = $this->monitor->getRecentHistory(12);
        if (count($history) < 10) {
            return false;
        }

        $loads = array_column($history, 'overall_load');
        $stdDev = $this->standardDeviation($loads);

        return $stdDev < 5; // Very stable
    }

    private function getGradualSeverity(float $slope): string
    {
        if ($slope < 0.05) return 'slow';
        if ($slope < 0.15) return 'moderate';
        return 'fast';
    }

    private function isCreepingLoad(array $snapshot): bool
    {
        $prediction = $this->monitor->predictLoad(300);
        return ($prediction['trend'] ?? '') === 'increasing' && $prediction['slope'] < 0.05;
    }

    private function isApproachingThreshold(array $snapshot, array $prediction): bool
    {
        $current = $snapshot['overall_load'] ?? 0;
        $predicted = $prediction['prediction'] ?? 0;

        return $current < 80 && $predicted > 85;
    }

    private function calculateVolatility(array $snapshot): float
    {
        $history = $this->monitor->getRecentHistory(12);
        if (count($history) < 5) {
            return 0;
        }

        $loads = array_column($history, 'overall_load');
        return $this->standardDeviation($loads);
    }

    private function isRapidOscillation(array $snapshot): bool
    {
        $history = $this->monitor->getRecentHistory(6);
        if (count($history) < 6) {
            return false;
        }

        $changes = 0;
        for ($i = 1; $i < count($history); $i++) {
            $diff = $history[$i]['overall_load'] - $history[$i-1]['overall_load'];
            if (abs($diff) > 20) {
                $changes++;
            }
        }

        return $changes >= 3;
    }

    private function hasIrregularBursts(array $snapshot): bool
    {
        return $this->calculateVolatility($snapshot) > 15;
    }

    private function detectCycle(string $type, array $snapshot): bool
    {
        // Simplified - would need longer history
        return false;
    }

    private function detectMemoryLeak(array $snapshot): bool
    {
        $history = $this->monitor->getRecentHistory(60);
        if (count($history) < 30) {
            return false;
        }

        $memValues = array_column(array_column($history, 'memory'), 'usage_percent');
        $firstHalf = array_slice($memValues, 0, count($memValues) / 2);
        $secondHalf = array_slice($memValues, count($memValues) / 2);

        $firstAvg = array_sum($firstHalf) / count($firstHalf);
        $secondAvg = array_sum($secondHalf) / count($secondHalf);

        return ($secondAvg - $firstAvg) > 10; // Steady 10%+ increase
    }

    private function isRecoveringFromSpike(array $snapshot): bool
    {
        $history = $this->monitor->getRecentHistory(12);
        if (count($history) < 10) {
            return false;
        }

        $recent = array_slice($history, -3);
        $previous = array_slice($history, -6, 3);

        $recentAvg = array_sum(array_column($recent, 'overall_load')) / count($recent);
        $previousAvg = array_sum(array_column($previous, 'overall_load')) / count($previous);

        return $previousAvg > 85 && $recentAvg < 75;
    }

    private function isPostCriticalStabilization(array $snapshot): bool
    {
        return $this->isRecoveringFromSpike($snapshot) && $snapshot['overall_load'] > 60;
    }

    private function isRapidRecovery(array $snapshot): bool
    {
        $history = $this->monitor->getRecentHistory(6);
        if (count($history) < 5) {
            return false;
        }

        $first = $history[0]['overall_load'];
        $last = end($history)['overall_load'];

        return $first > 80 && $last < 50;
    }

    private function isPredictedCapacityIssue(array $prediction, array $snapshot): bool
    {
        return ($prediction['prediction'] ?? 0) > 95 && ($snapshot['overall_load'] ?? 0) < 85;
    }

    private function standardDeviation(array $values): float
    {
        $count = count($values);
        if ($count === 0) return 0;

        $mean = array_sum($values) / $count;
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $values)) / $count;

        return sqrt($variance);
    }

    // ============================================================================
    // STRATEGIES (Return thresholds and actions)
    // ============================================================================

    private function strategySpikeMinor(array $useCase): array
    {
        return [
            'cpu_threshold' => 85,
            'memory_threshold' => 92,
            'max_concurrent_heavy' => 1,
            'max_concurrent_medium' => 3,
            'max_concurrent_light' => 10,
            'actions' => ['monitor_closely', 'prepare_throttle'],
        ];
    }

    private function strategySpikeExtreme(array $useCase): array
    {
        return [
            'cpu_threshold' => 98,
            'memory_threshold' => 98,
            'max_concurrent_heavy' => 0,
            'max_concurrent_medium' => 1,
            'max_concurrent_light' => 3,
            'actions' => ['emergency_throttle', 'kill_non_critical', 'alert_admin'],
        ];
    }

    private function strategySustainedLong(array $useCase): array
    {
        return [
            'cpu_threshold' => 80,
            'memory_threshold' => 88,
            'max_concurrent_heavy' => 1,
            'max_concurrent_medium' => 2,
            'max_concurrent_light' => 8,
            'actions' => ['aggressive_throttle', 'queue_non_critical'],
        ];
    }

    private function strategyResourceSwapThrashing(array $useCase): array
    {
        return [
            'cpu_threshold' => 95,
            'memory_threshold' => 98,
            'max_concurrent_heavy' => 0,
            'max_concurrent_medium' => 0,
            'max_concurrent_light' => 2,
            'actions' => ['emergency_stop', 'memory_cleanup', 'alert_critical'],
        ];
    }

    private function strategyTemporalOffPeak(array $useCase): array
    {
        return [
            'cpu_threshold' => 95,
            'memory_threshold' => 97,
            'max_concurrent_heavy' => 3,
            'max_concurrent_medium' => 8,
            'max_concurrent_light' => 20,
            'actions' => ['allow_more_tasks', 'batch_processing'],
        ];
    }

    private function strategyDefault(array $useCase): array
    {
        return [
            'cpu_threshold' => 90,
            'memory_threshold' => 95,
            'max_concurrent_heavy' => 2,
            'max_concurrent_medium' => 5,
            'max_concurrent_light' => 15,
            'actions' => ['standard_operation'],
        ];
    }
}
