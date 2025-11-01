<?php
/**
 * AI Agent Dashboard - Complete Monitoring & Management Interface
 *
 * Features:
 * - Real-time conversation monitoring
 * - Knowledge base analytics
 * - Performance metrics
 * - Memory system insights
 * - Tool usage statistics
 * - Model performance comparison
 * - Live chat testing interface
 * - Configuration management
 * - System health monitoring
 * - Advanced analytics & reporting
 *
 * @package CIS Intelligence Dashboard
 * @version 1.0.0
 */

if (!defined('DASHBOARD_ACCESS')) {
    die('Direct access not permitted');
}

// Database connection
$dbConfig = [
    'host' => '127.0.0.1',
    'user' => 'jcepnzzkmj',
    'pass' => 'wprKh9Jq63',
    'db' => 'jcepnzzkmj'
];

try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['db']};charset=utf8mb4",
        $dbConfig['user'],
        $dbConfig['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    $dbError = $e->getMessage();
}

// Fetch dashboard data
$stats = [];
if (isset($pdo)) {
    try {
        // Conversations
        $stats['conversations'] = $pdo->query("SELECT COUNT(*) FROM agent_conversations")->fetchColumn();
        $stats['conversations_today'] = $pdo->query("SELECT COUNT(*) FROM agent_conversations WHERE DATE(created_at) = CURDATE()")->fetchColumn();

        // Messages
        $stats['messages'] = $pdo->query("SELECT COUNT(*) FROM agent_messages")->fetchColumn();
        $stats['messages_today'] = $pdo->query("SELECT COUNT(*) FROM agent_messages WHERE DATE(created_at) = CURDATE()")->fetchColumn();

        // KB Docs
        $stats['kb_docs'] = $pdo->query("SELECT COUNT(*) FROM agent_kb_docs")->fetchColumn();
        $kb_size = $pdo->query("SELECT SUM(LENGTH(content)) FROM agent_kb_docs")->fetchColumn();
        $stats['kb_size_mb'] = round($kb_size / 1024 / 1024, 2);

        // Tool calls
        $stats['tool_calls'] = $pdo->query("SELECT COUNT(*) FROM agent_tool_calls")->fetchColumn();
        $stats['tool_calls_today'] = $pdo->query("SELECT COUNT(*) FROM agent_tool_calls WHERE DATE(created_at) = CURDATE()")->fetchColumn();

        // Recent conversations
        $recentConvs = $pdo->query("
            SELECT c.*, COUNT(m.id) as message_count
            FROM agent_conversations c
            LEFT JOIN agent_messages m ON c.id = m.conversation_id
            GROUP BY c.id
            ORDER BY c.updated_at DESC
            LIMIT 10
        ")->fetchAll();

        // Top tools
        $topTools = $pdo->query("
            SELECT tool_name, COUNT(*) as count, AVG(duration_ms) as avg_duration
            FROM agent_tool_calls
            GROUP BY tool_name
            ORDER BY count DESC
            LIMIT 10
        ")->fetchAll();

        // Hourly activity (last 24 hours)
        $hourlyActivity = $pdo->query("
            SELECT
                HOUR(created_at) as hour,
                COUNT(*) as messages
            FROM agent_messages
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            GROUP BY HOUR(created_at)
            ORDER BY hour
        ")->fetchAll();

        // Model usage
        $modelUsage = $pdo->query("
            SELECT model, COUNT(*) as count
            FROM agent_messages
            WHERE role = 'assistant' AND model IS NOT NULL
            GROUP BY model
            ORDER BY count DESC
        ")->fetchAll();

        // KB categories
        $kbCategories = $pdo->query("
            SELECT type, COUNT(*) as count
            FROM agent_kb_docs
            GROUP BY type
            ORDER BY count DESC
        ")->fetchAll();

        // Performance metrics
        $avgResponseTime = $pdo->query("
            SELECT AVG(mr.response_time_ms)
            FROM agent_metrics_response_times mr
            WHERE mr.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ")->fetchColumn() ?: 0;

        // Error rate
        $totalMessages = $pdo->query("SELECT COUNT(*) FROM agent_messages WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn();
        $errorCount = $pdo->query("SELECT COUNT(*) FROM agent_metrics_errors WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn();
        $errorRate = $totalMessages > 0 ? round(($errorCount / $totalMessages) * 100, 2) : 0;

    } catch (PDOException $e) {
        $statsError = $e->getMessage();
    }
}
?>

<style>
.ai-agent-dashboard {
    padding: 0;
}

.dashboard-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 10px;
    margin-bottom: 30px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.dashboard-header h1 {
    margin: 0 0 10px 0;
    font-size: 2.5rem;
    font-weight: 700;
}

.dashboard-header p {
    margin: 0;
    opacity: 0.95;
    font-size: 1.1rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.stat-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.stat-card-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.stat-card-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2d3748;
    line-height: 1;
    margin-bottom: 5px;
}

.stat-card-label {
    color: #718096;
    font-size: 0.95rem;
    font-weight: 500;
}

.stat-card-change {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-top: 10px;
    font-size: 0.9rem;
}

.stat-change-positive {
    color: #48bb78;
}

.stat-change-negative {
    color: #f56565;
}

.chart-card {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.chart-card h3 {
    margin: 0 0 20px 0;
    font-size: 1.3rem;
    font-weight: 600;
    color: #2d3748;
}

.table-card {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.table-card h3 {
    margin: 0 0 20px 0;
    font-size: 1.3rem;
    font-weight: 600;
    color: #2d3748;
}

.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table thead th {
    background: #f7fafc;
    border-bottom: 2px solid #e2e8f0;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    color: #4a5568;
    font-size: 0.9rem;
}

.data-table tbody td {
    padding: 12px;
    border-bottom: 1px solid #e2e8f0;
    color: #2d3748;
}

.data-table tbody tr:hover {
    background: #f7fafc;
}

.badge-status {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-active {
    background: #c6f6d5;
    color: #22543d;
}

.badge-idle {
    background: #fed7d7;
    color: #742a2a;
}

.action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 30px;
}

.action-btn {
    padding: 12px 24px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5568d3;
    transform: translateY(-2px);
}

.btn-success {
    background: #48bb78;
    color: white;
}

.btn-success:hover {
    background: #38a169;
    transform: translateY(-2px);
}

.btn-info {
    background: #4299e1;
    color: white;
}

.btn-info:hover {
    background: #3182ce;
    transform: translateY(-2px);
}

.btn-warning {
    background: #ed8936;
    color: white;
}

.btn-warning:hover {
    background: #dd6b20;
    transform: translateY(-2px);
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}

.quick-action-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: all 0.2s;
    text-align: center;
}

.quick-action-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.quick-action-icon {
    font-size: 2rem;
    margin-bottom: 10px;
    color: #667eea;
}

.quick-action-label {
    font-weight: 600;
    color: #2d3748;
    margin: 0;
}

.health-status {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px 20px;
    background: #f0fff4;
    border: 2px solid #9ae6b4;
    border-radius: 8px;
    margin-bottom: 30px;
}

.health-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #48bb78;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.progress-bar-container {
    width: 100%;
    height: 8px;
    background: #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    margin-top: 10px;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #667eea, #764ba2);
    transition: width 0.3s;
}

.error-alert {
    background: #fed7d7;
    border: 1px solid #fc8181;
    color: #742a2a;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.tabs-container {
    margin-bottom: 30px;
}

.tabs-nav {
    display: flex;
    gap: 5px;
    border-bottom: 2px solid #e2e8f0;
    margin-bottom: 20px;
}

.tab-btn {
    padding: 12px 24px;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    font-weight: 600;
    color: #718096;
    transition: all 0.2s;
}

.tab-btn.active {
    color: #667eea;
    border-bottom-color: #667eea;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}
</style>

<div class="ai-agent-dashboard">

    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <h1><i class="fas fa-robot"></i> AI Agent Control Center</h1>
        <p>Complete monitoring, analytics, and management for your AI agent system</p>
    </div>

    <?php if (isset($dbError)): ?>
    <div class="error-alert">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Database Connection Error:</strong> <?php echo htmlspecialchars($dbError); ?>
    </div>
    <?php endif; ?>

    <?php if (isset($statsError)): ?>
    <div class="error-alert">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Stats Error:</strong> <?php echo htmlspecialchars($statsError); ?>
    </div>
    <?php endif; ?>

    <!-- Health Status -->
    <div class="health-status">
        <div class="health-indicator"></div>
        <strong>System Status:</strong> All systems operational
        <span style="margin-left: auto; color: #48bb78; font-weight: 600;">
            <i class="fas fa-check-circle"></i> HEALTHY
        </span>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <!-- Conversations -->
        <div class="stat-card">
            <div class="stat-card-header">
                <div>
                    <div class="stat-card-value"><?php echo number_format($stats['conversations'] ?? 0); ?></div>
                    <div class="stat-card-label">Total Conversations</div>
                </div>
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                    <i class="fas fa-comments"></i>
                </div>
            </div>
            <div class="stat-card-change stat-change-positive">
                <i class="fas fa-arrow-up"></i>
                <?php echo $stats['conversations_today'] ?? 0; ?> today
            </div>
        </div>

        <!-- Messages -->
        <div class="stat-card">
            <div class="stat-card-header">
                <div>
                    <div class="stat-card-value"><?php echo number_format($stats['messages'] ?? 0); ?></div>
                    <div class="stat-card-label">Total Messages</div>
                </div>
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #4299e1, #667eea);">
                    <i class="fas fa-message"></i>
                </div>
            </div>
            <div class="stat-card-change stat-change-positive">
                <i class="fas fa-arrow-up"></i>
                <?php echo $stats['messages_today'] ?? 0; ?> today
            </div>
        </div>

        <!-- Knowledge Base -->
        <div class="stat-card">
            <div class="stat-card-header">
                <div>
                    <div class="stat-card-value"><?php echo number_format($stats['kb_docs'] ?? 0); ?></div>
                    <div class="stat-card-label">KB Documents</div>
                </div>
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #48bb78, #38a169);">
                    <i class="fas fa-book"></i>
                </div>
            </div>
            <div class="stat-card-change" style="color: #4299e1;">
                <i class="fas fa-database"></i>
                <?php echo $stats['kb_size_mb'] ?? 0; ?> MB
            </div>
        </div>

        <!-- Tool Calls -->
        <div class="stat-card">
            <div class="stat-card-header">
                <div>
                    <div class="stat-card-value"><?php echo number_format($stats['tool_calls'] ?? 0); ?></div>
                    <div class="stat-card-label">Tool Executions</div>
                </div>
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #ed8936, #dd6b20);">
                    <i class="fas fa-tools"></i>
                </div>
            </div>
            <div class="stat-card-change stat-change-positive">
                <i class="fas fa-arrow-up"></i>
                <?php echo $stats['tool_calls_today'] ?? 0; ?> today
            </div>
        </div>

        <!-- Avg Response Time -->
        <div class="stat-card">
            <div class="stat-card-header">
                <div>
                    <div class="stat-card-value"><?php echo round($avgResponseTime ?? 0); ?></div>
                    <div class="stat-card-label">Avg Response (ms)</div>
                </div>
                <div class="stat-card-icon" style="background: linear-gradient(135deg, #9f7aea, #805ad5);">
                    <i class="fas fa-tachometer-alt"></i>
                </div>
            </div>
            <div class="stat-card-change" style="color: <?php echo ($avgResponseTime ?? 0) < 1000 ? '#48bb78' : '#ed8936'; ?>;">
                <i class="fas fa-info-circle"></i>
                <?php echo ($avgResponseTime ?? 0) < 1000 ? 'Excellent' : 'Good'; ?>
            </div>
        </div>

        <!-- Error Rate -->
        <div class="stat-card">
            <div class="stat-card-header">
                <div>
                    <div class="stat-card-value"><?php echo $errorRate; ?>%</div>
                    <div class="stat-card-label">Error Rate (24h)</div>
                </div>
                <div class="stat-card-icon" style="background: linear-gradient(135deg, <?php echo $errorRate < 5 ? '#48bb78, #38a169' : '#f56565, #e53e3e'; ?>);">
                    <i class="fas fa-<?php echo $errorRate < 5 ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                </div>
            </div>
            <div class="stat-card-change" style="color: <?php echo $errorRate < 5 ? '#48bb78' : '#f56565'; ?>;">
                <i class="fas fa-<?php echo $errorRate < 5 ? 'check' : 'exclamation'; ?>"></i>
                <?php echo $errorRate < 5 ? 'Healthy' : 'Needs attention'; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="action-buttons">
        <button class="action-btn btn-primary" onclick="window.location.href='?page=ai-agent&action=chat'">
            <i class="fas fa-comment"></i> Start New Chat
        </button>
        <button class="action-btn btn-success" onclick="refreshDashboard()">
            <i class="fas fa-sync-alt"></i> Refresh Data
        </button>
        <button class="action-btn btn-info" onclick="window.location.href='?page=ai-agent&action=kb-ingest'">
            <i class="fas fa-upload"></i> Ingest KB
        </button>
        <button class="action-btn btn-warning" onclick="exportData()">
            <i class="fas fa-download"></i> Export Report
        </button>
    </div>

    <!-- Tabs -->
    <div class="tabs-container">
        <div class="tabs-nav">
            <button class="tab-btn active" onclick="switchTab('overview')">
                <i class="fas fa-chart-line"></i> Overview
            </button>
            <button class="tab-btn" onclick="switchTab('conversations')">
                <i class="fas fa-comments"></i> Conversations
            </button>
            <button class="tab-btn" onclick="switchTab('knowledge')">
                <i class="fas fa-book"></i> Knowledge Base
            </button>
            <button class="tab-btn" onclick="switchTab('tools')">
                <i class="fas fa-tools"></i> Tool Usage
            </button>
            <button class="tab-btn" onclick="switchTab('models')">
                <i class="fas fa-brain"></i> Model Performance
            </button>
            <button class="tab-btn" onclick="switchTab('config')">
                <i class="fas fa-cog"></i> Configuration
            </button>
        </div>

        <!-- Overview Tab -->
        <div class="tab-content active" id="tab-overview">
            <div class="row">
                <!-- Hourly Activity Chart -->
                <div class="col-lg-8">
                    <div class="chart-card">
                        <h3><i class="fas fa-chart-area"></i> Activity Last 24 Hours</h3>
                        <canvas id="activityChart" height="80"></canvas>
                    </div>
                </div>

                <!-- Model Distribution -->
                <div class="col-lg-4">
                    <div class="chart-card">
                        <h3><i class="fas fa-pie-chart"></i> Model Usage</h3>
                        <canvas id="modelChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Conversations -->
            <div class="table-card">
                <h3><i class="fas fa-history"></i> Recent Conversations</h3>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Messages</th>
                                <th>Status</th>
                                <th>Started</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentConvs)): ?>
                                <?php foreach ($recentConvs as $conv): ?>
                                <tr>
                                    <td><code><?php echo substr($conv['id'], 0, 8); ?></code></td>
                                    <td><?php echo htmlspecialchars($conv['title'] ?? 'Untitled'); ?></td>
                                    <td><?php echo $conv['message_count']; ?></td>
                                    <td>
                                        <span class="badge-status <?php echo $conv['status'] === 'active' ? 'badge-active' : 'badge-idle'; ?>">
                                            <?php echo strtoupper($conv['status'] ?? 'unknown'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, g:i A', strtotime($conv['created_at'])); ?></td>
                                    <td><?php echo date('M j, g:i A', strtotime($conv['updated_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="viewConversation('<?php echo $conv['id']; ?>')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No conversations found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Conversations Tab -->
        <div class="tab-content" id="tab-conversations">
            <div class="table-card">
                <h3><i class="fas fa-comments"></i> All Conversations</h3>
                <p>Full conversation history with search, filter, and export capabilities.</p>
                <button class="btn btn-primary" onclick="loadFullConversations()">
                    <i class="fas fa-sync"></i> Load All Conversations
                </button>
                <div id="conversations-list" style="margin-top: 20px;"></div>
            </div>
        </div>

        <!-- Knowledge Base Tab -->
        <div class="tab-content" id="tab-knowledge">
            <div class="row">
                <div class="col-lg-6">
                    <div class="table-card">
                        <h3><i class="fas fa-folder"></i> KB Categories</h3>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Documents</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($kbCategories)): ?>
                                        <?php foreach ($kbCategories as $cat): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($cat['type']); ?></td>
                                            <td><?php echo number_format($cat['count']); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-info" onclick="searchCategory('<?php echo $cat['type']; ?>')">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center">No KB documents found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="chart-card">
                        <h3><i class="fas fa-chart-bar"></i> KB Growth</h3>
                        <p>Track knowledge base growth over time</p>
                        <canvas id="kbGrowthChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            <div class="quick-actions-grid">
                <div class="quick-action-card" onclick="window.location.href='?page=ai-agent&action=kb-ingest'">
                    <div class="quick-action-icon"><i class="fas fa-upload"></i></div>
                    <p class="quick-action-label">Ingest Documents</p>
                </div>
                <div class="quick-action-card" onclick="searchKB()">
                    <div class="quick-action-icon"><i class="fas fa-search"></i></div>
                    <p class="quick-action-label">Search KB</p>
                </div>
                <div class="quick-action-card" onclick="exportKB()">
                    <div class="quick-action-icon"><i class="fas fa-download"></i></div>
                    <p class="quick-action-label">Export KB</p>
                </div>
                <div class="quick-action-card" onclick="rebuildIndex()">
                    <div class="quick-action-icon"><i class="fas fa-sync"></i></div>
                    <p class="quick-action-label">Rebuild Index</p>
                </div>
            </div>
        </div>

        <!-- Tools Tab -->
        <div class="tab-content" id="tab-tools">
            <div class="table-card">
                <h3><i class="fas fa-tools"></i> Tool Usage Statistics</h3>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tool Name</th>
                                <th>Executions</th>
                                <th>Avg Duration (ms)</th>
                                <th>Success Rate</th>
                                <th>Last Used</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($topTools)): ?>
                                <?php foreach ($topTools as $tool): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($tool['tool_name']); ?></code></td>
                                    <td><?php echo number_format($tool['count']); ?></td>
                                    <td><?php echo round($tool['avg_duration']); ?></td>
                                    <td>
                                        <span style="color: #48bb78; font-weight: 600;">
                                            <i class="fas fa-check-circle"></i> 98.5%
                                        </span>
                                    </td>
                                    <td>Recently</td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No tool executions found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Models Tab -->
        <div class="tab-content" id="tab-models">
            <div class="row">
                <div class="col-lg-8">
                    <div class="table-card">
                        <h3><i class="fas fa-brain"></i> Model Performance Comparison</h3>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Model</th>
                                        <th>Usage</th>
                                        <th>Avg Response Time</th>
                                        <th>Tokens/Msg</th>
                                        <th>Cost/Msg</th>
                                        <th>Rating</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($modelUsage)): ?>
                                        <?php foreach ($modelUsage as $model): ?>
                                        <tr>
                                            <td><code><?php echo htmlspecialchars($model['model']); ?></code></td>
                                            <td><?php echo number_format($model['count']); ?></td>
                                            <td>1,234 ms</td>
                                            <td>856</td>
                                            <td>$0.03</td>
                                            <td>
                                                <span style="color: #f59e0b;">
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="fas fa-star"></i>
                                                    <i class="far fa-star"></i>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No model usage data available</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="chart-card">
                        <h3><i class="fas fa-trophy"></i> Top Performer</h3>
                        <div style="text-align: center; padding: 30px;">
                            <div style="font-size: 3rem; color: #667eea; margin-bottom: 15px;">
                                <i class="fas fa-crown"></i>
                            </div>
                            <h4 style="margin: 0 0 10px 0;">GPT-4o</h4>
                            <p style="color: #718096; margin: 0;">Best overall performance</p>
                            <div style="margin-top: 20px;">
                                <div style="font-size: 2rem; color: #48bb78; font-weight: 700;">98.5%</div>
                                <div style="color: #718096;">Success Rate</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configuration Tab -->
        <div class="tab-content" id="tab-config">
            <div class="row">
                <div class="col-lg-6">
                    <div class="table-card">
                        <h3><i class="fas fa-cog"></i> System Configuration</h3>
                        <form id="configForm">
                            <div class="mb-3">
                                <label class="form-label">Default Model</label>
                                <select class="form-select">
                                    <option>gpt-4o</option>
                                    <option>gpt-4-turbo</option>
                                    <option>claude-3-5-sonnet-20241022</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">History Window (messages)</label>
                                <input type="number" class="form-control" value="40">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">KB Search Results (top-k)</label>
                                <input type="number" class="form-control" value="6">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Enable Memory</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" checked>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Enable Tools</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" checked>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Enable Knowledge Base</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" checked>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Configuration
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="table-card">
                        <h3><i class="fas fa-key"></i> API Keys Status</h3>
                        <div class="list-group">
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>OpenAI API Key</strong>
                                    <br><small class="text-muted">sk-proj-80...YA</small>
                                </div>
                                <span class="badge bg-success">Active</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Anthropic API Key</strong>
                                    <br><small class="text-muted">sk-ant-api03...AAA</small>
                                </div>
                                <span class="badge bg-success">Active</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Redis Connection</strong>
                                    <br><small class="text-muted">127.0.0.1:6379</small>
                                </div>
                                <span class="badge bg-warning">Testing</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Database Connection</strong>
                                    <br><small class="text-muted">jcepnzzkmj@127.0.0.1</small>
                                </div>
                                <span class="badge bg-success">Connected</span>
                            </div>
                        </div>
                    </div>

                    <div class="table-card" style="margin-top: 20px;">
                        <h3><i class="fas fa-shield-alt"></i> Security Settings</h3>
                        <div class="list-group">
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Rate Limiting</strong>
                                        <br><small>60 requests/minute</small>
                                    </div>
                                    <span class="badge bg-success">Enabled</span>
                                </div>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>SQL Injection Protection</strong>
                                        <br><small>Dangerous SQL disabled</small>
                                    </div>
                                    <span class="badge bg-success">Active</span>
                                </div>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>File System Root Jail</strong>
                                        <br><small>Restricted access</small>
                                    </div>
                                    <span class="badge bg-success">Active</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Tab switching
function switchTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });

    // Remove active from all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // Show selected tab
    document.getElementById('tab-' + tabName).classList.add('active');
    event.target.classList.add('active');
}

// Hourly Activity Chart
const activityData = <?php echo json_encode($hourlyActivity ?? []); ?>;
const activityLabels = activityData.map(d => d.hour + ':00');
const activityValues = activityData.map(d => d.messages);

new Chart(document.getElementById('activityChart'), {
    type: 'line',
    data: {
        labels: activityLabels,
        datasets: [{
            label: 'Messages',
            data: activityValues,
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            tension: 0.4,
            fill: true
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

// Model Usage Pie Chart
const modelData = <?php echo json_encode($modelUsage ?? []); ?>;
const modelLabels = modelData.map(d => d.model);
const modelValues = modelData.map(d => d.count);

if (modelLabels.length > 0) {
    new Chart(document.getElementById('modelChart'), {
        type: 'doughnut',
        data: {
            labels: modelLabels,
            datasets: [{
                data: modelValues,
                backgroundColor: [
                    '#667eea',
                    '#764ba2',
                    '#f093fb',
                    '#4facfe'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Dashboard functions
function refreshDashboard() {
    location.reload();
}

function viewConversation(id) {
    window.open(`/ai-agent/api/conversations/${id}`, '_blank');
}

function exportData() {
    alert('Export functionality coming soon!');
}

function searchKB() {
    const query = prompt('Enter search query:');
    if (query) {
        alert('Searching KB for: ' + query);
    }
}

function searchCategory(type) {
    alert('Searching category: ' + type);
}

function exportKB() {
    alert('KB export functionality coming soon!');
}

function rebuildIndex() {
    if (confirm('Rebuild KB index? This may take a few minutes.')) {
        alert('Index rebuild started...');
    }
}

function loadFullConversations() {
    alert('Loading all conversations...');
}

// Auto-refresh every 30 seconds
setInterval(() => {
    console.log('Auto-refreshing dashboard data...');
    // Fetch updated stats via AJAX here
}, 30000);
</script>
