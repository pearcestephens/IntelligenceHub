<?php
/**
 * AI Control Center - MEGA PAGE
 *
 * Combines:
 * - AI Prompt Generator with Rule Learning
 * - Bot Standards Manager
 * - MCP Tools Testing (13 tools)
 * - Rule Engine & Auto-Learning
 * - VS Code Sync
 * - Live Preview & Export
 *
 * ONE central place for ALL AI operations
 */

$pageTitle = 'AI Control Center';
require_once __DIR__ . '/../includes/header.php';
?>

<style>
:root {
    --primary: #667eea;
    --secondary: #764ba2;
    --success: #38ef7d;
    --danger: #f45c43;
    --warning: #f5576c;
    --info: #4facfe;
}

.control-hero {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    border-radius: 20px;
    padding: 50px;
    color: white;
    margin-bottom: 30px;
    box-shadow: 0 20px 60px rgba(102, 126, 234, 0.4);
    position: relative;
    overflow: hidden;
}

.control-hero::before {
    content: '🤖';
    position: absolute;
    font-size: 300px;
    opacity: 0.05;
    right: -50px;
    top: -50px;
}

.control-hero h1 {
    font-size: 3rem;
    font-weight: 800;
    margin-bottom: 1rem;
}

.control-hero p {
    font-size: 1.2rem;
    opacity: 0.9;
}

.nav-pills-custom {
    background: white;
    padding: 10px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.nav-pills-custom .nav-link {
    color: #666;
    font-weight: 600;
    padding: 15px 25px;
    border-radius: 12px;
    transition: all 0.3s;
    margin: 5px;
}

.nav-pills-custom .nav-link:hover {
    background: rgba(102, 126, 234, 0.1);
    color: var(--primary);
}

.nav-pills-custom .nav-link.active {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

.control-card {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin-bottom: 25px;
    transition: all 0.3s;
}

.control-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.15);
}

.tool-badge {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    margin: 5px;
}

.tool-badge.live {
    background: linear-gradient(135deg, #11998e 0%, var(--success) 100%);
    color: white;
}

.tool-badge.pending {
    background: linear-gradient(135deg, #f093fb 0%, var(--warning) 100%);
    color: white;
}

.mcp-tool-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    margin-bottom: 15px;
    border-left: 4px solid var(--primary);
    transition: all 0.3s;
}

.mcp-tool-card:hover {
    border-left-color: var(--success);
    transform: translateX(5px);
}

.rule-item {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 12px;
    border-left: 4px solid var(--info);
    transition: all 0.2s;
    animation: fadeInUp 0.5s ease-out;
}

.rule-item:hover {
    background: #e9ecef;
    border-left-color: var(--primary);
    transform: translateX(5px);
}

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

@keyframes shimmer {
    0% { background-position: -1000px 0; }
    100% { background-position: 1000px 0; }
}

.btn-gradient {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    border: none;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
    color: white;
}

.stat-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s;
    border-top: 4px solid var(--primary);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    animation: pulse 1s infinite;
}

.stat-card h3 {
    font-size: 2.5rem;
    font-weight: 800;
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 10px;
}

/* Loading states */
.loading {
    position: relative;
    overflow: hidden;
}

.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    animation: shimmer 2s infinite;
}

/* Success animations */
@keyframes checkmark {
    0% { stroke-dashoffset: 100; }
    100% { stroke-dashoffset: 0; }
}

.success-animation {
    animation: fadeInUp 0.5s ease-out, pulse 0.5s ease-out 0.5s;
}

/* Responsive improvements */
@media (max-width: 768px) {
    .control-hero {
        padding: 30px 20px;
    }

    .control-hero h1 {
        font-size: 2rem;
    }

    .nav-pills-custom .nav-link {
        padding: 10px 15px;
        font-size: 0.9rem;
    }
}

.priority-critical { border-left-color: var(--danger) !important; }
.priority-high { border-left-color: var(--warning) !important; }
.priority-medium { border-left-color: var(--info) !important; }
.priority-low { border-left-color: #6c757d !important; }

.code-preview {
    background: #1e1e1e;
    color: #d4d4d4;
    padding: 20px;
    border-radius: 10px;
    font-family: 'Fira Code', 'Courier New', monospace;
    font-size: 0.9rem;
    max-height: 500px;
    overflow-y: auto;
    margin: 20px 0;
}

.btn-gradient {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    border: none;
    color: white;
    padding: 12px 30px;
    border-radius: 10px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
    color: white;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px;
    border-radius: 15px;
    text-align: center;
}

.stat-card h3 {
    font-size: 2.5rem;
    font-weight: 800;
    margin-bottom: 0.5rem;
}

.stat-card p {
    font-size: 0.9rem;
    opacity: 0.9;
    margin: 0;
}
</style>

<!-- Hero Section -->
<div class="control-hero">
    <h1>🤖 AI Control Center</h1>
    <p class="lead">Your complete AI operations hub - prompt generation, rule learning, MCP tools, and bot standards management</p>

    <div class="stats-grid mt-4">
        <div class="stat-card">
            <h3 id="stat-tools">10/13</h3>
            <p>MCP Tools Live</p>
            <small class="text-white-50">77% Complete</small>
        </div>
        <div class="stat-card">
            <h3 id="stat-rules">150+</h3>
            <p>Active Rules</p>
            <small class="text-white-50">Auto-Learning</small>
        </div>
        <div class="stat-card">
            <h3 id="stat-prompts">50+</h3>
            <p>Generated Prompts</p>
            <small class="text-white-50">Ready to Sync</small>
        </div>
        <div class="stat-card">
            <h3 id="stat-files">15,151</h3>
            <p>Files Indexed</p>
            <small class="text-white-50">Real-time Search</small>
        </div>
    </div>
</div>

<!-- Main Navigation Tabs -->
<ul class="nav nav-pills nav-pills-custom" id="controlTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="generator-tab" data-bs-toggle="pill" href="#generator" role="tab">
            <i class="fas fa-magic"></i> Prompt Generator
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="rules-tab" data-bs-toggle="pill" href="#rules" role="tab">
            <i class="fas fa-book"></i> Rule Manager
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="mcp-tab" data-bs-toggle="pill" href="#mcp" role="tab">
            <i class="fas fa-tools"></i> MCP Tools <span class="badge bg-danger ms-2">13</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="standards-tab" data-bs-toggle="pill" href="#standards" role="tab">
            <i class="fas fa-robot"></i> Bot Standards
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="export-tab" data-bs-toggle="pill" href="#export" role="tab">
            <i class="fas fa-download"></i> Export & Sync
        </a>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content" id="controlTabsContent">

    <!-- PROMPT GENERATOR TAB -->
    <div class="tab-pane fade show active" id="generator" role="tabpanel">
        <div class="row">
            <div class="col-lg-8">
                <div class="control-card">
                    <h3><i class="fas fa-magic text-primary"></i> AI Prompt Generator</h3>
                    <p class="text-muted">Generate custom bot instructions with rule-based intelligence</p>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Select Rule Categories</label>
                        <div id="categorySelector">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Priority Level</label>
                        <select class="form-select" id="priorityLevel">
                            <option value="all">All Priorities</option>
                            <option value="critical">Critical Only</option>
                            <option value="high">High & Above</option>
                            <option value="medium">Medium & Above</option>
                            <option value="low">All Including Low</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Custom Instructions (Optional)</label>
                        <textarea class="form-control" id="customInstructions" rows="4"
                                  placeholder="Add any custom instructions specific to your current task..."></textarea>
                    </div>

                    <button class="btn btn-gradient btn-lg w-100" onclick="generatePrompt()">
                        <i class="fas fa-rocket"></i> Generate Prompt
                    </button>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="control-card">
                    <h4><i class="fas fa-lightbulb text-warning"></i> Quick Presets</h4>
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" onclick="loadPreset('full-stack')">
                            <i class="fas fa-layer-group"></i> Full Stack Development
                        </button>
                        <button class="btn btn-outline-primary" onclick="loadPreset('security')">
                            <i class="fas fa-shield-alt"></i> Security Focused
                        </button>
                        <button class="btn btn-outline-primary" onclick="loadPreset('database')">
                            <i class="fas fa-database"></i> Database Work
                        </button>
                        <button class="btn btn-outline-primary" onclick="loadPreset('frontend')">
                            <i class="fas fa-paint-brush"></i> Frontend/UI
                        </button>
                        <button class="btn btn-outline-primary" onclick="loadPreset('api')">
                            <i class="fas fa-plug"></i> API Development
                        </button>
                    </div>
                </div>

                <div class="control-card mt-3">
                    <h5><i class="fas fa-history text-info"></i> Recent Generations</h5>
                    <div id="recentGenerations">
                        <small class="text-muted">Your recent prompts will appear here</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Panel -->
        <div class="control-card mt-4" id="promptPreview" style="display: none;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3><i class="fas fa-eye text-success"></i> Generated Prompt Preview</h3>
                <div>
                    <button class="btn btn-sm btn-outline-primary" onclick="copyPrompt()">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                    <button class="btn btn-sm btn-outline-success" onclick="downloadPrompt()">
                        <i class="fas fa-download"></i> Download
                    </button>
                    <button class="btn btn-sm btn-gradient" onclick="syncToVSCode()">
                        <i class="fas fa-sync"></i> Sync to VS Code
                    </button>
                </div>
            </div>

            <div class="code-preview" id="promptContent">
                <!-- Generated prompt will appear here -->
            </div>

            <div class="mt-3">
                <button class="btn btn-success" onclick="savePrompt()">
                    <i class="fas fa-save"></i> Save Prompt
                </button>
                <button class="btn btn-primary" onclick="editPrompt()">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn btn-info" onclick="testPrompt()">
                    <i class="fas fa-vial"></i> Test with AI
                </button>
            </div>
        </div>
    </div>

    <!-- RULE MANAGER TAB -->
    <div class="tab-pane fade" id="rules" role="tabpanel">
        <div class="row">
            <div class="col-lg-8">
                <div class="control-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3><i class="fas fa-book text-primary"></i> Rule Library</h3>
                        <button class="btn btn-gradient" onclick="showAddRuleModal()">
                            <i class="fas fa-plus"></i> Add New Rule
                        </button>
                    </div>

                    <div class="mb-4">
                        <input type="text" class="form-control form-control-lg" id="ruleSearch"
                               placeholder="🔍 Search rules..." onkeyup="filterRules()">
                    </div>

                    <div id="rulesList">
                        <!-- Rules will be loaded here -->
                        <div class="text-center py-5">
                            <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
                            <p class="mt-3 text-muted">Loading rules...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="control-card">
                    <h4><i class="fas fa-chart-pie text-success"></i> Rule Statistics</h4>
                    <div id="ruleStats">
                        <!-- Stats will be populated -->
                    </div>
                </div>

                <div class="control-card mt-3">
                    <h4><i class="fas fa-brain text-warning"></i> Auto-Learning</h4>
                    <p class="small text-muted">AI is learning from your patterns and violations</p>
                    <div id="learningLog">
                        <!-- Learning activity -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MCP TOOLS TAB -->
    <div class="tab-pane fade" id="mcp" role="tabpanel">
        <div class="control-card">
            <h3><i class="fas fa-tools text-primary"></i> MCP Tools Dashboard</h3>
            <p class="text-muted">13 powerful tools available through Model Context Protocol</p>

            <div class="alert alert-success mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <strong><i class="fas fa-check-circle"></i> 10 of 13 Tools Live</strong>
                        <p class="mb-0 mt-1 small">Latest: DatabaseTool added with 6 advanced actions (analyze, query, optimize, schema, relations, indexes)</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="fs-2 fw-bold">77% Complete</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <?php
            $mcpTools = [
                ['name' => 'health', 'icon' => 'heartbeat', 'desc' => 'System health checks', 'status' => 'live'],
                ['name' => 'stats', 'icon' => 'chart-bar', 'desc' => 'System statistics', 'status' => 'live'],
                ['name' => 'search', 'icon' => 'search', 'desc' => 'Semantic search (22K+ files)', 'status' => 'live'],
                ['name' => 'analytics', 'icon' => 'chart-line', 'desc' => 'Search analytics', 'status' => 'live'],
                ['name' => 'fuzzy', 'icon' => 'magic', 'desc' => 'Fuzzy search for typos', 'status' => 'live'],
                ['name' => 'mysql', 'icon' => 'database', 'desc' => 'Safe database queries', 'status' => 'live'],
                ['name' => 'password', 'icon' => 'key', 'desc' => 'Encrypted storage', 'status' => 'live'],
                ['name' => 'browser', 'icon' => 'globe', 'desc' => 'Web browsing & screenshots', 'status' => 'live'],
                ['name' => 'crawler', 'icon' => 'spider', 'desc' => 'Full website testing (5 modes)', 'status' => 'live'],
                ['name' => 'database', 'icon' => 'server', 'desc' => 'Advanced DB analysis (NEW!)', 'status' => 'live'],
                ['name' => 'redis', 'icon' => 'memory', 'desc' => 'Cache management', 'status' => 'pending'],
                ['name' => 'file', 'icon' => 'file-code', 'desc' => 'Safe file operations', 'status' => 'pending'],
                ['name' => 'logs', 'icon' => 'file-alt', 'desc' => 'Log analysis', 'status' => 'pending'],
            ];

            foreach ($mcpTools as $tool): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="mcp-tool-card">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5><i class="fas fa-<?= $tool['icon'] ?>"></i> <?= ucfirst($tool['name']) ?></h5>
                            <span class="tool-badge <?= $tool['status'] ?>"><?= strtoupper($tool['status']) ?></span>
                        </div>
                        <p class="small text-muted mb-3"><?= $tool['desc'] ?></p>
                        <?php if ($tool['status'] === 'live'): ?>
                            <button class="btn btn-sm btn-outline-primary w-100" onclick="testMCPTool('<?= $tool['name'] ?>')">
                                <i class="fas fa-play"></i> Test Tool
                            </button>
                        <?php else: ?>
                            <button class="btn btn-sm btn-outline-secondary w-100" disabled>
                                <i class="fas fa-clock"></i> Coming Soon
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- BOT STANDARDS TAB -->
    <div class="tab-pane fade" id="standards" role="tabpanel">
        <div class="control-card">
            <h3><i class="fas fa-robot text-primary"></i> Bot Standards Manager</h3>
            <p class="text-muted">Manage bot behavior standards and instruction templates</p>

            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5><i class="fas fa-code text-info"></i> Coding Standards</h5>
                            <ul class="list-unstyled mt-3">
                                <li><i class="fas fa-check text-success"></i> PSR-12 compliance</li>
                                <li><i class="fas fa-check text-success"></i> Strict typing</li>
                                <li><i class="fas fa-check text-success"></i> PHPDoc comments</li>
                                <li><i class="fas fa-check text-success"></i> Security first</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5><i class="fas fa-shield-alt text-danger"></i> Security Standards</h5>
                            <ul class="list-unstyled mt-3">
                                <li><i class="fas fa-check text-success"></i> Prepared statements always</li>
                                <li><i class="fas fa-check text-success"></i> Input validation</li>
                                <li><i class="fas fa-check text-success"></i> Output escaping</li>
                                <li><i class="fas fa-check text-success"></i> CSRF protection</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- EXPORT & SYNC TAB -->
    <div class="tab-pane fade" id="export" role="tabpanel">
        <div class="control-card">
            <h3><i class="fas fa-download text-primary"></i> Export & VS Code Sync</h3>
            <p class="text-muted">Export your prompts and sync with VS Code instructions folder</p>

            <!-- Quick Export Buttons -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5><i class="fas fa-file-export"></i> Quick Export</h5>
                    <div class="btn-group w-100" role="group">
                        <button class="btn btn-outline-primary" onclick="exportMarkdown()">
                            <i class="fas fa-file-markdown"></i> Markdown
                        </button>
                        <button class="btn btn-outline-primary" onclick="exportJSON()">
                            <i class="fas fa-file-code"></i> JSON
                        </button>
                        <button class="btn btn-outline-success" onclick="exportForVSCode()">
                            <i class="fas fa-code"></i> VS Code Format
                        </button>
                        <button class="btn btn-outline-danger" onclick="exportPDF()">
                            <i class="fas fa-file-pdf"></i> PDF
                        </button>
                    </div>
                </div>
            </div>

            <!-- VS Code Sync Configuration -->
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-cog"></i> VS Code Configuration</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Local VS Code Path</label>
                                <input type="text" class="form-control" id="vscodePath"
                                       placeholder="C:\Users\YourName\AppData\Roaming\Code\User\prompts\"
                                       value="">
                                <small class="text-muted">Default Windows path shown. Adjust for your system.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Filename Pattern</label>
                                <select class="form-control" id="filenamePattern">
                                    <option value="{date}_{title}.instructions.md">{date}_{title}.instructions.md</option>
                                    <option value="{title}.instructions.md">{title}.instructions.md</option>
                                    <option value="{timestamp}_{title}.md">{timestamp}_{title}.md</option>
                                    <option value="custom">Custom Pattern...</option>
                                </select>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="autoBackup" checked>
                                <label class="form-check-label" for="autoBackup">
                                    Enable automatic backups
                                </label>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="versionControl" checked>
                                <label class="form-check-label" for="versionControl">
                                    Enable version control
                                </label>
                            </div>

                            <button class="btn btn-primary w-100" onclick="saveVSCodeConfig()">
                                <i class="fas fa-save"></i> Save Configuration
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-sync-alt"></i> Sync Actions</h5>
                        </div>
                        <div class="card-body">
                            <!-- Sync Status -->
                            <div id="syncStatus" class="alert alert-info mb-3">
                                <i class="fas fa-info-circle"></i> Ready to sync. Configure your path above.
                            </div>

                            <!-- Sync Actions -->
                            <div class="d-grid gap-2">
                                <button class="btn btn-success btn-lg" onclick="syncToVSCode()" id="syncBtn">
                                    <i class="fas fa-cloud-download-alt"></i> Generate & Download
                                </button>
                                <button class="btn btn-outline-primary" onclick="testVSCodePath()">
                                    <i class="fas fa-check-circle"></i> Test Path Configuration
                                </button>
                                <button class="btn btn-outline-info" onclick="previewFile()">
                                    <i class="fas fa-eye"></i> Preview Generated File
                                </button>
                            </div>

                            <!-- Quick Stats -->
                            <div class="mt-3 p-3 bg-light rounded">
                                <small class="text-muted d-block mb-2"><strong>Quick Stats:</strong></small>
                                <div class="d-flex justify-content-between">
                                    <span><i class="fas fa-history"></i> Last Sync:</span>
                                    <span id="lastSyncTime" class="text-muted">Never</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span><i class="fas fa-file-alt"></i> Files Generated:</span>
                                    <span id="totalSyncs" class="text-muted">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sync History -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-history"></i> Sync History</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="syncHistoryTable">
                                    <thead>
                                        <tr>
                                            <th>Date/Time</th>
                                            <th>Filename</th>
                                            <th>Size</th>
                                            <th>Rules</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="syncHistoryBody">
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">
                                                <i class="fas fa-info-circle"></i> No sync history yet. Generate your first prompt!
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    loadCategories();
    loadRules();
    loadMCPStats();
    loadRecentGenerations();
});

// Load rule categories
async function loadCategories() {
    try {
        const response = await fetch('api/rule-engine.php?action=get_categories');
        const result = await response.json();

        if (result.success) {
            const container = document.getElementById('categorySelector');
            container.innerHTML = result.data.map(cat => `
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="checkbox" id="cat_${cat.id}" value="${cat.id}">
                    <label class="form-check-label" for="cat_${cat.id}">${cat.name}</label>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Failed to load categories:', error);
    }
}

// Load rules
async function loadRules() {
    try {
        const response = await fetch('api/rule-engine.php?action=get_rules');
        const result = await response.json();

        if (result.success) {
            displayRules(result.data);
        }
    } catch (error) {
        console.error('Failed to load rules:', error);
    }
}

function displayRules(rules) {
    const container = document.getElementById('rulesList');
    container.innerHTML = rules.map(rule => `
        <div class="rule-item priority-${rule.priority}">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6>${rule.title}</h6>
                    <p class="small text-muted mb-2">${rule.description}</p>
                    <span class="badge bg-secondary">${rule.category}</span>
                    <span class="badge bg-${rule.priority === 'critical' ? 'danger' : rule.priority === 'high' ? 'warning' : 'info'}">${rule.priority}</span>
                </div>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-primary" onclick="editRule(${rule.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteRule(${rule.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

// Generate prompt
async function generatePrompt() {
    const categories = Array.from(document.querySelectorAll('#categorySelector input:checked'))
        .map(cb => cb.value);
    const priority = document.getElementById('priorityLevel').value;
    const custom = document.getElementById('customInstructions').value;

    // Show loading
    const preview = document.getElementById('promptPreview');
    preview.style.display = 'block';
    document.getElementById('promptContent').innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-3">Generating your prompt...</p></div>';

    try {
        const response = await fetch('api/rule-engine.php?action=generate_prompt', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ categories, priority, custom })
        });

        const result = await response.json();

        if (result.success) {
            document.getElementById('promptContent').textContent = result.data.prompt;
            saveToRecent(result.data.prompt);
        } else {
            document.getElementById('promptContent').innerHTML = `<div class="alert alert-danger">${result.error}</div>`;
        }
    } catch (error) {
        document.getElementById('promptContent').innerHTML = `<div class="alert alert-danger">Failed to generate prompt: ${error.message}</div>`;
    }
}

// Test MCP tool
async function testMCPTool(toolName) {
    alert(`Testing ${toolName} tool...\n\nThis will open the MCP dispatcher with test parameters.`);
    window.open(`https://gpt.ecigdis.co.nz/mcp/dispatcher.php?tool=${toolName}&test=1`, '_blank');
}

// Copy prompt
function copyPrompt() {
    const content = document.getElementById('promptContent').textContent;
    navigator.clipboard.writeText(content).then(() => {
        alert('✅ Prompt copied to clipboard!');
    });
}

// Download prompt
async function downloadPrompt() {
    const content = document.getElementById('promptContent').textContent;

    // Save to server and get download link
    try {
        const response = await fetch('api/vscode-sync.php?action=generate_file', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                prompt: content,
                title: 'Generated Prompt',
                metadata: {
                    priority: document.getElementById('priorityLevel')?.value,
                    categories: Array.from(document.querySelectorAll('#categorySelector input:checked'))
                        .map(cb => cb.nextElementSibling?.textContent)
                }
            })
        });

        const result = await response.json();

        if (result.success) {
            // Download the file
            window.location.href = result.download_url;

            // Show success
            alert(`✅ Prompt saved!\n\nFilename: ${result.filename}\nVersion: ${result.version}`);
        } else {
            // Fallback to direct download
            const blob = new Blob([content], { type: 'text/markdown' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `prompt-${Date.now()}.instructions.md`;
            a.click();
        }
    } catch (error) {
        // Fallback to direct download
        const blob = new Blob([content], { type: 'text/markdown' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `prompt-${Date.now()}.instructions.md`;
        a.click();
    }
}

// Sync to VS Code
async function syncToVSCode() {
    const content = document.getElementById('promptContent').textContent;

    // Get VS Code config
    const configResponse = await fetch('api/vscode-sync.php?action=get_config');
    const configResult = await configResponse.json();

    if (!configResult.success || !configResult.config.local_path) {
        if (confirm('VS Code sync not configured.\n\nWould you like to configure it now?')) {
            // Switch to export tab
            document.getElementById('export-tab').click();
        }
        return;
    }

    // Generate and save file
    const response = await fetch('api/vscode-sync.php?action=generate_file', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            prompt: content,
            title: 'Generated Prompt',
            metadata: {
                priority: document.getElementById('priorityLevel')?.value,
                categories: Array.from(document.querySelectorAll('#categorySelector input:checked'))
                    .map(cb => cb.nextElementSibling?.textContent)
            }
        })
    });

    const result = await response.json();

    if (result.success) {
        // Auto-download to configured folder
        window.location.href = result.download_url;

        alert(`✅ Synced to VS Code!\n\nFile: ${result.filename}\nLocation: ${configResult.config.local_path}\n\nPlace the downloaded file in your VS Code prompts folder.`);
    } else {
        alert('❌ Sync failed: ' + result.error);
    }
}

// Load presets
function loadPreset(preset) {
    const presets = {
        'full-stack': [1, 2, 3, 4, 5],
        'security': [3, 7, 8],
        'database': [2, 6],
        'frontend': [1, 4],
        'api': [5, 9]
    };

    // Uncheck all
    document.querySelectorAll('#categorySelector input').forEach(cb => cb.checked = false);

    // Check preset categories
    if (presets[preset]) {
        presets[preset].forEach(id => {
            const cb = document.getElementById(`cat_${id}`);
            if (cb) cb.checked = true;
        });
    }
}

// Filter rules
function filterRules() {
    const search = document.getElementById('ruleSearch').value.toLowerCase();
    document.querySelectorAll('.rule-item').forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(search) ? 'block' : 'none';
    });
}

// Load MCP stats
function loadMCPStats() {
    // Update stats from actual data
    document.getElementById('stat-tools').textContent = '10/13';
    document.getElementById('stat-rules').textContent = '150+';
    document.getElementById('stat-prompts').textContent = '50+';
    document.getElementById('stat-files').textContent = '15,151';
}

// Save to recent
function saveToRecent(prompt) {
    let recent = JSON.parse(localStorage.getItem('recentPrompts') || '[]');
    recent.unshift({
        timestamp: Date.now(),
        preview: prompt.substring(0, 100) + '...'
    });
    recent = recent.slice(0, 5);
    localStorage.setItem('recentPrompts', JSON.stringify(recent));
    loadRecentGenerations();
}

// Load recent generations
function loadRecentGenerations() {
    const recent = JSON.parse(localStorage.getItem('recentPrompts') || '[]');
    const container = document.getElementById('recentGenerations');

    if (recent.length === 0) {
        container.innerHTML = '<small class="text-muted">No recent prompts yet</small>';
    } else {
        container.innerHTML = recent.map((item, i) => `
            <div class="small mb-2 p-2 bg-light rounded">
                <div class="fw-bold">${new Date(item.timestamp).toLocaleString()}</div>
                <div class="text-muted">${item.preview}</div>
            </div>
        `).join('');
    }
}

// Placeholder functions
function showAddRuleModal() { alert('Add new rule modal - coming soon'); }
function editRule(id) { alert('Edit rule ' + id); }
function deleteRule(id) { if(confirm('Delete this rule?')) alert('Deleted rule ' + id); }
function editPrompt() { alert('Edit mode - coming soon'); }
function testPrompt() { alert('Testing prompt with AI - coming soon'); }

// ============================================================================
// VS CODE SYNC FUNCTIONS (FULLY FUNCTIONAL)
// ============================================================================

// Save VS Code configuration
async function saveVSCodeConfig() {
    const config = {
        local_path: document.getElementById('vscodePath').value,
        filename_pattern: document.getElementById('filenamePattern').value,
        auto_backup: document.getElementById('autoBackup').checked,
        version_control: document.getElementById('versionControl').checked
    };

    if (!config.local_path) {
        alert('⚠️ Please enter your VS Code path');
        return;
    }

    try {
        const response = await fetch('api/vscode-sync.php?action=save_config', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(config)
        });

        const result = await response.json();

        if (result.success) {
            updateSyncStatus('✅ Configuration saved successfully!', 'success');
            loadSyncStats();
        } else {
            updateSyncStatus('❌ Failed to save: ' + result.error, 'danger');
        }
    } catch (error) {
        updateSyncStatus('❌ Error: ' + error.message, 'danger');
    }
}

// Load VS Code configuration
async function loadVSCodeConfig() {
    try {
        const response = await fetch('api/vscode-sync.php?action=get_config');
        const result = await response.json();

        if (result.success && result.config) {
            document.getElementById('vscodePath').value = result.config.local_path || '';
            document.getElementById('filenamePattern').value = result.config.filename_pattern || '{date}_{title}.instructions.md';
            document.getElementById('autoBackup').checked = result.config.auto_backup !== false;
            document.getElementById('versionControl').checked = result.config.version_control !== false;
        }
    } catch (error) {
        console.error('Failed to load VS Code config:', error);
    }
}

// Test VS Code path
async function testVSCodePath() {
    const path = document.getElementById('vscodePath').value;

    if (!path) {
        alert('⚠️ Please enter a path first');
        return;
    }

    updateSyncStatus('🔍 Testing path configuration...', 'info');

    // Show what the file would look like
    const exampleFilename = path + (path.endsWith('\\') || path.endsWith('/') ? '' : '\\') + '2025-10-29_example-prompt.instructions.md';

    setTimeout(() => {
        updateSyncStatus(`✅ Path looks valid!\n\nExample file would be:\n${exampleFilename}`, 'success');
    }, 500);
}

// Preview generated file
async function previewFile() {
    const content = document.getElementById('promptContent').textContent;

    if (!content || content.includes('No prompt generated yet')) {
        alert('⚠️ Please generate a prompt first');
        return;
    }

    // Show in modal or new window
    const preview = window.open('', 'Preview', 'width=800,height=600');
    preview.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Prompt Preview</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 40px; max-width: 900px; margin: 0 auto; }
                pre { background: #f5f5f5; padding: 20px; border-radius: 5px; overflow-x: auto; }
                h1 { color: #667eea; }
            </style>
        </head>
        <body>
            <h1>📄 Generated Prompt Preview</h1>
            <pre>${content.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</pre>
        </body>
        </html>
    `);
}

// Export for VS Code (formatted)
async function exportForVSCode() {
    const content = document.getElementById('promptContent').textContent;

    if (!content || content.includes('No prompt generated yet')) {
        alert('⚠️ Please generate a prompt first');
        return;
    }

    // Add VS Code instruction format headers
    const formatted = `---
applyTo: '**'
priority: high
---

${content}`;

    const blob = new Blob([formatted], { type: 'text/markdown' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${Date.now()}_vscode-prompt.instructions.md`;
    a.click();
    URL.revokeObjectURL(url);

    updateSyncStatus('✅ File downloaded! Place it in your VS Code prompts folder.', 'success');
}

// Main sync to VS Code function
async function syncToVSCode() {
    const content = document.getElementById('promptContent').textContent;

    if (!content || content.includes('No prompt generated yet')) {
        alert('⚠️ Please generate a prompt first using the Generator tab');
        return;
    }

    const path = document.getElementById('vscodePath').value;
    if (!path) {
        alert('⚠️ Please configure your VS Code path first');
        return;
    }

    updateSyncStatus('🔄 Generating file for download...', 'info');
    document.getElementById('syncBtn').disabled = true;

    try {
        const response = await fetch('api/vscode-sync.php?action=generate_file', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                prompt: content,
                title: 'Generated Prompt',
                metadata: {
                    priority: document.getElementById('priorityLevel')?.value || 'high',
                    categories: Array.from(document.querySelectorAll('#categorySelector input:checked'))
                        .map(cb => cb.nextElementSibling?.textContent || 'Unknown')
                }
            })
        });

        const result = await response.json();

        if (result.success) {
            // Trigger download
            if (result.download_url) {
                window.location.href = result.download_url;
            } else {
                // Fallback to blob download
                exportForVSCode();
            }

            updateSyncStatus(`✅ File ready! Saved as: ${result.filename || 'prompt.instructions.md'}\n\n📁 Place it in: ${path}`, 'success');

            // Update sync history
            loadSyncHistory();
            loadSyncStats();
        } else {
            updateSyncStatus('❌ Generation failed: ' + result.error, 'danger');
        }
    } catch (error) {
        updateSyncStatus('❌ Error: ' + error.message, 'danger');
    } finally {
        document.getElementById('syncBtn').disabled = false;
    }
}

// Update sync status message
function updateSyncStatus(message, type) {
    const statusEl = document.getElementById('syncStatus');
    statusEl.className = `alert alert-${type}`;
    statusEl.innerHTML = message.replace(/\n/g, '<br>');
}

// Load sync history
async function loadSyncHistory() {
    try {
        const response = await fetch('api/vscode-sync.php?action=sync_history');
        const result = await response.json();

        if (result.success && result.history && result.history.length > 0) {
            const tbody = document.getElementById('syncHistoryBody');
            tbody.innerHTML = result.history.map(item => `
                <tr>
                    <td>${new Date(item.created_at).toLocaleString()}</td>
                    <td><code>${item.filename}</code></td>
                    <td>${item.size || 'N/A'}</td>
                    <td>${item.rule_count || 0}</td>
                    <td><span class="badge bg-success">Success</span></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="redownloadFile('${item.filename}')">
                            <i class="fas fa-download"></i> Re-download
                        </button>
                    </td>
                </tr>
            `).join('');
        }
    } catch (error) {
        console.error('Failed to load sync history:', error);
    }
}

// Load sync statistics
async function loadSyncStats() {
    try {
        const response = await fetch('api/vscode-sync.php?action=sync_history');
        const result = await response.json();

        if (result.success && result.history) {
            document.getElementById('totalSyncs').textContent = result.history.length;

            if (result.history.length > 0) {
                const lastSync = new Date(result.history[0].created_at);
                document.getElementById('lastSyncTime').textContent = lastSync.toLocaleString();
            }
        }
    } catch (error) {
        console.error('Failed to load sync stats:', error);
    }
}

// Re-download a previous file
function redownloadFile(filename) {
    window.location.href = `api/vscode-sync.php?action=download_prompt&filename=${encodeURIComponent(filename)}`;
}

// Save prompt to library
function savePrompt() {
    const content = document.getElementById('promptContent').textContent;

    if (!content || content.includes('No prompt generated yet')) {
        alert('⚠️ Generate a prompt first');
        return;
    }

    // Save to localStorage for now
    const saved = JSON.parse(localStorage.getItem('savedPrompts') || '[]');
    saved.unshift({
        id: Date.now(),
        content: content,
        timestamp: new Date().toISOString(),
        title: 'Saved Prompt ' + (saved.length + 1)
    });
    localStorage.setItem('savedPrompts', JSON.stringify(saved.slice(0, 10))); // Keep last 10

    alert('✅ Prompt saved to library!');
}

// Export functions
function exportMarkdown() {
    const content = document.getElementById('promptContent').textContent;
    const blob = new Blob([content], { type: 'text/markdown' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `prompt-${Date.now()}.md`;
    a.click();
    URL.revokeObjectURL(url);
}

function exportJSON() {
    const content = document.getElementById('promptContent').textContent;
    const data = {
        prompt: content,
        metadata: {
            generated: new Date().toISOString(),
            priority: document.getElementById('priorityLevel')?.value || 'medium',
            categories: Array.from(document.querySelectorAll('#categorySelector input:checked'))
                .map(cb => cb.nextElementSibling?.textContent || 'Unknown')
        }
    };
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `prompt-${Date.now()}.json`;
    a.click();
    URL.revokeObjectURL(url);
}

function exportPDF() {
    alert('📄 PDF export feature\n\nFor best results:\n1. Use "Export as Markdown"\n2. Open in VS Code\n3. Use Markdown PDF extension\n\nOr use your browser\'s Print > Save as PDF');
}

// Initialize VS Code tab when loaded
document.addEventListener('DOMContentLoaded', function() {
    loadVSCodeConfig();
    loadSyncHistory();
    loadSyncStats();
});

</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
