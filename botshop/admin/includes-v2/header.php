<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="CIS Intelligence Dashboard - Code quality and analysis platform">
    <title><?= $pageTitle ?? 'Dashboard' ?> | CIS Intelligence</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/favicon.png">

    <!-- Font Awesome 6.5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Chart.js 4.x -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <!-- Custom CSS - Design System -->
    <link rel="stylesheet" href="/dashboard/admin/assets/css/design-system.css">
    <link rel="stylesheet" href="/dashboard/admin/assets/css/components.css">
    <link rel="stylesheet" href="/dashboard/admin/assets/css/pages.css">

    <!-- Page-specific CSS -->
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="dashboard-layout">
    <!-- Skip to main content link for accessibility -->
    <a href="#main-content" class="skip-link">Skip to main content</a>

    <!-- Top Navigation Bar -->
    <nav class="navbar" role="navigation" aria-label="Main navigation">
        <div class="navbar__brand hide-md">
            <i class="fas fa-brain"></i>
            <span>CIS Intelligence</span>
        </div>

        <button class="btn btn--ghost btn--icon show-md" data-mobile-toggle aria-label="Toggle mobile menu">
            <i class="fas fa-bars"></i>
        </button>

        <div class="navbar__search hide-sm">
            <i class="fas fa-search navbar__search-icon" aria-hidden="true"></i>
            <input
                type="search"
                class="navbar__search-input"
                placeholder="Search files, rules, metrics..."
                aria-label="Search dashboard"
                data-search-target=".searchable-item"
            >
        </div>

        <div class="navbar__actions">
            <!-- Notifications -->
            <button class="btn btn--ghost btn--icon" data-tooltip="Notifications" aria-label="Notifications">
                <i class="fas fa-bell"></i>
                <span class="badge badge--danger" style="position: absolute; top: 8px; right: 8px; font-size: 0.6rem; padding: 0.15rem 0.35rem;">3</span>
            </button>

            <!-- Settings -->
            <a href="?page=settings" class="btn btn--ghost btn--icon" data-tooltip="Settings" aria-label="Settings">
                <i class="fas fa-cog"></i>
            </a>

            <!-- Help -->
            <button class="btn btn--ghost btn--icon" data-tooltip="Help" aria-label="Help">
                <i class="fas fa-question-circle"></i>
            </button>

            <!-- User Profile -->
            <div class="dropdown">
                <button class="btn btn--ghost btn--icon" aria-label="User menu" aria-haspopup="true">
                    <i class="fas fa-user-circle"></i>
                </button>
            </div>
        </div>
    </nav>
