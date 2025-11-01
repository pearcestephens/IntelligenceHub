<?php
/**
 * Bot Orchestrator & Management Dashboard
 *
 * Manages multiple AI bot instances, projects, and deployments
 * Integrates with existing AI Agent system
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/config.php';

// Initialize AI Agent if available
$aiAgentAvailable = false;
$aiAgentPath = dirname(__DIR__, 2) . '/ai-agent';
if (file_exists($aiAgentPath . '/autoload.php')) {
    require_once $aiAgentPath . '/autoload.php';
    $aiAgentAvailable = true;
}

$pageTitle = 'Bot Orchestrator';

// Get bot statistics
function getBotStats($db) {
    try {
        $stats = [
            'total_bots' => 0,
            'active_bots' => 0,
            'projects' => 0,
            'deployments' => 0,
            'tasks_completed' => 0,
            'tasks_running' => 0
        ];

        // Check if bot tables exist
        $tables = $db->query("SHOW TABLES LIKE 'bot_%'")->fetchAll(PDO::FETCH_COLUMN);

        if (in_array('bot_instances', $tables)) {
            $stmt = $db->query("SELECT COUNT(*) as total,
                                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active
                                FROM bot_instances");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['total_bots'] = $result['total'] ?? 0;
            $stats['active_bots'] = $result['active'] ?? 0;
        }

        if (in_array('bot_projects', $tables)) {
            $stats['projects'] = $db->query("SELECT COUNT(*) FROM bot_projects")->fetchColumn();
        }

        if (in_array('bot_deployments', $tables)) {
            $stats['deployments'] = $db->query("SELECT COUNT(*) FROM bot_deployments WHERE status = 'deployed'")->fetchColumn();
        }

        if (in_array('bot_tasks', $tables)) {
            $stmt = $db->query("SELECT
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'running' THEN 1 ELSE 0 END) as running
                FROM bot_tasks");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $stats['tasks_completed'] = $result['completed'] ?? 0;
            $stats['tasks_running'] = $result['running'] ?? 0;
        }

        return $stats;
    } catch (Exception $e) {
        return [
            'total_bots' => 0,
            'active_bots' => 0,
            'projects' => 0,
            'deployments' => 0,
            'tasks_completed' => 0,
            'tasks_running' => 0
        ];
    }
}

$stats = getBotStats($db);

include __DIR__ . '/../includes/header.php';
?>

<style>
.bot-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 25px;
    color: white;
    margin-bottom: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.bot-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.3);
}

.bot-status-online {
    display: inline-block;
    width: 10px;
    height: 10px;
    background: #10b981;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

.bot-status-offline {
    display: inline-block;
    width: 10px;
    height: 10px;
    background: #ef4444;
    border-radius: 50%;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.project-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 15px;
    border-left: 4px solid #667eea;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.deployment-badge {
    display: inline-block;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.deployment-active {
    background: #10b981;
    color: white;
}

.deployment-pending {
    background: #f59e0b;
    color: white;
}

.deployment-failed {
    background: #ef4444;
    color: white;
}

.control-panel {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}

.bot-terminal {
    background: #1e1e1e;
    color: #00ff00;
    font-family: 'Courier New', monospace;
    padding: 15px;
    border-radius: 5px;
    height: 300px;
    overflow-y: auto;
    font-size: 12px;
    line-height: 1.5;
}

.bot-terminal-line {
    margin-bottom: 5px;
}

.tab-content {
    padding: 20px;
    background: white;
    border-radius: 0 0 10px 10px;
}

.nav-tabs .nav-link {
    color: #667eea;
    font-weight: 600;
}

.nav-tabs .nav-link.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}

.metric-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    margin-bottom: 20px;
}

.metric-value {
    font-size: 2.5rem;
    font-weight: bold;
    margin: 10px 0;
}

.metric-label {
    font-size: 0.9rem;
    opacity: 0.9;
}
</style>

<div class="content-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1>🤖 Bot Orchestrator</h1>
            <p class="text-muted">Manage multiple AI bot instances, projects, and deployments</p>
        </div>
        <div>
            <button class="btn btn-primary" onclick="createNewBot()">
                <i class="fas fa-plus"></i> Create Bot Instance
            </button>
            <button class="btn btn-success" onclick="deployBot()">
                <i class="fas fa-rocket"></i> Deploy Bot
            </button>
        </div>
    </div>
</div>

<!-- Statistics Overview -->
<div class="row">
    <div class="col-md-2">
        <div class="metric-card">
            <div class="metric-label">Total Bots</div>
            <div class="metric-value"><?php echo $stats['total_bots']; ?></div>
            <small>Registered</small>
        </div>
    </div>
    <div class="col-md-2">
        <div class="metric-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="metric-label">Active Bots</div>
            <div class="metric-value"><?php echo $stats['active_bots']; ?></div>
            <small>Running Now</small>
        </div>
    </div>
    <div class="col-md-2">
        <div class="metric-card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
            <div class="metric-label">Projects</div>
            <div class="metric-value"><?php echo $stats['projects']; ?></div>
            <small>Active</small>
        </div>
    </div>
    <div class="col-md-2">
        <div class="metric-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <div class="metric-label">Deployments</div>
            <div class="metric-value"><?php echo $stats['deployments']; ?></div>
            <small>Live</small>
        </div>
    </div>
    <div class="col-md-2">
        <div class="metric-card" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
            <div class="metric-label">Tasks Running</div>
            <div class="metric-value"><?php echo $stats['tasks_running']; ?></div>
            <small>Active</small>
        </div>
    </div>
    <div class="col-md-2">
        <div class="metric-card" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
            <div class="metric-label">Completed</div>
            <div class="metric-value"><?php echo $stats['tasks_completed']; ?></div>
            <small>Total Tasks</small>
        </div>
    </div>
</div>

<!-- Main Tabs -->
<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#bot-instances">
            <i class="fas fa-robot"></i> Bot Instances
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#projects">
            <i class="fas fa-project-diagram"></i> Projects
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#deployments">
            <i class="fas fa-server"></i> Deployments
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tasks">
            <i class="fas fa-tasks"></i> Task Queue
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#monitoring">
            <i class="fas fa-chart-line"></i> Live Monitoring
        </a>
    </li>
</ul>

<div class="tab-content">
    <!-- Bot Instances Tab -->
    <div class="tab-pane fade show active" id="bot-instances">
        <div class="row">
            <div class="col-md-12">
                <h3>Bot Instances</h3>
                <p class="text-muted">Manage and control multiple AI bot instances</p>

                <div id="bot-instances-list">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        No bot instances created yet. Click "Create Bot Instance" to get started.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Tab -->
    <div class="tab-pane fade" id="projects">
        <div class="row">
            <div class="col-md-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3>Projects</h3>
                    <button class="btn btn-primary" onclick="createProject()">
                        <i class="fas fa-plus"></i> New Project
                    </button>
                </div>

                <div id="projects-list">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        No projects created. Create a project to organize your bot tasks.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Deployments Tab -->
    <div class="tab-pane fade" id="deployments">
        <div class="row">
            <div class="col-md-12">
                <h3>Bot Deployments</h3>
                <p class="text-muted">Deploy bots to remote servers and manage deployments</p>

                <div class="control-panel">
                    <h5>Quick Deploy</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <label>Select Bot Instance</label>
                            <select class="form-control" id="deploy-bot-select">
                                <option value="">Choose bot...</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Target Server</label>
                            <select class="form-control" id="deploy-server-select">
                                <option value="">Choose server...</option>
                                <option value="local">Local Server</option>
                                <option value="cloudways">Cloudways Production</option>
                                <option value="custom">Custom SSH...</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <button class="btn btn-success btn-block" onclick="quickDeploy()">
                                <i class="fas fa-rocket"></i> Deploy Now
                            </button>
                        </div>
                    </div>
                </div>

                <div id="deployments-list" class="mt-4">
                    <!-- Deployment list will load here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Tasks Tab -->
    <div class="tab-pane fade" id="tasks">
        <div class="row">
            <div class="col-md-12">
                <h3>Task Queue</h3>
                <p class="text-muted">View and manage bot tasks</p>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Task ID</th>
                                <th>Bot</th>
                                <th>Project</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Progress</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tasks-table-body">
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    No tasks in queue
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Monitoring Tab -->
    <div class="tab-pane fade" id="monitoring">
        <div class="row">
            <div class="col-md-8">
                <h3>Live Bot Output</h3>
                <div class="bot-terminal" id="live-terminal">
                    <div class="bot-terminal-line">[System] Bot Orchestrator Terminal v1.0</div>
                    <div class="bot-terminal-line">[System] Ready to display bot output...</div>
                </div>
            </div>
            <div class="col-md-4">
                <h3>Performance Metrics</h3>
                <div id="performance-metrics">
                    <div class="card mb-2">
                        <div class="card-body">
                            <h6>CPU Usage</h6>
                            <div class="progress">
                                <div class="progress-bar bg-success" style="width: 0%" id="cpu-bar">0%</div>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-2">
                        <div class="card-body">
                            <h6>Memory Usage</h6>
                            <div class="progress">
                                <div class="progress-bar bg-info" style="width: 0%" id="memory-bar">0%</div>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-2">
                        <div class="card-body">
                            <h6>Active Connections</h6>
                            <h3 id="connections-count">0</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Bot Modal -->
<div class="modal fade" id="createBotModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Bot Instance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="create-bot-form">
                    <div class="mb-3">
                        <label class="form-label">Bot Name</label>
                        <input type="text" class="form-control" name="bot_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bot Type</label>
                        <select class="form-control" name="bot_type" required>
                            <option value="web-dev">Web Development Bot</option>
                            <option value="api-dev">API Development Bot</option>
                            <option value="database">Database Management Bot</option>
                            <option value="devops">DevOps Bot</option>
                            <option value="testing">Testing & QA Bot</option>
                            <option value="custom">Custom Bot</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">AI Model</label>
                        <select class="form-control" name="model">
                            <option value="gpt-4-turbo">GPT-4 Turbo</option>
                            <option value="gpt-4">GPT-4</option>
                            <option value="gpt-3.5-turbo">GPT-3.5 Turbo</option>
                            <option value="claude-3-opus">Claude 3 Opus</option>
                            <option value="claude-3-sonnet">Claude 3 Sonnet</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Concurrent Instances</label>
                        <input type="number" class="form-control" name="max_instances" value="1" min="1" max="10">
                        <small class="text-muted">Maximum number of concurrent instances</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitCreateBot()">Create Bot</button>
            </div>
        </div>
    </div>
</div>

<script>
// Bot Orchestrator JavaScript
let refreshInterval = null;

function createNewBot() {
    $('#createBotModal').modal('show');
}

function submitCreateBot() {
    const form = document.getElementById('create-bot-form');
    const formData = new FormData(form);

    fetch('/dashboard/api/bot-orchestrator.php?action=create_bot', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            $('#createBotModal').modal('hide');
            loadBotInstances();
            alert('Bot instance created successfully!');
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to create bot instance');
    });
}

function loadBotInstances() {
    fetch('/dashboard/api/bot-orchestrator.php?action=list_bots')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('bot-instances-list');
            if (data.bots && data.bots.length > 0) {
                container.innerHTML = data.bots.map(bot => `
                    <div class="bot-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4>
                                    <span class="bot-status-${bot.status === 'active' ? 'online' : 'offline'}"></span>
                                    ${bot.name}
                                </h4>
                                <p class="mb-1">${bot.description || 'No description'}</p>
                                <small>Model: ${bot.model} | Instances: ${bot.active_instances}/${bot.max_instances}</small>
                            </div>
                            <div>
                                <button class="btn btn-light btn-sm me-2" onclick="startBot(${bot.id})">
                                    <i class="fas fa-play"></i> Start
                                </button>
                                <button class="btn btn-warning btn-sm me-2" onclick="stopBot(${bot.id})">
                                    <i class="fas fa-stop"></i> Stop
                                </button>
                                <button class="btn btn-info btn-sm" onclick="viewBotLogs(${bot.id})">
                                    <i class="fas fa-terminal"></i> Logs
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        });
}

function startBot(botId) {
    fetch(`/dashboard/api/bot-orchestrator.php?action=start_bot&bot_id=${botId}`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            addTerminalLine(`[Bot ${botId}] Started successfully`);
            loadBotInstances();
        }
    });
}

function stopBot(botId) {
    fetch(`/dashboard/api/bot-orchestrator.php?action=stop_bot&bot_id=${botId}`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            addTerminalLine(`[Bot ${botId}] Stopped`);
            loadBotInstances();
        }
    });
}

function addTerminalLine(text) {
    const terminal = document.getElementById('live-terminal');
    const line = document.createElement('div');
    line.className = 'bot-terminal-line';
    line.textContent = `[${new Date().toLocaleTimeString()}] ${text}`;
    terminal.appendChild(line);
    terminal.scrollTop = terminal.scrollHeight;
}

function createProject() {
    const name = prompt('Enter project name:');
    if (name) {
        fetch('/dashboard/api/bot-orchestrator.php?action=create_project', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({name: name})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Project created!');
                loadProjects();
            }
        });
    }
}

function quickDeploy() {
    const botId = document.getElementById('deploy-bot-select').value;
    const server = document.getElementById('deploy-server-select').value;

    if (!botId || !server) {
        alert('Please select both bot and server');
        return;
    }

    addTerminalLine(`[Deploy] Starting deployment to ${server}...`);

    fetch('/dashboard/api/bot-orchestrator.php?action=deploy', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({bot_id: botId, server: server})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            addTerminalLine(`[Deploy] Deployment completed successfully!`);
        } else {
            addTerminalLine(`[Deploy] ERROR: ${data.error}`);
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadBotInstances();

    // Start live monitoring
    refreshInterval = setInterval(() => {
        // Update metrics
        fetch('/dashboard/api/bot-orchestrator.php?action=metrics')
            .then(response => response.json())
            .then(data => {
                if (data.cpu !== undefined) {
                    document.getElementById('cpu-bar').style.width = data.cpu + '%';
                    document.getElementById('cpu-bar').textContent = data.cpu + '%';
                }
                if (data.memory !== undefined) {
                    document.getElementById('memory-bar').style.width = data.memory + '%';
                    document.getElementById('memory-bar').textContent = data.memory + '%';
                }
                if (data.connections !== undefined) {
                    document.getElementById('connections-count').textContent = data.connections;
                }
            });
    }, 5000);
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
