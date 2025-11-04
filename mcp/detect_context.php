<?php
/**
 * Project Context Detection Library
 *
 * Automatically detects which server, business unit, and project
 * the current user is working on based on file paths, workspace config,
 * and environment variables.
 *
 * Used by: GitHub Copilot, MCP server, conversation tracking
 *
 * @package IntelligenceHub
 * @version 1.0.0
 */

declare(strict_types=1);

class ContextDetector
{
    private array $serverMapping = [
        'hdgwrzntwa' => ['unit_id' => 1, 'unit_name' => 'Intelligence Hub'],
        'jcepnzzkmj' => ['unit_id' => 2, 'unit_name' => 'CIS System'],
        'dvaxgvsxmz' => ['unit_id' => 3, 'unit_name' => 'VapeShed Retail'],
        'fhrehrpjmu' => ['unit_id' => 4, 'unit_name' => 'Wholesale Portal'],
    ];

    private array $pathPatterns = [
        '/hdgwrzntwa/' => 'hdgwrzntwa',
        '/jcepnzzkmj/' => 'jcepnzzkmj',
        '/dvaxgvsxmz/' => 'dvaxgvsxmz',
        '/fhrehrpjmu/' => 'fhrehrpjmu',
    ];

    private array $projectMapping = [
        // Intelligence Hub
        '/hdgwrzntwa/' => 1,

        // CIS Modules
        '/jcepnzzkmj/public_html/modules/consignments/' => 2,
        '/jcepnzzkmj/public_html/modules/supplier/' => 3,
        '/jcepnzzkmj/public_html/modules/purchase_orders/' => 4,
        '/jcepnzzkmj/public_html/modules/inventory/' => 5,
        '/jcepnzzkmj/public_html/modules/transfers/' => 6,
        '/jcepnzzkmj/public_html/modules/hr/' => 7,
        '/jcepnzzkmj/public_html/modules/webhooks/' => 8,
        '/jcepnzzkmj/public_html/modules/base/' => 9,
        '/jcepnzzkmj/public_html' => 13, // CIS Core (default)

        // Other servers
        '/fhrehrpjmu/' => 10,
        '/dvaxgvsxmz/' => 12,
    ];

    /**
     * Detect context from file path
     *
     * @param string $filePath Full path to file being edited
     * @return array Context array with server_id, unit_id, project_id, project_name
     */
    public function detectFromPath(string $filePath): array
    {
        $context = [
            'server_id' => null,
            'unit_id' => null,
            'project_id' => null,
            'project_name' => null,
            'detection_method' => 'path',
            'confidence' => 0.0,
        ];

        // Detect server from path
        foreach ($this->pathPatterns as $pattern => $serverId) {
            if (strpos($filePath, $pattern) !== false) {
                $context['server_id'] = $serverId;
                $context['unit_id'] = $this->serverMapping[$serverId]['unit_id'];
                $context['confidence'] = 0.9;
                break;
            }
        }

        // Detect project from path (most specific match first)
        $matchedPath = '';
        foreach ($this->projectMapping as $pattern => $projectId) {
            if (strpos($filePath, $pattern) !== false && strlen($pattern) > strlen($matchedPath)) {
                $matchedPath = $pattern;
                $context['project_id'] = $projectId;
                $context['confidence'] = 1.0;
            }
        }

        return $context;
    }

    /**
     * Detect context from .vscode/mcp-context.json file
     *
     * @param string $workspaceRoot Path to workspace root
     * @return array|null Context array or null if file doesn't exist
     */
    public function detectFromVSCodeConfig(string $workspaceRoot): ?array
    {
        $configPath = rtrim($workspaceRoot, '/') . '/.vscode/mcp-context.json';

        if (!file_exists($configPath)) {
            return null;
        }

        $config = json_decode(file_get_contents($configPath), true);

        if (!$config) {
            return null;
        }

        return [
            'server_id' => $config['server_id'] ?? null,
            'unit_id' => $config['unit_id'] ?? null,
            'project_id' => $config['project_id'] ?? $config['default_project_id'] ?? null,
            'project_name' => $config['project_name'] ?? null,
            'detection_method' => 'vscode_config',
            'confidence' => 1.0,
        ];
    }

    /**
     * Detect context from environment variables
     *
     * @return array|null Context array or null if not detected
     */
    public function detectFromEnvironment(): ?array
    {
        $context = [
            'server_id' => $_ENV['MCP_SERVER_ID'] ?? $_SERVER['MCP_SERVER_ID'] ?? null,
            'unit_id' => isset($_ENV['MCP_UNIT_ID']) ? (int)$_ENV['MCP_UNIT_ID'] : null,
            'project_id' => isset($_ENV['MCP_PROJECT_ID']) ? (int)$_ENV['MCP_PROJECT_ID'] : null,
            'project_name' => $_ENV['MCP_PROJECT_NAME'] ?? null,
            'detection_method' => 'environment',
            'confidence' => 0.8,
        ];

        return ($context['server_id'] || $context['project_id']) ? $context : null;
    }

    /**
     * Detect context from current working directory
     *
     * @return array Context array
     */
    public function detectFromCwd(): array
    {
        $cwd = getcwd();
        return $this->detectFromPath($cwd);
    }

    /**
     * Detect context using all available methods (waterfall)
     *
     * Priority:
     * 1. VS Code config file (explicit configuration)
     * 2. File path (if provided)
     * 3. Environment variables
     * 4. Current working directory
     *
     * @param string|null $filePath File being edited (optional)
     * @param string|null $workspaceRoot Workspace root (optional)
     * @return array Best available context
     */
    public function detectContext(?string $filePath = null, ?string $workspaceRoot = null): array
    {
        // Try VS Code config first (highest priority)
        if ($workspaceRoot) {
            $context = $this->detectFromVSCodeConfig($workspaceRoot);
            if ($context && $context['project_id']) {
                return $context;
            }
        }

        // Try file path if provided
        if ($filePath) {
            $context = $this->detectFromPath($filePath);
            if ($context['project_id']) {
                return $context;
            }
        }

        // Try environment variables
        $context = $this->detectFromEnvironment();
        if ($context) {
            return $context;
        }

        // Fall back to current directory
        return $this->detectFromCwd();
    }

    /**
     * Detect project from module path within file path
     *
     * @param string $filePath Full file path
     * @param string $serverId Server ID (jcepnzzkmj, etc.)
     * @return int|null Project ID or null
     */
    public function detectProjectFromModulePath(string $filePath, string $serverId): ?int
    {
        if ($serverId !== 'jcepnzzkmj') {
            return null; // Only CIS has modules
        }

        // Check each module pattern
        $modules = [
            'modules/consignments' => 2,
            'modules/supplier' => 3,
            'modules/purchase_orders' => 4,
            'modules/inventory' => 5,
            'modules/transfers' => 6,
            'modules/hr' => 7,
            'modules/webhooks' => 8,
            'modules/base' => 9,
        ];

        foreach ($modules as $modulePath => $projectId) {
            if (strpos($filePath, $modulePath) !== false) {
                return $projectId;
            }
        }

        // Default to CIS Core if no module detected
        return 13;
    }

    /**
     * Get project name from database
     *
     * @param int $projectId Project ID
     * @return string|null Project name or null
     */
    public function getProjectName(int $projectId): ?string
    {
        static $pdo = null;

        if (!$pdo) {
            // Load .env for database credentials
            $env = parse_ini_file(__DIR__ . '/.env');
            $pdo = new PDO(
                "mysql:host={$env['DB_HOST']};dbname={$env['DB_NAME']};charset=utf8mb4",
                $env['DB_USER'],
                $env['DB_PASS'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        }

        $stmt = $pdo->prepare("SELECT project_name FROM projects WHERE id = ?");
        $stmt->execute([$projectId]);

        return $stmt->fetchColumn() ?: null;
    }

    /**
     * Validate detected context
     *
     * @param array $context Context array
     * @return bool True if context is valid
     */
    public function validateContext(array $context): bool
    {
        // Must have at least server_id or project_id
        if (!$context['server_id'] && !$context['project_id']) {
            return false;
        }

        // If server_id provided, must be valid
        if ($context['server_id'] && !isset($this->serverMapping[$context['server_id']])) {
            return false;
        }

        // If unit_id provided, must match server
        if ($context['server_id'] && $context['unit_id']) {
            if ($this->serverMapping[$context['server_id']]['unit_id'] !== $context['unit_id']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Format context for API submission
     *
     * @param array $context Detected context
     * @return array Formatted for save_conversation.php API
     */
    public function formatForAPI(array $context): array
    {
        return [
            'unit_id' => $context['unit_id'] ?? null,
            'project_id' => $context['project_id'] ?? null,
            'server_id' => $context['server_id'] ?? null,
            'source' => $context['source'] ?? 'github_copilot',
        ];
    }
}

/**
 * Helper function for quick context detection
 *
 * @param string|null $filePath File being edited
 * @param string|null $workspaceRoot Workspace root
 * @return array Detected context
 */
function detect_context(?string $filePath = null, ?string $workspaceRoot = null): array
{
    $detector = new ContextDetector();
    return $detector->detectContext($filePath, $workspaceRoot);
}

/**
 * Helper function to format context for API
 *
 * @param string|null $filePath File being edited
 * @param string|null $workspaceRoot Workspace root
 * @return array Context formatted for API
 */
function get_api_context(?string $filePath = null, ?string $workspaceRoot = null): array
{
    $detector = new ContextDetector();
    $context = $detector->detectContext($filePath, $workspaceRoot);
    return $detector->formatForAPI($context);
}
