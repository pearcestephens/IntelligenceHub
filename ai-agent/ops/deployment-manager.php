<?php

declare(strict_types=1);

/**
 * Deployment Automation System - Production deployment and environment management
 * 
 * Comprehensive deployment automation for AI Agent system:
 * - One-click deployments across environments (DEV, STAGING, PROD)
 * - Database migration system with rollback capabilities
 * - Environment configuration management and validation
 * - Automated backup and restore procedures
 * - SSL certificate management and renewal
 * - Dependency management and version control
 * - Blue/green deployment support
 * - Feature flag system for gradual rollouts
 * - Deployment health checks and validation
 * - Rollback procedures and disaster recovery
 * 
 * @package App\Operations
 * @author Production AI Agent System
 * @version 1.0.0
 */

require_once __DIR__ . '/../src/bootstrap.php';

use App\Config;
use App\Logger;
use App\DB;

class DeploymentManager 
{
    private Config $config;
    private Logger $logger;
    private string $baseDir;
    private string $backupDir;
    
    // Deployment environments
    private array $environments = ['dev', 'staging', 'prod'];
    
    // Deployment configuration
    private array $deployConfig = [
        'backup_retention_days' => 30,
        'migration_timeout' => 300,
        'health_check_timeout' => 60,
        'rollback_timeout' => 180,
        'max_deployment_time' => 600
    ];
    
    public function __construct()
    {
        $this->config = new Config();
        $this->logger = new Logger($this->config);
        $this->baseDir = dirname(__DIR__);
        $this->backupDir = $this->baseDir . '/backups/deployments';
        
        // Ensure backup directory exists
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }
    
    /**
     * Deploy to specified environment
     */
    public function deploy(string $environment, array $options = []): array
    {
        $deploymentId = $this->generateDeploymentId();
        $startTime = microtime(true);
        
        $this->logger->info("Starting deployment to {$environment}", [
            'deployment_id' => $deploymentId,
            'options' => $options
        ]);
        
        try {
            // Validate environment
            if (!in_array($environment, $this->environments)) {
                throw new Exception("Invalid environment: {$environment}");
            }
            
            // Pre-deployment checks
            $this->runPreDeploymentChecks($environment);
            
            // Create deployment backup
            $backupPath = $this->createDeploymentBackup($deploymentId, $environment);
            
            // Run database migrations
            if ($options['run_migrations'] ?? true) {
                $this->runDatabaseMigrations($environment);
            }
            
            // Deploy application code
            $this->deployApplicationCode($environment, $options);
            
            // Update configuration
            $this->updateConfiguration($environment, $options);
            
            // Clear caches
            <?php

            declare(strict_types=1);

            /**
             * CIS Deployment Manager - Web Console
             *
             * Enterprise-grade web interface for managing AI Agent deployments
             * Provides:
             *  - Environment status overview (DEV, STAGING, PROD)
             *  - Deployment history with detailed metadata
             *  - Feature flag governance and rollout controls
             *  - Database & infrastructure readiness checks
             *  - Automated runbooks and rollback guidance
             *
             * @package CIS_Neural_AI_Agent
             */

            // Integrate with CIS authentication / environment
            require_once $_SERVER['DOCUMENT_ROOT'] . '/app.php';

            date_default_timezone_set('Pacific/Auckland');

            header('Content-Type: text/html; charset=utf-8');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-Content-Type-Options: nosniff');

            $demo_mode = !isset($_SESSION['userID']);
            $user_name = $demo_mode
                ? 'Deployment Observer'
                : trim(($_SESSION['firstName'] ?? '') . ' ' . ($_SESSION['lastName'] ?? ''));

            $current_time = date('Y-m-d H:i:s');

            // Simulated environment data (replace with live data sources when ready)
            $environments = [
                'dev' => [
                    'label' => 'Development',
                    'status' => 'stable',
                    'version' => '2025.09.28-dev.4',
                    'last_deployment' => '2025-09-28 23:18',
                    'owner' => 'Engineering Guild',
                    'checks' => [
                        ['name' => 'CI Pipeline', 'status' => 'pass', 'details' => 'Last run 2 hours ago'],
                        ['name' => 'Database Connectivity', 'status' => 'pass', 'details' => 'Latency 14ms'],
                        ['name' => 'Redis Cache', 'status' => 'warning', 'details' => 'Hit rate 79%']
                    ]
                ],
                'staging' => [
                    'label' => 'Staging',
                    'status' => 'warning',
                    'version' => '2025.09.26-rc.2',
                    'last_deployment' => '2025-09-27 18:42',
                    'owner' => 'Release Council',
                    'checks' => [
                        ['name' => 'Synthetic Monitoring', 'status' => 'warning', 'details' => 'Response 520ms (SLA 400ms)'],
                        ['name' => 'Regression Tests', 'status' => 'pass', 'details' => '512 tests / 0 failures'],
                        ['name' => 'Feature Flags', 'status' => 'pass', 'details' => '12 flags enabled']
                    ]
                ],
                'prod' => [
                    'label' => 'Production',
                    'status' => 'healthy',
                    'version' => '2025.09.21',
                    'last_deployment' => '2025-09-24 06:15',
                    'owner' => 'Operations Command',
                    'checks' => [
                        ['name' => 'Customer Traffic', 'status' => 'pass', 'details' => '3.4k requests / min'],
                        ['name' => 'Error Budget', 'status' => 'pass', 'details' => '99.97% uptime (30d)'],
                        ['name' => 'Backup Snapshot', 'status' => 'pass', 'details' => '4 hours since last snapshot']
                    ]
                ]
            ];

            $feature_flags = [
                [
                    'name' => 'claudia_proactive_outreach',
                    'description' => 'Automated welcome outreach workflow for new Jessica hires',
                    'environment' => 'prod',
                    'state' => 'enabled',
                    'rollout' => 100,
                    'owner' => 'HR Ops'
                ],
                [
                    'name' => 'neural_brain_learning_v2',
                    'description' => 'Improved knowledge ingestion pipeline',
                    'environment' => 'staging',
                    'state' => 'gradual',
                    'rollout' => 45,
                    'owner' => 'Neural Platform'
                ],
                [
                    'name' => 'deployment_guardrails',
                    'description' => 'Mandatory checklist & approvals for production pushes',
                    'environment' => 'prod',
                    'state' => 'enabled',
                    'rollout' => 100,
                    'owner' => 'Site Reliability'
                ],
                [
                    'name' => 'contextual_ai_briefs',
                    'description' => 'Auto-generate deployment briefs leveraging CLAUDIA context engine',
                    'environment' => 'dev',
                    'state' => 'beta',
                    'rollout' => 15,
                    'owner' => 'AI Enablement'
                ]
            ];

            $deployment_history = [
                [
                    'id' => 'DEP-2025-0924-0615',
                    'environment' => 'prod',
                    'status' => 'success',
                    'initiated_by' => 'Pearce Stephens',
                    'duration' => 13,
                    'changes' => 18,
                    'summary' => 'Rolled out CLAUDIA proactive introduction + CIS header integration',
                    'timestamp' => '2025-09-24 06:15'
                ],
                [
                    'id' => 'DEP-2025-0923-1902',
                    'environment' => 'staging',
                    'status' => 'warning',
                    'initiated_by' => 'Jessica Automation',
                    'duration' => 19,
                    'changes' => 27,
                    'summary' => 'Validated neural brain knowledge ingestion improvements',
                    'timestamp' => '2025-09-23 19:02'
                ],
                [
                    'id' => 'DEP-2025-0922-2310',
                    'environment' => 'dev',
                    'status' => 'success',
                    'initiated_by' => 'Automation Pipeline',
                    'duration' => 7,
                    'changes' => 9,
                    'summary' => 'Integrated AI Agent orchestration telemetry + dashboards',
                    'timestamp' => '2025-09-22 23:10'
                ],
                [
                    'id' => 'DEP-2025-0920-1505',
                    'environment' => 'prod',
                    'status' => 'failed',
                    'initiated_by' => 'Release Council',
                    'duration' => 5,
                    'changes' => 6,
                    'summary' => 'Rollback triggered by Deputy API outage guardrails',
                    'timestamp' => '2025-09-20 15:05'
                ]
            ];

            $readiness_checks = [
                [
                    'category' => 'Pre-flight Validation',
                    'items' => [
                        ['name' => 'Jira deployment ticket approved', 'status' => 'pass', 'owner' => 'Product Ops'],
                        ['name' => 'Unit & integration suites green', 'status' => 'pass', 'owner' => 'QA Guild'],
                        ['name' => 'Database migrations reviewed', 'status' => 'warning', 'owner' => 'Data Engineering']
                    ]
                ],
                [
                    'category' => 'Infrastructure',
                    'items' => [
                        ['name' => 'Cloudways server capacity', 'status' => 'pass', 'owner' => 'SRE'],
                        ['name' => 'SSL certificates expiring soon', 'status' => 'pass', 'owner' => 'Security'],
                        ['name' => 'Backup snapshot verified', 'status' => 'pass', 'owner' => 'Platform Reliability']
                    ]
                ],
                [
                    'category' => 'Post-Deployment',
                    'items' => [
                        ['name' => 'Automated smoke tests scheduled', 'status' => 'pass', 'owner' => 'QA Guild'],
                        ['name' => 'Rollback plan documented', 'status' => 'pass', 'owner' => 'Operations Command'],
                        ['name' => 'Stakeholder comms drafted', 'status' => 'warning', 'owner' => 'AI Enablement']
                    ]
                ]
            ];

            // JSON payloads for the frontend visualisations
            $client_payload = [
                'environments' => $environments,
                'history' => $deployment_history,
                'featureFlags' => $feature_flags,
                'readinessChecks' => $readiness_checks
            ];

            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>CIS Deployment Manager</title>

                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
                <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

                <style>
                    :root {
                        --primary: #0d6efd;
                        --success: #198754;
                        --warning: #ffc107;
                        --danger: #dc3545;
                        --neutral: #6c757d;
                        --bg: #f5f6fa;
                    }

                    body {
                        background: linear-gradient(120deg, rgba(13,110,253,0.12), rgba(118,75,162,0.18));
                        min-height: 100vh;
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    }

                    .navbar {
                        background: linear-gradient(135deg, var(--primary) 0%, #0056b3 100%);
                        box-shadow: 0 2px 12px rgba(13, 110, 253, 0.18);
                    }

                    .badge-status {
                        font-size: 0.75rem;
                        text-transform: uppercase;
                        letter-spacing: 0.04em;
                    }

                    .badge-status.healthy { background: var(--success); }
                    .badge-status.warning { background: var(--warning); color: #1f1f1f; }
                    .badge-status.failed { background: var(--danger); }
                    .badge-status.stable { background: #3f7fff; }

                    .card-ambient {
                        background: rgba(255,255,255,0.88);
                        border-radius: 16px;
                        box-shadow: 0 12px 30px rgba(11, 18, 42, 0.08);
                        border: 1px solid rgba(255,255,255,0.6);
                        backdrop-filter: blur(12px);
                    }

                    .deployment-timeline-item {
                        border-left: 3px solid var(--primary);
                        padding-left: 1rem;
                        margin-bottom: 1.5rem;
                        position: relative;
                    }

                    .deployment-timeline-item::before {
                        content: '';
                        position: absolute;
                        left: -11px;
                        top: 6px;
                        width: 12px;
                        height: 12px;
                        border-radius: 50%;
                        background: var(--primary);
                    }

                    .deployment-timeline-item.failed::before { background: var(--danger); }
                    .deployment-timeline-item.warning::before { background: var(--warning); }

                    .checklist-item {
                        display: flex;
                        justify-content: space-between;
                        padding: 0.75rem 1rem;
                        border-radius: 12px;
                        background: rgba(13, 110, 253, 0.04);
                        margin-bottom: 0.75rem;
                        border: 1px solid rgba(13, 110, 253, 0.08);
                    }

                    .status-pill {
                        border-radius: 999px;
                        padding: 0.35rem 0.75rem;
                        font-size: 0.75rem;
                        text-transform: uppercase;
                        font-weight: 600;
                    }

                    .status-pill.pass { background: rgba(25, 135, 84, 0.12); color: var(--success); }
                    .status-pill.warning { background: rgba(255, 193, 7, 0.18); color: #9c6e00; }
                    .status-pill.fail { background: rgba(220, 53, 69, 0.14); color: var(--danger); }

                    .feature-flag-card {
                        border-left: 4px solid var(--primary);
                        border-radius: 14px;
                        padding: 1.25rem;
                        background: rgba(255,255,255,0.9);
                        margin-bottom: 1rem;
                        box-shadow: 0 6px 18px rgba(14, 30, 84, 0.1);
                    }

                    .rollout-bar {
                        height: 6px;
                        border-radius: 999px;
                        background: rgba(13,110,253,0.15);
                        overflow: hidden;
                    }

                    .rollout-bar > span {
                        display: block;
                        height: 100%;
                        background: linear-gradient(90deg, var(--primary), #42a5ff);
                    }

                    .action-button {
                        border-radius: 999px;
                        padding: 0.55rem 1.25rem;
                        font-weight: 600;
                        letter-spacing: 0.015em;
                    }

                    .timeline-meta {
                        font-size: 0.82rem;
                        color: #6c757d;
                    }

                    .section-heading {
                        font-weight: 600;
                        letter-spacing: 0.03em;
                        text-transform: uppercase;
                        font-size: 0.9rem;
                        color: rgba(13, 37, 63, 0.6);
                    }

                    .environment-card:hover {
                        transform: translateY(-4px);
                        transition: transform 0.15s ease, box-shadow 0.15s ease;
                        box-shadow: 0 15px 28px rgba(13, 110, 253, 0.14);
                    }

                    .environment-card {
                        transition: transform 0.15s ease, box-shadow 0.15s ease;
                    }

                    .deployment-controls {
                        background: rgba(13,110,253,0.08);
                        border-radius: 14px;
                        padding: 1.25rem;
                    }

                    .pulse-indicator {
                        position: absolute;
                        top: -12px;
                        right: -12px;
                        width: 12px;
                        height: 12px;
                        border-radius: 50%;
                        background: var(--success);
                        animation: pulse 2s infinite;
                    }

                    @keyframes pulse {
                        0% { opacity: 0.6; transform: scale(0.8); }
                        50% { opacity: 1; transform: scale(1.4); }
                        100% { opacity: 0.4; transform: scale(0.8); }
                    }
                </style>
            </head>
            <body>
                <nav class="navbar navbar-expand-lg navbar-dark">
                    <div class="container-fluid">
                        <a class="navbar-brand d-flex align-items-center" href="#">
                            <i class="fas fa-rocket me-2"></i>
                            CIS Deployment Command
                            <?php if ($demo_mode): ?>
                                <span class="badge bg-warning text-dark ms-2">DEMO MODE</span>
                            <?php endif; ?>
                        </a>
                        <div class="navbar-nav ms-auto align-items-center">
                            <span class="navbar-text me-3">
                                <i class="fas fa-clock me-1"></i>
                                <span id="current-time-display"><?= htmlspecialchars($current_time) ?></span>
                            </span>
                            <span class="navbar-text me-3">
                                <i class="fas fa-user-shield me-1"></i>
                                <?= htmlspecialchars($user_name) ?>
                            </span>
                            <button class="btn btn-outline-light btn-sm action-button" onclick="window.deploymentUI.refreshData()">
                                <i class="fas fa-sync-alt me-1"></i>Refresh Board
                            </button>
                        </div>
                    </div>
                </nav>

                <div class="container-fluid py-4">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="card-ambient p-4 position-relative overflow-hidden">
                                <div class="pulse-indicator"></div>
                                <div class="d-flex flex-wrap align-items-center justify-content-between">
                                    <div>
                                        <h1 class="h3 mb-1">Enterprise Deployment Control Hub</h1>
                                        <p class="text-muted mb-0">Coordinate safe rollouts, manage rollback plans, and monitor environment readiness for the CIS Neural AI platform.</p>
                                    </div>
                                    <div class="deployment-controls">
                                        <div class="text-muted text-uppercase fw-semibold small mb-2">Rapid Actions</div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button class="btn btn-primary btn-sm action-button" onclick="deploymentUI.openRunbook()">
                                                <i class="fas fa-book-open me-1"></i>Runbooks
                                            </button>
                                            <button class="btn btn-outline-primary btn-sm action-button" onclick="deploymentUI.scheduleWindow()">
                                                <i class="fas fa-calendar-check me-1"></i>Schedule Window
                                            </button>
                                            <button class="btn btn-outline-danger btn-sm action-button" onclick="deploymentUI.triggerRollback()">
                                                <i class="fas fa-undo-alt me-1"></i>Simulate Rollback
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mt-1">
                        <div class="col-xl-8">
                            <div class="card-ambient p-4 h-100">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <div>
                                        <div class="section-heading">Environment Readiness</div>
                                        <h2 class="h4 mb-0">Deployment Landscape</h2>
                                    </div>
                                    <div class="text-muted small">Telemetry refreshed every 5 minutes</div>
                                </div>

                                <div class="row g-3" id="environment-panels"></div>
                            </div>
                        </div>
                        <div class="col-xl-4">
                            <div class="card-ambient p-4 mb-4">
                                <div class="section-heading">Deployment Velocity</div>
                                <h2 class="h5 mb-3">Change Throughput</h2>
                                <div class="chart-container" style="height:220px">
                                    <canvas id="velocity-chart"></canvas>
                                </div>
                            </div>
                            <div class="card-ambient p-4">
                                <div class="section-heading">Guardrail Snapshot</div>
                                <h2 class="h5 mb-3">Release Confidence Index</h2>
                                <div class="chart-container" style="height:220px">
                                    <canvas id="confidence-chart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mt-1">
                        <div class="col-lg-6">
                            <div class="card-ambient p-4 h-100">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div>
                                        <div class="section-heading">Deployment History</div>
                                        <h2 class="h4 mb-0">Activity Timeline</h2>
                                    </div>
                                    <button class="btn btn-outline-primary btn-sm" onclick="deploymentUI.exportHistory()">
                                        <i class="fas fa-download me-1"></i>Export Report
                                    </button>
                                </div>
                                <div id="deployment-timeline"></div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="card-ambient p-4 h-100">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div>
                                        <div class="section-heading">Feature Governance</div>
                                        <h2 class="h4 mb-0">Feature Flag Control</h2>
                                    </div>
                                    <button class="btn btn-outline-success btn-sm" onclick="deploymentUI.manageFlags()">
                                        <i class="fas fa-toggle-on me-1"></i>Manage Flags
                                    </button>
                                </div>
                                <div id="feature-flag-panel"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mt-1">
                        <div class="col-12">
                            <div class="card-ambient p-4">
                                <div class="section-heading">Readiness Playbook</div>
                                <h2 class="h4 mb-3">Operational Checklist</h2>
                                <div class="row g-3" id="readiness-grid"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Toasts -->
                <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080">
                    <div id="deployment-toast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="fas fa-check-circle me-2"></i>
                                Deployment command executed in dry-run mode. View activity under timeline.
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                </div>

                <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
                <script>
                    const deploymentData = <?= json_encode($client_payload, JSON_HEX_TAG | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP) ?>;

                    class DeploymentUI {
                        constructor(data) {
                            this.data = data;
                            this.charts = {};
                            this.init();
                        }

                        init() {
                            this.renderEnvironments();
                            this.renderTimeline();
                            this.renderFeatureFlags();
                            this.renderReadiness();
                            this.initCharts();
                            this.startClock();
                        }

                        renderEnvironments() {
                            const container = document.getElementById('environment-panels');
                            container.innerHTML = '';

                            Object.entries(this.data.environments).forEach(([key, env]) => {
                                const statusClass = env.status;
                                const card = document.createElement('div');
                                card.className = 'col-md-4';
                                card.innerHTML = `
                                    <div class="environment-card card-ambient p-3 h-100">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <div class="section-heading mb-1">${env.label}</div>
                                                <h3 class="h5 mb-0">${env.version}</h3>
                                            </div>
                                            <span class="badge badge-status ${statusClass}">${env.status}</span>
                                        </div>
                                        <div class="text-muted small mb-3">Last deployment: ${env.last_deployment}</div>
                                        <div class="mb-3">
                                            ${env.checks.map(check => `
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="text-muted">${check.name}</span>
                                                    <span class="status-pill ${check.status === 'pass' ? 'pass' : check.status === 'warning' ? 'warning' : 'fail'}">${check.status}</span>
                                                </div>
                                            `).join('')}
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-muted small">Owner: ${env.owner}</span>
                                            <button class="btn btn-outline-primary btn-sm" onclick="deploymentUI.openEnvironment('${key}')">
                                                View Runbook
                                            </button>
                                        </div>
                                    </div>
                                `;
                                container.appendChild(card);
                            });
                        }

                        renderTimeline() {
                            const container = document.getElementById('deployment-timeline');
                            container.innerHTML = '';

                            this.data.history.forEach(entry => {
                                const item = document.createElement('div');
                                item.className = `deployment-timeline-item ${entry.status}`;
                                item.innerHTML = `
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="h6 mb-1">${entry.summary}</h3>
                                            <div class="timeline-meta">
                                                <i class="fas fa-hashtag me-1"></i>${entry.id}
                                                <span class="ms-3"><i class="fas fa-server me-1"></i>${entry.environment.toUpperCase()}</span>
                                                <span class="ms-3"><i class="fas fa-user me-1"></i>${entry.initiated_by}</span>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="badge badge-status ${entry.status}">${entry.status}</div>
                                            <div class="timeline-meta mt-1">${entry.timestamp}</div>
                                            <div class="timeline-meta">${entry.duration} min · ${entry.changes} changes</div>
                                        </div>
                                    </div>
                                `;
                                container.appendChild(item);
                            });
                        }

                        renderFeatureFlags() {
                            const container = document.getElementById('feature-flag-panel');
                            container.innerHTML = '';

                            this.data.featureFlags.forEach(flag => {
                                const card = document.createElement('div');
                                card.className = 'feature-flag-card';
                                card.innerHTML = `
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h3 class="h6 mb-1">${flag.name}</h3>
                                            <p class="text-muted small mb-2">${flag.description}</p>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-light text-uppercase text-muted">${flag.environment}</span>
                                            <div class="mt-2 fw-semibold ${flag.state === 'enabled' ? 'text-success' : flag.state === 'gradual' ? 'text-warning' : 'text-primary'}">
                                                ${flag.state}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="rollout-bar mt-3">
                                        <span style="width:${flag.rollout}%;"></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-2 text-muted small">
                                        <span>Rollout: ${flag.rollout}%</span>
                                        <span>Owner: ${flag.owner}</span>
                                    </div>
                                `;
                                container.appendChild(card);
                            });
                        }

                        renderReadiness() {
                            const container = document.getElementById('readiness-grid');
                            container.innerHTML = '';

                            this.data.readinessChecks.forEach(group => {
                                const col = document.createElement('div');
                                col.className = 'col-lg-4';
                                col.innerHTML = `
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-body">
                                            <h3 class="h6 text-uppercase text-muted mb-3">${group.category}</h3>
                                            ${group.items.map(item => `
                                                <div class="checklist-item">
                                                    <div>
                                                        <div class="fw-semibold">${item.name}</div>
                                                        <div class="text-muted small">Owner: ${item.owner}</div>
                                                    </div>
                                                    <span class="status-pill ${item.status === 'pass' ? 'pass' : item.status === 'warning' ? 'warning' : 'fail'}">${item.status}</span>
                                                </div>
                                            `).join('')}
                                        </div>
                                    </div>
                                `;
                                container.appendChild(col);
                            });
                        }

                        initCharts() {
                            const velocityCtx = document.getElementById('velocity-chart').getContext('2d');
                            this.charts.velocity = new Chart(velocityCtx, {
                                type: 'bar',
                                data: {
                                    labels: ['Week -4', 'Week -3', 'Week -2', 'Week -1', 'This Week'],
                                    datasets: [{
                                        label: 'Deployments',
                                        data: [6, 8, 9, 7, 5],
                                        backgroundColor: 'rgba(13, 110, 253, 0.75)',
                                        borderRadius: 6
                                    }, {
                                        label: 'Rollback Events',
                                        data: [1, 0, 0, 1, 0],
                                        backgroundColor: 'rgba(220, 53, 69, 0.55)',
                                        borderRadius: 6
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { display: true }
                                    },
                                    scales: {
                                        y: {
                                            beginAtZero: true,
                                            ticks: { stepSize: 2 }
                                        }
                                    }
                                }
                            });

                            const confidenceCtx = document.getElementById('confidence-chart').getContext('2d');
                            this.charts.confidence = new Chart(confidenceCtx, {
                                type: 'doughnut',
                                data: {
                                    labels: ['Automation Coverage', 'Guardrail Compliance', 'Human Reviews'],
                                    datasets: [{
                                        data: [55, 30, 15],
                                        backgroundColor: ['#0d6efd', '#20c997', '#ffc107'],
                                        borderWidth: 0
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: { position: 'bottom' }
                                    }
                                }
                            });
                        }

                        startClock() {
                            const clock = document.getElementById('current-time-display');
                            setInterval(() => {
                                const now = new Date();
                                clock.textContent = now.toLocaleString();
                            }, 1000);
                        }

                        refreshData() {
                            const toastEl = document.getElementById('deployment-toast');
                            const toast = new bootstrap.Toast(toastEl);
                            toast.show();

                            setTimeout(() => {
                                this.renderEnvironments();
                                this.renderTimeline();
                                this.renderFeatureFlags();
                                this.renderReadiness();
                            }, 800);
                        }

                        openRunbook() {
                            alert('Runbook library opens in a new CIS panel (simulated).');
                        }

                        scheduleWindow() {
                            alert('Deployment scheduling wizard launched (simulated).');
                        }

                        triggerRollback() {
                            const toastEl = document.getElementById('deployment-toast');
                            toastEl.classList.remove('text-bg-success');
                            toastEl.classList.add('text-bg-warning');
                            toastEl.querySelector('.toast-body').innerHTML = '<i class="fas fa-undo me-2"></i>Rollback simulation completed. No changes were applied to systems.';
                            new bootstrap.Toast(toastEl).show();
                        }

                        openEnvironment(envKey) {
                            alert(`Detailed runbook for ${envKey.toUpperCase()} opens in dedicated view (simulated).`);
                        }

                        exportHistory() {
                            alert('Deployment history export prepared (simulated).');
                        }

                        manageFlags() {
                            alert('Feature flag management console opens (simulated).');
                        }
                    }

                    const deploymentUI = new DeploymentUI(deploymentData);
                    window.deploymentUI = deploymentUI;
                </script>
            </body>
            </html>
        // Backup application files
        $this->backupApplicationFiles($backupPath . '/files');
        
        // Backup configuration
        $this->backupConfiguration($backupPath . '/config');
        
        // Create backup manifest
        $manifest = [
            'deployment_id' => $deploymentId,
            'environment' => $environment,
            'created_at' => date('Y-m-d H:i:s'),
            'version' => $this->getCurrentVersion(),
            'files' => [
                'database' => 'database.sql',
                'application' => 'files/',
                'configuration' => 'config/'
            ]
        ];
        
        file_put_contents($backupPath . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
        
        $this->logger->info("Deployment backup created: {$backupPath}");
        
        return $backupPath;
    }
    
    private function backupDatabase(string $backupFile): void
    {
        $host = $this->config->get('database.host');
        $database = $this->config->get('database.name');
        $username = $this->config->get('database.username');
        $password = $this->config->get('database.password');
        
        $command = sprintf(
            'mysqldump --host=%s --user=%s --password=%s --single-transaction --routines --triggers %s > %s',
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($backupFile)
        );
        
        exec($command, $output, $exitCode);
        
        if ($exitCode !== 0) {
            throw new Exception("Database backup failed with exit code: {$exitCode}");
        }
    }
    
    private function restoreDatabase(string $backupFile): void
    {
        if (!file_exists($backupFile)) {
            throw new Exception("Database backup file not found: {$backupFile}");
        }
        
        $host = $this->config->get('database.host');
        $database = $this->config->get('database.name');
        $username = $this->config->get('database.username');
        $password = $this->config->get('database.password');
        
        $command = sprintf(
            'mysql --host=%s --user=%s --password=%s %s < %s',
            escapeshellarg($host),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($backupFile)
        );
        
        exec($command, $output, $exitCode);
        
        if ($exitCode !== 0) {
            throw new Exception("Database restore failed with exit code: {$exitCode}");
        }
    }
    
    private function deployApplicationCode(string $environment, array $options): void
    {
        $this->logger->info("Deploying application code to {$environment}");
        
        // In a real implementation, this would:
        // - Pull latest code from repository
        // - Compile/build assets if needed
        // - Update file permissions
        // - Sync files to target environment
        
        // For now, just log the action
        $this->logger->info("Application code deployment completed");
    }
    
    private function updateConfiguration(string $environment, array $options): void
    {
        $this->logger->info("Updating configuration for {$environment}");
        
        // Environment-specific configuration updates would go here
        
        $this->logger->info("Configuration update completed");
    }
    
    private function clearCaches(string $environment): void
    {
        $this->logger->info("Clearing caches for {$environment}");
        
        try {
            // Clear Redis cache
            $redis = new Redis();
            $redis->connect($this->config->get('redis.host'), $this->config->get('redis.port'));
            $redis->flushDb();
            
            // Clear file-based caches
            $cacheDir = $this->baseDir . '/cache';
            if (is_dir($cacheDir)) {
                $this->recursiveDelete($cacheDir . '/*');
            }
            
        } catch (Exception $e) {
            $this->logger->warning("Cache clearing failed: " . $e->getMessage());
        }
    }
    
    private function runPostDeploymentChecks(string $environment): void
    {
        $this->logger->info("Running post-deployment checks for {$environment}");
        
        // Verify file permissions
        $critical_dirs = ['logs', 'cache', 'uploads'];
        
        foreach ($critical_dirs as $dir) {
            $path = $this->baseDir . '/' . $dir;
            if (is_dir($path) && !is_writable($path)) {
                throw new Exception("Directory not writable: {$path}");
            }
        }
        
        $this->logger->info("Post-deployment checks passed");
    }
    
    private function runHealthCheck(string $environment): array
    {
        $this->logger->info("Running health check for {$environment}");
        
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'file_permissions' => $this->checkFilePermissions(),
            'configuration' => $this->checkConfiguration()
        ];
        
        $overallStatus = array_reduce($checks, function($carry, $check) {
            return $carry && $check['status'] === 'healthy';
        }, true);
        
        return [
            'overall_status' => $overallStatus ? 'healthy' : 'unhealthy',
            'checks' => $checks,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    private function checkDatabase(): array
    {
        try {
            $db = new DB($this->config);
            $pdo = $db->getConnection();
            $pdo->query('SELECT 1')->fetch();
            
            return ['status' => 'healthy', 'message' => 'Database connection successful'];
        } catch (Exception $e) {
            return ['status' => 'unhealthy', 'message' => $e->getMessage()];
        }
    }
    
    private function checkRedis(): array
    {
        try {
            $redis = new Redis();
            $redis->connect($this->config->get('redis.host'), $this->config->get('redis.port'));
            $redis->ping();
            
            return ['status' => 'healthy', 'message' => 'Redis connection successful'];
        } catch (Exception $e) {
            return ['status' => 'unhealthy', 'message' => $e->getMessage()];
        }
    }
    
    private function checkFilePermissions(): array
    {
        $required_dirs = ['logs', 'cache', 'uploads'];
        $issues = [];
        
        foreach ($required_dirs as $dir) {
            $path = $this->baseDir . '/' . $dir;
            if (!is_dir($path)) {
                $issues[] = "Missing directory: {$dir}";
            } elseif (!is_writable($path)) {
                $issues[] = "Not writable: {$dir}";
            }
        }
        
        if (empty($issues)) {
            return ['status' => 'healthy', 'message' => 'File permissions OK'];
        } else {
            return ['status' => 'unhealthy', 'message' => implode(', ', $issues)];
        }
    }
    
    private function checkConfiguration(): array
    {
        $required_config = [
            'database.host',
            'database.name',
            'database.username',
            'openai.api_key',
            'redis.host'
        ];
        
        $missing = [];
        
        foreach ($required_config as $key) {
            if (empty($this->config->get($key))) {
                $missing[] = $key;
            }
        }
        
        if (empty($missing)) {
            return ['status' => 'healthy', 'message' => 'Configuration complete'];
        } else {
            return ['status' => 'unhealthy', 'message' => 'Missing config: ' . implode(', ', $missing)];
        }
    }
    
    private function getAppliedMigrations(): array
    {
        try {
            $db = new DB($this->config);
            $pdo = $db->getConnection();
            
            // Create migrations table if it doesn't exist
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS migrations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    migration VARCHAR(255) NOT NULL UNIQUE,
                    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            $stmt = $pdo->query("SELECT migration FROM migrations ORDER BY applied_at");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
            
        } catch (Exception $e) {
            $this->logger->warning("Could not fetch applied migrations: " . $e->getMessage());
            return [];
        }
    }
    
    private function recordMigration(string $migration): void
    {
        $db = new DB($this->config);
        $pdo = $db->getConnection();
        
        $stmt = $pdo->prepare("INSERT INTO migrations (migration) VALUES (?)");
        $stmt->execute([$migration]);
    }
    
    private function getCurrentVersion(): string
    {
        $versionFile = $this->baseDir . '/VERSION';
        
        if (file_exists($versionFile)) {
            return trim(file_get_contents($versionFile));
        }
        
        return 'unknown';
    }
    
    private function getLastDeployment(): ?array
    {
        // This would typically query a deployments log/database
        // For now, return mock data
        
        return [
            'deployment_id' => 'deploy_2024-01-15_14-30-15_abc123',
            'environment' => 'prod',
            'version' => '1.2.3',
            'deployed_at' => '2024-01-15 14:30:15',
            'deployed_by' => 'deployment_system',
            'status' => 'success'
        ];
    }
    
    private function getPendingMigrations(): array
    {
        $migrationsDir = $this->baseDir . '/sql/migrations';
        $appliedMigrations = $this->getAppliedMigrations();
        $pending = [];
        
        if (is_dir($migrationsDir)) {
            $migrations = glob($migrationsDir . '/*.sql');
            
            foreach ($migrations as $migration) {
                $migrationName = basename($migration, '.sql');
                if (!in_array($migrationName, $appliedMigrations)) {
                    $pending[] = $migrationName;
                }
            }
        }
        
        return $pending;
    }
    
    private function getActiveFeatureFlags(): array
    {
        $flagsFile = $this->baseDir . '/config/feature_flags.json';
        
        if (file_exists($flagsFile)) {
            return json_decode(file_get_contents($flagsFile), true) ?? [];
        }
        
        return [];
    }
    
    private function getSystemHealth(): array
    {
        return $this->runHealthCheck('current');
    }
    
    private function getEnvironmentStatus(): array
    {
        return [
            'dev' => ['status' => 'healthy', 'version' => '1.2.3-dev'],
            'staging' => ['status' => 'healthy', 'version' => '1.2.2'],
            'prod' => ['status' => 'healthy', 'version' => '1.2.1']
        ];
    }
    
    private function backupApplicationFiles(string $backupDir): void
    {
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        // In a real implementation, this would backup critical application files
        // For now, just create a placeholder
        file_put_contents($backupDir . '/backup_created.txt', date('Y-m-d H:i:s'));
    }
    
    private function restoreApplicationFiles(string $backupDir, string $environment): void
    {
        // In a real implementation, this would restore application files
        $this->logger->info("Application files restored from {$backupDir}");
    }
    
    private function backupConfiguration(string $backupDir): void
    {
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        // Backup configuration files
        $configFiles = glob($this->baseDir . '/config/*.php');
        
        foreach ($configFiles as $file) {
            $destination = $backupDir . '/' . basename($file);
            copy($file, $destination);
        }
    }
    
    private function restoreConfiguration(string $backupDir, string $environment): void
    {
        $configFiles = glob($backupDir . '/*.php');
        
        foreach ($configFiles as $file) {
            $destination = $this->baseDir . '/config/' . basename($file);
            copy($file, $destination);
        }
    }
    
    private function recursiveDelete(string $pattern): void
    {
        $files = glob($pattern);
        
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->recursiveDelete($file . '/*');
                rmdir($file);
            } else {
                unlink($file);
            }
        }
    }
}

// CLI interface
if (php_sapi_name() === 'cli') {
    $deployment = new DeploymentManager();
    
    $command = $argv[1] ?? 'status';
    
    switch ($command) {
        case 'deploy':
            $environment = $argv[2] ?? 'dev';
            $options = [
                'run_migrations' => ($argv[3] ?? 'true') === 'true'
            ];
            
            echo "Starting deployment to {$environment}...\n";
            $result = $deployment->deploy($environment, $options);
            
            if ($result['success']) {
                echo "✅ Deployment successful!\n";
                echo "Deployment ID: {$result['deployment_id']}\n";
                echo "Duration: {$result['duration_seconds']} seconds\n";
            } else {
                echo "❌ Deployment failed: {$result['error']}\n";
                exit(1);
            }
            break;
            
        case 'rollback':
            $deploymentId = $argv[2] ?? null;
            $environment = $argv[3] ?? 'dev';
            
            if (!$deploymentId) {
                echo "❌ Deployment ID required for rollback\n";
                exit(1);
            }
            
            echo "Starting rollback for deployment {$deploymentId}...\n";
            $result = $deployment->rollback($deploymentId, $environment);
            
            if ($result['success']) {
                echo "✅ Rollback successful!\n";
            } else {
                echo "❌ Rollback failed: {$result['error']}\n";
                exit(1);
            }
            break;
            
        case 'migrate':
            $environment = $argv[2] ?? 'dev';
            
            echo "Running database migrations for {$environment}...\n";
            $result = $deployment->runDatabaseMigrations($environment);
            
            echo "Applied {$result['applied_migrations']}/{$result['total_migrations']} migrations\n";
            
            foreach ($result['results'] as $migration) {
                $status = $migration['status'] === 'success' ? '✅' : '❌';
                echo "{$status} {$migration['migration']}\n";
            }
            break;
            
        case 'flags':
            $action = $argv[2] ?? 'list';
            $flag = $argv[3] ?? null;
            $value = $argv[4] ?? null;
            
            $result = $deployment->manageFeatureFlags($action, $flag, $value);
            
            if ($action === 'list') {
                echo "Feature Flags:\n";
                foreach ($result['flags'] as $flagName => $flagData) {
                    echo "  {$flagName}: {$flagData['value']}\n";
                }
            } else {
                echo $result['message'] . "\n";
            }
            break;
            
        case 'status':
        default:
            $status = $deployment->getDeploymentStatus();
            
            echo "Deployment Status:\n";
            echo "  Current Version: {$status['current_version']}\n";
            echo "  System Health: {$status['system_health']['overall_status']}\n";
            echo "  Pending Migrations: " . count($status['pending_migrations']) . "\n";
            echo "  Active Feature Flags: " . count($status['feature_flags']) . "\n";
            
            if ($status['last_deployment']) {
                $last = $status['last_deployment'];
                echo "  Last Deployment: {$last['deployment_id']} ({$last['environment']}) - {$last['status']}\n";
            }
            break;
    }
}

?>