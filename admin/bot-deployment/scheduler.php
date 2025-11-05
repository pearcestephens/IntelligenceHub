#!/usr/bin/env php
<?php
/**
 * Bot Scheduler Cron Runner
 *
 * Cron job entry point for executing scheduled bots
 * Add to crontab: * * * * * php /path/to/scheduler.php >> /path/to/scheduler.log 2>&1
 *
 * This script should run every minute and will determine which bots need execution
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
require_once __DIR__ . '/src/Services/SchedulerService.php';

use BotDeployment\Config\Config;
use BotDeployment\Database\Connection;
use BotDeployment\Repositories\BotRepository;
use BotDeployment\Services\BotExecutionService;
use BotDeployment\Services\SchedulerService;

// Log function
function log_message($level, $message, $context = []) {
    $timestamp = date('Y-m-d H:i:s');
    $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
    echo "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;
}

try {
    log_message('INFO', 'Scheduler started');

    // Initialize services
    $pdo = Connection::get();
    $botRepo = new BotRepository($pdo);
    $botExecution = new BotExecutionService();
    $scheduler = new SchedulerService();

    // Get due bots
    $dueBots = $scheduler->getDueBots();

    if (empty($dueBots)) {
        log_message('INFO', 'No bots due for execution');
        exit(0);
    }

    log_message('INFO', 'Found due bots', ['count' => count($dueBots)]);

    // Execute each bot
    $successCount = 0;
    $failCount = 0;

    foreach ($dueBots as $bot) {
        $botId = $bot->getBotId();
        $botName = $bot->getBotName();

        try {
            log_message('INFO', 'Executing bot', [
                'bot_id' => $botId,
                'bot_name' => $botName,
                'schedule' => $bot->getScheduleCron()
            ]);

            // Execute bot with default input
            $input = "Scheduled execution at " . date('Y-m-d H:i:s');
            $result = $botExecution->execute($bot, $input);

            // Update next execution time
            $scheduler->updateNextExecutionTime($botId);

            log_message('INFO', 'Bot executed successfully', [
                'bot_id' => $botId,
                'execution_id' => $result['execution_id'],
                'mode' => $result['mode']
            ]);

            $successCount++;

        } catch (Exception $e) {
            log_message('ERROR', 'Bot execution failed', [
                'bot_id' => $botId,
                'bot_name' => $botName,
                'error' => $e->getMessage()
            ]);

            $failCount++;

            // Update next execution time anyway to prevent retry loop
            try {
                $scheduler->updateNextExecutionTime($botId);
            } catch (Exception $e2) {
                log_message('ERROR', 'Failed to update next execution time', [
                    'bot_id' => $botId,
                    'error' => $e2->getMessage()
                ]);
            }
        }
    }

    log_message('INFO', 'Scheduler completed', [
        'total' => count($dueBots),
        'success' => $successCount,
        'failed' => $failCount
    ]);

    exit($failCount > 0 ? 1 : 0);

} catch (Exception $e) {
    log_message('ERROR', 'Scheduler fatal error', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
    exit(1);
}
