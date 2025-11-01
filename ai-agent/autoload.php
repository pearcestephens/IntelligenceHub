<?php
/**
 * Lightweight PSR-4 style autoloader for App namespace (Neuro AI Agent)
 * Avoids Composer dependency; maps App\ -> this directory's src/ folder.
 */
declare(strict_types=1);

if (!\defined('NEURO_AI_AGENT_AUTOLOAD')) {
    \define('NEURO_AI_AGENT_AUTOLOAD', 1);
    spl_autoload_register(function(string $class) {
        if (str_starts_with($class, 'App\\')) {
            $baseDir = __DIR__ . '/src/';
            $relative = substr($class, 4); // drop 'App\'
            $path = $baseDir . str_replace('\\', '/', $relative) . '.php';
            if (is_file($path)) {
                require $path;
            }
        }
    }, true, true);
}
?>