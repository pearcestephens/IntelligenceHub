<?php
/**
 * Simple .env File Loader
 * 
 * Lightweight environment variable loader for Smart Cron system.
 * No external dependencies required.
 * 
 * @package SmartCron
 * @author CIS Development Team
 */

declare(strict_types=1);

namespace SmartCron\Core;

class EnvLoader
{
    private static array $loaded = [];
    private static bool $initialized = false;
    
    /**
     * Load .env file from specified directory
     */
    public static function load(string $path = null): void
    {
        if (self::$initialized) {
            return; // Already loaded
        }
        
        $envFile = ($path ?? __DIR__ . '/..') . '/.env';
        
        if (!file_exists($envFile)) {
            throw new \RuntimeException(".env file not found at: {$envFile}");
        }
        
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments and empty lines
            if (strpos(trim($line), '#') === 0 || empty(trim($line))) {
                continue;
            }
            
            // Parse KEY=VALUE format
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if ((substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")) {
                    $value = substr($value, 1, -1);
                }
                
                // Set as environment variable
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
                self::$loaded[$key] = $value;
                
                // Also define as constant for backward compatibility
                if (!defined($key)) {
                    define($key, $value);
                }
            }
        }
        
        self::$initialized = true;
    }
    
    /**
     * Get environment variable with fallback
     */
    public static function get(string $key, $default = null)
    {
        // Check in order: $_ENV, $_SERVER, getenv(), loaded cache
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        if (isset(self::$loaded[$key])) {
            return self::$loaded[$key];
        }
        
        return $default;
    }
    
    /**
     * Get required environment variable (throws if missing)
     */
    public static function getRequired(string $key): string
    {
        $value = self::get($key);
        
        if ($value === null || $value === '') {
            throw new \RuntimeException("Required environment variable '{$key}' is not set");
        }
        
        return (string)$value;
    }
    
    /**
     * Check if .env is loaded
     */
    public static function isLoaded(): bool
    {
        return self::$initialized;
    }
    
    /**
     * Get all loaded environment variables
     */
    public static function all(): array
    {
        return self::$loaded;
    }
}
