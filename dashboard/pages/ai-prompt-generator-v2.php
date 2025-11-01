<?php
/**
 * AI Prompt Generator 2.0 - COMPLETE OVERHAUL
 *
 * Features:
 * - Rule-based prompt generation
 * - Auto-learning from user patterns
 * - CIS-specific rules integration
 * - MCP tool discovery system
 * - Live preview and editing
 * - VS Code auto-sync
 * - User preference learning
 * - Priority-based rule system
 *
 * @package CIS\Dashboard
 * @version 2.0.0
 */

$pageTitle = 'AI Prompt Generator 2.0';
require_once __DIR__ . '/../includes/header.php';

// Initialize rule engine
require_once __DIR__ . '/../api/rule-engine.php';
?>

<style>
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    --danger-gradient: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
    --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.prompt-hero {
    background: var(--primary-gradient);
    border-radius: 20px;
    padding: 40px;
    color: white;
    margin-bottom: 30px;
    box-shadow: 0 15px 40px rgba(102, 126, 234, 0.3);
    position: relative;
    overflow: hidden;
}

.prompt-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: pulse 15s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    text-align: center;
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.stat-card.critical { border-left-color: #dc3545; }
.stat-card.high { border-left-color: #fd7e14; }
.stat-card.medium { border-left-color: #ffc107; }
.stat-card.info { border-left-color: #17a2b8; }

.stat-number {
    font-size: 36px;
    font-weight: 700;
    color: #667eea;
    margin: 10px 0;
}

.section-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.section-card:hover {
    box-shadow: 0 8px 30px rgba(0,0,0,0.12);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.rule-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px;
}

.rule-card {
    background: #f8f9fa;
    border-left: 4px solid #dee2e6;
    padding: 20px;
    border-radius: 10px;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
}

.rule-card:hover {
    transform: translateX(5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.rule-card.selected {
    background: #e7f1ff;
    border-left-color: #0d6efd;
}

.rule-card.critical { border-left-color: #dc3545; }
.rule-card.high { border-left-color: #fd7e14; }
.rule-card.medium { border-left-color: #ffc107; }
.rule-card.low { border-left-color: #28a745; }

.rule-card.disabled {
    opacity: 0.5;
    filter: grayscale(0.5);
}

.rule-toggle {
    position: absolute;
    top: 15px;
    right: 15px;
}

.toggle-switch {
    position: relative;
    width: 50px;
    height: 26px;
    background-color: #ccc;
    border-radius: 26px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.toggle-switch.active {
    background: var(--success-gradient);
}

.toggle-slider {
    position: absolute;
    top: 3px;
    left: 3px;
    width: 20px;
    height: 20px;
    background-color: white;
    border-radius: 50%;
    transition: transform 0.3s;
}

.toggle-switch.active .toggle-slider {
    transform: translateX(24px);
}

.priority-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    margin-right: 8px;
}

.badge-critical { background: #dc3545; color: white; }
.badge-high { background: #fd7e14; color: white; }
.badge-medium { background: #ffc107; color: #333; }
.badge-low { background: #28a745; color: white; }

.mcp-tool-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 3px solid transparent;
}

.mcp-tool-card:hover {
    transform: scale(1.02);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
}

.mcp-tool-card.selected {
    border-color: #f5f5f5;
    background: var(--warning-gradient);
}

.tool-modes {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 10px;
}

.mode-badge {
    background: rgba(255, 255, 255, 0.2);
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 600;
}

.live-preview {
    background: #1e1e1e;
    color: #d4d4d4;
    padding: 30px;
    border-radius: 15px;
    font-family: 'Monaco', 'Courier New', monospace;
    font-size: 14px;
    line-height: 1.8;
    max-height: 600px;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    position: relative;
}

.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #404040;
}

.preview-actions {
    display: flex;
    gap: 10px;
}

.btn-glow {
    background: var(--primary-gradient);
    border: none;
    color: white;
    padding: 12px 28px;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    position: relative;
    overflow: hidden;
}

.btn-glow:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
}

.btn-glow:active {
    transform: translateY(-1px);
}

.btn-glow::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.btn-glow:hover::before {
    left: 100%;
}

.btn-success { background: var(--success-gradient); }
.btn-danger { background: var(--danger-gradient); }

.category-filter {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.filter-btn {
    padding: 10px 20px;
    border-radius: 20px;
    border: 2px solid #dee2e6;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
}

.filter-btn:hover {
    border-color: #667eea;
    color: #667eea;
}

.filter-btn.active {
    background: var(--primary-gradient);
    color: white;
    border-color: transparent;
}

.search-box {
    position: relative;
    margin-bottom: 20px;
}

.search-box input {
    width: 100%;
    padding: 15px 50px 15px 20px;
    border-radius: 25px;
    border: 2px solid #dee2e6;
    font-size: 16px;
    transition: all 0.3s ease;
}

.search-box input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.search-icon {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: #667eea;
}

.learning-indicator {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--warning-gradient);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    animation: blink 2s ease-in-out infinite;
}

@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.quick-actions-sticky {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 1000;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.fab-button {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: var(--primary-gradient);
    color: white;
    border: none;
    cursor: pointer;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.5);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.fab-button:hover {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 15px 40px rgba(102, 126, 234, 0.7);
}

.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background: white;
    padding: 20px 30px;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
    z-index: 2000;
    animation: slideIn 0.3s ease;
    display: none;
}

.toast.show {
    display: block;
}

@keyframes slideIn {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    z-index: 9999;
    display: none;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    color: white;
}

.loading-overlay.active {
    display: flex;
}

.spinner-large {
    width: 80px;
    height: 80px;
    border: 6px solid rgba(255,255,255,0.2);
    border-top-color: #667eea;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.cis-badge {
    background: var(--warning-gradient);
    color: white;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 10px;
    font-weight: 700;
    display: inline-block;
    margin-left: 8px;
}
</style>

<div class="container-fluid py-4">
    <!-- Hero Section -->
    <div class="prompt-hero">
        <h1><i class="fas fa-brain"></i> AI Prompt Generator 2.0</h1>
        <p class="mb-0">Rule-based, auto-learning prompt system with MCP tool integration and CIS-specific standards</p>
        <div class="learning-indicator mt-3">
            <i class="fas fa-graduation-cap"></i>
            Auto-Learning Active
        </div>
    </div>

    <!-- Statistics Dashboard -->
    <div class="stats-grid">
        <div class="stat-card critical">
            <i class="fas fa-shield-alt fa-2x"></i>
            <div class="stat-number" id="stat-rules-total">0</div>
            <div>Total Rules</div>
        </div>
        <div class="stat-card high">
            <i class="fas fa-building fa-2x"></i>
            <div class="stat-number" id="stat-rules-cis">0</div>
            <div>CIS Specific</div>
        </div>
        <div class="stat-card medium">
            <i class="fas fa-tools fa-2x"></i>
            <div class="stat-number">9</div>
            <div>MCP Tools</div>
        </div>
        <div class="stat-card info">
            <i class="fas fa-robot fa-2x"></i>
            <div class="stat-number" id="stat-auto-learned">0</div>
            <div>Auto-Learned</div>
        </div>
    </div>

    <!-- Category Filters -->
    <div class="section-card">
        <div class="section-header">
            <h3><i class="fas fa-filter"></i> Rule Categories</h3>
            <button class="btn-glow btn-sm" onclick="selectAllCategories()">
                <i class="fas fa-check-double"></i> Select All
            </button>
        </div>
        <div class="category-filter" id="category-filters">
            <!-- Dynamically populated -->
        </div>
    </div>

    <!-- Search Rules -->
    <div class="section-card">
        <div class="search-box">
            <input type="text"
                   id="rule-search"
                   placeholder="Search rules, patterns, examples..."
                   onkeyup="searchRules()">
            <i class="fas fa-search search-icon"></i>
        </div>
    </div>

    <!-- MCP Tools Integration -->
    <div class="section-card">
        <div class="section-header">
            <h3><i class="fas fa-tools"></i> MCP Tools - Select for Integration</h3>
            <span class="badge bg-primary">9 Available</span>
        </div>
        <div id="mcp-tools-grid">
            <!-- Dynamically populated with 9 MCP tools -->
        </div>
    </div>

    <!-- Rules Grid -->
    <div class="section-card">
        <div class="section-header">
            <h3><i class="fas fa-list-check"></i> Active Rules</h3>
            <div>
                <span class="badge bg-danger" id="count-critical">0 Critical</span>
                <span class="badge bg-warning" id="count-high">0 High</span>
                <span class="badge bg-info" id="count-medium">0 Medium</span>
            </div>
        </div>
        <div class="rule-grid" id="rules-grid">
            <!-- Dynamically populated -->
        </div>
    </div>

    <!-- Live Preview -->
    <div class="section-card">
        <div class="section-header">
            <h3><i class="fas fa-eye"></i> Live Preview</h3>
            <button class="btn-glow" onclick="generatePrompt()">
                <i class="fas fa-magic"></i> Generate Prompt
            </button>
        </div>
        <div class="live-preview" id="live-preview">
            <div class="preview-header">
                <span><i class="fas fa-code"></i> Generated Instructions</span>
                <div class="preview-actions">
                    <button class="btn-glow btn-sm" onclick="copyPrompt()" title="Copy to Clipboard">
                        <i class="fas fa-copy"></i>
                    </button>
                    <button class="btn-glow btn-success btn-sm" onclick="savePrompt()" title="Save as .md">
                        <i class="fas fa-save"></i>
                    </button>
                    <button class="btn-glow btn-sm" onclick="syncToVSCode()" title="Sync to VS Code">
                        <i class="fas fa-sync"></i>
                    </button>
                </div>
            </div>
            <div id="preview-content">
                <p style="color: #888;">Select rules and generate prompt to see preview...</p>
            </div>
        </div>
    </div>

    <!-- Rule Learning Section -->
    <div class="section-card">
        <div class="section-header">
            <h3><i class="fas fa-graduation-cap"></i> Auto-Learning Suggestions</h3>
            <span class="learning-indicator">
                <i class="fas fa-brain"></i>
                AI Analyzing Patterns
            </span>
        </div>
        <div id="learning-suggestions">
            <p class="text-muted">AI will suggest new rules based on your coding patterns...</p>
        </div>
    </div>
</div>

<!-- Floating Action Buttons -->
<div class="quick-actions-sticky">
    <button class="fab-button" onclick="generatePrompt()" title="Generate Prompt">
        <i class="fas fa-magic"></i>
    </button>
    <button class="fab-button btn-success" onclick="savePrompt()" title="Save">
        <i class="fas fa-save"></i>
    </button>
    <button class="fab-button" onclick="syncToVSCode()" title="Sync to VS Code">
        <i class="fas fa-sync"></i>
    </button>
</div>

<!-- Toast Notification -->
<div class="toast" id="toast">
    <div id="toast-content"></div>
</div>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loading-overlay">
    <div class="spinner-large"></div>
    <h3 class="mt-4">Generating Your Prompt...</h3>
    <p>This may take a few seconds</p>
</div>

<script>
// Global state
let categories = [];
let rules = [];
let selectedCategories = [];
let selectedRules = [];
let selectedTools = [];
let generatedPrompt = '';

// MCP Tools Definition
const mcpTools = [
    {
        name: 'health',
        description: 'System health checks and uptime monitoring',
        icon: 'heartbeat',
        usage: 'GET /mcp/dispatcher.php?tool=health',
        examples: ['Check if MCP is responding', 'Verify system components']
    },
    {
        name: 'crawler',
        description: 'Comprehensive website testing and auditing',
        icon: 'spider',
        usage: 'POST with tool=crawler&mode=full&url=...',
        modes: ['quick', 'authenticated', 'interactive', 'full', 'errors_only'],
        profiles: ['cis_desktop', 'cis_mobile', 'cis_tablet', 'gpt_hub', 'customer'],
        examples: ['Test entire website', 'Find broken links', 'Capture screenshots', 'Detect JS errors']
    },
    {
        name: 'search',
        description: 'Semantic search across 22K+ files',
        icon: 'search',
        usage: 'POST with tool=search&query=...',
        examples: ['Find code patterns', 'Search documentation', 'Locate functions']
    },
    {
        name: 'mysql',
        description: 'Safe database querying with security validation',
        icon: 'database',
        usage: 'POST with tool=mysql&query=...',
        examples: ['Run SELECT queries', 'Check table structures', 'Analyze data']
    },
    {
        name: 'browser',
        description: 'Web browsing and screenshot capture',
        icon: 'globe',
        usage: 'POST with tool=browser&url=...',
        examples: ['Take webpage screenshots', 'Check page rendering', 'Verify content']
    },
    {
        name: 'password',
        description: 'Encrypted password storage and retrieval',
        icon: 'key',
        usage: 'POST with tool=password&action=store/retrieve',
        examples: ['Store API keys securely', 'Retrieve credentials', 'Manage secrets']
    },
    {
        name: 'analytics',
        description: 'Search analytics and usage trends',
        icon: 'chart-line',
        usage: 'GET /mcp/dispatcher.php?tool=analytics',
        examples: ['Track search patterns', 'Analyze usage trends', 'View statistics']
    },
    {
        name: 'fuzzy',
        description: 'Fuzzy search for misspellings and variations',
        icon: 'magic',
        usage: 'POST with tool=fuzzy&query=...',
        examples: ['Find similar terms', 'Handle typos', 'Suggest corrections']
    },
    {
        name: 'stats',
        description: 'System statistics and file counts',
        icon: 'chart-bar',
        usage: 'GET /mcp/dispatcher.php?tool=stats',
        examples: ['Get file counts', 'System overview', 'Database stats']
    }
];

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
    loadRules();
    renderMCPTools();
    loadStats();
});

// Load categories from API
async function loadCategories() {
    try {
        const response = await fetch('/dashboard/api/rule-engine.php?action=categories');
        const data = await response.json();

        if (data.success) {
            categories = data.data;
            renderCategories();
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Render category filters
function renderCategories() {
    const container = document.getElementById('category-filters');
    container.innerHTML = categories.map(cat => `
        <button class="filter-btn ${selectedCategories.includes(cat.id) ? 'active' : ''}"
                onclick="toggleCategory(${cat.id})"
                style="border-color: ${cat.color}">
            <i class="fas fa-${cat.icon}"></i>
            ${cat.name}
            <span class="badge bg-light text-dark ms-2">${cat.active_count}</span>
        </button>
    `).join('');
}

// Toggle category selection
function toggleCategory(categoryId) {
    const index = selectedCategories.indexOf(categoryId);
    if (index > -1) {
        selectedCategories.splice(index, 1);
    } else {
        selectedCategories.push(categoryId);
    }
    renderCategories();
    filterRules();
}

// Select all categories
function selectAllCategories() {
    selectedCategories = categories.map(c => c.id);
    renderCategories();
    filterRules();
}

// Load rules from API
async function loadRules() {
    try {
        const response = await fetch('/dashboard/api/rule-engine.php?action=rules');
        const data = await response.json();

        if (data.success) {
            rules = data.data;
            selectedRules = rules.filter(r => r.is_active).map(r => r.id);
            renderRules();
            updateStats();
        }
    } catch (error) {
        console.error('Error loading rules:', error);
    }
}

// Render rules grid
function renderRules() {
    const container = document.getElementById('rules-grid');
    const filtered = filterRules();

    container.innerHTML = filtered.map(rule => `
        <div class="rule-card ${rule.priority} ${selectedRules.includes(rule.id) ? 'selected' : ''} ${!rule.is_active ? 'disabled' : ''}"
             onclick="toggleRule(${rule.id})">
            <div class="rule-toggle" onclick="event.stopPropagation(); toggleRuleActive(${rule.id})">
                <div class="toggle-switch ${rule.is_active ? 'active' : ''}">
                    <div class="toggle-slider"></div>
                </div>
            </div>
            <div class="mb-2">
                <span class="priority-badge badge-${rule.priority}">${rule.priority}</span>
                ${rule.is_cis_specific ? '<span class="cis-badge">CIS</span>' : ''}
                ${rule.auto_learned ? '<span class="badge bg-info">Auto</span>' : ''}
            </div>
            <h6><i class="fas fa-${rule.category_icon}"></i> ${rule.title}</h6>
            <p class="small text-muted mb-2">${rule.description || ''}</p>
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">${rule.category_name}</small>
                <small class="text-muted">
                    <i class="fas fa-check-circle"></i> ${rule.usage_count} uses
                    ${rule.violation_count > 0 ? `<i class="fas fa-exclamation-triangle text-warning"></i> ${rule.violation_count}` : ''}
                </small>
            </div>
        </div>
    `).join('');

    updateCounts();
}

// Filter rules based on search and categories
function filterRules() {
    let filtered = rules;

    // Filter by selected categories
    if (selectedCategories.length > 0) {
        filtered = filtered.filter(r => selectedCategories.includes(r.category_id));
    }

    // Filter by search
    const search = document.getElementById('rule-search').value.toLowerCase();
    if (search) {
        filtered = filtered.filter(r =>
            r.title.toLowerCase().includes(search) ||
            (r.description && r.description.toLowerCase().includes(search)) ||
            (r.rule_text && r.rule_text.toLowerCase().includes(search))
        );
    }

    return filtered;
}

// Search rules
function searchRules() {
    renderRules();
}

// Toggle rule selection
function toggleRule(ruleId) {
    const index = selectedRules.indexOf(ruleId);
    if (index > -1) {
        selectedRules.splice(index, 1);
    } else {
        selectedRules.push(ruleId);
    }
    renderRules();
}

// Toggle rule active status
async function toggleRuleActive(ruleId) {
    const rule = rules.find(r => r.id === ruleId);
    if (!rule) return;

    // Update locally
    rule.is_active = !rule.is_active;

    // TODO: Save to backend
    renderRules();
}

// Render MCP Tools
function renderMCPTools() {
    const container = document.getElementById('mcp-tools-grid');
    container.innerHTML = mcpTools.map(tool => `
        <div class="mcp-tool-card ${selectedTools.includes(tool.name) ? 'selected' : ''}"
             onclick="toggleTool('${tool.name}')">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h5><i class="fas fa-${tool.icon}"></i> ${tool.name}</h5>
                    <p class="mb-2">${tool.description}</p>
                    <small><code>${tool.usage}</code></small>
                </div>
                <i class="fas fa-check-circle fa-2x" style="opacity: ${selectedTools.includes(tool.name) ? 1 : 0.2}"></i>
            </div>
            ${tool.modes ? `
                <div class="tool-modes">
                    ${tool.modes.map(mode => `<span class="mode-badge">${mode}</span>`).join('')}
                </div>
            ` : ''}
        </div>
    `).join('');
}

// Toggle tool selection
function toggleTool(toolName) {
    const index = selectedTools.indexOf(toolName);
    if (index > -1) {
        selectedTools.splice(index, 1);
    } else {
        selectedTools.push(toolName);
    }
    renderMCPTools();
}

// Generate prompt
async function generatePrompt() {
    if (selectedRules.length === 0 && selectedTools.length === 0) {
        showToast('Please select at least one rule or MCP tool', 'warning');
        return;
    }

    document.getElementById('loading-overlay').classList.add('active');

    try {
        // Build prompt from selected rules and tools
        const selectedRuleObjects = rules.filter(r => selectedRules.includes(r.id));
        const selectedToolObjects = mcpTools.filter(t => selectedTools.includes(t.name));

        // Generate comprehensive prompt
        generatedPrompt = buildPromptFromRules(selectedRuleObjects, selectedToolObjects);

        // Display in preview
        document.getElementById('preview-content').innerHTML = `<pre>${escapeHtml(generatedPrompt)}</pre>`;

        showToast('Prompt generated successfully!', 'success');

    } catch (error) {
        console.error('Error generating prompt:', error);
        showToast('Error generating prompt: ' + error.message, 'error');
    } finally {
        document.getElementById('loading-overlay').classList.remove('active');
    }
}

// Build prompt from rules and tools
function buildPromptFromRules(ruleList, toolList) {
    let prompt = `---
applyTo: '**'
priority: 80
---

# CIS Custom Coding Standards - Auto-Generated
**Generated:** ${new Date().toLocaleString()}
**Rules Applied:** ${ruleList.length}
**MCP Tools Integrated:** ${toolList.length}

---

## 🎯 YOUR MISSION

You are an elite developer working on the CIS (Central Information System) for The Vape Shed.
Follow these standards STRICTLY. They are based on real project patterns and lessons learned.

---

## 🔥 CRITICAL RULES (NEVER VIOLATE)

`;

    // Add critical rules
    const criticalRules = ruleList.filter(r => r.priority === 'critical');
    criticalRules.forEach((rule, index) => {
        prompt += `### ${index + 1}. ${rule.title}\n\n`;
        prompt += `${rule.rule_text}\n\n`;
        if (rule.example_good || rule.example_bad) {
            prompt += `**Examples:**\n\n`;
            if (rule.example_good) prompt += `✅ **Good:**\n\`\`\`\n${rule.example_good}\n\`\`\`\n\n`;
            if (rule.example_bad) prompt += `❌ **Bad:**\n\`\`\`\n${rule.example_bad}\n\`\`\`\n\n`;
        }
        prompt += `---\n\n`;
    });

    // Add high priority rules
    const highRules = ruleList.filter(r => r.priority === 'high');
    if (highRules.length > 0) {
        prompt += `## 🔶 HIGH PRIORITY RULES\n\n`;
        highRules.forEach(rule => {
            prompt += `### ${rule.title}\n${rule.rule_text}\n\n`;
        });
    }

    // Add MCP tools section
    if (toolList.length > 0) {
        prompt += `## 🛠️ MCP TOOLS YOU MUST USE\n\n`;
        prompt += `**Base URL:** https://gpt.ecigdis.co.nz/mcp/dispatcher.php\n\n`;
        prompt += `You have access to ${toolList.length} powerful MCP tools. USE THEM CONSTANTLY!\n\n`;

        toolList.forEach(tool => {
            prompt += `### ${tool.name} - ${tool.description}\n\n`;
            prompt += `**Usage:** \`${tool.usage}\`\n\n`;
            if (tool.modes) {
                prompt += `**Modes:** ${tool.modes.join(', ')}\n\n`;
            }
            if (tool.examples && tool.examples.length > 0) {
                prompt += `**Common uses:**\n`;
                tool.examples.forEach(ex => prompt += `- ${ex}\n`);
                prompt += `\n`;
            }
            prompt += `---\n\n`;
        });

        // Add specific CrawlerTool instructions if selected
        if (toolList.some(t => t.name === 'crawler')) {
            prompt += `### 🕷️ CrawlerTool Special Instructions\n\n`;
            prompt += `When user asks to test a website, ALWAYS use CrawlerTool with mode=full:\n\n`;
            prompt += `\`\`\`bash
curl -X POST https://gpt.ecigdis.co.nz/mcp/dispatcher.php \\
  -d tool=crawler \\
  -d mode=full \\
  -d url=https://staff.vapeshed.co.nz \\
  -d profile=cis_desktop
\`\`\`\n\n`;
            prompt += `This SINGLE CALL does:\n`;
            prompt += `- ✅ Crawl entire site\n`;
            prompt += `- ✅ Login with authentication\n`;
            prompt += `- ✅ Click all buttons/links\n`;
            prompt += `- ✅ Fill all forms\n`;
            prompt += `- ✅ Take screenshots\n`;
            prompt += `- ✅ Detect 404 errors\n`;
            prompt += `- ✅ Capture JS errors\n`;
            prompt += `- ✅ GPT Vision ready\n`;
            prompt += `- ✅ Performance metrics\n\n`;
            prompt += `**DO NOT** manually test websites. Use this tool!\n\n`;
            prompt += `---\n\n`;
        }
    }

    // Add medium/low rules summary
    const mediumRules = ruleList.filter(r => r.priority === 'medium' || r.priority === 'low');
    if (mediumRules.length > 0) {
        prompt += `## 📋 ADDITIONAL GUIDELINES\n\n`;
        mediumRules.forEach(rule => {
            prompt += `- **${rule.title}:** ${rule.description || rule.rule_text.substring(0, 100) + '...'}\n`;
        });
        prompt += `\n`;
    }

    // Add CIS-specific section if any CIS rules selected
    const cisRules = ruleList.filter(r => r.is_cis_specific);
    if (cisRules.length > 0) {
        prompt += `## 🏢 CIS-SPECIFIC REQUIREMENTS\n\n`;
        prompt += `These rules are MANDATORY for The Vape Shed CIS project:\n\n`;
        cisRules.forEach(rule => {
            prompt += `### ${rule.title}\n${rule.rule_text}\n\n`;
        });
    }

    prompt += `## ✅ SUCCESS CRITERIA\n\n`;
    prompt += `Your code is "done" when:\n`;
    prompt += `- ✅ All critical rules followed\n`;
    prompt += `- ✅ MCP tools used where appropriate\n`;
    prompt += `- ✅ Tests pass\n`;
    prompt += `- ✅ No security vulnerabilities\n`;
    prompt += `- ✅ Performance benchmarks met\n`;
    prompt += `- ✅ Documentation updated\n\n`;

    prompt += `---\n\n`;
    prompt += `**Remember:** These rules exist because they've solved real problems.
Follow them strictly, and you'll build production-ready code that integrates
seamlessly with the existing CIS system.\n\n`;

    prompt += `**Need help?** Use the MCP tools! They're your superpowers. 🚀`;

    return prompt;
}

// Update statistics
function updateStats() {
    document.getElementById('stat-rules-total').textContent = rules.length;
    document.getElementById('stat-rules-cis').textContent = rules.filter(r => r.is_cis_specific).length;
    document.getElementById('stat-auto-learned').textContent = rules.filter(r => r.auto_learned).length;
}

// Update rule counts
function updateCounts() {
    const filtered = filterRules();
    document.getElementById('count-critical').textContent = `${filtered.filter(r => r.priority === 'critical').length} Critical`;
    document.getElementById('count-high').textContent = `${filtered.filter(r => r.priority === 'high').length} High`;
    document.getElementById('count-medium').textContent = `${filtered.filter(r => r.priority === 'medium').length} Medium`;
}

// Load statistics
async function loadStats() {
    // Stats already loaded with rules
    updateStats();
}

// Copy prompt to clipboard
function copyPrompt() {
    if (!generatedPrompt) {
        showToast('Generate a prompt first!', 'warning');
        return;
    }

    navigator.clipboard.writeText(generatedPrompt).then(() => {
        showToast('Copied to clipboard!', 'success');
    });
}

// Save prompt as file
function savePrompt() {
    if (!generatedPrompt) {
        showToast('Generate a prompt first!', 'warning');
        return;
    }

    const blob = new Blob([generatedPrompt], { type: 'text/markdown' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'cis_coding_standards_' + Date.now() + '.instructions.md';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);

    showToast('Downloaded successfully!', 'success');
}

// Sync to VS Code
async function syncToVSCode() {
    if (!generatedPrompt) {
        showToast('Generate a prompt first!', 'warning');
        return;
    }

    // TODO: Implement actual VS Code sync via API
    showToast('VS Code sync coming soon! For now, copy and paste manually.', 'info');
    copyPrompt();
}

// Show toast notification
function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    const content = document.getElementById('toast-content');

    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };

    const colors = {
        success: '#28a745',
        error: '#dc3545',
        warning: '#ffc107',
        info: '#17a2b8'
    };

    content.innerHTML = `
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-${icons[type]}" style="color: ${colors[type]}; font-size: 24px;"></i>
            <span>${message}</span>
        </div>
    `;

    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3000);
}

// Escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
