<?php
/**
 * Sidebar Navigation Component
 *
 * @package Scanner
 * @version 3.0.0
 */
?>
<aside class="sidebar bg-dark text-white" role="navigation">
    <div class="sidebar-header p-3 border-bottom border-secondary">
        <h4 class="mb-0">
            <i class="bi bi-radar text-primary"></i>
            <span class="ms-2">Scanner</span>
        </h4>
        <small class="text-muted d-block">Intelligence Dashboard</small>
    </div>

    <nav class="sidebar-nav p-3">

        <!-- Core Analysis Section -->
        <div class="nav-section mb-4">
            <h6 class="nav-section-title text-uppercase text-muted mb-2">
                <small>Analysis</small>
            </h6>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="?page=overview" class="nav-link <?php echo $page === 'overview' ? 'active' : ''; ?>">
                        <i class="bi bi-speedometer2"></i>
                        <span>Overview</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?page=files" class="nav-link <?php echo $page === 'files' ? 'active' : ''; ?>">
                        <i class="bi bi-file-earmark-code"></i>
                        <span>Files</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?page=dependencies" class="nav-link <?php echo $page === 'dependencies' ? 'active' : ''; ?>">
                        <i class="bi bi-diagram-3"></i>
                        <span>Dependencies</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?page=violations" class="nav-link <?php echo $page === 'violations' ? 'active' : ''; ?>">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span>Violations</span>
                        <?php
                        // Show violation count badge if available
                        try {
                            $violationCount = dbFetchOne(
                                "SELECT COUNT(*) as count FROM rule_violations WHERE project_id = ? AND status = 'open'",
                                [$projectId]
                            );
                            if ($violationCount && $violationCount['count'] > 0) {
                                echo '<span class="badge bg-danger rounded-pill ms-auto">' . $violationCount['count'] . '</span>';
                            }
                        } catch (Exception $e) {
                            // Silently fail
                        }
                        ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?page=rules" class="nav-link <?php echo $page === 'rules' ? 'active' : ''; ?>">
                        <i class="bi bi-list-check"></i>
                        <span>Rules</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?page=metrics" class="nav-link <?php echo $page === 'metrics' ? 'active' : ''; ?>">
                        <i class="bi bi-graph-up"></i>
                        <span>Metrics</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Management Section -->
        <div class="nav-section mb-4">
            <h6 class="nav-section-title text-uppercase text-muted mb-2">
                <small>Management</small>
            </h6>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="?page=projects" class="nav-link <?php echo $page === 'projects' ? 'active' : ''; ?>">
                        <i class="bi bi-folder"></i>
                        <span>Projects</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?page=business-units" class="nav-link <?php echo $page === 'business-units' ? 'active' : ''; ?>">
                        <i class="bi bi-building"></i>
                        <span>Business Units</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?page=scan-config" class="nav-link <?php echo $page === 'scan-config' ? 'active' : ''; ?>">
                        <i class="bi bi-gear"></i>
                        <span>Scan Config</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?page=scan-history" class="nav-link <?php echo $page === 'scan-history' ? 'active' : ''; ?>">
                        <i class="bi bi-clock-history"></i>
                        <span>Scan History</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- System Section -->
        <div class="nav-section">
            <h6 class="nav-section-title text-uppercase text-muted mb-2">
                <small>System</small>
            </h6>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="?page=settings" class="nav-link <?php echo $page === 'settings' ? 'active' : ''; ?>">
                        <i class="bi bi-sliders"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?page=documentation" class="nav-link <?php echo $page === 'documentation' ? 'active' : ''; ?>">
                        <i class="bi bi-book"></i>
                        <span>Documentation</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="?page=support" class="nav-link <?php echo $page === 'support' ? 'active' : ''; ?>">
                        <i class="bi bi-question-circle"></i>
                        <span>Support</span>
                    </a>
                </li>
            </ul>
        </div>

    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer p-3 border-top border-secondary mt-auto">
        <div class="d-flex align-items-center text-muted small">
            <i class="bi bi-info-circle me-2"></i>
            <span>Scanner v<?php echo SCANNER_VERSION; ?></span>
        </div>
    </div>

</aside>

<style>
.sidebar {
    width: 260px;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.sidebar-nav {
    flex: 1;
    overflow-y: auto;
}

.nav-link {
    color: rgba(255, 255, 255, 0.7);
    padding: 0.75rem 1rem;
    border-radius: 0.375rem;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.nav-link:hover {
    color: #fff;
    background-color: rgba(255, 255, 255, 0.1);
}

.nav-link.active {
    color: #fff;
    background-color: rgba(13, 110, 253, 0.25);
    border-left: 3px solid #0d6efd;
}

.nav-link i {
    width: 20px;
    text-align: center;
}

.nav-section-title {
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.05em;
}
</style>
