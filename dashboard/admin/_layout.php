<?php
/**
 * _layout.php - Main layout template
 */
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' : ''; ?>Dashboard</title>

    <!-- CSS Files (auto-included) -->
    <link rel="stylesheet" href="/dashboard/admin/assets/css/01-base.css">
    <link rel="stylesheet" href="/dashboard/admin/assets/css/02-cards.css">
    <link rel="stylesheet" href="/dashboard/admin/assets/css/03-tables.css">
    <link rel="stylesheet" href="/dashboard/admin/assets/css/04-forms.css">
    <link rel="stylesheet" href="/dashboard/admin/assets/css/05-buttons.css">
    <link rel="stylesheet" href="/dashboard/admin/assets/css/06-modals.css">
    <link rel="stylesheet" href="/dashboard/admin/assets/css/07-animations.css">
    <link rel="stylesheet" href="/dashboard/admin/assets/css/08-navigation.css">
    <link rel="stylesheet" href="/dashboard/admin/assets/css/09-responsive.css">
    <link rel="stylesheet" href="/dashboard/admin/assets/css/10-utilities.css">
</head>
<body>
    <div class="app-wrapper d-flex">
        <!-- Sidebar -->
        <?php include '_sidebar.php'; ?>

        <div class="app-main flex-fill">
            <!-- Navigation -->
            <?php include '_nav.php'; ?>

            <!-- Main Content -->
            <main class="page-content">
                <div class="container-fluid p-4">
                    <?php if (isset($breadcrumbs)): ?>
                        <nav aria-label="breadcrumb" class="mb-3">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="?page=overview">Home</a></li>
                                <?php foreach ($breadcrumbs as $breadcrumb): ?>
                                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($breadcrumb); ?></li>
                                <?php endforeach; ?>
                            </ol>
                        </nav>
                    <?php endif; ?>

                    <!-- Page Content -->
                    <?php echo $pageContent ?? ''; ?>
                </div>
            </main>

            <!-- Footer -->
            <?php include '_footer.php'; ?>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;"></div>

    <!-- JavaScript Files (auto-included in order) -->
    <script src="/dashboard/admin/assets/js/01-utils.js"></script>
    <script src="/dashboard/admin/assets/js/02-api.js"></script>
    <script src="/dashboard/admin/assets/js/03-tables.js"></script>
    <script src="/dashboard/admin/assets/js/04-modals.js"></script>
    <script src="/dashboard/admin/assets/js/05-notifications.js"></script>
    <script src="/dashboard/admin/assets/js/06-storage.js"></script>
    <script src="/dashboard/admin/assets/js/07-forms.js"></script>
    <script src="/dashboard/admin/assets/js/08-navigation.js"></script>
    <script src="/dashboard/admin/assets/js/09-charts.js"></script>
    <script src="/dashboard/admin/assets/js/10-init.js"></script>
</body>
</html>
