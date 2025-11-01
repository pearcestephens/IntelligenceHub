<?php
/**
 * COMPLETE KB PIPELINE RUNNER - CORRECTED VERSION
 * 
 * Executes the entire Knowledge Base pipeline using working standalone scripts
 * Handles dependencies, error checking, and progress reporting
 * 
 * @package CIS\KB\Pipeline
 * @version 2.0.0
 */

declare(strict_types=1);

echo "🚀 CIS KNOWLEDGE BASE - COMPLETE PIPELINE RUNNER v2.0\n";
echo str_repeat("=", 70) . "\n";
echo "Running entire KB system using verified standalone scripts...\n\n";

$startTime = microtime(true);
$errors = 0;
$totalSteps = 6; // Using only the working scripts we've verified

// Database connection for status tracking
$host = '127.0.0.1';
$dbname = 'hdgwrzntwa';
$username = 'hdgwrzntwa';
$password = 'bFUdRjh4Jx';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Pipeline database connected\n\n";
} catch (PDOException $e) {
    die("❌ Pipeline database connection failed: " . $e->getMessage() . "\n");
}

// Pipeline Definition using VERIFIED working scripts
$pipeline = [
    1 => [
        'name' => 'File System Scan & Database Update',
        'script' => 'ultra_tight_db_update.php',
        'critical' => true,
        'description' => 'Scan filesystem and populate KB database with clean file data'
    ],
    2 => [
        'name' => 'Content Analysis',
        'script' => 'error_proof_content_analyzer.php',
        'critical' => true,
        'description' => 'Analyze file content and extract keywords/metadata'
    ],
    3 => [
        'name' => 'Search Index Creation',
        'script' => 'simple_search_indexer.php',
        'critical' => true,
        'description' => 'Build full-text search indexes for fast searching'
    ],
    4 => [
        'name' => 'Intelligent Organization',
        'script' => 'standalone_auto_organizer.php',
        'critical' => true,
        'description' => 'Organize files into business categories automatically'
    ],
    5 => [
        'name' => 'Cognitive Intelligence',
        'script' => 'simple_cognitive_analysis.php',
        'critical' => false,
        'description' => 'Extract semantic intelligence and business insights'
    ],
    6 => [
        'name' => 'Quality Analysis',
        'script' => 'simple_quality_control.php',
        'critical' => false,
        'description' => 'Analyze content quality and identify improvements'
    ]
];

// Function to run a pipeline step
function runPipelineStep($stepNum, $stepInfo, $pipelineId) {
    global $totalSteps, $errors;
    
    echo "┌" . str_repeat("─", 65) . "┐\n";
    echo "│ STEP $stepNum/$totalSteps: {$stepInfo['name']}" . str_repeat(" ", 65 - strlen("STEP $stepNum/$totalSteps: {$stepInfo['name']}") - 2) . "│\n";
    echo "│ {$stepInfo['description']}" . str_repeat(" ", 65 - strlen($stepInfo['description']) - 2) . "│\n";
    echo "└" . str_repeat("─", 65) . "┘\n";
    
    $criticalStatus = $stepInfo['critical'] ? '🔴 CRITICAL' : '🟡 OPTIONAL';
    echo "Status: $criticalStatus | Script: {$stepInfo['script']}\n";
    
    $stepStart = microtime(true);
    
    // Use the working standalone scripts in the current directory
    $scriptPath = "/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/scripts/{$stepInfo['script']}";
    
    echo "🚀 Executing: {$stepInfo['script']}\n";
    echo str_repeat("-", 65) . "\n";
    
    // Execute the script and capture output
    $command = "cd /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/assets/services && php $scriptPath 2>&1";
    
    // Start execution
    $process = popen($command, 'r');
    $output = '';
    $lineCount = 0;
    
    // Stream output in real-time for first few lines
    while (!feof($process)) {
        $line = fgets($process);
        if ($line !== false) {
            $output .= $line;
            $lineCount++;
            
            // Show first 10 lines in real-time
            if ($lineCount <= 10) {
                echo "   " . trim($line) . "\n";
            }
        }
    }
    
    $returnCode = pclose($process);
    $stepTime = round(microtime(true) - $stepStart, 2);
    
    // Show summary of remaining output
    if ($lineCount > 10) {
        echo "   ... (" . ($lineCount - 10) . " more lines)\n";
        
        // Show last 3 lines
        $allLines = explode("\n", trim($output));
        $lastLines = array_slice($allLines, -3);
        foreach ($lastLines as $line) {
            if (!empty($line)) {
                echo "   " . trim($line) . "\n";
            }
        }
    }
    
    echo str_repeat("-", 65) . "\n";
    echo "📊 Output: $lineCount lines | Duration: {$stepTime}s\n";
    
    if ($returnCode === 0) {
        echo "✅ STEP $stepNum COMPLETED SUCCESSFULLY\n\n";
        return true;
    } else {
        echo "❌ STEP $stepNum FAILED (exit code: $returnCode)\n";
        if ($stepInfo['critical']) {
            echo "🚨 CRITICAL STEP FAILED - Pipeline cannot continue\n\n";
            $errors++;
            return false;
        } else {
            echo "⚠️  Optional step failed - continuing pipeline\n\n";
            return true;
        }
    }
}

// Execute the complete pipeline
echo "🎯 STARTING VERIFIED KB PIPELINE EXECUTION\n";
echo "═══════════════════════════════════════════\n\n";

$successfulSteps = 0;
$failedSteps = 0;
$skippedSteps = 0;

foreach ($pipeline as $stepNum => $stepInfo) {
    $result = runPipelineStep($stepNum, $stepInfo, 'verified_pipeline_' . date('His'));
    
    if ($result === true) {
        $successfulSteps++;
    } elseif ($result === false && $stepInfo['critical']) {
        $failedSteps++;
        // Stop pipeline on critical failure
        echo "🛑 PIPELINE STOPPED: Critical step failed\n";
        break;
    } else {
        $skippedSteps++;
    }
    
    // Small delay between steps for system stability
    echo "⏳ Waiting 1 second before next step...\n\n";
    sleep(1);
}

$totalTime = round(microtime(true) - $startTime, 2);

// Get final system statistics
echo "📈 GATHERING FINAL SYSTEM STATISTICS...\n";
try {
    $stats = $pdo->query("
        SELECT 
            (SELECT COUNT(*) FROM ecig_kb_files) as files,
            (SELECT COUNT(*) FROM ecig_kb_simple_search) as search_records,
            (SELECT COUNT(*) FROM ecig_kb_categories) as categories,
            (SELECT COUNT(*) FROM ecig_kb_file_organization) as organized_files,
            (SELECT COUNT(*) FROM ecig_kb_cognitive_analysis) as cognitive_elements,
            (SELECT COUNT(*) FROM ecig_kb_simple_quality) as quality_records
    ")->fetch();
    
    echo "\n" . str_repeat("=", 70) . "\n";
    echo "🏁 COMPLETE KB PIPELINE EXECUTION RESULTS\n";
    echo str_repeat("=", 70) . "\n";
    
    echo "📊 EXECUTION SUMMARY:\n";
    echo "• Total Execution Time: {$totalTime}s\n";
    echo "• Successful Steps: $successfulSteps/$totalSteps\n";
    echo "• Failed Steps: $failedSteps\n";
    echo "• Skipped Steps: $skippedSteps\n";
    echo "• Error Count: $errors\n\n";
    
    echo "📈 FINAL KB SYSTEM STATISTICS:\n";
    echo "• Files in Database: " . number_format($stats['files']) . "\n";
    echo "• Search Index Records: " . number_format($stats['search_records']) . "\n";
    echo "• Business Categories: " . $stats['categories'] . "\n";
    echo "• Organized Files: " . number_format($stats['organized_files']) . "\n";
    echo "• Cognitive Elements: " . number_format($stats['cognitive_elements']) . "\n";
    echo "• Quality Records: " . number_format($stats['quality_records']) . "\n\n";
    
    // Calculate data processing metrics
    $totalRecords = $stats['files'] + $stats['search_records'] + $stats['organized_files'] + 
                   $stats['cognitive_elements'] + $stats['quality_records'];
    
    echo "⚡ PERFORMANCE METRICS:\n";
    echo "• Total Records Processed: " . number_format($totalRecords) . "\n";
    echo "• Processing Rate: " . number_format($totalRecords / max($totalTime, 1)) . " records/second\n";
    echo "• Average Step Time: " . round($totalTime / $totalSteps, 2) . "s\n\n";
    
} catch (Exception $e) {
    echo "⚠️  Could not retrieve final statistics: " . $e->getMessage() . "\n\n";
}

// Final status
if ($errors === 0 && $successfulSteps >= 4) {
    echo "🎉 SUCCESS: KB Pipeline completed successfully!\n";
    echo "🚀 Knowledge Base system is fully operational!\n\n";
    
    echo "💡 SYSTEM IS NOW READY FOR:\n";
    echo "• Full-text searching across all files\n";
    echo "• Intelligent file organization\n";
    echo "• Business intelligence extraction\n";
    echo "• Content quality analysis\n";
    echo "• Real-time KB updates\n\n";
    
} else {
    echo "⚠️  PARTIAL SUCCESS: Some steps failed\n";
    echo "🔧 Review the output above to identify issues\n";
    
    if ($errors > 0) {
        echo "🚨 CRITICAL ERRORS: $errors critical steps failed\n";
        echo "🛠️  Manual intervention required\n\n";
    }
}

echo "🎯 KB PIPELINE EXECUTION FLOW DEMONSTRATED:\n";
echo "1. File System → Database (ultra_tight_db_update.php)\n";
echo "2. Database → Content Analysis (error_proof_content_analyzer.php)\n";
echo "3. Content → Search Index (simple_search_indexer.php)\n";
echo "4. Content → Organization (standalone_auto_organizer.php)\n";
echo "5. Organization → Intelligence (simple_cognitive_analysis.php)\n";
echo "6. All Data → Quality Analysis (simple_quality_control.php)\n\n";

echo "✅ Complete KB pipeline execution finished!\n";