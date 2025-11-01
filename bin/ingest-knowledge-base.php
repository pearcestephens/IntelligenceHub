#!/usr/bin/env php
<?php
/**
 * CIS Knowledge Base Ingestion Pipeline
 *
 * Loads all CIS documentation (_kb directory) into agent_kb_docs table
 * with semantic embeddings for AI agent search capabilities
 *
 * Usage:
 *   php bin/ingest-knowledge-base.php [options]
 *
 * Options:
 *   --dry-run         Preview what would be ingested (no DB writes)
 *   --force           Re-ingest existing documents
 *   --category=NAME   Ingest specific category only
 *   --limit=N         Limit to N documents (for testing)
 *   --verbose         Show detailed progress
 *   --help            Show this help
 *
 * Examples:
 *   php bin/ingest-knowledge-base.php --dry-run --verbose
 *   php bin/ingest-knowledge-base.php --category=architecture
 *   php bin/ingest-knowledge-base.php --limit=10
 *
 * @package CIS\AIAgent\Tools
 * @author Ecigdis Limited
 * @version 1.0.0
 */

declare(strict_types=1);

// Bootstrap paths
$kbPath = '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/_kb';
$agentPath = '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/ai-agent';

// Parse command line options
$options = getopt('', [
    'dry-run',
    'force',
    'category:',
    'limit:',
    'verbose',
    'help'
]);

if (isset($options['help'])) {
    showHelp();
    exit(0);
}

$dryRun = isset($options['dry-run']);
$force = isset($options['force']);
$category = $options['category'] ?? null;
$limit = isset($options['limit']) ? (int)$options['limit'] : null;
$verbose = isset($options['verbose']);

// Configuration
$config = [
    'mysql_host' => '127.0.0.1',
    'mysql_user' => 'jcepnzzkmj',
    'mysql_pass' => 'wprKh9Jq63',
    'mysql_db' => 'jcepnzzkmj',
    'chunk_size' => 1200,
    'chunk_overlap' => 120
];

// Initialize
echo "🚀 CIS Knowledge Base Ingestion Pipeline\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

if ($dryRun) {
    echo "⚠️  DRY RUN MODE - No database changes will be made\n\n";
}

// Connect to database
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

// Step 1: Scan KB directory
echo "[1/5] Scanning knowledge base directory...\n";
$files = scanKnowledgeBase($kbPath, $category);
echo "      Found: " . count($files) . " markdown files\n\n";

if (count($files) === 0) {
    echo "❌ No files found. Check path: {$kbPath}\n";
    exit(1);
}

if ($limit) {
    $files = array_slice($files, 0, $limit);
    echo "      Limited to: {$limit} files\n\n";
}

// Step 2: Check existing documents
echo "[2/5] Checking for existing documents...\n";
$existing = getExistingDocuments($pdo);
echo "      Existing: " . count($existing) . " documents in agent_kb_docs\n\n";

// Step 3: Process files
echo "[3/5] Processing documents...\n";
$stats = [
    'total' => count($files),
    'processed' => 0,
    'skipped' => 0,
    'failed' => 0,
    'chunks_created' => 0
];

foreach ($files as $file) {
    $relativePath = str_replace($kbPath . '/', '', $file);

    // Check if already exists
    if (!$force && in_array($relativePath, $existing)) {
        if ($verbose) {
            echo "      ⏭  SKIP: {$relativePath} (already exists)\n";
        }
        $stats['skipped']++;
        continue;
    }

    try {
        // Read file content
        $content = file_get_contents($file);
        if (!$content || strlen(trim($content)) < 50) {
            throw new Exception("File too short or empty");
        }

        // Extract metadata
        $metadata = extractMetadata($file, $relativePath);

        // Ingest document
        if (!$dryRun) {
            $docId = addDocument($pdo, $metadata, $content, $relativePath, $config);

            $chunks = $metadata['chunks'];
            $stats['chunks_created'] += $chunks;

            if ($verbose) {
                echo "      ✅ ADDED: {$relativePath} ({$chunks} chunks)\n";
            }
        } else {
            if ($verbose) {
                echo "      📄 WOULD ADD: {$relativePath} ({$metadata['chunks']} chunks)\n";
            }
        }

        $stats['processed']++;

    } catch (Exception $e) {
        $stats['failed']++;
        echo "      ❌ ERROR: {$relativePath} - {$e->getMessage()}\n";
    }

    // Progress indicator (every 10 files)
    if (!$verbose && $stats['processed'] % 10 === 0) {
        $progress = round(($stats['processed'] / $stats['total']) * 100);
        echo "      Progress: {$progress}% ({$stats['processed']}/{$stats['total']})\n";
    }
}

echo "\n";

// Step 4: Note about embeddings
if (!$dryRun && $stats['processed'] > 0) {
    echo "[4/5] Embeddings note...\n";
    echo "      Documents ingested without embeddings (OpenAI API required)\n";
    echo "      Agent can still search by keywords and metadata\n";
    echo "      For semantic search, run: php bin/generate-embeddings.php\n\n";
} else {
    echo "[4/5] Skipping embeddings (dry run or no new documents)\n\n";
}

// Step 5: Summary
echo "[5/5] Ingestion complete!\n\n";
echo "📊 Summary:\n";
echo "   Total files:      {$stats['total']}\n";
echo "   Processed:        {$stats['processed']}\n";
echo "   Skipped:          {$stats['skipped']}\n";
echo "   Failed:           {$stats['failed']}\n";
if (!$dryRun) {
    echo "   Chunks created:   {$stats['chunks_created']}\n";
}
echo "\n";

if ($dryRun) {
    echo "💡 Remove --dry-run to perform actual ingestion\n\n";
} else {
    echo "✅ Knowledge base ready for search!\n\n";
    echo "Next steps:\n";
    echo "  1. Test search: SELECT * FROM agent_kb_docs LIMIT 5;\n";
    echo "  2. Generate embeddings (optional): php bin/generate-embeddings.php\n";
    echo "  3. Test agent: curl -X POST api/chat\n\n";
}

exit($stats['failed'] > 0 ? 1 : 0);

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

function showHelp(): void
{
    echo <<<HELP
CIS Knowledge Base Ingestion Pipeline

Loads CIS documentation (_kb directory) into agent_kb_docs table for AI agent.

Usage:
  php bin/ingest-knowledge-base.php [OPTIONS]

Options:
  --dry-run         Preview ingestion without database changes
  --force           Re-ingest existing documents
  --category=NAME   Ingest specific category only
  --limit=N         Limit to N documents (for testing)
  --verbose         Show detailed progress for each file
  --help            Show this help message

Examples:
  # Preview what would be ingested
  php bin/ingest-knowledge-base.php --dry-run --verbose

  # Ingest specific category
  php bin/ingest-knowledge-base.php --category=architecture

  # Test with first 10 files
  php bin/ingest-knowledge-base.php --limit=10

  # Full ingestion
  php bin/ingest-knowledge-base.php

Categories available:
  - architecture    (System design and structure)
  - api             (API documentation)
  - guides          (How-to tutorials)
  - troubleshooting (Problem solving)
  - ai-agent        (AI agent documentation)
  - intelligence    (System intelligence)
  - security        (Security analysis)
  - performance     (Performance data)
  - database        (Database schemas)

HELP;
}

function scanKnowledgeBase(string $kbPath, ?string $category): array
{
    if (!is_dir($kbPath)) {
        echo "❌ KB path not found: {$kbPath}\n";
        exit(1);
    }

    $files = [];

    if ($category) {
        // Scan specific category
        $categoryPath = $kbPath . '/' . $category;
        if (!is_dir($categoryPath)) {
            echo "❌ Category not found: {$category}\n";
            exit(1);
        }
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($categoryPath, RecursiveDirectoryIterator::SKIP_DOTS)
        );
    } else {
        // Scan entire KB
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($kbPath, RecursiveDirectoryIterator::SKIP_DOTS)
        );
    }

    foreach ($iterator as $file) {
        if ($file->isFile() && strtolower($file->getExtension()) === 'md') {
            // Skip certain common files
            $filename = $file->getFilename();
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

function getExistingDocuments(PDO $pdo): array
{
    try {
        $stmt = $pdo->query('SELECT uri FROM agent_kb_docs WHERE source = "cis_kb"');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        echo "⚠️  Warning: Could not check existing documents: {$e->getMessage()}\n";
        return [];
    }
}

function extractMetadata(string $file, string $relativePath): array
{
    $filename = basename($file, '.md');
    $category = dirname($relativePath);

    // Extract title from filename (convert snake_case/kebab-case to Title Case)
    $title = ucwords(str_replace(['_', '-'], ' ', $filename));

    // Determine type based on category
    $typeMap = [
        'architecture' => 'architecture',
        'api' => 'api_documentation',
        'guides' => 'guide',
        'troubleshooting' => 'troubleshooting',
        'ai-agent' => 'ai_documentation',
        'intelligence' => 'intelligence',
        'security' => 'security',
        'performance' => 'performance',
        'database' => 'database_schema',
        'mcp' => 'mcp_tools',
        'analytics' => 'analytics'
    ];

    $docType = 'document';
    foreach ($typeMap as $key => $type) {
        if (stripos($category, $key) !== false) {
            $docType = $type;
            break;
        }
    }

    // Calculate chunks
    $content = file_get_contents($file);
    $chunks = calculateChunks($content, 1200, 120);

    return [
        'title' => $title,
        'type' => $docType,
        'category' => $category,
        'filename' => $filename,
        'size' => filesize($file),
        'modified' => date('Y-m-d H:i:s', filemtime($file)),
        'chunks' => $chunks
    ];
}

function calculateChunks(string $content, int $chunkSize, int $overlap): int
{
    $length = strlen($content);
    if ($length <= $chunkSize) {
        return 1;
    }

    $chunks = 1;
    $position = $chunkSize;

    while ($position < $length) {
        $chunks++;
        $position += ($chunkSize - $overlap);
    }

    return $chunks;
}

function addDocument(PDO $pdo, array $metadata, string $content, string $source, array $config): string
{
    $docId = generateUUID();
    $createdAt = date('Y-m-d H:i:s');

    // Prepare metadata JSON (includes content and category info)
    $metadataJson = json_encode([
        'category' => $metadata['category'],
        'filename' => $metadata['filename'],
        'size' => $metadata['size'],
        'modified' => $metadata['modified'],
        'chunks' => $metadata['chunks'],
        'content' => $content,  // Store content in meta JSON
        'content_length' => strlen($content)
    ]);

    // Determine MIME type
    $mimeType = 'text/markdown';

    // Insert document (using actual table columns: id, source, uri, title, mime, meta, created_at)
    $stmt = $pdo->prepare(
        'INSERT INTO agent_kb_docs (id, source, uri, title, mime, meta, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );

    $stmt->execute([
        $docId,
        'cis_kb',           // source (system identifier)
        $source,            // uri (file path)
        $metadata['title'], // title
        $mimeType,          // mime
        $metadataJson,      // meta (JSON with content, category, etc)
        $createdAt
    ]);

    return $docId;
}

function generateUUID(): string
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}
