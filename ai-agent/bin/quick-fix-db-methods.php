#!/usr/bin/env php
<?php
/**
 * Quick Fix Script: Database Method Name Corrections
 * 
 * Automatically fixes all DB::query() calls to use correct methods:
 * - SELECT queries → DB::select()
 * - INSERT/UPDATE/DELETE → DB::execute()
 * 
 * Usage: php bin/quick-fix-db-methods.php
 */

$file = __DIR__ . '/../tests/Integration/DatabaseIntegrationTest.php';

if (!file_exists($file)) {
    die("❌ File not found: $file\n");
}

$content = file_get_contents($file);
$original = $content;

// Pattern 1: SELECT queries → DB::select()
$content = preg_replace(
    '/DB::query\((\'|")SELECT\s/i',
    'DB::select($1SELECT ',
    $content
);

// Pattern 2: INSERT queries → DB::execute()
$content = preg_replace(
    '/DB::query\((\'|")INSERT\s/i',
    'DB::execute($1INSERT ',
    $content
);

// Pattern 3: UPDATE queries → DB::execute()
$content = preg_replace(
    '/DB::query\((\'|")UPDATE\s/i',
    'DB::execute($1UPDATE ',
    $content
);

// Pattern 4: DELETE queries → DB::execute()
$content = preg_replace(
    '/DB::query\((\'|")DELETE\s/i',
    'DB::execute($1DELETE ',
    $content
);

// Pattern 5: SHOW queries → DB::select()
$content = preg_replace(
    '/DB::query\((\'|")SHOW\s/i',
    'DB::select($1SHOW ',
    $content
);

if ($content === $original) {
    echo "✓ No changes needed - file already correct\n";
    exit(0);
}

// Backup original
$backup = $file . '.backup-' . date('Ymd_His');
if (!copy($file, $backup)) {
    die("❌ Failed to create backup: $backup\n");
}

// Write fixed content
if (!file_put_contents($file, $content)) {
    die("❌ Failed to write fixed content\n");
}

// Count changes
$changes = substr_count($original, 'DB::query') - substr_count($content, 'DB::query');

echo "✅ Fixed $changes DB method calls\n";
echo "📝 Backup saved: $backup\n";
echo "✓ File updated: $file\n\n";

echo "Changes made:\n";
echo "  - SELECT queries → DB::select()\n";
echo "  - INSERT queries → DB::execute()\n";
echo "  - UPDATE queries → DB::execute()\n";
echo "  - DELETE queries → DB::execute()\n";
echo "  - SHOW queries → DB::select()\n\n";

echo "Next step: Run tests\n";
echo "  php bin/run-inline-tests.php\n";
echo "  php bin/run-phase-c-tests.php\n";

exit(0);
