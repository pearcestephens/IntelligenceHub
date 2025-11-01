<?php
/**
 * MCP Tools Testing Dashboard
 * Professional interface for testing all 13 MCP tools
 * 
 * @package CIS Intelligence Dashboard
 * @version 2.1.0
 */

if (!defined('DASHBOARD_ACCESS')) {
    die('Direct access not permitted');
}

// Get database connection
$db = getDbConnection();

// Default values in case DB is unavailable
$stats = [
    'total_files' => 0,
    'categorized_files' => 0,
    'total_categories' => 0,
    'avg_readability' => 0
];
$topCategories = [];
$toolUsage = [];

// Get real statistics (with error handling)
if ($db) {
    try {
        $statsQuery = "
            SELECT 
                COUNT(DISTINCT content_id) as total_files,
                COUNT(DISTINCT CASE WHEN category_id IS NOT NULL THEN content_id END) as categorized_files,
                COUNT(DISTINCT category_id) as total_categories,
                AVG(readability_score) as avg_readability
            FROM intelligence_content
        ";
        $result = $db->query($statsQuery);
        if ($result) {
            $stats = $result->fetch(PDO::FETCH_ASSOC);
        }

        // Get category breakdown
        $categoryQuery = "
            SELECT 
                category_name,
                priority_weight,
                file_count
            FROM kb_categories
            WHERE file_count > 0
            ORDER BY priority_weight DESC, file_count DESC
            LIMIT 10
        ";
        $result = $db->query($categoryQuery);
        if ($result) {
            $topCategories = $result->fetchAll(PDO::FETCH_ASSOC);
        }

        // Get recent tool usage from analytics
        $analyticsQuery = "
            SELECT 
                query_type as tool_name,
                COUNT(*) as call_count,
                AVG(execution_time_ms) as avg_time,
                AVG(results_found) as avg_results
            FROM mcp_search_analytics
            WHERE timestamp >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            GROUP BY query_type
            ORDER BY call_count DESC
        ";
        $result = $db->query($analyticsQuery);
        if ($result) {
            $toolUsage = $result->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        error_log("MCP Tools page DB error: " . $e->getMessage());
        // Keep using default values
    }
}
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="page-header">
            <h1><i class="fas fa-tools text-primary"></i> MCP Tools Testing Dashboard</h1>
            <p class="text-muted">Professional interface for testing all 13 Model Context Protocol tools</p>
        </div>
    </div>
</div>

<!-- System Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon">
                <i class="fas fa-database"></i>
            </div>
            <div class="stat-details">
                <div class="stat-value"><?php echo number_format($stats['total_files']); ?></div>
                <div class="stat-label">Total Files Indexed</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-success">
            <div class="stat-icon">
                <i class="fas fa-tags"></i>
            </div>
            <div class="stat-details">
                <div class="stat-value"><?php echo number_format($stats['categorized_files']); ?></div>
                <div class="stat-label">Categorized Files</div>
                <div class="stat-sublabel"><?php echo $stats['total_files'] > 0 ? round(($stats['categorized_files'] / $stats['total_files']) * 100, 1) : 0; ?>% coverage</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-info">
            <div class="stat-icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="stat-details">
                <div class="stat-value"><?php echo $stats['total_categories']; ?></div>
                <div class="stat-label">Active Categories</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-warning">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-details">
                <div class="stat-value"><?php echo round($stats['avg_readability'], 1); ?></div>
                <div class="stat-label">Avg Readability Score</div>
            </div>
        </div>
    </div>
</div>

<!-- Tool Testing Interface -->
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-vial"></i> Tool Testing Interface</h5>
            </div>
            <div class="card-body">
                
                <!-- Tool Selector -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Select MCP Tool</label>
                    <select class="form-select form-select-lg" id="toolSelector">
                        <optgroup label="Search Tools">
                            <option value="semantic_search">1. semantic_search - Natural language search</option>
                            <option value="find_code">2. find_code - Find functions, classes, patterns</option>
                            <option value="search_by_category">3. search_by_category - Search within categories</option>
                            <option value="find_similar">4. find_similar - Find similar files</option>
                            <option value="explore_by_tags">5. explore_by_tags - Search by semantic tags</option>
                        </optgroup>
                        <optgroup label="Analysis Tools">
                            <option value="analyze_file">6. analyze_file - Deep file analysis</option>
                            <option value="get_file_content">7. get_file_content - Get file with context</option>
                            <option value="get_stats">8. get_stats - System-wide statistics</option>
                            <option value="top_keywords">9. top_keywords - Most common keywords</option>
                        </optgroup>
                        <optgroup label="Business Tools">
                            <option value="list_categories">10. list_categories - List all categories</option>
                            <option value="get_analytics">11. get_analytics - Analytics data</option>
                        </optgroup>
                        <optgroup label="Infrastructure Tools">
                            <option value="list_satellites">12. list_satellites - List satellite servers</option>
                            <option value="sync_satellite">13. sync_satellite - Trigger satellite sync</option>
                        </optgroup>
                    </select>
                </div>
                
                <!-- Dynamic Parameter Form -->
                <div id="parameterForm"></div>
                
                <!-- Action Buttons -->
                <div class="d-flex gap-2 mt-3">
                    <button type="button" class="btn btn-primary btn-lg" id="runTestBtn">
                        <i class="fas fa-play"></i> Run Test
                    </button>
                    <button type="button" class="btn btn-secondary" id="clearResultsBtn">
                        <i class="fas fa-eraser"></i> Clear Results
                    </button>
                    <button type="button" class="btn btn-info" id="copyResultBtn" style="display: none;">
                        <i class="fas fa-copy"></i> Copy Results
                    </button>
                </div>
                
                <!-- Test Results -->
                <div id="testResults" class="mt-4" style="display: none;">
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fas fa-info-circle"></i> Test Results</h6>
                            <span id="executionTime" class="badge bg-secondary"></span>
                        </div>
                    </div>
                    <div id="resultsContent"></div>
                </div>
                
            </div>
        </div>
    </div>
    
    <!-- Tool Usage Statistics -->
    <div class="col-lg-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Last 24h Tool Usage</h5>
            </div>
            <div class="card-body">
                <?php if (empty($toolUsage)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p>No tool usage data yet</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($toolUsage as $tool): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-truncate"><?php echo htmlspecialchars($tool['tool_name']); ?></strong>
                                    <span class="badge bg-primary"><?php echo $tool['call_count']; ?> calls</span>
                                </div>
                                <div class="small text-muted">
                                    <i class="fas fa-clock"></i> <?php echo round($tool['avg_time'], 1); ?>ms avg
                                    <span class="mx-2">|</span>
                                    <i class="fas fa-search"></i> <?php echo round($tool['avg_results'], 1); ?> results avg
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Top Categories -->
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-layer-group"></i> Top Categories</h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php foreach ($topCategories as $cat): ?>
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-truncate"><?php echo htmlspecialchars($cat['category_name']); ?></div>
                                    <div class="small text-muted">
                                        Priority: <?php echo $cat['priority_weight']; ?>
                                    </div>
                                </div>
                                <span class="badge bg-secondary"><?php echo number_format($cat['file_count']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tool Documentation -->
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-book"></i> MCP Tools Documentation</h5>
            </div>
            <div class="card-body">
                <div class="accordion" id="toolDocs">
                    <!-- Search Tools -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#searchTools">
                                <i class="fas fa-search text-primary me-2"></i> Search Tools (5)
                            </button>
                        </h2>
                        <div id="searchTools" class="accordion-collapse collapse" data-bs-parent="#toolDocs">
                            <div class="accordion-body">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Tool</th>
                                            <th>Description</th>
                                            <th>Key Parameters</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>semantic_search</code></td>
                                            <td>Natural language search across all indexed files</td>
                                            <td>query, limit, file_type</td>
                                        </tr>
                                        <tr>
                                            <td><code>find_code</code></td>
                                            <td>Find specific code patterns, functions, or classes</td>
                                            <td>pattern, search_in, limit</td>
                                        </tr>
                                        <tr>
                                            <td><code>search_by_category</code></td>
                                            <td>Search within specific business categories</td>
                                            <td>query, category_name, limit</td>
                                        </tr>
                                        <tr>
                                            <td><code>find_similar</code></td>
                                            <td>Find files similar to a reference file</td>
                                            <td>file_path, limit</td>
                                        </tr>
                                        <tr>
                                            <td><code>explore_by_tags</code></td>
                                            <td>Search by semantic tags (inventory, security, etc.)</td>
                                            <td>semantic_tags, match_all, limit</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Analysis Tools -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#analysisTools">
                                <i class="fas fa-chart-line text-success me-2"></i> Analysis Tools (4)
                            </button>
                        </h2>
                        <div id="analysisTools" class="accordion-collapse collapse" data-bs-parent="#toolDocs">
                            <div class="accordion-body">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Tool</th>
                                            <th>Description</th>
                                            <th>Key Parameters</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>analyze_file</code></td>
                                            <td>Deep analysis of a specific file with metrics</td>
                                            <td>file_path</td>
                                        </tr>
                                        <tr>
                                            <td><code>get_file_content</code></td>
                                            <td>Retrieve file content with related context</td>
                                            <td>file_path, include_related</td>
                                        </tr>
                                        <tr>
                                            <td><code>get_stats</code></td>
                                            <td>System-wide statistics and breakdowns</td>
                                            <td>breakdown_by</td>
                                        </tr>
                                        <tr>
                                            <td><code>top_keywords</code></td>
                                            <td>Most frequently occurring keywords</td>
                                            <td>unit_id, limit</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Business Tools -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#businessTools">
                                <i class="fas fa-briefcase text-info me-2"></i> Business Tools (2)
                            </button>
                        </h2>
                        <div id="businessTools" class="accordion-collapse collapse" data-bs-parent="#toolDocs">
                            <div class="accordion-body">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Tool</th>
                                            <th>Description</th>
                                            <th>Key Parameters</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>list_categories</code></td>
                                            <td>List all business categories with statistics</td>
                                            <td>parent_id, min_priority</td>
                                        </tr>
                                        <tr>
                                            <td><code>get_analytics</code></td>
                                            <td>Retrieve analytics data (7 action types)</td>
                                            <td>action, timeframe, limit</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Infrastructure Tools -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#infraTools">
                                <i class="fas fa-server text-warning me-2"></i> Infrastructure Tools (2)
                            </button>
                        </h2>
                        <div id="infraTools" class="accordion-collapse collapse" data-bs-parent="#toolDocs">
                            <div class="accordion-body">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Tool</th>
                                            <th>Description</th>
                                            <th>Key Parameters</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><code>list_satellites</code></td>
                                            <td>List all satellite servers with status</td>
                                            <td>None</td>
                                        </tr>
                                        <tr>
                                            <td><code>sync_satellite</code></td>
                                            <td>Trigger synchronization with satellite</td>
                                            <td>satellite_unit_id</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Tool parameter definitions
const toolParameters = {
    semantic_search: [
        { name: 'query', type: 'text', label: 'Search Query', placeholder: 'e.g., inventory management', required: true },
        { name: 'limit', type: 'number', label: 'Result Limit', value: 10, min: 1, max: 100 },
        { name: 'file_type', type: 'select', label: 'File Type (optional)', options: ['', 'php', 'js', 'css', 'md', 'txt'] }
    ],
    find_code: [
        { name: 'pattern', type: 'text', label: 'Code Pattern', placeholder: 'e.g., validateTransfer', required: true },
        { name: 'search_in', type: 'select', label: 'Search In', value: 'all', options: ['all', 'functions', 'classes'] },
        { name: 'limit', type: 'number', label: 'Result Limit', value: 20, min: 1, max: 100 }
    ],
    search_by_category: [
        { name: 'query', type: 'text', label: 'Search Query', placeholder: 'e.g., stock transfer', required: true },
        { name: 'category_name', type: 'text', label: 'Category Name', placeholder: 'e.g., Inventory Management', required: true },
        { name: 'limit', type: 'number', label: 'Result Limit', value: 20, min: 1, max: 100 }
    ],
    find_similar: [
        { name: 'file_path', type: 'text', label: 'Reference File Path', placeholder: 'e.g., modules/transfers/pack.php', required: true },
        { name: 'limit', type: 'number', label: 'Result Limit', value: 10, min: 1, max: 50 }
    ],
    explore_by_tags: [
        { name: 'semantic_tags', type: 'text', label: 'Tags (comma-separated)', placeholder: 'e.g., inventory, security', required: true },
        { name: 'match_all', type: 'checkbox', label: 'Match All Tags', value: false },
        { name: 'limit', type: 'number', label: 'Result Limit', value: 20, min: 1, max: 100 }
    ],
    analyze_file: [
        { name: 'file_path', type: 'text', label: 'File Path', placeholder: 'e.g., modules/transfers/pack.php', required: true }
    ],
    get_file_content: [
        { name: 'file_path', type: 'text', label: 'File Path', placeholder: 'e.g., api/save_transfer.php', required: true },
        { name: 'include_related', type: 'checkbox', label: 'Include Related Files', value: true }
    ],
    get_stats: [
        { name: 'breakdown_by', type: 'select', label: 'Breakdown By', value: 'unit', options: ['unit', 'category', 'file_type'] }
    ],
    top_keywords: [
        { name: 'unit_id', type: 'number', label: 'Unit ID (optional)', placeholder: '2', min: 1 },
        { name: 'limit', type: 'number', label: 'Result Limit', value: 50, min: 1, max: 200 }
    ],
    list_categories: [
        { name: 'parent_id', type: 'number', label: 'Parent ID (optional)', placeholder: 'Leave empty for all', min: 1 },
        { name: 'include_children', type: 'checkbox', label: 'Include Children', value: true },
        { name: 'min_priority', type: 'number', label: 'Min Priority', value: 1.0, step: 0.1, min: 0 },
        { name: 'order_by', type: 'select', label: 'Order By', value: 'priority', options: ['priority', 'name', 'file_count'] }
    ],
    get_analytics: [
        { name: 'action', type: 'select', label: 'Analytics Action', value: 'overview', options: ['overview', 'hourly', 'failed', 'slow', 'popular_queries', 'tool_usage', 'category_performance'] },
        { name: 'timeframe', type: 'select', label: 'Timeframe', value: '24h', options: ['1h', '6h', '24h', '7d', '30d'] },
        { name: 'limit', type: 'number', label: 'Result Limit', value: 50, min: 1, max: 200 },
        { name: 'threshold', type: 'number', label: 'Slow Query Threshold (ms)', value: 500, min: 100 }
    ],
    list_satellites: [],
    sync_satellite: [
        { name: 'satellite_unit_id', type: 'number', label: 'Satellite Unit ID', placeholder: 'e.g., 1, 3, 4', required: true, min: 1 }
    ]
};

// Render parameter form based on selected tool
function renderParameterForm(toolName) {
    const params = toolParameters[toolName] || [];
    const formHtml = params.map(param => {
        let inputHtml = '';
        
        if (param.type === 'text') {
            inputHtml = `<input type="text" class="form-control" id="param_${param.name}" 
                placeholder="${param.placeholder || ''}" ${param.required ? 'required' : ''}>`;
        } else if (param.type === 'number') {
            inputHtml = `<input type="number" class="form-control" id="param_${param.name}" 
                value="${param.value || ''}" placeholder="${param.placeholder || ''}"
                ${param.min !== undefined ? `min="${param.min}"` : ''} 
                ${param.max !== undefined ? `max="${param.max}"` : ''}
                ${param.step !== undefined ? `step="${param.step}"` : ''}
                ${param.required ? 'required' : ''}>`;
        } else if (param.type === 'select') {
            const options = param.options.map(opt => 
                `<option value="${opt}" ${opt === param.value ? 'selected' : ''}>${opt || '(none)'}</option>`
            ).join('');
            inputHtml = `<select class="form-select" id="param_${param.name}">${options}</select>`;
        } else if (param.type === 'checkbox') {
            inputHtml = `<div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="param_${param.name}" 
                    ${param.value ? 'checked' : ''}>
                <label class="form-check-label" for="param_${param.name}">${param.label}</label>
            </div>`;
            return `<div class="mb-3">${inputHtml}</div>`;
        }
        
        return `
            <div class="mb-3">
                <label for="param_${param.name}" class="form-label fw-bold">
                    ${param.label}${param.required ? ' <span class="text-danger">*</span>' : ''}
                </label>
                ${inputHtml}
            </div>
        `;
    }).join('');
    
    document.getElementById('parameterForm').innerHTML = formHtml || 
        '<div class="alert alert-info"><i class="fas fa-info-circle"></i> This tool requires no parameters.</div>';
}

// Collect parameter values
function collectParameters(toolName) {
    const params = toolParameters[toolName] || [];
    const values = {};
    
    params.forEach(param => {
        const element = document.getElementById(`param_${param.name}`);
        if (!element) return;
        
        if (param.type === 'checkbox') {
            values[param.name] = element.checked;
        } else if (param.type === 'number') {
            const val = element.value;
            if (val !== '') values[param.name] = parseFloat(val);
        } else if (param.name === 'semantic_tags') {
            // Split comma-separated tags
            values[param.name] = element.value.split(',').map(t => t.trim()).filter(t => t);
        } else {
            if (element.value) values[param.name] = element.value;
        }
    });
    
    return values;
}

// Run MCP tool test
async function runToolTest() {
    const toolName = document.getElementById('toolSelector').value;
    const params = collectParameters(toolName);
    
    // Show loading
    const resultsDiv = document.getElementById('testResults');
    const resultsContent = document.getElementById('resultsContent');
    resultsDiv.style.display = 'block';
    resultsContent.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Running test...</p></div>';
    
    const startTime = performance.now();
    
    try {
        const response = await fetch('https://gpt.ecigdis.co.nz/mcp/server_v2_complete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                jsonrpc: '2.0',
                method: 'tools/call',
                params: {
                    name: toolName,
                    arguments: params
                },
                id: 1
            })
        });
        
        const endTime = performance.now();
        const executionTime = Math.round(endTime - startTime);
        
        const data = await response.json();
        
        // Update execution time
        document.getElementById('executionTime').textContent = `${executionTime}ms`;
        
        // Display results
        if (data.result) {
            displayResults(data.result, toolName);
            document.getElementById('copyResultBtn').style.display = 'inline-block';
        } else if (data.error) {
            resultsContent.innerHTML = `
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-triangle"></i> Error</h6>
                    <p class="mb-0"><strong>Code:</strong> ${data.error.code}</p>
                    <p class="mb-0"><strong>Message:</strong> ${data.error.message}</p>
                </div>
            `;
        }
        
    } catch (error) {
        resultsContent.innerHTML = `
            <div class="alert alert-danger">
                <h6><i class="fas fa-exclamation-triangle"></i> Request Failed</h6>
                <p class="mb-0">${error.message}</p>
            </div>
        `;
    }
}

// Display formatted results
function displayResults(result, toolName) {
    const resultsContent = document.getElementById('resultsContent');
    let html = '';
    
    // Success indicator
    html += `<div class="alert alert-success mb-3">
        <i class="fas fa-check-circle"></i> Test completed successfully
    </div>`;
    
    // Results count or summary
    if (result.results_count !== undefined) {
        html += `<div class="mb-3">
            <span class="badge bg-primary fs-6">${result.results_count} results found</span>
        </div>`;
    } else if (result.summary) {
        html += `<div class="mb-3">
            <h6>Summary</h6>
            <pre class="bg-light p-3 rounded"><code>${JSON.stringify(result.summary, null, 2)}</code></pre>
        </div>`;
    }
    
    // Display results in table or JSON
    if (result.results && Array.isArray(result.results)) {
        html += '<div class="table-responsive"><table class="table table-sm table-hover">';
        html += '<thead class="table-light"><tr>';
        
        // Get column headers from first result
        const firstResult = result.results[0];
        if (firstResult) {
            Object.keys(firstResult).forEach(key => {
                if (!['content_summary', 'entities_detected', 'extracted_keywords', 'semantic_tags'].includes(key)) {
                    html += `<th>${key}</th>`;
                }
            });
        }
        html += '</tr></thead><tbody>';
        
        // Add rows
        result.results.forEach((row, idx) => {
            if (idx < 10) { // Limit to first 10 for display
                html += '<tr>';
                Object.entries(row).forEach(([key, value]) => {
                    if (!['content_summary', 'entities_detected', 'extracted_keywords', 'semantic_tags'].includes(key)) {
                        let displayValue = value;
                        if (typeof value === 'string' && value.length > 100) {
                            displayValue = value.substring(0, 100) + '...';
                        }
                        html += `<td>${displayValue !== null ? displayValue : '<em class="text-muted">null</em>'}</td>`;
                    }
                });
                html += '</tr>';
            }
        });
        html += '</tbody></table></div>';
        
        if (result.results.length > 10) {
            html += `<p class="text-muted"><em>Showing first 10 of ${result.results.length} results</em></p>`;
        }
    } else if (result.data) {
        // Analytics or other structured data
        html += '<div class="mb-3"><h6>Data</h6>';
        html += '<pre class="bg-light p-3 rounded" style="max-height: 400px; overflow-y: auto;">';
        html += `<code>${JSON.stringify(result.data, null, 2)}</code></pre></div>`;
    }
    
    // Full JSON response (collapsible)
    html += `
        <div class="mt-3">
            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#fullJson">
                <i class="fas fa-code"></i> View Full JSON Response
            </button>
            <div class="collapse mt-2" id="fullJson">
                <pre class="bg-dark text-light p-3 rounded" style="max-height: 400px; overflow-y: auto;"><code>${JSON.stringify(result, null, 2)}</code></pre>
            </div>
        </div>
    `;
    
    resultsContent.innerHTML = html;
}

// Copy results to clipboard
function copyResults() {
    const resultsContent = document.getElementById('resultsContent');
    const text = resultsContent.innerText;
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.getElementById('copyResultBtn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(() => { btn.innerHTML = originalText; }, 2000);
    });
}

// Clear results
function clearResults() {
    document.getElementById('testResults').style.display = 'none';
    document.getElementById('resultsContent').innerHTML = '';
    document.getElementById('copyResultBtn').style.display = 'none';
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    const toolSelector = document.getElementById('toolSelector');
    const runTestBtn = document.getElementById('runTestBtn');
    const clearResultsBtn = document.getElementById('clearResultsBtn');
    const copyResultBtn = document.getElementById('copyResultBtn');
    
    // Render initial form
    renderParameterForm(toolSelector.value);
    
    // Event listeners
    toolSelector.addEventListener('change', function() {
        renderParameterForm(this.value);
        clearResults();
    });
    
    runTestBtn.addEventListener('click', runToolTest);
    clearResultsBtn.addEventListener('click', clearResults);
    copyResultBtn.addEventListener('click', copyResults);
});
</script>
