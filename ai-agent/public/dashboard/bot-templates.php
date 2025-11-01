<?php
/**
 * Bot Templates Page
 */
$currentPage = 'bot-templates';
$pageTitle = 'Bot Templates - CIS Neural AI';
$breadcrumb = ['Dashboard', 'Bot Management', 'Templates'];
require_once __DIR__ . '/templates/header.php';
?>

<div class="container-fluid">
    <div class="page-header mb-4">
        <h1 class="h3">Bot Templates</h1>
        <p class="text-muted">Pre-configured bot templates for quick deployment</p>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-chat-dots"></i> Customer Support</h5>
                    <p class="card-text text-muted">Handle customer inquiries and support tickets</p>
                    <button class="btn btn-primary btn-sm">Deploy Template</button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-code-slash"></i> Code Review</h5>
                    <p class="card-text text-muted">Review code and provide suggestions</p>
                    <button class="btn btn-primary btn-sm">Deploy Template</button>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-search"></i> Research Assistant</h5>
                    <p class="card-text text-muted">Research and analyze information</p>
                    <button class="btn btn-primary btn-sm">Deploy Template</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
