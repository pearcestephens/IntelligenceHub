#!/usr/bin/env php
<?php
/**
 * Smart Cron - Auto-Discovery Script
 * 
 * Scans the filesystem for cron job scripts and automatically registers them
 * in the integrated job management system.
 * 
 * Usage:
 *   php bin/discover-cron-jobs.php
 *   php bin/discover-cron-jobs.php --dry-run
 *   php bin/discover-cron-jobs.php --verbose
 * 
 * @package SmartCron\Bin
 */

declare(strict_types=1);

// Bootstrap - load classes without executing tasks
// Path: smart-cron/bin/discover-cron-jobs.php -> smart-cron/bootstrap.php
require_once __DIR__ . '/../bootstrap.php';

use SmartCron\Core\Config;
use SmartCron\Core\MetricsCollector;
use SmartCron\Core\IntegratedJobManager;
use SmartCron\Core\LoadBalancer;

// Parse command-line arguments
$options = getopt('', ['dry-run', 'verbose', 'help']);
$dryRun = isset($options['dry-run']);
$verbose = isset($options['verbose']);

if (isset($options['help'])) {
    echo <<<HELP

Smart Cron - Auto-Discovery Script

Automatically discovers and registers cron job scripts.

Usage:
  php bin/discover-cron-jobs.php [OPTIONS]

Options:
  --dry-run     Show what would be discovered without registering
  --verbose     Show detailed output for each script found
  --help        Show this help message

Scan Locations:
  - assets/services/queue/bin/
  - assets/services/cron/scripts/
  - assets/services/neuro/neuro_/cron_jobs/
  - assets/services/*/cron/
  - assets/services/smart-cron/bin/

HELP;
    exit(0);
}

echo "\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "  Smart Cron - Auto-Discovery Script\n";
echo "════════════════════════════════════════════════════════════════\n";
echo ($dryRun ? "  [DRY RUN MODE - No changes will be made]\n" : "");
echo "\n";

// Initialize components
$config = new Config();
$metrics = new MetricsCollector($config);
$loadBalancer = new LoadBalancer($config, $metrics);
$jobManager = new IntegratedJobManager($config, $metrics, $loadBalancer);

// Define scan locations - comprehensive scan of all cron job directories
$servicesPath = dirname(__DIR__, 3); // /assets/services
$scanLocations = [
    'Services Scripts' => $servicesPath . '/scripts',
    'Cron Scripts' => $servicesPath . '/cron/scripts',
    'Queue Workers' => $servicesPath . '/queue/bin',
    'Queue Cron Jobs' => $servicesPath . '/queue/bin/cron',
    'Neuro Cron Jobs' => $servicesPath . '/neuro/neuro_/cron_jobs',
    'AI Agent Scripts' => $servicesPath . '/ai-agent/scripts',
    'Transfer Engine Scripts' => $servicesPath . '/neuro/neuro_/vapeshed_transfer/transfer_engine/scripts',
    'Smart Cron Jobs' => dirname(__DIR__) . '/bin'
];

// Base path for relative path calculation
$basePath = $servicesPath;

// Statistics
$stats = [
    'found' => 0,
    'registered' => 0,
    'updated' => 0,
    'skipped' => 0,
    'errors' => 0
];

// Scan each location
foreach ($scanLocations as $locationName => $path) {
    if (!is_dir($path)) {
        if ($verbose) {
            echo "⚠️  {$locationName}: Path not found ({$path})\n";
        }
        continue;
    }
    
    echo "\n📂 Scanning: {$locationName}\n";
    echo "   Path: {$path}\n";
    
    $scripts = scanDirectory($path);
    
    foreach ($scripts as $script) {
        $stats['found']++;
        
        try {
            $jobData = analyzeScript($script, $basePath);
            
            if ($verbose) {
                echo "\n   📄 {$jobData['job_name']}\n";
                echo "      Type: {$jobData['script_type']}\n";
                echo "      Category: {$jobData['job_category']}\n";
                echo "      Priority: {$jobData['priority']}\n";
                echo "      Memory Weight: {$jobData['memory_weight']}\n";
            }
            
            if (!$dryRun) {
                // Check if already registered
                $existing = $jobManager->getJobByName($jobData['job_name']);
                
                if ($existing && $existing['auto_discovered']) {
                    // Update existing auto-discovered job
                    $jobManager->registerJob($jobData);
                    $stats['updated']++;
                    
                    if ($verbose) {
                        echo "      ✅ Updated\n";
                    } else {
                        echo "   ✅ {$jobData['job_name']} (updated)\n";
                    }
                } elseif ($existing) {
                    // Skip manually configured jobs
                    $stats['skipped']++;
                    
                    if ($verbose) {
                        echo "      ⏭️  Skipped (manually configured)\n";
                    }
                } else {
                    // Register new job
                    $jobManager->registerJob($jobData);
                    $stats['registered']++;
                    
                    if ($verbose) {
                        echo "      ✅ Registered\n";
                    } else {
                        echo "   ✅ {$jobData['job_name']}\n";
                    }
                }
            } else {
                echo "   🔍 Would register: {$jobData['job_name']}\n";
            }
            
        } catch (\Exception $e) {
            $stats['errors']++;
            echo "   ❌ Error: " . basename($script) . " - " . $e->getMessage() . "\n";
        }
    }
}

// Print summary
echo "\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "  Discovery Summary\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "\n";
echo "  📊 Scripts Found:     {$stats['found']}\n";

if (!$dryRun) {
    echo "  ✅ Newly Registered:  {$stats['registered']}\n";
    echo "  🔄 Updated:           {$stats['updated']}\n";
    echo "  ⏭️  Skipped:          {$stats['skipped']}\n";
    echo "  ❌ Errors:            {$stats['errors']}\n";
} else {
    echo "  🔍 Would Register:    " . ($stats['found'] - $stats['errors']) . "\n";
    echo "  ❌ Errors:            {$stats['errors']}\n";
}

echo "\n";

if (!$dryRun && ($stats['registered'] > 0 || $stats['updated'] > 0)) {
    echo "✅ Jobs successfully registered/updated!\n";
    echo "\n";
    echo "Next Steps:\n";
    echo "  1. Review jobs in dashboard: dashboard.php\n";
    echo "  2. Configure schedules for jobs with schedule_type='manual'\n";
    echo "  3. Enable/disable jobs as needed\n";
    echo "  4. Monitor performance baselines (calculated after 10 executions)\n";
    echo "\n";
} elseif ($dryRun) {
    echo "ℹ️  Run without --dry-run to register these jobs.\n";
    echo "\n";
}

exit($stats['errors'] > 0 ? 1 : 0);

// ============================================================================
// Helper Functions
// ============================================================================

/**
 * Scan directory for executable scripts
 */
function scanDirectory(string $path): array
{
    $scripts = [];
    
    if (!is_dir($path)) {
        return $scripts;
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }
        
        $filename = $file->getFilename();
        $extension = $file->getExtension();
        
        // Skip hidden files, backups, and non-executable types
        if (str_starts_with($filename, '.') || 
            str_ends_with($filename, '.bak') || 
            str_ends_with($filename, '~')) {
            continue;
        }
        
        // Check if it's an executable script
        if (in_array($extension, ['php', 'sh', 'py', 'js', 'pl']) || 
            is_executable($file->getPathname())) {
            $scripts[] = $file->getPathname();
        }
    }
    
    return $scripts;
}

/**
 * Analyze script and generate job data
 */
function analyzeScript(string $scriptPath, string $basePath): array
{
    $filename = basename($scriptPath);
    $extension = pathinfo($scriptPath, PATHINFO_EXTENSION);
    $relativePath = str_replace($basePath . '/', '', $scriptPath);
    
    // Detect script type
    $scriptType = detectScriptType($scriptPath, $extension);
    
    // Generate job name from filename
    $jobName = preg_replace('/[^a-z0-9_-]/', '_', strtolower(pathinfo($filename, PATHINFO_FILENAME)));
    $jobName = 'auto_' . $jobName;
    
    // Analyze script content for hints
    $content = file_get_contents($scriptPath);
    $analysis = analyzeContent($content, $scriptType);
    
    // Determine job category
    $category = determineCategory($scriptPath, $filename, $analysis);
    
    // Determine priority
    $priority = determinePriority($filename, $analysis);
    
    // Estimate resource requirements
    $resources = estimateResources($scriptPath, $content, $analysis);
    
    // Build job data
    $jobData = [
        'job_name' => $jobName,
        'job_category' => $category,
        'description' => "Auto-discovered: {$filename}" . ($analysis['description'] ? " - {$analysis['description']}" : ""),
        'script_path' => $scriptPath,
        'script_type' => $scriptType,
        'script_args' => $analysis['default_args'],
        'working_directory' => dirname($scriptPath),
        'schedule_type' => $analysis['schedule_type'] ?? 'manual',
        'cron_expression' => $analysis['cron_expression'] ?? null,
        'interval_seconds' => $analysis['interval_seconds'] ?? null,
        'timeout_seconds' => $resources['timeout'],
        'memory_limit_mb' => $resources['memory_limit'],
        'priority' => $priority,
        'cpu_weight' => $resources['cpu_weight'],
        'memory_weight' => $resources['memory_weight'],
        'can_run_during_peak' => $priority !== 'critical',
        'enabled' => false, // Disabled by default - user must enable
        'status' => 'testing',
        'auto_discovered' => true,
        'created_by' => 'auto_discovery'
    ];
    
    return $jobData;
}

/**
 * Detect script type from shebang or extension
 */
function detectScriptType(string $path, string $extension): string
{
    // Try to read shebang
    $handle = fopen($path, 'r');
    if ($handle) {
        $firstLine = fgets($handle);
        fclose($handle);
        
        if (preg_match('/^#!.*\/php/', $firstLine)) {
            return 'php';
        } elseif (preg_match('/^#!.*\/bash/', $firstLine)) {
            return 'bash';
        } elseif (preg_match('/^#!.*\/python/', $firstLine)) {
            return 'python';
        } elseif (preg_match('/^#!.*\/node/', $firstLine)) {
            return 'node';
        }
    }
    
    // Fall back to extension
    return match($extension) {
        'php' => 'php',
        'sh' => 'bash',
        'py' => 'python',
        'js' => 'node',
        'pl' => 'perl',
        default => 'bash'
    };
}

/**
 * Analyze script content for metadata - DEEP ANALYSIS VERSION
 */
function analyzeContent(string $content, string $scriptType): array
{
    $analysis = [
        'description' => null,
        'schedule_type' => 'manual',
        'cron_expression' => null,
        'interval_seconds' => null,
        'default_args' => null,
        'keywords' => [],
        'requires_db' => false,
        'requires_vend_api' => false,
        'requires_external_api' => false,
        'is_long_running' => false,
        'is_critical' => false,
        'dependencies' => []
    ];
    
    // Extract description from PHPDoc or comments (first 500 chars)
    $contentStart = substr($content, 0, 2000);
    
    // Try PHPDoc format: /** ... */
    if (preg_match('/\/\*\*\s*\n\s*\*\s*(.+?)(?:\n\s*\*\s*@|\*\/)/s', $contentStart, $matches)) {
        $analysis['description'] = trim($matches[1]);
        $analysis['description'] = preg_replace('/\s*\n\s*\*\s*/', ' ', $analysis['description']);
        $analysis['description'] = substr($analysis['description'], 0, 200);
    }
    // Try single-line comment at top
    elseif (preg_match('/^<\?php\s*\n\s*\/\/\s*(.+)/m', $contentStart, $matches)) {
        $analysis['description'] = trim($matches[1]);
        $analysis['description'] = substr($analysis['description'], 0, 200);
    }
    // Try # comment for bash scripts
    elseif (preg_match('/^#\s*Description:\s*(.+)/mi', $contentStart, $matches)) {
        $analysis['description'] = trim($matches[1]);
    }
    
    // Detect schedule hints from comments
    // Pattern: @cron 0 * * * * or Schedule: every 5 minutes
    if (preg_match('/@cron\s+([\d\s\*\/,-]+)/i', $content, $matches)) {
        $analysis['schedule_type'] = 'cron';
        $analysis['cron_expression'] = trim($matches[1]);
    }
    elseif (preg_match('/Schedule:\s*every\s+(\d+)\s*(second|minute|hour|day)/i', $content, $matches)) {
        $analysis['schedule_type'] = 'interval';
        $multiplier = match(strtolower($matches[2])) {
            'second' => 1,
            'minute' => 60,
            'hour' => 3600,
            'day' => 86400,
            default => 60
        };
        $analysis['interval_seconds'] = (int)$matches[1] * $multiplier;
    }
    elseif (preg_match('/Run\s*every:\s*(\d+)\s*(s|sec|m|min|h|hour)/i', $content, $matches)) {
        $analysis['schedule_type'] = 'interval';
        $multiplier = match(strtolower($matches[2])) {
            's', 'sec' => 1,
            'm', 'min' => 60,
            'h', 'hour' => 3600,
            default => 60
        };
        $analysis['interval_seconds'] = (int)$matches[1] * $multiplier;
    }
    
    // Detect database usage
    if (preg_match('/mysqli|PDO|->query\(|->prepare\(|getDb|database/i', $content)) {
        $analysis['requires_db'] = true;
    }
    
    // Detect Vend/Lightspeed API usage
    if (preg_match('/vend|lightspeed|retailer\.myshopify|\/api\/2\.0/i', $content)) {
        $analysis['requires_vend_api'] = true;
    }
    
    // Detect other external APIs
    if (preg_match('/curl_|file_get_contents.*http|GuzzleHttp|HttpClient/i', $content)) {
        $analysis['requires_external_api'] = true;
    }
    
    // Detect long-running operations
    if (preg_match('/set_time_limit\(0\)|ini_set.*max_execution_time.*[3-9]\d{2,}/i', $content)) {
        $analysis['is_long_running'] = true;
    }
    
    // Detect critical/emergency keywords
    if (preg_match('/critical|emergency|urgent|failsafe|recovery|killer/i', $content)) {
        $analysis['is_critical'] = true;
    }
    
    // Extract keywords from content (for better categorization)
    $keywords = [];
    
    // Check for common operation types
    if (preg_match_all('/\b(backup|archive|export|import|sync|synchronize|transfer|migrate|cleanup|purge|optimize|analyze|monitor|watch|alert|notify|report|aggregate|calculate|process|validate|verify|test|check|health|status|generate|create|update|delete|remove|repair|fix|recover|restart|kill|stop|start|deploy|install|configure|setup|maintain|refresh|rebuild|reindex|compress|decompress|encrypt|decrypt|scan|discover|collect|fetch|pull|push|send|receive|email|sms|webhook|queue|worker|job|task|cron|schedule|daily|hourly|weekly|monthly)\b/i', $content, $matches)) {
        $keywords = array_unique(array_map('strtolower', $matches[0]));
        $analysis['keywords'] = $keywords;
    }
    
    // Detect dependencies (require/include statements)
    if (preg_match_all('/(?:require|include)(?:_once)?\s*[\'"]([^\'"]+)[\'"]/i', $content, $matches)) {
        $analysis['dependencies'] = array_unique($matches[1]);
    }
    
    // Extract description from comments
    if (preg_match('/(?:\/\*\*|#)\s*(.+?)(?:\*\/|\n)/s', $content, $matches)) {
        $desc = trim($matches[1]);
        $desc = preg_replace('/\s*\*\s*/', ' ', $desc);
        $analysis['description'] = substr($desc, 0, 200);
    }
    
    // Look for cron expression in comments
    if (preg_match('/(?:cron|schedule):\s*([0-9*\/,-]+\s+[0-9*\/,-]+\s+[0-9*\/,-]+\s+[0-9*\/,-]+\s+[0-9*\/,-]+)/i', $content, $matches)) {
        $analysis['schedule_type'] = 'cron';
        $analysis['cron_expression'] = $matches[1];
    }
    
    // Look for interval in comments
    if (preg_match('/(?:interval|every):\s*(\d+)\s*(seconds?|minutes?|hours?)/i', $content, $matches)) {
        $analysis['schedule_type'] = 'interval';
        $value = (int)$matches[1];
        $unit = strtolower($matches[2]);
        
        $analysis['interval_seconds'] = match(true) {
            str_starts_with($unit, 'minute') => $value * 60,
            str_starts_with($unit, 'hour') => $value * 3600,
            default => $value
        };
    }
    
    // Extract keywords for categorization
    $keywords = [];
    if (preg_match_all('/\b(backup|report|sync|cleanup|migration|import|export|email|notification|queue|process|analyze|monitor|health|check|update|batch|cron)\b/i', $content, $matches)) {
        $keywords = array_unique(array_map('strtolower', $matches[0]));
    }
    $analysis['keywords'] = $keywords;
    
    return $analysis;
}

/**
 * Determine job category based on path and content
 */
function determineCategory(string $path, string $filename, array $analysis): string
{
    $keywords = $analysis['keywords'];
    $lower = strtolower($filename . ' ' . $path);
    
    // Check keywords first
    if (in_array('backup', $keywords)) return 'backup';
    if (in_array('report', $keywords)) return 'reporting';
    if (in_array('sync', $keywords)) return 'sync';
    if (in_array('cleanup', $keywords)) return 'cleanup';
    if (in_array('monitor', $keywords) || in_array('health', $keywords)) return 'monitoring';
    
    // Check path/filename
    if (strpos($lower, 'queue') !== false) return 'business';
    if (strpos($lower, 'backup') !== false) return 'backup';
    if (strpos($lower, 'report') !== false) return 'reporting';
    if (strpos($lower, 'sync') !== false) return 'sync';
    if (strpos($lower, 'clean') !== false) return 'cleanup';
    if (strpos($lower, 'maintain') !== false) return 'maintenance';
    if (strpos($lower, 'monitor') !== false) return 'monitoring';
    
    return 'business'; // Default
}

/**
 * Determine priority based on filename and content
 */
function determinePriority(string $filename, array $analysis): string
{
    $keywords = $analysis['keywords'];
    $lower = strtolower($filename);
    
    // Critical indicators
    if (strpos($lower, 'critical') !== false || 
        strpos($lower, 'emergency') !== false ||
        in_array('health', $keywords)) {
        return 'critical';
    }
    
    // High priority indicators
    if (strpos($lower, 'backup') !== false ||
        strpos($lower, 'sync') !== false ||
        in_array('backup', $keywords) ||
        in_array('sync', $keywords)) {
        return 'high';
    }
    
    // Low priority indicators
    if (strpos($lower, 'cleanup') !== false ||
        strpos($lower, 'archive') !== false ||
        in_array('cleanup', $keywords)) {
        return 'low';
    }
    
    return 'medium'; // Default
}

/**
 * Estimate resource requirements
 */
function estimateResources(string $path, string $content, array $analysis): array
{
    $fileSize = filesize($path);
    $lineCount = substr_count($content, "\n");
    $keywords = $analysis['keywords'];
    
    // Default resources
    $timeout = 300; // 5 minutes
    $memoryLimit = 512; // MB
    $cpuWeight = 1.0;
    $memoryWeight = 1.0;
    
    // Adjust based on keywords
    if (in_array('backup', $keywords) || in_array('export', $keywords)) {
        $timeout = 1800; // 30 minutes
        $memoryLimit = 1024; // 1 GB
        $cpuWeight = 1.5;
        $memoryWeight = 1.8;
    }
    
    if (in_array('import', $keywords) || in_array('migration', $keywords)) {
        $timeout = 3600; // 1 hour
        $memoryLimit = 2048; // 2 GB
        $cpuWeight = 1.8;
        $memoryWeight = 2.0;
    }
    
    if (in_array('report', $keywords) || in_array('analyze', $keywords)) {
        $timeout = 900; // 15 minutes
        $memoryLimit = 768; // 768 MB
        $cpuWeight = 1.2;
        $memoryWeight = 1.3;
    }
    
    if (in_array('cleanup', $keywords) || in_array('monitor', $keywords)) {
        $timeout = 180; // 3 minutes
        $memoryLimit = 256; // 256 MB
        $cpuWeight = 0.5;
        $memoryWeight = 0.5;
    }
    
    // Adjust based on script size (larger = more complex)
    if ($lineCount > 1000) {
        $timeout *= 1.5;
        $memoryLimit = (int)($memoryLimit * 1.3);
    }
    
    return [
        'timeout' => $timeout,
        'memory_limit' => $memoryLimit,
        'cpu_weight' => $cpuWeight,
        'memory_weight' => $memoryWeight
    ];
}
