<?php
/**
 * Load KB Ignore Config into Database
 * 
 * Loads the centralized ignore patterns from JSON into scanner_ignore_config table
 * 
 * Usage: php load_ignore_config.php
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'hdgwrzntwa');
define('DB_USER', 'hdgwrzntwa');
define('DB_PASS', 'bFUdRjh4Jx');

$db = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
    DB_USER,
    DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Load JSON config
$config_file = '/home/master/applications/hdgwrzntwa/public_html/_kb/kb_ignore_config.json';
$config = json_decode(file_get_contents($config_file), true);

if (!$config) {
    die("Failed to load config file\n");
}

echo "Loading ignore patterns into database...\n";

$stmt = $db->prepare("
    INSERT INTO scanner_ignore_config (pattern_type, pattern_value, description, applies_to, priority)
    VALUES (?, ?, ?, 'all', ?)
    ON DUPLICATE KEY UPDATE 
        description = VALUES(description),
        is_active = 1
");

$loaded = 0;

// Load directories
if (isset($config['ignore_patterns']['directories'])) {
    foreach ($config['ignore_patterns']['directories'] as $dir) {
        $stmt->execute(['directory', $dir, 'Auto-loaded from kb_ignore_config.json', 10]);
        $loaded++;
    }
}

// Load files
if (isset($config['ignore_patterns']['files'])) {
    foreach ($config['ignore_patterns']['files'] as $file) {
        $stmt->execute(['file', $file, 'Auto-loaded from kb_ignore_config.json', 20]);
        $loaded++;
    }
}

// Load extensions
if (isset($config['ignore_patterns']['extensions'])) {
    foreach ($config['ignore_patterns']['extensions'] as $ext) {
        $stmt->execute(['extension', $ext, 'Auto-loaded from kb_ignore_config.json', 30]);
        $loaded++;
    }
}

// Load filename patterns
if (isset($config['ignore_patterns']['filename_patterns'])) {
    foreach ($config['ignore_patterns']['filename_patterns'] as $pattern) {
        $stmt->execute(['filename_pattern', $pattern, 'Auto-loaded from kb_ignore_config.json', 40]);
        $loaded++;
    }
}

echo "✅ Loaded $loaded ignore patterns\n";

// Show summary
$stmt = $db->query("
    SELECT pattern_type, COUNT(*) as count 
    FROM scanner_ignore_config 
    WHERE is_active = 1 
    GROUP BY pattern_type
");

echo "\n📊 Active Ignore Patterns:\n";
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    echo "   {$row['pattern_type']}: {$row['count']}\n";
}
