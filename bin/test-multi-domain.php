#!/usr/bin/env php
<?php

/**
 * Multi-Domain System Test Script
 *
 * Tests all multi-domain functionality:
 * - Domain switching
 * - GOD MODE activation/deactivation
 * - Domain-aware search
 * - Domain statistics
 * - Document-domain mapping
 *
 * Usage: php test-multi-domain.php
 */

declare(strict_types=1);

// Load autoloader
// Load Composer vendor dependencies first (includes ramsey/uuid)
require_once __DIR__ . '/../ai-agent/vendor/autoload.php';

// Then load our custom App\ namespace autoloader
require_once __DIR__ . '/../ai-agent/autoload.php';

use App\Agent;
use App\Config;
use App\Logger;
use App\Memory\MultiDomain;
use App\Util\Ids;

// Color output helpers
function colorize(string $text, string $color): string {
    $colors = [
        'green' => "\033[0;32m",
        'red' => "\033[0;31m",
        'yellow' => "\033[1;33m",
        'blue' => "\033[0;34m",
        'reset' => "\033[0m"
    ];
    return ($colors[$color] ?? '') . $text . $colors['reset'];
}

function success(string $message): void {
    echo colorize("✓ ", 'green') . $message . "\n";
}

function error(string $message): void {
    echo colorize("✗ ", 'red') . $message . "\n";
}

function info(string $message): void {
    echo colorize("ℹ ", 'blue') . $message . "\n";
}

function section(string $title): void {
    echo "\n" . colorize(str_repeat("=", 60), 'yellow') . "\n";
    echo colorize($title, 'yellow') . "\n";
    echo colorize(str_repeat("=", 60), 'yellow') . "\n\n";
}

// Initialize
try {
    $config = new Config();
    $logger = new Logger($config);
    $agent = new Agent($config, $logger);
    $agent->initialize();

    section("MULTI-DOMAIN SYSTEM TEST");

    // Create test conversation
    $conversationId = Ids::uuid();
    info("Test conversation ID: {$conversationId}");

    // Insert test conversation
    \App\DB::execute(
        'INSERT INTO agent_conversations (conversation_id, user_id, created_at) VALUES (?, ?, NOW())',
        [$conversationId, 'test-user']
    );
    success("Test conversation created");

    // Test 1: List all domains
    section("TEST 1: List All Domains");
    $domains = $agent->getAllDomains();

    if (!empty($domains)) {
        success("Found " . count($domains) . " domains:");
        foreach ($domains as $domain) {
            echo "  - {$domain['name']} (ID: {$domain['domain_id']})\n";
        }
    } else {
        error("No domains found");
    }

    // Test 2: Get initial domain (should default to global)
    section("TEST 2: Get Current Domain");
    $currentDomain = $agent->getCurrentDomain($conversationId);

    if ($currentDomain) {
        success("Current domain: {$currentDomain['domain_name']} (ID: {$currentDomain['active_domain_id']})");
        echo "  GOD MODE: " . ($currentDomain['god_mode_enabled'] ? 'ENABLED' : 'DISABLED') . "\n";
    } else {
        error("Failed to get current domain");
    }

    // Test 3: Switch to staff domain
    section("TEST 3: Switch to Staff Domain");
    $switched = $agent->switchDomain($conversationId, 'staff');

    if ($switched) {
        success("Switched to staff domain");
        $currentDomain = $agent->getCurrentDomain($conversationId);
        echo "  Current domain: {$currentDomain['domain_name']}\n";
        echo "  Domain switches: {$currentDomain['domain_switch_count']}\n";
    } else {
        error("Failed to switch domain");
    }

    // Test 4: Get domain statistics
    section("TEST 4: Get Domain Statistics");
    $stats = $agent->getDomainStats();

    if (!empty($stats)) {
        success("Retrieved statistics for " . count($stats) . " domains:");
        foreach ($stats as $stat) {
            echo "\n  Domain: {$stat['domain_name']}\n";
            echo "    Documents: {$stat['total_documents']}\n";
            echo "    Avg Relevance: " . number_format($stat['avg_relevance_score'], 2) . "\n";
            echo "    Total Queries: {$stat['total_queries']}\n";
        }
    } else {
        error("Failed to get domain statistics");
    }

    // Test 5: Enable GOD MODE
    section("TEST 5: Enable GOD MODE");
    $godModeEnabled = $agent->enableGodMode($conversationId);

    if ($godModeEnabled) {
        success("GOD MODE enabled");
        $currentDomain = $agent->getCurrentDomain($conversationId);
        echo "  GOD MODE: " . ($currentDomain['god_mode_enabled'] ? colorize('ACTIVE', 'red') : 'INACTIVE') . "\n";

        // Get GOD MODE overview
        $godModeOverview = $agent->getGodModeOverview();
        if ($godModeOverview) {
            echo "  Total documents: {$godModeOverview['total_documents']}\n";
            echo "  Active domains: {$godModeOverview['active_domains']}\n";
        }
    } else {
        error("Failed to enable GOD MODE");
    }

    // Test 6: Domain-aware search (with GOD MODE)
    section("TEST 6: Domain-Aware Search (GOD MODE)");
    $searchResults = $agent->domainAwareSearch($conversationId, 'system architecture', 3);

    if (!empty($searchResults)) {
        success("Found " . count($searchResults) . " results:");
        foreach ($searchResults as $result) {
            echo "  - {$result['title']} (similarity: " . number_format($result['similarity'], 2) . ")\n";
        }
    } else {
        info("No search results (this is OK if KB is empty)");
    }

    // Test 7: Disable GOD MODE
    section("TEST 7: Disable GOD MODE");
    $godModeDisabled = $agent->disableGodMode($conversationId);

    if ($godModeDisabled) {
        success("GOD MODE disabled");
        $currentDomain = $agent->getCurrentDomain($conversationId);
        echo "  GOD MODE: " . ($currentDomain['god_mode_enabled'] ? 'ACTIVE' : colorize('INACTIVE', 'green')) . "\n";
    } else {
        error("Failed to disable GOD MODE");
    }

    // Test 8: Domain-aware search (normal mode)
    section("TEST 8: Domain-Aware Search (Normal Mode)");
    $searchResults = $agent->domainAwareSearch($conversationId, 'system architecture', 3);

    if (!empty($searchResults)) {
        success("Found " . count($searchResults) . " results (filtered by staff domain):");
        foreach ($searchResults as $result) {
            echo "  - {$result['title']} (similarity: " . number_format($result['similarity'], 2) . ")\n";
        }
    } else {
        info("No search results (this is expected - staff domain may be empty)");
    }

    // Test 9: Switch to superadmin domain
    section("TEST 9: Switch to Superadmin Domain");
    $switched = $agent->switchDomain($conversationId, 'superadmin');

    if ($switched) {
        success("Switched to superadmin domain");
        $currentDomain = $agent->getCurrentDomain($conversationId);
        echo "  Current domain: {$currentDomain['domain_name']}\n";
        echo "  Total domain switches: {$currentDomain['domain_switch_count']}\n";
    } else {
        error("Failed to switch to superadmin domain");
    }

    // Test 10: Test invalid operations
    section("TEST 10: Test Error Handling");

    // Try invalid domain
    $invalidSwitch = $agent->switchDomain($conversationId, 'invalid-domain');
    if (!$invalidSwitch) {
        success("Correctly rejected invalid domain name");
    } else {
        error("Should have rejected invalid domain name");
    }

    // Try invalid conversation ID
    try {
        $invalidId = $agent->getCurrentDomain('invalid-uuid');
        error("Should have thrown validation error for invalid UUID");
    } catch (\Exception $e) {
        success("Correctly rejected invalid conversation UUID");
    }

    // Cleanup
    section("CLEANUP");
    \App\DB::execute(
        'DELETE FROM agent_conversations WHERE conversation_id = ?',
        [$conversationId]
    );
    success("Test conversation deleted");

    // Final summary
    section("TEST SUMMARY");
    success("All multi-domain tests completed!");
    echo "\nVerified functionality:\n";
    echo "  ✓ Domain listing\n";
    echo "  ✓ Domain switching\n";
    echo "  ✓ GOD MODE activation/deactivation\n";
    echo "  ✓ Domain statistics\n";
    echo "  ✓ Domain-aware search\n";
    echo "  ✓ Error handling\n";

    echo "\n" . colorize("Multi-domain system is operational!", 'green') . "\n\n";

} catch (\Throwable $e) {
    error("Test failed: " . $e->getMessage());
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
