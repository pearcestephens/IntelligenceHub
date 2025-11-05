#!/usr/bin/env php
<?php
/**
 * Bot Deployment CLI Tool
 *
 * Interactive command-line tool for creating and deploying bots
 *
 * Usage:
 *   php bot-deploy.php
 *   php bot-deploy.php --name="Security Bot" --role=security
 *
 * @package BotDeployment\CLI
 * @author  Ecigdis Limited
 * @version 1.0.0
 */

// Bootstrap
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Exceptions/DatabaseException.php';
require_once __DIR__ . '/src/Database/Connection.php';
require_once __DIR__ . '/src/Models/Bot.php';
require_once __DIR__ . '/src/Repositories/BotRepository.php';
require_once __DIR__ . '/src/Services/SchedulerService.php';

use BotDeployment\Config\Config;
use BotDeployment\Database\Connection;
use BotDeployment\Models\Bot;
use BotDeployment\Repositories\BotRepository;
use BotDeployment\Services\SchedulerService;

// CLI Colors
class CLI {
    const RESET = "\033[0m";
    const RED = "\033[31m";
    const GREEN = "\033[32m";
    const YELLOW = "\033[33m";
    const BLUE = "\033[34m";
    const CYAN = "\033[36m";
    const BOLD = "\033[1m";

    public static function print($text, $color = '') {
        echo $color . $text . self::RESET . PHP_EOL;
    }

    public static function success($text) {
        self::print("✓ " . $text, self::GREEN);
    }

    public static function error($text) {
        self::print("✗ " . $text, self::RED);
    }

    public static function info($text) {
        self::print("ℹ " . $text, self::CYAN);
    }

    public static function warning($text) {
        self::print("⚠ " . $text, self::YELLOW);
    }

    public static function heading($text) {
        echo PHP_EOL;
        self::print(str_repeat("=", 60), self::BOLD);
        self::print($text, self::BOLD . self::CYAN);
        self::print(str_repeat("=", 60), self::BOLD);
        echo PHP_EOL;
    }

    public static function input($prompt, $default = null) {
        $defaultText = $default ? " [" . $default . "]" : "";
        echo self::YELLOW . $prompt . $defaultText . ": " . self::RESET;
        $input = trim(fgets(STDIN));
        return empty($input) && $default !== null ? $default : $input;
    }

    public static function confirm($prompt) {
        $response = self::input($prompt . " (y/n)", "n");
        return strtolower($response) === 'y';
    }
}

// Parse command line arguments
function parseArgs($argv) {
    $args = [];
    foreach ($argv as $arg) {
        if (preg_match('/^--([^=]+)=(.*)$/', $arg, $matches)) {
            $args[$matches[1]] = $matches[2];
        }
    }
    return $args;
}

try {
    CLI::heading("Bot Deployment Tool");

    // Initialize services
    $pdo = Connection::get();
    $botRepo = new BotRepository($pdo);
    $scheduler = new SchedulerService();

    // Parse command line args
    $args = parseArgs($argv);

    // Interactive mode if no args provided
    if (empty($args)) {
        CLI::info("Starting interactive bot creation...");
        echo PHP_EOL;

        // Bot Name
        $botName = CLI::input("Bot Name", "My Bot");
        while (strlen($botName) < 3) {
            CLI::error("Bot name must be at least 3 characters");
            $botName = CLI::input("Bot Name", "My Bot");
        }

        // Bot Role
        CLI::info("Available roles: security, developer, analyst, monitor, general");
        $botRole = CLI::input("Bot Role", "general");
        $validRoles = ['security', 'developer', 'analyst', 'monitor', 'general'];
        while (!in_array($botRole, $validRoles)) {
            CLI::error("Invalid role. Choose: " . implode(', ', $validRoles));
            $botRole = CLI::input("Bot Role", "general");
        }

        // System Prompt
        echo PHP_EOL;
        CLI::info("Enter system prompt (press ENTER twice when done):");
        $systemPrompt = '';
        $line = '';
        while (true) {
            $line = fgets(STDIN);
            if (trim($line) === '' && !empty($systemPrompt)) {
                break;
            }
            $systemPrompt .= $line;
        }
        $systemPrompt = trim($systemPrompt);

        if (strlen($systemPrompt) < 10) {
            CLI::error("System prompt too short. Using default...");
            $systemPrompt = "You are a helpful AI assistant specialized in {$botRole} tasks.";
        }

        // Schedule
        echo PHP_EOL;
        $useSchedule = CLI::confirm("Schedule this bot?");
        $scheduleCron = null;
        if ($useSchedule) {
            CLI::info("Cron examples:");
            CLI::info("  */5 * * * *  - Every 5 minutes");
            CLI::info("  0 * * * *    - Every hour");
            CLI::info("  0 */4 * * *  - Every 4 hours");
            CLI::info("  0 0 * * *    - Daily at midnight");

            $scheduleCron = CLI::input("Cron Expression", "0 * * * *");

            // Validate cron
            $validation = $scheduler->validateCronExpression($scheduleCron);
            if (!$validation['valid']) {
                CLI::error("Invalid cron expression: " . $validation['error']);
                $scheduleCron = null;
            } else {
                $description = $scheduler->describeCronExpression($scheduleCron);
                CLI::success("Schedule: " . $description);
            }
        }

        // Status
        echo PHP_EOL;
        $status = CLI::input("Initial Status (active/paused)", "active");
        if (!in_array($status, [Bot::STATUS_ACTIVE, Bot::STATUS_PAUSED])) {
            $status = Bot::STATUS_ACTIVE;
        }

        // Configuration
        echo PHP_EOL;
        CLI::info("Bot Configuration (optional)");
        $model = CLI::input("Model", "gpt-5-turbo");
        $temperature = CLI::input("Temperature (0.0-1.0)", "0.7");
        $multiThread = CLI::confirm("Enable multi-threading?");

        $config = [
            'model' => $model,
            'temperature' => (float)$temperature,
            'multi_thread_enabled' => $multiThread
        ];

        if ($multiThread) {
            $threadCount = CLI::input("Preferred thread count (2-6)", "4");
            $config['preferred_thread_count'] = max(2, min(6, (int)$threadCount));
        }

    } else {
        // Non-interactive mode from args
        $botName = $args['name'] ?? 'CLI Bot';
        $botRole = $args['role'] ?? 'general';
        $systemPrompt = $args['prompt'] ?? "You are a helpful AI assistant.";
        $scheduleCron = $args['schedule'] ?? null;
        $status = $args['status'] ?? Bot::STATUS_ACTIVE;
        $config = [
            'model' => $args['model'] ?? 'gpt-5-turbo',
            'temperature' => (float)($args['temperature'] ?? 0.7)
        ];
    }

    // Create bot
    echo PHP_EOL;
    CLI::heading("Creating Bot");
    CLI::info("Name: " . $botName);
    CLI::info("Role: " . $botRole);
    CLI::info("Status: " . $status);
    if ($scheduleCron) {
        CLI::info("Schedule: " . $scheduleCron);
    }

    if (!CLI::confirm("Create this bot?")) {
        CLI::warning("Bot creation cancelled");
        exit(0);
    }

    // Build bot model
    $bot = new Bot();
    $bot->setBotName($botName)
        ->setBotRole($botRole)
        ->setSystemPrompt($systemPrompt)
        ->setStatus($status);

    if ($scheduleCron) {
        $bot->setScheduleCron($scheduleCron);
    }

    foreach ($config as $key => $value) {
        $bot->setConfig($key, $value);
    }

    // Save to database
    try {
        $botId = $botRepo->create($bot);

        // Update schedule if needed
        if ($bot->isScheduled()) {
            $scheduler->updateNextExecutionTime($botId);
        }

        echo PHP_EOL;
        CLI::success("Bot created successfully!");
        CLI::info("Bot ID: " . $botId);
        CLI::info("Bot Name: " . $botName);

        if ($bot->isScheduled()) {
            $nextRun = $scheduler->getNextExecutionTime($bot);
            CLI::info("Next execution: " . date('Y-m-d H:i:s', $nextRun));
        }

        // Display bot details
        echo PHP_EOL;
        CLI::heading("Bot Details");
        $botData = $bot->toArray();
        foreach ($botData as $key => $value) {
            if ($key === 'config' && is_array($value)) {
                CLI::info($key . ":");
                foreach ($value as $k => $v) {
                    CLI::info("  " . $k . ": " . json_encode($v));
                }
            } elseif ($key !== 'system_prompt') {
                CLI::info($key . ": " . (is_string($value) ? $value : json_encode($value)));
            }
        }

        echo PHP_EOL;
        CLI::success("Deployment complete!");
        CLI::info("Execute bot with: php bot-execute.php " . $botId);

    } catch (Exception $e) {
        CLI::error("Failed to create bot: " . $e->getMessage());
        exit(1);
    }

} catch (Exception $e) {
    CLI::error("Fatal error: " . $e->getMessage());
    if (isset($argv) && in_array('--debug', $argv)) {
        echo PHP_EOL;
        echo $e->getTraceAsString() . PHP_EOL;
    }
    exit(1);
}
