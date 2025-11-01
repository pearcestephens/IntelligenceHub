#!/usr/bin/env php
<?php
/**
 * Environment Configuration Loader
 * Simple .env file parser (no dependencies)
 */

declare(strict_types=1);

class EnvLoader {
    private static $loaded = false;
    private static $vars = [];
    
    public static function load(string $path = null): void {
        if (self::$loaded) {
            return;
        }
        
        if ($path === null) {
            $path = dirname(__DIR__) . '/.env';
        }
        
        if (!file_exists($path)) {
            // Try .env.example as fallback
            $path = dirname(__DIR__) . '/.env.example';
            if (!file_exists($path)) {
                return; // No env file found
            }
        }
        
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip comments
            if (strpos($line, '#') === 0) {
                continue;
            }
            
            // Parse KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes
                $value = trim($value, '"\'');
                
                // Set environment variable
                putenv("{$key}={$value}");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
                self::$vars[$key] = $value;
            }
        }
        
        self::$loaded = true;
    }
    
    public static function get(string $key, $default = null) {
        if (!self::$loaded) {
            self::load();
        }
        
        return self::$vars[$key] ?? getenv($key) ?: $default;
    }
    
    public static function has(string $key): bool {
        if (!self::$loaded) {
            self::load();
        }
        
        return isset(self::$vars[$key]) || getenv($key) !== false;
    }
}

// Auto-load on include
EnvLoader::load();
