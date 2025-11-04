#!/usr/bin/env php
<?php
/**
 * Extract Complete Database Schema and Sample Data
 * Generates comprehensive relational database documentation
 */

$config = require __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "Connected to Intelligence Hub database: {$config['database']}\n\n";

    // Get all tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Total tables found: " . count($tables) . "\n";
    echo str_repeat("=", 80) . "\n\n";

    $output = [
        'database' => $config['database'],
        'total_tables' => count($tables),
        'extracted_at' => date('Y-m-d H:i:s'),
        'tables' => []
    ];

    foreach ($tables as $table) {
        echo "Analyzing table: $table\n";

        $tableData = [
            'name' => $table,
            'columns' => [],
            'indexes' => [],
            'foreign_keys' => [],
            'sample_data' => [],
            'row_count' => 0,
            'is_view' => false,
            'error' => null
        ];

        try {
            // Get column structure
            $columns = $pdo->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
            $tableData['columns'] = $columns;

            // Check if it's a view
            $isView = $pdo->query("
                SELECT TABLE_TYPE
                FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA = '{$config['database']}'
                AND TABLE_NAME = '$table'
            ")->fetchColumn();
            $tableData['is_view'] = ($isView === 'VIEW');

            // Get indexes (skip for views)
            if (!$tableData['is_view']) {
                $indexes = $pdo->query("SHOW INDEX FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
                $tableData['indexes'] = $indexes;

                // Get foreign keys
                $fkeys = $pdo->query("
                    SELECT
                        COLUMN_NAME,
                        REFERENCED_TABLE_NAME,
                        REFERENCED_COLUMN_NAME
                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = '{$config['database']}'
                    AND TABLE_NAME = '$table'
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ")->fetchAll(PDO::FETCH_ASSOC);
                $tableData['foreign_keys'] = $fkeys;
            }

            // Get row count
            $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            $tableData['row_count'] = (int)$count;

            // Get sample data (up to 5 rows)
            if ($count > 0) {
                $samples = $pdo->query("SELECT * FROM `$table` LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
                $tableData['sample_data'] = $samples;
            }

            $output['tables'][$table] = $tableData;
            $type = $tableData['is_view'] ? 'VIEW' : 'TABLE';
            echo "  ✓ [$type] Columns: " . count($columns) . " | Indexes: " . count($tableData['indexes']) . " | FKs: " . count($tableData['foreign_keys']) . " | Rows: $count\n";

        } catch (Exception $e) {
            $tableData['error'] = $e->getMessage();
            $output['tables'][$table] = $tableData;
            echo "  ⚠ ERROR: " . $e->getMessage() . "\n";
        }
    }

    // Save to JSON file
    $outputFile = __DIR__ . '/../DATABASE_COMPLETE_SCHEMA.json';
    file_put_contents($outputFile, json_encode($output, JSON_PRETTY_PRINT));
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "✓ Complete schema saved to: $outputFile\n";
    echo "File size: " . number_format(filesize($outputFile)) . " bytes\n";

    // Generate summary report
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "DATABASE SUMMARY\n";
    echo str_repeat("=", 80) . "\n";

    $totalRows = array_sum(array_column($output['tables'], 'row_count'));
    $tablesWithData = count(array_filter($output['tables'], fn($t) => $t['row_count'] > 0));

    echo "Total Tables: " . count($tables) . "\n";
    echo "Tables with Data: $tablesWithData\n";
    echo "Empty Tables: " . (count($tables) - $tablesWithData) . "\n";
    echo "Total Rows: " . number_format($totalRows) . "\n";

    // Top 10 largest tables
    echo "\nTop 10 Largest Tables:\n";
    $sorted = $output['tables'];
    uasort($sorted, fn($a, $b) => $b['row_count'] - $a['row_count']);
    $top10 = array_slice($sorted, 0, 10, true);
    foreach ($top10 as $name => $data) {
        echo sprintf("  - %-40s %s rows\n", $name, number_format($data['row_count']));
    }

    echo "\n✓ Schema extraction complete!\n";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
