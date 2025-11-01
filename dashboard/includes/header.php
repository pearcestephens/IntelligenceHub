<?php
/**
 * Dashboard Header
 * Top navigation bar with search, notifications, and user menu
 */
?>
<header class="header">
    <div class="header-left">
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
        <a href="index.php" class="header-logo">
            <i class="fas fa-brain"></i>
            <span><?php echo APP_NAME; ?></span>
        </a>
    </div>

    <div class="header-center">
        <div class="header-search">
            <i class="fas fa-search"></i>
            <input type="text" id="globalSearch" placeholder="Search intelligence... (Ctrl+K)" autocomplete="off">
        </div>
    </div>

    <div class="header-right">
        <!-- Refresh Stats -->
        <div class="header-icon" id="refreshStats" title="Refresh Statistics">
            <i class="fas fa-sync-alt"></i>
        </div>

        <!-- Trigger Scan -->
        <?php if (ENABLE_SYSTEM_SCAN): ?>
        <div class="header-icon" id="triggerScan" title="Trigger System Scan">
            <i class="fas fa-radar"></i>
        </div>
        <?php endif; ?>

        <!-- Notifications -->
        <div class="header-icon dropdown" id="notificationsMenu">
            <i class="fas fa-bell"></i>
            <span class="badge" id="notificationCount">0</span>
        </div>

        <!-- Help -->
        <div class="header-icon" id="helpMenu">
            <i class="fas fa-question-circle"></i>
        </div>

        <!-- User Menu -->
        <div class="dropdown">
            <div class="user-menu dropdown-toggle" id="userMenuToggle" data-bs-toggle="dropdown">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'A', 0, 1)); ?>
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?></div>
                    <div class="user-role">Administrator</div>
                </div>
                <i class="fas fa-chevron-down ms-2"></i>
            </div>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="?page=settings"><i class="fas fa-cog me-2"></i> Settings</a></li>
                <li><a class="dropdown-item" href="?page=profile"><i class="fas fa-user me-2"></i> Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</header>
