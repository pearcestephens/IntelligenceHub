<?php
/**
 * API Bootstrap
 *
 * Entry point for Bot Deployment API
 *
 * Usage: Include this file from your public API endpoint
 * Example: /admin/bot-deployment/api.php
 *
 * @package BotDeployment
 * @author  Ecigdis Limited
 * @version 1.0.0
 */

// Set error reporting for production
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// Or manual autoloading if composer not available
spl_autoload_register(function ($class) {
    $prefix = 'BotDeployment\\';
    $baseDir = __DIR__ . '/src/';

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

use BotDeployment\Http\Request;
use BotDeployment\Http\Response;
use BotDeployment\Http\Router;

try {
    // Capture request
    $request = Request::capture();

    // Create router and dispatch
    $router = new Router();
    $router->dispatch($request);

} catch (\Throwable $e) {
    // Catch any uncaught errors
    Response::serverError('An unexpected error occurred', [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
