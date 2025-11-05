<?php

/**
 * Intelligence Hub Bootstrap
 *
 * Initializes autoloading, configuration, and core services
 */

// Define base path
define('INTELLIGENCE_HUB_PATH', __DIR__ . '/..');

// Autoloader
spl_autoload_register(function ($class) {
    // Convert namespace to file path
    $prefix = 'IntelligenceHub\\';
    $baseDir = INTELLIGENCE_HUB_PATH . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Load environment variables
if (file_exists(INTELLIGENCE_HUB_PATH . '/.env')) {
    $lines = file(INTELLIGENCE_HUB_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv("$name=$value");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error handling
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }

    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Exception handling
set_exception_handler(function ($exception) {
    $logger = new \IntelligenceHub\Services\Logger('exception');
    $logger->error("Uncaught exception", [
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);

    if (getenv('APP_ENV') === 'production') {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'An unexpected error occurred'
        ]);
    } else {
        echo "<pre>";
        echo "Exception: " . $exception->getMessage() . "\n";
        echo "File: " . $exception->getFile() . " (line " . $exception->getLine() . ")\n\n";
        echo $exception->getTraceAsString();
        echo "</pre>";
    }
    exit(1);
});
