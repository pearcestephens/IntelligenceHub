#!/usr/bin/env php
<?php
/**
 * Multi-Domain Knowledge Base Ingestion System
 *
 * Intelligently categorizes and ingests documentation into domain-specific tables
 * Supports: global, staff, web, gpt, wiki, superadmin + future domains
 *
 * Usage:
 *   php bin/ingest-multi-domain.php --domain=staff      # Single domain
 *   php bin/ingest-multi-domain.php --domain=all        # All domains
 *   php bin/ingest-multi-domain.php --god-mode          # Superadmin (all knowledge)
 *   php bin/ingest-multi-domain.php --auto-categorize   # Smart domain detection
 *
 * @version 2.0.0
 */

declare(strict_types=1);

// Parse command line options
$options = getopt('', [
    'domain:',
    'god-mode',
    'auto-categorize',
    'force',
    'dry-run',
    'verbose',
    'help'
]);

if (isset($options['help'])) {
    showHelp();
    exit(0);
}

$targetDomain = $options['domain'] ?? null;
$godMode = isset($options['god-mode']);
$autoCategorize = isset($options['auto-categorize']);
$force = isset($options['force']);
$dryRun = isset($options['dry-run']);
$verbose = isset($options['verbose']);

// Load configuration
$config = loadConfig();

// Database connection
try {
    $pdo = new PDO(
        "mysql:host={$config['mysql_host']};dbname={$config['mysql_db']};charset=utf8mb4",
        $config['mysql_user'],
        $config['mysql_pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("❌ Database connection failed: {$e->getMessage()}\n");
}

echo "🌐 Multi-Domain Knowledge Base Ingestion System\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Step 1: Load domain registry
echo "[1/6] Loading domain registry...\n";
$domains = loadDomains($pdo);
echo "      Registered domains: " . count($domains) . "\n";
if ($verbose) {
    foreach ($domains as $domain) {
        echo "        - {$domain['domain_key']}: {$domain['domain_name']}\n";
    }
}
echo "\n";

// Step 2: Scan knowledge base
echo "[2/6] Scanning knowledge base...\n";
$kbPath = $config['kb_path'];
$files = scanKnowledgeBase($kbPath);
echo "      Found: " . count($files) . " markdown files\n\n";

// Step 3: Auto-categorize or use target domain
echo "[3/6] Categorizing documents...\n";
$categorized = [];

if ($godMode) {
    echo "      🔱 GOD MODE: All documents → superadmin domain\n";
    $superadminId = getDomainId($pdo, 'superadmin');
    foreach ($files as $file) {
        $categorized[$superadminId][] = $file;
    }
} elseif ($autoCategorize) {
    echo "      🧠 Auto-categorizing based on content analysis...\n";
    $categorized = autoCategorizeFiles($files, $domains, $kbPath, $verbose);
} elseif ($targetDomain === 'all') {
    echo "      📚 Processing all domains individually...\n";
    $categorized = categorizeByDirectory($files, $domains, $kbPath, $verbose);
} elseif ($targetDomain) {
    echo "      🎯 Target domain: {$targetDomain}\n";
    $domainId = getDomainId($pdo, $targetDomain);
    if (!$domainId) {
        die("❌ Domain '{$targetDomain}' not found in registry\n");
    }
    foreach ($files as $file) {
        $categorized[$domainId][] = $file;
    }
} else {
    die("❌ Please specify --domain=X, --god-mode, or --auto-categorize\n");
}

// Show categorization summary
echo "\n      Categorization summary:\n";
$domainNames = array_column($domains, 'domain_name', 'id');
foreach ($categorized as $domainId => $domainFiles) {
    $domainName = $domainNames[$domainId] ?? "Unknown";
    echo "        - {$domainName}: " . count($domainFiles) . " files\n";
}
echo "\n";

if ($dryRun) {
    echo "🏁 Dry run complete! Remove --dry-run to proceed with ingestion.\n";
    exit(0);
}

// Step 4: Ingest documents
echo "[4/6] Ingesting documents...\n";
$stats = ['total' => 0, 'inserted' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];

foreach ($categorized as $domainId => $domainFiles) {
    $domainName = $domainNames[$domainId] ?? "Unknown";
    echo "      Processing domain: {$domainName}\n";

    foreach ($domainFiles as $file) {
        $stats['total']++;
        $relativePath = str_replace($kbPath . '/', '', $file);

        try {
            $content = file_get_contents($file);
            $metadata = extractMetadata($file, $relativePath);

            // Check if document exists
            $existing = findExistingDoc($pdo, $domainId, $relativePath);

            if ($existing && !$force) {
                $stats['skipped']++;
                if ($verbose) {
                    echo "        ⏭️  SKIPPED: {$relativePath}\n";
                }
                continue;
            }

            if ($existing) {
                // Update existing
                updateDocument($pdo, $existing['doc_id'], $content, $metadata);
                $stats['updated']++;
                if ($verbose) {
                    echo "        ♻️  UPDATED: {$relativePath}\n";
                }
            } else {
                // Insert new
                insertDocument($pdo, $domainId, $content, $metadata, $relativePath);
                $stats['inserted']++;
                if ($verbose) {
                    echo "        ✅ INSERTED: {$relativePath}\n";
                }
            }

        } catch (Exception $e) {
            $stats['failed']++;
            echo "        ❌ ERROR: {$relativePath} - {$e->getMessage()}\n";
        }
    }
}

echo "\n";

// Step 5: Update domain metrics
echo "[5/6] Updating domain metrics...\n";
updateDomainMetrics($pdo, $domains);
echo "      Metrics updated for " . count($domains) . " domains\n\n";

// Step 6: Generate cross-domain relationships
echo "[6/6] Building cross-domain relationships...\n";
buildCrossDomainRelationships($pdo, $domains);
echo "      Relationships indexed\n\n";

// Summary
echo "📊 Ingestion Summary:\n";
echo "   Total files:     {$stats['total']}\n";
echo "   Inserted:        {$stats['inserted']}\n";
echo "   Updated:         {$stats['updated']}\n";
echo "   Skipped:         {$stats['skipped']}\n";
echo "   Failed:          {$stats['failed']}\n";
echo "\n";

if ($stats['failed'] === 0) {
    echo "✅ Multi-domain knowledge base ready!\n\n";
    echo "Next steps:\n";
    echo "  1. Test domain search: php bin/test-domain-search.php --domain=staff\n";
    echo "  2. Access live chat: http://staff.vapeshed.co.nz/dashboard/?page=ai-chat-live\n";
    echo "  3. Use GOD MODE: Toggle in chat interface for full visibility\n\n";
} else {
    echo "⚠️  Completed with {$stats['failed']} errors\n\n";
}

exit($stats['failed'] > 0 ? 1 : 0);

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function showHelp(): void
{
    echo <<<HELP
Multi-Domain Knowledge Base Ingestion System

Usage:
  php bin/ingest-multi-domain.php [OPTIONS]

Options:
  --domain=DOMAIN       Target specific domain (global, staff, web, gpt, wiki, superadmin)
  --domain=all          Process all domains (auto-categorize by directory)
  --god-mode            Ingest everything into superadmin (full visibility)
  --auto-categorize     Smart AI-based domain detection
  --force               Re-ingest existing documents
  --dry-run             Preview without database writes
  --verbose             Show detailed progress
  --help                Show this help message

Examples:
  # Ingest CIS docs into staff domain
  php bin/ingest-multi-domain.php --domain=staff --verbose

  # GOD MODE - everything visible to superadmin
  php bin/ingest-multi-domain.php --god-mode

  # Auto-detect domains from content
  php bin/ingest-multi-domain.php --auto-categorize

  # Process all domains
  php bin/ingest-multi-domain.php --domain=all

HELP;
}

function loadConfig(): array
{
    // Hardcoded config (bypass .env parsing issues)
    return [
        'kb_path' => __DIR__ . '/../_kb',
        'mysql_host' => '127.0.0.1',
        'mysql_db' => 'jcepnzzkmj',
        'mysql_user' => 'jcepnzzkmj',
        'mysql_pass' => 'wprKh9Jq63',
    ];
}function loadDomains(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT * FROM ai_kb_domain_registry WHERE is_active = 1 ORDER BY id');
    return $stmt->fetchAll();
}

function getDomainId(PDO $pdo, string $domainKey): ?int
{
    $stmt = $pdo->prepare('SELECT id FROM ai_kb_domain_registry WHERE domain_key = ? AND is_active = 1');
    $stmt->execute([$domainKey]);
    $result = $stmt->fetch();
    return $result ? (int)$result['id'] : null;
}

function scanKnowledgeBase(string $path): array
{
    $files = [];

    if (!is_dir($path)) {
        return $files;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'md') {
            $filename = $file->getFilename();

            // Skip certain files
            if (in_array(strtoupper($filename), ['README.MD', 'CHANGELOG.MD', 'LICENSE.MD'])) {
                continue;
            }

            // Skip hidden files
            if (strpos($filename, '.') === 0) {
                continue;
            }

            $files[] = $file->getPathname();
        }
    }

    sort($files);
    return $files;
}

function autoCategorizeFiles(array $files, array $domains, string $kbPath, bool $verbose): array
{
    $categorized = [];
    $domainMap = array_column($domains, 'id', 'domain_key');

    foreach ($files as $file) {
        $relativePath = str_replace($kbPath . '/', '', $file);
        $content = file_get_contents($file);

        // Smart categorization based on path and content
        $domainKey = detectDomain($relativePath, $content);
        $domainId = $domainMap[$domainKey] ?? $domainMap['global'];

        $categorized[$domainId][] = $file;

        if ($verbose) {
            echo "        {$relativePath} → {$domainKey}\n";
        }
    }

    return $categorized;
}

function detectDomain(string $path, string $content): string
{
    // Path-based detection
    if (preg_match('#(staff|cis|inventory|transfer|hr)#i', $path)) {
        return 'staff';
    }
    if (preg_match('#(web|public|customer|faq)#i', $path)) {
        return 'web';
    }
    if (preg_match('#(gpt|ai|agent|prompt|intelligence)#i', $path)) {
        return 'gpt';
    }
    if (preg_match('#(wiki|docs|documentation|guide)#i', $path)) {
        return 'wiki';
    }

    // Content-based detection
    if (preg_match('#(staff portal|CIS|inventory management|stock transfer)#i', $content)) {
        return 'staff';
    }
    if (preg_match('#(AI agent|GPT|prompt|intelligence hub)#i', $content)) {
        return 'gpt';
    }
    if (preg_match('#(customer|public|website|product)#i', $content)) {
        return 'web';
    }

    return 'global';
}

function categorizeByDirectory(array $files, array $domains, string $kbPath, bool $verbose): array
{
    $categorized = [];
    $domainMap = array_column($domains, 'id', 'domain_key');

    foreach ($files as $file) {
        $relativePath = str_replace($kbPath . '/', '', $file);
        $parts = explode('/', $relativePath);
        $topDir = $parts[0] ?? '';

        // Map directory to domain
        $domainKey = match(true) {
            str_contains($topDir, 'staff') => 'staff',
            str_contains($topDir, 'web') => 'web',
            str_contains($topDir, 'gpt') || str_contains($topDir, 'ai') => 'gpt',
            str_contains($topDir, 'wiki') || str_contains($topDir, 'doc') => 'wiki',
            default => 'global'
        };

        $domainId = $domainMap[$domainKey] ?? $domainMap['global'];
        $categorized[$domainId][] = $file;
    }

    return $categorized;
}

function extractMetadata(string $file, string $relativePath): array
{
    $filename = basename($file, '.md');
    $title = ucwords(str_replace(['_', '-'], ' ', $filename));

    // Detect document type from filename/path
    $docType = 'overview';
    if (preg_match('#api|endpoint#i', $relativePath)) {
        $docType = 'api';
    } elseif (preg_match('#database|schema|table#i', $relativePath)) {
        $docType = 'database';
    } elseif (preg_match('#workflow|process#i', $relativePath)) {
        $docType = 'workflow';
    } elseif (preg_match('#integration|sync#i', $relativePath)) {
        $docType = 'integration';
    } elseif (preg_match('#troubleshoot|error|fix#i', $relativePath)) {
        $docType = 'troubleshooting';
    } elseif (preg_match('#security|auth#i', $relativePath)) {
        $docType = 'security';
    } elseif (preg_match('#performance|optim#i', $relativePath)) {
        $docType = 'performance';
    } elseif (preg_match('#architect#i', $relativePath)) {
        $docType = 'architecture';
    }

    return [
        'title' => $title,
        'doc_type' => $docType,
        'filename' => $filename,
        'relative_path' => $relativePath,
        'size' => filesize($file),
        'modified' => date('Y-m-d H:i:s', filemtime($file))
    ];
}

function findExistingDoc(PDO $pdo, int $domainId, string $relativePath): ?array
{
    $stmt = $pdo->prepare('
        SELECT doc_id, title
        FROM ai_kb_domain_documentation
        WHERE domain_id = ? AND JSON_EXTRACT(tags, "$.relative_path") = ?
    ');
    $stmt->execute([$domainId, $relativePath]);
    return $stmt->fetch() ?: null;
}

function insertDocument(PDO $pdo, int $domainId, string $content, array $metadata, string $relativePath): void
{
    $tags = json_encode([
        'filename' => $metadata['filename'],
        'relative_path' => $relativePath,
        'size' => $metadata['size'],
        'modified' => $metadata['modified']
    ]);

    $stmt = $pdo->prepare('
        INSERT INTO ai_kb_domain_documentation
        (domain_id, doc_type, title, content, format, tags, is_auto_generated, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
    ');

    $stmt->execute([
        $domainId,
        $metadata['doc_type'],
        $metadata['title'],
        $content,
        'markdown',
        $tags
    ]);
}

function updateDocument(PDO $pdo, int $docId, string $content, array $metadata): void
{
    $stmt = $pdo->prepare('
        UPDATE ai_kb_domain_documentation
        SET content = ?, title = ?, updated_at = NOW()
        WHERE doc_id = ?
    ');

    $stmt->execute([$content, $metadata['title'], $docId]);
}

function updateDomainMetrics(PDO $pdo, array $domains): void
{
    foreach ($domains as $domain) {
        $stmt = $pdo->prepare('
            INSERT INTO ai_kb_domain_metrics (domain_id, metric_date, doc_count, total_size_kb)
            SELECT
                ? as domain_id,
                CURDATE() as metric_date,
                COUNT(*) as doc_count,
                ROUND(SUM(LENGTH(content)) / 1024, 2) as total_size_kb
            FROM ai_kb_domain_documentation
            WHERE domain_id = ?
            ON DUPLICATE KEY UPDATE
                doc_count = VALUES(doc_count),
                total_size_kb = VALUES(total_size_kb),
                updated_at = NOW()
        ');
        $stmt->execute([$domain['id'], $domain['id']]);
    }
}

function buildCrossDomainRelationships(PDO $pdo, array $domains): void
{
    // Find documents that reference other domains
    $stmt = $pdo->prepare('
        INSERT IGNORE INTO ai_kb_cross_domain_relationships (source_domain_id, target_domain_id, relationship_type, doc_id)
        SELECT DISTINCT
            d1.domain_id as source_domain_id,
            d2.id as target_domain_id,
            "reference" as relationship_type,
            d1.doc_id
        FROM ai_kb_domain_documentation d1
        CROSS JOIN ai_kb_domain_registry d2
        WHERE d1.content LIKE CONCAT("%", d2.domain_name, "%")
        AND d1.domain_id != d2.id
    ');
    $stmt->execute();
}
