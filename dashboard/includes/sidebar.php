<?php
/**
 * Dashboard Sidebar
 * Main navigation menu
 */
?>
<aside class="sidebar" id="sidebar">
    <nav class="sidebar-menu">

        <!-- Main Section -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">Main</div>

            <a href="?page=overview" class="sidebar-item <?php echo $page === 'overview' ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>

            <a href="?page=search" class="sidebar-item <?php echo $page === 'search' ? 'active' : ''; ?>">
                <i class="fas fa-search"></i>
                <span>Intelligence Search</span>
            </a>

            <a href="?page=files" class="sidebar-item <?php echo $page === 'files' ? 'active' : ''; ?>">
                <i class="fas fa-folder-open"></i>
                <span>File Browser</span>
            </a>

            <a href="?page=functions" class="sidebar-item <?php echo $page === 'functions' ? 'active' : ''; ?>">
                <i class="fas fa-code"></i>
                <span>Function Explorer</span>
            </a>
        </div>

        <!-- Intelligence Section -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">Intelligence</div>

            <a href="?page=analytics" class="sidebar-item <?php echo $page === 'analytics' ? 'active' : ''; ?>">
                <i class="fas fa-chart-line"></i>
                <span>Analytics & Insights</span>
            </a>

            <a href="?page=patterns" class="sidebar-item <?php echo $page === 'patterns' ? 'active' : ''; ?>">
                <i class="fas fa-project-diagram"></i>
                <span>Pattern Recognition</span>
            </a>

            <a href="?page=neural" class="sidebar-item <?php echo $page === 'neural' ? 'active' : ''; ?>">
                <i class="fas fa-brain"></i>
                <span>Neural Networks</span>
            </a>

            <a href="?page=conversations" class="sidebar-item <?php echo $page === 'conversations' ? 'active' : ''; ?>">
                <i class="fas fa-comments"></i>
                <span>Conversations</span>
            </a>
        </div>

        <!-- System Section -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">System</div>

            <a href="?page=servers" class="sidebar-item <?php echo $page === 'servers' ? 'active' : ''; ?>">
                <i class="fas fa-server"></i>
                <span>Server Management</span>
            </a>

            <a href="?page=cron" class="sidebar-item <?php echo $page === 'cron' ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i>
                <span>Cron Management</span>
            </a>

            <a href="?page=scanner" class="sidebar-item <?php echo $page === 'scanner' ? 'active' : ''; ?>">
                <i class="fas fa-radar"></i>
                <span>Neural Scanner</span>
            </a>

            <a href="?page=logs" class="sidebar-item <?php echo $page === 'logs' ? 'active' : ''; ?>">
                <i class="fas fa-file-alt"></i>
                <span>System Logs</span>
            </a>

            <a href="?page=api" class="sidebar-item <?php echo $page === 'api' ? 'active' : ''; ?>">
                <i class="fas fa-plug"></i>
                <span>API Management</span>
            </a>
        </div>

        <!-- Tools Section -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">Tools</div>

            <a href="?page=ai-control-center" class="sidebar-item <?php echo $page === 'ai-control-center' ? 'active' : ''; ?>">
                <i class="fas fa-rocket"></i>
                <span>🤖 AI Control Center</span>
                <span class="badge bg-gradient ms-auto">MEGA</span>
            </a>

            <a href="?page=ai-control-center" class="sidebar-item <?php echo $page === 'ai-control-center' ? 'active' : ''; ?>">
                <i class="fas fa-rocket"></i>
                <span>🤖 AI Control Center</span>
                <span class="badge">MEGA</span>
            </a>

            <a href="?page=ai-agent" class="sidebar-item <?php echo $page === 'ai-agent' ? 'active' : ''; ?>">
                <i class="fas fa-robot"></i>
                <span>AI Agent Dashboard</span>
                <span class="badge">NEW</span>
            </a>

            <a href="?page=ai-chat" class="sidebar-item <?php echo $page === 'ai-chat' ? 'active' : ''; ?>">
                <i class="fas fa-comment"></i>
                <span>AI Chat Interface</span>
            </a>

            <a href="?page=crawler-monitor" class="sidebar-item <?php echo $page === 'crawler-monitor' ? 'active' : ''; ?>">
                <i class="fas fa-spider"></i>
                <span>Web Crawler Monitor</span>
            </a>

            <a href="?page=mcp-tools" class="sidebar-item <?php echo $page === 'mcp-tools' ? 'active' : ''; ?>">
                <i class="fas fa-tools"></i>
                <span>MCP Tools Testing</span>
                <span class="badge">13</span>
            </a>

            <a href="?page=bot-standards" class="sidebar-item <?php echo $page === 'bot-standards' ? 'active' : ''; ?>">
                <i class="fas fa-robot"></i>
                <span>Bot Standards Manager</span>
                <span class="badge">NEW</span>
            </a>

            <a href="?page=bot-commands" class="sidebar-item <?php echo $page === 'bot-commands' ? 'active' : ''; ?>">
                <i class="fas fa-terminal"></i>
                <span>Bot Commands</span>
            </a>

            <a href="?page=sql-query" class="sidebar-item <?php echo $page === 'sql-query' ? 'active' : ''; ?>">
                <i class="fas fa-database"></i>
                <span>SQL Query Tool</span>
            </a>

            <a href="?page=cleanup" class="sidebar-item <?php echo $page === 'cleanup' ? 'active' : ''; ?>">
                <i class="fas fa-broom"></i>
                <span>Database Cleanup</span>
            </a>

            <a href="?page=documentation" class="sidebar-item <?php echo $page === 'documentation' ? 'active' : ''; ?>">
                <i class="fas fa-book"></i>
                <span>Documentation</span>
            </a>
        </div>

        <!-- Settings Section -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">Configuration</div>

            <a href="?page=settings" class="sidebar-item <?php echo $page === 'settings' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </div>

    </nav>
</aside>
