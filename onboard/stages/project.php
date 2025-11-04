<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Save project details
    $_SESSION['project'] = [
        'name' => $_POST['project_name'],
        'description' => $_POST['description'],
        'visibility' => $_POST['visibility'],
        'features' => $_POST['features'] ?? []
    ];
    header('Location: index.php?stage=configure');
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
    <div class="stage-item active">
        <div class="stage-circle">3</div>
        <div class="stage-label">Project</div>
    </div>
    <div class="stage-item">
        <div class="stage-circle">4</div>
        <div class="stage-label">Configure</div>
    </div>
    <div class="stage-item">
        <div class="stage-circle">5</div>
        <div class="stage-label">Deploy</div>
    </div>
</div>

<h2>Create Your Project</h2>

<form method="POST">
    <div class="form-group">
        <label for="project_name">Project Name *</label>
        <input type="text" id="project_name" name="project_name" required
               pattern="[a-zA-Z0-9_-]+"
               placeholder="my-ai-agent-project">
        <small>Use lowercase letters, numbers, hyphens, and underscores only.</small>
    </div>

    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="3"
                  placeholder="My AI-powered development project with automated testing and deployment."></textarea>
    </div>

    <div class="form-group">
        <label for="visibility">Repository Visibility *</label>
        <select id="visibility" name="visibility" required>
            <option value="private">Private (Recommended)</option>
            <option value="public">Public</option>
        </select>
        <small>Private repositories are only visible to you and collaborators you add.</small>
    </div>

    <div class="form-group">
        <label>Features to Include</label>
        <div style="margin-top: 10px;">
            <label style="display: block; margin-bottom: 10px;">
                <input type="checkbox" name="features[]" value="frontend_tools" checked>
                <strong>Frontend Testing Tools</strong> - Screenshot, audit, monitoring
            </label>
            <label style="display: block; margin-bottom: 10px;">
                <input type="checkbox" name="features[]" value="workflows" checked>
                <strong>Workflow Automation</strong> - Pre-built automation workflows
            </label>
            <label style="display: block; margin-bottom: 10px;">
                <input type="checkbox" name="features[]" value="mcp_integration" checked>
                <strong>MCP Integration</strong> - Connect to Intelligence Hub
            </label>
            <label style="display: block; margin-bottom: 10px;">
                <input type="checkbox" name="features[]" value="documentation" checked>
                <strong>Complete Documentation</strong> - Guides, references, examples
            </label>
            <label style="display: block; margin-bottom: 10px;">
                <input type="checkbox" name="features[]" value="github_actions">
                <strong>GitHub Actions</strong> - CI/CD pipelines
            </label>
        </div>
    </div>

    <div class="button-group">
        <a href="?stage=github" class="btn" style="background: #e0e0e0; color: #666;">← Back</a>
        <button type="submit" class="btn btn-primary">Continue →</button>
    </div>
</form>
