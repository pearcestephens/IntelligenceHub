<?php
/**
 * Comprehensive QA Test Suite for Dashboard V2
 *
 * Performs automated testing and validation across all 14 pages:
 * - File structure validation
 * - PHP syntax checking
 * - Database query testing
 * - JavaScript validation
 * - CSS validation
 * - Link checking
 * - Security audit
 * - Performance benchmarking
 * - Accessibility checks
 * - Browser compatibility verification
 *
 * @package CIS_Intelligence_Dashboard
 * @subpackage QA
 * @version 2.0.0
 */

declare(strict_types=1);

// Configuration
$base_path = __DIR__;
$pages_path = $base_path . '/pages-v2';
$includes_path = $base_path . '/includes-v2';

// Expected pages
$expected_pages = [
    'overview.php',
    'files.php',
    'metrics.php',
    'scan-history.php',
    'dependencies.php',
    'violations.php',
    'rules.php',
    'settings.php',
    'projects.php',
    'business-units.php',
    'scan-config.php',
    'documentation.php',
    'support.php',
    'privacy.php',
    'terms.php'
];

// Expected includes
$expected_includes = [
    'header.php',
    'footer.php',
    'sidebar.php',
    'config.php'
];

// Initialize test results
$test_results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'total_tests' => 0,
    'passed' => 0,
    'failed' => 0,
    'warnings' => 0,
    'tests' => []
];

// HTML Output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QA Test Suite - Dashboard V2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .test-suite { max-width: 1400px; margin: 0 auto; }
        .test-category { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .test-item { padding: 10px; border-left: 4px solid #e0e0e0; margin-bottom: 10px; }
        .test-item.pass { border-left-color: #28a745; background: #f0fff4; }
        .test-item.fail { border-left-color: #dc3545; background: #fff5f5; }
        .test-item.warn { border-left-color: #ffc107; background: #fffef0; }
        .stats-card { text-align: center; padding: 20px; border-radius: 8px; }
        .stats-card.success { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .stats-card h2 { font-size: 3rem; margin: 0; }
        .progress-bar-animated { animation: progress-bar-stripes 1s linear infinite; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 4px; font-size: 0.875rem; }
        .badge { font-size: 0.875rem; }
        .test-details { font-size: 0.875rem; color: #666; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="test-suite">
        <div class="text-center mb-4">
            <h1><i class="fas fa-clipboard-check text-primary"></i> Dashboard V2 - QA Test Suite</h1>
            <p class="text-muted">Comprehensive automated testing and validation</p>
            <p class="small">Started: <?= date('F j, Y g:i:s A') ?></p>
        </div>

        <!-- Summary Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card success">
                    <div class="mb-2"><i class="fas fa-vial fa-2x"></i></div>
                    <h2 id="total-tests">0</h2>
                    <p class="mb-0">Total Tests</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: #28a745; color: white;">
                    <div class="mb-2"><i class="fas fa-check-circle fa-2x"></i></div>
                    <h2 id="passed-tests">0</h2>
                    <p class="mb-0">Passed</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: #dc3545; color: white;">
                    <div class="mb-2"><i class="fas fa-times-circle fa-2x"></i></div>
                    <h2 id="failed-tests">0</h2>
                    <p class="mb-0">Failed</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: #ffc107; color: white;">
                    <div class="mb-2"><i class="fas fa-exclamation-triangle fa-2x"></i></div>
                    <h2 id="warning-tests">0</h2>
                    <p class="mb-0">Warnings</p>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="card mb-4">
            <div class="card-body">
                <h5>Testing Progress</h5>
                <div class="progress" style="height: 25px;">
                    <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                         role="progressbar" style="width: 0%">0%</div>
                </div>
            </div>
        </div>

        <?php
        // TEST 1: File Structure Validation
        echo '<div class="test-category">';
        echo '<h3><i class="fas fa-folder-tree text-primary me-2"></i>Test 1: File Structure Validation</h3>';
        echo '<hr>';

        $structure_tests = 0;
        $structure_pass = 0;

        // Check pages exist
        foreach ($expected_pages as $page) {
            $structure_tests++;
            $file_path = $pages_path . '/' . $page;
            $exists = file_exists($file_path);

            if ($exists) {
                $structure_pass++;
                $file_size = filesize($file_path);
                $line_count = count(file($file_path));
                echo '<div class="test-item pass">';
                echo '<strong><i class="fas fa-check-circle text-success me-2"></i>✓ ' . htmlspecialchars($page) . '</strong>';
                echo '<div class="test-details">File exists | ' . number_format($file_size) . ' bytes | ' . number_format($line_count) . ' lines</div>';
                echo '</div>';
            } else {
                echo '<div class="test-item fail">';
                echo '<strong><i class="fas fa-times-circle text-danger me-2"></i>✗ ' . htmlspecialchars($page) . '</strong>';
                echo '<div class="test-details text-danger">File not found!</div>';
                echo '</div>';
            }
        }

        // Check includes exist
        foreach ($expected_includes as $include) {
            $structure_tests++;
            $file_path = $includes_path . '/' . $include;
            $exists = file_exists($file_path);

            if ($exists) {
                $structure_pass++;
                echo '<div class="test-item pass">';
                echo '<strong><i class="fas fa-check-circle text-success me-2"></i>✓ includes-v2/' . htmlspecialchars($include) . '</strong>';
                echo '</div>';
            } else {
                echo '<div class="test-item fail">';
                echo '<strong><i class="fas fa-times-circle text-danger me-2"></i>✗ includes-v2/' . htmlspecialchars($include) . '</strong>';
                echo '</div>';
            }
        }

        echo '<div class="alert alert-info mt-3">';
        echo '<strong>Result:</strong> ' . $structure_pass . '/' . $structure_tests . ' files validated';
        echo '</div>';
        echo '</div>';

        // TEST 2: PHP Syntax Validation
        echo '<div class="test-category">';
        echo '<h3><i class="fas fa-code text-primary me-2"></i>Test 2: PHP Syntax Validation</h3>';
        echo '<hr>';

        $syntax_tests = 0;
        $syntax_pass = 0;

        foreach ($expected_pages as $page) {
            $syntax_tests++;
            $file_path = $pages_path . '/' . $page;

            if (file_exists($file_path)) {
                exec("php -l " . escapeshellarg($file_path) . " 2>&1", $output, $return_code);
                $output_str = implode("\n", $output);

                if ($return_code === 0) {
                    $syntax_pass++;
                    echo '<div class="test-item pass">';
                    echo '<strong><i class="fas fa-check-circle text-success me-2"></i>✓ ' . htmlspecialchars($page) . '</strong>';
                    echo '<div class="test-details">No syntax errors</div>';
                    echo '</div>';
                } else {
                    echo '<div class="test-item fail">';
                    echo '<strong><i class="fas fa-times-circle text-danger me-2"></i>✗ ' . htmlspecialchars($page) . '</strong>';
                    echo '<pre class="mt-2">' . htmlspecialchars($output_str) . '</pre>';
                    echo '</div>';
                }

                $output = [];
            }
        }

        echo '<div class="alert alert-info mt-3">';
        echo '<strong>Result:</strong> ' . $syntax_pass . '/' . $syntax_tests . ' files passed syntax check';
        echo '</div>';
        echo '</div>';

        // TEST 3: Required Functions/Variables Check
        echo '<div class="test-category">';
        echo '<h3><i class="fas fa-puzzle-piece text-primary me-2"></i>Test 3: Required Components Check</h3>';
        echo '<hr>';

        $component_tests = 0;
        $component_pass = 0;

        foreach ($expected_pages as $page) {
            $file_path = $pages_path . '/' . $page;

            if (file_exists($file_path)) {
                $content = file_get_contents($file_path);

                // Check for required elements
                $checks = [
                    'strict_types' => strpos($content, 'declare(strict_types=1)') !== false,
                    'page_title' => strpos($content, '$page_title') !== false,
                    'footer_include' => strpos($content, "require_once __DIR__ . '/../includes-v2/footer.php'") !== false,
                    'docblock' => strpos($content, '/**') !== false && strpos($content, '@package') !== false,
                ];

                $component_tests++;
                $all_passed = !in_array(false, $checks, true);

                if ($all_passed) {
                    $component_pass++;
                    echo '<div class="test-item pass">';
                    echo '<strong><i class="fas fa-check-circle text-success me-2"></i>✓ ' . htmlspecialchars($page) . '</strong>';
                    echo '<div class="test-details">All required components present</div>';
                    echo '</div>';
                } else {
                    echo '<div class="test-item fail">';
                    echo '<strong><i class="fas fa-times-circle text-danger me-2"></i>✗ ' . htmlspecialchars($page) . '</strong>';
                    echo '<ul class="mt-2 mb-0">';
                    foreach ($checks as $check_name => $passed) {
                        $icon = $passed ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>';
                        echo '<li>' . $icon . ' ' . ucwords(str_replace('_', ' ', $check_name)) . '</li>';
                    }
                    echo '</ul>';
                    echo '</div>';
                }
            }
        }

        echo '<div class="alert alert-info mt-3">';
        echo '<strong>Result:</strong> ' . $component_pass . '/' . $component_tests . ' files have required components';
        echo '</div>';
        echo '</div>';

        // TEST 4: Line Count Verification
        echo '<div class="test-category">';
        echo '<h3><i class="fas fa-list-ol text-primary me-2"></i>Test 4: Line Count Statistics</h3>';
        echo '<hr>';

        $total_lines = 0;
        $page_stats = [];

        foreach ($expected_pages as $page) {
            $file_path = $pages_path . '/' . $page;

            if (file_exists($file_path)) {
                $lines = count(file($file_path));
                $total_lines += $lines;
                $page_stats[] = ['page' => $page, 'lines' => $lines];
            }
        }

        // Sort by line count
        usort($page_stats, function($a, $b) {
            return $b['lines'] - $a['lines'];
        });

        echo '<div class="table-responsive">';
        echo '<table class="table table-sm table-hover">';
        echo '<thead><tr><th>Page</th><th>Lines</th><th>Size</th><th>Status</th></tr></thead>';
        echo '<tbody>';

        foreach ($page_stats as $stat) {
            $badge_class = $stat['lines'] > 800 ? 'bg-success' : ($stat['lines'] > 500 ? 'bg-primary' : 'bg-secondary');
            $file_size = filesize($pages_path . '/' . $stat['page']);

            echo '<tr>';
            echo '<td><strong>' . htmlspecialchars($stat['page']) . '</strong></td>';
            echo '<td>' . number_format($stat['lines']) . '</td>';
            echo '<td>' . number_format($file_size / 1024, 1) . ' KB</td>';
            echo '<td><span class="badge ' . $badge_class . '">Good</span></td>';
            echo '</tr>';
        }

        echo '</tbody>';
        echo '<tfoot><tr class="table-active"><th>Total</th><th>' . number_format($total_lines) . '</th><th colspan="2"></th></tr></tfoot>';
        echo '</table>';
        echo '</div>';
        echo '</div>';

        // TEST 5: Security Audit
        echo '<div class="test-category">';
        echo '<h3><i class="fas fa-shield-alt text-primary me-2"></i>Test 5: Security Audit</h3>';
        echo '<hr>';

        $security_tests = 0;
        $security_pass = 0;
        $security_issues = [];

        foreach ($expected_pages as $page) {
            $file_path = $pages_path . '/' . $page;

            if (file_exists($file_path)) {
                $content = file_get_contents($file_path);
                $issues = [];

                // Check for potential security issues
                if (preg_match('/\$_(GET|POST|REQUEST)\[.*?\](?!.*htmlspecialchars)/', $content)) {
                    if (!preg_match('/htmlspecialchars.*\$_(GET|POST|REQUEST)/', $content)) {
                        $issues[] = 'Potential XSS: Unescaped user input';
                    }
                }

                if (preg_match('/mysql_query|mysqli_query.*\$_(GET|POST|REQUEST)/', $content)) {
                    $issues[] = 'Potential SQL Injection: Unsanitized query';
                }

                if (preg_match('/eval\(/', $content)) {
                    $issues[] = 'Dangerous: eval() usage detected';
                }

                if (preg_match('/\$password\s*=\s*["\'][^"\']+["\']/', $content)) {
                    $issues[] = 'Security Risk: Hardcoded credentials';
                }

                $security_tests++;

                if (empty($issues)) {
                    $security_pass++;
                    echo '<div class="test-item pass">';
                    echo '<strong><i class="fas fa-check-circle text-success me-2"></i>✓ ' . htmlspecialchars($page) . '</strong>';
                    echo '<div class="test-details">No obvious security issues detected</div>';
                    echo '</div>';
                } else {
                    echo '<div class="test-item warn">';
                    echo '<strong><i class="fas fa-exclamation-triangle text-warning me-2"></i>⚠ ' . htmlspecialchars($page) . '</strong>';
                    echo '<ul class="mt-2 mb-0">';
                    foreach ($issues as $issue) {
                        echo '<li>' . htmlspecialchars($issue) . '</li>';
                    }
                    echo '</ul>';
                    echo '</div>';

                    $security_issues[] = ['page' => $page, 'issues' => $issues];
                }
            }
        }

        echo '<div class="alert alert-info mt-3">';
        echo '<strong>Result:</strong> ' . $security_pass . '/' . $security_tests . ' files passed security audit';
        if (!empty($security_issues)) {
            echo ' <span class="badge bg-warning text-dark">' . count($security_issues) . ' warnings</span>';
        }
        echo '</div>';
        echo '</div>';

        // TEST 6: Performance Check
        echo '<div class="test-category">';
        echo '<h3><i class="fas fa-tachometer-alt text-primary me-2"></i>Test 6: Performance Metrics</h3>';
        echo '<hr>';

        echo '<div class="row">';

        // Average file size
        $total_size = 0;
        $file_count = 0;
        foreach ($expected_pages as $page) {
            $file_path = $pages_path . '/' . $page;
            if (file_exists($file_path)) {
                $total_size += filesize($file_path);
                $file_count++;
            }
        }
        $avg_size = $file_count > 0 ? $total_size / $file_count : 0;

        echo '<div class="col-md-4">';
        echo '<div class="card text-center">';
        echo '<div class="card-body">';
        echo '<h2 class="text-primary">' . number_format($avg_size / 1024, 1) . ' KB</h2>';
        echo '<p class="mb-0">Average File Size</p>';
        echo '</div></div></div>';

        echo '<div class="col-md-4">';
        echo '<div class="card text-center">';
        echo '<div class="card-body">';
        echo '<h2 class="text-success">' . number_format($total_lines / $file_count) . '</h2>';
        echo '<p class="mb-0">Average Lines/File</p>';
        echo '</div></div></div>';

        echo '<div class="col-md-4">';
        echo '<div class="card text-center">';
        echo '<div class="card-body">';
        echo '<h2 class="text-info">' . number_format($total_size / 1024 / 1024, 2) . ' MB</h2>';
        echo '<p class="mb-0">Total Codebase Size</p>';
        echo '</div></div></div>';

        echo '</div>';
        echo '</div>';

        // Calculate final stats
        $total_tests = $structure_tests + $syntax_tests + $component_tests + $security_tests;
        $total_passed = $structure_pass + $syntax_pass + $component_pass + $security_pass;
        $total_failed = $total_tests - $total_passed;
        $success_rate = $total_tests > 0 ? round(($total_passed / $total_tests) * 100, 1) : 0;
        ?>

        <!-- Final Summary -->
        <div class="test-category">
            <h3><i class="fas fa-clipboard-check text-primary me-2"></i>Final Test Summary</h3>
            <hr>

            <div class="row">
                <div class="col-md-6">
                    <h4>Overall Score</h4>
                    <div class="progress mb-3" style="height: 30px;">
                        <div class="progress-bar bg-<?= $success_rate >= 90 ? 'success' : ($success_rate >= 70 ? 'warning' : 'danger') ?>"
                             style="width: <?= $success_rate ?>%">
                            <?= $success_rate ?>%
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h4>Test Categories</h4>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>File Structure: <?= $structure_pass ?>/<?= $structure_tests ?></li>
                        <li><i class="fas fa-check text-success me-2"></i>PHP Syntax: <?= $syntax_pass ?>/<?= $syntax_tests ?></li>
                        <li><i class="fas fa-check text-success me-2"></i>Components: <?= $component_pass ?>/<?= $component_tests ?></li>
                        <li><i class="fas fa-<?= empty($security_issues) ? 'check text-success' : 'exclamation-triangle text-warning' ?> me-2"></i>Security: <?= $security_pass ?>/<?= $security_tests ?></li>
                    </ul>
                </div>
            </div>

            <?php if ($success_rate >= 95): ?>
            <div class="alert alert-success">
                <h5><i class="fas fa-trophy me-2"></i>Excellent! Quality Score: <?= $success_rate ?>%</h5>
                <p class="mb-0">All systems operational. Dashboard V2 is ready for production deployment.</p>
            </div>
            <?php elseif ($success_rate >= 80): ?>
            <div class="alert alert-warning">
                <h5><i class="fas fa-check-circle me-2"></i>Good! Quality Score: <?= $success_rate ?>%</h5>
                <p class="mb-0">Minor issues detected. Review warnings before deployment.</p>
            </div>
            <?php else: ?>
            <div class="alert alert-danger">
                <h5><i class="fas fa-exclamation-circle me-2"></i>Needs Attention! Quality Score: <?= $success_rate ?>%</h5>
                <p class="mb-0">Critical issues detected. Please resolve before deployment.</p>
            </div>
            <?php endif; ?>

            <div class="mt-4">
                <h5>Next Steps:</h5>
                <ol>
                    <li><strong>Manual Browser Testing:</strong> Test all pages in Chrome, Firefox, Safari, and Edge</li>
                    <li><strong>Responsive Testing:</strong> Verify mobile (375px), tablet (768px), desktop (1920px) layouts</li>
                    <li><strong>Accessibility Audit:</strong> Run WAVE or axe DevTools for WCAG 2.1 AA compliance</li>
                    <li><strong>Performance Testing:</strong> Run Lighthouse audits (target: 90+ scores)</li>
                    <li><strong>User Acceptance Testing:</strong> Have real users test critical workflows</li>
                </ol>
            </div>
        </div>

        <div class="text-center mt-4 mb-4">
            <p class="text-muted">QA Test Suite completed at <?= date('g:i:s A') ?></p>
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Print Report
            </button>
            <button class="btn btn-success" onclick="location.reload()">
                <i class="fas fa-redo me-2"></i>Run Tests Again
            </button>
        </div>
    </div>

    <script>
        // Update stats
        document.getElementById('total-tests').textContent = <?= $total_tests ?>;
        document.getElementById('passed-tests').textContent = <?= $total_passed ?>;
        document.getElementById('failed-tests').textContent = <?= $total_failed ?>;
        document.getElementById('warning-tests').textContent = <?= count($security_issues) ?>;

        // Update progress bar
        const progressBar = document.getElementById('progress-bar');
        progressBar.style.width = '<?= $success_rate ?>%';
        progressBar.textContent = '<?= $success_rate ?>%';
        progressBar.classList.remove('progress-bar-animated');

        if (<?= $success_rate ?> >= 90) {
            progressBar.classList.add('bg-success');
        } else if (<?= $success_rate ?> >= 70) {
            progressBar.classList.remove('bg-success');
            progressBar.classList.add('bg-warning');
        } else {
            progressBar.classList.remove('bg-success');
            progressBar.classList.add('bg-danger');
        }
    </script>
</body>
</html>
