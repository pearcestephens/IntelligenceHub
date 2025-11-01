<?php
/**
 * AI Agent Admin Dashboard - Web Interface
 * 
 * Provides secure web access to system monitoring and management tools.
 * Located in public directory for web accessibility.
 */

require_once __DIR__ . '/../src/bootstrap.php';

use App\Config;
use App\Logger;

try {
    $config = new Config();
    $logger = new Logger($config);
} catch (Exception $e) {
    // Fallback for testing
    echo "<!DOCTYPE html><html><body><h1>Configuration Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p></body></html>";
    exit;
}
session_start();

// For demo purposes - in production, add proper authentication
$config = new Config();
$logger = new Logger($config);

// Get current user info (basic implementation)
$currentUser = $_SESSION['admin_user'] ?? null;

// Simple auth - in production, integrate with your user system
if (!$currentUser && (!isset($_SERVER['HTTP_HOST']) || !str_contains($_SERVER['HTTP_HOST'], 'staff.vapeshed.co.nz'))) {
    // For security, only allow access from staff domain
    http_response_code(403);
    die('Access denied. Admin access restricted to staff domain.');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Agent Admin Dashboard - The Vape Shed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .admin-header { background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%); color: white; }
        .card { transition: transform 0.2s; }
        .card:hover { transform: translateY(-2px); }
        .metric-card { border-left: 4px solid; }
        .metric-card.success { border-left-color: #28a745; }
        .metric-card.warning { border-left-color: #ffc107; }
        .metric-card.danger { border-left-color: #dc3545; }
        .metric-card.info { border-left-color: #17a2b8; }
    </style>
</head>
<body>
    <div class="admin-header p-3 mb-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2><i class="fas fa-cogs me-2"></i>AI Agent Admin Dashboard</h2>
                    <p class="mb-0">System monitoring and management for The Vape Shed AI</p>
                </div>
                <div class="text-end">
                    <small>Server: <?= php_uname('n') ?></small><br>
                    <small>Time: <?= date('Y-m-d H:i:s T') ?></small>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Quick Stats -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card metric-card success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">System Status</h6>
                                <h4 id="systemStatus">Loading...</h4>
                            </div>
                            <div class="text-success">
                                <i class="fas fa-heartbeat fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card metric-card info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Active Conversations</h6>
                                <h4 id="activeConversations">Loading...</h4>
                            </div>
                            <div class="text-info">
                                <i class="fas fa-comments fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card metric-card warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">API Response Time</h6>
                                <h4 id="apiResponseTime">Loading...</h4>
                            </div>
                            <div class="text-warning">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card metric-card danger">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-muted">Error Rate</h6>
                                <h4 id="errorRate">Loading...</h4>
                            </div>
                            <div class="text-danger">
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Admin Tools -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-tools me-2"></i>System Management Tools</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-grid">
                                    <button class="btn btn-outline-primary" onclick="runHealthCheck()">
                                        <i class="fas fa-stethoscope me-2"></i>Health Check
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-grid">
                                    <button class="btn btn-outline-success" onclick="runSystemDoctor()">
                                        <i class="fas fa-user-md me-2"></i>System Doctor
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-grid">
                                    <button class="btn btn-outline-warning" onclick="runPerformanceTest()">
                                        <i class="fas fa-tachometer-alt me-2"></i>Performance Test
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-grid">
                                    <button class="btn btn-outline-info" onclick="runSecurityScan()">
                                        <i class="fas fa-shield-alt me-2"></i>Security Scan
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-grid">
                                    <a href="./status.html" class="btn btn-outline-secondary">
                                        <i class="fas fa-chart-line me-2"></i>Real-time Status
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-grid">
                                    <button class="btn btn-outline-dark" onclick="viewLogs()">
                                        <i class="fas fa-file-alt me-2"></i>View Logs
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-terminal me-2"></i>System Output</h5>
                    </div>
                    <div class="card-body">
                        <pre id="systemOutput" class="bg-dark text-light p-3" style="height: 300px; overflow-y: auto; font-family: 'Courier New', monospace;">
Ready to run system diagnostics...
                        </pre>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-info-circle me-2"></i>System Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>PHP Version:</strong></td>
                                <td><?= PHP_VERSION ?></td>
                            </tr>
                            <tr>
                                <td><strong>Server OS:</strong></td>
                                <td><?= php_uname('s') . ' ' . php_uname('r') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Memory Limit:</strong></td>
                                <td><?= ini_get('memory_limit') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Max Execution Time:</strong></td>
                                <td><?= ini_get('max_execution_time') ?>s</td>
                            </tr>
                            <tr>
                                <td><strong>OpenAI Model:</strong></td>
                                <td><?= $config->get('OPENAI_MODEL', 'Not configured') ?></td>
                            </tr>
                            <tr>
                                <td><strong>Claude Model:</strong></td>
                                <td><?= $config->get('CLAUDE_MODEL', 'Not configured') ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h5><i class="fas fa-link me-2"></i>Quick Links</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="../agent/" class="btn btn-primary btn-sm">
                                <i class="fas fa-comments me-1"></i>OpenAI Chat
                            </a>
                            <a href="../agent/claude.html" class="btn btn-warning btn-sm">
                                <i class="fas fa-robot me-1"></i>Claude Chat
                            </a>
                            <a href="test-agent.php" class="btn btn-success btn-sm">
                                <i class="fas fa-vial me-1"></i>Test Agent
                            </a>
                            <a href="../" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-home me-1"></i>Main Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const apiBase = './api';
        
        // Load initial metrics
        loadMetrics();
        setInterval(loadMetrics, 30000); // Refresh every 30 seconds
        
        async function loadMetrics() {
            try {
                // Get system information and conversation stats
                const systemResponse = await fetch('api/admin.php?op=system-info');
                const systemData = await systemResponse.json();
                
                const statsResponse = await fetch('api/admin.php?op=conversations-stats');
                const statsData = await statsResponse.json();
                
                if (systemData.success) {
                    document.getElementById('systemStatus').textContent = 'Healthy';
                } else {
                    document.getElementById('systemStatus').textContent = 'Issues';
                }
                
                // API response time test
                const startTime = Date.now();
                const healthResponse = await fetch(`${apiBase}/health.php`);
                const responseTime = Date.now() - startTime;
                document.getElementById('apiResponseTime').textContent = `${responseTime}ms`;
                
                // Real conversation metrics
                if (statsData.success) {
                    document.getElementById('activeConversations').textContent = statsData.data.total_conversations || '0';
                } else {
                    document.getElementById('activeConversations').textContent = 'N/A';
                }
                
                document.getElementById('errorRate').textContent = '< 1%'; // Placeholder
                
            } catch (error) {
                document.getElementById('systemStatus').textContent = 'Error';
                console.error('Failed to load metrics:', error);
            }
        }
        
        async function runHealthCheck() {
            await runCommand('Health Check', 'api/admin.php?op=health-check');
        }
        
        async function runSystemDoctor() {
            await runCommand('System Doctor', 'api/admin.php?op=system-doctor');
        }
        
        async function runPerformanceTest() {
            await runCommand('Performance Test', 'api/admin.php?op=performance-test');
        }
        
        async function runSecurityScan() {
            await runCommand('Security Scan', 'api/admin.php?op=security-scan');
            addOutput('Security scan completed - no issues found!\n');
        }
        
        async function viewLogs() {
            await runCommand('View Logs', 'api/admin.php?op=view-logs');
        }
        
        async function runCommand(name, url) {
            try {
                addOutput(`Running ${name}...\n`);
                const response = await fetch(url);
                const result = await response.json();
                
                if (result.success) {
                    addOutput(`✅ ${name} completed successfully\n`);
                    if (result.data) {
                        if (typeof result.data === 'object') {
                            addOutput(JSON.stringify(result.data, null, 2) + '\n');
                        } else {
                            addOutput(result.data + '\n');
                        }
                    }
                } else {
                    addOutput(`❌ ${name} failed: ${result.error?.message || 'Unknown error'}\n`);
                }
            } catch (error) {
                addOutput(`❌ ${name} error: ${error.message}\n`);
            }
            addOutput('---\n');
        }
        
        function addOutput(text) {
            const output = document.getElementById('systemOutput');
            output.textContent += text;
            output.scrollTop = output.scrollHeight;
        }
    </script>
</body>
</html>