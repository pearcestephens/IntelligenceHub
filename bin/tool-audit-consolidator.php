#!/usr/bin/env php
<?php
/**
 * 🔍 Tool Audit & Consolidation System
 *
 * MISSION: Ensure we never lose valuable tools, scripts, or capabilities
 *
 * This script:
 * 1. Discovers ALL tools across all directories
 * 2. Catalogs their capabilities
 * 3. Identifies duplicates and gaps
 * 4. Suggests consolidation opportunities
 * 5. Generates integration checklist
 * 6. Creates migration plan
 *
 * Usage:
 *   php bin/tool-audit-consolidator.php
 *   php bin/tool-audit-consolidator.php --auto-integrate
 *   php bin/tool-audit-consolidator.php --report-only
 *
 * @author Intelligence Hub
 * @version 2.0.0
 * @date 2025-10-29
 */

declare(strict_types=1);

// Configuration
$config = [
    'base_dir' => '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html',
    'scan_paths' => [
        'ai-agent/src/Tools/',
        'ai-agent/bin/',
        'mcp/src/Tools/',
        'mcp/tools_impl.php',
        'mcp/advanced_tools.php',
        '_automation/',
        '_dev-tools/',
        'frontend-tools/',
        'api/',
        'services/',
    ],
    'output_dir' => '_kb/audits',
    'registry_path' => 'ai-agent/src/Tools/ToolRegistry.php',
];

// Parse command line options
$options = getopt('', ['auto-integrate', 'report-only', 'help']);

if (isset($options['help'])) {
    showHelp();
    exit(0);
}

// Initialize
echo "\n🔍 TOOL AUDIT & CONSOLIDATION SYSTEM\n";
echo str_repeat("=", 60) . "\n\n";

// Step 1: Discover all tools
echo "📊 PHASE 1: Discovery\n";
echo str_repeat("-", 60) . "\n";

$discovered = discoverAllTools($config);
echo sprintf("✅ Found %d tools across %d locations\n\n",
    count($discovered['all']),
    count($discovered['by_location'])
);

// Step 2: Categorize tools
echo "🏷️  PHASE 2: Categorization\n";
echo str_repeat("-", 60) . "\n";

$categorized = categorizeTools($discovered['all']);
foreach ($categorized as $category => $tools) {
    echo sprintf("  %s: %d tools\n", ucfirst($category), count($tools));
}
echo "\n";

// Step 3: Detect duplicates
echo "🔄 PHASE 3: Duplicate Detection\n";
echo str_repeat("-", 60) . "\n";

$duplicates = detectDuplicates($discovered['all']);
if (empty($duplicates)) {
    echo "✅ No duplicates found\n\n";
} else {
    echo sprintf("⚠️  Found %d potential duplicates:\n", count($duplicates));
    foreach ($duplicates as $dup) {
        echo sprintf("  - %s (%d instances)\n", $dup['name'], count($dup['files']));
    }
    echo "\n";
}

// Step 4: Identify gaps
echo "🔍 PHASE 4: Gap Analysis\n";
echo str_repeat("-", 60) . "\n";

$gaps = identifyGaps($categorized);
if (empty($gaps)) {
    echo "✅ All capability areas covered\n\n";
} else {
    echo "📋 Recommended additions:\n";
    foreach ($gaps as $gap) {
        echo sprintf("  - %s: %s\n", $gap['category'], $gap['description']);
    }
    echo "\n";
}

// Step 5: Check ToolRegistry integration
echo "🔌 PHASE 5: Registry Integration Check\n";
echo str_repeat("-", 60) . "\n";

$registryStatus = checkRegistryIntegration($config, $discovered['all']);
echo sprintf("  Registered: %d tools\n", $registryStatus['registered']);
echo sprintf("  Missing: %d tools\n", $registryStatus['missing']);
echo sprintf("  Deprecated: %d tools\n\n", $registryStatus['deprecated']);

// Step 6: Generate reports
echo "📄 PHASE 6: Report Generation\n";
echo str_repeat("-", 60) . "\n";

$reports = generateReports($config, [
    'discovered' => $discovered,
    'categorized' => $categorized,
    'duplicates' => $duplicates,
    'gaps' => $gaps,
    'registry' => $registryStatus,
]);

echo "✅ Generated reports:\n";
foreach ($reports as $report) {
    echo sprintf("  - %s\n", $report);
}
echo "\n";

// Step 7: Auto-integrate if requested
if (isset($options['auto-integrate'])) {
    echo "🚀 PHASE 7: Auto-Integration\n";
    echo str_repeat("-", 60) . "\n";

    $integrated = autoIntegrate($config, $registryStatus['missing']);
    echo sprintf("✅ Integrated %d tools\n\n", $integrated);
}

// Final summary
echo str_repeat("=", 60) . "\n";
echo "✅ AUDIT COMPLETE\n\n";
echo "📊 Summary:\n";
echo sprintf("  Total Tools: %d\n", count($discovered['all']));
echo sprintf("  Categories: %d\n", count($categorized));
echo sprintf("  Duplicates: %d\n", count($duplicates));
echo sprintf("  Gaps: %d\n", count($gaps));
echo sprintf("  Registry Coverage: %.1f%%\n\n",
    ($registryStatus['registered'] / count($discovered['all'])) * 100
);

echo "📁 Reports saved to: {$config['output_dir']}/\n";
echo "🔗 Next: Review consolidation plan in TOOL_CONSOLIDATION_PLAN.md\n\n";

exit(0);

// ============================================================================
// FUNCTIONS
// ============================================================================

function showHelp(): void
{
    echo <<<HELP

🔍 Tool Audit & Consolidation System

Usage:
  php bin/tool-audit-consolidator.php [OPTIONS]

Options:
  --auto-integrate    Automatically integrate missing tools into registry
  --report-only       Generate reports without recommendations
  --help              Show this help message

Examples:
  php bin/tool-audit-consolidator.php
  php bin/tool-audit-consolidator.php --auto-integrate
  php bin/tool-audit-consolidator.php --report-only

HELP;
}

function discoverAllTools(array $config): array
{
    $tools = [];
    $byLocation = [];

    foreach ($config['scan_paths'] as $scanPath) {
        $fullPath = $config['base_dir'] . '/' . $scanPath;

        if (!is_dir($fullPath) && !is_file($fullPath)) {
            continue;
        }

        $found = scanDirectory($fullPath, $scanPath);
        $tools = array_merge($tools, $found);

        if (!empty($found)) {
            $byLocation[$scanPath] = $found;
        }
    }

    return [
        'all' => $tools,
        'by_location' => $byLocation,
    ];
}

function scanDirectory(string $path, string $relativePath): array
{
    $tools = [];

    if (is_file($path)) {
        $tool = analyzeFile($path, $relativePath);
        if ($tool) {
            $tools[] = $tool;
        }
        return $tools;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && preg_match('/\.(php|sh|js)$/', $file->getFilename())) {
            $relPath = str_replace($path, $relativePath, $file->getPathname());
            $tool = analyzeFile($file->getPathname(), $relPath);
            if ($tool) {
                $tools[] = $tool;
            }
        }
    }

    return $tools;
}

function analyzeFile(string $filepath, string $relativePath): ?array
{
    $content = file_get_contents($filepath);
    $filename = basename($filepath);

    // Skip if not a tool/script
    if (!preg_match('/(tool|script|util|helper|automation)/i', $filename) &&
        !preg_match('/(class|function|public function|execute|handle)/i', $content)) {
        return null;
    }

    $tool = [
        'name' => pathinfo($filename, PATHINFO_FILENAME),
        'file' => $relativePath,
        'type' => getFileType($filepath),
        'size' => filesize($filepath),
        'capabilities' => extractCapabilities($content),
        'dependencies' => extractDependencies($content),
        'class_name' => extractClassName($content),
        'methods' => extractMethods($content),
        'last_modified' => date('Y-m-d H:i:s', filemtime($filepath)),
    ];

    return $tool;
}

function getFileType(string $filepath): string
{
    $extension = pathinfo($filepath, PATHINFO_EXTENSION);

    switch ($extension) {
        case 'php':
            return 'PHP';
        case 'sh':
            return 'Shell';
        case 'js':
            return 'JavaScript';
        default:
            return 'Unknown';
    }
}

function extractCapabilities(string $content): array
{
    $capabilities = [];

    // Look for docblock descriptions
    if (preg_match('/@description\s+(.+)/i', $content, $matches)) {
        $capabilities[] = trim($matches[1]);
    }

    // Look for function purposes
    preg_match_all('/\/\*\*\s*\n\s*\*\s*(.+?)\n/s', $content, $matches);
    foreach ($matches[1] as $desc) {
        if (strlen($desc) > 10 && !str_contains($desc, '@')) {
            $capabilities[] = trim($desc);
        }
    }

    // Look for method names that indicate capabilities
    if (preg_match_all('/function\s+(\w+)/i', $content, $matches)) {
        foreach ($matches[1] as $funcName) {
            if (preg_match('/(analyze|scan|check|validate|test|monitor|deploy|execute)/i', $funcName)) {
                $capabilities[] = ucfirst(preg_replace('/([A-Z])/', ' $1', $funcName));
            }
        }
    }

    return array_unique(array_slice($capabilities, 0, 5));
}

function extractDependencies(string $content): array
{
    $deps = [];

    // PHP use statements
    preg_match_all('/use\s+([^;]+);/', $content, $matches);
    $deps = array_merge($deps, $matches[1]);

    // require/include
    preg_match_all('/(require|include)(?:_once)?\s*[\'"]([^\'"\)]+)[\'"]/', $content, $matches);
    $deps = array_merge($deps, $matches[2]);

    return array_unique(array_slice($deps, 0, 10));
}

function extractClassName(string $content): ?string
{
    if (preg_match('/class\s+(\w+)/i', $content, $matches)) {
        return $matches[1];
    }
    return null;
}

function extractMethods(string $content): array
{
    $methods = [];

    preg_match_all('/(?:public|private|protected)?\s*function\s+(\w+)/i', $content, $matches);

    return array_slice(array_unique($matches[1]), 0, 20);
}

function categorizeTools(array $tools): array
{
    $categories = [
        'ai_agent' => [],
        'mcp_server' => [],
        'automation' => [],
        'dev_tools' => [],
        'frontend' => [],
        'database' => [],
        'security' => [],
        'monitoring' => [],
        'deployment' => [],
        'testing' => [],
        'utilities' => [],
        'uncategorized' => [],
    ];

    foreach ($tools as $tool) {
        $category = detectCategory($tool);
        $categories[$category][] = $tool;
    }

    return array_filter($categories);
}

function detectCategory(array $tool): string
{
    $file = strtolower($tool['file']);
    $name = strtolower($tool['name']);

    if (str_contains($file, 'ai-agent')) return 'ai_agent';
    if (str_contains($file, 'mcp')) return 'mcp_server';
    if (str_contains($file, '_automation')) return 'automation';
    if (str_contains($file, '_dev-tools')) return 'dev_tools';
    if (str_contains($file, 'frontend-tools')) return 'frontend';
    if (str_contains($name, 'database') || str_contains($name, 'db_')) return 'database';
    if (str_contains($name, 'security') || str_contains($name, 'audit')) return 'security';
    if (str_contains($name, 'monitor') || str_contains($name, 'health')) return 'monitoring';
    if (str_contains($name, 'deploy')) return 'deployment';
    if (str_contains($name, 'test')) return 'testing';

    return 'utilities';
}

function detectDuplicates(array $tools): array
{
    $duplicates = [];
    $seen = [];

    foreach ($tools as $tool) {
        $key = $tool['name'];

        if (isset($seen[$key])) {
            if (!isset($duplicates[$key])) {
                $duplicates[$key] = [
                    'name' => $key,
                    'files' => [$seen[$key]['file']],
                ];
            }
            $duplicates[$key]['files'][] = $tool['file'];
        } else {
            $seen[$key] = $tool;
        }
    }

    return array_values($duplicates);
}

function identifyGaps(array $categorized): array
{
    $gaps = [];

    // Check for missing critical tool categories
    $criticalCategories = [
        'code_analysis' => 'Static code analysis and quality checks',
        'performance' => 'Performance profiling and optimization',
        'backup' => 'Automated backup and recovery',
        'logging' => 'Centralized logging and analysis',
        'notification' => 'Alert and notification system',
    ];

    // Check each category for tool presence
    foreach ($criticalCategories as $category => $description) {
        $hasTools = false;
        foreach ($categorized as $tools) {
            foreach ($tools as $tool) {
                if (str_contains(strtolower($tool['name']), $category)) {
                    $hasTools = true;
                    break 2;
                }
            }
        }

        if (!$hasTools) {
            $gaps[] = [
                'category' => $category,
                'description' => $description,
                'priority' => 'medium',
            ];
        }
    }

    return $gaps;
}

function checkRegistryIntegration(array $config, array $tools): array
{
    $registryPath = $config['base_dir'] . '/' . $config['registry_path'];

    if (!file_exists($registryPath)) {
        return [
            'registered' => 0,
            'missing' => $tools,
            'deprecated' => [],
        ];
    }

    $registryContent = file_get_contents($registryPath);

    $registered = [];
    $missing = [];

    foreach ($tools as $tool) {
        $toolName = strtolower($tool['name']);

        // Check if tool is mentioned in registry
        if (preg_match("/self::register\(['\"]" . preg_quote($toolName, '/') . "['\"]/i", $registryContent)) {
            $registered[] = $tool;
        } else {
            $missing[] = $tool;
        }
    }

    return [
        'registered' => count($registered),
        'missing' => $missing,
        'deprecated' => [], // TODO: Implement deprecation detection
    ];
}

function generateReports(array $config, array $data): array
{
    $outputDir = $config['base_dir'] . '/' . $config['output_dir'];

    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }

    $reports = [];

    // 1. Full tool inventory
    $inventoryPath = $outputDir . '/COMPLETE_TOOL_INVENTORY.md';
    file_put_contents($inventoryPath, generateInventoryReport($data));
    $reports[] = $inventoryPath;

    // 2. Consolidation plan
    $planPath = $outputDir . '/TOOL_CONSOLIDATION_PLAN.md';
    file_put_contents($planPath, generateConsolidationPlan($data));
    $reports[] = $planPath;

    // 3. Integration checklist
    $checklistPath = $outputDir . '/INTEGRATION_CHECKLIST.md';
    file_put_contents($checklistPath, generateIntegrationChecklist($data));
    $reports[] = $checklistPath;

    // 4. JSON data
    $jsonPath = $outputDir . '/tool_audit_data.json';
    file_put_contents($jsonPath, json_encode($data, JSON_PRETTY_PRINT));
    $reports[] = $jsonPath;

    return $reports;
}

function generateInventoryReport(array $data): string
{
    $md = "# 📦 Complete Tool Inventory\n\n";
    $md .= "**Generated:** " . date('Y-m-d H:i:s') . "\n\n";
    $md .= "---\n\n";

    $md .= "## 📊 Statistics\n\n";
    $md .= sprintf("- **Total Tools:** %d\n", count($data['discovered']['all']));
    $md .= sprintf("- **Categories:** %d\n", count($data['categorized']));
    $md .= sprintf("- **Duplicates:** %d\n", count($data['duplicates']));
    $md .= sprintf("- **Registry Coverage:** %.1f%%\n\n",
        ($data['registry']['registered'] / count($data['discovered']['all'])) * 100
    );

    $md .= "---\n\n";

    $md .= "## 🏷️  Tools by Category\n\n";

    foreach ($data['categorized'] as $category => $tools) {
        $md .= sprintf("### %s (%d tools)\n\n", ucwords(str_replace('_', ' ', $category)), count($tools));

        foreach ($tools as $tool) {
            $md .= sprintf("#### %s\n", $tool['name']);
            $md .= sprintf("- **File:** `%s`\n", $tool['file']);
            $md .= sprintf("- **Type:** %s\n", $tool['type']);
            $md .= sprintf("- **Size:** %s\n", formatBytes($tool['size']));

            if (!empty($tool['capabilities'])) {
                $md .= "- **Capabilities:**\n";
                foreach (array_slice($tool['capabilities'], 0, 3) as $cap) {
                    $md .= sprintf("  - %s\n", $cap);
                }
            }

            if (!empty($tool['methods'])) {
                $md .= sprintf("- **Methods:** %d\n", count($tool['methods']));
            }

            $md .= "\n";
        }

        $md .= "---\n\n";
    }

    return $md;
}

function generateConsolidationPlan(array $data): string
{
    $md = "# 🔄 Tool Consolidation Plan\n\n";
    $md .= "**Generated:** " . date('Y-m-d H:i:s') . "\n\n";
    $md .= "---\n\n";

    $md .= "## 🎯 Objectives\n\n";
    $md .= "1. Eliminate duplicate functionality\n";
    $md .= "2. Integrate all tools into ToolRegistry\n";
    $md .= "3. Standardize tool interfaces\n";
    $md .= "4. Improve discoverability\n\n";

    $md .= "---\n\n";

    if (!empty($data['duplicates'])) {
        $md .= "## 🔄 Duplicate Tools\n\n";
        $md .= "These tools appear to have similar functionality:\n\n";

        foreach ($data['duplicates'] as $dup) {
            $md .= sprintf("### %s\n\n", $dup['name']);
            $md .= "**Instances:**\n";
            foreach ($dup['files'] as $file) {
                $md .= sprintf("- `%s`\n", $file);
            }
            $md .= "\n**Recommendation:** Review and keep best implementation\n\n";
        }

        $md .= "---\n\n";
    }

    $md .= "## 🔌 Missing from Registry\n\n";
    $md .= sprintf("**Total:** %d tools\n\n", count($data['registry']['missing']));

    foreach (array_slice($data['registry']['missing'], 0, 20) as $tool) {
        $md .= sprintf("- [ ] `%s` (%s)\n", $tool['name'], $tool['file']);
    }

    $md .= "\n---\n\n";

    if (!empty($data['gaps'])) {
        $md .= "## 📋 Capability Gaps\n\n";

        foreach ($data['gaps'] as $gap) {
            $md .= sprintf("### %s\n", ucwords(str_replace('_', ' ', $gap['category'])));
            $md .= sprintf("**Description:** %s\n", $gap['description']);
            $md .= sprintf("**Priority:** %s\n\n", $gap['priority']);
        }
    }

    return $md;
}

function generateIntegrationChecklist(array $data): string
{
    $md = "# ✅ Integration Checklist\n\n";
    $md .= "**Generated:** " . date('Y-m-d H:i:s') . "\n\n";
    $md .= "---\n\n";

    $md .= "## 🎯 Phase 1: High-Priority Tools\n\n";

    $highPriority = array_filter($data['registry']['missing'], function($tool) {
        return str_contains(strtolower($tool['file']), 'ai-agent') ||
               str_contains(strtolower($tool['file']), 'mcp');
    });

    foreach (array_slice($highPriority, 0, 10) as $tool) {
        $md .= sprintf("- [ ] Integrate `%s`\n", $tool['name']);
        $md .= sprintf("  - File: `%s`\n", $tool['file']);
        $md .= sprintf("  - Add to ToolRegistry\n");
        $md .= sprintf("  - Write tests\n");
        $md .= sprintf("  - Update documentation\n\n");
    }

    $md .= "---\n\n";

    $md .= "## 🎯 Phase 2: Automation Tools\n\n";

    $automation = array_filter($data['registry']['missing'], function($tool) {
        return str_contains(strtolower($tool['file']), '_automation');
    });

    foreach (array_slice($automation, 0, 10) as $tool) {
        $md .= sprintf("- [ ] Integrate `%s`\n", $tool['name']);
        $md .= sprintf("  - File: `%s`\n\n", $tool['file']);
    }

    $md .= "---\n\n";

    $md .= "## 🎯 Phase 3: Developer Tools\n\n";

    $devTools = array_filter($data['registry']['missing'], function($tool) {
        return str_contains(strtolower($tool['file']), '_dev-tools');
    });

    foreach (array_slice($devTools, 0, 10) as $tool) {
        $md .= sprintf("- [ ] Integrate `%s`\n", $tool['name']);
        $md .= sprintf("  - File: `%s`\n\n", $tool['file']);
    }

    return $md;
}

function autoIntegrate(array $config, array $missingTools): int
{
    // TODO: Implement auto-integration logic
    // This would:
    // 1. Parse tool file to extract metadata
    // 2. Generate ToolRegistry entry
    // 3. Update ToolRegistry.php
    // 4. Create test file
    // 5. Update documentation

    return 0;
}

function formatBytes(int $bytes): string
{
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 2) . ' KB';
    return round($bytes / 1048576, 2) . ' MB';
}
