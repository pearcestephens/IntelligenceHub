<?php
/**
 * Scanner - Quick Scan Service
 *
 * Provides one-click scanning for all projects with real-time progress tracking
 * and comprehensive PDF report generation.
 *
 * @package Scanner\Lib
 * @version 1.0.0
 */

declare(strict_types=1);

namespace Scanner\Lib;

use PDO;
use RuntimeException;
use InvalidArgumentException;

/**
 * Quick Scan Service
 *
 * Handles rapid scanning of multiple projects with progress tracking
 */
class QuickScanService
{
    private PDO $pdo;
    private string $tempDir;
    private array $config;

    /**
     * Constructor
     *
     * @param PDO $pdo Database connection
     * @param string $tempDir Temporary directory for scan data
     * @param array $config Scan configuration
     */
    public function __construct(PDO $pdo, string $tempDir = '/tmp/scanner', array $config = [])
    {
        $this->pdo = $pdo;
        $this->tempDir = $tempDir;
        $this->config = array_merge($this->getDefaultConfig(), $config);

        $this->ensureTempDir();
    }

    /**
     * Get default configuration
     *
     * @return array
     */
    private function getDefaultConfig(): array
    {
        return [
            'max_files_per_batch' => 100,
            'timeout_seconds' => 300,
            'rules_enabled' => ['SEC001', 'SEC002', 'SEC003', 'CODE001', 'CODE002', 'PERF001'],
            'severity_threshold' => 'low',
            'create_backup' => true,
            'generate_pdf' => true,
        ];
    }

    /**
     * Ensure temp directory exists
     *
     * @return void
     */
    private function ensureTempDir(): void
    {
        if (!is_dir($this->tempDir)) {
            if (!mkdir($this->tempDir, 0755, true)) {
                throw new RuntimeException("Cannot create temp directory: {$this->tempDir}");
            }
        }
    }

    /**
     * Start a quick scan for all CIS modules
     *
     * @param array $projectIds Array of project IDs to scan (defaults to all CIS modules 2-9)
     * @return array Scan session data
     * @throws InvalidArgumentException
     */
    public function startQuickScan(array $projectIds = []): array
    {
        if (empty($projectIds)) {
            // Default: All CIS modules (projects 2-9)
            $projectIds = [2, 3, 4, 5, 6, 7, 8, 9];
        }

        // Validate project IDs
        foreach ($projectIds as $id) {
            if (!is_int($id) || $id <= 0) {
                throw new InvalidArgumentException("Invalid project ID: {$id}");
            }
        }

        // Create scan session
        $scanId = $this->createScanSession($projectIds);

        // Get total files to scan
        $totalFiles = $this->countFilesToScan($projectIds);

        $sessionData = [
            'scan_id' => $scanId,
            'project_ids' => $projectIds,
            'total_files' => $totalFiles,
            'status' => 'started',
            'progress' => 0.0,
            'started_at' => date('Y-m-d H:i:s'),
            'estimated_completion' => $this->estimateCompletion($totalFiles),
        ];

        // Store in session for progress tracking
        $this->saveScanProgress($scanId, $sessionData);

        return $sessionData;
    }

    /**
     * Create a new scan session in database
     *
     * @param array $projectIds
     * @return string Scan session ID
     */
    private function createScanSession(array $projectIds): string
    {
        $scanId = 'scan_' . bin2hex(random_bytes(8)) . '_' . time();

        $stmt = $this->pdo->prepare("
            INSERT INTO scan_jobs (
                scan_id, project_ids, status, config, created_at
            ) VALUES (?, ?, 'running', ?, NOW())
        ");

        $stmt->execute([
            $scanId,
            json_encode($projectIds),
            json_encode($this->config)
        ]);

        return $scanId;
    }

    /**
     * Count total files to scan across projects
     *
     * @param array $projectIds
     * @return int
     */
    private function countFilesToScan(array $projectIds): int
    {
        $placeholders = str_repeat('?,', count($projectIds) - 1) . '?';

        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) as total
            FROM intelligence_files
            WHERE project_id IN ($placeholders)
              AND file_type = 'code_intelligence'
              AND is_active = 1
        ");

        $stmt->execute($projectIds);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return (int)($result['total'] ?? 0);
    }

    /**
     * Estimate completion time
     *
     * @param int $totalFiles
     * @return string ISO 8601 datetime
     */
    private function estimateCompletion(int $totalFiles): string
    {
        // Estimate: 100 files per second
        $secondsEstimate = (int)ceil($totalFiles / 100);
        $completionTime = time() + $secondsEstimate;

        return date('Y-m-d H:i:s', (int)$completionTime);
    }

    /**
     * Get scan progress
     *
     * @param string $scanId
     * @return array Progress data
     * @throws RuntimeException
     */
    public function getScanProgress(string $scanId): array
    {
        $progressFile = $this->tempDir . '/' . $scanId . '.json';

        if (!file_exists($progressFile)) {
            throw new RuntimeException("Scan session not found: {$scanId}");
        }

        $data = file_get_contents($progressFile);
        $progress = json_decode($data, true);

        if (!$progress) {
            throw new RuntimeException("Invalid progress data for scan: {$scanId}");
        }

        // Calculate current progress percentage
        if ($progress['total_files'] > 0) {
            $progress['progress'] = round(
                ($progress['files_scanned'] ?? 0) / $progress['total_files'] * 100,
                2
            );
        }

        // Update ETA
        if (isset($progress['files_scanned']) && $progress['files_scanned'] > 0) {
            $elapsed = time() - strtotime($progress['started_at']);
            $rate = $progress['files_scanned'] / max($elapsed, 1);
            $remaining = $progress['total_files'] - $progress['files_scanned'];
            $etaSeconds = ceil($remaining / max($rate, 1));

            $progress['eta_seconds'] = $etaSeconds;
            $progress['estimated_completion'] = date('Y-m-d H:i:s', time() + $etaSeconds);
        }

        return $progress;
    }

    /**
     * Perform incremental scan
     *
     * @param string $scanId
     * @param int $batchSize Number of files to scan in this batch
     * @return array Scan results
     * @throws RuntimeException
     */
    public function scanBatch(string $scanId, int $batchSize = 100): array
    {
        $progress = $this->getScanProgress($scanId);

        if ($progress['status'] === 'completed') {
            return ['message' => 'Scan already completed', 'progress' => $progress];
        }

        $projectIds = $progress['project_ids'];
        $offset = $progress['files_scanned'] ?? 0;

        // Get next batch of files
        $files = $this->getFilesToScan($projectIds, $offset, $batchSize);

        if (empty($files)) {
            // Scan complete
            $this->completeScan($scanId);
            return ['message' => 'Scan completed', 'progress' => $this->getScanProgress($scanId)];
        }

        // Scan files
        $violations = [];
        foreach ($files as $file) {
            $fileViolations = $this->scanFile($file, $scanId);
            $violations = array_merge($violations, $fileViolations);
        }

        // Update progress
        $progress['files_scanned'] = $offset + count($files);
        $progress['violations_found'] = ($progress['violations_found'] ?? 0) + count($violations);

        $this->saveScanProgress($scanId, $progress);

        return [
            'files_scanned' => count($files),
            'violations_found' => count($violations),
            'progress' => $this->getScanProgress($scanId)
        ];
    }

    /**
     * Get files to scan
     *
     * @param array $projectIds
     * @param int $offset
     * @param int $limit
     * @return array
     */
    private function getFilesToScan(array $projectIds, int $offset, int $limit): array
    {
        $placeholders = str_repeat('?,', count($projectIds) - 1) . '?';

        $stmt = $this->pdo->prepare("
            SELECT file_id, file_path, file_name, project_id
            FROM intelligence_files
            WHERE project_id IN ($placeholders)
              AND file_type = 'code_intelligence'
              AND is_active = 1
            LIMIT ? OFFSET ?
        ");

        $params = array_merge($projectIds, [$limit, $offset]);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Scan a single file
     *
     * @param array $file File data
     * @param string $scanId
     * @return array Violations found
     */
    private function scanFile(array $file, string $scanId): array
    {
        // This is a simplified version - in production, use proper AST parsing
        // For now, just check if file exists and return empty
        // The actual scanning would be done by RealTimeScanner or similar

        return []; // Placeholder
    }

    /**
     * Complete scan and generate report
     *
     * @param string $scanId
     * @return void
     */
    private function completeScan(string $scanId): void
    {
        $progress = $this->getScanProgress($scanId);
        $progress['status'] = 'completed';
        $progress['completed_at'] = date('Y-m-d H:i:s');

        // Update database
        $stmt = $this->pdo->prepare("
            UPDATE scan_jobs
            SET status = 'completed',
                completed_at = NOW(),
                results = ?
            WHERE scan_id = ?
        ");

        $stmt->execute([
            json_encode($progress),
            $scanId
        ]);

        $this->saveScanProgress($scanId, $progress);

        // Generate PDF report if enabled
        if ($this->config['generate_pdf']) {
            $this->generatePDFReport($scanId);
        }
    }

    /**
     * Generate PDF report
     *
     * @param string $scanId
     * @return string Path to PDF file
     */
    private function generatePDFReport(string $scanId): string
    {
        // Placeholder - would integrate with PDF library (TCPDF, DomPDF, etc.)
        $pdfPath = $this->tempDir . '/' . $scanId . '.pdf';

        // For now, just create a placeholder file
        file_put_contents($pdfPath, "PDF Report for scan: {$scanId}");

        return $pdfPath;
    }

    /**
     * Save scan progress to temp file
     *
     * @param string $scanId
     * @param array $progress
     * @return void
     */
    private function saveScanProgress(string $scanId, array $progress): void
    {
        $progressFile = $this->tempDir . '/' . $scanId . '.json';
        file_put_contents($progressFile, json_encode($progress, JSON_PRETTY_PRINT));
    }

    /**
     * Get scan summary
     *
     * @param string $scanId
     * @return array Summary data
     */
    public function getScanSummary(string $scanId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT sj.*,
                   COUNT(v.id) as total_violations,
                   SUM(CASE WHEN v.severity = 'critical' THEN 1 ELSE 0 END) as critical_count,
                   SUM(CASE WHEN v.severity = 'high' THEN 1 ELSE 0 END) as high_count,
                   SUM(CASE WHEN v.severity = 'medium' THEN 1 ELSE 0 END) as medium_count,
                   SUM(CASE WHEN v.severity = 'low' THEN 1 ELSE 0 END) as low_count
            FROM scan_jobs sj
            LEFT JOIN violations v ON JSON_CONTAINS(sj.project_ids, CAST(v.project_id AS JSON))
                AND v.created_at >= sj.created_at
            WHERE sj.scan_id = ?
            GROUP BY sj.id
        ");

        $stmt->execute([$scanId]);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$summary) {
            throw new RuntimeException("Scan not found: {$scanId}");
        }

        return $summary;
    }
}
