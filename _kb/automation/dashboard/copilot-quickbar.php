<?php
/**
 * Copilot Quick Commands Bar
 * Add this to the top of CIS dashboard for instant copy/paste access
 */
?>

<style>
.copilot-quickbar {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 8px 15px;
    z-index: 9999;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    font-size: 12px;
}

.copilot-quickbar .quick-commands {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.copilot-quickbar .cmd-btn {
    background: rgba(255,255,255,0.2);
    border: 1px solid rgba(255,255,255,0.3);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 11px;
    transition: all 0.2s;
    white-space: nowrap;
}

.copilot-quickbar .cmd-btn:hover {
    background: rgba(255,255,255,0.3);
    transform: translateY(-1px);
}

.copilot-quickbar .toggle-btn {
    background: rgba(255,255,255,0.3);
    border: none;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 11px;
    margin-left: auto;
}

.copilot-quickbar.collapsed .quick-commands {
    display: none;
}

.copied-toast {
    position: fixed;
    top: 50px;
    right: 20px;
    background: #28a745;
    color: white;
    padding: 8px 16px;
    border-radius: 4px;
    z-index: 10000;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

body { margin-top: 50px !important; }
</style>

<div class="copilot-quickbar" id="copilotQuickbar">
    <div class="quick-commands">
        <strong>🤖 LIVE CIS COMMANDS:</strong>
        
        <button class="cmd-btn" onclick="copyLivePrompt('daily_start')" title="Dynamic daily start with current system state">
            🌅 START DAY (LIVE)
        </button>
        
        <button class="cmd-btn" onclick="copyLivePrompt('new_module')" title="Create module with current CIS standards">
            🆕 NEW MODULE (LIVE)
        </button>
        
        <button class="cmd-btn" onclick="copyLivePrompt('api_development')" title="API work with current patterns">
            � API WORK (LIVE)
        </button>
        
        <button class="cmd-btn" onclick="copyCommand('@workspace Search for [ERROR/FUNCTION] and help me debug it')">
            � DEBUG
        </button>
        
        <button class="cmd-btn" onclick="copyLivePrompt('security_audit')" title="Security audit with current modules">
            � SECURITY (LIVE)
        </button>
        
        <button class="cmd-btn" onclick="copyCommand('@workspace #file:modules/ Which modules need attention?')">
            📁 MODULES
        </button>
        
                <button class="copilot-btn team-btn" onclick="showMultiBotInterface()">
            👥 MULTI-BOT
        </button>
        
        <button class="cmd-btn" onclick="showAnnouncements()" title="Check for system changes">
            🔔 UPDATES
        </button>
        
        <button class="cmd-btn" onclick="showApprovals()" title="Manage pending approvals">
            📋 APPROVALS
        </button>
        
        <button class="toggle-btn" onclick="toggleQuickbar()">
            ▼ HIDE
        </button>
    </div>
    
    <!-- Announcements Panel -->
    <div id="announcementsPanel" style="display: none; background: rgba(255,255,255,0.95); color: #333; padding: 10px; margin-top: 5px; border-radius: 4px;">
        <div id="announcementsContent"></div>
        <button onclick="closeAnnouncements()" style="float: right; background: none; border: none; color: #666;">✕</button>
    </div>
</div>

<script>
let livePrompts = {};
let announcements = [];

// Load live prompts on page load
document.addEventListener('DOMContentLoaded', function() {
    loadLivePrompts();
    checkForAnnouncements();
});

function loadLivePrompts() {
    fetch('/api/automation/live-prompts')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                livePrompts = data.prompts;
                announcements = data.announcements || [];
                
                // Update announcement indicator
                if (announcements.length > 0) {
                    updateAnnouncementIndicator(announcements.length);
                }
            }
        })
        .catch(error => console.log('Live prompts not available:', error));
}

function copyLivePrompt(promptType) {
    if (livePrompts[promptType]) {
        copyCommand(livePrompts[promptType]);
    } else {
        // Fallback to static prompts
        const fallbacks = {
            'daily_start': '@workspace #file:_automation/prompts/daily/morning-checklist.md',
            'new_module': '@workspace #file:_automation/prompts/project/new-module.md Create new module following CIS standards',
            'api_development': '@workspace #file:_automation/prompts/project/api-development.md',
            'security_audit': '@workspace Review all modules for security issues'
        };
        
        copyCommand(fallbacks[promptType] || '@workspace Help me with this task');
    }
}

function copyCommand(command) {
    navigator.clipboard.writeText(command).then(function() {
        showToast('📋 Copied: ' + command.substring(0, 50) + '...');
    });
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'copied-toast';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

function showAnnouncements() {
    if (announcements.length === 0) {
        showToast('✅ No new system updates');
        return;
    }
    
    const panel = document.getElementById('announcementsPanel');
    const content = document.getElementById('announcementsContent');
    
    content.innerHTML = '<h6>🔔 System Updates:</h6>';
    announcements.forEach(announcement => {
        content.innerHTML += `<div style="margin: 5px 0; padding: 5px; background: #f8f9fa; border-left: 3px solid #007bff;">
            <small>${announcement.created_at}</small><br>
            ${announcement.message}
        </div>`;
    });
    
    panel.style.display = 'block';
}

function closeAnnouncements() {
    document.getElementById('announcementsPanel').style.display = 'none';
}

function showApprovals() {
    window.open('/dashboard/?page=automation-approvals', '_blank');
}

function updateAnnouncementIndicator(count) {
    const btn = document.querySelector('[onclick="showAnnouncements()"]');
    if (btn && count > 0) {
        btn.innerHTML = `🔔 UPDATES (${count})`;
        btn.style.background = 'rgba(255,193,7,0.3)';
        btn.style.animation = 'pulse 2s infinite';
    }
}

function checkForAnnouncements() {
    fetch('/api/automation/bot-announcements')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.count > 0) {
                announcements = data.announcements;
                updateAnnouncementIndicator(data.count);
            }
        });
}

function toggleQuickbar() {
    const quickbar = document.getElementById('copilotQuickbar');
    const toggleBtn = quickbar.querySelector('.toggle-btn');
    
    if (quickbar.classList.contains('collapsed')) {
        quickbar.classList.remove('collapsed');
        toggleBtn.textContent = '▼ HIDE';
        document.body.style.marginTop = '50px';
    } else {
        quickbar.classList.add('collapsed');
        toggleBtn.textContent = '▲ SHOW COMMANDS';
        document.body.style.marginTop = '25px';
    }
}

// Auto-refresh live prompts every 5 minutes
setInterval(loadLivePrompts, 300000);

// Auto-check announcements every 2 minutes
setInterval(checkForAnnouncements, 120000);

// Multi-Bot Collaboration Interface
function showMultiBotInterface() {
    const modal = `
        <div class="modal fade" id="multiBotModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">🤖 Multi-Bot Collaboration</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>🎯 Select Collaboration Topic:</h6>
                                <select class="form-control mb-3" id="collaborationTopic">
                                    <option value="">Choose a topic...</option>
                                    <option value="new_module">🆕 New Module Development</option>
                                    <option value="api_design">🔧 API Design & Implementation</option>
                                    <option value="security_review">🔒 Security Review & Audit</option>
                                    <option value="performance_optimization">⚡ Performance Optimization</option>
                                    <option value="ui_redesign">🎨 UI/UX Redesign</option>
                                    <option value="database_optimization">🗄️ Database Optimization</option>
                                    <option value="architecture_review">🏗️ Architecture Review</option>
                                    <option value="custom">✨ Custom Collaboration</option>
                                </select>
                                
                                <div id="customTopicDiv" class="d-none mb-3">
                                    <input type="text" class="form-control" id="customTopic" placeholder="Describe your collaboration topic...">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h6>👥 Select Bot Participants:</h6>
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="architectBot" value="architect">
                                        <label class="form-check-label" for="architectBot">
                                            🏗️ Architect Bot (System Design)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="securityBot" value="security">
                                        <label class="form-check-label" for="securityBot">
                                            🔒 Security Bot (Vulnerability Assessment)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="apiBot" value="api">
                                        <label class="form-check-label" for="apiBot">
                                            🔧 API Bot (Endpoint Design)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="frontendBot" value="frontend">
                                        <label class="form-check-label" for="frontendBot">
                                            🎨 Frontend Bot (UI/UX Design)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="databaseBot" value="database">
                                        <label class="form-check-label" for="databaseBot">
                                            🗄️ Database Bot (Schema & Performance)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <h6>📝 Additional Context (Optional):</h6>
                            <textarea class="form-control" id="collaborationContext" rows="3" 
                                placeholder="Provide any specific requirements, constraints, or additional context for the collaboration..."></textarea>
                        </div>
                        
                        <div id="activeSessions" class="mt-4">
                            <h6>🔄 Active Multi-Bot Sessions:</h6>
                            <div id="sessionsList" class="list-group">
                                <!-- Dynamic session list -->
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="startMultiBotSession()">Start Collaboration</button>
                        <button type="button" class="btn btn-info" onclick="refreshActiveSessions()">Refresh Sessions</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if present
    $('#multiBotModal').remove();
    
    // Add modal to page
    $('body').append(modal);
    
    // Show modal
    $('#multiBotModal').modal('show');
    
    // Handle topic change
    $('#collaborationTopic').change(function() {
        if ($(this).val() === 'custom') {
            $('#customTopicDiv').removeClass('d-none');
        } else {
            $('#customTopicDiv').addClass('d-none');
        }
        
        // Auto-select recommended bots based on topic
        autoSelectBots($(this).val());
    });
    
    // Load active sessions
    refreshActiveSessions();
}

// Auto-select recommended bots based on topic
function autoSelectBots(topic) {
    // Clear all selections
    $('.form-check-input[type="checkbox"]').prop('checked', false);
    
    const recommendations = {
        'new_module': ['architect', 'security', 'database'],
        'api_design': ['api', 'architect', 'security'],
        'security_review': ['security', 'architect', 'api'],
        'performance_optimization': ['database', 'api', 'frontend'],
        'ui_redesign': ['frontend', 'architect', 'api'],
        'database_optimization': ['database', 'api', 'architect'],
        'architecture_review': ['architect', 'security', 'database']
    };
    
    if (recommendations[topic]) {
        recommendations[topic].forEach(bot => {
            $(`#${bot}Bot`).prop('checked', true);
        });
    }
}

// Start multi-bot collaboration session
async function startMultiBotSession() {
    const topic = $('#collaborationTopic').val() === 'custom' ? 
        $('#customTopic').val() : $('#collaborationTopic').val();
    const context = $('#collaborationContext').val();
    
    if (!topic) {
        alert('Please select or enter a collaboration topic');
        return;
    }
    
    const selectedBots = [];
    $('.form-check-input[type="checkbox"]:checked').each(function() {
        selectedBots.push($(this).val());
    });
    
    if (selectedBots.length === 0) {
        alert('Please select at least one bot to collaborate with');
        return;
    }
    
    try {
        const response = await fetch('/api/automation/multi-bot-collaboration.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'start_session',
                topic: topic,
                participants: selectedBots,
                context: context
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Generate the collaboration prompt
            const collaborationPrompt = generateCollaborationPrompt(topic, selectedBots, result.data.session_id, context);
            
            // Copy to clipboard
            navigator.clipboard.writeText(collaborationPrompt).then(() => {
                alert('Multi-bot collaboration prompt copied to clipboard! Paste it into your conversation to start the session.');
                $('#multiBotModal').modal('hide');
            });
        } else {
            alert('Error starting collaboration: ' + result.error.message);
        }
    } catch (error) {
        alert('Error starting collaboration: ' + error.message);
    }
}

// Generate collaboration prompt
function generateCollaborationPrompt(topic, bots, sessionId, context) {
    const botDescriptions = {
        'architect': '🏗️ Architect Bot (System Design & Architecture)',
        'security': '🔒 Security Bot (Security Review & Vulnerability Assessment)',
        'api': '🔧 API Bot (API Design & Implementation)', 
        'frontend': '🎨 Frontend Bot (UI/UX Design & Implementation)',
        'database': '🗄️ Database Bot (Database Design & Optimization)'
    };
    
    let prompt = `🤖 **MULTI-BOT COLLABORATION SESSION**

**Session ID:** ${sessionId}
**Topic:** ${topic}
**Participants:** ${bots.map(bot => botDescriptions[bot] || bot).join(', ')}

${context ? `**Context:** ${context}\n` : ''}
---

@workspace Start multi-bot collaboration on: ${topic}

**Bot Roles:**
${bots.map(bot => `- ${botDescriptions[bot] || bot}: #file:_automation/prompts/multi-bot/${bot}.md`).join('\n')}

**Collaboration Flow:**
1. Each bot should analyze the topic from their specialized perspective
2. Share findings and recommendations
3. Build on other bots' contributions
4. Work toward consensus on the best approach
5. Provide implementation recommendations

**Instructions for Bots:**
- Use your specialized knowledge and role-specific prompt templates
- Reference other bots' contributions and build on them
- Flag any conflicts or concerns with other bots' recommendations
- Work collaboratively toward the best solution

Let's begin the multi-bot collaboration!`;

    return prompt;
}

// Refresh active sessions
async function refreshActiveSessions() {
    try {
        const response = await fetch('/api/automation/multi-bot-collaboration.php?action=list_sessions');
        const result = await response.json();
        
        const sessionsList = $('#sessionsList');
        sessionsList.empty();
        
        if (result.success && result.data.length > 0) {
            result.data.forEach(session => {
                const sessionItem = `
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${session.topic}</strong><br>
                            <small class="text-muted">
                                Participants: ${session.participants.join(', ')}<br>
                                Started: ${new Date(session.created_at).toLocaleString()}
                            </small>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-primary" onclick="joinSession('${session.session_id}')">
                                Join
                            </button>
                        </div>
                    </div>
                `;
                sessionsList.append(sessionItem);
            });
        } else {
            sessionsList.html('<div class="list-group-item text-muted">No active sessions</div>');
        }
    } catch (error) {
        console.error('Error refreshing sessions:', error);
    }
}

// Join existing session
async function joinSession(sessionId) {
    try {
        const response = await fetch(`/api/automation/multi-bot-collaboration.php?action=get_context&session_id=${sessionId}`);
        const result = await response.json();
        
        if (result.success) {
            const joinPrompt = `🤖 **JOIN MULTI-BOT COLLABORATION**

**Session ID:** ${sessionId}
**Topic:** ${result.data.topic}
**Existing Participants:** ${result.data.participants.join(', ')}

**Current Conversation Context:**
${result.data.conversation.map(msg => `- **${msg.bot_id}:** ${msg.message}`).join('\n')}

---

@workspace Join multi-bot session ${sessionId}:
- Review the conversation above
- Add my perspective as [YOUR_BOT_ROLE]
- Build on existing contributions
- Help work toward consensus`;

            navigator.clipboard.writeText(joinPrompt).then(() => {
                alert('Session join prompt copied to clipboard! Paste it into your conversation to join the session.');
                $('#multiBotModal').modal('hide');
            });
        } else {
            alert('Error joining session: ' + result.error.message);
        }
    } catch (error) {
        alert('Error joining session: ' + error.message);
    }
}

// Auto-hide after 30 seconds of inactivity
let hideTimer;
function resetHideTimer() {
    clearTimeout(hideTimer);
    hideTimer = setTimeout(() => {
        const quickbar = document.getElementById('copilotQuickbar');
        if (!quickbar.classList.contains('collapsed')) {
            quickbar.classList.add('collapsed');
            quickbar.querySelector('.toggle-btn').textContent = '▲ SHOW COMMANDS';
            document.body.style.marginTop = '25px';
        }
    }, 30000);
}

document.addEventListener('mousemove', resetHideTimer);
document.addEventListener('click', resetHideTimer);
resetHideTimer();
</script>