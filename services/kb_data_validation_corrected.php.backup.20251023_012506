<?php
/**
 * CIS KB DATA VALIDATION & QUALITY AUDIT - CORRECTED VERSION
 * 
 * Comprehensive analysis using actual database schema
 * Identifies garbage data, inconsistencies, and quality issues
 * 
 * @package CIS\KB\Validation
 * @version 2.0.0
 */

declare(strict_types=1);

echo "🔍 CIS KB DATA VALIDATION & QUALITY AUDIT v2.0\n";
echo str_repeat("=", 60) . "\n";
echo "Analyzing current KB data using actual schema...\n\n";

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

echo "📊 COMPREHENSIVE DATA QUALITY VALIDATION\n";
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

// 2. FILE PATH QUALITY ANALYSIS
echo "📁 2. FILE PATH & NAME QUALITY\n";
echo str_repeat("-", 40) . "\n";

try {
    // Check for suspicious file paths using actual columns
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
           OR file_name IS NULL
           OR file_path IS NULL
        GROUP BY file_name, file_path
        ORDER BY count DESC
        LIMIT 20
    ")->fetchAll();
    
    if (count($suspiciousFiles) > 0) {
        echo "🚨 GARBAGE DETECTED: " . count($suspiciousFiles) . " suspicious files found:\n";
        foreach ($suspiciousFiles as $file) {
            $fileName = $file['file_name'] ?? 'NULL';
            $filePath = $file['file_path'] ?? 'NULL';
            echo "   ❌ $fileName ($filePath) - {$file['count']} occurrences\n";
            $validationStats['garbage_detected']++;
        }
    } else {
        echo "✅ No suspicious file paths detected\n";
    }
    
    // Check for duplicate file paths
    $duplicatePaths = $pdo->query("
        SELECT file_path, COUNT(*) as count
        FROM ecig_kb_files 
        WHERE file_path IS NOT NULL AND file_path != ''
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

// 3. CONTENT QUALITY ANALYSIS (using actual columns)
echo "📝 3. CONTENT QUALITY ANALYSIS\n";
echo str_repeat("-", 40) . "\n";

try {
    // Check for empty or minimal content using actual columns
    $emptyContent = $pdo->query("
        SELECT COUNT(*) as count
        FROM ecig_kb_files 
        WHERE (full_content = '' OR full_content IS NULL OR LENGTH(full_content) < 10)
          AND (content_preview = '' OR content_preview IS NULL OR LENGTH(content_preview) < 10)
    ")->fetch()['count'];
    
    if ($emptyContent > 0) {
        echo "⚠️  WARNING: $emptyContent files with empty or minimal content\n";
        $validationStats['warnings']++;
    } else {
        echo "✅ All files have meaningful content\n";
    }
    
    // Check content length distribution using actual columns
    $contentStats = $pdo->query("
        SELECT 
            AVG(LENGTH(COALESCE(full_content, content_preview, ''))) as avg_length,
            MIN(LENGTH(COALESCE(full_content, content_preview, ''))) as min_length,
            MAX(LENGTH(COALESCE(full_content, content_preview, ''))) as max_length,
            COUNT(CASE WHEN LENGTH(COALESCE(full_content, content_preview, '')) < 100 THEN 1 END) as very_small,
            COUNT(CASE WHEN LENGTH(COALESCE(full_content, content_preview, '')) > 100000 THEN 1 END) as very_large,
            COUNT(CASE WHEN full_content IS NOT NULL AND full_content != '' THEN 1 END) as has_full_content,
            COUNT(CASE WHEN content_preview IS NOT NULL AND content_preview != '' THEN 1 END) as has_preview
        FROM ecig_kb_files
    ")->fetch();
    
    echo "\n📈 CONTENT SIZE DISTRIBUTION:\n";
    echo "   • Average length: " . number_format((int)$contentStats['avg_length']) . " characters\n";
    echo "   • Min length: " . number_format($contentStats['min_length']) . " characters\n";
    echo "   • Max length: " . number_format($contentStats['max_length']) . " characters\n";
    echo "   • Very small files (<100 chars): " . $contentStats['very_small'] . "\n";
    echo "   • Very large files (>100K chars): " . $contentStats['very_large'] . "\n";
    echo "   • Files with full content: " . number_format($contentStats['has_full_content']) . "\n";
    echo "   • Files with preview: " . number_format($contentStats['has_preview']) . "\n";
    
    if ($contentStats['very_small'] > ($tableCounts['ecig_kb_files'] * 0.2)) {
        echo "   ⚠️  WARNING: High number of very small files\n";
        $validationStats['warnings']++;
    }
    
    // Check keyword quality
    $keywordStats = $pdo->query("
        SELECT 
            COUNT(CASE WHEN content_keywords IS NOT NULL AND content_keywords != '' THEN 1 END) as has_keywords,
            COUNT(CASE WHEN content_topics IS NOT NULL AND content_topics != '' THEN 1 END) as has_topics,
            COUNT(CASE WHEN content_summary IS NOT NULL AND content_summary != '' THEN 1 END) as has_summary,
            AVG(content_complexity) as avg_complexity
        FROM ecig_kb_files
    ")->fetch();
    
    echo "\n🎯 CONTENT METADATA QUALITY:\n";
    echo "   • Files with keywords: " . number_format($keywordStats['has_keywords']) . "\n";
    echo "   • Files with topics: " . number_format($keywordStats['has_topics']) . "\n";
    echo "   • Files with summary: " . number_format($keywordStats['has_summary']) . "\n";
    echo "   • Average complexity: " . round($keywordStats['avg_complexity'], 1) . "\n";
    
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
            COUNT(CASE WHEN search_terms = '' OR search_terms IS NULL THEN 1 END) as empty_terms,
            COUNT(CASE WHEN search_content = '' OR search_content IS NULL THEN 1 END) as empty_content
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

// 5. BUSINESS ORGANIZATION VALIDATION (using actual columns)
echo "🏢 5. BUSINESS ORGANIZATION QUALITY\n";
echo str_repeat("-", 40) . "\n";

try {
    // Check category distribution using actual columns
    $categoryStats = $pdo->query("
        SELECT 
            c.category_name,
            c.description,
            c.file_count as recorded_count,
            COUNT(o.file_id) as actual_count,
            AVG(o.business_value_score) as avg_score,
            c.priority_weight
        FROM ecig_kb_categories c
        LEFT JOIN ecig_kb_file_organization o ON c.category_id = o.category_id
        GROUP BY c.category_id, c.category_name, c.description, c.file_count, c.priority_weight
        ORDER BY actual_count DESC
    ")->fetchAll();
    
    echo "📋 CATEGORY DISTRIBUTION:\n";
    $totalOrganized = 0;
    foreach ($categoryStats as $cat) {
        $fileCount = $cat['actual_count'];
        $recordedCount = $cat['recorded_count'];
        $avgScore = round($cat['avg_score'] ?? 0, 1);
        $priority = $cat['priority_weight'];
        
        echo "   • {$cat['category_name']}: $fileCount files";
        if ($recordedCount != $fileCount) {
            echo " (recorded: $recordedCount ⚠️)";
            $validationStats['inconsistencies']++;
        }
        echo " | Score: $avgScore | Priority: $priority\n";
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

// 6. COGNITIVE ANALYSIS VALIDATION (using actual columns)
echo "🧠 6. COGNITIVE INTELLIGENCE QUALITY\n";
echo str_repeat("-", 40) . "\n";

try {
    $cognitiveStats = $pdo->query("
        SELECT 
            analysis_type,
            analysis_category,
            COUNT(*) as count,
            AVG(confidence_score * 100) as avg_confidence,
            AVG(business_value) as avg_business_value,
            technical_complexity,
            COUNT(technical_complexity) as complexity_count
        FROM ecig_kb_cognitive_analysis
        GROUP BY analysis_type, analysis_category, technical_complexity
        ORDER BY count DESC
    ")->fetchAll();
    
    echo "🧪 COGNITIVE ELEMENTS BY TYPE:\n";
    $totalCognitive = 0;
    $typeGroups = [];
    
    foreach ($cognitiveStats as $cog) {
        $type = $cog['analysis_type'];
        if (!isset($typeGroups[$type])) {
            $typeGroups[$type] = ['count' => 0, 'confidence' => 0, 'value' => 0];
        }
        $typeGroups[$type]['count'] += $cog['count'];
        $typeGroups[$type]['confidence'] += $cog['avg_confidence'] * $cog['count'];
        $typeGroups[$type]['value'] += $cog['avg_business_value'] * $cog['count'];
        $totalCognitive += $cog['count'];
    }
    
    foreach ($typeGroups as $type => $data) {
        $avgConf = round($data['confidence'] / $data['count'], 1);
        $avgValue = round($data['value'] / $data['count'], 1);
        echo "   • $type: " . number_format($data['count']) . " elements (confidence: {$avgConf}%, value: $avgValue)\n";
    }
    
    echo "\n   📊 Total cognitive elements: " . number_format($totalCognitive) . "\n";
    
    // Check for low confidence elements
    $lowConfidence = $pdo->query("
        SELECT COUNT(*) as count
        FROM ecig_kb_cognitive_analysis
        WHERE confidence_score < 0.3
    ")->fetch()['count'];
    
    if ($lowConfidence > ($totalCognitive * 0.3)) {
        echo "   ⚠️  WARNING: High number of low-confidence elements ($lowConfidence)\n";
        $validationStats['warnings']++;
    } else {
        echo "   ✅ Cognitive analysis confidence levels are good\n";
    }
    
    // Complexity distribution
    $complexityDist = $pdo->query("
        SELECT 
            technical_complexity,
            COUNT(*) as count
        FROM ecig_kb_cognitive_analysis
        GROUP BY technical_complexity
    ")->fetchAll();
    
    echo "\n   🎯 Complexity Distribution:\n";
    foreach ($complexityDist as $comp) {
        echo "      • {$comp['technical_complexity']}: " . number_format($comp['count']) . " elements\n";
    }
    
} catch (Exception $e) {
    echo "❌ Cognitive analysis validation failed: " . $e->getMessage() . "\n";
    $validationStats['critical_issues']++;
}

echo "\n";

// 7. FILE TYPE & LANGUAGE DISTRIBUTION
echo "📋 7. FILE TYPE & LANGUAGE ANALYSIS\n";
echo str_repeat("-", 40) . "\n";

try {
    $fileTypeStats = $pdo->query("
        SELECT 
            file_type,
            language,
            COUNT(*) as count,
            AVG(file_size_bytes) as avg_size,
            AVG(line_count) as avg_lines
        FROM ecig_kb_files
        GROUP BY file_type, language
        ORDER BY count DESC
        LIMIT 15
    ")->fetchAll();
    
    echo "📂 FILE TYPE DISTRIBUTION:\n";
    foreach ($fileTypeStats as $type) {
        $lang = $type['language'] ?? 'unknown';
        $avgSize = number_format($type['avg_size']);
        $avgLines = number_format($type['avg_lines']);
        echo "   • {$type['file_type']} ($lang): " . number_format($type['count']) . " files | Avg: {$avgSize}B, {$avgLines} lines\n";
    }
    
    // Check for unusual file types that might be garbage
    $unusualTypes = $pdo->query("
        SELECT file_type, COUNT(*) as count
        FROM ecig_kb_files
        WHERE file_type IN ('', 'unknown', 'binary', 'tmp', 'cache', 'log')
           OR file_type IS NULL
        GROUP BY file_type
    ")->fetchAll();
    
    if (count($unusualTypes) > 0) {
        echo "\n⚠️  UNUSUAL FILE TYPES:\n";
        foreach ($unusualTypes as $unusual) {
            $type = $unusual['file_type'] ?? 'NULL';
            echo "   • $type: {$unusual['count']} files\n";
            $validationStats['garbage_detected']++;
        }
    } else {
        echo "\n✅ All file types are standard\n";
    }
    
} catch (Exception $e) {
    echo "❌ File type analysis failed: " . $e->getMessage() . "\n";
    $validationStats['critical_issues']++;
}

echo "\n";

// 8. CALCULATE OVERALL DATA QUALITY SCORE
$totalIssues = $validationStats['critical_issues'] + $validationStats['warnings'] + 
               $validationStats['garbage_detected'] + $validationStats['inconsistencies'];

$maxPossibleIssues = 30; // Adjusted for realistic scoring
$qualityScore = max(0, 100 - (($totalIssues / $maxPossibleIssues) * 100));
$validationStats['data_quality_score'] = $qualityScore;
$validationStats['total_issues'] = $totalIssues;

$processingTime = round(microtime(true) - $startTime, 2);

// FINAL REPORT
echo str_repeat("=", 60) . "\n";
echo "📋 COMPREHENSIVE DATA VALIDATION RESULTS\n";
echo str_repeat("=", 60) . "\n";

echo "🎯 OVERALL DATA QUALITY SCORE: " . round($qualityScore, 1) . "/100\n\n";

echo "📊 DETAILED ISSUE BREAKDOWN:\n";
echo "• Critical Issues: " . $validationStats['critical_issues'] . " 🚨\n";
echo "• Warnings: " . $validationStats['warnings'] . " ⚠️\n";
echo "• Garbage Files Detected: " . $validationStats['garbage_detected'] . " 🗑️\n";
echo "• Data Inconsistencies: " . $validationStats['inconsistencies'] . " 🔄\n";
echo "• Total Issues Found: " . $totalIssues . "\n\n";

// Quality assessment
if ($qualityScore >= 95) {
    echo "🏆 OUTSTANDING: Your KB data quality is exceptional!\n";
    echo "🚀 System is production-ready with minimal issues.\n";
} elseif ($qualityScore >= 85) {
    echo "✅ EXCELLENT: Your KB data quality is very good!\n";
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

echo "\n💡 SPECIFIC RECOMMENDATIONS:\n";
if ($validationStats['garbage_detected'] > 0) {
    echo "• Clean up garbage files (tmp, cache, logs, unusual types)\n";
}
if ($validationStats['inconsistencies'] > 0) {
    echo "• Fix data inconsistencies between tables\n";
}
if ($validationStats['warnings'] > 3) {
    echo "• Address warnings to improve system reliability\n";
}
if ($validationStats['critical_issues'] > 0) {
    echo "• 🚨 URGENT: Fix critical issues immediately\n";
}
if ($qualityScore >= 85) {
    echo "• Your KB system is in excellent condition!\n";
    echo "• Consider running regular maintenance scans\n";
}

echo "\n📈 SUMMARY STATISTICS:\n";
echo "• Total Files Analyzed: " . number_format($tableCounts['ecig_kb_files']) . "\n";
echo "• Search Records: " . number_format($tableCounts['ecig_kb_simple_search']) . "\n";
echo "• Business Categories: " . $tableCounts['ecig_kb_categories'] . "\n";
echo "• Organized Files: " . number_format($tableCounts['ecig_kb_file_organization']) . "\n";
echo "• Cognitive Elements: " . number_format($tableCounts['ecig_kb_cognitive_analysis']) . "\n";
echo "• Quality Records: " . number_format($tableCounts['ecig_kb_simple_quality']) . "\n";

echo "\n⏱️  Validation completed in {$processingTime}s\n";
echo "✅ Comprehensive data validation finished!\n\n";

if ($qualityScore >= 85) {
    echo "🎉 CONCLUSION: Your KB data is HIGH QUALITY and ready for production!\n";
} else {
    echo "🔧 CONCLUSION: Consider running cleanup tools to improve data quality.\n";
}

echo "✅ DATA VALIDATION COMPLETE!\n";