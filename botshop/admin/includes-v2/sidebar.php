    <!-- Sidebar Navigation -->
    <aside class="sidebar" role="complementary" aria-label="Sidebar navigation">
        <div class="sidebar__header">
            <a href="?page=overview" class="sidebar__logo">
                <i class="fas fa-brain"></i>
                <span>CIS Intelligence</span>
            </a>
        </div>

        <nav class="sidebar__nav" aria-label="Primary">
            <!-- Main Section -->
            <div class="sidebar__section">
                <div class="sidebar__section-title">Main</div>

                <a href="?page=overview" class="sidebar__link sidebar__link--active" data-page="overview">
                    <i class="fas fa-th-large sidebar__link-icon" aria-hidden="true"></i>
                    <span>Overview</span>
                </a>

                <a href="?page=files" class="sidebar__link" data-page="files">
                    <i class="fas fa-file-code sidebar__link-icon" aria-hidden="true"></i>
                    <span>Files</span>
                </a>

                <a href="?page=dependencies" class="sidebar__link" data-page="dependencies">
                    <i class="fas fa-project-diagram sidebar__link-icon" aria-hidden="true"></i>
                    <span>Dependencies</span>
                </a>

                <a href="?page=violations" class="sidebar__link" data-page="violations">
                    <i class="fas fa-exclamation-triangle sidebar__link-icon" aria-hidden="true"></i>
                    <span>Violations</span>
                    <?php if (isset($violationCount) && $violationCount > 0): ?>
                        <span class="sidebar__badge"><?= $violationCount ?></span>
                    <?php endif; ?>
                </a>
            </div>

            <!-- Analysis Section -->
            <div class="sidebar__section">
                <div class="sidebar__section-title">Analysis</div>

                <a href="?page=metrics" class="sidebar__link" data-page="metrics">
                    <i class="fas fa-chart-line sidebar__link-icon" aria-hidden="true"></i>
                    <span>Metrics</span>
                </a>

                <a href="?page=rules" class="sidebar__link" data-page="rules">
                    <i class="fas fa-clipboard-list sidebar__link-icon" aria-hidden="true"></i>
                    <span>Rules</span>
                </a>

                <a href="?page=scan-history" class="sidebar__link" data-page="scan-history">
                    <i class="fas fa-history sidebar__link-icon" aria-hidden="true"></i>
                    <span>Scan History</span>
                </a>
            </div>

            <!-- Configuration Section -->
            <div class="sidebar__section">
                <div class="sidebar__section-title">Configuration</div>

                <a href="?page=projects" class="sidebar__link" data-page="projects">
                    <i class="fas fa-folder sidebar__link-icon" aria-hidden="true"></i>
                    <span>Projects</span>
                </a>

                <a href="?page=business-units" class="sidebar__link" data-page="business-units">
                    <i class="fas fa-building sidebar__link-icon" aria-hidden="true"></i>
                    <span>Business Units</span>
                </a>

                <a href="?page=scan-config" class="sidebar__link" data-page="scan-config">
                    <i class="fas fa-cog sidebar__link-icon" aria-hidden="true"></i>
                    <span>Scan Config</span>
                </a>

                <a href="?page=settings" class="sidebar__link" data-page="settings">
                    <i class="fas fa-sliders-h sidebar__link-icon" aria-hidden="true"></i>
                    <span>Settings</span>
                </a>
            </div>

            <!-- Help Section -->
            <div class="sidebar__section">
                <div class="sidebar__section-title">Resources</div>

                <a href="?page=documentation" class="sidebar__link" data-page="documentation">
                    <i class="fas fa-book sidebar__link-icon" aria-hidden="true"></i>
                    <span>Documentation</span>
                </a>

                <a href="?page=support" class="sidebar__link" data-page="support">
                    <i class="fas fa-life-ring sidebar__link-icon" aria-hidden="true"></i>
                    <span>Support</span>
                </a>
            </div>
        </nav>
    </aside>
