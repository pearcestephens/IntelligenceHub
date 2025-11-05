/**
 * Bot Deployment Center - JavaScript Controller
 * Manages multi-bot deployment and multi-threaded conversations
 */

const API_BASE = '/api/bot-deployment-api.php';
const API_KEY = '31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35';

let currentSession = null;
let activeThreads = {};
let refreshInterval = null;

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    loadBots();
    loadMetrics();
    startAutoRefresh();

    // Update thread count selector
    document.getElementById('threadCount')?.addEventListener('change', updateThreadAssignment);
});

/**
 * API Request Helper
 */
async function apiRequest(action, data = {}) {
    const response = await fetch(API_BASE, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${API_KEY}`
        },
        body: JSON.stringify({ action, ...data })
    });

    if (!response.ok) {
        throw new Error(`API Error: ${response.statusText}`);
    }

    return await response.json();
}

/**
 * Load all deployed bots
 */
async function loadBots() {
    try {
        const result = await apiRequest('listBots');
        const bots = result.data || [];

        document.getElementById('totalBots').textContent = bots.filter(b => b.status === 'active').length;
        document.getElementById('botCount').textContent = `${bots.length} bots`;

        const botList = document.getElementById('botList');
        if (bots.length === 0) {
            botList.innerHTML = '<div class="col-12 text-center text-muted py-5">No bots deployed yet. Deploy your first bot to get started!</div>';
            return;
        }

        botList.innerHTML = bots.map(bot => `
            <div class="col-md-4 mb-3">
                <div class="card bot-card ${bot.status === 'active' ? 'active' : ''}"
                     data-bot-id="${bot.bot_id}">
                    <span class="bot-status-badge status-${bot.status}">${bot.status.toUpperCase()}</span>
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-robot"></i> ${bot.bot_name}
                        </h5>
                        <p class="card-text">
                            <span class="badge bg-primary">${bot.bot_role}</span>
                        </p>
                        <p class="card-text text-muted small">
                            ${bot.system_prompt.substring(0, 100)}...
                        </p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <button class="btn btn-sm btn-success" onclick="executeBot(${bot.bot_id})">
                                <i class="fas fa-play"></i> Execute
                            </button>
                            <button class="btn btn-sm btn-info" onclick="viewBotDetails(${bot.bot_id})">
                                <i class="fas fa-info-circle"></i> Details
                            </button>
                            <button class="btn btn-sm btn-warning" onclick="pauseBot(${bot.bot_id})">
                                <i class="fas fa-pause"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

    } catch (error) {
        console.error('Error loading bots:', error);
        addLog('Failed to load bots: ' + error.message, 'error');
    }
}

/**
 * Load dashboard metrics
 */
async function loadMetrics() {
    try {
        const result = await apiRequest('getMetrics');
        const metrics = result.data || {};

        document.getElementById('activeSessions').textContent = metrics.active_sessions || 0;
        document.getElementById('totalThreads').textContent = metrics.total_threads || 0;
        document.getElementById('successRate').textContent = (metrics.success_rate || 0) + '%';

    } catch (error) {
        console.error('Error loading metrics:', error);
    }
}

/**
 * Start Multi-Thread Session
 */
async function startMultiThreadSession() {
    // Load bots for assignment
    const result = await apiRequest('listBots');
    const bots = result.data || [];

    if (bots.length === 0) {
        alert('Please deploy at least one bot before starting a multi-thread session.');
        return;
    }

    // Update thread assignment UI
    updateThreadAssignment();

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('multiThreadModal'));
    modal.show();
}

/**
 * Update thread bot assignment UI
 */
async function updateThreadAssignment() {
    const threadCount = parseInt(document.getElementById('threadCount').value);
    const result = await apiRequest('listBots');
    const bots = result.data.filter(b => b.status === 'active') || [];

    const container = document.getElementById('threadBotAssignment');
    container.innerHTML = '';

    for (let i = 1; i <= threadCount; i++) {
        const div = document.createElement('div');
        div.className = 'mb-2';
        div.innerHTML = `
            <label class="form-label">Thread ${i} Bot:</label>
            <select class="form-control" name="thread_${i}_bot" required>
                <option value="">Auto-assign</option>
                ${bots.map(bot => `
                    <option value="${bot.bot_id}">${bot.bot_name} (${bot.bot_role})</option>
                `).join('')}
            </select>
        `;
        container.appendChild(div);
    }
}

/**
 * Submit Multi-Thread Session
 */
async function submitMultiThreadSession() {
    const topic = document.getElementById('sessionTopicInput').value;
    const threadCount = parseInt(document.getElementById('threadCount').value);

    if (!topic) {
        alert('Please enter a session topic');
        return;
    }

    // Get bot assignments
    const botAssignments = {};
    for (let i = 1; i <= threadCount; i++) {
        const select = document.querySelector(`select[name="thread_${i}_bot"]`);
        if (select && select.value) {
            botAssignments[i] = parseInt(select.value);
        }
    }

    try {
        addLog(`Starting multi-thread session: ${topic}`, 'info');

        const result = await apiRequest('startMultiThread', {
            topic: topic,
            thread_count: threadCount,
            bot_assignments: botAssignments
        });

        currentSession = result.data;

        // Close modal
        bootstrap.Modal.getInstance(document.getElementById('multiThreadModal')).hide();

        // Show multi-thread area
        document.getElementById('multiThreadArea').style.display = 'block';
        document.getElementById('sessionTopic').textContent = topic;

        // Start monitoring threads
        monitorSession(currentSession.session_id);

        addLog(`Multi-thread session started: ${currentSession.session_id}`, 'success');

    } catch (error) {
        console.error('Error starting session:', error);
        addLog('Failed to start session: ' + error.message, 'error');
    }
}

/**
 * Monitor multi-thread session progress
 */
async function monitorSession(sessionId) {
    const container = document.getElementById('threadContainer');

    // Poll for updates every 2 seconds
    const monitor = setInterval(async () => {
        try {
            const result = await apiRequest('getSessionStatus', { session_id: sessionId });
            const session = result.data;

            if (!session) {
                clearInterval(monitor);
                return;
            }

            // Update overall progress
            const progress = Math.round((session.completed_threads / session.thread_count) * 100);
            document.getElementById('progressBar').style.width = progress + '%';
            document.getElementById('overallProgress').textContent = progress + '%';

            // Update threads
            container.innerHTML = session.threads.map((thread, index) => `
                <div class="thread-card">
                    <div class="thread-header">
                        <strong>Thread ${index + 1}</strong>
                        <span class="badge bg-${thread.status === 'completed' ? 'success' : 'primary'}">
                            ${thread.status}
                        </span>
                    </div>
                    <div class="thread-messages" id="thread_${thread.thread_id}">
                        ${thread.messages.map(msg => `
                            <div class="message ${msg.role}">
                                <strong>${msg.role}:</strong>
                                <div>${msg.content}</div>
                                <small class="text-muted">${new Date(msg.created_at).toLocaleTimeString()}</small>
                            </div>
                        `).join('')}
                    </div>
                    <div class="thread-footer">
                        <small class="text-muted">
                            Bot: ${thread.bot_name || 'Auto'} |
                            Messages: ${thread.messages.length}
                        </small>
                    </div>
                </div>
            `).join('');

            // Check if session is complete
            if (session.status === 'completed') {
                clearInterval(monitor);
                addLog(`Session completed: ${sessionId}`, 'success');
                document.getElementById('progressBar').classList.remove('progress-bar-animated');
            }

        } catch (error) {
            console.error('Error monitoring session:', error);
            clearInterval(monitor);
        }
    }, 2000);
}

/**
 * Stop Multi-Thread Session
 */
async function stopMultiThreadSession() {
    if (!currentSession) return;

    try {
        await apiRequest('stopSession', { session_id: currentSession.session_id });
        currentSession = null;
        document.getElementById('multiThreadArea').style.display = 'none';
        addLog('Multi-thread session stopped', 'warning');
    } catch (error) {
        console.error('Error stopping session:', error);
    }
}

/**
 * Deploy New Bot
 */
function deployNewBot() {
    const modal = new bootstrap.Modal(document.getElementById('deployBotModal'));
    modal.show();
}

/**
 * Submit Deploy Bot Form
 */
async function submitDeployBot() {
    const name = document.getElementById('botName').value;
    const role = document.getElementById('botRole').value;
    const systemPrompt = document.getElementById('systemPrompt').value;
    const schedule = document.getElementById('scheduleCron').value;

    if (!name || !role || !systemPrompt) {
        alert('Please fill in all required fields');
        return;
    }

    try {
        addLog(`Deploying new bot: ${name}`, 'info');

        const result = await apiRequest('deployBot', {
            name: name,
            role: role,
            system_prompt: systemPrompt,
            schedule: schedule,
            enable_tools: true,
            enable_rag: true
        });

        addLog(`Bot deployed successfully: ${result.data.bot_id}`, 'success');

        // Close modal and refresh
        bootstrap.Modal.getInstance(document.getElementById('deployBotModal')).hide();
        document.getElementById('deployBotForm').reset();
        loadBots();

    } catch (error) {
        console.error('Error deploying bot:', error);
        addLog('Failed to deploy bot: ' + error.message, 'error');
    }
}

/**
 * Execute a bot manually
 */
async function executeBot(botId) {
    const prompt = window.prompt('Enter task for the bot:');
    if (!prompt) return;

    try {
        addLog(`Executing bot ${botId}...`, 'info');

        const result = await apiRequest('executeBot', {
            bot_id: botId,
            input: prompt
        });

        addLog(`Bot execution completed in ${result.data.execution_time_ms}ms`, 'success');

        // Show result
        alert(`Bot Response:\n\n${result.data.output}`);

    } catch (error) {
        console.error('Error executing bot:', error);
        addLog('Failed to execute bot: ' + error.message, 'error');
    }
}

/**
 * Pause/Resume a bot
 */
async function pauseBot(botId) {
    try {
        await apiRequest('pauseBot', { bot_id: botId });
        loadBots();
        addLog(`Bot ${botId} paused`, 'warning');
    } catch (error) {
        console.error('Error pausing bot:', error);
    }
}

/**
 * View bot details
 */
async function viewBotDetails(botId) {
    try {
        const result = await apiRequest('getBotDetails', { bot_id: botId });
        const bot = result.data;

        alert(`Bot Details:\n\nName: ${bot.bot_name}\nRole: ${bot.bot_role}\nStatus: ${bot.status}\nTotal Executions: ${bot.total_executions}\nSuccess Rate: ${bot.success_rate}%`);

    } catch (error) {
        console.error('Error loading bot details:', error);
    }
}

/**
 * View Analytics
 */
function viewAnalytics() {
    window.open('/admin/bot-analytics.php', '_blank');
}

/**
 * Refresh Dashboard
 */
function refreshDashboard() {
    addLog('Refreshing dashboard...', 'info');
    loadBots();
    loadMetrics();
}

/**
 * Add log entry
 */
function addLog(message, type = 'info') {
    const container = document.getElementById('logContainer');

    // Remove empty state message
    if (container.querySelector('.text-muted')) {
        container.innerHTML = '';
    }

    const timestamp = new Date().toLocaleTimeString();
    const logEntry = document.createElement('div');
    logEntry.className = `log-entry ${type}`;
    logEntry.textContent = `[${timestamp}] ${message}`;

    container.insertBefore(logEntry, container.firstChild);

    // Keep only last 50 logs
    while (container.children.length > 50) {
        container.removeChild(container.lastChild);
    }
}

/**
 * Clear logs
 */
function clearLogs() {
    document.getElementById('logContainer').innerHTML = '<div class="text-muted text-center py-3">Logs cleared.</div>';
}

/**
 * Start auto-refresh
 */
function startAutoRefresh() {
    refreshInterval = setInterval(() => {
        loadMetrics();
    }, 10000); // Refresh every 10 seconds
}

/**
 * Stop auto-refresh (cleanup)
 */
window.addEventListener('beforeunload', () => {
    if (refreshInterval) {
        clearInterval(refreshInterval);
    }
});
