#!/usr/bin/env php
<?php
/**
 * Bot Execution CLI Tool
 *
 * Execute bots manually from command line
 *
 * Usage:
 *   php bot-execute.php <bot_id> "<input>"
 *   php bot-execute.php 1 "Analyze consignment module"
 *   php bot-execute.php 1 "Security audit" --multi-thread
 *
 * @package BotDeployment\CLI
 * @author  Ecigdis Limited
 * @version 1.0.0
 */

// Bootstrap
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Exceptions/DatabaseException.php';
require_once __DIR__ . '/src/Database/Connection.php';
require_once __DIR__ . '/src/Repositories/BotRepository.php';
require_once __DIR__ . '/src/Services/BotExecutionService.php';

use BotDeployment\Config\Config;
use BotDeployment\Database\Connection;
use BotDeployment\Repositories\BotRepository;
use BotDeployment\Services\BotExecutionService;

// CLI Colors (reuse from bot-deploy.php)
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
}

try {
    // Check arguments
    if ($argc < 3) {
        CLI::heading("Bot Execution Tool");
        CLI::error("Usage: php bot-execute.php <bot_id> \"<input>\" [--multi-thread]");
        echo PHP_EOL;
        CLI::info("Examples:");
        CLI::info("  php bot-execute.php 1 \"Analyze consignment module\"");
        CLI::info("  php bot-execute.php 2 \"Security audit\" --multi-thread");
        echo PHP_EOL;
        exit(1);
    }

    $botId = (int)$argv[1];
    $input = $argv[2];
    $multiThread = in_array('--multi-thread', $argv) || in_array('-m', $argv);

    CLI::heading("Bot Execution");
    CLI::info("Bot ID: " . $botId);
    CLI::info("Input: " . $input);
    CLI::info("Mode: " . ($multiThread ? "Multi-threaded" : "Single-threaded"));
    echo PHP_EOL;

    // Initialize services
    $pdo = Connection::get();
    $botRepo = new BotRepository($pdo);
    $botExecution = new BotExecutionService();

    // Find bot
    CLI::info("Loading bot...");
    $bot = $botRepo->find($botId);

    if (!$bot) {
        CLI::error("Bot not found: " . $botId);
        exit(1);
    }

    CLI::success("Bot loaded: " . $bot->getBotName());
    CLI::info("Role: " . $bot->getBotRole());
    CLI::info("Status: " . $bot->getStatus());
    echo PHP_EOL;

    // Execute bot
    CLI::info("Executing bot...");
    $startTime = microtime(true);

    try {
        $result = $botExecution->execute($bot, $input, [], $multiThread);

        $duration = (microtime(true) - $startTime) * 1000;

        echo PHP_EOL;
        CLI::success("Execution complete!");
        CLI::info("Execution ID: " . $result['execution_id']);
        CLI::info("Duration: " . number_format($duration, 2) . "ms");
        CLI::info("Mode: " . $result['mode']);

        if (isset($result['metadata']['thread_count'])) {
            CLI::info("Threads used: " . $result['metadata']['thread_count']);
        }

        // Output
        echo PHP_EOL;
        CLI::heading("Bot Output");
        echo $result['output'] . PHP_EOL;

        // Metadata
        if (!empty($result['metadata'])) {
            echo PHP_EOL;
            CLI::heading("Execution Metadata");
            foreach ($result['metadata'] as $key => $value) {
                if ($key === 'threads' && is_array($value)) {
                    CLI::info("threads:");
                    foreach ($value as $thread) {
                        CLI::info("  - Thread " . $thread['thread_id'] . ": " .
                                 $thread['status'] . " (" .
                                 number_format($thread['execution_time'], 2) . "ms)");
                    }
                } elseif (is_array($value)) {
                    CLI::info($key . ": " . json_encode($value));
                } else {
                    CLI::info($key . ": " . $value);
                }
            }
        }

        echo PHP_EOL;
        CLI::success("Bot execution completed successfully!");
        exit(0);

    } catch (Exception $e) {
        echo PHP_EOL;
        CLI::error("Execution failed: " . $e->getMessage());

        if (in_array('--debug', $argv)) {
            echo PHP_EOL;
            echo $e->getTraceAsString() . PHP_EOL;
        }

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
