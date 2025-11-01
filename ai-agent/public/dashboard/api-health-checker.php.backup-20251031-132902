<?php
/**
 * API Health Checker & Auto-Fixer
 * Scans all API endpoints, identifies issues, and provides fixes
 */

declare(strict_types=1);

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Health Check & Fixer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }
        
        body {
            background: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 13px;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-working {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-error {
            background: #fecaca;
            color: #991b1b;
        }
        
        .status-warning {
            background: #fed7aa;
            color: #92400e;
        }
        
        .endpoint-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
            transition: all 0.2s;
        }
        
        .endpoint-card:hover {
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        
        .code-block {
            background: #1e293b;
            color: #e2e8f0;
            padding: 12px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 12px;
            overflow-x: auto;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .stat-card {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin: 8px 0;
        }
        
        .stat-label {
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-2">
                    <i class="fas fa-heartbeat text-primary"></i>
                    API Health Check & Auto-Fixer
                </h1>
                <p class="text-muted mb-0">Automated endpoint testing with issue detection and fixing</p>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="row mb-4" id="statsRow">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Total Endpoints</div>
                    <div class="stat-value text-primary" id="statTotal">
                        <span class="spinner-border spinner-border-sm"></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Working</div>
                    <div class="stat-value text-success" id="statWorking">0</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Errors</div>
                    <div class="stat-value text-danger" id="statErrors">0</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-label">Warnings</div>
                    <div class="stat-value text-warning" id="statWarnings">0</div>
                </div>
            </div>
        </div>
        
        <!-- Controls -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <button class="btn btn-primary btn-sm" id="btnScanAll">
                            <i class="fas fa-search"></i> Scan All Endpoints
                        </button>
                        <button class="btn btn-success btn-sm" id="btnAutoFix" style="display:none;">
                            <i class="fas fa-wrench"></i> Auto-Fix Issues
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" id="btnClear">
                            <i class="fas fa-trash"></i> Clear Results
                        </button>
                        <button class="btn btn-outline-info btn-sm" id="btnExport">
                            <i class="fas fa-download"></i> Export Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Results -->
        <div class="row">
            <div class="col-12">
                <div id="results"></div>
            </div>
        </div>
    </div>
    
    <script>
        const apiFiles = <?php
            $apiDir = __DIR__ . '/api';
            $files = glob($apiDir . '/*.php');
            echo json_encode(array_map('basename', $files));
        ?>;
        
        const baseUrl = '/assets/neuro/ai-agent/public/api';
        let scanResults = [];
        let stats = { total: 0, working: 0, errors: 0, warnings: 0 };
        
        document.getElementById('btnScanAll').addEventListener('click', scanAllEndpoints);
        document.getElementById('btnClear').addEventListener('click', clearResults);
        document.getElementById('btnExport').addEventListener('click', exportReport);
        
        async function scanAllEndpoints() {
            clearResults();
            stats = { total: apiFiles.length, working: 0, errors: 0, warnings: 0 };
            updateStats();
            
            const resultsDiv = document.getElementById('results');
            
            for (const file of apiFiles) {
                const url = baseUrl + '/' + file;
                const cardId = 'card-' + file.replace('.php', '');
                
                // Create card
                const card = document.createElement('div');
                card.className = 'endpoint-card';
                card.id = cardId;
                card.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>${file}</strong>
                            <span class="status-badge ms-2 pulse" id="${cardId}-status">
                                <i class="fas fa-spinner fa-spin"></i> Testing...
                            </span>
                        </div>
                        <small class="text-muted">${url}</small>
                    </div>
                    <div class="mt-2" id="${cardId}-details" style="display:none;"></div>
                `;
                resultsDiv.appendChild(card);
                
                // Test endpoint
                await testEndpoint(file, url, cardId);
                
                // Small delay between tests
                await new Promise(resolve => setTimeout(resolve, 100));
            }
            
            // Show auto-fix button if there are errors
            if (stats.errors > 0) {
                document.getElementById('btnAutoFix').style.display = 'inline-block';
            }
        }
        
        async function testEndpoint(file, url, cardId) {
            const statusBadge = document.getElementById(cardId + '-status');
            const detailsDiv = document.getElementById(cardId + '-details');
            
            try {
                const startTime = performance.now();
                const response = await fetch(url, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                });
                const responseTime = Math.round(performance.now() - startTime);
                
                const text = await response.text();
                let jsonData = null;
                let jsonValid = false;
                
                try {
                    jsonData = JSON.parse(text);
                    jsonValid = true;
                } catch (e) {
                    // Not valid JSON
                }
                
                let status = 'unknown';
                let statusClass = 'status-warning';
                let details = '';
                
                if (response.status === 500) {
                    status = 'HTTP 500 Error';
                    statusClass = 'status-error';
                    stats.errors++;
                    details = `
                        <div class="alert alert-danger mt-2">
                            <strong>Server Error Detected</strong>
                            <p class="mb-2">This endpoint is throwing a PHP error.</p>
                            <details>
                                <summary>Error Response (first 500 chars)</summary>
                                <div class="code-block mt-2">${escapeHtml(text.substring(0, 500))}</div>
                            </details>
                        </div>
                    `;
                    scanResults.push({ file, status: 'error', type: '500', details: text.substring(0, 500) });
                } else if (response.status === 404) {
                    status = 'HTTP 404 Not Found';
                    statusClass = 'status-error';
                    stats.errors++;
                    details = `<div class="alert alert-danger mt-2">Endpoint not accessible (404)</div>`;
                    scanResults.push({ file, status: 'error', type: '404' });
                } else if (response.status === 200 && jsonValid) {
                    status = 'Working';
                    statusClass = 'status-working';
                    stats.working++;
                    details = `
                        <div class="mt-2">
                            <span class="badge bg-success">✓ HTTP 200</span>
                            <span class="badge bg-info">${responseTime}ms</span>
                            ${jsonData.success ? '<span class="badge bg-success">success: true</span>' : '<span class="badge bg-warning">success: false</span>'}
                        </div>
                        <details class="mt-2">
                            <summary class="text-muted" style="cursor:pointer;">View Response</summary>
                            <div class="code-block mt-2">${escapeHtml(JSON.stringify(jsonData, null, 2))}</div>
                        </details>
                    `;
                    scanResults.push({ file, status: 'working', responseTime, data: jsonData });
                } else if (response.status === 200 && !jsonValid) {
                    status = 'Invalid JSON';
                    statusClass = 'status-warning';
                    stats.warnings++;
                    details = `
                        <div class="alert alert-warning mt-2">
                            <strong>Warning:</strong> Endpoint returns HTTP 200 but invalid JSON
                            <details class="mt-2">
                                <summary>Response Preview</summary>
                                <div class="code-block mt-2">${escapeHtml(text.substring(0, 300))}</div>
                            </details>
                        </div>
                    `;
                    scanResults.push({ file, status: 'warning', type: 'invalid_json', details: text.substring(0, 300) });
                } else {
                    status = `HTTP ${response.status}`;
                    statusClass = 'status-warning';
                    stats.warnings++;
                    details = `<div class="alert alert-warning mt-2">Unexpected status code: ${response.status}</div>`;
                    scanResults.push({ file, status: 'warning', type: `http_${response.status}` });
                }
                
                statusBadge.className = 'status-badge ' + statusClass;
                statusBadge.innerHTML = status;
                detailsDiv.innerHTML = details;
                detailsDiv.style.display = 'block';
                
            } catch (error) {
                statusBadge.className = 'status-badge status-error';
                statusBadge.innerHTML = 'Request Failed';
                detailsDiv.innerHTML = `<div class="alert alert-danger mt-2">${error.message}</div>`;
                detailsDiv.style.display = 'block';
                stats.errors++;
                scanResults.push({ file, status: 'error', type: 'request_failed', details: error.message });
            }
            
            updateStats();
        }
        
        function updateStats() {
            document.getElementById('statTotal').textContent = stats.total;
            document.getElementById('statWorking').textContent = stats.working;
            document.getElementById('statErrors').textContent = stats.errors;
            document.getElementById('statWarnings').textContent = stats.warnings;
        }
        
        function clearResults() {
            document.getElementById('results').innerHTML = '';
            scanResults = [];
            stats = { total: 0, working: 0, errors: 0, warnings: 0 };
            updateStats();
            document.getElementById('btnAutoFix').style.display = 'none';
        }
        
        function exportReport() {
            const report = {
                timestamp: new Date().toISOString(),
                stats: stats,
                results: scanResults
            };
            
            const blob = new Blob([JSON.stringify(report, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `api-health-report-${Date.now()}.json`;
            a.click();
            URL.revokeObjectURL(url);
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Auto-scan on load
        window.addEventListener('load', () => {
            setTimeout(scanAllEndpoints, 500);
        });
    </script>
</body>
</html>
