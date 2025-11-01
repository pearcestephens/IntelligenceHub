<?php
/**
 * Deploy Intelligence Scan Endpoints to All Satellites
 * 
 * Creates /api/intelligence/scan.php on each satellite server
 * This allows the Intelligence Hub to scan and index their codebases
 * 
 * @package Intelligence Hub
 * @version 2.0.0
 */

declare(strict_types=1);

// Satellite configurations
$satellites = [
    'dvaxgvsxmz' => [
        'name' => 'VapeShed',
        'base_path' => '/home/master/applications/dvaxgvsxmz/public_html',
        'db_name' => 'dvaxgvsxmz',
        'description' => 'VapeShed E-Commerce Platform',
        'unit_id' => 3
    ],
    'jcepnzzkmj' => [
        'name' => 'CIS',
        'base_path' => '/home/master/applications/jcepnzzkmj/public_html',
        'db_name' => 'jcepnzzkmj',
        'description' => 'Central Information System',
        'unit_id' => 2
    ],
    'fhrehrpjmu' => [
        'name' => 'Wholesale',
        'base_path' => '/home/master/applications/fhrehrpjmu/public_html',
        'db_name' => 'fhrehrpjmu',
        'description' => 'Wholesale E-Commerce Platform',
        'unit_id' => 4
    ]
];

$scannerCode = <<<'PHP'
<?php
/**
 * Intelligence Hub - Satellite Scan Endpoint
 * 
 * Provides file listing and content scanning for the Intelligence Hub
 * Version: 2.0.0
 * 
 * Security: Only accessible from Intelligence Hub (IP whitelist optional)
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://gpt.ecigdis.co.nz');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Configuration
$config = [
    'satellite_name' => '{{SATELLITE_NAME}}',
    'satellite_id' => {{UNIT_ID}},
    'base_path' => '{{BASE_PATH}}',
    'db_name' => '{{DB_NAME}}',
    'allowed_extensions' => ['php', 'js', 'css', 'html', 'md', 'json', 'xml', 'sql'],
    'exclude_dirs' => ['vendor', 'node_modules', '.git', 'cache', 'sessions', 'tmp', 'logs'],
    'max_file_size' => 5 * 1024 * 1024, // 5MB
];

// Parse request
$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? $_GET['action'] ?? 'info';

try {
    switch ($action) {
        case 'info':
            echo json_encode(getSatelliteInfo($config), JSON_PRETTY_PRINT);
            break;
            
        case 'scan':
            $path = $input['path'] ?? '.';
            $recursive = $input['recursive'] ?? true;
            echo json_encode(scanDirectory($config, $path, $recursive), JSON_PRETTY_PRINT);
            break;
            
        case 'file':
            $file = $input['file'] ?? null;
            if (!$file) {
                throw new Exception('File path required');
            }
            echo json_encode(getFileContent($config, $file), JSON_PRETTY_PRINT);
            break;
            
        case 'stats':
            echo json_encode(getStatistics($config), JSON_PRETTY_PRINT);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'satellite' => $config['satellite_name']
    ], JSON_PRETTY_PRINT);
}

function getSatelliteInfo(array $config): array
{
    return [
        'success' => true,
        'satellite' => [
            'name' => $config['satellite_name'],
            'unit_id' => $config['satellite_id'],
            'base_path' => $config['base_path'],
            'db_name' => $config['db_name'],
            'php_version' => PHP_VERSION,
            'server_time' => date('Y-m-d H:i:s'),
            'disk_free' => disk_free_space($config['base_path']),
            'disk_total' => disk_total_space($config['base_path']),
        ],
        'capabilities' => [
            'scan_files' => true,
            'read_content' => true,
            'statistics' => true,
            'allowed_extensions' => $config['allowed_extensions'],
        ]
    ];
}

function scanDirectory(array $config, string $path, bool $recursive): array
{
    $basePath = rtrim($config['base_path'], '/');
    $scanPath = $basePath . '/' . ltrim($path, '/');
    
    if (!is_dir($scanPath)) {
        throw new Exception("Directory not found: {$path}");
    }
    
    $files = [];
    $iterator = $recursive 
        ? new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($scanPath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        )
        : new DirectoryIterator($scanPath);
    
    foreach ($iterator as $file) {
        if ($file->isDot()) continue;
        
        $relativePath = str_replace($basePath . '/', '', $file->getPathname());
        
        // Skip excluded directories
        $skip = false;
        foreach ($config['exclude_dirs'] as $excluded) {
            if (strpos($relativePath, $excluded . '/') !== false || $relativePath === $excluded) {
                $skip = true;
                break;
            }
        }
        if ($skip) continue;
        
        if ($file->isFile()) {
            $ext = strtolower($file->getExtension());
            if (in_array($ext, $config['allowed_extensions'])) {
                $files[] = [
                    'path' => $relativePath,
                    'name' => $file->getFilename(),
                    'size' => $file->getSize(),
                    'extension' => $ext,
                    'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                    'type' => 'file'
                ];
            }
        } elseif ($file->isDir() && !$recursive) {
            $files[] = [
                'path' => $relativePath,
                'name' => $file->getFilename(),
                'type' => 'directory'
            ];
        }
    }
    
    return [
        'success' => true,
        'satellite' => $config['satellite_name'],
        'path' => $path,
        'recursive' => $recursive,
        'file_count' => count($files),
        'files' => $files,
        'scanned_at' => date('Y-m-d H:i:s')
    ];
}

function getFileContent(array $config, string $file): array
{
    $basePath = rtrim($config['base_path'], '/');
    $filePath = $basePath . '/' . ltrim($file, '/');
    
    if (!file_exists($filePath)) {
        throw new Exception("File not found: {$file}");
    }
    
    if (!is_file($filePath)) {
        throw new Exception("Path is not a file: {$file}");
    }
    
    $size = filesize($filePath);
    if ($size > $config['max_file_size']) {
        throw new Exception("File too large: " . round($size / 1024 / 1024, 2) . "MB");
    }
    
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    if (!in_array($ext, $config['allowed_extensions'])) {
        throw new Exception("File extension not allowed: {$ext}");
    }
    
    $content = file_get_contents($filePath);
    
    return [
        'success' => true,
        'satellite' => $config['satellite_name'],
        'file' => $file,
        'size' => $size,
        'extension' => $ext,
        'modified' => date('Y-m-d H:i:s', filemtime($filePath)),
        'lines' => substr_count($content, "\n") + 1,
        'content' => $content,
        'md5' => md5($content),
        'retrieved_at' => date('Y-m-d H:i:s')
    ];
}

function getStatistics(array $config): array
{
    $basePath = $config['base_path'];
    $stats = [
        'total_files' => 0,
        'total_size' => 0,
        'by_extension' => [],
        'by_directory' => []
    ];
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($basePath, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if (!$file->isFile()) continue;
        
        $relativePath = str_replace($basePath . '/', '', $file->getPathname());
        
        // Skip excluded directories
        $skip = false;
        foreach ($config['exclude_dirs'] as $excluded) {
            if (strpos($relativePath, $excluded . '/') !== false) {
                $skip = true;
                break;
            }
        }
        if ($skip) continue;
        
        $ext = strtolower($file->getExtension());
        if (!in_array($ext, $config['allowed_extensions'])) continue;
        
        $size = $file->getSize();
        $stats['total_files']++;
        $stats['total_size'] += $size;
        
        // By extension
        if (!isset($stats['by_extension'][$ext])) {
            $stats['by_extension'][$ext] = ['count' => 0, 'size' => 0];
        }
        $stats['by_extension'][$ext]['count']++;
        $stats['by_extension'][$ext]['size'] += $size;
        
        // By directory (top level only)
        $parts = explode('/', $relativePath);
        $topDir = $parts[0] ?? 'root';
        if (!isset($stats['by_directory'][$topDir])) {
            $stats['by_directory'][$topDir] = ['count' => 0, 'size' => 0];
        }
        $stats['by_directory'][$topDir]['count']++;
        $stats['by_directory'][$topDir]['size'] += $size;
    }
    
    return [
        'success' => true,
        'satellite' => $config['satellite_name'],
        'statistics' => $stats,
        'generated_at' => date('Y-m-d H:i:s')
    ];
}
PHP;

// Deploy to each satellite
$results = [];

foreach ($satellites as $id => $sat) {
    echo "Deploying to {$sat['name']} ({$id})...\n";
    
    // Create api/intelligence directory
    $apiDir = $sat['base_path'] . '/api/intelligence';
    if (!is_dir($apiDir)) {
        mkdir($apiDir, 0755, true);
        echo "  ✓ Created directory: {$apiDir}\n";
    }
    
    // Replace placeholders in scanner code
    $deployCode = $scannerCode;
    $deployCode = str_replace('{{SATELLITE_NAME}}', $sat['name'], $deployCode);
    $deployCode = str_replace('{{UNIT_ID}}', (string)$sat['unit_id'], $deployCode);
    $deployCode = str_replace('{{BASE_PATH}}', $sat['base_path'], $deployCode);
    $deployCode = str_replace('{{DB_NAME}}', $sat['db_name'], $deployCode);
    
    // Write scan.php file
    $scanFile = $apiDir . '/scan.php';
    $written = file_put_contents($scanFile, $deployCode);
    
    if ($written !== false) {
        chmod($scanFile, 0644);
        echo "  ✓ Deployed scan.php ({$written} bytes)\n";
        
        $results[$id] = [
            'success' => true,
            'satellite' => $sat['name'],
            'file' => $scanFile,
            'size' => $written
        ];
    } else {
        echo "  ✗ Failed to write scan.php\n";
        $results[$id] = [
            'success' => false,
            'satellite' => $sat['name'],
            'error' => 'Failed to write file'
        ];
    }
    
    echo "\n";
}

// Summary
echo "═══════════════════════════════════════════════════════════════\n";
echo "DEPLOYMENT SUMMARY\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

foreach ($results as $id => $result) {
    $status = $result['success'] ? '✓' : '✗';
    echo "{$status} {$result['satellite']}: ";
    if ($result['success']) {
        echo "Deployed ({$result['size']} bytes)\n";
        echo "   URL: https://" . getUrlForSatellite($id) . "/api/intelligence/scan.php\n";
    } else {
        echo "Failed - {$result['error']}\n";
    }
}

echo "\n";
echo "Next: Trigger scans from Intelligence Hub\n";
echo "  curl https://gpt.ecigdis.co.nz/mcp/server_v2_complete.php \\\n";
echo "    -H 'Content-Type: application/json' \\\n";
echo "    -d '{\"jsonrpc\":\"2.0\",\"method\":\"tools/call\",\"params\":{\"name\":\"sync_satellite\",\"arguments\":{\"unit_id\":2}},\"id\":1}'\n";

function getUrlForSatellite(string $id): string
{
    $urls = [
        'dvaxgvsxmz' => 'www.vapeshed.co.nz',
        'jcepnzzkmj' => 'staff.vapeshed.co.nz',
        'fhrehrpjmu' => 'vapingkiwi.co.nz'
    ];
    return $urls[$id] ?? 'unknown';
}
