<?php
/**
 * AI Agent Configuration Page
 *
 * Configure and manage AI agent settings, models, API keys, and domains.
 *
 * @package Dashboard\Admin
 * @author Ecigdis Limited
 */

declare(strict_types=1);

// Validate project access
$projectId = isset($_GET['project_id']) ? (int)$_GET['project_id'] : null;

if ($projectId) {
    $projectCheckStmt = $pdo->prepare("SELECT id FROM projects WHERE id = ? AND status = 'active' LIMIT 1");
    $projectCheckStmt->execute([$projectId]);
    if (!$projectCheckStmt->fetch()) {
        echo '<div class="alert alert-danger">Invalid or inactive project selected.</div>';
        exit;
    }
}

// Read AI agent configuration from .env file
$envPath = '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/ai-agent/.env';
$agentConfig = [];

if (file_exists($envPath)) {
    $envLines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        if (strpos(trim($line), '#') === 0) continue; // Skip comments
        if (strpos($line, '=') === false) continue;

        [$key, $value] = explode('=', $line, 2);
        $agentConfig[trim($key)] = trim($value);
    }
}

// Get agent domains from database
$agentDomains = [];
try {
    $domainsStmt = $pdo->prepare("SELECT * FROM ai_agent_domains ORDER BY created_at DESC");
    $domainsStmt->execute();
    $agentDomains = $domainsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Table might not exist yet
    $agentDomains = [];
}

// Get agent statistics
$agentStats = [
    'total_requests' => 0,
    'total_tokens' => 0,
    'avg_response_time' => 0,
    'last_request' => null
];

try {
    $statsStmt = $pdo->query("
        SELECT
            COUNT(*) as total_requests,
            SUM(tokens_used) as total_tokens,
            AVG(response_time_ms) as avg_response_time,
            MAX(created_at) as last_request
        FROM ai_agent_requests
        WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
    if ($stats) {
        $agentStats = $stats;
    }
} catch (PDOException $e) {
    // Table might not exist yet
}

// Mask API keys for display
function maskApiKey($key) {
    if (strlen($key) < 20) return '***';
    return substr($key, 0, 10) . '...' . substr($key, -6);
}
?>

<div class="ai-agent-config">
    <div class="page-header">
        <h1>🤖 AI Agent Configuration</h1>
        <p class="text-muted">Manage AI agent models, API keys, domains, and monitoring</p>
    </div>

    <!-- Agent Status Card -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">🟢 Agent Status & Health</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <h6>Total Requests (30d)</h6>
                            <h3><?php echo number_format((int)$agentStats['total_requests']); ?></h3>
                        </div>
                        <div class="col-md-3 text-center">
                            <h6>Total Tokens Used</h6>
                            <h3><?php echo number_format((int)$agentStats['total_tokens']); ?></h3>
                        </div>
                        <div class="col-md-3 text-center">
                            <h6>Avg Response Time</h6>
                            <h3><?php echo number_format((float)$agentStats['avg_response_time'], 0); ?>ms</h3>
                        </div>
                        <div class="col-md-3 text-center">
                            <h6>Last Request</h6>
                            <h3><?php echo $agentStats['last_request'] ? date('M d, H:i', strtotime((string)$agentStats['last_request'])) : 'N/A'; ?></h3>
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <a href="/ai-agent/public/dashboard/agent-monitor.php" target="_blank" class="btn btn-primary">
                            📊 Open Agent Dashboard
                        </a>
                        <a href="/ai-agent/api/health.php" target="_blank" class="btn btn-outline-secondary">
                            🏥 Health Check
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- OpenAI Configuration -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">🧠 OpenAI Configuration (GPT Models)</h5>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <tbody>
                    <tr>
                        <td class="fw-bold" style="width: 250px;">API Key</td>
                        <td>
                            <code><?php echo maskApiKey($agentConfig['OPENAI_API_KEY'] ?? 'Not configured'); ?></code>
                            <span class="badge bg-success ms-2">Active</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Primary Model</td>
                        <td><code><?php echo htmlspecialchars($agentConfig['OPENAI_MODEL'] ?? 'gpt-4o'); ?></code></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Fallback Model</td>
                        <td><code><?php echo htmlspecialchars($agentConfig['OPENAI_FALLBACK_MODEL'] ?? 'gpt-4-turbo'); ?></code></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Realtime Model</td>
                        <td><code><?php echo htmlspecialchars($agentConfig['REALTIME_MODEL'] ?? 'gpt-4o-realtime-preview-2024-10-01'); ?></code></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Embeddings Model</td>
                        <td><code><?php echo htmlspecialchars($agentConfig['EMBEDDINGS_MODEL'] ?? 'text-embedding-3-large'); ?></code></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Voice</td>
                        <td><code><?php echo htmlspecialchars($agentConfig['REALTIME_VOICE'] ?? 'alloy'); ?></code></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Claude Configuration -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">🤖 Anthropic Claude Configuration</h5>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <tbody>
                    <tr>
                        <td class="fw-bold" style="width: 250px;">API Key</td>
                        <td>
                            <code><?php echo maskApiKey($agentConfig['ANTHROPIC_API_KEY'] ?? 'Not configured'); ?></code>
                            <span class="badge bg-success ms-2">Active</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Primary Model</td>
                        <td><code><?php echo htmlspecialchars($agentConfig['CLAUDE_MODEL'] ?? 'claude-3-5-sonnet-20241022'); ?></code></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Fallback Model</td>
                        <td><code><?php echo htmlspecialchars($agentConfig['CLAUDE_FALLBACK_MODEL'] ?? 'claude-3-5-haiku-20241022'); ?></code></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Max Tokens</td>
                        <td><code><?php echo htmlspecialchars($agentConfig['CLAUDE_MAX_TOKENS'] ?? '8192'); ?></code></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Database Configuration -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">🗄️ Database Configuration</h5>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <tbody>
                    <tr>
                        <td class="fw-bold" style="width: 250px;">MySQL Host</td>
                        <td><code><?php echo htmlspecialchars($agentConfig['MYSQL_HOST'] ?? '127.0.0.1'); ?></code></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">MySQL Port</td>
                        <td><code><?php echo htmlspecialchars($agentConfig['MYSQL_PORT'] ?? '3306'); ?></code></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Database</td>
                        <td><code><?php echo htmlspecialchars($agentConfig['MYSQL_DATABASE'] ?? 'jcepnzzkmj'); ?></code></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Username</td>
                        <td><code><?php echo htmlspecialchars($agentConfig['MYSQL_USER'] ?? 'jcepnzzkmj'); ?></code></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Redis Configuration -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">🔴 Redis Configuration (Caching)</h5>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <tbody>
                    <tr>
                        <td class="fw-bold" style="width: 250px;">Redis URL</td>
                        <td><code><?php echo htmlspecialchars($agentConfig['REDIS_URL'] ?? 'redis://127.0.0.1:6379'); ?></code></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Redis Host</td>
                        <td><code><?php echo htmlspecialchars($agentConfig['REDIS_HOST'] ?? '127.0.0.1'); ?></code></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Key Prefix</td>
                        <td><code><?php echo htmlspecialchars($agentConfig['REDIS_PREFIX'] ?? 'aiagent:'); ?></code></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Registered Domains -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">🌐 Registered Domains</h5>
            <button class="btn btn-sm btn-primary" onclick="location.href='/ai-agent/api/domains.php'">
                + Add Domain
            </button>
        </div>
        <div class="card-body">
            <?php if (empty($agentDomains)): ?>
                <div class="alert alert-info">
                    No domains registered yet. Add a domain to enable AI agent access for that site.
                </div>
            <?php else: ?>
                <table class="table table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Domain</th>
                            <th>API Key</th>
                            <th>Status</th>
                            <th>Rate Limit</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agentDomains as $domain): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars((string)$domain['domain']); ?></code></td>
                                <td><code><?php echo maskApiKey((string)$domain['api_key']); ?></code></td>
                                <td>
                                    <span class="badge bg-<?php echo $domain['is_active'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $domain['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars((string)($domain['rate_limit'] ?? '100/hour')); ?></td>
                                <td><?php echo date('M d, Y', strtotime((string)$domain['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="editDomain(<?php echo $domain['id']; ?>)">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- API Endpoints -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">🔌 Available API Endpoints</h5>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Endpoint</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>/ai-agent/api/chat.php</code></td>
                        <td>Basic chat endpoint (legacy)</td>
                        <td><a href="/ai-agent/api/chat.php" target="_blank" class="btn btn-sm btn-outline-secondary">Test</a></td>
                    </tr>
                    <tr>
                        <td><code>/ai-agent/api/chat-v2.php</code></td>
                        <td>Enhanced chat with context</td>
                        <td><a href="/ai-agent/api/chat-v2.php" target="_blank" class="btn btn-sm btn-outline-secondary">Test</a></td>
                    </tr>
                    <tr>
                        <td><code>/ai-agent/api/chat-enterprise.php</code></td>
                        <td>Enterprise chat with advanced features</td>
                        <td><a href="/ai-agent/api/chat-enterprise.php" target="_blank" class="btn btn-sm btn-outline-secondary">Test</a></td>
                    </tr>
                    <tr>
                        <td><code>/ai-agent/api/health.php</code></td>
                        <td>System health check</td>
                        <td><a href="/ai-agent/api/health.php" target="_blank" class="btn btn-sm btn-outline-success">Check</a></td>
                    </tr>
                    <tr>
                        <td><code>/ai-agent/api/domains.php</code></td>
                        <td>Domain management</td>
                        <td><a href="/ai-agent/api/domains.php" target="_blank" class="btn btn-sm btn-outline-secondary">Manage</a></td>
                    </tr>
                    <tr>
                        <td><code>/ai-agent/api/bot-info.php</code></td>
                        <td>Agent information & capabilities</td>
                        <td><a href="/ai-agent/api/bot-info.php" target="_blank" class="btn btn-sm btn-outline-secondary">View</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">⚡ Quick Actions</h5>
        </div>
        <div class="card-body">
            <div class="d-grid gap-2">
                <button class="btn btn-primary" onclick="location.href='/ai-agent/public/admin.php'">
                    🎛️ Open Full Agent Admin Panel
                </button>
                <button class="btn btn-outline-primary" onclick="location.href='/ai-agent/public/dashboard/'">
                    📊 View Analytics Dashboard
                </button>
                <button class="btn btn-outline-secondary" onclick="location.href='/ai-agent/public/agent/'">
                    💬 Test Agent Interface
                </button>
                <button class="btn btn-outline-warning" onclick="editAgentEnv()">
                    ✏️ Edit .env Configuration
                </button>
            </div>
        </div>
    </div>

    <!-- Notes -->
    <div class="alert alert-info mt-4">
        <h6>📝 Configuration Notes:</h6>
        <ul class="mb-0">
            <li>API keys are masked for security. Edit <code>.env</code> file to update them.</li>
            <li>GPT-4o and Claude 3.5 Sonnet are currently active models.</li>
            <li>Redis is used for caching and session management.</li>
            <li>All API endpoints require proper domain authentication via API key.</li>
            <li>Rate limits are configurable per domain in the domains table.</li>
        </ul>
    </div>
</div>

<style>
.ai-agent-config .card {
    border: 1px solid #dee2e6;
    margin-bottom: 1.5rem;
}

.ai-agent-config .card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    font-weight: 600;
}

.ai-agent-config .table td {
    vertical-align: middle;
}

.ai-agent-config .badge {
    font-size: 0.75rem;
}

.ai-agent-config code {
    background-color: #f8f9fa;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.9em;
}

.ai-agent-config .page-header {
    margin-bottom: 2rem;
}

.ai-agent-config .page-header h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #2c3e50;
}
</style>

<script>
function editDomain(domainId) {
    alert('Domain editing interface coming soon. For now, edit directly via /ai-agent/api/domains.php');
}

function editAgentEnv() {
    if (confirm('This will open the .env file for editing. Make sure you have proper backup before making changes. Continue?')) {
        alert('Please edit /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/ai-agent/.env file via SSH or file manager.');
    }
}
</script>
