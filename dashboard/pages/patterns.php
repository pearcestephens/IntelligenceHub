<?php
/**
 * Pattern Recognition
 * Analyze code patterns and relationships across the codebase
 */
defined('DASHBOARD_ACCESS') or die('Direct access not permitted');

$db = getDbConnection();

// Get pattern statistics
$patterns = [
    'common_functions' => [
        'name' => 'Most Used Functions',
        'icon' => 'fa-code',
        'color' => '#667eea',
        'query' => "SELECT COUNT(*) as count FROM intelligence_files WHERE intelligence_type = 'function'"
    ],
    'file_relationships' => [
        'name' => 'File Dependencies',
        'icon' => 'fa-project-diagram',
        'color' => '#f093fb',
        'query' => "SELECT COUNT(DISTINCT server_id) as count FROM intelligence_files"
    ],
    'code_complexity' => [
        'name' => 'Complex Files',
        'icon' => 'fa-brain',
        'color' => '#4facfe',
        'query' => "SELECT COUNT(*) as count FROM intelligence_files WHERE file_size > 50000"
    ],
    'duplication' => [
        'name' => 'Potential Duplicates',
        'icon' => 'fa-copy',
        'color' => '#fa709a',
        'query' => "SELECT COUNT(*) as count FROM intelligence_files GROUP BY file_size HAVING COUNT(*) > 1"
    ]
];

// Execute queries
foreach ($patterns as $key => &$pattern) {
    try {
        $result = $db->query($pattern['query'])->fetch(PDO::FETCH_ASSOC);
        $pattern['value'] = $result['count'] ?? 0;
    } catch (PDOException $e) {
        $pattern['value'] = 0;
    }
}

// Get file type distribution
$fileTypes = $db->query("
    SELECT
        SUBSTRING_INDEX(file_path, '.', -1) as extension,
        COUNT(*) as count
    FROM intelligence_files
    GROUP BY extension
    ORDER BY count DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

// Get server distribution
$serverDist = $db->query("
    SELECT
        server_id,
        COUNT(*) as file_count,
        ROUND(SUM(file_size) / 1024 / 1024, 2) as total_mb
    FROM intelligence_files
    GROUP BY server_id
    ORDER BY file_count DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="page-header">
    <h1 class="page-title">Pattern Recognition</h1>
    <p class="page-subtitle">Analyze code patterns and discover relationships across your codebase</p>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <?php foreach ($patterns as $pattern): ?>
    <div class="col-xl-3 col-lg-6">
        <div class="stats-card" style="background: linear-gradient(135deg, <?php echo $pattern['color']; ?> 0%, <?php echo $pattern['color']; ?>88 100%);">
            <div class="stats-card-icon">
                <i class="fas <?php echo $pattern['icon']; ?>"></i>
            </div>
            <div class="stats-card-value"><?php echo number_format($pattern['value']); ?></div>
            <div class="stats-card-label"><?php echo $pattern['name']; ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-4">

    <!-- File Type Distribution -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-file-code text-primary me-2"></i>
                    File Type Distribution
                </h5>
            </div>
            <div class="card-body">
                <canvas id="fileTypeChart" height="200"></canvas>
            </div>
            <div class="card-body border-top">
                <div class="list-group list-group-flush">
                    <?php foreach ($fileTypes as $type): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span>.<?php echo htmlspecialchars($type['extension']); ?></span>
                        <span class="badge bg-primary rounded-pill"><?php echo number_format($type['count']); ?> files</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Server Distribution -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-server text-success me-2"></i>
                    Server Distribution
                </h5>
            </div>
            <div class="card-body">
                <canvas id="serverChart" height="200"></canvas>
            </div>
            <div class="card-body border-top">
                <div class="list-group list-group-flush">
                    <?php foreach ($serverDist as $server): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong><?php echo htmlspecialchars($server['server_id']); ?></strong>
                            <span class="badge bg-success"><?php echo number_format($server['file_count']); ?> files</span>
                        </div>
                        <small class="text-muted"><?php echo $server['total_mb']; ?> MB total</small>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Live Pattern Analysis Tools -->
<div class="row g-4 mt-4">

    <!-- Pattern Search -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-search me-2"></i>
                    Find Code Patterns
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Search for specific patterns across 15,151 files using MCP semantic search</p>

                <div class="mb-3">
                    <label class="form-label fw-bold">Quick Pattern Searches:</label>
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" onclick="searchPattern('database connection')">
                            <i class="fas fa-database"></i> Database Connection Patterns
                        </button>
                        <button class="btn btn-outline-success" onclick="searchPattern('authentication login')">
                            <i class="fas fa-lock"></i> Authentication Patterns
                        </button>
                        <button class="btn btn-outline-info" onclick="searchPattern('validation sanitize')">
                            <i class="fas fa-shield-alt"></i> Input Validation Patterns
                        </button>
                        <button class="btn btn-outline-warning" onclick="searchPattern('error handling try catch')">
                            <i class="fas fa-exclamation-triangle"></i> Error Handling Patterns
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Custom Pattern Search:</label>
                    <input type="text" class="form-control" id="customPattern"
                           placeholder="e.g., 'PDO prepared statement', 'API endpoint', 'class extends'">
                </div>

                <button class="btn btn-primary w-100" onclick="searchCustomPattern()">
                    <i class="fas fa-search"></i> Search Pattern
                </button>
            </div>
        </div>
    </div>

    <!-- Dependency Analyzer -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-project-diagram me-2"></i>
                    Dependency Analyzer
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Analyze file dependencies and relationships</p>

                <div class="mb-3">
                    <label class="form-label fw-bold">Analyze Dependencies For:</label>
                    <input type="text" class="form-control" id="dependencyFile"
                           placeholder="Enter file path (e.g., dashboard/pages/files.php)">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Analysis Type:</label>
                    <select class="form-select" id="analysisType">
                        <option value="requires">What does this file require?</option>
                        <option value="required_by">What files require this?</option>
                        <option value="similar">Find similar files</option>
                        <option value="imports">What does this import/include?</option>
                    </select>
                </div>

                <button class="btn btn-success w-100" onclick="analyzeDependencies()">
                    <i class="fas fa-sitemap"></i> Analyze Dependencies
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Common Code Patterns -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-code text-primary me-2"></i>
            Common Code Patterns Detected
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card bg-light border-0">
                    <div class="card-body">
                        <h6><i class="fas fa-database text-primary"></i> Database Patterns</h6>
                        <ul class="small mb-0">
                            <li>PDO connections: <span class="badge bg-primary" id="pattern-pdo">Loading...</span></li>
                            <li>Prepared statements: <span class="badge bg-success" id="pattern-prepared">Loading...</span></li>
                            <li>SQL queries: <span class="badge bg-info" id="pattern-sql">Loading...</span></li>
                        </ul>
                        <button class="btn btn-sm btn-outline-primary mt-2 w-100" onclick="searchPattern('PDO database')">
                            <i class="fas fa-search"></i> View Examples
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-light border-0">
                    <div class="card-body">
                        <h6><i class="fas fa-shield-alt text-success"></i> Security Patterns</h6>
                        <ul class="small mb-0">
                            <li>Input validation: <span class="badge bg-primary" id="pattern-validation">Loading...</span></li>
                            <li>CSRF protection: <span class="badge bg-success" id="pattern-csrf">Loading...</span></li>
                            <li>Authentication: <span class="badge bg-info" id="pattern-auth">Loading...</span></li>
                        </ul>
                        <button class="btn btn-sm btn-outline-success mt-2 w-100" onclick="searchPattern('security validation csrf')">
                            <i class="fas fa-search"></i> View Examples
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card bg-light border-0">
                    <div class="card-body">
                        <h6><i class="fas fa-cogs text-warning"></i> API Patterns</h6>
                        <ul class="small mb-0">
                            <li>REST endpoints: <span class="badge bg-primary" id="pattern-rest">Loading...</span></li>
                            <li>JSON responses: <span class="badge bg-success" id="pattern-json">Loading...</span></li>
                            <li>Error handling: <span class="badge bg-info" id="pattern-errors">Loading...</span></li>
                        </ul>
                        <button class="btn btn-sm btn-outline-warning mt-2 w-100" onclick="searchPattern('API endpoint JSON')">
                            <i class="fas fa-search"></i> View Examples
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Results Panel -->
<div class="card mt-4" id="resultsPanel" style="display: none;">
    <div class="card-header bg-dark text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>
                Pattern Analysis Results
            </h5>
            <button class="btn btn-sm btn-outline-light" onclick="closeResults()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <div class="card-body" id="resultsContent">
        <!-- Results will be loaded here -->
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    loadPatternStats();
});

// Load pattern statistics
async function loadPatternStats() {
    try {
        // PDO patterns
        const pdoResult = await fetch('../mcp/dispatcher.php?tool=semantic_search&query=PDO%20database%20connection&limit=100');
        const pdoData = await pdoResult.json();
        document.getElementById('pattern-pdo').textContent = pdoData.success ? pdoData.data.length : '0';

        // Prepared statements
        const preparedResult = await fetch('../mcp/dispatcher.php?tool=semantic_search&query=prepared%20statement%20execute&limit=100');
        const preparedData = await preparedResult.json();
        document.getElementById('pattern-prepared').textContent = preparedData.success ? preparedData.data.length : '0';

        // SQL queries
        const sqlResult = await fetch('../mcp/dispatcher.php?tool=semantic_search&query=SELECT%20FROM%20WHERE&limit=100');
        const sqlData = await sqlResult.json();
        document.getElementById('pattern-sql').textContent = sqlData.success ? sqlData.data.length : '0';

        // Validation
        const validationResult = await fetch('../mcp/dispatcher.php?tool=semantic_search&query=validation%20sanitize%20filter&limit=100');
        const validationData = await validationResult.json();
        document.getElementById('pattern-validation').textContent = validationData.success ? validationData.data.length : '0';

        // CSRF
        const csrfResult = await fetch('../mcp/dispatcher.php?tool=semantic_search&query=CSRF%20token%20protection&limit=100');
        const csrfData = await csrfResult.json();
        document.getElementById('pattern-csrf').textContent = csrfData.success ? csrfData.data.length : '0';

        // Authentication
        const authResult = await fetch('../mcp/dispatcher.php?tool=semantic_search&query=authentication%20login%20session&limit=100');
        const authData = await authResult.json();
        document.getElementById('pattern-auth').textContent = authData.success ? authData.data.length : '0';

        // REST
        const restResult = await fetch('../mcp/dispatcher.php?tool=semantic_search&query=REST%20API%20endpoint&limit=100');
        const restData = await restResult.json();
        document.getElementById('pattern-rest').textContent = restData.success ? restData.data.length : '0';

        // JSON
        const jsonResult = await fetch('../mcp/dispatcher.php?tool=semantic_search&query=json_encode%20response&limit=100');
        const jsonData = await jsonResult.json();
        document.getElementById('pattern-json').textContent = jsonData.success ? jsonData.data.length : '0';

        // Errors
        const errorResult = await fetch('../mcp/dispatcher.php?tool=semantic_search&query=error%20handling%20try%20catch&limit=100');
        const errorData = await errorResult.json();
        document.getElementById('pattern-errors').textContent = errorData.success ? errorData.data.length : '0';

    } catch (error) {
        console.error('Failed to load pattern stats:', error);
    }
}

// Search for pattern
async function searchPattern(query) {
    showResults('Searching for: ' + query);

    try {
        const response = await fetch('../mcp/dispatcher.php?tool=semantic_search&query=' + encodeURIComponent(query) + '&limit=20');
        const result = await response.json();

        if (result.success && result.data.length > 0) {
            let html = `
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Found ${result.data.length} files matching pattern: <strong>${query}</strong>
                </div>
                <div class="list-group">
            `;

            result.data.forEach(file => {
                const relevance = Math.round(file.relevance_score * 100);
                html += `
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">
                                    <i class="fas fa-file-code text-primary"></i>
                                    ${escapeHtml(file.file_path)}
                                </h6>
                                <p class="mb-1 small text-muted">${escapeHtml(file.file_preview || 'No preview available')}</p>
                                <div class="small">
                                    <span class="badge bg-secondary">${file.intelligence_type || 'unknown'}</span>
                                    <span class="badge bg-info">${formatBytes(file.file_size || 0)}</span>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="badge bg-success mb-2">${relevance}% match</div>
                                <br>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewFile('${escapeHtml(file.file_path)}')">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            document.getElementById('resultsContent').innerHTML = html;
        } else {
            document.getElementById('resultsContent').innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> No files found matching pattern: <strong>${query}</strong>
                </div>
            `;
        }
    } catch (error) {
        document.getElementById('resultsContent').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-times-circle"></i> Error searching: ${error.message}
            </div>
        `;
    }
}

// Search custom pattern
function searchCustomPattern() {
    const query = document.getElementById('customPattern').value.trim();
    if (query) {
        searchPattern(query);
    } else {
        alert('Please enter a pattern to search for');
    }
}

// Analyze dependencies
async function analyzeDependencies() {
    const file = document.getElementById('dependencyFile').value.trim();
    const type = document.getElementById('analysisType').value;

    if (!file) {
        alert('Please enter a file path');
        return;
    }

    showResults('Analyzing dependencies for: ' + file);

    try {
        let query = '';
        switch (type) {
            case 'requires':
                query = 'require include ' + file;
                break;
            case 'required_by':
                query = 'require include use import ' + file;
                break;
            case 'similar':
                query = file; // Will find similar files
                break;
            case 'imports':
                query = 'use import namespace ' + file;
                break;
        }

        const response = await fetch('../mcp/dispatcher.php?tool=semantic_search&query=' + encodeURIComponent(query) + '&limit=30');
        const result = await response.json();

        if (result.success && result.data.length > 0) {
            let html = `
                <div class="alert alert-info">
                    <i class="fas fa-sitemap"></i> Dependency Analysis: <strong>${type.replace('_', ' ')}</strong>
                    <br>Target: <code>${escapeHtml(file)}</code>
                    <br>Found ${result.data.length} related files
                </div>
                <div class="list-group">
            `;

            result.data.forEach(relatedFile => {
                const relevance = Math.round(relatedFile.relevance_score * 100);
                html += `
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">
                                    <i class="fas fa-link text-success"></i>
                                    ${escapeHtml(relatedFile.file_path)}
                                </h6>
                                <p class="mb-1 small text-muted">${escapeHtml(relatedFile.file_preview || 'No preview')}</p>
                            </div>
                            <div class="text-end">
                                <div class="badge bg-primary mb-2">${relevance}% related</div>
                                <br>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewFile('${escapeHtml(relatedFile.file_path)}')">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            document.getElementById('resultsContent').innerHTML = html;
        } else {
            document.getElementById('resultsContent').innerHTML = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> No dependencies found
                </div>
            `;
        }
    } catch (error) {
        document.getElementById('resultsContent').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-times-circle"></i> Error analyzing: ${error.message}
            </div>
        `;
    }
}

// View file
function viewFile(filePath) {
    window.location.href = '?page=files&view=' + encodeURIComponent(filePath);
}

// Show results panel
function showResults(message) {
    const panel = document.getElementById('resultsPanel');
    panel.style.display = 'block';
    document.getElementById('resultsContent').innerHTML = `
        <div class="text-center py-5">
            <i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>
            <p class="text-muted">${message}</p>
        </div>
    `;
    panel.scrollIntoView({ behavior: 'smooth' });
}

// Close results
function closeResults() {
    document.getElementById('resultsPanel').style.display = 'none';
}

// Helper functions
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatBytes(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
}

// File Type Chart
const fileTypeCtx = document.getElementById('fileTypeChart').getContext('2d');
new Chart(fileTypeCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($fileTypes, 'extension')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($fileTypes, 'count')); ?>,
            backgroundColor: [
                '#667eea', '#f093fb', '#4facfe', '#fa709a', '#ffa500',
                '#00f2fe', '#764ba2', '#f5576c', '#fed6e3', '#a8edea'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'right'
            }
        }
    }
});

// Server Chart
const serverCtx = document.getElementById('serverChart').getContext('2d');
new Chart(serverCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_column($serverDist, 'server_id')); ?>,
        datasets: [{
            label: 'Files',
            data: <?php echo json_encode(array_column($serverDist, 'file_count')); ?>,
            backgroundColor: '#667eea'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
