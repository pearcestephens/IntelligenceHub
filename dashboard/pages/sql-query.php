<?php
/**
 * SQL Query Tool
 * Execute and analyze SQL queries safely
 */
defined('DASHBOARD_ACCESS') or die('Direct access not permitted');

$db = getDbConnection();
$result = null;
$error = null;
$executionTime = 0;
$affectedRows = 0;
$query = $_POST['query'] ?? '';

// Execute query if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($query)) {
    // Security: Only allow SELECT queries for safety
    $queryType = strtoupper(substr(trim($query), 0, 6));
    
    if ($queryType !== 'SELECT') {
        $error = 'Only SELECT queries are allowed for safety. Use phpMyAdmin or CLI for modifications.';
    } else {
        try {
            $startTime = microtime(true);
            $stmt = $db->query($query);
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $affectedRows = count($result);
        } catch (PDOException $e) {
            $error = $e->getMessage();
        }
    }
}

// Get recent query history from session
if (!isset($_SESSION['query_history'])) {
    $_SESSION['query_history'] = [];
}

// Add to history if successful
if ($result !== null && $error === null && !empty($query)) {
    array_unshift($_SESSION['query_history'], [
        'query' => $query,
        'time' => date('Y-m-d H:i:s'),
        'rows' => $affectedRows,
        'execution_time' => $executionTime
    ]);
    $_SESSION['query_history'] = array_slice($_SESSION['query_history'], 0, 10); // Keep last 10
}

// Sample queries
$sampleQueries = [
    'Intelligence Files' => 'SELECT server_id, COUNT(*) as file_count, ROUND(SUM(file_size)/1024/1024, 2) as total_mb FROM intelligence_files GROUP BY server_id',
    'Recent Files' => 'SELECT * FROM intelligence_files ORDER BY indexed_at DESC LIMIT 20',
    'File Type Stats' => 'SELECT intelligence_type, COUNT(*) as count FROM intelligence_files GROUP BY intelligence_type',
    'Large Files' => 'SELECT file_path, ROUND(file_size/1024, 2) as size_kb FROM intelligence_files WHERE file_size > 100000 ORDER BY file_size DESC LIMIT 20',
    'Table Sizes' => 'SELECT table_name, ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb FROM information_schema.TABLES WHERE table_schema = DATABASE() ORDER BY size_mb DESC LIMIT 20'
];
?>

<div class="page-header">
    <h1 class="page-title">SQL Query Tool</h1>
    <p class="page-subtitle">Execute SELECT queries and analyze database data</p>
</div>

<!-- Query Stats -->
<?php if ($result !== null || $error !== null): ?>
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, <?php echo $error ? '#f093fb 0%, #f5576c' : '#4facfe 0%, #00f2fe'; ?> 100%);">
            <div class="stats-card-icon">
                <i class="fas <?php echo $error ? 'fa-times-circle' : 'fa-check-circle'; ?>"></i>
            </div>
            <div class="stats-card-value"><?php echo $error ? 'ERROR' : 'SUCCESS'; ?></div>
            <div class="stats-card-label">Query Status</div>
        </div>
    </div>
    
    <?php if (!$error): ?>
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-table"></i>
            </div>
            <div class="stats-card-value"><?php echo number_format($affectedRows); ?></div>
            <div class="stats-card-label">Rows Returned</div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-stopwatch"></i>
            </div>
            <div class="stats-card-value"><?php echo $executionTime; ?>ms</div>
            <div class="stats-card-label">Execution Time</div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);">
            <div class="stats-card-icon">
                <i class="fas fa-columns"></i>
            </div>
            <div class="stats-card-value"><?php echo $result ? count($result[0] ?? []) : 0; ?></div>
            <div class="stats-card-label">Columns</div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="row g-4">
    
    <!-- Query Editor -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-code text-primary me-2"></i>
                    Query Editor
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <textarea name="query" class="form-control" rows="10" style="font-family: 'Courier New', monospace; font-size: 14px;" placeholder="Enter your SELECT query here..."><?php echo htmlspecialchars($query); ?></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            Only SELECT queries allowed for security
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-play me-1"></i>
                            Execute Query
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Results -->
        <?php if ($error): ?>
        <div class="card mt-4">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Query Error
                </h5>
            </div>
            <div class="card-body">
                <pre class="mb-0" style="color: #dc3545;"><?php echo htmlspecialchars($error); ?></pre>
            </div>
        </div>
        <?php elseif ($result !== null): ?>
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-table text-success me-2"></i>
                    Query Results
                </h5>
                <button class="btn btn-sm btn-outline-primary" onclick="exportToCSV()">
                    <i class="fas fa-download me-1"></i>
                    Export CSV
                </button>
            </div>
            <div class="card-body p-0">
                <?php if (count($result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0" id="resultsTable">
                        <thead class="table-dark">
                            <tr>
                                <?php foreach (array_keys($result[0]) as $column): ?>
                                <th><?php echo htmlspecialchars($column); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($result as $row): ?>
                            <tr>
                                <?php foreach ($row as $value): ?>
                                <td><?php echo htmlspecialchars($value ?? 'NULL'); ?></td>
                                <?php endforeach; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-info-circle fa-3x mb-3"></i>
                    <p>Query executed successfully but returned no rows.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
    
    <!-- Sidebar -->
    <div class="col-md-4">
        
        <!-- Sample Queries -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-lightbulb text-warning me-2"></i>
                    Sample Queries
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach ($sampleQueries as $name => $sampleQuery): ?>
                    <button type="button" class="list-group-item list-group-item-action" onclick="loadQuery(<?php echo htmlspecialchars(json_encode($sampleQuery), ENT_QUOTES); ?>)">
                        <i class="fas fa-chevron-right me-2 text-muted"></i>
                        <?php echo $name; ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Query History -->
        <?php if (!empty($_SESSION['query_history'])): ?>
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-history text-info me-2"></i>
                    Recent Queries
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach ($_SESSION['query_history'] as $idx => $historyItem): ?>
                    <button type="button" class="list-group-item list-group-item-action" onclick="loadQuery(<?php echo htmlspecialchars(json_encode($historyItem['query']), ENT_QUOTES); ?>)">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="text-truncate" style="max-width: 200px;">
                                <small><?php echo htmlspecialchars(substr($historyItem['query'], 0, 50)); ?>...</small>
                            </div>
                            <span class="badge bg-secondary"><?php echo $historyItem['rows']; ?> rows</span>
                        </div>
                        <small class="text-muted"><?php echo $historyItem['time']; ?> • <?php echo $historyItem['execution_time']; ?>ms</small>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
    
</div>

<script>
function loadQuery(query) {
    document.querySelector('textarea[name="query"]').value = query;
    document.querySelector('textarea[name="query"]').focus();
}

function exportToCSV() {
    const table = document.getElementById('resultsTable');
    let csv = [];
    
    // Get headers
    const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent);
    csv.push(headers.join(','));
    
    // Get rows
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const cells = Array.from(row.querySelectorAll('td')).map(td => {
            let value = td.textContent;
            // Escape commas and quotes
            if (value.includes(',') || value.includes('"')) {
                value = '"' + value.replace(/"/g, '""') + '"';
            }
            return value;
        });
        csv.push(cells.join(','));
    });
    
    // Download
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'query_results_' + Date.now() + '.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}
</script>
