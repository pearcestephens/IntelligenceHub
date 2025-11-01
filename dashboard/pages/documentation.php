<?php
/**
 * Documentation Dashboard
 * View system documentation and guides
 */
defined('DASHBOARD_ACCESS') or die('Direct access not permitted');
?>

<div class="page-header">
    <h1 class="page-title">Documentation</h1>
    <p class="page-subtitle">System guides and reference materials</p>
</div>

<div class="row g-4">
    <div class="col-lg-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-folder me-2"></i>Categories</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="#getting-started" class="list-group-item list-group-item-action">
                    <i class="fas fa-play-circle me-2"></i>Getting Started
                </a>
                <a href="#api" class="list-group-item list-group-item-action">
                    <i class="fas fa-plug me-2"></i>API Reference
                </a>
                <a href="#database" class="list-group-item list-group-item-action">
                    <i class="fas fa-database me-2"></i>Database Schema
                </a>
                <a href="#deployment" class="list-group-item list-group-item-action">
                    <i class="fas fa-rocket me-2"></i>Deployment
                </a>
                <a href="#troubleshooting" class="list-group-item list-group-item-action">
                    <i class="fas fa-wrench me-2"></i>Troubleshooting
                </a>
                <a href="#faq" class="list-group-item list-group-item-action">
                    <i class="fas fa-question-circle me-2"></i>FAQ
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-lg-9">
        <div class="card mb-4" id="getting-started">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-play-circle me-2"></i>Getting Started</h5>
            </div>
            <div class="card-body">
                <h6>Welcome to the Intelligence Hub Dashboard</h6>
                <p>This system provides comprehensive code intelligence and analysis capabilities.</p>
                
                <h6 class="mt-4">Quick Start Guide</h6>
                <ol>
                    <li><strong>Dashboard Overview:</strong> View system statistics and recent activity</li>
                    <li><strong>Intelligence Search:</strong> Search across your entire codebase</li>
                    <li><strong>File Browser:</strong> Navigate and explore files</li>
                    <li><strong>Function Explorer:</strong> Browse and analyze functions</li>
                </ol>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Tip:</strong> Use the sidebar menu to navigate between different sections of the dashboard.
                </div>
            </div>
        </div>
        
        <div class="card mb-4" id="api">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-plug me-2"></i>API Reference</h5>
            </div>
            <div class="card-body">
                <h6>Available Endpoints</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Method</th>
                                <th>Endpoint</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-success">GET</span></td>
                                <td><code>/api/files</code></td>
                                <td>List all indexed files</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">GET</span></td>
                                <td><code>/api/search</code></td>
                                <td>Search codebase</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-primary">POST</span></td>
                                <td><code>/api/scan</code></td>
                                <td>Trigger system scan</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">GET</span></td>
                                <td><code>/api/functions</code></td>
                                <td>List all functions</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card mb-4" id="database">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-database me-2"></i>Database Schema</h5>
            </div>
            <div class="card-body">
                <h6>Main Tables</h6>
                <ul>
                    <li><strong>intelligence_files:</strong> Stores information about indexed files</li>
                    <li><strong>intelligence_functions:</strong> Function definitions and metadata</li>
                    <li><strong>cron_jobs:</strong> Scheduled task configurations</li>
                </ul>
            </div>
        </div>
        
        <div class="card" id="troubleshooting">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-wrench me-2"></i>Troubleshooting</h5>
            </div>
            <div class="card-body">
                <h6>Common Issues</h6>
                <div class="accordion" id="troubleshootingAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#issue1">
                                Scanner not updating
                            </button>
                        </h2>
                        <div id="issue1" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                            <div class="accordion-body">
                                Check that the neural scanner cron job is running properly in the Cron Management section.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#issue2">
                                Search results empty
                            </button>
                        </h2>
                        <div id="issue2" class="accordion-collapse collapse" data-bs-parent="#troubleshootingAccordion">
                            <div class="accordion-body">
                                Ensure the file index is up to date. Run a manual scan from the Scanner page.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
