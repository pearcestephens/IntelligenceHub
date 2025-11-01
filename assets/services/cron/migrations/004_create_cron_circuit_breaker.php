<?php
/**
 * Migration: Create cron_circuit_breaker table
 * 
 * Stores circuit breaker state for each task (database backend)
 * 
 * @version 1.0.0
 * @created 2025-10-21
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/smart-cron/bootstrap.php';

// Connect to database
$db = getMysqliConnection();

if (!$db) {
    die("ERROR: Could not connect to database\n");
}

echo "Creating cron_circuit_breaker table...\n";

$sql = "CREATE TABLE IF NOT EXISTS cron_circuit_breaker (
    task_name VARCHAR(255) PRIMARY KEY,
    state ENUM('closed', 'open', 'half_open') NOT NULL DEFAULT 'closed',
    failures INT NOT NULL DEFAULT 0,
    last_failure_time BIGINT DEFAULT NULL,
    opened_at BIGINT DEFAULT NULL,
    last_success_time BIGINT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_state (state),
    INDEX idx_updated_at (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Circuit breaker state storage'";

if ($db->query($sql)) {
    echo "✓ cron_circuit_breaker table created successfully\n";
} else {
    echo "✗ ERROR: " . $db->error . "\n";
    exit(1);
}

// Check if table has data
$result = $db->query("SELECT COUNT(*) as count FROM cron_circuit_breaker");
$row = $result->fetch_assoc();
echo "  Current records: {$row['count']}\n";

$db->close();
echo "\nMigration completed successfully!\n";
