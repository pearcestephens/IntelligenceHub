<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bot Deployment Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .stat-card { border-left: 4px solid #0d6efd; }
        .stat-card.success { border-left-color: #198754; }
        .stat-card.warning { border-left-color: #ffc107; }
        .stat-card.danger { border-left-color: #dc3545; }
        .recent-execution { border-left: 3px solid #dee2e6; padding-left: 1rem; margin-bottom: 1rem; }
        .recent-execution.success { border-left-color: #198754; }
        .recent-execution.error { border-left-color: #dc3545; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/dashboard">
                <i class="bi bi-robot"></i> Bot Deployment
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="/dashboard">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard/bots">
                            <i class="bi bi-list-ul"></i> Bots
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard/metrics">
                            <i class="bi bi-graph-up"></i> Metrics
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard/health">
                            <i class="bi bi-heart-pulse"></i> Health
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col">
                <h1 class="h3">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </h1>
                <p class="text-muted">Bot deployment system overview</p>
            </div>
            <div class="col-auto">
                <a href="/dashboard/bots/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create Bot
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Bots</h6>
                                <h2 class="mb-0"><?= $total_bots ?></h2>
                            </div>
                            <div class="text-primary">
                                <i class="bi bi-robot" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Active Bots</h6>
                                <h2 class="mb-0"><?= $active_bots ?></h2>
                            </div>
                            <div class="text-success">
                                <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Scheduled</h6>
                                <h2 class="mb-0"><?= $scheduled_bots ?></h2>
                            </div>
                            <div class="text-warning">
                                <i class="bi bi-clock" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card stat-card danger">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-2">Total Executions</h6>
                                <h2 class="mb-0"><?= number_format($total_executions) ?></h2>
                            </div>
                            <div class="text-info">
                                <i class="bi bi-play-circle" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Executions -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-activity"></i> Recent Executions
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_executions)): ?>
                            <p class="text-muted">No recent executions</p>
                        <?php else: ?>
                            <?php foreach ($recent_executions as $execution): ?>
                                <?php
                                    $statusClass = ($execution['status'] ?? 'success') === 'success' ? 'success' : 'error';
                                    $statusIcon = $statusClass === 'success' ? 'check-circle-fill' : 'x-circle-fill';
                                    $statusColor = $statusClass === 'success' ? 'text-success' : 'text-danger';
                                ?>
                                <div class="recent-execution <?= $statusClass ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                <i class="bi bi-<?= $statusIcon ?> <?= $statusColor ?>"></i>
                                                <?= htmlspecialchars($execution['bot_name']) ?>
                                            </h6>
                                            <p class="text-muted small mb-1">
                                                <?= htmlspecialchars(substr($execution['input_data'], 0, 100)) ?>
                                                <?php if (strlen($execution['input_data']) > 100): ?>...<?php endif; ?>
                                            </p>
                                            <small class="text-muted">
                                                <i class="bi bi-clock"></i> <?= date('Y-m-d H:i:s', strtotime($execution['created_at'])) ?>
                                                | <i class="bi bi-stopwatch"></i> <?= round($execution['execution_time'], 2) ?>ms
                                            </small>
                                        </div>
                                        <div>
                                            <a href="/dashboard/bot?id=<?= $execution['bot_id'] ?>" class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white">
                        <a href="/dashboard/bots" class="text-decoration-none">
                            View all bots <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- System Health -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-heart-pulse"></i> System Health
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Database</span>
                                <span class="badge bg-success">
                                    <?= $system_health['database'] ?>
                                </span>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-success" style="width: 100%"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Memory Usage</span>
                                <span class="badge bg-info">
                                    <?= round($system_health['memory'] / 1024 / 1024, 2) ?> MB
                                </span>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-info" style="width: 45%"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Disk Space</span>
                                <span class="badge bg-warning">
                                    <?= round($system_health['disk'] / 1024 / 1024 / 1024, 2) ?> GB free
                                </span>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-warning" style="width: 70%"></div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <a href="/dashboard/health" class="btn btn-outline-primary btn-sm">
                                Full Health Check
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card mt-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Quick Actions</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="/dashboard/bots/create" class="list-group-item list-group-item-action">
                            <i class="bi bi-plus-circle"></i> Create New Bot
                        </a>
                        <a href="/dashboard/bots" class="list-group-item list-group-item-action">
                            <i class="bi bi-list-ul"></i> View All Bots
                        </a>
                        <a href="/dashboard/metrics" class="list-group-item list-group-item-action">
                            <i class="bi bi-graph-up"></i> View Metrics
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh every 30 seconds
        setTimeout(() => location.reload(), 30000);
    </script>
</body>
</html>
