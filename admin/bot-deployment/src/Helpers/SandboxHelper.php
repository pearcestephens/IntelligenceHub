<?php
/**
 * Sandbox Helper
 *
 * Provides generic/public/sandbox fallback functionality when no specific
 * project or business unit context is available.
 *
 * @package BotDeployment\Helpers
 * @author  Ecigdis Limited
 * @version 1.0.0
 */

namespace BotDeployment\Helpers;

use PDO;

class SandboxHelper
{
    /**
     * Generic Sandbox Business Unit ID
     */
    const SANDBOX_UNIT_ID = 999;

    /**
     * Generic Sandbox Project ID
     */
    const SANDBOX_PROJECT_ID = 999;

    /**
     * Allowed paths for sandbox access
     */
    const ALLOWED_PATHS = [
        '/sandbox',
        '/public_html/sandbox',
        '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/sandbox'
    ];

    /**
     * Excluded paths (even within sandbox)
     */
    const EXCLUDED_PATHS = [
        '/sandbox/private',
        '/sandbox/secrets',
        '/sandbox/.env',
        '/sandbox/.git'
    ];

    /**
     * Check if using generic sandbox
     *
     * @param int|null $projectId Project ID to check
     * @param int|null $unitId Unit ID to check
     * @return bool True if using sandbox
     */
    public static function isSandbox(?int $projectId, ?int $unitId = null): bool
    {
        return $projectId === self::SANDBOX_PROJECT_ID ||
               $unitId === self::SANDBOX_UNIT_ID ||
               $projectId === null ||
               $unitId === null;
    }

    /**
     * Get sandbox project ID (fallback)
     *
     * @param int|null $projectId Current project ID
     * @return int Validated project ID or sandbox fallback
     */
    public static function getProjectId(?int $projectId): int
    {
        return $projectId ?? self::SANDBOX_PROJECT_ID;
    }

    /**
     * Get sandbox unit ID (fallback)
     *
     * @param int|null $unitId Current unit ID
     * @return int Validated unit ID or sandbox fallback
     */
    public static function getUnitId(?int $unitId): int
    {
        return $unitId ?? self::SANDBOX_UNIT_ID;
    }

    /**
     * Get sandbox project info from database
     *
     * @param PDO $pdo Database connection
     * @return array|null Project info or null
     */
    public static function getSandboxProject(PDO $pdo): ?array
    {
        $stmt = $pdo->prepare("
            SELECT
                id,
                project_name,
                project_slug,
                project_type,
                project_path,
                status
            FROM projects
            WHERE id = ?
        ");

        $stmt->execute([self::SANDBOX_PROJECT_ID]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }    /**
     * Get sandbox business unit info from database
     *
     * @param PDO $pdo Database connection
     * @return array|null Unit info or null
     */
    public static function getSandboxUnit(PDO $pdo): ?array
    {
        $stmt = $pdo->prepare("
            SELECT
                unit_id,
                unit_name,
                unit_type,
                domain_mapping,
                intelligence_level
            FROM business_units
            WHERE unit_id = ?
        ");

        $stmt->execute([self::SANDBOX_UNIT_ID]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }    /**
     * Verify sandbox exists in database
     *
     * @param PDO $pdo Database connection
     * @return bool True if sandbox exists
     */
    public static function verifySandboxExists(PDO $pdo): bool
    {
        $project = self::getSandboxProject($pdo);
        $unit = self::getSandboxUnit($pdo);

        return $project !== null && $unit !== null;
    }

    /**
     * Initialize sandbox in session
     *
     * @param int|null $projectId Current project ID
     * @param int|null $unitId Current unit ID
     * @return array Session data to set
     */
    public static function initializeSandboxSession(?int $projectId, ?int $unitId): array
    {
        $usingSandbox = self::isSandbox($projectId, $unitId);

        return [
            'current_project_id' => self::getProjectId($projectId),
            'current_unit_id' => self::getUnitId($unitId),
            'is_sandbox' => $usingSandbox,
            'sandbox_mode' => $usingSandbox ? 'active' : 'inactive',
            'access_level' => $usingSandbox ? 'sandbox' : 'normal'
        ];
    }

    /**
     * Validate file path for sandbox access
     *
     * @param string $path File path to validate
     * @param bool $isSandbox Whether in sandbox mode
     * @return bool True if path is allowed
     */
    public static function validatePath(string $path, bool $isSandbox): bool
    {
        // If not in sandbox mode, normal validation applies
        if (!$isSandbox) {
            return true;
        }

        // Normalize path
        $path = realpath($path) ?: $path;
        $path = str_replace('\\', '/', $path);

        // Check if path is in allowed sandbox paths
        $inAllowedPath = false;
        foreach (self::ALLOWED_PATHS as $allowedPath) {
            if (strpos($path, $allowedPath) === 0) {
                $inAllowedPath = true;
                break;
            }
        }

        if (!$inAllowedPath) {
            return false;
        }

        // Check if path is NOT in excluded paths
        foreach (self::EXCLUDED_PATHS as $excludedPath) {
            if (strpos($path, $excludedPath) !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get sandbox restrictions info
     *
     * @return array Restriction details
     */
    public static function getRestrictions(): array
    {
        return [
            'allowed_paths' => self::ALLOWED_PATHS,
            'excluded_paths' => self::EXCLUDED_PATHS,
            'max_file_size' => 1024 * 1024, // 1MB
            'allowed_extensions' => ['.txt', '.md', '.json', '.log', '.csv'],
            'max_files' => 100,
            'max_depth' => 2,
            'read_only' => true,
            'no_execution' => true,
            'no_database_write' => true
        ];
    }

    /**
     * Log sandbox access attempt
     *
     * @param PDO $pdo Database connection
     * @param string $action Action being performed
     * @param array $details Additional details
     * @return bool Success
     */
    public static function logAccess(PDO $pdo, string $action, array $details = []): bool
    {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO sandbox_access_log (
                    project_id,
                    unit_id,
                    action,
                    details,
                    ip_address,
                    user_agent,
                    created_at
                ) VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

            return $stmt->execute([
                self::SANDBOX_PROJECT_ID,
                self::SANDBOX_UNIT_ID,
                $action,
                json_encode($details),
                $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ]);
        } catch (\Exception $e) {
            // Table might not exist - fail silently
            return false;
        }
    }

    /**
     * Get sandbox usage statistics
     *
     * @param PDO $pdo Database connection
     * @return array Statistics
     */
    public static function getStats(PDO $pdo): array
    {
        try {
            $stmt = $pdo->prepare("
                SELECT
                    COUNT(*) as total_accesses,
                    COUNT(DISTINCT ip_address) as unique_ips,
                    MAX(created_at) as last_access,
                    MIN(created_at) as first_access
                FROM sandbox_access_log
                WHERE project_id = ? AND unit_id = ?
                AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ");

            $stmt->execute([self::SANDBOX_PROJECT_ID, self::SANDBOX_UNIT_ID]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);

            return $stats ?: [
                'total_accesses' => 0,
                'unique_ips' => 0,
                'last_access' => null,
                'first_access' => null
            ];
        } catch (\Exception $e) {
            return [
                'total_accesses' => 0,
                'unique_ips' => 0,
                'last_access' => null,
                'first_access' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create sandbox directory structure if not exists
     *
     * @param string $basePath Base path to create sandbox under
     * @return bool Success
     */
    public static function createSandboxStructure(string $basePath): bool
    {
        $sandboxPath = rtrim($basePath, '/') . '/sandbox';

        $directories = [
            $sandboxPath,
            $sandboxPath . '/public',
            $sandboxPath . '/temp',
            $sandboxPath . '/logs'
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    return false;
                }
            }
        }

        // Create readme file
        $readmePath = $sandboxPath . '/README.md';
        if (!file_exists($readmePath)) {
            $readme = "# Generic Sandbox\n\n";
            $readme .= "This is a generic/public sandbox environment for testing and experiments.\n\n";
            $readme .= "## Restrictions\n\n";
            $readme .= "- Read-only access\n";
            $readme .= "- No sensitive data\n";
            $readme .= "- Limited file types\n";
            $readme .= "- Max 2 levels deep\n";
            $readme .= "- No execution permissions\n";

            file_put_contents($readmePath, $readme);
        }

        return true;
    }
}
