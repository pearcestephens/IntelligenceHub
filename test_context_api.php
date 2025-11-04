#!/usr/bin/env php
<?php
/**
 * Test save_conversation.php API with project context
 */

$apiUrl = 'http://localhost/api/save_conversation.php';

$testData = [
    'session_id' => 'test-session-' . time(),
    'platform' => 'github_copilot',
    'user_identifier' => 'pearcestephens',
    'conversation_title' => 'Testing Context Detection - CIS Consignments',
    'conversation_context' => 'Working on pack validation in the Consignments module',

    // NEW: Project context fields
    'unit_id' => 2,              // CIS System
    'project_id' => 2,           // CIS - Consignments Module
    'server_id' => 'jcepnzzkmj', // CIS server
    'source' => 'github_copilot',

    'status' => 'active',
    'messages' => [
        [
            'role' => 'user',
            'content' => 'How do I validate pack items in the consignments module?',
            'timestamp' => date('Y-m-d H:i:s'),
            'tokens' => 15
        ],
        [
            'role' => 'assistant',
            'content' => 'You can validate pack items using the validatePackData() function located in modules/consignments/lib/Validation.php. It checks quantities, product IDs, and outlet availability.',
            'timestamp' => date('Y-m-d H:i:s'),
            'tokens' => 45
        ]
    ],
    'topics' => ['validation', 'consignments', 'pack', 'inventory']
];

echo "🧪 Testing save_conversation.php API with project context\n\n";
echo "📊 Test Data:\n";
echo "  Session ID: " . $testData['session_id'] . "\n";
echo "  Unit ID: " . $testData['unit_id'] . " (CIS System)\n";
echo "  Project ID: " . $testData['project_id'] . " (Consignments Module)\n";
echo "  Server ID: " . $testData['server_id'] . "\n";
echo "  Source: " . $testData['source'] . "\n\n";

// Test via direct PHP include (simulating API call)
echo "📡 Making API request...\n\n";

$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [];

// Simulate php://input
$mockInput = json_encode($testData);
file_put_contents('php://memory', $mockInput);

// Capture output
ob_start();

// Mock php://input by creating a stream
$stream = fopen('php://temp', 'r+');
fwrite($stream, $mockInput);
rewind($stream);
stream_context_set_default(['http' => ['header' => 'Content-Type: application/json']]);

// Include API (with input mocking)
$GLOBALS['_TEST_INPUT'] = $mockInput;
include __DIR__ . '/save_conversation.php';

$output = ob_get_clean();

echo "✅ API Response:\n";
echo $output . "\n\n";

// Parse response
$response = json_decode($output, true);

if ($response && $response['success']) {
    echo "✅ SUCCESS! Conversation saved with context.\n";
    echo "  Conversation ID: " . $response['conversation_id'] . "\n\n";

    // Verify in database
    echo "🔍 Verifying in database...\n";
    $pdo = new PDO('mysql:host=localhost;dbname=hdgwrzntwa;charset=utf8mb4', 'hdgwrzntwa', 'bFUdRjh4Jx');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("
        SELECT conversation_id, session_id, conversation_title,
               unit_id, project_id, server_id, source,
               total_messages, status
        FROM ai_conversations
        WHERE conversation_id = ?
    ");
    $stmt->execute([$response['conversation_id']]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($record) {
        echo "\n📋 Database Record:\n";
        echo "  Conversation ID: " . $record['conversation_id'] . "\n";
        echo "  Session ID: " . $record['session_id'] . "\n";
        echo "  Title: " . $record['conversation_title'] . "\n";
        echo "  Unit ID: " . ($record['unit_id'] ?? 'NULL') . " " . ($record['unit_id'] == 2 ? '✅' : '❌') . "\n";
        echo "  Project ID: " . ($record['project_id'] ?? 'NULL') . " " . ($record['project_id'] == 2 ? '✅' : '❌') . "\n";
        echo "  Server ID: " . ($record['server_id'] ?? 'NULL') . " " . ($record['server_id'] == 'jcepnzzkmj' ? '✅' : '❌') . "\n";
        echo "  Source: " . ($record['source'] ?? 'NULL') . " " . ($record['source'] == 'github_copilot' ? '✅' : '❌') . "\n";
        echo "  Total Messages: " . $record['total_messages'] . "\n";
        echo "  Status: " . $record['status'] . "\n";

        if ($record['unit_id'] == 2 && $record['project_id'] == 2 && $record['server_id'] == 'jcepnzzkmj') {
            echo "\n🎉 PERFECT! All context fields saved correctly!\n";
        } else {
            echo "\n⚠️  WARNING: Context fields not saved correctly\n";
        }
    }

} else {
    echo "❌ FAILED: " . ($response['error'] ?? 'Unknown error') . "\n";
}
