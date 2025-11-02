<?php
/**
 * Smart Cron - Schedule Optimizer
 *
 * Generates optimal schedules based on task classifications.
 * Heavy tasks run overnight, medium tasks off-peak, light tasks distributed evenly.
 *
 * @package SmartCron\Core
 */

declare(strict_types=1);

namespace SmartCron\Core;

class ScheduleOptimizer
{
    private Config $config;
    private TaskAnalyzer $analyzer;
    private array $schedule = [];

    public function __construct(Config $config, TaskAnalyzer $analyzer)
    {
        $this->config = $config;
        $this->analyzer = $analyzer;
        $this->loadSchedule();
    }

    /**
     * Load current schedule from file
     */
    private function loadSchedule(): void
    {
        $scheduleFile = dirname(__DIR__) . '/config/schedule.json';

        if (file_exists($scheduleFile)) {
            $this->schedule = json_decode(file_get_contents($scheduleFile), true) ?? [];
        }
    }

    /**
     * Generate optimized schedule based on task definitions from database
     *
     * @param array $analysis Optional task analysis from TaskAnalyzer (for optimization)
     * @return array Optimized schedule
     */
    public function generateSchedule(array $analysis = []): array
    {
        // Load tasks from database via Config
        $tasks = $this->config->getTasks();

        if (empty($tasks)) {
            error_log('[ScheduleOptimizer] No tasks found in database');
            return [];
        }

        $schedule = [];

        // Initialize all 60 minutes
        for ($i = 0; $i < 60; $i++) {
            $schedule[$i] = [];
        }

        // Process each task based on its frequency
        foreach ($tasks as $task) {
            $frequency = $task['frequency'] ?? 'daily';
            $name = $task['name'];

            switch ($frequency) {
                case 'every_minute':
                    // Critical tasks: run every minute
                    for ($minute = 0; $minute < 60; $minute++) {
                        $schedule[$minute][] = [
                            'name' => $name,
                            'script' => $task['script'],
                            'type' => $task['type'] ?? 'light'
                        ];
                    }
                    break;

                case 'every_2_minutes':
                    // Run every 2 minutes starting at offset
                    $offset = $task['offset'] ?? 0;
                    for ($minute = $offset; $minute < 60; $minute += 2) {
                        $schedule[$minute][] = [
                            'name' => $name,
                            'script' => $task['script'],
                            'type' => $task['type'] ?? 'light'
                        ];
                    }
                    break;

                case 'every_5_minutes':
                    // Run every 5 minutes starting at offset
                    $offset = $task['offset'] ?? 0;
                    for ($minute = $offset; $minute < 60; $minute += 5) {
                        $schedule[$minute][] = [
                            'name' => $name,
                            'script' => $task['script'],
                            'type' => $task['type'] ?? 'light'
                        ];
                    }
                    break;

                case 'every_10_minutes':
                    // Run every 10 minutes starting at offset
                    $offset = $task['offset'] ?? 0;
                    for ($minute = $offset; $minute < 60; $minute += 10) {
                        $schedule[$minute][] = [
                            'name' => $name,
                            'script' => $task['script'],
                            'type' => $task['type'] ?? 'light'
                        ];
                    }
                    break;

                case 'every_15_minutes':
                    // Run every 15 minutes starting at offset
                    $offset = $task['offset'] ?? 0;
                    for ($minute = $offset; $minute < 60; $minute += 15) {
                        $schedule[$minute][] = [
                            'name' => $name,
                            'script' => $task['script'],
                            'type' => $task['type'] ?? 'light'
                        ];
                    }
                    break;

                case 'every_30_minutes':
                    // Run every 30 minutes starting at offset
                    $offset = $task['offset'] ?? 0;
                    for ($minute = $offset; $minute < 60; $minute += 30) {
                        $schedule[$minute][] = [
                            'name' => $name,
                            'script' => $task['script'],
                            'type' => $task['type'] ?? 'light',
                            'business_hours' => $task['business_hours'] ?? false
                        ];
                    }
                    break;

                case 'hourly':
                    // Run once per hour at specified minute
                    $minute = $task['minute'] ?? 0;
                    $schedule[$minute][] = [
                        'name' => $name,
                        'script' => $task['script'],
                        'type' => $task['type'] ?? 'medium',
                        'business_hours' => $task['business_hours'] ?? false
                    ];
                    break;

                case 'daily':
                    // Run once per day at specified hour and minute
                    $minute = $task['minute'] ?? 0;
                    $hour = $task['hour'] ?? 2;
                    $schedule[$minute][] = [
                        'name' => $name,
                        'script' => $task['script'],
                        'type' => $task['type'] ?? 'heavy',
                        'hour' => $hour,
                        'frequency' => 'daily'
                    ];
                    break;

                case 'weekly':
                    // Run once per week (Mondays) at specified time
                    $minute = $task['minute'] ?? 0;
                    $hour = $task['hour'] ?? 5;
                    $schedule[$minute][] = [
                        'name' => $name,
                        'script' => $task['script'],
                        'type' => $task['type'] ?? 'heavy',
                        'hour' => $hour,
                        'frequency' => 'weekly',
                        'day_of_week' => 1 // Monday
                    ];
                    break;
            }
        }

        return $schedule;
    }    /**
     * Calculate improvement percentage
     */
    private function calculateImprovement(array $analysis): int
    {
        // Simplified calculation: improvement based on heavy task distribution
        $heavyCount = count($analysis['heavy']);
        if ($heavyCount === 0) {
            return 0;
        }

        // Assume 40% improvement by moving heavy tasks to off-peak
        return min(50, $heavyCount * 5); // Up to 50% improvement
    }

    /**
     * Get task script path from task name
     */
    private function getTaskScript(string $taskName): string
    {
        // Try to map task name to script
        // This would ideally come from a configuration file
        // For now, return a placeholder
        return "scripts/{$taskName}.php";
    }

    /**
     * Save schedule to file
     */
    public function saveSchedule(array $schedule): void
    {
        $scheduleFile = dirname(__DIR__) . '/config/schedule.json';
        $dir = dirname($scheduleFile);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(
            $scheduleFile,
            json_encode($schedule, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        $this->schedule = $schedule;
    }

    /**
     * Get tasks scheduled for specific minute
     */
    public function getTasksForMinute(int $minute, int $hour = null, int $dayOfWeek = null): array
    {
        if ($hour === null) {
            $hour = (int)date('G');
        }
        if ($dayOfWeek === null) {
            $dayOfWeek = (int)date('w');
        }

        // Use the schedule already loaded in constructor
        $schedule = $this->schedule;

        if (!isset($schedule[$minute])) {
            return [];
        }

        $tasks = [];
        foreach ($schedule[$minute] as $task) {
            // Check if task should run based on frequency
            if (isset($task['frequency'])) {
                if ($task['frequency'] === 'daily') {
                    // Only run at specified hour
                    if ($task['hour'] !== $hour) {
                        continue;
                    }
                } elseif ($task['frequency'] === 'weekly') {
                    // Only run on specified day and hour
                    if ($task['day_of_week'] !== $dayOfWeek || $task['hour'] !== $hour) {
                        continue;
                    }
                }
            }

            // Check business hours restriction
            if (isset($task['business_hours']) && $task['business_hours']) {
                // Business hours: 8 AM - 11 PM (8-22)
                if ($hour < 8 || $hour >= 23) {
                    continue;
                }
            }

            $tasks[] = $task;
        }

        return $tasks;
    }
}
