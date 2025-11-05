<?php
/**
 * Scheduler Service
 *
 * Manages cron-based bot scheduling, calculates next execution times,
 * finds bots due for execution, and handles schedule validation.
 *
 * @package BotDeployment\Services
 * @author  Ecigdis Limited
 * @version 1.0.0
 */

namespace BotDeployment\Services;

use BotDeployment\Config\Config;
use BotDeployment\Models\Bot;
use BotDeployment\Repositories\BotRepository;
use BotDeployment\Database\Connection;

class SchedulerService
{
    private ?BotRepository $botRepo;
    private ?\PDO $db;

    /**
     * Constructor
     */
    public function __construct(
        ?BotRepository $botRepo = null,
        ?\PDO $db = null
    ) {
        $this->db = $db ?? Connection::get();
        $this->botRepo = $botRepo ?? new BotRepository($this->db);
    }

    /**
     * Get all bots due for execution
     *
     * @return Bot[] Array of bots ready to run
     */
    public function getDueBots(): array
    {
        return $this->botRepo->findDueForExecution();
    }

    /**
     * Calculate next execution time for a bot
     *
     * @param Bot      $bot      Bot instance
     * @param int|null $fromTime Calculate from this timestamp (default: now)
     * @return int|null Unix timestamp of next execution, null if not scheduled
     */
    public function getNextExecutionTime(Bot $bot, ?int $fromTime = null): ?int
    {
        $cronExpression = $bot->getScheduleCron();

        if (empty($cronExpression)) {
            return null;
        }

        $fromTime = $fromTime ?? time();

        try {
            return $this->calculateNextRun($cronExpression, $fromTime);
        } catch (\Exception $e) {
            error_log("Failed to calculate next execution for bot {$bot->getBotId()}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Update bot's next execution time
     *
     * @param int|Bot $bot Bot ID or instance
     * @return bool Success status
     */
    public function updateNextExecutionTime($bot): bool
    {
        if (is_int($bot)) {
            $bot = $this->botRepo->find($bot);
            if (!$bot) {
                return false;
            }
        }

        $nextTime = $this->getNextExecutionTime($bot);

        if ($nextTime === null) {
            return false;
        }

        try {
            $this->botRepo->updateExecutionTime($bot->getBotId(), $nextTime);
            return true;
        } catch (\Exception $e) {
            error_log("Failed to update execution time: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate cron expression
     *
     * @param string $expression Cron expression to validate
     * @return array Validation result with 'valid' and 'error' keys
     */
    public function validateCronExpression(string $expression): array
    {
        if (empty($expression)) {
            return ['valid' => false, 'error' => 'Expression cannot be empty'];
        }

        $parts = preg_split('/\s+/', trim($expression));

        if (count($parts) !== 5) {
            return [
                'valid' => false,
                'error' => 'Expression must have 5 parts: minute hour day month weekday'
            ];
        }

        list($minute, $hour, $day, $month, $weekday) = $parts;

        // Validate each part
        $validations = [
            'minute' => $this->validateCronField($minute, 0, 59),
            'hour' => $this->validateCronField($hour, 0, 23),
            'day' => $this->validateCronField($day, 1, 31),
            'month' => $this->validateCronField($month, 1, 12),
            'weekday' => $this->validateCronField($weekday, 0, 7) // 0 and 7 are both Sunday
        ];

        foreach ($validations as $field => $result) {
            if (!$result['valid']) {
                return [
                    'valid' => false,
                    'error' => "Invalid {$field}: {$result['error']}"
                ];
            }
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Get human-readable description of cron expression
     *
     * @param string $expression Cron expression
     * @return string Human-readable description
     */
    public function describeCronExpression(string $expression): string
    {
        $validation = $this->validateCronExpression($expression);

        if (!$validation['valid']) {
            return "Invalid cron expression: {$validation['error']}";
        }

        $parts = preg_split('/\s+/', trim($expression));
        list($minute, $hour, $day, $month, $weekday) = $parts;

        // Common patterns
        if ($expression === '* * * * *') {
            return 'Every minute';
        }
        if ($expression === '0 * * * *') {
            return 'Every hour';
        }
        if ($expression === '0 0 * * *') {
            return 'Daily at midnight';
        }
        if ($expression === '0 0 * * 0') {
            return 'Weekly on Sunday at midnight';
        }
        if ($expression === '0 0 1 * *') {
            return 'Monthly on the 1st at midnight';
        }

        // Build description
        $description = 'Runs ';

        // Minute
        if ($minute === '*') {
            $description .= 'every minute';
        } elseif (strpos($minute, '*/') === 0) {
            $interval = substr($minute, 2);
            $description .= "every {$interval} minutes";
        } else {
            $description .= "at minute {$minute}";
        }

        // Hour
        if ($hour !== '*') {
            if (strpos($hour, '*/') === 0) {
                $interval = substr($hour, 2);
                $description .= " every {$interval} hours";
            } else {
                $description .= " at {$hour}:00";
            }
        }

        // Day
        if ($day !== '*') {
            $description .= " on day {$day}";
        }

        // Month
        if ($month !== '*') {
            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $monthName = is_numeric($month) ? $months[(int)$month - 1] : $month;
            $description .= " in {$monthName}";
        }

        // Weekday
        if ($weekday !== '*') {
            $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            $dayName = is_numeric($weekday) ? $days[(int)$weekday % 7] : $weekday;
            $description .= " on {$dayName}";
        }

        return $description;
    }

    /**
     * Calculate next run time from cron expression
     *
     * @param string $expression Cron expression
     * @param int    $fromTime   Calculate from this time
     * @return int Unix timestamp of next run
     * @throws \Exception If expression is invalid
     */
    private function calculateNextRun(string $expression, int $fromTime): int
    {
        $validation = $this->validateCronExpression($expression);

        if (!$validation['valid']) {
            throw new \Exception($validation['error']);
        }

        $parts = preg_split('/\s+/', trim($expression));
        list($minute, $hour, $day, $month, $weekday) = $parts;

        // Start from next minute
        $currentTime = $fromTime;
        $maxIterations = 525600; // One year in minutes

        for ($i = 0; $i < $maxIterations; $i++) {
            $currentTime += 60; // Next minute

            if ($this->matchesCronExpression($currentTime, $minute, $hour, $day, $month, $weekday)) {
                return $currentTime;
            }
        }

        throw new \Exception('Could not find next execution time within one year');
    }

    /**
     * Check if timestamp matches cron expression
     */
    private function matchesCronExpression(
        int $timestamp,
        string $minute,
        string $hour,
        string $day,
        string $month,
        string $weekday
    ): bool {
        $time = getdate($timestamp);

        return $this->matchesCronField($time['minutes'], $minute, 0, 59) &&
               $this->matchesCronField($time['hours'], $hour, 0, 23) &&
               $this->matchesCronField($time['mday'], $day, 1, 31) &&
               $this->matchesCronField($time['mon'], $month, 1, 12) &&
               $this->matchesCronField($time['wday'], $weekday, 0, 7);
    }

    /**
     * Check if value matches cron field
     */
    private function matchesCronField(int $value, string $field, int $min, int $max): bool
    {
        // Wildcard
        if ($field === '*') {
            return true;
        }

        // Specific value
        if (is_numeric($field)) {
            return (int)$field === $value;
        }

        // Step values (*/5)
        if (preg_match('/^\*\/(\d+)$/', $field, $matches)) {
            $step = (int)$matches[1];
            return $value % $step === 0;
        }

        // Range (1-5)
        if (preg_match('/^(\d+)-(\d+)$/', $field, $matches)) {
            $rangeMin = (int)$matches[1];
            $rangeMax = (int)$matches[2];
            return $value >= $rangeMin && $value <= $rangeMax;
        }

        // List (1,3,5)
        if (strpos($field, ',') !== false) {
            $values = array_map('intval', explode(',', $field));
            return in_array($value, $values, true);
        }

        // Range with step (1-10/2)
        if (preg_match('/^(\d+)-(\d+)\/(\d+)$/', $field, $matches)) {
            $rangeMin = (int)$matches[1];
            $rangeMax = (int)$matches[2];
            $step = (int)$matches[3];

            if ($value < $rangeMin || $value > $rangeMax) {
                return false;
            }

            return ($value - $rangeMin) % $step === 0;
        }

        return false;
    }

    /**
     * Validate individual cron field
     */
    private function validateCronField(string $field, int $min, int $max): array
    {
        // Wildcard
        if ($field === '*') {
            return ['valid' => true, 'error' => null];
        }

        // Specific value
        if (is_numeric($field)) {
            $value = (int)$field;
            if ($value < $min || $value > $max) {
                return ['valid' => false, 'error' => "Value must be between {$min} and {$max}"];
            }
            return ['valid' => true, 'error' => null];
        }

        // Step values (*/5)
        if (preg_match('/^\*\/(\d+)$/', $field, $matches)) {
            $step = (int)$matches[1];
            if ($step < 1 || $step > $max) {
                return ['valid' => false, 'error' => "Step must be between 1 and {$max}"];
            }
            return ['valid' => true, 'error' => null];
        }

        // Range (1-5)
        if (preg_match('/^(\d+)-(\d+)$/', $field, $matches)) {
            $rangeMin = (int)$matches[1];
            $rangeMax = (int)$matches[2];

            if ($rangeMin < $min || $rangeMax > $max || $rangeMin >= $rangeMax) {
                return ['valid' => false, 'error' => "Invalid range"];
            }

            return ['valid' => true, 'error' => null];
        }

        // List (1,3,5)
        if (strpos($field, ',') !== false) {
            $values = explode(',', $field);
            foreach ($values as $value) {
                if (!is_numeric($value) || (int)$value < $min || (int)$value > $max) {
                    return ['valid' => false, 'error' => "Invalid value in list"];
                }
            }
            return ['valid' => true, 'error' => null];
        }

        // Range with step (1-10/2)
        if (preg_match('/^(\d+)-(\d+)\/(\d+)$/', $field)) {
            return ['valid' => true, 'error' => null];
        }

        return ['valid' => false, 'error' => 'Invalid format'];
    }
}
