<?php
/**
 * PSR-4 Autoloader for Smart Cron
 *
 * Automatically loads classes from the smart-cron directory
 */

spl_autoload_register(function ($class) {
    // Project namespace prefix
    $prefix = 'SmartCron\\';

    // Base directory for the namespace prefix
    $base_dir = __DIR__ . '/smart-cron/';

    // Check if the class uses the namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // No, move to the next registered autoloader
        return;
    }

    // Get the relative class name
    $relative_class = substr($class, $len);

    // Replace namespace separators with directory separators
    $parts = explode('\\', $relative_class);

    // Convert directory names to lowercase, but keep filename case-sensitive
    // e.g., "Core\Config" -> "core/Config.php"
    $filename = array_pop($parts); // Get the class name (e.g., "Config")
    $directories = implode('/', array_map('strtolower', $parts)); // Lowercase directories

    $file = $base_dir . ($directories ? $directories . '/' : '') . $filename . '.php';

    // Debug logging
    error_log("[AUTOLOADER] Attempting to load: {$class}");
    error_log("[AUTOLOADER] File path: {$file}");
    error_log("[AUTOLOADER] File exists: " . (file_exists($file) ? 'YES' : 'NO'));

    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
        error_log("[AUTOLOADER] Successfully loaded: {$class}");
    } else {
        error_log("[AUTOLOADER] FAILED - File not found: {$file}");
    }
});
