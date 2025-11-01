<?php
/**
 * AI Agent Configuration Page - VERSION 2.0 REDESIGN
 * Modern, semantic HTML5 with professional design
 *
 * IMPROVEMENTS:
 * - Semantic HTML5 structure (<main>, <header>, <article>, <section>)
 * - Modern CSS with design system
 * - Proper accessibility (ARIA labels, semantic tags)
 * - Type-safe data handling
 * - Clean component-based layout
 * - AI model management, API configuration
 *
 * @package botshop/admin
 * @version 2.0.0
 * @updated October 2025
 */

declare(strict_types=1);

// Load application bootstrap
require_once $_SERVER['DOCUMENT_ROOT'] . '/app.php';

// Get current project ID from session
$projectId = (int)($_SESSION['current_project_id'] ?? $_GET['project_id'] ?? 1);

// Validate project exists and is active
$valQuery = "SELECT id FROM projects WHERE id = ? AND status = 'active' LIMIT 1";
$valStmt = $pdo->prepare($valQuery);
$valStmt->execute([$projectId]);
if (!$valStmt->fetch()) {
    $projectId = 1;
    $_SESSION['current_project_id'] = $projectId;
}

// ============================================================================
// AI AGENT CONFIGURATION LOADING
// ============================================================================

// Read AI agent configuration from .env file
$envPath = '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/ai-agent/.env';
$agentConfig = [
    'OPENAI_API_KEY' => '',
    'ANTHROPIC_API_KEY' => '',
    'DEFAULT_MODEL' => 'gpt-4',
    'MAX_TOKENS' => '4096',
    'TEMPERATURE' => '0.7',
    'AGENT_NAME' => 'BotShop Assistant',
    'AGENT_VERSION' => '1.0.0',
];

if (file_exists($envPath)) {
    $envLines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;

        [$key, $value] = explode('=', $line, 2);
        $agentConfig[trim($key)] = trim($value);
    }
}

// Get agent domains from database
$agentDomains = [];
try {
    $domainsStmt = $pdo->prepare("SELECT * FROM ai_agent_domains ORDER BY created_at DESC LIMIT 50");
    $domainsStmt->execute();
    $agentDomains = $domainsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("AI Agent: Domains query error - " . $e->getMessage());
    $agentDomains = [];
}

// Get agent activity logs
$activityLogs = [];
try {
    $logsQuery = "
        SELECT
            id,
            event_type,
            description,
            created_at,
            user_id,
            metadata
        FROM ai_agent_logs
        WHERE project_id = ?
        ORDER BY created_at DESC
        LIMIT 20
    ";
    $logsStmt = $pdo->prepare($logsQuery);
    $logsStmt->execute([$projectId]);
    $activityLogs = $logsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("AI Agent: Logs query error - " . $e->getMessage());
    $activityLogs = [];
}

// Calculate statistics
$totalDomains = count($agentDomains);
$activeDomains = count(array_filter($agentDomains, fn($d) => $d['status'] === 'active'));
$totalLogs = count($activityLogs);
$hasOpenAI = !empty($agentConfig['OPENAI_API_KEY']) && $agentConfig['OPENAI_API_KEY'] !== 'your-openai-api-key';
$hasAnthropic = !empty($agentConfig['ANTHROPIC_API_KEY']) && $agentConfig['ANTHROPIC_API_KEY'] !== 'your-anthropic-api-key';

?>

<!-- ============================================================================
     SEMANTIC HTML5 STRUCTURE
     ============================================================================ -->

<main class="main-content" role="main">

    <!-- Page Header -->
    <header class="page-header">
        <div class="header-content">
            <div class="header-text">
                <h1 class="page-title">
                    <i class="fas fa-robot"></i>
                    AI Agent Configuration
                </h1>
                <p class="page-subtitle">
                    Manage AI models, API keys, and agent behavior settings
                </p>
            </div>
            <div class="header-actions">
                <button type="button" class="btn btn-primary" onclick="testConnection()">
                    <i class="fas fa-plug"></i> Test Connection
                </button>
                <button type="button" class="btn btn-success" onclick="saveConfiguration()">
                    <i class="fas fa-save"></i> Save Configuration
                </button>
            </div>
        </div>

        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="breadcrumb-nav">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="?page=overview">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">AI Agent</li>
            </ol>
        </nav>
    </header>

    <!-- Statistics Cards -->
    <section class="stats-grid" aria-label="AI Agent Statistics">
        <article class="stat-card">
            <div class="stat-icon bg-primary">
                <i class="fas fa-server"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $totalDomains; ?></div>
                <div class="stat-label">Total Domains</div>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-icon bg-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $activeDomains; ?></div>
                <div class="stat-label">Active Domains</div>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-icon bg-info">
                <i class="fas fa-key"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo ($hasOpenAI ? 1 : 0) + ($hasAnthropic ? 1 : 0); ?>/2</div>
                <div class="stat-label">API Keys Configured</div>
            </div>
        </article>

        <article class="stat-card">
            <div class="stat-icon bg-warning">
                <i class="fas fa-history"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?php echo $totalLogs; ?></div>
                <div class="stat-label">Recent Activity</div>
            </div>
        </article>
    </section>

    <!-- Main Content Grid -->
    <div class="content-grid">

        <!-- Left Column: Configuration -->
        <div class="content-column-main">

            <!-- API Configuration Section -->
            <section class="content-card" id="api-config">
                <header class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-key"></i>
                        API Configuration
                    </h2>
                    <p class="card-subtitle">Configure AI model provider API keys</p>
                </header>

                <div class="card-body">
                    <form id="api-config-form">

                        <!-- OpenAI Configuration -->
                        <div class="form-section">
                            <div class="form-section-header">
                                <h3 class="form-section-title">
                                    <i class="fab fa-openai"></i> OpenAI
                                </h3>
                                <span class="badge <?php echo $hasOpenAI ? 'badge-success' : 'badge-secondary'; ?>">
                                    <?php echo $hasOpenAI ? 'Configured' : 'Not Configured'; ?>
                                </span>
                            </div>

                            <div class="form-group">
                                <label for="openai_api_key" class="form-label">
                                    API Key
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input
                                        type="password"
                                        class="form-control"
                                        id="openai_api_key"
                                        name="openai_api_key"
                                        value="<?php echo htmlspecialchars($agentConfig['OPENAI_API_KEY'] ?? ''); ?>"
                                        placeholder="sk-..."
                                    >
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('openai_api_key')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">
                                    Get your API key from <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>
                                </small>
                            </div>
                        </div>

                        <!-- Anthropic Configuration -->
                        <div class="form-section">
                            <div class="form-section-header">
                                <h3 class="form-section-title">
                                    <i class="fas fa-brain"></i> Anthropic (Claude)
                                </h3>
                                <span class="badge <?php echo $hasAnthropic ? 'badge-success' : 'badge-secondary'; ?>">
                                    <?php echo $hasAnthropic ? 'Configured' : 'Not Configured'; ?>
                                </span>
                            </div>

                            <div class="form-group">
                                <label for="anthropic_api_key" class="form-label">
                                    API Key
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input
                                        type="password"
                                        class="form-control"
                                        id="anthropic_api_key"
                                        name="anthropic_api_key"
                                        value="<?php echo htmlspecialchars($agentConfig['ANTHROPIC_API_KEY'] ?? ''); ?>"
                                        placeholder="sk-ant-..."
                                    >
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('anthropic_api_key')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">
                                    Get your API key from <a href="https://console.anthropic.com/" target="_blank">Anthropic Console</a>
                                </small>
                            </div>
                        </div>

                        <!-- Model Settings -->
                        <div class="form-section">
                            <div class="form-section-header">
                                <h3 class="form-section-title">
                                    <i class="fas fa-cog"></i> Model Settings
                                </h3>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="default_model" class="form-label">Default Model</label>
                                        <select class="form-select" id="default_model" name="default_model">
                                            <optgroup label="OpenAI">
                                                <option value="gpt-4" <?php echo ($agentConfig['DEFAULT_MODEL'] ?? '') === 'gpt-4' ? 'selected' : ''; ?>>GPT-4</option>
                                                <option value="gpt-4-turbo" <?php echo ($agentConfig['DEFAULT_MODEL'] ?? '') === 'gpt-4-turbo' ? 'selected' : ''; ?>>GPT-4 Turbo</option>
                                                <option value="gpt-3.5-turbo" <?php echo ($agentConfig['DEFAULT_MODEL'] ?? '') === 'gpt-3.5-turbo' ? 'selected' : ''; ?>>GPT-3.5 Turbo</option>
                                            </optgroup>
                                            <optgroup label="Anthropic">
                                                <option value="claude-3-opus" <?php echo ($agentConfig['DEFAULT_MODEL'] ?? '') === 'claude-3-opus' ? 'selected' : ''; ?>>Claude 3 Opus</option>
                                                <option value="claude-3-sonnet" <?php echo ($agentConfig['DEFAULT_MODEL'] ?? '') === 'claude-3-sonnet' ? 'selected' : ''; ?>>Claude 3 Sonnet</option>
                                                <option value="claude-2" <?php echo ($agentConfig['DEFAULT_MODEL'] ?? '') === 'claude-2' ? 'selected' : ''; ?>>Claude 2</option>
                                            </optgroup>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="max_tokens" class="form-label">Max Tokens</label>
                                        <input
                                            type="number"
                                            class="form-control"
                                            id="max_tokens"
                                            name="max_tokens"
                                            value="<?php echo htmlspecialchars($agentConfig['MAX_TOKENS'] ?? '4096'); ?>"
                                            min="100"
                                            max="32000"
                                            step="100"
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="temperature" class="form-label">
                                            Temperature
                                            <small class="text-muted">(0.0 - 2.0)</small>
                                        </label>
                                        <input
                                            type="number"
                                            class="form-control"
                                            id="temperature"
                                            name="temperature"
                                            value="<?php echo htmlspecialchars($agentConfig['TEMPERATURE'] ?? '0.7'); ?>"
                                            min="0"
                                            max="2"
                                            step="0.1"
                                        >
                                        <small class="form-text text-muted">
                                            Lower = more focused, Higher = more creative
                                        </small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="agent_name" class="form-label">Agent Name</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="agent_name"
                                            name="agent_name"
                                            value="<?php echo htmlspecialchars($agentConfig['AGENT_NAME'] ?? 'BotShop Assistant'); ?>"
                                            placeholder="BotShop Assistant"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" onclick="resetForm()">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Configuration
                            </button>
                        </div>

                    </form>
                </div>
            </section>

            <!-- Agent Domains Section -->
            <section class="content-card" id="agent-domains">
                <header class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-globe"></i>
                        Registered Domains
                    </h2>
                    <button type="button" class="btn btn-sm btn-primary" onclick="addDomain()">
                        <i class="fas fa-plus"></i> Add Domain
                    </button>
                </header>

                <div class="card-body">
                    <?php if (empty($agentDomains)): ?>
                        <div class="empty-state">
                            <i class="fas fa-globe fa-3x text-muted"></i>
                            <p class="text-muted mt-3">No domains registered yet</p>
                            <button type="button" class="btn btn-primary mt-2" onclick="addDomain()">
                                <i class="fas fa-plus"></i> Add First Domain
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Domain</th>
                                        <th>Status</th>
                                        <th>Type</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($agentDomains as $domain): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-globe me-2 text-primary"></i>
                                                    <span class="fw-medium">
                                                        <?php echo htmlspecialchars($domain['domain_name'] ?? 'Unknown'); ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo ($domain['status'] ?? 'inactive') === 'active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo htmlspecialchars($domain['status'] ?? 'inactive'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">
                                                    <?php echo htmlspecialchars($domain['type'] ?? 'standard'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo isset($domain['created_at']) ? date('M d, Y', strtotime($domain['created_at'])) : 'N/A'; ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-ghost" onclick="editDomain(<?php echo (int)$domain['id']; ?>)" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-ghost" onclick="deleteDomain(<?php echo (int)$domain['id']; ?>)" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

        </div>

        <!-- Right Column: Activity & Info -->
        <aside class="content-column-sidebar">

            <!-- Quick Actions -->
            <section class="content-card">
                <header class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-bolt"></i>
                        Quick Actions
                    </h3>
                </header>
                <div class="card-body">
                    <div class="quick-actions">
                        <button type="button" class="btn btn-block btn-outline-primary" onclick="testConnection()">
                            <i class="fas fa-plug"></i> Test Connection
                        </button>
                        <button type="button" class="btn btn-block btn-outline-success" onclick="viewLogs()">
                            <i class="fas fa-file-alt"></i> View Logs
                        </button>
                        <button type="button" class="btn btn-block btn-outline-info" onclick="viewDocumentation()">
                            <i class="fas fa-book"></i> Documentation
                        </button>
                        <button type="button" class="btn btn-block btn-outline-warning" onclick="exportConfig()">
                            <i class="fas fa-download"></i> Export Config
                        </button>
                    </div>
                </div>
            </section>

            <!-- Recent Activity -->
            <section class="content-card">
                <header class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i>
                        Recent Activity
                    </h3>
                </header>
                <div class="card-body">
                    <?php if (empty($activityLogs)): ?>
                        <p class="text-muted text-center py-3">No activity logged yet</p>
                    <?php else: ?>
                        <div class="activity-timeline">
                            <?php foreach (array_slice($activityLogs, 0, 10) as $log): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-circle text-primary"></i>
                                    </div>
                                    <div class="activity-content">
                                        <div class="activity-text">
                                            <?php echo htmlspecialchars($log['description'] ?? 'Activity'); ?>
                                        </div>
                                        <div class="activity-time text-muted">
                                            <small>
                                                <?php echo isset($log['created_at']) ? date('M d, H:i', strtotime($log['created_at'])) : 'N/A'; ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Configuration Status -->
            <section class="content-card">
                <header class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-check-circle"></i>
                        Configuration Status
                    </h3>
                </header>
                <div class="card-body">
                    <div class="status-list">
                        <div class="status-item">
                            <span class="status-label">OpenAI API</span>
                            <span class="badge badge-<?php echo $hasOpenAI ? 'success' : 'warning'; ?>">
                                <?php echo $hasOpenAI ? 'Connected' : 'Not Set'; ?>
                            </span>
                        </div>
                        <div class="status-item">
                            <span class="status-label">Anthropic API</span>
                            <span class="badge badge-<?php echo $hasAnthropic ? 'success' : 'warning'; ?>">
                                <?php echo $hasAnthropic ? 'Connected' : 'Not Set'; ?>
                            </span>
                        </div>
                        <div class="status-item">
                            <span class="status-label">Default Model</span>
                            <span class="badge badge-info">
                                <?php echo htmlspecialchars($agentConfig['DEFAULT_MODEL'] ?? 'Not Set'); ?>
                            </span>
                        </div>
                        <div class="status-item">
                            <span class="status-label">Agent Name</span>
                            <span class="text-muted">
                                <?php echo htmlspecialchars($agentConfig['AGENT_NAME'] ?? 'Not Set'); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </section>

        </aside>

    </div>

</main>

<!-- ============================================================================
     JAVASCRIPT
     ============================================================================ -->

<script>
// AI Agent Configuration Management
const AIAgentConfig = {

    // Test API connection
    testConnection: async function() {
        const openaiKey = document.getElementById('openai_api_key').value;
        const anthropicKey = document.getElementById('anthropic_api_key').value;

        if (!openaiKey && !anthropicKey) {
            alert('Please configure at least one API key');
            return;
        }

        try {
            const response = await fetch('/api/test-ai-connection.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    openai_key: openaiKey,
                    anthropic_key: anthropicKey
                })
            });

            const result = await response.json();

            if (result.success) {
                alert('✅ Connection successful!');
            } else {
                alert('❌ Connection failed: ' + result.message);
            }
        } catch (error) {
            console.error('Connection test error:', error);
            alert('Error testing connection');
        }
    },

    // Save configuration
    saveConfiguration: async function() {
        const form = document.getElementById('api-config-form');
        const formData = new FormData(form);

        try {
            const response = await fetch('/api/save-ai-config.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                alert('✅ Configuration saved successfully!');
                location.reload();
            } else {
                alert('❌ Failed to save: ' + result.message);
            }
        } catch (error) {
            console.error('Save error:', error);
            alert('Error saving configuration');
        }
    },

    // Toggle password visibility
    togglePassword: function(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = event.target.closest('button').querySelector('i');

        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    },

    // Add domain
    addDomain: function() {
        const domain = prompt('Enter domain name:');
        if (!domain) return;

        // In production: POST to API
        alert('Domain registration coming soon');
    },

    // Edit domain
    editDomain: function(domainId) {
        // In production: Load domain data and show edit modal
        alert('Edit domain: ' + domainId);
    },

    // Delete domain
    deleteDomain: async function(domainId) {
        if (!confirm('Are you sure you want to delete this domain?')) return;

        // In production: DELETE via API
        alert('Delete domain: ' + domainId);
    },

    // View logs
    viewLogs: function() {
        window.location.href = '?page=logs';
    },

    // View documentation
    viewDocumentation: function() {
        window.location.href = '?page=documentation';
    },

    // Export configuration
    exportConfig: function() {
        const config = {
            default_model: document.getElementById('default_model').value,
            max_tokens: document.getElementById('max_tokens').value,
            temperature: document.getElementById('temperature').value,
            agent_name: document.getElementById('agent_name').value,
        };

        const blob = new Blob([JSON.stringify(config, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'ai-agent-config.json';
        a.click();
    },

    // Reset form
    resetForm: function() {
        if (confirm('Are you sure you want to reset the form?')) {
            document.getElementById('api-config-form').reset();
        }
    }
};

// Expose functions to global scope for onclick handlers
window.testConnection = () => AIAgentConfig.testConnection();
window.saveConfiguration = () => AIAgentConfig.saveConfiguration();
window.togglePassword = (id) => AIAgentConfig.togglePassword(id);
window.addDomain = () => AIAgentConfig.addDomain();
window.editDomain = (id) => AIAgentConfig.editDomain(id);
window.deleteDomain = (id) => AIAgentConfig.deleteDomain(id);
window.viewLogs = () => AIAgentConfig.viewLogs();
window.viewDocumentation = () => AIAgentConfig.viewDocumentation();
window.exportConfig = () => AIAgentConfig.exportConfig();
window.resetForm = () => AIAgentConfig.resetForm();

// Form submission handler
document.getElementById('api-config-form').addEventListener('submit', function(e) {
    e.preventDefault();
    AIAgentConfig.saveConfiguration();
});
</script>
