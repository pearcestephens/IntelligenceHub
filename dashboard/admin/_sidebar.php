<?php
/**
 * _sidebar.php - Left sidebar navigation
 */
$currentPage = $_GET['page'] ?? 'overview';
?>
<aside class="sidebar">
    <div class="sidebar-header p-3 border-bottom">
        <h5 class="mb-0">
            <a href="?" class="text-decoration-none text-dark fw-bold">
                📊 Dashboard
            </a>
        </h5>
    </div>

    <nav class="sidebar-menu">
        <!-- Main Section -->
        <div class="sidebar-section">
            <span class="sidebar-section-title">Navigation</span>

            <li class="sidebar-menu-item <?php echo $currentPage === 'overview' ? 'active' : ''; ?>">
                <a href="?page=overview" class="sidebar-menu-link" data-page="overview">
                    <i class="icon">📈</i>
                    <span>Overview</span>
                </a>
            </li>

            <li class="sidebar-menu-item <?php echo $currentPage === 'files' ? 'active' : ''; ?>">
                <a href="?page=files" class="sidebar-menu-link" data-page="files">
                    <i class="icon">📁</i>
                    <span>Files</span>
                </a>
            </li>

            <li class="sidebar-menu-item <?php echo $currentPage === 'dependencies' ? 'active' : ''; ?>">
                <a href="?page=dependencies" class="sidebar-menu-link" data-page="dependencies">
                    <i class="icon">🔗</i>
                    <span>Dependencies</span>
                </a>
            </li>
        </div>

        <!-- Analysis Section -->
        <div class="sidebar-section">
            <span class="sidebar-section-title">Analysis</span>

            <li class="sidebar-menu-item <?php echo $currentPage === 'violations' ? 'active' : ''; ?>">
                <a href="?page=violations" class="sidebar-menu-link" data-page="violations">
                    <i class="icon">⚠️</i>
                    <span>Violations</span>
                </a>
            </li>

            <li class="sidebar-menu-item <?php echo $currentPage === 'rules' ? 'active' : ''; ?>">
                <a href="?page=rules" class="sidebar-menu-link" data-page="rules">
                    <i class="icon">📋</i>
                    <span>Rules</span>
                </a>
            </li>

            <li class="sidebar-menu-item <?php echo $currentPage === 'metrics' ? 'active' : ''; ?>">
                <a href="?page=metrics" class="sidebar-menu-link" data-page="metrics">
                    <i class="icon">📊</i>
                    <span>Metrics</span>
                </a>
            </li>
        </div>

        <!-- Management Section -->
        <div class="sidebar-section">
            <span class="sidebar-section-title">Management</span>

            <li class="sidebar-menu-item <?php echo $currentPage === 'projects' ? 'active' : ''; ?>">
                <a href="/dashboard/admin/management.php?page=projects" class="sidebar-menu-link" data-page="projects">
                    <i class="icon">📂</i>
                    <span>Projects</span>
                </a>
            </li>

            <li class="sidebar-menu-item <?php echo $currentPage === 'business-units' ? 'active' : ''; ?>">
                <a href="/dashboard/admin/management.php?page=business-units" class="sidebar-menu-link" data-page="business-units">
                    <i class="icon">🏢</i>
                    <span>Business Units</span>
                </a>
            </li>

            <li class="sidebar-menu-item <?php echo $currentPage === 'scan-config' ? 'active' : ''; ?>">
                <a href="/dashboard/admin/management.php?page=scan-config" class="sidebar-menu-link" data-page="scan-config">
                    <i class="icon">⚙️</i>
                    <span>Scan Config</span>
                </a>
            </li>

            <li class="sidebar-menu-item <?php echo $currentPage === 'scan-history' ? 'active' : ''; ?>">
                <a href="/dashboard/admin/management.php?page=scan-history" class="sidebar-menu-link" data-page="scan-history">
                    <i class="icon">📜</i>
                    <span>Scan History</span>
                </a>
            </li>
        </div>

        <!-- Settings Section -->
        <div class="sidebar-section">
            <span class="sidebar-section-title">Configuration</span>

            <li class="sidebar-menu-item <?php echo $currentPage === 'ai-agent' ? 'active' : ''; ?>">
                <a href="?page=ai-agent" class="sidebar-menu-link" data-page="ai-agent">
                    <i class="icon">🤖</i>
                    <span>AI Agent</span>
                </a>
            </li>

            <li class="sidebar-menu-item <?php echo $currentPage === 'settings' ? 'active' : ''; ?>">
                <a href="?page=settings" class="sidebar-menu-link" data-page="settings">
                    <i class="icon">⚙️</i>
                    <span>Settings</span>
                </a>
            </li>
        </div>
    </nav>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer p-3 border-top mt-auto" style="border-top: 1px solid var(--border-color); margin-top: auto;">
        <small class="text-muted">
            Version 1.0.0<br>
            <span id="last-updated">Loading...</span>
        </small>
    </div>
</aside>

<script>
// Update last updated time
document.addEventListener('DOMContentLoaded', () => {
    const lastUpdated = new Date().toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
    document.getElementById('last-updated').textContent = 'Updated: ' + lastUpdated;
});
</script>
