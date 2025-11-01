<?php
/**
 * AI Prompt Generator (LEGACY)
 *
 * ⚠️ NOTICE: This page has been superseded by the AI Control Center
 * Please use: ?page=ai-control-center
 *
 * This legacy version is kept for compatibility but the new unified
 * AI Control Center provides all functionality plus more features.
 */

// Optional: Auto-redirect to new page (uncomment to enable)
// header('Location: ?page=ai-control-center');
// exit;

$pageTitle = 'AI Prompt Generator (Legacy)';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Upgrade Notice Banner -->
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <h5><i class="fas fa-info-circle"></i> New Version Available!</h5>
    <p class="mb-2">This page has been integrated into the <strong>AI Control Center</strong> with enhanced features:</p>
    <ul class="mb-3">
        <li>Unified interface for prompts, rules, MCP tools & bot standards</li>
        <li>Real-time pattern analysis & statistics</li>
        <li>Enhanced rule learning engine</li>
        <li>VS Code sync functionality</li>
    </ul>
    <a href="?page=ai-control-center" class="btn btn-primary">
        <i class="fas fa-rocket"></i> Go to AI Control Center
    </a>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>

<style>
.prompt-generator {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 30px;
    color: white;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
}

.prompt-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.prompt-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 25px rgba(0,0,0,0.15);
}

.preset-btn {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    padding: 15px 25px;
    border-radius: 8px;
    margin: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 600;
}

.preset-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.preset-btn.active {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.ai-response {
    background: #f8f9fa;
    border-left: 4px solid #667eea;
    padding: 20px;
    border-radius: 8px;
    margin-top: 20px;
    font-family: 'Monaco', 'Courier New', monospace;
    white-space: pre-wrap;
    max-height: 600px;
    overflow-y: auto;
}

.category-tag {
    display: inline-block;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    margin: 3px;
    font-size: 12px;
    font-weight: 600;
}

.priority-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 11px;
    font-weight: bold;
    margin-left: 10px;
}

.priority-critical { background: #ff4757; color: white; }
.priority-high { background: #ffa502; color: white; }
.priority-medium { background: #ffc107; color: #333; }
.priority-low { background: #28a745; color: white; }

.loading-spinner {
    display: none;
    text-align: center;
    padding: 30px;
}

.loading-spinner.active {
    display: block;
}

.spinner {
    border: 4px solid rgba(0,0,0,0.1);
    border-left-color: #667eea;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin: 0 auto;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.generated-prompt {
    background: #1e1e1e;
    color: #d4d4d4;
    padding: 25px;
    border-radius: 12px;
    margin-top: 20px;
    font-family: 'Monaco', 'Courier New', monospace;
    font-size: 13px;
    line-height: 1.6;
    max-height: 500px;
    overflow-y: auto;
}

.action-buttons {
    margin-top: 20px;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-action {
    padding: 12px 24px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-copy {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-save {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
}

.btn-vscode {
    background: linear-gradient(135deg, #007acc 0%, #0098ff 100%);
    color: white;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.2);
}

.template-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.template-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
}

.template-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
}

.template-card i {
    font-size: 32px;
    margin-bottom: 10px;
}
</style>

<div class="container-fluid py-4">
    <div class="prompt-generator">
        <h1><i class="fas fa-magic"></i> AI Prompt Generator</h1>
        <p>Use AI to dynamically generate custom coding standards, bot instructions, and project-specific prompts</p>
    </div>

    <!-- Quick Templates -->
    <div class="prompt-card">
        <h3><i class="fas fa-bolt"></i> Quick Templates</h3>
        <p>Select a template to start with:</p>

        <div class="template-grid">
            <div class="template-card" data-template="enterprise-php">
                <i class="fas fa-code"></i>
                <h5>Enterprise PHP</h5>
                <small>Strict typing, PSR-12, security-first</small>
            </div>

            <div class="template-card" data-template="frontend-react">
                <i class="fab fa-react"></i>
                <h5>React Frontend</h5>
                <small>TypeScript, hooks, performance</small>
            </div>

            <div class="template-card" data-template="api-builder">
                <i class="fas fa-exchange-alt"></i>
                <h5>REST API Builder</h5>
                <small>Endpoints, validation, docs</small>
            </div>

            <div class="template-card" data-template="database-expert">
                <i class="fas fa-database"></i>
                <h5>Database Expert</h5>
                <small>Queries, indexes, optimization</small>
            </div>

            <div class="template-card" data-template="security-audit">
                <i class="fas fa-shield-alt"></i>
                <h5>Security Auditor</h5>
                <small>Vulnerabilities, hardening</small>
            </div>

            <div class="template-card" data-template="devops">
                <i class="fas fa-server"></i>
                <h5>DevOps Engineer</h5>
                <small>CI/CD, deployment, monitoring</small>
            </div>

            <div class="template-card" data-template="css-designer">
                <i class="fas fa-palette"></i>
                <h5>CSS Designer</h5>
                <small>Modern CSS, animations, responsive</small>
            </div>

            <div class="template-card" data-template="fullstack">
                <i class="fas fa-layer-group"></i>
                <h5>Full Stack Dev</h5>
                <small>Everything from UI to DB</small>
            </div>
        </div>
    </div>

    <!-- Custom Prompt Builder -->
    <div class="prompt-card">
        <h3><i class="fas fa-brain"></i> Custom Prompt Builder</h3>

        <div class="row">
            <div class="col-md-6">
                <label class="form-label"><strong>What are you building?</strong></label>
                <input type="text" class="form-control" id="project-type"
                       placeholder="e.g., E-commerce API, Admin Dashboard, Mobile App Backend">
            </div>

            <div class="col-md-6">
                <label class="form-label"><strong>Programming Language(s)</strong></label>
                <input type="text" class="form-control" id="languages"
                       placeholder="e.g., PHP 8.1, JavaScript ES6, TypeScript">
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-6">
                <label class="form-label"><strong>Framework(s)</strong></label>
                <input type="text" class="form-control" id="frameworks"
                       placeholder="e.g., Laravel, React, Vue, Bootstrap">
            </div>

            <div class="col-md-6">
                <label class="form-label"><strong>Database</strong></label>
                <input type="text" class="form-control" id="database"
                       placeholder="e.g., MySQL 8.0, PostgreSQL, MongoDB">
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12">
                <label class="form-label"><strong>Specific Requirements / Standards</strong></label>
                <textarea class="form-control" id="requirements" rows="4"
                          placeholder="e.g., Must follow WCAG 2.1 AA, PCI-DSS compliance, Maximum cyclomatic complexity 10, etc."></textarea>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12">
                <label class="form-label"><strong>Coding Style Preferences</strong></label>
                <div>
                    <button class="preset-btn" data-style="strict">🔒 Strict & Secure</button>
                    <button class="preset-btn" data-style="performance">⚡ Performance-Focused</button>
                    <button class="preset-btn" data-style="maintainable">🔧 Highly Maintainable</button>
                    <button class="preset-btn" data-style="rapid">🚀 Rapid Development</button>
                    <button class="preset-btn" data-style="enterprise">🏢 Enterprise-Grade</button>
                    <button class="preset-btn" data-style="startup">💡 Startup MVP</button>
                    <button class="preset-btn" data-style="accessible">♿ Accessibility-First</button>
                    <button class="preset-btn" data-style="scalable">📈 Highly Scalable</button>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <label class="form-label"><strong>AI Provider</strong></label>
                <select class="form-select" id="ai-provider">
                    <option value="claude">🤖 Claude 3.5 Sonnet (Best for code)</option>
                    <option value="openai">🧠 GPT-4o (Fast & versatile)</option>
                </select>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <button class="btn btn-lg preset-btn" id="generate-prompt">
                    <i class="fas fa-magic"></i> Generate Custom Prompt
                </button>
            </div>
        </div>
    </div>

    <!-- Generated Output -->
    <div class="prompt-card" id="output-section" style="display:none;">
        <h3><i class="fas fa-check-circle"></i> Generated Prompt</h3>

        <div class="loading-spinner" id="loading">
            <div class="spinner"></div>
            <p class="mt-3">AI is generating your custom prompt...</p>
        </div>

        <div id="generated-output"></div>

        <div class="action-buttons" id="action-buttons" style="display:none;">
            <button class="btn-action btn-copy" id="copy-btn">
                <i class="fas fa-copy"></i> Copy to Clipboard
            </button>
            <button class="btn-action btn-save" id="save-btn">
                <i class="fas fa-save"></i> Save as .instructions.md
            </button>
            <button class="btn-action btn-vscode" id="vscode-btn">
                <i class="fas fa-code"></i> Auto-Load to VS Code Settings
            </button>
        </div>
    </div>

    <!-- Saved Prompts -->
    <div class="prompt-card">
        <h3><i class="fas fa-history"></i> Recently Generated Prompts</h3>
        <div id="recent-prompts">
            <p class="text-muted">Your recently generated prompts will appear here...</p>
        </div>
    </div>
</div>

<script>
let selectedStyles = [];
let generatedPrompt = '';

// Template selection
document.querySelectorAll('.template-card').forEach(card => {
    card.addEventListener('click', function() {
        const template = this.dataset.template;
        loadTemplate(template);
    });
});

// Style selection
document.querySelectorAll('[data-style]').forEach(btn => {
    btn.addEventListener('click', function() {
        const style = this.dataset.style;
        if (selectedStyles.includes(style)) {
            selectedStyles = selectedStyles.filter(s => s !== style);
            this.classList.remove('active');
        } else {
            selectedStyles.push(style);
            this.classList.add('active');
        }
    });
});

// Generate prompt
document.getElementById('generate-prompt').addEventListener('click', generateCustomPrompt);

// Action buttons
document.getElementById('copy-btn').addEventListener('click', copyToClipboard);
document.getElementById('save-btn').addEventListener('click', saveAsFile);
document.getElementById('vscode-btn').addEventListener('click', loadToVSCode);

function loadTemplate(template) {
    const templates = {
        'enterprise-php': {
            project: 'Enterprise PHP Application',
            languages: 'PHP 8.1+',
            frameworks: 'Custom MVC, Composer',
            database: 'MySQL 8.0',
            requirements: 'Strict typing, PSR-12, security-first, prepared statements only, comprehensive error handling',
            styles: ['strict', 'enterprise', 'maintainable']
        },
        'frontend-react': {
            project: 'React Frontend Application',
            languages: 'TypeScript, JavaScript ES6+',
            frameworks: 'React 18, TailwindCSS',
            database: 'N/A (Frontend)',
            requirements: 'Functional components, hooks, performance optimization, accessibility WCAG 2.1 AA',
            styles: ['performance', 'accessible', 'maintainable']
        },
        'api-builder': {
            project: 'RESTful API Backend',
            languages: 'PHP 8.1, JSON',
            frameworks: 'Slim Framework or Custom',
            database: 'MySQL 8.0',
            requirements: 'REST principles, OpenAPI documentation, rate limiting, JWT authentication, comprehensive validation',
            styles: ['strict', 'scalable', 'enterprise']
        },
        'database-expert': {
            project: 'Database-Intensive Application',
            languages: 'SQL, PHP',
            frameworks: 'PDO, Query Builder',
            database: 'MySQL 8.0 / MariaDB 10.5',
            requirements: 'Query optimization, proper indexing, transaction management, connection pooling, N+1 prevention',
            styles: ['performance', 'scalable']
        },
        'security-audit': {
            project: 'Security-Focused Application',
            languages: 'PHP, JavaScript',
            frameworks: 'Security Libraries',
            database: 'MySQL with encryption',
            requirements: 'OWASP Top 10, input validation, output encoding, CSRF protection, SQL injection prevention, XSS prevention',
            styles: ['strict', 'enterprise']
        },
        'css-designer': {
            project: 'Modern CSS-Driven Interface',
            languages: 'CSS3, HTML5, JavaScript',
            frameworks: 'PostCSS, CSS Grid, Flexbox',
            database: 'N/A',
            requirements: 'Modern CSS architecture, BEM methodology, responsive design, animations, accessibility, performance',
            styles: ['accessible', 'performance', 'maintainable']
        },
        'fullstack': {
            project: 'Full Stack Web Application',
            languages: 'PHP, JavaScript, SQL, HTML, CSS',
            frameworks: 'Custom MVC, Bootstrap, jQuery',
            database: 'MySQL 8.0',
            requirements: 'End-to-end development, security, performance, scalability, maintainability, documentation',
            styles: ['enterprise', 'maintainable', 'scalable']
        }
    };

    const config = templates[template];
    if (config) {
        document.getElementById('project-type').value = config.project;
        document.getElementById('languages').value = config.languages;
        document.getElementById('frameworks').value = config.frameworks;
        document.getElementById('database').value = config.database;
        document.getElementById('requirements').value = config.requirements;

        // Select styles
        selectedStyles = config.styles;
        document.querySelectorAll('[data-style]').forEach(btn => {
            if (config.styles.includes(btn.dataset.style)) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        // Scroll to form
        document.getElementById('project-type').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

async function generateCustomPrompt() {
    const projectType = document.getElementById('project-type').value;
    const languages = document.getElementById('languages').value;
    const frameworks = document.getElementById('frameworks').value;
    const database = document.getElementById('database').value;
    const requirements = document.getElementById('requirements').value;
    const provider = document.getElementById('ai-provider').value;

    if (!projectType) {
        alert('Please enter what you are building');
        return;
    }

    // Show output section and loading
    document.getElementById('output-section').style.display = 'block';
    document.getElementById('loading').classList.add('active');
    document.getElementById('generated-output').innerHTML = '';
    document.getElementById('action-buttons').style.display = 'none';

    // Scroll to output
    document.getElementById('output-section').scrollIntoView({ behavior: 'smooth' });

    // Build AI prompt
    const aiPrompt = `You are an expert software architect and coding standards specialist. Generate a comprehensive, production-ready coding instruction prompt for the following project:

**PROJECT DETAILS:**
- Type: ${projectType}
- Languages: ${languages || 'Not specified'}
- Frameworks: ${frameworks || 'Not specified'}
- Database: ${database || 'Not specified'}
- Special Requirements: ${requirements || 'None'}
- Coding Style: ${selectedStyles.join(', ') || 'Balanced approach'}

**YOUR TASK:**
Create a detailed, actionable coding standards document that includes:

1. **Project Overview** - Brief description and goals
2. **Core Principles** - 5-10 fundamental rules (MUST FOLLOW)
3. **Language-Specific Standards** - Best practices for each language
4. **Framework Guidelines** - How to use frameworks correctly
5. **Database Standards** - Query patterns, indexing, security
6. **Security Requirements** - Authentication, authorization, input validation, etc.
7. **Performance Standards** - Caching, optimization, profiling
8. **Code Quality** - Naming conventions, documentation, testing
9. **Error Handling** - Exception patterns, logging, monitoring
10. **Deployment Checklist** - Pre-deployment verification steps

**FORMAT:**
- Use markdown with clear headings
- Include ✅ DO and ❌ DON'T examples
- Add code snippets where helpful
- Prioritize rules (CRITICAL, HIGH, MEDIUM)
- Be specific and actionable
- Target 500-800 lines of comprehensive standards

Make this prompt ready to copy directly into VS Code settings or .instructions.md files. Be thorough but concise.`;

    try {
        const response = await fetch('/api/ai-chat.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                message: aiPrompt,
                provider: provider,
                context: {
                    source: 'prompt_generator',
                    project_type: projectType,
                    timestamp: new Date().toISOString()
                }
            })
        });

        const data = await response.json();

        if (data.success) {
            generatedPrompt = data.response.message || data.response.content || data.response;
            displayGeneratedPrompt(generatedPrompt);
            saveToRecent(projectType, generatedPrompt);
        } else {
            throw new Error(data.error || 'Failed to generate prompt');
        }

    } catch (error) {
        console.error('Error:', error);
        document.getElementById('generated-output').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                Error generating prompt: ${error.message}
            </div>
        `;
    } finally {
        document.getElementById('loading').classList.remove('active');
        document.getElementById('action-buttons').style.display = 'flex';
    }
}

function displayGeneratedPrompt(prompt) {
    document.getElementById('generated-output').innerHTML = `
        <div class="generated-prompt">${escapeHtml(prompt)}</div>
    `;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function copyToClipboard() {
    navigator.clipboard.writeText(generatedPrompt).then(() => {
        const btn = document.getElementById('copy-btn');
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-copy"></i> Copy to Clipboard';
        }, 2000);
    });
}

function saveAsFile() {
    const projectType = document.getElementById('project-type').value;
    const filename = projectType.toLowerCase().replace(/[^a-z0-9]/g, '_') + '.instructions.md';

    const blob = new Blob([generatedPrompt], { type: 'text/markdown' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);

    const btn = document.getElementById('save-btn');
    btn.innerHTML = '<i class="fas fa-check"></i> Saved!';
    setTimeout(() => {
        btn.innerHTML = '<i class="fas fa-save"></i> Save as .instructions.md';
    }, 2000);
}

function loadToVSCode() {
    // This would integrate with VS Code API if available
    // For now, show instructions
    alert('To load into VS Code:\n\n1. Copy the prompt\n2. Open VS Code settings (settings.json)\n3. Add to "intelligencehub.custom_prompts" section\n\nOr save as .instructions.md in your project root!');

    copyToClipboard();
}

function saveToRecent(projectType, prompt) {
    let recent = JSON.parse(localStorage.getItem('recent_prompts') || '[]');
    recent.unshift({
        project: projectType,
        prompt: prompt.substring(0, 200) + '...',
        timestamp: new Date().toISOString(),
        full: prompt
    });
    recent = recent.slice(0, 10); // Keep last 10
    localStorage.setItem('recent_prompts', JSON.stringify(recent));
    loadRecentPrompts();
}

function loadRecentPrompts() {
    const recent = JSON.parse(localStorage.getItem('recent_prompts') || '[]');
    const container = document.getElementById('recent-prompts');

    if (recent.length === 0) {
        container.innerHTML = '<p class="text-muted">Your recently generated prompts will appear here...</p>';
        return;
    }

    container.innerHTML = recent.map((item, index) => `
        <div class="card mb-2">
            <div class="card-body">
                <h6>${item.project}</h6>
                <small class="text-muted">${new Date(item.timestamp).toLocaleString()}</small>
                <p class="mt-2 mb-0">${item.prompt}</p>
                <button class="btn btn-sm btn-outline-primary mt-2" onclick="loadPrompt(${index})">
                    <i class="fas fa-redo"></i> Load
                </button>
            </div>
        </div>
    `).join('');
}

function loadPrompt(index) {
    const recent = JSON.parse(localStorage.getItem('recent_prompts') || '[]');
    if (recent[index]) {
        generatedPrompt = recent[index].full;
        displayGeneratedPrompt(generatedPrompt);
        document.getElementById('output-section').style.display = 'block';
        document.getElementById('action-buttons').style.display = 'flex';
        document.getElementById('output-section').scrollIntoView({ behavior: 'smooth' });
    }
}

// Load recent prompts on page load
loadRecentPrompts();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
