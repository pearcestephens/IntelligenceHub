<?php

declare(strict_types=1);

namespace Scanner\Lib;

use PDO;
use PDOException;
use InvalidArgumentException;
use RuntimeException;

/**
 * AutoFixService - AI-Powered Automatic Code Fix Service
 *
 * Provides intelligent auto-fix capabilities for code violations using
 * AI-generated fixes with preview, validation, and safe application.
 *
 * @package Scanner\Lib
 * @version 4.0.0
 * @author  Scanner Team
 */
class AutoFixService
{
    private PDO $db;
    private AIAssistant $aiAssistant;
    private string $backupDir;

    /**
     * Supported fix types that can be auto-applied
     */
    private const SAFE_FIX_TYPES = [
        'SEC001', // SQL Injection - prepared statements
        'SEC002', // XSS - output escaping
        'SEC003', // Hardcoded credentials - move to .env
        'CODE001', // Missing type hints
        'CODE002', // Missing docblocks
    ];

    /**
     * Constructor
     *
     * @param PDO $db Database connection
     * @param AIAssistant $aiAssistant AI assistant service
     * @param string $backupDir Directory for file backups
     *
     * @throws InvalidArgumentException If backup directory is invalid
     */
    public function __construct(PDO $db, AIAssistant $aiAssistant, string $backupDir)
    {
        $this->db = $db;
        $this->aiAssistant = $aiAssistant;
        $this->backupDir = rtrim($backupDir, '/');

        if (!is_dir($this->backupDir) && !mkdir($this->backupDir, 0755, true)) {
            throw new InvalidArgumentException(
                "Cannot create backup directory: {$this->backupDir}"
            );
        }
    }

    /**
     * Generate auto-fix preview for a violation
     *
     * @param int $violationId Violation ID
     * @return array{
     *     success: bool,
     *     violation: array,
     *     original_code: string,
     *     fixed_code: string,
     *     explanation: string,
     *     is_safe: bool,
     *     can_auto_apply: bool
     * }
     *
     * @throws InvalidArgumentException If violation not found
     * @throws RuntimeException If AI service fails
     */
    public function generateFixPreview(int $violationId): array
    {
        // Get violation details
        $violation = $this->getViolation($violationId);

        if (empty($violation)) {
            throw new InvalidArgumentException("Violation {$violationId} not found");
        }

        // Get file content
        $fileContent = $this->getFileContent($violation['file_path']);

        // Extract code snippet around violation
        $codeSnippet = $this->extractCodeSnippet(
            $fileContent,
            (int)$violation['line_number'],
            10 // lines of context
        );

        // Generate AI fix using violation ID
        $aiResponse = $this->aiAssistant->generateFix($violationId);

        if (!$aiResponse['success']) {
            throw new RuntimeException("Failed to generate fix: " . $aiResponse['error']);
        }

        // Determine if fix is safe to auto-apply
        $isSafe = $this->isFixSafe($aiResponse['fixed_code']);

        return [
            'success' => true,
            'violation' => $violation,
            'original_code' => $aiResponse['original_code'],
            'fixed_code' => $aiResponse['fixed_code'],
            'explanation' => $aiResponse['explanation'],
            'is_safe' => $isSafe,
            'can_auto_apply' => $isSafe && in_array($violation['rule_id'], self::SAFE_FIX_TYPES, true)
        ];
    }

    /**
     * Apply auto-fix to a file
     *
     * @param int $violationId Violation ID
     * @param bool $createBackup Create backup before applying fix
     * @return array{
     *     success: bool,
     *     message: string,
     *     backup_path?: string,
     *     lines_changed?: int
     * }
     *
     * @throws InvalidArgumentException If violation not found or not fixable
     * @throws RuntimeException If file operations fail
     */
    public function applyAutoFix(int $violationId, bool $createBackup = true): array
    {
        // Generate fix preview first
        $preview = $this->generateFixPreview($violationId);

        if (!$preview['can_auto_apply']) {
            throw new InvalidArgumentException(
                "Violation {$violationId} cannot be auto-fixed (manual review required)"
            );
        }

        $violation = $preview['violation'];
        $filePath = $violation['file_path'];

        // Validate file exists and is writable
        if (!file_exists($filePath)) {
            throw new RuntimeException("File not found: {$filePath}");
        }

        if (!is_writable($filePath)) {
            throw new RuntimeException("File not writable: {$filePath}");
        }

        // Create backup
        $backupPath = null;
        if ($createBackup) {
            $backupPath = $this->createBackup($filePath);
        }

        try {
            // Read current content
            $content = file_get_contents($filePath);
            if ($content === false) {
                throw new RuntimeException("Failed to read file: {$filePath}");
            }

            // Apply fix
            $newContent = $this->applyFixToContent(
                $content,
                $preview['original_code'],
                $preview['fixed_code']
            );

            // Write fixed content
            $bytesWritten = file_put_contents($filePath, $newContent);
            if ($bytesWritten === false) {
                throw new RuntimeException("Failed to write file: {$filePath}");
            }

            // Calculate lines changed
            $linesChanged = $this->calculateLinesChanged(
                $preview['original_code'],
                $preview['fixed_code']
            );

            // Update violation status
            $this->markViolationResolved($violationId, 'auto_fixed');

            // Log the fix
            $this->logAutoFix($violationId, $backupPath, $linesChanged);

            return [
                'success' => true,
                'message' => 'Fix applied successfully',
                'backup_path' => $backupPath,
                'lines_changed' => $linesChanged
            ];

        } catch (\Exception $e) {
            // Restore backup if something went wrong
            if ($backupPath && file_exists($backupPath)) {
                copy($backupPath, $filePath);
            }

            throw new RuntimeException(
                "Failed to apply fix: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    /**
     * Batch apply fixes to multiple violations
     *
     * @param int[] $violationIds Array of violation IDs
     * @param bool $stopOnError Stop processing if error occurs
     * @return array{
     *     total: int,
     *     succeeded: int,
     *     failed: int,
     *     results: array
     * }
     */
    public function batchApplyFixes(array $violationIds, bool $stopOnError = false): array
    {
        $results = [
            'total' => count($violationIds),
            'succeeded' => 0,
            'failed' => 0,
            'results' => []
        ];

        foreach ($violationIds as $violationId) {
            try {
                $result = $this->applyAutoFix($violationId, true);
                $results['succeeded']++;
                $results['results'][$violationId] = [
                    'success' => true,
                    'message' => $result['message']
                ];

            } catch (\Exception $e) {
                $results['failed']++;
                $results['results'][$violationId] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];

                if ($stopOnError) {
                    break;
                }
            }
        }

        return $results;
    }

    /**
     * Get violation details from database
     *
     * @param int $violationId
     * @return array
     */
    private function getViolation(int $violationId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                v.*,
                f.file_path,
                f.file_type
            FROM violations v
            JOIN intelligence_files f ON v.file_id = f.file_id
            WHERE v.id = ?
        ");

        $stmt->execute([$violationId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get file content
     *
     * @param string $filePath
     * @return string
     *
     * @throws RuntimeException If file cannot be read
     */
    private function getFileContent(string $filePath): string
    {
        $content = file_get_contents($filePath);

        if ($content === false) {
            throw new RuntimeException("Cannot read file: {$filePath}");
        }

        return $content;
    }

    /**
     * Extract code snippet around specific line
     *
     * @param string $content File content
     * @param int $lineNumber Target line number
     * @param int $contextLines Lines of context to include
     * @return string
     */
    private function extractCodeSnippet(
        string $content,
        int $lineNumber,
        int $contextLines = 10
    ): string {
        $lines = explode("\n", $content);
        $startLine = max(0, $lineNumber - $contextLines - 1);
        $endLine = min(count($lines), $lineNumber + $contextLines);

        return implode("\n", array_slice($lines, $startLine, $endLine - $startLine));
    }

    /**
     * Check if fix is safe to auto-apply
     *
     * @param string $fixedCode Fixed code
     * @return bool
     */
    private function isFixSafe(string $fixedCode): bool
    {
        // Safety checks for dangerous patterns
        $dangerousPatterns = [
            '/rm\s+-rf/',           // Dangerous shell commands
            '/DROP\s+TABLE/i',      // Database drops
            '/exec\s*\(/i',         // Code execution
            '/eval\s*\(/i',         // Eval
            '/system\s*\(/i',       // System calls
            '/unserialize\s*\(/i',  // Unserialize (potential PHP object injection)
            '/file_get_contents\s*\(\s*\$/i',  // File operations with variables
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $fixedCode)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create backup of file
     *
     * @param string $filePath
     * @return string Backup file path
     *
     * @throws RuntimeException If backup fails
     */
    private function createBackup(string $filePath): string
    {
        $timestamp = date('Y-m-d_H-i-s');
        $basename = basename($filePath);
        $backupPath = "{$this->backupDir}/{$basename}.{$timestamp}.backup";

        if (!copy($filePath, $backupPath)) {
            throw new RuntimeException("Failed to create backup: {$backupPath}");
        }

        return $backupPath;
    }

    /**
     * Apply fix to content
     *
     * @param string $content Original content
     * @param string $originalCode Code to replace
     * @param string $fixedCode Replacement code
     * @return string Fixed content
     */
    private function applyFixToContent(
        string $content,
        string $originalCode,
        string $fixedCode
    ): string {
        // Normalize line endings
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $originalCode = str_replace(["\r\n", "\r"], "\n", $originalCode);
        $fixedCode = str_replace(["\r\n", "\r"], "\n", $fixedCode);

        // Replace the code
        $newContent = str_replace($originalCode, $fixedCode, $content, $count);

        if ($count === 0) {
            throw new RuntimeException("Original code not found in file");
        }

        if ($count > 1) {
            throw new RuntimeException("Original code appears multiple times (ambiguous)");
        }

        return $newContent;
    }

    /**
     * Calculate number of lines changed
     *
     * @param string $originalCode
     * @param string $fixedCode
     * @return int
     */
    private function calculateLinesChanged(string $originalCode, string $fixedCode): int
    {
        $originalLines = count(explode("\n", $originalCode));
        $fixedLines = count(explode("\n", $fixedCode));

        return abs($fixedLines - $originalLines);
    }

    /**
     * Mark violation as resolved
     *
     * @param int $violationId
     * @param string $resolutionMethod
     * @return void
     */
    private function markViolationResolved(int $violationId, string $resolutionMethod): void
    {
        $stmt = $this->db->prepare("
            UPDATE violations
            SET
                status = 'resolved',
                resolved_at = NOW(),
                resolution_notes = ?
            WHERE id = ?
        ");

        $stmt->execute(["Auto-fixed by AI ({$resolutionMethod})", $violationId]);
    }

    /**
     * Log auto-fix action
     *
     * @param int $violationId
     * @param string|null $backupPath
     * @param int $linesChanged
     * @return void
     */
    private function logAutoFix(int $violationId, ?string $backupPath, int $linesChanged): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO auto_fix_log (
                violation_id,
                backup_path,
                lines_changed,
                applied_at
            ) VALUES (?, ?, ?, NOW())
        ");

        $stmt->execute([$violationId, $backupPath, $linesChanged]);
    }
}
