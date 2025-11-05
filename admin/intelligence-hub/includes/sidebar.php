    <aside class="sidebar" role="complementary" aria-label="Sidebar navigation">
        <div class="sidebar__header">
            <a href="?page=overview" class="sidebar__logo">
                <i class="fas fa-robot"></i>
                <span>Intelligence Hub</span>
            </a>
        </div>

        <nav class="sidebar__nav" aria-label="Primary">
            <!-- AI Section -->
            <div class="sidebar__section">
                <div class="sidebar__section-title">AI Control</div>

                <a href="?page=overview" class="sidebar__link <?= ($currentPage === 'overview') ? 'sidebar__link--active' : '' ?>" data-page="overview">
                    <i class="fas fa-th-large sidebar__link-icon" aria-hidden="true"></i>
                    <span>Overview</span>
                </a>

                <a href="?page=agents" class="sidebar__link <?= ($currentPage === 'agents') ? 'sidebar__link--active' : '' ?>" data-page="agents">
                    <i class="fas fa-robot sidebar__link-icon" aria-hidden="true"></i>
                    <span>Agents</span>
                    <span class="sidebar__badge sidebar__badge--success">9</span>
                </a>

                <a href="?page=decisions" class="sidebar__link <?= ($currentPage === 'decisions') ? 'sidebar__link--active' : '' ?>" data-page="decisions">
                    <i class="fas fa-brain sidebar__link-icon" aria-hidden="true"></i>
                    <span>AI Decisions</span>
                </a>

                <a href="?page=automation" class="sidebar__link <?= ($currentPage === 'automation') ? 'sidebar__link--active' : '' ?>" data-page="automation">
                    <i class="fas fa-bolt sidebar__link-icon" aria-hidden="true"></i>
                    <span>Automation Rules</span>
                </a>
            </div>

            <!-- Business Operations -->
            <div class="sidebar__section">
                <div class="sidebar__section-title">Operations</div>

                <a href="?page=inventory" class="sidebar__link <?= ($currentPage === 'inventory') ? 'sidebar__link--active' : '' ?>" data-page="inventory">
                    <i class="fas fa-boxes sidebar__link-icon" aria-hidden="true"></i>
                    <span>Inventory</span>
                </a>

                <a href="?page=sales" class="sidebar__link <?= ($currentPage === 'sales') ? 'sidebar__link--active' : '' ?>" data-page="sales">
                    <i class="fas fa-chart-line sidebar__link-icon" aria-hidden="true"></i>
                    <span>Sales</span>
                </a>

                <a href="?page=security" class="sidebar__link <?= ($currentPage === 'security') ? 'sidebar__link--active' : '' ?>" data-page="security">
                    <i class="fas fa-shield-alt sidebar__link-icon" aria-hidden="true"></i>
                    <span>Security</span>
                </a>

                <a href="?page=web-monitor" class="sidebar__link <?= ($currentPage === 'web-monitor') ? 'sidebar__link--active' : '' ?>" data-page="web-monitor">
                    <i class="fas fa-globe sidebar__link-icon" aria-hidden="true"></i>
                    <span>Web Monitor</span>
                </a>
            </div>

            <!-- Analytics & Insights -->
            <div class="sidebar__section">
                <div class="sidebar__section-title">Insights</div>

                <a href="?page=analytics" class="sidebar__link <?= ($currentPage === 'analytics') ? 'sidebar__link--active' : '' ?>" data-page="analytics">
                    <i class="fas fa-chart-bar sidebar__link-icon" aria-hidden="true"></i>
                    <span>Analytics</span>
                </a>

                <a href="?page=reports" class="sidebar__link <?= ($currentPage === 'reports') ? 'sidebar__link--active' : '' ?>" data-page="reports">
                    <i class="fas fa-file-chart-line sidebar__link-icon" aria-hidden="true"></i>
                    <span>Reports</span>
                </a>

                <a href="?page=forecasting" class="sidebar__link <?= ($currentPage === 'forecasting') ? 'sidebar__link--active' : '' ?>" data-page="forecasting">
                    <i class="fas fa-crystal-ball sidebar__link-icon" aria-hidden="true"></i>
                    <span>Forecasting</span>
                </a>
            </div>

            <!-- System -->
            <div class="sidebar__section">
                <div class="sidebar__section-title">System</div>

                <a href="?page=logs" class="sidebar__link <?= ($currentPage === 'logs') ? 'sidebar__link--active' : '' ?>" data-page="logs">
                    <i class="fas fa-file-alt sidebar__link-icon" aria-hidden="true"></i>
                    <span>Logs</span>
                </a>

                <a href="?page=settings" class="sidebar__link <?= ($currentPage === 'settings') ? 'sidebar__link--active' : '' ?>" data-page="settings">
                    <i class="fas fa-cog sidebar__link-icon" aria-hidden="true"></i>
                    <span>Settings</span>
                </a>
            </div>
        </nav>

        <div class="sidebar__footer">
            <div class="sidebar__status">
                <div class="status-indicator status-indicator--online"></div>
                <div class="sidebar__status-text">
                    <div class="sidebar__status-label">System Status</div>
                    <div class="sidebar__status-value">All Systems Operational</div>
                </div>
            </div>
        </div>
    </aside>
