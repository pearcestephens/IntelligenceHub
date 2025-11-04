<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save configuration
    $_SESSION['config'] = [
        'mcp_server' => $_POST['mcp_server'],
        'editor' => $_POST['editor'],
        'notifications' => $_POST['notifications'] ?? [],
        'deploy_location' => $_POST['deploy_location'] ?? 'local'
    ];

    // Generate unique session ID for this setup
    $_SESSION['onboard_id'] = uniqid('onboard_', true);

    header('Location: index.php?stage=deploy');
    exit;
}
?>

<div class="stage-indicator">
    <div class="stage-item completed">
        <div class="stage-circle">✓</div>
        <div class="stage-label">Welcome</div>
    </div>
    <div class="stage-item completed">
        <div class="stage-circle">✓</div>
        <div class="stage-label">GitHub</div>
    </div>
    <div class="stage-item completed">
        <div class="stage-circle">✓</div>
        <div class="stage-label">Project</div>
    </div>
    <div class="stage-item active">
        <div class="stage-circle">4</div>
        <div class="stage-label">Configure</div>
    </div>
    <div class="stage-item">
        <div class="stage-circle">5</div>
        <div class="stage-label">Deploy</div>
    </div>
</div>

<h2>⚙️ Configure MCP & Tools</h2>

<form method="POST">
    <div class="form-group">
        <label for="mcp_server"><strong>MCP Server URL</strong> (Intelligence Hub Connection) *</label>
        <input type="url" id="mcp_server" name="mcp_server" required
               value="https://gpt.ecigdis.co.nz/mcp/server_v3_complete.php"
               style="font-family: monospace; background: #f8f9fa;">
        <small style="display: block; margin-top: 8px; line-height: 1.5;">
            <strong>Default:</strong> Intelligence Hub v3 API<br>
            <strong>Provides:</strong> conversation.*, documentation.*, knowledge.*, frontend.* tools<br>
            <strong>Auth:</strong> No authentication required (internal network)
        </small>
    </div>

    <div class="form-group">
        <label><strong>GitHub Copilot Engine</strong></label>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; border: 2px solid #e3f2fd;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <span style="font-family: monospace; font-weight: 600;">gpt-4o</span>
                <span style="background: #10b981; color: white; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">LATEST</span>
            </div>
            <small style="color: #64748b; line-height: 1.5;">
                Latest OpenAI model with 32,768 token context window<br>
                Memory & persistence enabled • Auto-context retrieval • 6 instruction files
            </small>
        </div>
    </div>

    <div class="form-group">
        <label><strong>Available MCP Tools</strong></label>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 13px; line-height: 1.8;">
            <div style="margin-bottom: 12px;">
                <strong style="color: #2563eb;">conversation.*</strong><br>
                <span style="color: #64748b; font-size: 12px;">• get_project_context • search • list_conversations • get_conversation</span>
            </div>
            <div style="margin-bottom: 12px;">
                <strong style="color: #0891b2;">documentation.*</strong><br>
                <span style="color: #64748b; font-size: 12px;">• search • get_file • list_docs</span>
            </div>
            <div style="margin-bottom: 12px;">
                <strong style="color: #7c3aed;">knowledge.*</strong><br>
                <span style="color: #64748b; font-size: 12px;">• search • add • get_article</span>
            </div>
            <div>
                <strong style="color: #10b981;">frontend.*</strong><br>
                <span style="color: #64748b; font-size: 12px;">• screenshot • audit • monitor • auto_fix • visual_regression</span>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="editor"><strong>Code Editor</strong> *</label>
        <select id="editor" name="editor" required style="padding: 12px; font-size: 15px;">
            <option value="vscode">Visual Studio Code (Recommended - Full MCP support)</option>
            <option value="cursor">Cursor (Compatible)</option>
            <option value="other">Other (Manual configuration required)</option>
        </select>
    </div>

    <div class="form-group">
        <label for="deploy_location"><strong>Deployment Target</strong> *</label>
        <select id="deploy_location" name="deploy_location" required style="padding: 12px; font-size: 15px;">
            <option value="github">GitHub Repository (Clone locally)</option>
            <option value="codespaces">GitHub Codespaces (Cloud IDE)</option>
            <option value="local">Local Setup Only (No GitHub push)</option>
        </select>
    </div>

    <div class="alert alert-info">
        <strong>� What Gets Deployed:</strong><br>
        • .vscode/settings.json with MCP configuration<br>
        • .github/copilot-instructions.md with custom prompts<br>
        • Complete documentation (_kb/ folder, docs/, guides)<br>
        • Deployment scripts (deploy_docs_to_cis.sh, indexing tools)<br>
        • README.md with quick start guide
    </div>

    <div class="button-group">
        <a href="?stage=project" class="btn" style="background: #e0e0e0; color: #666;">← Back</a>
        <button type="submit" class="btn btn-primary">Deploy Now →</button>
    </div>
</form>
