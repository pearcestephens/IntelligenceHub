<?php

/**
 * Configuration management class
 * Loads and validates environment variables with Cloudways support
 *
 * @package App
 * @author Ecigdis Limited (The Vape Shed)
 */

declare(strict_types=1);

namespace App;

class Config
{
    private static array $config = [];
    private static bool $initialized = false;

    /**
     * Initialize configuration from environment
     */
    public static function initialize(): void
    {
        if (self::$initialized) {
            return;
        }

        // OpenAI Configuration - lazy loaded when needed
        self::$config['OPENAI_API_KEY'] = self::getEnv('OPENAI_API_KEY', '');
        self::$config['OPENAI_MODEL'] = self::getEnv('OPENAI_MODEL', 'gpt-4o-mini');
        self::$config['REALTIME_MODEL'] = self::getEnv('REALTIME_MODEL', 'gpt-4o-realtime-preview');
        self::$config['REALTIME_VOICE'] = self::getEnv('REALTIME_VOICE', 'alloy');
        self::$config['EMBEDDINGS_MODEL'] = self::getEnv('EMBEDDINGS_MODEL', 'text-embedding-3-small');

        // Claude/Anthropic Configuration
        self::$config['ANTHROPIC_API_KEY'] = self::getEnv('ANTHROPIC_API_KEY', '');
        self::$config['ANTHROPIC_MODEL'] = self::getEnv('ANTHROPIC_MODEL', 'claude-3-5-sonnet-20241022');

    // Database Configuration - supports both MYSQL_* and DB_* variable names
    // Primary keys are MYSQL_* in internal config; values may be sourced from DB_* fallbacks.
        self::$config['MYSQL_HOST'] = self::getEnvAny(['MYSQL_HOST', 'DB_HOST'], '127.0.0.1');
        self::$config['MYSQL_PORT'] = (int)self::getEnvAny(['MYSQL_PORT', 'DB_PORT'], '3306');
        self::$config['MYSQL_USER'] = self::getEnvAny(['MYSQL_USER', 'DB_USER', 'DB_USERNAME'], '');
        self::$config['MYSQL_PASSWORD'] = self::getEnvAny(['MYSQL_PASSWORD', 'DB_PASSWORD'], '');
        self::$config['MYSQL_DATABASE'] = self::getEnvAny(['MYSQL_DATABASE', 'DB_DATABASE', 'DB_NAME'], '');

        // Redis Configuration
        self::$config['REDIS_URL'] = self::getEnv('REDIS_URL', 'redis://127.0.0.1:6379');
        self::$config['REDIS_PREFIX'] = self::getEnv('REDIS_PREFIX', 'aiagent:');

        // Tool Guardrails
        self::$config['HTTP_TOOL_ALLOWLIST'] = self::getEnv('HTTP_TOOL_ALLOWLIST', 'api.example.com');
        self::$config['TOOL_FS_ROOT'] = self::getEnv('TOOL_FS_ROOT', sys_get_temp_dir() . '/agent_sandbox');
        self::$config['DANGEROUS_SQL_ENABLED'] = self::parseBoolFromEnv('DANGEROUS_SQL_ENABLED', false);

        // Memory & Knowledge Base
        self::$config['HISTORY_WINDOW'] = (int)self::getEnv('HISTORY_WINDOW', '40');
        self::$config['SUMMARY_EVERY'] = (int)self::getEnv('SUMMARY_EVERY', '8');
        self::$config['KB_INDEX_NAME'] = self::getEnv('KB_INDEX_NAME', 'kb:docs');
        self::$config['KB_CHUNK_SIZE'] = (int)self::getEnv('KB_CHUNK_SIZE', '1200');
        self::$config['KB_CHUNK_OVERLAP'] = (int)self::getEnv('KB_CHUNK_OVERLAP', '120');
        self::$config['KB_TOP_K'] = (int)self::getEnv('KB_TOP_K', '6');

        // Server Configuration
        self::$config['APP_BASE_PATH'] = self::getEnv('APP_BASE_PATH', '/agent');
        self::$config['LOG_LEVEL'] = self::getEnv('LOG_LEVEL', 'info');
        self::$config['SSE_KEEPALIVE_SEC'] = (int)self::getEnv('SSE_KEEPALIVE_SEC', '20');
        self::$config['RATE_LIMIT_WINDOW_MS'] = (int)self::getEnv('RATE_LIMIT_WINDOW_MS', '60000');
        self::$config['RATE_LIMIT_MAX'] = (int)self::getEnv('RATE_LIMIT_MAX', '120');

        // Feature Flags
        self::$config['ENABLE_EXAMPLE_ADAPTER'] = self::parseBoolFromEnv('ENABLE_EXAMPLE_ADAPTER', false);

        // CORS Configuration
        self::$config['CORS_ALLOWED_ORIGINS'] = self::getEnv('CORS_ALLOWED_ORIGINS', 'same-origin');

        // Only validate non-critical paths immediately
        self::validateBasicConfiguration();

        self::$initialized = true;
    }

    /**
     * Get configuration value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (!self::$initialized) {
            self::initialize();
        }

        return self::$config[$key] ?? $default;
    }

    /**
     * Get configuration value as boolean
     * Accepts "true/false/1/0/on/off/yes/no" (case-insensitive)
     */
    public static function getBool(string $key, bool $default = false): bool
    {
        if (!self::$initialized) {
            self::initialize();
        }

        $value = self::$config[$key] ?? null;

        if ($value === null) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            $lowered = strtolower(trim($value));
            return in_array($lowered, ['true', '1', 'on', 'yes'], true);
        }

        return (bool)$value;
    }

    /**
     * Get all configuration
     */
    public static function all(): array
    {
        if (!self::$initialized) {
            self::initialize();
        }

        return self::$config;
    }

    /**
     * Get required environment variable
     */
    private static function getRequired(string $key): string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;

        if ($value === null || $value === '') {
            throw new \RuntimeException("Required environment variable {$key} is not set");
        }

        return $value;
    }

    /**
     * Get environment variable with default
     */
    private static function getEnv(string $key, string $default = ''): string
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }

    /**
     * Get the first defined environment variable from a list of keys
     * Useful for supporting multiple naming conventions (e.g., MYSQL_* vs DB_*) without breaking changes.
     *
     * @param array<int,string> $keys
     */
    private static function getEnvAny(array $keys, string $default = ''): string
    {
        foreach ($keys as $key) {
            $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;
            if ($value !== null && $value !== '') {
                return (string)$value;
            }
        }
        return $default;
    }

    /**
     * Parse boolean from environment variable (used in initialize to avoid recursion)
     */
    private static function parseBoolFromEnv(string $key, bool $default = false): bool
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;

        if ($value === null) {
            return $default;
        }

        if (is_string($value)) {
            $lowered = strtolower(trim($value));
            return in_array($lowered, ['true', '1', 'on', 'yes'], true);
        }

        return (bool)$value;
    }



    /**
     * Validate basic configuration (non-critical for UI)
     */
    private static function validateBasicConfiguration(): void
    {
        // Validate Redis URL
        $redisUrl = self::$config['REDIS_URL'];
        if (!filter_var($redisUrl, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException('Invalid Redis URL format');
        }

        // Validate tool filesystem root
        $fsRoot = self::$config['TOOL_FS_ROOT'];
        if (!is_dir(dirname($fsRoot))) {
            throw new \RuntimeException("Tool filesystem root directory does not exist: {$fsRoot}");
        }

        // Create tool sandbox directory if it doesn't exist
        if (!is_dir($fsRoot)) {
            if (!mkdir($fsRoot, 0755, true)) {
                throw new \RuntimeException("Could not create tool filesystem root: {$fsRoot}");
            }
        }

        // Validate HTTP allowlist
        $allowlist = self::$config['HTTP_TOOL_ALLOWLIST'];
        $hosts = array_map('trim', explode(',', $allowlist));
        foreach ($hosts as $host) {
            if (!filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
                throw new \RuntimeException("Invalid hostname in HTTP allowlist: {$host}");
            }
        }

        // Validate numeric ranges
        $historyWindow = self::$config['HISTORY_WINDOW'];
        if ($historyWindow < 1 || $historyWindow > 200) {
            throw new \RuntimeException('HISTORY_WINDOW must be between 1 and 200');
        }

        $summaryEvery = self::$config['SUMMARY_EVERY'];
        if ($summaryEvery < 1 || $summaryEvery > 50) {
            throw new \RuntimeException('SUMMARY_EVERY must be between 1 and 50');
        }

        $chunkSize = self::$config['KB_CHUNK_SIZE'];
        if ($chunkSize < 100 || $chunkSize > 8000) {
            throw new \RuntimeException('KB_CHUNK_SIZE must be between 100 and 8000');
        }

        $topK = self::$config['KB_TOP_K'];
        if ($topK < 1 || $topK > 50) {
            throw new \RuntimeException('KB_TOP_K must be between 1 and 50');
        }
    }

    /**
     * Validate OpenAI configuration when needed
     */
    public static function validateOpenAI(): void
    {
        if (!self::$initialized) {
            self::initialize();
        }

        $apiKey = self::$config['OPENAI_API_KEY'];
        if (empty($apiKey)) {
            throw new \RuntimeException('OpenAI API key is required but not set in OPENAI_API_KEY environment variable');
        }

        if (!preg_match('/^sk-[a-zA-Z0-9]{32,}$/', $apiKey) && !preg_match('/^sk-proj-[a-zA-Z0-9-_]{32,}$/', $apiKey)) {
            throw new \RuntimeException('Invalid OpenAI API key format');
        }
    }

    /**
     * Validate database configuration when needed
     */
    public static function validateDatabase(): void
    {
        if (!self::$initialized) {
            self::initialize();
        }

        $required = ['MYSQL_USER', 'MYSQL_PASSWORD', 'MYSQL_DATABASE'];
        foreach ($required as $key) {
            $value = self::$config[$key] ?? '';
            if (empty($value)) {
                throw new \RuntimeException("Required database configuration {$key} is not set");
            }
        }
    }
}
