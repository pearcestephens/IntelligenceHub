<?php
/**
 * Create Bot Page - Bot creation form
 */
$currentPage = 'bot-create';
$pageTitle = 'Create Bot - CIS Neural AI';
$breadcrumb = ['Dashboard', 'Bot Management', 'Create Bot'];
require_once __DIR__ . '/templates/header.php';
?>

<div class="container-fluid">
    <div class="page-header mb-4">
        <h1 class="h3">Create New Bot</h1>
        <p class="text-muted">Configure and deploy a new AI bot</p>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-gear"></i> Bot Configuration</h5>
                </div>
                <div class="card-body">
                    <form id="createBotForm">
                        <div class="mb-3">
                            <label class="form-label">Bot Name</label>
                            <input type="text" class="form-control" placeholder="e.g., Customer Support Bot" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Bot Type</label>
                            <select class="form-select" required>
                                <option value="">Select type...</option>
                                <option value="assistant">General Assistant</option>
                                <option value="code-review">Code Review</option>
                                <option value="support">Customer Support</option>
                                <option value="research">Research Assistant</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">AI Model</label>
                            <select class="form-select" required>
                                <option value="gpt-4">GPT-4 (Recommended)</option>
                                <option value="gpt-3.5-turbo">GPT-3.5 Turbo (Faster)</option>
                                <option value="claude-3">Claude 3</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">System Prompt</label>
                            <textarea class="form-control" rows="4" placeholder="Enter the bot's instructions and behavior..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Memory Settings</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enableMemory" checked>
                                <label class="form-check-label" for="enableMemory">
                                    Enable conversation memory
                                </label>
                            </div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Create Bot
                            </button>
                            <a href="bots.php" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-info-circle"></i> Quick Tips</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Choose descriptive names</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Start with GPT-4 for best results</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Enable memory for context</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Test before deploying</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$inlineScript = <<<'JS'
document.getElementById('createBotForm').addEventListener('submit', (e) => {
    e.preventDefault();
    alert('Bot creation functionality coming soon!');
});
JS;
require_once __DIR__ . '/templates/footer.php';
?>
