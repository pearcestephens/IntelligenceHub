<?php
/**
 * Breadcrumb Navigation
 */

$breadcrumbs = [
    'overview' => ['Dashboard', 'Overview'],
    'search' => ['Intelligence', 'Search'],
    'files' => ['Intelligence', 'File Browser'],
    'functions' => ['Intelligence', 'Function Explorer'],
    'analytics' => ['Intelligence', 'Analytics & Insights'],
    'patterns' => ['Intelligence', 'Pattern Recognition'],
    'neural' => ['Intelligence', 'Neural Networks'],
    'conversations' => ['Intelligence', 'Conversations'],
    'servers' => ['System', 'Server Management'],
    'scanner' => ['System', 'Neural Scanner'],
    'logs' => ['System', 'System Logs'],
    'api' => ['System', 'API Management'],
    'bot-commands' => ['Tools', 'Bot Commands'],
    'sql-query' => ['Tools', 'SQL Query Tool'],
    'documentation' => ['Tools', 'Documentation'],
    'settings' => ['Configuration', 'Settings']
];

$currentBreadcrumb = $breadcrumbs[$page] ?? ['Dashboard', 'Page'];
?>
<nav aria-label="breadcrumb" class="breadcrumb-wrapper">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
        <?php if (isset($currentBreadcrumb[0])): ?>
        <li class="breadcrumb-item"><?php echo htmlspecialchars($currentBreadcrumb[0]); ?></li>
        <?php endif; ?>
        <?php if (isset($currentBreadcrumb[1])): ?>
        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($currentBreadcrumb[1]); ?></li>
        <?php endif; ?>
    </ol>
</nav>
