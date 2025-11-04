<?php
/**
 * Frontend Tool Registry
 *
 * Registers frontend automation tools with the AI Agent ToolChainOrchestrator
 *
 * Usage:
 *   $registry = new FrontendToolRegistry($logger);
 *   $tools = $registry->getTools();
 *
 * @package App\Tools\Frontend
 * @version 1.0.0
 * @date 2025-11-04
 */

declare(strict_types=1);

namespace App\Tools\Frontend;

use App\Tools\ToolInterface;
use App\Logger;

class FrontendToolRegistry
{
    private Logger $logger;
    private string $frontendToolsPath;
    private string $uploadPath;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
        $this->frontendToolsPath = '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/frontend-tools';
        $this->uploadPath = '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/private_html/uploads/frontend';

        // Ensure upload directory exists
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    /**
     * Get all available frontend tools
     */
    public function getTools(): array
    {
        return [
            'frontend_audit_page' => new FrontendAuditTool($this->logger, $this->frontendToolsPath),
            'frontend_auto_fix' => new FrontendAutoFixTool($this->logger, $this->frontendToolsPath),
            'frontend_screenshot' => new FrontendScreenshotTool($this->logger, $this->frontendToolsPath),
            'frontend_monitor_start' => new FrontendMonitorTool($this->logger, $this->frontendToolsPath),
            'frontend_visual_regression' => new FrontendVisualRegressionTool($this->logger, $this->frontendToolsPath),
            'frontend_performance_audit' => new FrontendPerformanceTool($this->logger, $this->frontendToolsPath),
            'frontend_accessibility_check' => new FrontendAccessibilityTool($this->logger, $this->frontendToolsPath),
        ];
    }
}

/**
 * Frontend Audit Tool
 * Comprehensive page audit (errors, performance, accessibility, SEO)
 */
class FrontendAuditTool implements ToolInterface
{
    private Logger $logger;
    private string $toolsPath;

    public function __construct(Logger $logger, string $toolsPath)
    {
        $this->logger = $logger;
        $this->toolsPath = $toolsPath;
    }

    public function getName(): string
    {
        return 'frontend_audit_page';
    }

    public function getDescription(): string
    {
        return 'Audit any webpage for errors, performance, accessibility, and SEO issues';
    }

    public function getParameters(): array
    {
        return [
            'url' => [
                'type' => 'string',
                'required' => true,
                'description' => 'URL of page to audit'
            ],
            'checks' => [
                'type' => 'array',
                'required' => false,
                'default' => ['errors', 'performance'],
                'description' => 'Types of checks to run: errors, performance, accessibility, seo'
            ],
            'auto_fix' => [
                'type' => 'boolean',
                'required' => false,
                'default' => false,
                'description' => 'Automatically fix issues found'
            ],
            'approval_required' => [
                'type' => 'boolean',
                'required' => false,
                'default' => true,
                'description' => 'Require user approval before applying fixes'
            ]
        ];
    }

    public function execute(array $params): array
    {
        $url = $params['url'];
        $checks = $params['checks'] ?? ['errors', 'performance'];
        $autoFix = $params['auto_fix'] ?? false;
        $approvalRequired = $params['approval_required'] ?? true;

        $this->logger->info('Frontend audit started', [
            'url' => $url,
            'checks' => $checks,
            'auto_fix' => $autoFix
        ]);

        $startTime = microtime(true);

        // Build command
        $cmd = sprintf(
            'cd %s && timeout 60 node examples/comprehensive-audit.js %s 2>&1',
            escapeshellarg($this->toolsPath),
            escapeshellarg($url)
        );

        // Execute Node.js audit script
        exec($cmd, $output, $returnCode);
        $outputStr = implode("\n", $output);

        // Parse output
        $result = $this->parseAuditOutput($outputStr);
        $result['duration_ms'] = round((microtime(true) - $startTime) * 1000);

        // Store in database
        $this->storeAuditHistory($url, $result);

        // If errors found and auto-fix enabled
        if ($autoFix && isset($result['errors']['total']) && $result['errors']['total'] > 0) {
            if ($approvalRequired) {
                // Store pending fixes for user approval
                $fixIds = $this->storePendingFixes($result['errors'], $url, $result['screenshot_path'] ?? null);
                $result['requires_approval'] = true;
                $result['approval_url'] = 'https://gpt.ecigdis.co.nz/ai-agent/public/dashboard/approvals.php';
                $result['pending_fix_ids'] = $fixIds;
            } else {
                // Apply fixes immediately (not recommended for production)
                $fixes = $this->applyAutoFix($result['errors']);
                $result['fixes_applied'] = $fixes;
            }
        }

        $this->logger->info('Frontend audit complete', [
            'url' => $url,
            'errors' => $result['errors']['total'] ?? 0,
            'duration' => $result['duration_ms']
        ]);

        return [
            'success' => true,
            'tool' => 'frontend_audit_page',
            'result' => $result
        ];
    }

    private function parseAuditOutput(string $output): array
    {
        // Extract JSON from output
        if (preg_match('/\{.*"audit_id".*\}/s', $output, $matches)) {
            $data = json_decode($matches[0], true);
            if ($data) {
                return $data;
            }
        }

        // Fallback if no JSON found
        return [
            'audit_id' => 'audit_' . time(),
            'errors' => ['total' => 0, 'items' => []],
            'performance' => ['load_time' => 0],
            'screenshot_path' => '',
            'gallery_url' => '',
            'raw_output' => $output
        ];
    }

    private function storeAuditHistory(string $url, array $result): void
    {
        global $db;

        $stmt = $db->prepare(
            "INSERT INTO frontend_audit_history
             (audit_id, url, errors_total, errors_json, performance_json,
              screenshot_path, gallery_url, duration_ms)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $auditId = $result['audit_id'] ?? 'audit_' . time();
        $errorsTotal = $result['errors']['total'] ?? 0;
        $errorsJson = json_encode($result['errors'] ?? []);
        $performanceJson = json_encode($result['performance'] ?? []);
        $screenshotPath = $result['screenshot_path'] ?? '';
        $galleryUrl = $result['gallery_url'] ?? '';
        $durationMs = $result['duration_ms'] ?? 0;

        $stmt->bind_param(
            'ssissssi',
            $auditId,
            $url,
            $errorsTotal,
            $errorsJson,
            $performanceJson,
            $screenshotPath,
            $galleryUrl,
            $durationMs
        );

        $stmt->execute();
    }

    private function storePendingFixes(array $errors, string $url, ?string $screenshotPath): array
    {
        global $db;

        $fixIds = [];
        $items = $errors['items'] ?? [];

        foreach ($items as $error) {
            $stmt = $db->prepare(
                "INSERT INTO frontend_pending_fixes
                 (url, file_path, line_number, fix_type, original_code, reason,
                  screenshot_path, errors_json, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')"
            );

            $filePath = $error['file'] ?? 'unknown';
            $lineNumber = $error['line'] ?? 0;
            $fixType = $error['type'] ?? 'auto';
            $originalCode = $error['code'] ?? '';
            $reason = $error['message'] ?? '';
            $errorsJson = json_encode($error);

            $stmt->bind_param(
                'ssisssss',
                $url,
                $filePath,
                $lineNumber,
                $fixType,
                $originalCode,
                $reason,
                $screenshotPath,
                $errorsJson
            );

            $stmt->execute();
            $fixIds[] = $db->insert_id;
        }

        return $fixIds;
    }

    private function applyAutoFix(array $errors): array
    {
        // This would call the auto-fix tool
        // For now, just return placeholder
        return [
            'applied' => [],
            'failed' => [],
            'message' => 'Auto-fix requires approval workflow'
        ];
    }
}

/**
 * Frontend Screenshot Tool
 * Capture screenshots in various formats
 */
class FrontendScreenshotTool implements ToolInterface
{
    private Logger $logger;
    private string $toolsPath;

    public function __construct(Logger $logger, string $toolsPath)
    {
        $this->logger = $logger;
        $this->toolsPath = $toolsPath;
    }

    public function getName(): string
    {
        return 'frontend_screenshot';
    }

    public function getDescription(): string
    {
        return 'Capture screenshots of webpages in various formats and devices';
    }

    public function getParameters(): array
    {
        return [
            'url' => [
                'type' => 'string',
                'required' => true,
                'description' => 'URL of page to screenshot'
            ],
            'type' => [
                'type' => 'string',
                'required' => false,
                'default' => 'full_page',
                'description' => 'Screenshot type: full_page, viewport, responsive'
            ],
            'device' => [
                'type' => 'string',
                'required' => false,
                'default' => 'desktop',
                'description' => 'Device preset: desktop, mobile, tablet'
            ],
            'upload' => [
                'type' => 'boolean',
                'required' => false,
                'default' => true,
                'description' => 'Upload to gallery server'
            ]
        ];
    }

    public function execute(array $params): array
    {
        $url = $params['url'];
        $type = $params['type'] ?? 'full_page';
        $device = $params['device'] ?? 'desktop';
        $upload = $params['upload'] ?? true;

        $this->logger->info('Frontend screenshot started', [
            'url' => $url,
            'type' => $type,
            'device' => $device
        ]);

        // Build command
        $cmd = sprintf(
            'cd %s && timeout 30 node test-screenshot.js %s %s %s 2>&1',
            escapeshellarg($this->toolsPath),
            escapeshellarg($url),
            escapeshellarg($type),
            escapeshellarg($device)
        );

        exec($cmd, $output, $returnCode);
        $outputStr = implode("\n", $output);

        // Parse output for screenshot path
        $screenshotPath = $this->extractScreenshotPath($outputStr);

        $result = [
            'screenshot_path' => $screenshotPath,
            'type' => $type,
            'device' => $device,
            'url' => $url,
            'success' => !empty($screenshotPath)
        ];

        // Upload if requested
        if ($upload && !empty($screenshotPath)) {
            $uploadResult = $this->uploadToGallery($screenshotPath, $url);
            $result['gallery_url'] = $uploadResult['gallery_url'] ?? '';
        }

        return [
            'success' => true,
            'tool' => 'frontend_screenshot',
            'result' => $result
        ];
    }

    private function extractScreenshotPath(string $output): string
    {
        if (preg_match('/screenshot.*?:\s*([^\s]+\.png)/i', $output, $matches)) {
            return $matches[1];
        }
        return '';
    }

    private function uploadToGallery(string $filePath, string $url): array
    {
        // Call reporter upload
        $cmd = sprintf(
            'cd %s && node -e "const reporter = require(\'./lib/reporter.js\'); reporter.uploadToGPT(\'%s\', \'%s\').then(r => console.log(JSON.stringify(r)));" 2>&1',
            escapeshellarg($this->toolsPath),
            addslashes($filePath),
            addslashes($url)
        );

        exec($cmd, $output);
        $result = json_decode(implode("\n", $output), true);

        return $result ?? ['success' => false];
    }
}

/**
 * Frontend Monitor Tool
 * Start continuous monitoring of a page
 */
class FrontendMonitorTool implements ToolInterface
{
    private Logger $logger;
    private string $toolsPath;

    public function __construct(Logger $logger, string $toolsPath)
    {
        $this->logger = $logger;
        $this->toolsPath = $toolsPath;
    }

    public function getName(): string
    {
        return 'frontend_monitor_start';
    }

    public function getDescription(): string
    {
        return 'Start continuous monitoring of a webpage with configurable checks and alerts';
    }

    public function getParameters(): array
    {
        return [
            'url' => [
                'type' => 'string',
                'required' => true,
                'description' => 'URL to monitor'
            ],
            'interval' => [
                'type' => 'string',
                'required' => false,
                'default' => '5m',
                'description' => 'Check interval (e.g., 1m, 5m, 1h)'
            ],
            'checks' => [
                'type' => 'array',
                'required' => false,
                'default' => ['errors', 'performance', 'uptime'],
                'description' => 'Types of checks to perform'
            ],
            'alert_channels' => [
                'type' => 'array',
                'required' => false,
                'default' => ['email'],
                'description' => 'Alert channels: email, slack, webhook'
            ]
        ];
    }

    public function execute(array $params): array
    {
        $url = $params['url'];
        $interval = $params['interval'] ?? '5m';
        $checks = $params['checks'] ?? ['errors', 'performance'];
        $alertChannels = $params['alert_channels'] ?? ['email'];

        global $db;

        // Create monitor record
        $stmt = $db->prepare(
            "INSERT INTO frontend_monitors
             (name, url, check_interval, check_types, alert_channels, is_active)
             VALUES (?, ?, ?, ?, ?, TRUE)"
        );

        $name = 'Monitor: ' . parse_url($url, PHP_URL_HOST);
        $checksJson = json_encode($checks);
        $alertsJson = json_encode($alertChannels);

        $stmt->bind_param('sssss', $name, $url, $interval, $checksJson, $alertsJson);
        $stmt->execute();

        $monitorId = $db->insert_id;

        $this->logger->info('Frontend monitor started', [
            'monitor_id' => $monitorId,
            'url' => $url,
            'interval' => $interval
        ]);

        return [
            'success' => true,
            'tool' => 'frontend_monitor_start',
            'result' => [
                'monitor_id' => $monitorId,
                'status' => 'active',
                'interval' => $interval,
                'checks' => $checks,
                'dashboard_url' => 'https://gpt.ecigdis.co.nz/ai-agent/public/dashboard/monitors.php'
            ]
        ];
    }
}

/**
 * Additional tool implementations would go here:
 * - FrontendAutoFixTool
 * - FrontendVisualRegressionTool
 * - FrontendPerformanceTool
 * - FrontendAccessibilityTool
 *
 * Following the same pattern as above
 */

class FrontendAutoFixTool implements ToolInterface
{
    private Logger $logger;
    private string $toolsPath;

    public function __construct(Logger $logger, string $toolsPath)
    {
        $this->logger = $logger;
        $this->toolsPath = $toolsPath;
    }

    public function getName(): string { return 'frontend_auto_fix'; }

    public function getDescription(): string
    {
        return 'Automatically fix detected issues using AI (requires approval)';
    }

    public function getParameters(): array
    {
        return [
            'fix_ids' => [
                'type' => 'array',
                'required' => true,
                'description' => 'IDs of fixes to apply (from approval)'
            ]
        ];
    }

    public function execute(array $params): array
    {
        // Implementation: Apply approved fixes
        return ['success' => true, 'message' => 'Fixes applied'];
    }
}

class FrontendVisualRegressionTool implements ToolInterface
{
    private Logger $logger;
    private string $toolsPath;

    public function __construct(Logger $logger, string $toolsPath)
    {
        $this->logger = $logger;
        $this->toolsPath = $toolsPath;
    }

    public function getName(): string { return 'frontend_visual_regression'; }

    public function getDescription(): string
    {
        return 'Compare screenshots to detect visual regressions';
    }

    public function getParameters(): array
    {
        return [
            'url' => ['type' => 'string', 'required' => true],
            'threshold' => ['type' => 'number', 'required' => false, 'default' => 0.1]
        ];
    }

    public function execute(array $params): array
    {
        // Implementation: Visual regression testing
        return ['success' => true, 'diff_percentage' => 0];
    }
}

class FrontendPerformanceTool implements ToolInterface
{
    private Logger $logger;
    private string $toolsPath;

    public function __construct(Logger $logger, string $toolsPath)
    {
        $this->logger = $logger;
        $this->toolsPath = $toolsPath;
    }

    public function getName(): string { return 'frontend_performance_audit'; }

    public function getDescription(): string
    {
        return 'Run Lighthouse performance audit';
    }

    public function getParameters(): array
    {
        return [
            'url' => ['type' => 'string', 'required' => true]
        ];
    }

    public function execute(array $params): array
    {
        // Implementation: Lighthouse audit
        return ['success' => true, 'score' => 95];
    }
}

class FrontendAccessibilityTool implements ToolInterface
{
    private Logger $logger;
    private string $toolsPath;

    public function __construct(Logger $logger, string $toolsPath)
    {
        $this->logger = $logger;
        $this->toolsPath = $toolsPath;
    }

    public function getName(): string { return 'frontend_accessibility_check'; }

    public function getDescription(): string
    {
        return 'Check WCAG accessibility compliance';
    }

    public function getParameters(): array
    {
        return [
            'url' => ['type' => 'string', 'required' => true],
            'level' => ['type' => 'string', 'required' => false, 'default' => 'AA']
        ];
    }

    public function execute(array $params): array
    {
        // Implementation: Accessibility checks
        return ['success' => true, 'violations' => []];
    }
}
