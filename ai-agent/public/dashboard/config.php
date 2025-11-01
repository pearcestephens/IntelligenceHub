<?php
/**
 * Configuration Page - System settings
 */
$currentPage = 'config';
$pageTitle = 'Configuration - CIS Neural AI';
$breadcrumb = ['Dashboard', 'System', 'Configuration'];
require_once __DIR__ . '/templates/header.php';
?>

<div class="container-fluid">
    <div class="page-header mb-4">
        <h1 class="h3">System Configuration</h1>
        <p class="text-muted">Manage system settings and preferences</p>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-gear"></i> General Settings</h5>
                </div>
                <div class="card-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">System Name</label>
                            <input type="text" class="form-control" value="CIS Neural AI System">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Default AI Model</label>
                            <select class="form-select">
                                <option value="gpt-4" selected>GPT-4</option>
                                <option value="gpt-3.5-turbo">GPT-3.5 Turbo</option>
                                <option value="claude-3">Claude 3</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">API Timeout (seconds)</label>
                            <input type="number" class="form-control" value="30">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Max Concurrent Requests</label>
                            <input type="number" class="form-control" value="10">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-database"></i> Database Settings</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Database Host</label>
                        <input type="text" class="form-control" value="localhost" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Connection Pool Size</label>
                        <input type="number" class="form-control" value="20">
                    </div>
                    
                    <button class="btn btn-outline-primary">Test Connection</button>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-shield-check"></i> Security</h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="enableAuth" checked>
                        <label class="form-check-label" for="enableAuth">
                            Require Authentication
                        </label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="enable2FA">
                        <label class="form-check-label" for="enable2FA">
                            Enable 2FA
                        </label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="enableRateLimit" checked>
                        <label class="form-check-label" for="enableRateLimit">
                            Enable Rate Limiting
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-bell"></i> Notifications</h5>
                </div>
                <div class="card-body">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="emailNotif" checked>
                        <label class="form-check-label" for="emailNotif">
                            Email Notifications
                        </label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="slackNotif">
                        <label class="form-check-label" for="slackNotif">
                            Slack Notifications
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
