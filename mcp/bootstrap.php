<?php
/**
 * Bootstrap - Initializes MCP Server Environment
 *
 * Loads autoloader, configuration, and sets up error handling
 *
 * @package IntelligenceHub\MCP
 * @version 3.0.0
 */

declare(strict_types=1);

// Suppress warnings in production
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', '0');

// Load composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load configuration from .env if it exists
if (file_exists(__DIR__ . '/.env') && is_readable(__DIR__ . '/.env')) {
    $env = @parse_ini_file(__DIR__ . '/.env');
    if ($env && is_array($env)) {
        foreach ($env as $key => $value) {
            // Set in all three places for maximum compatibility
            // putenv() may be disabled on some hosts for security
            if (function_exists('putenv')) {
                @putenv("$key=$value");
            }
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Pacific/Auckland');

// Initialize configuration singleton
use IntelligenceHub\MCP\Config\Config;

Config::init(__DIR__);

// Set error handling for production
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Don't display in output
ini_set('log_errors', '1');
