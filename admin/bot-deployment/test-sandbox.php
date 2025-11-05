#!/usr/bin/env php
<?php
/**
 * Test Generic Sandbox Setup
 *
 * Verifies that the generic sandbox fallback system is working correctly
 *
 * @package BotDeployment\Tests
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Database/Connection.php';
require_once __DIR__ . '/src/Helpers/SandboxHelper.php';

use BotDeployment\Database\Connection;
use BotDeployment\Helpers\SandboxHelper;

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "  GENERIC SANDBOX TEST SUITE\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";

$allPassed = true;

// Test 1: Database Records
echo "TEST 1: Database Records\n";
echo "─────────────────────────\n";
try {
    $pdo = Connection::get();

    $project = SandboxHelper::getSandboxProject($pdo);
    $unit = SandboxHelper::getSandboxUnit($pdo);

    if ($project && $unit) {
        echo "✅ PASS - Sandbox records exist in database\n";
        echo "   Business Unit: {$unit['unit_name']} (ID: {$unit['unit_id']})\n";
        echo "   Project: {$project['project_name']} (ID: {$project['id']})\n";
    } else {
        echo "❌ FAIL - Sandbox records not found\n";
        $allPassed = false;
    }

    Connection::release($pdo);
} catch (Exception $e) {
    echo "❌ FAIL - Database error: " . $e->getMessage() . "\n";
    $allPassed = false;
}
echo "\n";

// Test 2: Fallback Logic
echo "TEST 2: Fallback Logic\n";
echo "───────────────────────\n";
$testProjectId = SandboxHelper::getProjectId(null);
$testUnitId = SandboxHelper::getUnitId(null);

if ($testProjectId === 999 && $testUnitId === 999) {
    echo "✅ PASS - Fallback returns sandbox IDs\n";
    echo "   Project ID: {$testProjectId}\n";
    echo "   Unit ID: {$testUnitId}\n";
} else {
    echo "❌ FAIL - Fallback not working correctly\n";
    $allPassed = false;
}
echo "\n";

// Test 3: Sandbox Detection
echo "TEST 3: Sandbox Detection\n";
echo "─────────────────────────\n";
$isSandbox1 = SandboxHelper::isSandbox(999, 999);
$isSandbox2 = SandboxHelper::isSandbox(null, null);
$isSandbox3 = SandboxHelper::isSandbox(1, 1);

if ($isSandbox1 && $isSandbox2 && !$isSandbox3) {
    echo "✅ PASS - Sandbox detection working\n";
    echo "   isSandbox(999, 999): true ✓\n";
    echo "   isSandbox(null, null): true ✓\n";
    echo "   isSandbox(1, 1): false ✓\n";
} else {
    echo "❌ FAIL - Sandbox detection not working\n";
    $allPassed = false;
}
echo "\n";

// Test 4: Path Validation
echo "TEST 4: Path Validation\n";
echo "────────────────────────\n";
$validPath = '/sandbox/test.txt';
$invalidPath = '/var/www/private/data.txt';
$excludedPath = '/sandbox/private/secret.txt';

$valid1 = SandboxHelper::validatePath($validPath, true);
$valid2 = SandboxHelper::validatePath($invalidPath, true);
$valid3 = SandboxHelper::validatePath($excludedPath, true);
$valid4 = SandboxHelper::validatePath($invalidPath, false); // Not in sandbox mode

if ($valid1 && !$valid2 && !$valid3 && $valid4) {
    echo "✅ PASS - Path validation working\n";
    echo "   /sandbox/test.txt (sandbox): allowed ✓\n";
    echo "   /var/www/private/data.txt (sandbox): blocked ✓\n";
    echo "   /sandbox/private/secret.txt (sandbox): blocked ✓\n";
    echo "   /var/www/private/data.txt (normal): allowed ✓\n";
} else {
    echo "❌ FAIL - Path validation not working\n";
    echo "   Debug: valid1={$valid1}, valid2={$valid2}, valid3={$valid3}, valid4={$valid4}\n";
    $allPassed = false;
}
echo "\n";

// Test 5: Restrictions
echo "TEST 5: Restrictions\n";
echo "────────────────────\n";
$restrictions = SandboxHelper::getRestrictions();

if (
    !empty($restrictions['allowed_paths']) &&
    !empty($restrictions['excluded_paths']) &&
    $restrictions['max_depth'] === 2 &&
    $restrictions['read_only'] === true
) {
    echo "✅ PASS - Restrictions defined\n";
    echo "   Allowed paths: " . count($restrictions['allowed_paths']) . "\n";
    echo "   Excluded paths: " . count($restrictions['excluded_paths']) . "\n";
    echo "   Max depth: {$restrictions['max_depth']}\n";
    echo "   Read only: " . ($restrictions['read_only'] ? 'Yes' : 'No') . "\n";
} else {
    echo "❌ FAIL - Restrictions not properly defined\n";
    $allPassed = false;
}
echo "\n";

// Test 6: Session Initialization
echo "TEST 6: Session Initialization\n";
echo "───────────────────────────────\n";
$session1 = SandboxHelper::initializeSandboxSession(null, null);
$session2 = SandboxHelper::initializeSandboxSession(1, 1);

if (
    $session1['current_project_id'] === 999 &&
    $session1['is_sandbox'] === true &&
    $session2['current_project_id'] === 1 &&
    $session2['is_sandbox'] === false
) {
    echo "✅ PASS - Session initialization working\n";
    echo "   Null inputs → Sandbox mode: true\n";
    echo "   Valid inputs → Sandbox mode: false\n";
} else {
    echo "❌ FAIL - Session initialization not working\n";
    $allPassed = false;
}
echo "\n";

// Test 7: Directory Structure
echo "TEST 7: Directory Structure\n";
echo "───────────────────────────\n";
$sandboxBase = '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/sandbox';
$dirs = ['', '/public', '/temp', '/logs'];
$allDirsExist = true;

foreach ($dirs as $dir) {
    $fullPath = $sandboxBase . $dir;
    if (!is_dir($fullPath)) {
        $allDirsExist = false;
        echo "❌ Missing: {$fullPath}\n";
    }
}

if ($allDirsExist && file_exists($sandboxBase . '/README.md')) {
    echo "✅ PASS - Directory structure exists\n";
    echo "   /sandbox ✓\n";
    echo "   /sandbox/public ✓\n";
    echo "   /sandbox/temp ✓\n";
    echo "   /sandbox/logs ✓\n";
    echo "   /sandbox/README.md ✓\n";
} else {
    echo "❌ FAIL - Directory structure incomplete\n";
    $allPassed = false;
}
echo "\n";

// Final Result
echo "═══════════════════════════════════════════════════════════════\n";
if ($allPassed) {
    echo "  ✅ ALL TESTS PASSED\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "\n";
    echo "🎉 Generic Sandbox is fully operational!\n";
    echo "\n";
    echo "You can now use:\n";
    echo "  • SandboxHelper::getProjectId(null) → 999\n";
    echo "  • SandboxHelper::getUnitId(null) → 999\n";
    echo "  • SandboxHelper::isSandbox(\$projectId, \$unitId)\n";
    echo "  • SandboxHelper::validatePath(\$path, \$isSandbox)\n";
    echo "\n";
    exit(0);
} else {
    echo "  ❌ SOME TESTS FAILED\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "\n";
    echo "⚠️  Please review the failed tests above.\n";
    echo "\n";
    exit(1);
}
