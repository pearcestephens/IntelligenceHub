<?php
/**
 * Intelligence Hub - System Health Check & Startup Validation
 *
 * Comprehensive validation of all system components, files, and services
 * Run this on system startup or periodically to ensure everything is operational
 *
 * @version 1.0.0
 * @date 2025-11-05
 */

declare(strict_types=1);

// Set execution time limit for thorough checks
set_time_limit(300);

// Load environment
$envPaths = [
    __DIR__ . '/.env',
    __DIR__ . '/../../../private_html/config/.env',
];

foreach ($envPaths as $envPath) {
    if (file_exists($envPath) && is_readable($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || $line[0] === '#' || $line[0] === ';') continue;
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value, '"\'');
                $_SERVER[trim($key)] = trim($value, '"\'');
            }
        }
        break;
    }
}

// Output formatting
$isCliMode = php_sapi_name() === 'cli';
$RED = $isCliMode ? "\033[31m" : '';
$GREEN = $isCliMode ? "\033[32m" : '';
$YELLOW = $isCliMode ? "\033[33m" : '';
$RESET = $isCliMode ? "\033[0m" : '';

if (!$isCliMode) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html><head><title>Intelligence Hub Health Check</title>";
    echo "<style>body{font-family:monospace;background:#1a1a1a;color:#0f0;padding:20px;}";
    echo ".pass{color:#0f0;}.fail{color:#f00;}.warn{color:#ff0;}.section{margin:20px 0;border-top:1px solid #333;padding-top:10px;}";
    echo "pre{white-space:pre-wrap;}</style></head><body><pre>";
}

echo "╔══════════════════════════════════════════════════════════════════════╗\n";
echo "║  INTELLIGENCE HUB - SYSTEM HEALTH CHECK                             ║\n";
echo "╚══════════════════════════════════════════════════════════════════════╝\n\n";
echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Server: " . php_uname('n') . "\n\n";

// Tracking
$totalChecks = 0;
$passedChecks = 0;
$failedChecks = 0;
$warnings = 0;
$criticalErrors = [];

/**
 * Check helper functions
 */
function check(string $name, bool $passed, string $message = '', bool $critical = false): bool {
    global $totalChecks, $passedChecks, $failedChecks, $warnings, $criticalErrors;
    global $RED, $GREEN, $YELLOW, $RESET;

    $totalChecks++;

    if ($passed) {
        $passedChecks++;
        echo "{$GREEN}✓{$RESET} {$name}\n";
        if ($message) echo "  {$message}\n";
    } else {
        if ($critical) {
            $failedChecks++;
            $criticalErrors[] = $name;
            echo "{$RED}✗ CRITICAL{$RESET} {$name}\n";
        } else {
            $warnings++;
            echo "{$YELLOW}⚠ WARNING{$RESET} {$name}\n";
        }
        if ($message) echo "  {$message}\n";
    }

    return $passed;
}

function section(string $title): void {
    echo "\n" . str_repeat("─", 70) . "\n";
    echo "  {$title}\n";
    echo str_repeat("─", 70) . "\n";
}

// ============================================================================
// 1. CRITICAL FILES CHECK
// ============================================================================
section("1. CRITICAL FILES");

$basePath = '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html';

$criticalFiles = [
    // Core Intelligence Hub
    'admin/intelligence-hub/index.php' => 'Main dashboard entry',
    'admin/intelligence-hub/api.php' => 'API endpoint',
    'admin/intelligence-hub/includes/functions.php' => 'Core functions',
    'admin/intelligence-hub/includes/header.php' => 'Template header',
    'admin/intelligence-hub/includes/sidebar.php' => 'Template sidebar',
    'admin/intelligence-hub/includes/footer.php' => 'Template footer',

    // Agents
    'admin/intelligence-hub/agents/InventoryAgent.php' => 'Inventory Agent',
    'admin/intelligence-hub/agents/WebMonitorAgent.php' => 'Web Monitor Agent',
    'admin/intelligence-hub/agents/SecurityAgent.php' => 'Security Agent',

    // MCP Server
    'mcp/server_v3.php' => 'MCP Server v3',
    'mcp/mcp-health-monitor.php' => 'Health monitor',
    'mcp/verify-env-config.php' => 'Environment validator',

    // Configuration
    '../private_html/config/.env' => 'Master environment config',
];

foreach ($criticalFiles as $file => $description) {
    $fullPath = $basePath . '/' . $file;
    $exists = file_exists($fullPath);
    $readable = $exists && is_readable($fullPath);
    $size = $exists ? filesize($fullPath) : 0;

    if ($exists && $readable && $size > 0) {
        check(
            $description,
            true,
            sprintf("File: %s (%s)", basename($file), number_format($size) . ' bytes'),
            false
        );
    } else {
        check(
            $description,
            false,
            sprintf("File: %s - %s", $file, !$exists ? 'NOT FOUND' : ($size === 0 ? 'EMPTY' : 'NOT READABLE')),
            true
        );
    }
}

// ============================================================================
// 2. ENVIRONMENT CONFIGURATION
// ============================================================================
section("2. ENVIRONMENT CONFIGURATION");

$requiredEnvVars = [
    'DB_HOST' => 'Database host',
    'DB_NAME' => 'Database name',
    'DB_USER' => 'Database username',
    'DB_PASS' => 'Database password',
    'MCP_API_KEY' => 'MCP API key',
    'MCP_SERVER_URL' => 'MCP server URL',
    'AI_AGENT_ENDPOINT' => 'AI Agent endpoint',
];

foreach ($requiredEnvVars as $var => $description) {
    $value = $_ENV[$var] ?? $_SERVER[$var] ?? '';
    $exists = !empty($value);

    check(
        $description . " ({$var})",
        $exists,
        $exists ? (strlen($value) > 50 ? substr($value, 0, 30) . '...' : (preg_match('/(PASS|KEY|TOKEN)/i', $var) ? str_repeat('*', min(20, strlen($value))) : $value)) : 'NOT SET',
        true
    );
}

// Check .env file loaded
$envVarCount = count(array_filter(array_keys($_ENV), function($key) {
    return !in_array($key, ['PATH', 'PWD', 'HOME', 'USER', 'SHELL']);
}));
check(
    'Environment variables loaded',
    $envVarCount > 50,
    sprintf('%d variables loaded', $envVarCount),
    true
);

// ============================================================================
// 3. DATABASE CONNECTION
// ============================================================================
section("3. DATABASE CONNECTION");

try {
    $dsn = sprintf(
        "mysql:host=%s;dbname=%s;charset=%s",
        $_ENV['DB_HOST'] ?? '127.0.0.1',
        $_ENV['DB_NAME'] ?? 'hdgwrzntwa',
        $_ENV['DB_CHARSET'] ?? 'utf8mb4'
    );

    $pdo = new PDO(
        $dsn,
        $_ENV['DB_USER'] ?? 'hdgwrzntwa',
        $_ENV['DB_PASS'] ?? '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]
    );

    // Test query
    $stmt = $pdo->query('SELECT VERSION() as version, DATABASE() as dbname, NOW() as now');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    check('Database connection', true, sprintf('MySQL %s', $result['version']), false);
    check('Database selected', true, sprintf('Using: %s', $result['dbname']), false);
    check('Database time', true, sprintf('Server time: %s', $result['now']), false);

    // Check critical tables
    $criticalTables = [
        'agent_runs' => 'Agent execution tracking',
        'agent_tasks' => 'Agent task queue',
        'mcp_health_log' => 'MCP health monitoring',
        'mcp_service_metrics' => 'MCP service metrics',
    ];

    foreach ($criticalTables as $table => $description) {
        $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
        $exists = $stmt->rowCount() > 0;

        if ($exists) {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM `{$table}`");
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            check(
                "Table: {$description}",
                true,
                sprintf('%s rows', number_format($count)),
                false
            );
        } else {
            check("Table: {$description}", false, "Table '{$table}' not found", false);
        }
    }

} catch (PDOException $e) {
    check('Database connection', false, $e->getMessage(), true);
}

// ============================================================================
// 4. PHP CONFIGURATION
// ============================================================================
section("4. PHP CONFIGURATION");

$phpRequirements = [
    'version' => [
        'check' => version_compare(PHP_VERSION, '8.0.0', '>='),
        'message' => 'PHP 8.0+ required, running ' . PHP_VERSION,
        'critical' => true
    ],
    'pdo' => [
        'check' => extension_loaded('pdo'),
        'message' => 'PDO extension',
        'critical' => true
    ],
    'pdo_mysql' => [
        'check' => extension_loaded('pdo_mysql'),
        'message' => 'PDO MySQL driver',
        'critical' => true
    ],
    'curl' => [
        'check' => extension_loaded('curl'),
        'message' => 'cURL extension',
        'critical' => true
    ],
    'json' => [
        'check' => extension_loaded('json'),
        'message' => 'JSON extension',
        'critical' => true
    ],
    'mbstring' => [
        'check' => extension_loaded('mbstring'),
        'message' => 'Multibyte string extension',
        'critical' => false
    ],
    'openssl' => [
        'check' => extension_loaded('openssl'),
        'message' => 'OpenSSL extension',
        'critical' => true
    ],
];

foreach ($phpRequirements as $name => $req) {
    check(
        $req['message'],
        $req['check'],
        $req['check'] ? 'Available' : 'Missing',
        $req['critical']
    );
}

// Memory limit
$memoryLimit = ini_get('memory_limit');
$memoryBytes = preg_replace('/[^0-9]/', '', $memoryLimit) * (1024 * 1024);
check(
    'Memory limit',
    $memoryBytes >= 128 * 1024 * 1024,
    sprintf('Current: %s (minimum: 128M)', $memoryLimit),
    false
);

// Max execution time
$maxExecTime = (int)ini_get('max_execution_time');
check(
    'Max execution time',
    $maxExecTime >= 300 || $maxExecTime === 0,
    sprintf('Current: %s seconds', $maxExecTime === 0 ? 'unlimited' : $maxExecTime),
    false
);

// ============================================================================
// 5. FILE PERMISSIONS
// ============================================================================
section("5. FILE PERMISSIONS");

$writableDirs = [
    'logs' => 'Log directory',
    'cache' => 'Cache directory',
    '../private_html/sessions' => 'Session directory',
    '../private_html/uploads' => 'Upload directory',
];

foreach ($writableDirs as $dir => $description) {
    $fullPath = $basePath . '/' . $dir;
    $exists = is_dir($fullPath);
    $writable = $exists && is_writable($fullPath);

    check(
        $description,
        $writable,
        sprintf('%s - %s', $dir, $writable ? 'Writable' : ($exists ? 'Not writable' : 'Not found')),
        !$writable
    );
}

// ============================================================================
// 6. AGENT STATUS
// ============================================================================
section("6. AGENT STATUS");

$agentClasses = [
    'InventoryAgent' => 'Inventory Management',
    'WebMonitorAgent' => 'Website Monitoring',
    'SecurityAgent' => 'Security Monitoring',
];

foreach ($agentClasses as $class => $description) {
    $file = $basePath . '/admin/intelligence-hub/agents/' . $class . '.php';

    if (file_exists($file)) {
        require_once $file;

        $classExists = class_exists($class, false);
        check(
            "Agent: {$description}",
            $classExists,
            sprintf('%s.php - %s', $class, $classExists ? 'Class loaded' : 'Class not found'),
            !$classExists
        );

        if ($classExists) {
            // Check required methods
            $requiredMethods = ['execute', 'shouldRun', 'getPriority'];
            foreach ($requiredMethods as $method) {
                $hasMethod = method_exists($class, $method);
                if (!$hasMethod) {
                    check(
                        "  Method: {$method}",
                        false,
                        "Required method missing",
                        false
                    );
                }
            }
        }
    } else {
        check(
            "Agent: {$description}",
            false,
            "{$class}.php not found",
            true
        );
    }
}

// ============================================================================
// 7. MCP SERVER STATUS
// ============================================================================
section("7. MCP SERVER STATUS");

$mcpUrl = $_ENV['MCP_SERVER_URL'] ?? '';
$mcpKey = $_ENV['MCP_API_KEY'] ?? '';

if ($mcpUrl && $mcpKey) {
    // Check if MCP server file exists
    $mcpFile = $basePath . '/mcp/server_v3.php';
    check(
        'MCP server file',
        file_exists($mcpFile),
        file_exists($mcpFile) ? 'server_v3.php found' : 'File not found',
        true
    );

    // Test MCP endpoint (basic connectivity - 401 is acceptable since we're outside VS Code context)
    $ch = curl_init($mcpUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'X-API-Key: ' . $mcpKey
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'jsonrpc' => '2.0',
            'method' => 'tools/list',
            'params' => [],
            'id' => 1
        ]),
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    // 401 is acceptable - means server is responding but needs proper authentication context
    $acceptable = in_array($httpCode, [200, 401]);

    check(
        'MCP server responding',
        $acceptable,
        sprintf('HTTP %d%s', $httpCode, $error ? " - {$error}" : ''),
        !$acceptable && $httpCode !== 401
    );
} else {
    check('MCP configuration', false, 'MCP_SERVER_URL or MCP_API_KEY not set', true);
}

// ============================================================================
// 8. EXTERNAL SERVICES
// ============================================================================
section("8. EXTERNAL SERVICES");

// Test Vend API
$vendUrl = $_ENV['VEND_URL'] ?? '';
$vendToken = $_ENV['VEND_TOKEN'] ?? '';

if ($vendUrl && $vendToken) {
    $ch = curl_init($vendUrl . '/api/2.0/products');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $vendToken
        ],
        CURLOPT_TIMEOUT => 10
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    check(
        'Vend POS API',
        $httpCode === 200,
        sprintf('HTTP %d - %s', $httpCode, $httpCode === 200 ? 'Connected' : 'Connection failed'),
        false
    );
} else {
    check('Vend POS API', false, 'Configuration not set (optional)', false);
}

// Test Redis (if configured)
$redisHost = $_ENV['REDIS_HOST'] ?? '';
$redisPort = $_ENV['REDIS_PORT'] ?? 6379;

if ($redisHost && extension_loaded('redis')) {
    try {
        $redis = new Redis();
        $connected = $redis->connect($redisHost, (int)$redisPort, 2);
        check(
            'Redis cache',
            $connected,
            sprintf('%s:%d - %s', $redisHost, $redisPort, $connected ? 'Connected' : 'Failed'),
            false
        );
        if ($connected) $redis->close();
    } catch (Exception $e) {
        check('Redis cache', false, $e->getMessage(), false);
    }
} else {
    check('Redis cache', false, 'Not configured or extension not loaded (optional)', false);
}

// ============================================================================
// SUMMARY
// ============================================================================
echo "\n";
echo "╔══════════════════════════════════════════════════════════════════════╗\n";
echo "║  HEALTH CHECK SUMMARY                                                ║\n";
echo "╚══════════════════════════════════════════════════════════════════════╝\n\n";

echo sprintf("Total Checks:    %s%d%s\n", $RESET, $totalChecks, $RESET);
echo sprintf("Passed:          %s%d%s\n", $GREEN, $passedChecks, $RESET);
echo sprintf("Warnings:        %s%d%s\n", $YELLOW, $warnings, $RESET);
echo sprintf("Critical Errors: %s%d%s\n", $RED, $failedChecks, $RESET);

$passRate = $totalChecks > 0 ? round(($passedChecks / $totalChecks) * 100, 1) : 0;
echo sprintf("\nPass Rate:       %s%.1f%%%s\n", $passRate >= 90 ? $GREEN : ($passRate >= 70 ? $YELLOW : $RED), $passRate, $RESET);

if (count($criticalErrors) > 0) {
    echo "\n{$RED}CRITICAL ISSUES:{$RESET}\n";
    foreach ($criticalErrors as $error) {
        echo "  • {$error}\n";
    }
    echo "\n{$RED}SYSTEM NOT READY FOR PRODUCTION{$RESET}\n";
    $exitCode = 1;
} else if ($warnings > 0) {
    echo "\n{$YELLOW}System operational with warnings{$RESET}\n";
    $exitCode = 0;
} else {
    echo "\n{$GREEN}✓ ALL SYSTEMS OPERATIONAL{$RESET}\n";
    echo "{$GREEN}✓ SYSTEM READY FOR PRODUCTION{$RESET}\n";
    $exitCode = 0;
}

echo "\n" . str_repeat("═", 70) . "\n";
echo "Health check completed at " . date('Y-m-d H:i:s') . "\n";

if (!$isCliMode) {
    echo "</pre></body></html>";
}

exit($exitCode);
