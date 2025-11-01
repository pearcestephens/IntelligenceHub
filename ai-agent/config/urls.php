<?php

/**
 * URL routing configuration
 * Maps endpoint keys to controller and method handlers.
 *
 * @package Config
 */

declare(strict_types=1);

return [
    'default' => 'dashboard.index',
    'routes' => [
        'dashboard.index' => [
            'path' => '/',
            'controller' => 'App\\Http\\Controllers\\DashboardController@index',
            'methods' => ['GET'],
            'auth' => true,
        ],
        'health.ping' => [
            'path' => '/admin/health/ping',
            'controller' => 'App\\Http\\Controllers\\HealthController@ping',
            'methods' => ['GET'],
            'auth' => true,
        ],
        'health.phpinfo' => [
            'path' => '/admin/health/phpinfo',
            'controller' => 'App\\Http\\Controllers\\HealthController@phpinfo',
            'methods' => ['GET'],
            'auth' => true,
        ],
        'logs.tail' => [
            'path' => '/admin/logs/apache-error-tail',
            'controller' => 'App\\Http\\Controllers\\LogsController@tail',
            'methods' => ['GET'],
            'auth' => true,
            'csrf' => true,
        ],
    ],
];
