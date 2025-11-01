<?php
/**
 * CIS KB DATA VALIDATION & QUALITY AUDIT
 * 
 * Comprehensive analysis of current KB data quality
 * Identifies garbage data, inconsistencies, and quality issues
 * 
 * @package CIS\KB\Validation
 * @version 1.0.0
 */

declare(strict_types=1);

echo "🔍 CIS KB DATA VALIDATION & QUALITY AUDIT\n";
echo str_repeat("=", 60) . "\n";
echo "Analyzing current KB data for quality and consistency...\n\n";

$startTime = microtime(true);

// Database connection
$host = '127.0.0.1';
$dbname = 'hdgwrzntwa';
$username = 'hdgwrzntwa';
$password = 'bFUdRjh4Jx';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Connected to KB database\n\n";
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage() . "\n");
}

$validationStats = [
    'total_issues' => 0,
    'critical_issues' => 0,
    'warnings' => 0,
    'data_quality_score' => 0,
    'garbage_detected' => 0,
    'inconsistencies' => 0
];

echo "📊 DATA VALIDATION REPORT\n";
echo str_repeat("=", 60) . "\n\n";

// 1. BASIC DATA INTEGRITY CHECK
echo "🔧 1. BASIC DATA INTEGRITY\n";
echo str_repeat("-", 40) . "\n";

try {
    $tables = [
        'ecig_kb_files',
        'ecig_kb_simple_search', 
        'ecig_kb_categories',
        'ecig_kb_file_organization',
        'ecig_kb_cognitive_analysis',
        'ecig_kb_simple_quality'
    ];
    
    $tableCounts = [];
    foreach ($tables as $table) {
        try {
            $count = $pdo->query("SELECT COUNT(*) as count FROM $table")->fetch()['count'];
            $tableCounts[$table] = $count;
            echo "✅ $table: " . number_format($count) . " records\n";
        } catch (PDOException $e) {
            echo "❌ $table: MISSING or CORRUPTED\n";
            $validationStats['critical_issues']++;
        }
    }
    
    // Check for data consistency between tables
    if (isset($tableCounts['ecig_kb_files']) && isset($tableCounts['ecig_kb_simple_search'])) {
        $fileDiff = abs($tableCounts['ecig_kb_files'] - $tableCounts['ecig_kb_simple_search']);
        if ($fileDiff > 100) {
            echo "⚠️  WARNING: Large mismatch between files and search records ($fileDiff difference)\n";
            $validationStats['warnings']++;
        } else {
            echo "✅ File/Search record counts are consistent\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ CRITICAL: Basic integrity check failed: " . $e->getMessage() . "\n";
    $validationStats['critical_issues']++;
}

echo "\n";

// 2. FILE PATH VALIDATION
echo "📁 2. FILE PATH QUALITY ANALYSIS\n";
echo str_repeat("-", 40) . "\n";

try {
    // Check for suspicious file paths
    $suspiciousFiles = $pdo->query("
        SELECT file_name, file_path, COUNT(*) as count
        FROM ecig_kb_files 
        WHERE file_path LIKE '%/tmp/%' 
           OR file_path LIKE '%/cache/%'
           OR file_path LIKE '%/logs/%'
           OR file_path LIKE '%/vendor/%'
           OR file_path LIKE '%/node_modules/%'
           OR file_path LIKE '%/.git/%'
           OR file_name LIKE '%.tmp'
           OR file_name LIKE '%.log'
           OR file_name LIKE '%.cache'
           OR file_name = ''
           OR file_path = ''
        GROUP BY file_name, file_path
        ORDER BY count DESC
        LIMIT 20
    ")->fetchAll();
    
    if (count($suspiciousFiles) > 0) {
        echo "🚨 GARBAGE DETECTED: " . count($suspiciousFiles) . " suspicious files found:\n";
        foreach ($suspiciousFiles as $file) {
            echo "   ❌ {$file['file_name']} ({$file['file_path']}) - {$file['count']} occurrences\n";
            $validationStats['garbage_detected']++;
        }
    } else {
        echo "✅ No suspicious file paths detected\n";
    }
    
    // Check for duplicate file paths
    $duplicatePaths = $pdo->query("
        SELECT file_path, COUNT(*) as count
        FROM ecig_kb_files 
        WHERE file_path != ''
        GROUP BY file_path
        HAVING count > 1
        ORDER BY count DESC
        LIMIT 10
    ")->fetchAll();
    
    if (count($duplicatePaths) > 0) {
        echo "\n⚠️  DUPLICATES: " . count($duplicatePaths) . " duplicate file paths:\n";
        foreach ($duplicatePaths as $dup) {
            echo "   🔄 {$dup['file_path']} - {$dup['count']} duplicates\n";
            $validationStats['inconsistencies']++;
        }
    } else {
        echo "✅ No duplicate file paths found\n";
    }
    
} catch (Exception $e) {
    echo "❌ File path validation failed: " . $e->getMessage() . "\n";
    $validationStats['critical_issues']++;
}

echo "\n";

// 3. CONTENT QUALITY ANALYSIS
echo "📝 3. CONTENT QUALITY ANALYSIS\n";
echo str_repeat("-", 40) . "\n";

try {
    // Check for empty or minimal content
    $emptyContent = $pdo->query("
        SELECT COUNT(*) as count
        FROM ecig_kb_files 
        WHERE content = '' OR content IS NULL OR LENGTH(content) < 10
    ")->fetch()['count'];
    
    if ($emptyContent > 0) {
        echo "⚠️  WARNING: $emptyContent files with empty or minimal content\n";
        $validationStats['warnings']++;
    } else {
        echo "✅ All files have meaningful content\n";
    }
    
    // Check for binary/garbage content
    $binaryContent = $pdo->query("
        SELECT file_name, file_path, content
        FROM ecig_kb_files 
        WHERE content REGEXP '[[:cntrl:]][[:cntrl:]][[:cntrl:]]'
           OR content LIKE '%\\0%'
           OR content LIKE '%����%'
           OR LENGTH(content) - LENGTH(REPLACE(content, CHAR(0), '')) > 0
        LIMIT 10
    ")->fetchAll();
    
    if (count($binaryContent) > 0) {
        echo "🚨 BINARY/GARBAGE CONTENT DETECTED: " . count($binaryContent) . " files:\n";
        foreach ($binaryContent as $file) {
            echo "   ❌ {$file['file_name']} - Contains binary data\n";
            $validationStats['garbage_detected']++;
        }
    } else {
        echo "✅ No binary/garbage content detected\n";
    }
    
    // Check content length distribution
    $contentStats = $pdo->query("
        SELECT 
            AVG(LENGTH(content)) as avg_length,
            MIN(LENGTH(content)) as min_length,
            MAX(LENGTH(content)) as max_length,
            COUNT(CASE WHEN LENGTH(content) < 100 THEN 1 END) as very_small,
            COUNT(CASE WHEN LENGTH(content) > 100000 THEN 1 END) as very_large
        FROM ecig_kb_files
        WHERE content IS NOT NULL
    ")->fetch();
    
    echo "\n📈 CONTENT SIZE DISTRIBUTION:\n";
    echo "   • Average length: " . number_format((int)$contentStats['avg_length']) . " characters\n";
    echo "   • Min length: " . number_format($contentStats['min_length']) . " characters\n";
    echo "   • Max length: " . number_format($contentStats['max_length']) . " characters\n";
    echo "   • Very small files (<100 chars): " . $contentStats['very_small'] . "\n";
    echo "   • Very large files (>100K chars): " . $contentStats['very_large'] . "\n";
    
    if ($contentStats['very_small'] > ($tableCounts['ecig_kb_files'] * 0.2)) {
        echo "   ⚠️  WARNING: High number of very small files\n";
        $validationStats['warnings']++;
    }
    
} catch (Exception $e) {
    echo "❌ Content quality analysis failed: " . $e->getMessage() . "\n";
    $validationStats['critical_issues']++;
}

echo "\n";

// 4. SEARCH INDEX VALIDATION
echo "🔍 4. SEARCH INDEX QUALITY\n";
echo str_repeat("-", 40) . "\n";

try {
    // Check search terms quality
    $searchStats = $pdo->query("
        SELECT 
            COUNT(*) as total_records,
            COUNT(DISTINCT file_id) as unique_files,
            AVG(LENGTH(search_content)) as avg_content_length,
            AVG(LENGTH(search_terms)) as avg_terms_length,
            COUNT(CASE WHEN search_terms = '' THEN 1 END) as empty_terms,
            COUNT(CASE WHEN search_content = '' THEN 1 END) as empty_content
        FROM ecig_kb_simple_search
    ")->fetch();
    
    echo "📊 SEARCH INDEX STATISTICS:\n";
    echo "   • Total search records: " . number_format($searchStats['total_records']) . "\n";
    echo "   • Unique files indexed: " . number_format($searchStats['unique_files']) . "\n";
    echo "   • Avg content length: " . number_format((int)$searchStats['avg_content_length']) . " chars\n";
    echo "   • Avg terms length: " . number_format((int)$searchStats['avg_terms_length']) . " chars\n";
    echo "   • Empty search terms: " . $searchStats['empty_terms'] . "\n";
    echo "   • Empty search content: " . $searchStats['empty_content'] . "\n";
    
    if ($searchStats['empty_terms'] > 0 || $searchStats['empty_content'] > 0) {
        echo "   ⚠️  WARNING: Search index has empty entries\n";
        $validationStats['warnings']++;
    } else {
        echo "   ✅ Search index quality is good\n";
    }
    
    // Test search functionality
    $testSearches = ['php', 'function', 'class', 'database', 'api'];
    $workingSearches = 0;
    
    foreach ($testSearches as $term) {
        $results = $pdo->query("
            SELECT COUNT(*) as count 
            FROM ecig_kb_simple_search 
            WHERE MATCH(search_content, search_terms) AGAINST('$term' IN BOOLEAN MODE)
        ")->fetch()['count'];
        
        if ($results > 0) {
            $workingSearches++;
        }
    }
    
    $searchSuccessRate = ($workingSearches / count($testSearches)) * 100;
    echo "   🎯 Search functionality: $workingSearches/" . count($testSearches) . " tests passed ({$searchSuccessRate}%)\n";
    
    if ($searchSuccessRate < 80) {
        echo "   ⚠️  WARNING: Search functionality may be impaired\n";
        $validationStats['warnings']++;
    }
    
} catch (Exception $e) {
    echo "❌ Search index validation failed: " . $e->getMessage() . "\n";
    $validationStats['critical_issues']++;
}

echo "\n";

// 5. BUSINESS ORGANIZATION VALIDATION
echo "🏢 5. BUSINESS ORGANIZATION QUALITY\n";
echo str_repeat("-", 40) . "\n";

try {
    // Check category distribution
    $categoryStats = $pdo->query("
        SELECT 
            c.category_name,
            c.category_description,
            COUNT(o.file_id) as file_count,
            AVG(o.business_value_score) as avg_score
        FROM ecig_kb_categories c
        LEFT JOIN ecig_kb_file_organization o ON c.category_id = o.category_id
        GROUP BY c.category_id, c.category_name, c.category_description
        ORDER BY file_count DESC
    ")->fetchAll();
    
    echo "📋 CATEGORY DISTRIBUTION:\n";
    $totalOrganized = 0;
    foreach ($categoryStats as $cat) {
        $fileCount = $cat['file_count'];
        $avgScore = round($cat['avg_score'], 1);
        echo "   • {$cat['category_name']}: $fileCount files (avg score: $avgScore)\n";
        $totalOrganized += $fileCount;
    }
    
    $organizationRate = ($totalOrganized / max($tableCounts['ecig_kb_files'], 1)) * 100;
    echo "\n   📈 Organization rate: " . round($organizationRate, 1) . "% of files categorized\n";
    
    if ($organizationRate < 70) {
        echo "   ⚠️  WARNING: Low organization rate\n";
        $validationStats['warnings']++;
    } else {
        echo "   ✅ Good organization coverage\n";
    }
    
    // Check for orphaned organization records
    $orphanedOrg = $pdo->query("
        SELECT COUNT(*) as count
        FROM ecig_kb_file_organization o
        LEFT JOIN ecig_kb_files f ON o.file_id = f.file_id
        WHERE f.file_id IS NULL
    ")->fetch()['count'];
    
    if ($orphanedOrg > 0) {
        echo "   ⚠️  WARNING: $orphanedOrg orphaned organization records\n";
        $validationStats['inconsistencies']++;
    } else {
        echo "   ✅ No orphaned organization records\n";
    }
    
} catch (Exception $e) {
    echo "❌ Organization validation failed: " . $e->getMessage() . "\n";
    $validationStats['critical_issues']++;
}

echo "\n";

// 6. COGNITIVE ANALYSIS VALIDATION
echo "🧠 6. COGNITIVE INTELLIGENCE QUALITY\n";
echo str_repeat("-", 40) . "\n";

try {
    $cognitiveStats = $pdo->query("
        SELECT 
            element_type,
            COUNT(*) as count,
            AVG(confidence_score) as avg_confidence
        FROM ecig_kb_cognitive_analysis
        GROUP BY element_type
        ORDER BY count DESC
    ")->fetchAll();
    
    echo "🧪 COGNITIVE ELEMENTS:\n";
    $totalCognitive = 0;
    foreach ($cognitiveStats as $cog) {
        $count = $cog['count'];
        $confidence = round($cog['avg_confidence'], 1);
        echo "   • {$cog['element_type']}: " . number_format($count) . " elements (avg confidence: {$confidence}%)\n";
        $totalCognitive += $count;
    }
    
    echo "\n   📊 Total cognitive elements: " . number_format($totalCognitive) . "\n";
    
    // Check for low confidence elements
    $lowConfidence = $pdo->query("
        SELECT COUNT(*) as count
        FROM ecig_kb_cognitive_analysis
        WHERE confidence_score < 30
    ")->fetch()['count'];
    
    if ($lowConfidence > ($totalCognitive * 0.3)) {
        echo "   ⚠️  WARNING: High number of low-confidence elements ($lowConfidence)\n";
        $validationStats['warnings']++;
    } else {
        echo "   ✅ Cognitive analysis confidence levels are good\n";
    }
    
} catch (Exception $e) {
    echo "❌ Cognitive analysis validation failed: " . $e->getMessage() . "\n";
    $validationStats['critical_issues']++;
}

echo "\n";

// 7. DATA RELATIONSHIPS & CONSISTENCY
echo "🔗 7. DATA RELATIONSHIPS & CONSISTENCY\n";
echo str_repeat("-", 40) . "\n";

try {
    // Check for missing relationships
    $orphanedSearch = $pdo->query("
        SELECT COUNT(*) as count
        FROM ecig_kb_simple_search s
        LEFT JOIN ecig_kb_files f ON s.file_id = f.file_id
        WHERE f.file_id IS NULL
    ")->fetch()['count'];
    
    $orphanedQuality = $pdo->query("
        SELECT COUNT(*) as count
        FROM ecig_kb_simple_quality q
        LEFT JOIN ecig_kb_files f ON q.file_id = f.file_id
        WHERE f.file_id IS NULL
    ")->fetch()['count'];
    
    echo "🔍 RELATIONSHIP INTEGRITY:\n";
    echo "   • Orphaned search records: $orphanedSearch\n";
    echo "   • Orphaned quality records: $orphanedQuality\n";
    
    if ($orphanedSearch > 0 || $orphanedQuality > 0) {
        echo "   ⚠️  WARNING: Orphaned records detected\n";
        $validationStats['inconsistencies']++;
    } else {
        echo "   ✅ All relationships are intact\n";
    }
    
    // Check for missing file records in related tables
    $missingInSearch = $pdo->query("
        SELECT COUNT(*) as count
        FROM ecig_kb_files f
        LEFT JOIN ecig_kb_simple_search s ON f.file_id = s.file_id
        WHERE s.file_id IS NULL AND f.content IS NOT NULL AND f.content != ''
    ")->fetch()['count'];
    
    echo "   • Files missing from search index: $missingInSearch\n";
    
    if ($missingInSearch > 100) {
        echo "   ⚠️  WARNING: Many files not indexed for search\n";
        $validationStats['warnings']++;
    }
    
} catch (Exception $e) {
    echo "❌ Relationship validation failed: " . $e->getMessage() . "\n";
    $validationStats['critical_issues']++;
}

echo "\n";

// 8. CALCULATE OVERALL DATA QUALITY SCORE
$totalIssues = $validationStats['critical_issues'] + $validationStats['warnings'] + 
               $validationStats['garbage_detected'] + $validationStats['inconsistencies'];

$maxPossibleIssues = 50; // Estimated maximum issues for scoring
$qualityScore = max(0, 100 - (($totalIssues / $maxPossibleIssues) * 100));
$validationStats['data_quality_score'] = $qualityScore;
$validationStats['total_issues'] = $totalIssues;

$processingTime = round(microtime(true) - $startTime, 2);

// FINAL REPORT
echo str_repeat("=", 60) . "\n";
echo "📋 FINAL DATA VALIDATION REPORT\n";
echo str_repeat("=", 60) . "\n";

echo "🎯 OVERALL DATA QUALITY SCORE: " . round($qualityScore, 1) . "/100\n\n";

echo "📊 ISSUE SUMMARY:\n";
echo "• Critical Issues: " . $validationStats['critical_issues'] . " 🚨\n";
echo "• Warnings: " . $validationStats['warnings'] . " ⚠️\n";
echo "• Garbage Detected: " . $validationStats['garbage_detected'] . " 🗑️\n";
echo "• Inconsistencies: " . $validationStats['inconsistencies'] . " 🔄\n";
echo "• Total Issues: " . $totalIssues . "\n\n";

if ($qualityScore >= 90) {
    echo "✅ EXCELLENT: Your KB data quality is excellent!\n";
    echo "🚀 System is ready for production use.\n";
} elseif ($qualityScore >= 75) {
    echo "✅ GOOD: Your KB data quality is good with minor issues.\n";
    echo "🔧 Consider addressing warnings for optimal performance.\n";
} elseif ($qualityScore >= 60) {
    echo "⚠️  FAIR: Your KB data has some quality issues.\n";
    echo "🛠️  Recommend cleaning up identified problems.\n";
} else {
    echo "🚨 POOR: Your KB data has significant quality issues.\n";
    echo "🔥 Immediate cleanup required before production use.\n";
}

echo "\n💡 RECOMMENDATIONS:\n";
if ($validationStats['garbage_detected'] > 0) {
    echo "• Clean up garbage files (tmp, cache, logs)\n";
}
if ($validationStats['inconsistencies'] > 0) {
    echo "• Fix data inconsistencies and orphaned records\n";
}
if ($validationStats['warnings'] > 5) {
    echo "• Address warnings to improve system reliability\n";
}
if ($validationStats['critical_issues'] > 0) {
    echo "• URGENT: Fix critical issues immediately\n";
}

echo "\n⏱️  Validation completed in {$processingTime}s\n";
echo "✅ Data validation analysis finished!\n";