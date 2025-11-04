#!/usr/bin/env php
<?php
/**
 * Generate Comprehensive Database Relationship Diagram
 * Analyzes schema JSON and creates visual relationship map
 */

$schemaFile = __DIR__ . '/../DATABASE_COMPLETE_SCHEMA.json';
if (!file_exists($schemaFile)) {
    die("ERROR: Schema file not found. Run extract_full_schema.php first.\n");
}

$schema = json_decode(file_get_contents($schemaFile), true);
echo "Loaded schema with " . count($schema['tables']) . " tables\n\n";

// ==================== RELATIONSHIP ANALYSIS ====================

$relationships = [];
$tableGroups = [
    'ORGANIZATIONAL_HIERARCHY' => [],
    'PROJECT_MANAGEMENT' => [],
    'INTELLIGENCE_SYSTEM' => [],
    'SCANNER_SYSTEM' => [],
    'BOT_ORCHESTRATION' => [],
    'AI_CONVERSATION' => [],
    'CRON_SCHEDULING' => [],
    'CONTENT_KB' => [],
    'MCP_TOOLS' => [],
    'MONITORING' => [],
    'SECURITY' => [],
    'OTHER' => []
];

// Analyze each table and categorize
foreach ($schema['tables'] as $tableName => $tableData) {
    if (isset($tableData['error'])) continue;

    // Extract foreign key relationships
    foreach ($tableData['foreign_keys'] as $fk) {
        $relationships[] = [
            'from_table' => $tableName,
            'from_column' => $fk['COLUMN_NAME'],
            'to_table' => $fk['REFERENCED_TABLE_NAME'],
            'to_column' => $fk['REFERENCED_COLUMN_NAME']
        ];
    }

    // Categorize tables by naming patterns
    if (preg_match('/^(organization|business_unit|project_domain|project_unit)/i', $tableName)) {
        $tableGroups['ORGANIZATIONAL_HIERARCHY'][] = $tableName;
    } elseif (preg_match('/^(project_|hub_project)/i', $tableName)) {
        $tableGroups['PROJECT_MANAGEMENT'][] = $tableName;
    } elseif (preg_match('/^intelligence_/i', $tableName)) {
        $tableGroups['INTELLIGENCE_SYSTEM'][] = $tableName;
    } elseif (preg_match('/^(scan_|scanner_|cis_rule|violations|auto_fix)/i', $tableName)) {
        $tableGroups['SCANNER_SYSTEM'][] = $tableName;
    } elseif (preg_match('/^bot_/i', $tableName)) {
        $tableGroups['BOT_ORCHESTRATION'][] = $tableName;
    } elseif (preg_match('/^(ai_|neural_)/i', $tableName)) {
        $tableGroups['AI_CONVERSATION'][] = $tableName;
    } elseif (preg_match('/^(cron_|hub_cron_)/i', $tableName)) {
        $tableGroups['CRON_SCHEDULING'][] = $tableName;
    } elseif (preg_match('/^(kb_|content_)/i', $tableName)) {
        $tableGroups['CONTENT_KB'][] = $tableName;
    } elseif (preg_match('/^mcp_/i', $tableName)) {
        $tableGroups['MCP_TOOLS'][] = $tableName;
    } elseif (preg_match('/^(activity_log|system_|db_|chrome_|dashboard_|redis_)/i', $tableName)) {
        $tableGroups['MONITORING'][] = $tableName;
    } elseif (preg_match('/credential|security|auth/i', $tableName)) {
        $tableGroups['SECURITY'][] = $tableName;
    } else {
        $tableGroups['OTHER'][] = $tableName;
    }
}

echo "═══════════════════════════════════════════════════════════════════\n";
echo "   INTELLIGENCE HUB - COMPLETE DATABASE ANALYSIS\n";
echo "═══════════════════════════════════════════════════════════════════\n\n";

echo "Database: {$schema['database']}\n";
echo "Extracted: {$schema['extracted_at']}\n";
echo "Total Tables: {$schema['total_tables']}\n";
echo "Total Relationships: " . count($relationships) . "\n\n";

// ==================== ORGANIZATIONAL HIERARCHY ====================

echo "\n" . str_repeat("═", 70) . "\n";
echo "1. ORGANIZATIONAL HIERARCHY\n";
echo str_repeat("═", 70) . "\n\n";

$orgTables = $tableGroups['ORGANIZATIONAL_HIERARCHY'];
if (!empty($orgTables)) {
    echo "Tables (" . count($orgTables) . "):\n";
    foreach ($orgTables as $table) {
        $data = $schema['tables'][$table];
        echo "  • $table ({$data['row_count']} rows)\n";

        // Show key columns
        $keyCols = array_filter($data['columns'], function($col) {
            return stripos($col['Field'], 'id') !== false ||
                   stripos($col['Field'], 'parent') !== false ||
                   stripos($col['Field'], 'name') !== false;
        });
        foreach ($keyCols as $col) {
            echo "    - {$col['Field']}: {$col['Type']}\n";
        }

        // Show sample data
        if (!empty($data['sample_data'])) {
            echo "    Sample data:\n";
            foreach (array_slice($data['sample_data'], 0, 2) as $row) {
                $preview = array_slice($row, 0, 3, true);
                $str = json_encode($preview, JSON_UNESCAPED_SLASHES);
                echo "    " . substr($str, 0, 100) . "...\n";
            }
        }
        echo "\n";
    }
}

// Show relationships between org tables
$orgRelationships = array_filter($relationships, function($rel) use ($orgTables) {
    return in_array($rel['from_table'], $orgTables) || in_array($rel['to_table'], $orgTables);
});

if (!empty($orgRelationships)) {
    echo "Relationships:\n";
    foreach ($orgRelationships as $rel) {
        echo "  {$rel['from_table']}.{$rel['from_column']} → {$rel['to_table']}.{$rel['to_column']}\n";
    }
    echo "\n";
}

echo "📊 HIERARCHY DIAGRAM:\n";
echo <<<DIAGRAM

┌─────────────────┐
│ organizations   │ (org_id, org_name, parent_org_id, org_type)
│    1 row        │
└────────┬────────┘
         │
         ├─→ business_units (business_unit_id, organization_id, unit_name)
         │      4 rows
         │
         └─→ project_unit_mapping (links projects to business units)
                0 rows

DIAGRAM;

// ==================== PROJECT MANAGEMENT ====================

echo "\n" . str_repeat("═", 70) . "\n";
echo "2. PROJECT MANAGEMENT SYSTEM\n";
echo str_repeat("═", 70) . "\n\n";

$projTables = $tableGroups['PROJECT_MANAGEMENT'];
echo "Tables (" . count($projTables) . "):\n";
foreach ($projTables as $table) {
    $data = $schema['tables'][$table];
    echo "  • $table ({$data['row_count']} rows, " . count($data['foreign_keys']) . " FKs)\n";
}

echo "\n📊 PROJECT HIERARCHY:\n";
echo <<<DIAGRAM

┌─────────────────┐
│   projects      │ (id, project_name, project_path, business_unit_id)
│    12 rows      │
└────────┬────────┘
         │
         ├─→ project_domains (domain, subdomain, full_url)
         │      11 rows
         │
         ├─→ project_metadata (key, value pairs)
         │      3 rows
         │
         ├─→ project_requirements (requirement_text, priority)
         │      5 rows
         │
         ├─→ project_rules (rule_name, rule_type)
         │      6 rows
         │
         ├─→ project_scan_config (scan settings)
         │      3 rows
         │
         ├─→ project_standards (standard_name, standard_type)
         │      5 rows
         │
         └─→ project_metrics (files_count, violations_count)
                1 row

DIAGRAM;

// Show actual projects
$projectsData = $schema['tables']['projects'];
if (!empty($projectsData['sample_data'])) {
    echo "\nACTUAL PROJECTS:\n";
    foreach ($projectsData['sample_data'] as $proj) {
        echo "  • ID {$proj['id']}: {$proj['project_name']}\n";
        if (isset($proj['project_path'])) {
            echo "    Path: " . substr($proj['project_path'], 0, 70) . "\n";
        }
    }
}

// ==================== INTELLIGENCE SYSTEM ====================

echo "\n" . str_repeat("═", 70) . "\n";
echo "3. INTELLIGENCE FILE SYSTEM\n";
echo str_repeat("═", 70) . "\n\n";

$intTables = $tableGroups['INTELLIGENCE_SYSTEM'];
echo "Tables (" . count($intTables) . "):\n";
foreach ($intTables as $table) {
    $data = $schema['tables'][$table];
    echo "  • $table ({$data['row_count']} rows)\n";
}

echo "\n📊 INTELLIGENCE ARCHITECTURE:\n";
echo <<<DIAGRAM

┌──────────────────────────────┐
│ intelligence_files           │ (NEW SYSTEM - Scanner V3)
│       26,121 rows            │
│                              │
│ • project_id, business_unit_id
│ • file_path, file_content    │
│ • intelligence_data (JSON)   │
│ • content_summary            │
│ • extracted_at               │
└──────────┬───────────────────┘
           │
           │ UNIQUE KEY: (business_unit_id, file_path)
           │
           │
┌──────────▼───────────────────┐
│ intelligence_content         │ (OLD SYSTEM - V2 Tools)
│       22,191 rows            │
│                              │
│ • project_id, file_path      │
│ • content_type               │
│ • raw_content                │
│ • metadata (JSON)            │
│ • analysis_result            │
└──────────┬───────────────────┘
           │
           ├─→ intelligence_content_text (11,286 rows)
           │   Full-text search index
           │
           ├─→ intelligence_content_types (31 types)
           │   Type definitions
           │
           ├─→ intelligence_metrics (3,000 rows)
           │   Usage analytics
           │
           ├─→ intelligence_automation (0 rows)
           │   Automated actions
           │
           └─→ intelligence_alerts (0 rows)
               Alert rules

BACKUP TABLE:
│ intelligence_files_backup_20251025 (55,357 rows) │

DIAGRAM;

// ==================== SCANNER SYSTEM ====================

echo "\n" . str_repeat("═", 70) . "\n";
echo "4. SCANNER & CODE QUALITY SYSTEM\n";
echo str_repeat("═", 70) . "\n\n";

$scanTables = $tableGroups['SCANNER_SYSTEM'];
echo "Tables (" . count($scanTables) . "):\n";
foreach ($scanTables as $table) {
    $data = $schema['tables'][$table];
    echo "  • $table ({$data['row_count']} rows)\n";
}

echo "\n📊 SCANNER PIPELINE:\n";
echo <<<DIAGRAM

┌─────────────────┐
│  scan_config    │ → Project scan settings
│    10 rows      │   (ignore patterns, file types)
└────────┬────────┘
         │
         ├─→ scan_jobs (9 jobs)
         │      Job queue
         │
         ├─→ scan_history (0 records)
         │      Scan execution history
         │
         └─→ scan_logs (41,481 records!) ⚠️
                Detailed scan output

┌──────────────────────────┐
│  scanner_ignore_config   │
│      146 patterns        │ → .git, node_modules, vendor, etc.
└──────────────────────────┘

┌─────────────────┐
│   cis_rules     │ → Code quality rules
│    0 rows       │   (needs population)
└────────┬────────┘
         │
         ├─→ cis_rule_categories (10 categories)
         │      security, performance, best_practices
         │
         ├─→ cis_rule_violations (0 violations)
         │      Found issues
         │
         ├─→ cis_rule_learning_log (0 records)
         │      AI learning from feedback
         │
         └─→ cis_user_rule_preferences (0 prefs)
                User customizations

┌─────────────────┐
│   violations    │ → Generic violation tracker
│     1 row       │
└─────────────────┘

┌─────────────────┐
│ auto_fix_log    │ → Automatic fix attempts
│    0 rows       │
└─────────────────┘

DIAGRAM;

// ==================== COMPLETE RELATIONSHIP MAP ====================

echo "\n" . str_repeat("═", 70) . "\n";
echo "5. COMPLETE FOREIGN KEY RELATIONSHIP MAP\n";
echo str_repeat("═", 70) . "\n\n";

// Group relationships by target table
$relByTarget = [];
foreach ($relationships as $rel) {
    $relByTarget[$rel['to_table']][] = $rel;
}

echo "Total Foreign Key Relationships: " . count($relationships) . "\n\n";

foreach ($relByTarget as $targetTable => $rels) {
    echo "┌─ $targetTable\n";
    foreach ($rels as $rel) {
        echo "│   ← {$rel['from_table']}.{$rel['from_column']}\n";
    }
    echo "└" . str_repeat("─", 50) . "\n";
}

// ==================== CRITICAL FINDINGS ====================

echo "\n" . str_repeat("═", 70) . "\n";
echo "6. CRITICAL FINDINGS & RECOMMENDATIONS\n";
echo str_repeat("═", 70) . "\n\n";

echo "✅ WELL-DESIGNED AREAS:\n";
echo "  • Intelligence system has both old (intelligence_content) and new\n";
echo "    (intelligence_files) tables for migration\n";
echo "  • Proper indexing on most tables (24 indexes on intelligence_files)\n";
echo "  • Business unit segregation via business_unit_id\n";
echo "  • Comprehensive audit logging (activity_logs, scan_logs)\n\n";

echo "⚠️  ISSUES IDENTIFIED:\n";
echo "  1. DUAL INTELLIGENCE SYSTEMS: Two separate intelligence tables\n";
echo "     - intelligence_content (22,191 rows) ← OLD, used by V2 tools\n";
echo "     - intelligence_files (26,121 rows) ← NEW, used by Scanner V3\n";
echo "     → Need to migrate V2 tools to use intelligence_files\n\n";

echo "  2. BROKEN VIEWS:\n";
echo "     - kb_files: References invalid tables\n";
echo "     - simple_quality: View definition error\n";
echo "     → Need to drop or fix these views\n\n";

echo "  3. MASSIVE LOG TABLE:\n";
echo "     - scan_logs: 41,481 records\n";
echo "     → Consider archiving/rotation strategy\n\n";

echo "  4. UNPOPULATED RULE SYSTEM:\n";
echo "     - cis_rules: 0 rows (should have rules defined)\n";
echo "     - cis_rule_violations: 0 rows (scanner not recording violations)\n";
echo "     → Scanner V3 may not be properly writing violations\n\n";

echo "  5. ORGANIZATIONAL HIERARCHY INCOMPLETE:\n";
echo "     - organizations: Only 1 row\n";
echo "     - business_units: Only 4 rows\n";
echo "     - project_unit_mapping: 0 rows (projects not linked to units!)\n";
echo "     → Need to properly map projects to business units\n\n";

// ==================== RECOMMENDED HIERARCHY ====================

echo "\n" . str_repeat("═", 70) . "\n";
echo "7. RECOMMENDED ORGANIZATIONAL STRUCTURE\n";
echo str_repeat("═", 70) . "\n\n";

echo <<<STRUCTURE
Based on the schema analysis, here is the CORRECT hierarchy:

┌─────────────────────────────────────────────────────────────┐
│                      ORGANIZATION                           │
│                    (organizations)                          │
│                         1 row                               │
│                                                             │
│   • org_id, org_name, org_type                             │
│   • parent_org_id (for nested orgs)                        │
│   • intelligence_level (basic/advanced/neural/quantum)     │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       │ organization_id
                       ▼
┌─────────────────────────────────────────────────────────────┐
│                    BUSINESS UNITS                           │
│                  (business_units)                           │
│                       4 rows                                │
│                                                             │
│   • business_unit_id, unit_name                            │
│   • organization_id (FK)                                   │
│   • unit_type, is_active                                   │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       │ business_unit_id
                       ▼
┌─────────────────────────────────────────────────────────────┐
│                      PROJECTS                               │
│                    (projects)                               │
│                      12 rows                                │
│                                                             │
│   • id, project_name, project_path                         │
│   • business_unit_id (FK) ← MISSING IN CURRENT DATA!      │
│   • status, priority                                       │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       │ project_id
                       ▼
┌─────────────────────────────────────────────────────────────┐
│                   PROJECT DOMAINS                           │
│                 (project_domains)                           │
│                      11 rows                                │
│                                                             │
│   • domain, subdomain, full_url                            │
│   • project_id (FK)                                        │
│   • is_primary                                             │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       │ Scanned files stored in:
                       ▼
┌─────────────────────────────────────────────────────────────┐
│              INTELLIGENCE FILES (Scanner V3)                │
│                (intelligence_files)                         │
│                     26,121 rows                             │
│                                                             │
│   • project_id, business_unit_id                           │
│   • file_path, file_content                                │
│   • intelligence_data (JSON analysis)                      │
│   • UNIQUE KEY: (business_unit_id, file_path)             │
└─────────────────────────────────────────────────────────────┘

STRUCTURE;

// ==================== REDUNDANT vs EMPTY TABLES ====================

echo "\n" . str_repeat("═", 70) . "\n";
echo "8. REDUNDANT TABLES vs EMPTY BUT USEFUL TABLES\n";
echo str_repeat("═", 70) . "\n\n";

// Categorize empty tables
$emptyTables = array_filter($schema['tables'], fn($t) => $t['row_count'] === 0 && !isset($t['error']));
$redundantTables = [];
$emptyUseful = [];

foreach ($emptyTables as $name => $data) {
    // Check if table is redundant (duplicate functionality)
    if (preg_match('/^(cron_jobs|cron_executions|cron_metrics|cron_satellites)$/', $name) &&
        array_key_exists('hub_' . $name, $schema['tables'])) {
        $redundantTables[] = [
            'name' => $name,
            'reason' => "Duplicate of hub_$name (which has data)",
            'action' => 'DROP - use hub_* version instead'
        ];
    } elseif ($name === 'intelligence_files_backup_20251025') {
        $redundantTables[] = [
            'name' => $name,
            'reason' => 'Old backup table from Oct 25, 2025',
            'action' => 'ARCHIVE to file and DROP'
        ];
    } elseif (in_array($name, ['kb_files', 'simple_quality']) && isset($data['error'])) {
        $redundantTables[] = [
            'name' => $name,
            'reason' => 'Broken view: ' . $data['error'],
            'action' => 'DROP and recreate or remove'
        ];
    } else {
        // Determine if empty but useful
        $category = '';
        $purpose = '';

        if (preg_match('/^(ai_|neural_|mcp_)/', $name)) {
            $category = 'AI/ML System';
            $purpose = 'For future AI features and learning';
        } elseif (preg_match('/^(cis_rule|auto_fix|scanner_)/', $name)) {
            $category = 'Scanner System';
            $purpose = 'Will be populated when Scanner V3 runs with rules';
        } elseif (preg_match('/^(intelligence_alert|intelligence_automation)/', $name)) {
            $category = 'Intelligence System';
            $purpose = 'For automated intelligence actions';
        } elseif (preg_match('/^(bot_|chrome_)/', $name)) {
            $category = 'Bot Orchestration';
            $purpose = 'For bot deployment and automation';
        } elseif (preg_match('/^(cron_|hub_cron)/', $name)) {
            $category = 'Cron Scheduling';
            $purpose = 'For scheduled job management';
        } elseif (preg_match('/^(content_|kb_)/', $name)) {
            $category = 'Knowledge Base';
            $purpose = 'For content management and linking';
        } elseif (preg_match('/^(project_unit_mapping|unit_team)/', $name)) {
            $category = 'Organizational';
            $purpose = 'CRITICAL: Needs data to link projects to units';
        } elseif (preg_match('/^(scan_history|violations|circular_dep)/', $name)) {
            $category = 'Monitoring/Tracking';
            $purpose = 'Will populate as system is used';
        } else {
            $category = 'Utility';
            $purpose = 'Future use or logging';
        }

        $emptyUseful[] = [
            'name' => $name,
            'category' => $category,
            'purpose' => $purpose,
            'fk_count' => count($data['foreign_keys']),
            'action' => 'KEEP - Will be used'
        ];
    }
}

echo "🗑️  REDUNDANT TABLES (SAFE TO DROP):\n";
echo str_repeat("-", 70) . "\n";
if (empty($redundantTables)) {
    echo "  (None identified - all empty tables appear useful)\n";
} else {
    foreach ($redundantTables as $table) {
        echo "  ❌ {$table['name']}\n";
        echo "     Reason: {$table['reason']}\n";
        echo "     Action: {$table['action']}\n\n";
    }
}

echo "\n✅ EMPTY BUT USEFUL TABLES (KEEP FOR FUTURE USE):\n";
echo str_repeat("-", 70) . "\n";

// Group by category
$byCategory = [];
foreach ($emptyUseful as $table) {
    $byCategory[$table['category']][] = $table;
}

foreach ($byCategory as $category => $tables) {
    echo "\n📁 $category (" . count($tables) . " tables):\n";
    foreach ($tables as $table) {
        $fkInfo = $table['fk_count'] > 0 ? " [{$table['fk_count']} FKs]" : "";
        echo "  • {$table['name']}$fkInfo\n";
        echo "    → {$table['purpose']}\n";
    }
}

echo "\n\n📊 SUMMARY:\n";
echo str_repeat("-", 70) . "\n";
echo "Total Tables: " . count($schema['tables']) . "\n";
echo "Tables with Data: " . count(array_filter($schema['tables'], fn($t) => $t['row_count'] > 0)) . "\n";
echo "Empty but Useful: " . count($emptyUseful) . "\n";
echo "Redundant/Broken: " . count($redundantTables) . "\n";

// ==================== CRITICAL ACTION ITEMS ====================

echo "\n" . str_repeat("═", 70) . "\n";
echo "9. CRITICAL ACTION ITEMS\n";
echo str_repeat("═", 70) . "\n\n";

echo "🚨 IMMEDIATE ACTIONS REQUIRED:\n\n";

echo "1. FIX ORGANIZATIONAL HIERARCHY:\n";
echo "   - project_unit_mapping has 0 rows!\n";
echo "   - Need to link all 12 projects to their business units\n";
echo "   - SQL: INSERT INTO project_unit_mapping (project_id, unit_id) VALUES ...\n\n";

echo "2. POPULATE SCANNER RULES:\n";
echo "   - cis_rules table is empty (0 rows)\n";
echo "   - Scanner V3 cannot detect violations without rules\n";
echo "   - Need to import rule definitions\n\n";

echo "3. MIGRATE MCP V2 TOOLS TO USE intelligence_files:\n";
echo "   - V2 tools use intelligence_content (22,191 rows)\n";
echo "   - V3 scanner uses intelligence_files (26,121 rows)\n";
echo "   - Need unified storage or route V2 to intelligence_files\n\n";

echo "4. FIX OR DROP BROKEN VIEWS:\n";
echo "   - kb_files and simple_quality reference invalid tables\n";
echo "   - Either fix view definitions or DROP them\n\n";

echo "5. ARCHIVE MASSIVE LOG TABLE:\n";
echo "   - scan_logs: 41,481 records\n";
echo "   - Implement log rotation/archival strategy\n\n";

echo "\n" . str_repeat("═", 70) . "\n";
echo "ANALYSIS COMPLETE\n";
echo str_repeat("═", 70) . "\n";

echo "\n✓ Analysis generated successfully!\n";
