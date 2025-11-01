<?php
/**
 * Credentials Manager Page
 * Manage system credentials securely
 */

if (!defined('DASHBOARD_ACCESS')) {
    die('Direct access not permitted');
}
?>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-key me-2"></i>
                    Credentials Manager
                </h5>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Under Construction</strong>
                    <p class="mb-0 mt-2">The Credentials Manager is currently being developed. This page will allow secure management of:</p>
                    <ul class="mb-0 mt-2">
                        <li>API Keys</li>
                        <li>Database Credentials</li>
                        <li>Service Account Passwords</li>
                        <li>OAuth Tokens</li>
                        <li>SSH Keys</li>
                    </ul>
                </div>

                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="fas fa-database fa-3x text-primary mb-3"></i>
                                <h6>Database Credentials</h6>
                                <p class="small text-muted">Manage database connection credentials</p>
                                <button class="btn btn-sm btn-outline-primary" disabled>
                                    <i class="fas fa-cog me-1"></i>
                                    Manage
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="fas fa-cloud fa-3x text-success mb-3"></i>
                                <h6>API Keys</h6>
                                <p class="small text-muted">External service API keys</p>
                                <button class="btn btn-sm btn-outline-success" disabled>
                                    <i class="fas fa-cog me-1"></i>
                                    Manage
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <i class="fas fa-lock fa-3x text-danger mb-3"></i>
                                <h6>Secure Vault</h6>
                                <p class="small text-muted">Encrypted credential storage</p>
                                <button class="btn btn-sm btn-outline-danger" disabled>
                                    <i class="fas fa-cog me-1"></i>
                                    Manage
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <h6>Coming Soon Features:</h6>
                    <ul>
                        <li>Encrypted credential storage with AES-256</li>
                        <li>Credential rotation scheduling</li>
                        <li>Access audit logging</li>
                        <li>Multi-user approval workflow</li>
                        <li>Automatic credential testing</li>
                        <li>Integration with secrets managers (HashiCorp Vault, AWS Secrets Manager)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
