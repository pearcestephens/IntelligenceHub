<?php
/**
 * VS Code & GitHub Prompt Manager Dashboard
 * Integrated into CIS Dashboard
 */

declare(strict_types=1);

class PromptManagerDashboard 
{
    public function render(): string 
    {
        return <<<'HTML'
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-robot"></i> VS Code & GitHub Automation</h4>
                    <small class="text-muted">Version 2025.10.27.1</small>
                </div>
                <div class="card-body">
                    
                    <!-- Quick Actions -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h5>Morning Start</h5>
                                    <button class="btn btn-light btn-sm copy-prompt" 
                                            data-prompt="@workspace #file:_automation/prompts/daily/morning-checklist.md">
                                        Copy Start Prompt
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h5>API Work</h5>
                                    <button class="btn btn-light btn-sm copy-prompt" 
                                            data-prompt="@workspace #file:_automation/prompts/project/api-development.md">
                                        Copy API Prompt
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h5>Debug Session</h5>
                                    <button class="btn btn-light btn-sm copy-prompt" 
                                            data-prompt="@workspace Search for [ISSUE] and help me debug it">
                                        Copy Debug Prompt
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <h5>Security Audit</h5>
                                    <button class="btn btn-light btn-sm copy-prompt" 
                                            data-prompt="@workspace #file:_automation/prompts/project/security-audit.md">
                                        Copy Security Prompt
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Settings Sync -->
                    <div class="row">
                        <div class="col-md-6">
                            <h5>VS Code Settings</h5>
                            <button class="btn btn-primary" onclick="syncVSCodeSettings()">
                                <i class="fas fa-sync"></i> Sync VS Code Settings
                            </button>
                            <button class="btn btn-info" onclick="viewSettings()">
                                <i class="fas fa-eye"></i> View Current Settings
                            </button>
                        </div>
                        <div class="col-md-6">
                            <h5>GitHub Instructions</h5>
                            <button class="btn btn-success" onclick="updateInstructions()">
                                <i class="fas fa-refresh"></i> Update Instructions
                            </button>
                            <button class="btn btn-info" onclick="viewInstructions()">
                                <i class="fas fa-eye"></i> View Instructions
                            </button>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyPrompt(prompt) {
    navigator.clipboard.writeText(prompt);
    alert('Prompt copied to clipboard!');
}

document.querySelectorAll('.copy-prompt').forEach(btn => {
    btn.addEventListener('click', function() {
        copyPrompt(this.dataset.prompt);
    });
});

function syncVSCodeSettings() {
    fetch('/api/automation/sync-vscode-settings')
        .then(response => response.json())
        .then(data => {
            alert('VS Code settings synced! Version: ' + data.version);
        });
}

function updateInstructions() {
    fetch('/api/automation/update-github-instructions')
        .then(response => response.json())
        .then(data => {
            alert('GitHub instructions updated! Version: ' + data.version);
        });
}
</script>
HTML;
    }
}
