/**
 * Intelligence Hub Dashboard - Frontend Controller
 *
 * Handles real-time updates, AI command processing, and user interactions
 */

class IntelligenceHub {
    constructor() {
        this.wsConnection = null;
        this.updateInterval = null;
        this.agents = [];
        this.currentApproval = null;

        this.init();
    }

    init() {
        console.log('Intelligence Hub initializing...');

        // Set up event listeners
        this.setupEventListeners();

        // Connect to WebSocket
        this.connectWebSocket();

        // Start periodic updates
        this.startPeriodicUpdates();

        // Load initial data
        this.loadDashboardData();

        // Initialize voice recognition if available
        this.initVoiceRecognition();
    }

    setupEventListeners() {
        // AI Command input
        document.getElementById('sendCommand').addEventListener('click', () => {
            this.sendCommand();
        });

        document.getElementById('aiCommandInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendCommand();
            }
        });

        // Voice command
        document.getElementById('voiceCommand').addEventListener('click', () => {
            this.startVoiceRecognition();
        });

        // Approval button
        document.getElementById('approveButton').addEventListener('click', () => {
            this.approveDecision();
        });
    }

    connectWebSocket() {
        const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const wsUrl = `${protocol}//${window.location.host}/websocket`;

        try {
            this.wsConnection = new WebSocket(wsUrl);

            this.wsConnection.onopen = () => {
                console.log('WebSocket connected');
                this.updateSystemStatus('success', 'Connected');
            };

            this.wsConnection.onmessage = (event) => {
                this.handleWebSocketMessage(JSON.parse(event.data));
            };

            this.wsConnection.onerror = (error) => {
                console.error('WebSocket error:', error);
                this.updateSystemStatus('error', 'Connection Error');
            };

            this.wsConnection.onclose = () => {
                console.log('WebSocket disconnected. Reconnecting in 5s...');
                this.updateSystemStatus('warning', 'Reconnecting...');
                setTimeout(() => this.connectWebSocket(), 5000);
            };
        } catch (e) {
            console.error('Failed to connect WebSocket:', e);
        }
    }

    handleWebSocketMessage(data) {
        console.log('WebSocket message:', data);

        switch (data.type) {
            case 'agent_status':
                this.updateAgentStatus(data.agent, data.status);
                break;
            case 'task_complete':
                this.addActivity(data.message);
                this.updateMetrics();
                break;
            case 'approval_request':
                this.showApprovalRequest(data);
                break;
            case 'alert':
                this.addAlert(data);
                break;
            case 'recommendation':
                this.addRecommendation(data);
                break;
        }
    }

    async sendCommand() {
        const input = document.getElementById('aiCommandInput');
        const command = input.value.trim();

        if (!command) return;

        // Show loading state
        const responseDiv = document.getElementById('aiResponse');
        const responseText = document.getElementById('aiResponseText');
        responseDiv.classList.remove('d-none');
        responseText.innerHTML = '<i class="spinner-border spinner-border-sm"></i> Processing...';

        try {
            const response = await fetch('api/ai/command', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ command })
            });

            const result = await response.json();

            if (result.success) {
                responseText.textContent = result.response;

                // If there are actions, show them
                if (result.actions && result.actions.length > 0) {
                    this.showActions(result.actions);
                }

                // Clear input
                input.value = '';
            } else {
                responseText.textContent = 'Error: ' + (result.error || 'Unknown error');
            }
        } catch (error) {
            console.error('Command error:', error);
            responseText.textContent = 'Failed to process command. Please try again.';
        }
    }

    async loadDashboardData() {
        try {
            const response = await fetch('api/dashboard/overview');
            const data = await response.json();

            if (data.success) {
                this.updateQuickStats(data.stats);
                this.updateAgentGrid(data.agents);
                this.updateRecommendations(data.recommendations);
                this.updateRecentActivity(data.activity);
                this.updateAlerts(data.alerts);
            }
        } catch (error) {
            console.error('Failed to load dashboard data:', error);
        }
    }

    updateQuickStats(stats) {
        document.getElementById('activeAgents').textContent = stats.active_agents || 0;
        document.getElementById('tasksCompleted').textContent = stats.tasks_completed || 0;
        document.getElementById('pendingApprovals').textContent = stats.pending_approvals || 0;
        document.getElementById('costSavings').textContent = '$' + (stats.cost_savings || 0).toLocaleString();
    }

    updateAgentGrid(agents) {
        const grid = document.getElementById('agentStatusGrid');
        grid.innerHTML = '';

        agents.forEach(agent => {
            const statusClass = this.getStatusClass(agent.status);
            const statusIcon = this.getStatusIcon(agent.status);

            const card = document.createElement('div');
            card.className = 'agent-card';
            card.innerHTML = `
                <div class="agent-card__header">
                    <div class="agent-card__icon agent-card__icon--${agent.status}">
                        <i class="fas ${statusIcon}"></i>
                    </div>
                    <div>
                        <h4 class="agent-card__name">${agent.name}</h4>
                        <span class="agent-card__status badge badge--${statusClass}">${agent.status}</span>
                    </div>
                </div>
                <p class="agent-card__description">${agent.description || 'Autonomous agent performing tasks'}</p>
                <div class="agent-card__meta">
                    <span class="agent-card__tasks">${agent.tasks_today || 0} tasks today</span>
                </div>
            `;
            grid.appendChild(card);
        });

        this.agents = agents;
    }    updateRecommendations(recommendations) {
        const list = document.getElementById('recommendationsList');

        if (!recommendations || recommendations.length === 0) {
            list.innerHTML = '<p class="text--muted">No recommendations at this time.</p>';
            return;
        }

        list.innerHTML = recommendations.map(rec => `
            <div class="alert alert--${rec.priority === 'high' ? 'warning' : 'info'}" style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem;">
                    <div style="flex: 1;">
                        <h6 style="margin: 0 0 0.5rem; font-weight: 600;">${rec.title}</h6>
                        <p style="margin: 0 0 0.5rem; font-size: 0.875rem;">${rec.description}</p>
                        <small class="text--muted">
                            <i class="fas fa-bolt"></i> ${rec.agent}
                            • Confidence: ${(rec.confidence * 100).toFixed(0)}%
                            ${rec.expected_impact ? ' • Impact: ' + rec.expected_impact : ''}
                        </small>
                    </div>
                    <div style="display: flex; gap: 0.5rem; flex-shrink: 0;">
                        ${rec.requires_approval ? `
                            <button class="btn btn--sm btn--success" onclick="hub.approveRecommendation(${rec.id})" title="Approve recommendation">
                                <i class="fas fa-check"></i> Approve
                            </button>
                            <button class="btn btn--sm btn--danger" onclick="hub.declineRecommendation(${rec.id})" title="Decline recommendation">
                                <i class="fas fa-times"></i>
                            </button>
                        ` : `
                            <button class="btn btn--sm btn--primary" onclick="hub.viewDetails(${rec.id})" title="View details">
                                <i class="fas fa-info-circle"></i> Details
                            </button>
                        `}
                    </div>
                </div>
            </div>
        `).join('');
    }

    updateRecentActivity(activities) {
        const list = document.getElementById('recentActivityList');

        if (!activities || activities.length === 0) {
            list.innerHTML = '<p class="text--muted">No recent activity.</p>';
            return;
        }

        list.innerHTML = activities.slice(0, 10).map(activity => `
            <a href="#" class="activity-item">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">
                    <div style="flex: 1;">
                        <h6 style="margin: 0 0 0.25rem; font-weight: 600; font-size: 0.875rem;">${activity.title}</h6>
                        <p style="margin: 0 0 0.25rem; font-size: 0.8125rem; color: var(--color-text-secondary);">${activity.description}</p>
                        <small class="text--muted">${activity.agent}</small>
                    </div>
                    <small class="text--muted" style="flex-shrink: 0; margin-left: 1rem;">${activity.time_ago}</small>
                </div>
            </a>
        `).join('');
    }

    updateAlerts(alerts) {
        const list = document.getElementById('alertsList');
        const notificationBadge = document.getElementById('notificationCount');

        if (notificationBadge) {
            notificationBadge.textContent = alerts.length;
        }

        if (!alerts || alerts.length === 0) {
            list.innerHTML = '<p class="text--success"><i class="fas fa-check-circle"></i> No alerts</p>';
            return;
        }

        list.innerHTML = alerts.map(alert => {
            const severityClass = this.getSeverityClass(alert.severity);
            return `
                <div class="alert-item alert-item--${severityClass}">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">
                        <div style="flex: 1;">
                            <strong style="display: block; margin-bottom: 0.25rem;">${alert.title}</strong>
                            <p style="margin: 0 0 0.25rem; font-size: 0.8125rem;">${alert.message}</p>
                            <small class="text--muted">${alert.time_ago}</small>
                        </div>
                        <button class="btn btn--sm btn--ghost" onclick="hub.dismissAlert(${alert.id})" title="Dismiss alert" style="flex-shrink: 0; margin-left: 1rem;">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
        }).join('');
    }

    showApprovalRequest(data) {
        this.currentApproval = data;

        const modalBody = document.getElementById('approvalModalBody');
        modalBody.innerHTML = `
            <div class="alert alert-warning">
                <h5>${data.title}</h5>
                <p>${data.description}</p>
            </div>

            <h6>Proposed Action:</h6>
            <div class="bg-light p-3 rounded mb-3">
                <code>${JSON.stringify(data.action, null, 2)}</code>
            </div>

            <h6>AI Analysis:</h6>
            <p>${data.reasoning}</p>

            <div class="row">
                <div class="col-md-6">
                    <strong>Confidence:</strong> ${(data.confidence * 100).toFixed(0)}%
                </div>
                <div class="col-md-6">
                    <strong>Expected Impact:</strong> ${data.expected_impact || 'N/A'}
                </div>
            </div>
        `;

        const modal = new bootstrap.Modal(document.getElementById('approvalModal'));
        modal.show();
    }

    async approveDecision() {
        if (!this.currentApproval) return;

        try {
            const response = await fetch('api/ai/approve', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    decision_id: this.currentApproval.id,
                    approved: true
                })
            });

            const result = await response.json();

            if (result.success) {
                // Close modal
                const modal = document.getElementById('approvalModal');
                if (modal) {
                    modal.classList.remove('modal--visible');
                }

                // Show success message
                this.showToast('Decision Approved', 'The AI will execute the approved action.', 'success');

                // Refresh dashboard
                this.loadDashboardData();
            }
        } catch (error) {
            console.error('Approval error:', error);
            this.showToast('Error', 'Failed to approve decision.', 'error');
        }
    }

    async approveRecommendation(id) {
        // Similar to approveDecision but for recommendations
        console.log('Approving recommendation:', id);
        // Implementation here
    }

    async declineRecommendation(id) {
        console.log('Declining recommendation:', id);
        // Implementation here
    }

    async dismissAlert(id) {
        console.log('Dismissing alert:', id);
        // Implementation here
        this.loadDashboardData();
    }

    initVoiceRecognition() {
        if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            this.recognition = new SpeechRecognition();
            this.recognition.continuous = false;
            this.recognition.interimResults = false;

            this.recognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript;
                document.getElementById('aiCommandInput').value = transcript;
                this.sendCommand();
            };

            this.recognition.onerror = (event) => {
                console.error('Speech recognition error:', event.error);
                this.showToast('Voice Error', 'Failed to recognize speech.', 'error');
            };
        } else {
            console.log('Speech recognition not supported');
            document.getElementById('voiceCommand').disabled = true;
        }
    }

    startVoiceRecognition() {
        if (this.recognition) {
            this.recognition.start();
            document.getElementById('voiceCommand').innerHTML = '<i class="fas fa-microphone text--danger"></i> Listening...';

            setTimeout(() => {
                document.getElementById('voiceCommand').innerHTML = '<i class="fas fa-microphone"></i> Voice';
            }, 5000);
        }
    }

    startPeriodicUpdates() {
        // Update metrics every 30 seconds
        this.updateInterval = setInterval(() => {
            this.updateMetrics();
        }, 30000);
    }

    async updateMetrics() {
        try {
            const response = await fetch('api/dashboard/metrics');
            const data = await response.json();

            if (data.success) {
                this.updateQuickStats(data.stats);
            }
        } catch (error) {
            console.error('Metrics update failed:', error);
        }
    }

    updateSystemStatus(level, message) {
        const statusElement = document.getElementById('systemHealth');
        if (!statusElement) return;

        const iconClass = level === 'success' ? 'text--success' : level === 'warning' ? 'text--warning' : 'text--danger';
        statusElement.innerHTML = `<i class="fas fa-heartbeat ${iconClass}"></i> ${message}`;
    }

    getStatusClass(status) {
        const map = {
            'idle': 'secondary',
            'active': 'primary',
            'busy': 'warning',
            'error': 'danger',
            'disabled': 'dark'
        };
        return map[status] || 'secondary';
    }

    getStatusIcon(status) {
        const map = {
            'idle': 'fa-pause-circle',
            'active': 'fa-play-circle',
            'busy': 'fa-hourglass-half',
            'error': 'fa-exclamation-triangle',
            'disabled': 'fa-minus-circle'
        };
        return map[status] || 'fa-question-circle';
    }

    getSeverityClass(severity) {
        const map = {
            'info': 'info',
            'warning': 'warning',
            'error': 'danger',
            'critical': 'danger'
        };
        return map[severity] || 'info';
    }

    showToast(title, message, type = 'info') {
        // Simple toast notification (could be enhanced with Bootstrap Toast)
        console.log(`[${type.toUpperCase()}] ${title}: ${message}`);
    }
}

// Initialize when DOM is ready
let hub;
document.addEventListener('DOMContentLoaded', () => {
    hub = new IntelligenceHub();
});
