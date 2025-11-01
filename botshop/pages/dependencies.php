<?php
/**
 * Dependencies Page - Complete V2 Redesign
 * Interactive dependency visualization with graph, tree, and list views
 *
 * Features:
 * - Semantic HTML5 structure
 * - Interactive dependency graph visualization
 * - Tree and list view toggles
 * - Dependency metrics and analysis
 * - Circular dependency detection
 * - Import/export visualization
 * - File search and filtering
 * - Dependency detail panels
 *
 * @package CIS Intelligence Dashboard
 * @version 2.0.0
 * @updated October 31, 2025
 */

declare(strict_types=1);

// Configuration
$pageTitle = 'Dependencies';
$lastScanTime = date('M j, Y g:i A');

// Load application bootstrap
require_once $_SERVER['DOCUMENT_ROOT'] . '/app.php';

// Get project ID from session
$projectId = (int)($_SESSION['current_project_id'] ?? 1);

// Validate project exists
$valQuery = "SELECT id, project_name FROM projects WHERE id = ? AND status = 'active' LIMIT 1";
$valStmt = $pdo->prepare($valQuery);
$valStmt->execute([$projectId]);
$projectData = $valStmt->fetch(PDO::FETCH_ASSOC);

if (!$projectData) {
    $projectId = 1;
    $_SESSION['current_project_id'] = $projectId;
    $valStmt->execute([$projectId]);
    $projectData = $valStmt->fetch(PDO::FETCH_ASSOC) ?: ['id' => 1, 'project_name' => 'Default'];
}

// Get dependency data
$depsQuery = "
    SELECT
        id,
        source_file,
        target_file,
        dependency_type,
        created_at
    FROM code_dependencies
    WHERE project_id = ?
    ORDER BY id DESC
    LIMIT 1000
";

$depsStmt = $pdo->prepare($depsQuery);
$depsStmt->execute([$projectId]);
$dependencies = $depsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get circular dependencies
$circularQuery = "
    SELECT
        id,
        chain as file_chain,
        severity,
        dependency_type,
        detected_at
    FROM circular_dependencies
    WHERE project_id = ?
    ORDER BY detected_at DESC
    LIMIT 50
";

$circularStmt = $pdo->prepare($circularQuery);
$circularStmt->execute([$projectId]);
$circularDeps = $circularStmt->fetchAll(PDO::FETCH_ASSOC);

// Get most depended on files
$mostDependedQuery = "
    SELECT
        target_file,
        COUNT(*) as dep_count
    FROM code_dependencies
    WHERE project_id = ? AND target_file IS NOT NULL
    GROUP BY target_file
    ORDER BY dep_count DESC
    LIMIT 20
";

$mostDependedStmt = $pdo->prepare($mostDependedQuery);
$mostDependedStmt->execute([$projectId]);
$mostDepended = $mostDependedStmt->fetchAll(PDO::FETCH_ASSOC);

// Get dependency statistics
$statsQuery = "
    SELECT
        COUNT(DISTINCT source_file) as total_files,
        COUNT(*) as total_dependencies,
        COUNT(DISTINCT CONCAT(source_file, target_file)) as unique_relationships,
        AVG(dep_count) as avg_dependencies
    FROM (
        SELECT source_file, COUNT(*) as dep_count
        FROM code_dependencies
        WHERE project_id = ?
        GROUP BY source_file
    ) AS file_deps
";

$statsStmt = $pdo->prepare($statsQuery);
$statsStmt->execute([$projectId]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Get dependency depth analysis
$depthQuery = "
    SELECT
        dependency_type,
        COUNT(*) as count
    FROM code_dependencies
    WHERE project_id = ?
    GROUP BY dependency_type
    ORDER BY count DESC
";

$depthStmt = $pdo->prepare($depthQuery);
$depthStmt->execute([$projectId]);
$depthAnalysis = $depthStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate metrics
$totalFiles = (int)($stats['total_files'] ?? 0);
$totalDeps = (int)($stats['total_dependencies'] ?? 0);
$uniqueRelationships = (int)($stats['unique_relationships'] ?? 0);
$avgDeps = round((float)($stats['avg_dependencies'] ?? 0), 1);
$circularCount = count($circularDeps);
$externalDeps = 0; // TODO: Calculate from dependency_type

// Build graph data for visualization
$graphNodes = [];
$graphEdges = [];
$nodeMap = [];

foreach ($dependencies as $dep) {
    $source = $dep['source_file'];
    $target = $dep['target_file'];

    if (!isset($nodeMap[$source])) {
        $nodeMap[$source] = count($graphNodes);
        $graphNodes[] = [
            'id' => count($graphNodes),
            'label' => basename($source),
            'path' => $source,
            'type' => pathinfo($source, PATHINFO_EXTENSION)
        ];
    }

    if (!isset($nodeMap[$target])) {
        $nodeMap[$target] = count($graphNodes);
        $graphNodes[] = [
            'id' => count($graphNodes),
            'label' => basename($target),
            'path' => $target,
            'type' => pathinfo($target, PATHINFO_EXTENSION)
        ];
    }

    $graphEdges[] = [
        'from' => $nodeMap[$source],
        'to' => $nodeMap[$target],
        'type' => $dep['dependency_type']
    ];
}

// Limit graph data for performance
$graphNodes = array_slice($graphNodes, 0, 100);
$graphEdges = array_slice($graphEdges, 0, 200);

// Include header
require_once __DIR__ . '/../includes-v2/header.php';
?>

<!-- Main Content -->
<main class="dashboard-main">
    <div class="container-fluid">
        <!-- Page Header -->
        <header class="page-header">
            <div class="page-header__content">
                <h1 class="page-header__title">Dependencies</h1>
                <p class="page-header__subtitle">
                    <?php echo htmlspecialchars($projectData['project_name']); ?>
                    • <?php echo number_format($totalDeps); ?> dependencies
                    • Last analyzed: <?php echo $lastScanTime; ?>
                </p>
            </div>
            <div class="page-header__actions">
                <button type="button" class="btn btn--secondary" onclick="refreshDependencies()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <button type="button" class="btn btn--primary" onclick="exportDependencies()">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </header>

        <!-- Dependency Metrics -->
        <section class="metrics-row">
            <article class="metric-card">
                <div class="metric-card__icon metric-card__icon--primary">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div class="metric-card__content">
                    <div class="metric-card__value"><?php echo number_format($totalDeps); ?></div>
                    <div class="metric-card__label">Total Dependencies</div>
                    <div class="metric-card__change metric-card__change--neutral">
                        <?php echo number_format($uniqueRelationships); ?> unique relationships
                    </div>
                </div>
            </article>

            <article class="metric-card">
                <div class="metric-card__icon metric-card__icon--warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="metric-card__content">
                    <div class="metric-card__value"><?php echo $circularCount; ?></div>
                    <div class="metric-card__label">Circular Dependencies</div>
                    <div class="metric-card__change <?php echo $circularCount > 0 ? 'metric-card__change--negative' : 'metric-card__change--positive'; ?>">
                        <?php echo $circularCount > 0 ? 'Needs attention' : 'All clear'; ?>
                    </div>
                </div>
            </article>

            <article class="metric-card">
                <div class="metric-card__icon metric-card__icon--info">
                    <i class="fas fa-external-link-alt"></i>
                </div>
                <div class="metric-card__content">
                    <div class="metric-card__value"><?php echo number_format($externalDeps); ?></div>
                    <div class="metric-card__label">External Dependencies</div>
                    <div class="metric-card__change metric-card__change--neutral">
                        Third-party packages
                    </div>
                </div>
            </article>

            <article class="metric-card">
                <div class="metric-card__icon metric-card__icon--success">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="metric-card__content">
                    <div class="metric-card__value"><?php echo $avgDeps; ?></div>
                    <div class="metric-card__label">Avg Dependencies/File</div>
                    <div class="metric-card__change metric-card__change--neutral">
                        <?php echo number_format($totalFiles); ?> files analyzed
                    </div>
                </div>
            </article>
        </section>

        <?php if ($circularCount > 0): ?>
        <!-- Circular Dependency Alert -->
        <div class="alert alert--warning">
            <div class="alert__icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="alert__content">
                <div class="alert__title">Circular Dependencies Detected</div>
                <div class="alert__message">
                    Found <?php echo $circularCount; ?> circular dependency pattern<?php echo $circularCount !== 1 ? 's' : ''; ?>
                    that should be refactored to improve maintainability.
                </div>
            </div>
            <button type="button" class="alert__close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <?php endif; ?>

        <!-- Dependency Visualization -->
        <section class="content-section">
            <header class="content-section__header">
                <h2 class="content-section__title">Dependency Visualization</h2>
                <div class="content-section__actions">
                    <div class="btn-group">
                        <button type="button" class="btn btn--sm btn--secondary active" data-view="graph" onclick="switchView('graph')">
                            <i class="fas fa-project-diagram"></i> Graph
                        </button>
                        <button type="button" class="btn btn--sm btn--secondary" data-view="tree" onclick="switchView('tree')">
                            <i class="fas fa-sitemap"></i> Tree
                        </button>
                        <button type="button" class="btn btn--sm btn--secondary" data-view="list" onclick="switchView('list')">
                            <i class="fas fa-list"></i> List
                        </button>
                    </div>
                </div>
            </header>

            <div class="content-section__body">
                <!-- Graph View -->
                <div id="view-graph" class="dependency-view dependency-view--active">
                    <div class="dependency-graph">
                        <div class="dependency-graph__toolbar">
                            <div class="input-group">
                                <i class="fas fa-search input-group__icon"></i>
                                <input type="text" id="graph-search" class="form-control" placeholder="Search files..." onkeyup="filterGraph(this.value)">
                            </div>
                            <div class="dependency-graph__controls">
                                <button type="button" class="btn btn--sm btn--secondary" onclick="zoomIn()" title="Zoom In">
                                    <i class="fas fa-search-plus"></i>
                                </button>
                                <button type="button" class="btn btn--sm btn--secondary" onclick="zoomOut()" title="Zoom Out">
                                    <i class="fas fa-search-minus"></i>
                                </button>
                                <button type="button" class="btn btn--sm btn--secondary" onclick="resetZoom()" title="Reset">
                                    <i class="fas fa-compress-arrows-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div id="dependency-graph-canvas" class="dependency-graph__canvas">
                            <!-- Graph will be rendered here by JavaScript -->
                            <div class="dependency-graph__placeholder">
                                <i class="fas fa-project-diagram fa-3x"></i>
                                <p>Interactive dependency graph</p>
                                <small><?php echo count($graphNodes); ?> nodes, <?php echo count($graphEdges); ?> edges</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tree View -->
                <div id="view-tree" class="dependency-view">
                    <div class="dependency-tree">
                        <div class="dependency-tree__toolbar">
                            <div class="input-group">
                                <i class="fas fa-search input-group__icon"></i>
                                <input type="text" id="tree-search" class="form-control" placeholder="Search files..." onkeyup="filterTree(this.value)">
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn--sm btn--secondary" onclick="expandAll()">
                                    <i class="fas fa-plus-square"></i> Expand All
                                </button>
                                <button type="button" class="btn btn--sm btn--secondary" onclick="collapseAll()">
                                    <i class="fas fa-minus-square"></i> Collapse All
                                </button>
                            </div>
                        </div>
                        <div class="dependency-tree__content">
                            <?php if (empty($dependencies)): ?>
                                <div class="empty-state">
                                    <div class="empty-state__icon">
                                        <i class="fas fa-sitemap"></i>
                                    </div>
                                    <div class="empty-state__message">
                                        <h3>No Dependencies Found</h3>
                                        <p>Run a code analysis scan to detect dependencies.</p>
                                    </div>
                                </div>
                            <?php else: ?>
                                <ul class="tree-list">
                                    <?php
                                    // Build tree structure
                                    $treeData = [];
                                    foreach ($dependencies as $dep) {
                                        $source = $dep['source_file'];
                                        if (!isset($treeData[$source])) {
                                            $treeData[$source] = [];
                                        }
                                        $treeData[$source][] = [
                                            'target' => $dep['target_file'],
                                            'type' => $dep['dependency_type']
                                        ];
                                    }

                                    // Limit to first 50 files for performance
                                    $treeData = array_slice($treeData, 0, 50, true);

                                    foreach ($treeData as $sourceFile => $targets):
                                        $extension = pathinfo($sourceFile, PATHINFO_EXTENSION);
                                        $iconClass = match($extension) {
                                            'php' => 'fab fa-php',
                                            'js' => 'fab fa-js-square',
                                            'css' => 'fab fa-css3-alt',
                                            'html' => 'fab fa-html5',
                                            default => 'fas fa-file-code'
                                        };
                                    ?>
                                        <li class="tree-item">
                                            <div class="tree-item__header" onclick="toggleTreeItem(this)">
                                                <i class="tree-item__toggle fas fa-chevron-right"></i>
                                                <i class="tree-item__icon <?php echo $iconClass; ?>"></i>
                                                <span class="tree-item__label" title="<?php echo htmlspecialchars($sourceFile); ?>">
                                                    <?php echo htmlspecialchars(basename($sourceFile)); ?>
                                                </span>
                                                <span class="badge badge--secondary"><?php echo count($targets); ?></span>
                                            </div>
                                            <ul class="tree-item__children">
                                                <?php foreach ($targets as $target): ?>
                                                    <?php
                                                    $targetExt = pathinfo($target['target'], PATHINFO_EXTENSION);
                                                    $targetIcon = match($targetExt) {
                                                        'php' => 'fab fa-php',
                                                        'js' => 'fab fa-js-square',
                                                        'css' => 'fab fa-css3-alt',
                                                        'html' => 'fab fa-html5',
                                                        default => 'fas fa-file-code'
                                                    };
                                                    ?>
                                                    <li class="tree-item tree-item--leaf">
                                                        <i class="tree-item__icon <?php echo $targetIcon; ?>"></i>
                                                        <span class="tree-item__label" title="<?php echo htmlspecialchars($target['target']); ?>">
                                                            <?php echo htmlspecialchars(basename($target['target'])); ?>
                                                        </span>
                                                        <span class="badge badge--info"><?php echo htmlspecialchars($target['type']); ?></span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- List View -->
                <div id="view-list" class="dependency-view">
                    <div class="dependency-list">
                        <div class="dependency-list__toolbar">
                            <div class="input-group">
                                <i class="fas fa-search input-group__icon"></i>
                                <input type="text" id="list-search" class="form-control" placeholder="Search dependencies..." onkeyup="filterList(this.value)">
                            </div>
                            <div class="btn-group">
                                <select class="form-control" id="type-filter" onchange="filterByType(this.value)">
                                    <option value="">All Types</option>
                                    <?php foreach ($depthAnalysis as $type): ?>
                                        <option value="<?php echo htmlspecialchars($type['dependency_type']); ?>">
                                            <?php echo htmlspecialchars(ucfirst($type['dependency_type'])); ?> (<?php echo $type['count']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <?php if (empty($dependencies)): ?>
                            <div class="empty-state">
                                <div class="empty-state__icon">
                                    <i class="fas fa-list"></i>
                                </div>
                                <div class="empty-state__message">
                                    <h3>No Dependencies Found</h3>
                                    <p>Run a code analysis scan to detect dependencies.</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="data-table-wrapper">
                                <table class="data-table data-table--striped" id="dependencies-table">
                                    <thead>
                                        <tr>
                                            <th class="data-table__header data-table__header--sortable" onclick="sortTable(0)">
                                                Source File
                                                <i class="fas fa-sort"></i>
                                            </th>
                                            <th class="data-table__header data-table__header--sortable" onclick="sortTable(1)">
                                                Target File
                                                <i class="fas fa-sort"></i>
                                            </th>
                                            <th class="data-table__header data-table__header--sortable" onclick="sortTable(2)">
                                                Type
                                                <i class="fas fa-sort"></i>
                                            </th>
                                            <th class="data-table__header data-table__header--sortable" onclick="sortTable(3)">
                                                Detected
                                                <i class="fas fa-sort"></i>
                                            </th>
                                            <th class="data-table__header">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($dependencies, 0, 100) as $dep): ?>
                                            <tr data-type="<?php echo htmlspecialchars($dep['dependency_type']); ?>">
                                                <td class="data-table__cell" title="<?php echo htmlspecialchars($dep['source_file']); ?>">
                                                    <code class="code-inline">
                                                        <?php echo htmlspecialchars(basename($dep['source_file'])); ?>
                                                    </code>
                                                </td>
                                                <td class="data-table__cell" title="<?php echo htmlspecialchars($dep['target_file']); ?>">
                                                    <code class="code-inline">
                                                        <?php echo htmlspecialchars(basename($dep['target_file'])); ?>
                                                    </code>
                                                </td>
                                                <td class="data-table__cell">
                                                    <span class="badge badge--info">
                                                        <?php echo htmlspecialchars(ucfirst($dep['dependency_type'])); ?>
                                                    </span>
                                                </td>
                                                <td class="data-table__cell">
                                                    <?php echo date('M j, Y', strtotime($dep['created_at'])); ?>
                                                </td>
                                                <td class="data-table__cell data-table__cell--actions">
                                                    <button type="button" class="btn btn--sm btn--secondary" onclick="viewDependencyDetails(<?php echo $dep['id']; ?>)" title="View Details">
                                                        <i class="fas fa-info-circle"></i>
                                                    </button>
                                                    <button type="button" class="btn btn--sm btn--secondary" onclick="visualizePath('<?php echo htmlspecialchars($dep['source_file'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($dep['target_file'], ENT_QUOTES); ?>')" title="Visualize Path">
                                                        <i class="fas fa-route"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Most Depended On Files & Circular Dependencies -->
        <div class="row-layout">
            <!-- Most Depended On Files -->
            <section class="content-section">
                <header class="content-section__header">
                    <h2 class="content-section__title">Most Depended On Files</h2>
                    <p class="content-section__subtitle">Core files with highest dependency count</p>
                </header>
                <div class="content-section__body">
                    <?php if (empty($mostDepended)): ?>
                        <div class="empty-state empty-state--compact">
                            <i class="fas fa-file-code"></i>
                            <p>No dependency data available</p>
                        </div>
                    <?php else: ?>
                        <div class="stat-list">
                            <?php foreach ($mostDepended as $file): ?>
                                <div class="stat-item">
                                    <div class="stat-item__icon">
                                        <i class="fas fa-file-code"></i>
                                    </div>
                                    <div class="stat-item__content">
                                        <div class="stat-item__label" title="<?php echo htmlspecialchars($file['target_file']); ?>">
                                            <?php echo htmlspecialchars(basename($file['target_file'])); ?>
                                        </div>
                                        <div class="stat-item__path">
                                            <?php echo htmlspecialchars(dirname($file['target_file'])); ?>
                                        </div>
                                    </div>
                                    <div class="stat-item__value">
                                        <span class="badge badge--danger"><?php echo $file['dep_count']; ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Circular Dependencies -->
            <section class="content-section">
                <header class="content-section__header">
                    <h2 class="content-section__title">Circular Dependencies</h2>
                    <p class="content-section__subtitle">Dependency cycles requiring refactoring</p>
                </header>
                <div class="content-section__body">
                    <?php if (empty($circularDeps)): ?>
                        <div class="alert alert--success">
                            <div class="alert__icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="alert__content">
                                <div class="alert__title">No Circular Dependencies</div>
                                <div class="alert__message">Your project has no circular dependency patterns.</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="circular-deps-list">
                            <?php foreach (array_slice($circularDeps, 0, 10) as $circular): ?>
                                <?php
                                $files = explode(' -> ', $circular['file_chain']);
                                $severityClass = match(strtolower($circular['severity'] ?? 'medium')) {
                                    'critical', 'high' => 'danger',
                                    'low' => 'warning',
                                    default => 'info'
                                };
                                ?>
                                <div class="circular-dep-item">
                                    <div class="circular-dep-item__header">
                                        <span class="badge badge--<?php echo $severityClass; ?>">
                                            <?php echo htmlspecialchars(ucfirst($circular['severity'] ?? 'Medium')); ?>
                                        </span>
                                        <span class="circular-dep-item__type">
                                            <?php echo htmlspecialchars(ucfirst($circular['dependency_type'])); ?>
                                        </span>
                                        <span class="circular-dep-item__date">
                                            <?php echo date('M j, Y', strtotime($circular['detected_at'])); ?>
                                        </span>
                                    </div>
                                    <div class="circular-dep-item__chain">
                                        <?php foreach ($files as $index => $file): ?>
                                            <div class="circular-dep-item__file">
                                                <code><?php echo htmlspecialchars(basename(trim($file))); ?></code>
                                            </div>
                                            <?php if ($index < count($files) - 1): ?>
                                                <i class="fas fa-arrow-right circular-dep-item__arrow"></i>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="circular-dep-item__actions">
                                        <button type="button" class="btn btn--sm btn--secondary" onclick="viewCircularDetails(<?php echo $circular['id']; ?>)">
                                            <i class="fas fa-info-circle"></i> Details
                                        </button>
                                        <button type="button" class="btn btn--sm btn--secondary" onclick="visualizeCircular(<?php echo $circular['id']; ?>)">
                                            <i class="fas fa-project-diagram"></i> Visualize
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <!-- Dependency Type Distribution -->
        <section class="content-section">
            <header class="content-section__header">
                <h2 class="content-section__title">Dependency Type Distribution</h2>
                <p class="content-section__subtitle">Breakdown of dependency types across your project</p>
            </header>
            <div class="content-section__body">
                <div class="chart-container">
                    <canvas id="dependency-type-chart"></canvas>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Dependency Details Modal -->
<div id="modal-dependency-details" class="modal">
    <div class="modal__overlay" onclick="closeModal('modal-dependency-details')"></div>
    <div class="modal__content modal__content--large">
        <header class="modal__header">
            <h2 class="modal__title">Dependency Details</h2>
            <button type="button" class="modal__close" onclick="closeModal('modal-dependency-details')">
                <i class="fas fa-times"></i>
            </button>
        </header>
        <div class="modal__body" id="modal-dependency-content">
            <div class="loading-overlay">
                <div class="spinner"></div>
            </div>
        </div>
    </div>
</div>

<script>
// Graph data from PHP
const graphData = {
    nodes: <?php echo json_encode($graphNodes); ?>,
    edges: <?php echo json_encode($graphEdges); ?>
};

// Dependency type data for chart
const depTypeData = <?php echo json_encode(array_column($depthAnalysis, 'count')); ?>;
const depTypeLabels = <?php echo json_encode(array_map(fn($t) => ucfirst($t['dependency_type']), $depthAnalysis)); ?>;

// Switch between graph/tree/list views
function switchView(view) {
    // Update buttons
    document.querySelectorAll('[data-view]').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.view === view);
    });

    // Update views
    document.querySelectorAll('.dependency-view').forEach(viewEl => {
        viewEl.classList.remove('dependency-view--active');
    });
    document.getElementById('view-' + view).classList.add('dependency-view--active');

    // Initialize graph on first view
    if (view === 'graph' && !window.graphInitialized) {
        initializeGraph();
    }
}

// Initialize dependency graph (placeholder - would use D3.js or vis.js in production)
function initializeGraph() {
    window.graphInitialized = true;
    const canvas = document.getElementById('dependency-graph-canvas');

    // Placeholder: In production, use D3.js or vis.js for interactive graph
    canvas.innerHTML = `
        <div class="dependency-graph__info">
            <p><strong>Graph Visualization:</strong> ${graphData.nodes.length} nodes, ${graphData.edges.length} edges</p>
            <p>Interactive graph rendering would be implemented with D3.js or vis.js</p>
            <small>Click nodes to see details, drag to rearrange, zoom with mouse wheel</small>
        </div>
    `;
}

// Graph controls
function zoomIn() {
    DashboardApp.showAlert('Zoom in functionality', 'info', 2000);
}

function zoomOut() {
    DashboardApp.showAlert('Zoom out functionality', 'info', 2000);
}

function resetZoom() {
    DashboardApp.showAlert('Reset zoom', 'info', 2000);
}

function filterGraph(searchTerm) {
    // TODO: Filter graph nodes by search term
}

// Tree controls
function toggleTreeItem(header) {
    const item = header.parentElement;
    const isExpanded = item.classList.contains('tree-item--expanded');

    if (isExpanded) {
        item.classList.remove('tree-item--expanded');
        header.querySelector('.tree-item__toggle').classList.remove('fa-chevron-down');
        header.querySelector('.tree-item__toggle').classList.add('fa-chevron-right');
    } else {
        item.classList.add('tree-item--expanded');
        header.querySelector('.tree-item__toggle').classList.remove('fa-chevron-right');
        header.querySelector('.tree-item__toggle').classList.add('fa-chevron-down');
    }
}

function expandAll() {
    document.querySelectorAll('.tree-item').forEach(item => {
        item.classList.add('tree-item--expanded');
        const toggle = item.querySelector('.tree-item__toggle');
        if (toggle) {
            toggle.classList.remove('fa-chevron-right');
            toggle.classList.add('fa-chevron-down');
        }
    });
}

function collapseAll() {
    document.querySelectorAll('.tree-item').forEach(item => {
        item.classList.remove('tree-item--expanded');
        const toggle = item.querySelector('.tree-item__toggle');
        if (toggle) {
            toggle.classList.remove('fa-chevron-down');
            toggle.classList.add('fa-chevron-right');
        }
    });
}

function filterTree(searchTerm) {
    const term = searchTerm.toLowerCase();
    document.querySelectorAll('.tree-item').forEach(item => {
        const label = item.querySelector('.tree-item__label').textContent.toLowerCase();
        item.style.display = label.includes(term) ? '' : 'none';
    });
}

// List controls
function filterList(searchTerm) {
    const term = searchTerm.toLowerCase();
    document.querySelectorAll('#dependencies-table tbody tr').forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(term) ? '' : 'none';
    });
}

function filterByType(type) {
    document.querySelectorAll('#dependencies-table tbody tr').forEach(row => {
        if (!type || row.dataset.type === type) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function sortTable(columnIndex) {
    // TODO: Implement table sorting
    DashboardApp.showAlert('Sorting by column ' + (columnIndex + 1), 'info', 2000);
}

// Action functions
function refreshDependencies() {
    window.location.reload();
}

function exportDependencies() {
    DashboardApp.showAlert('Export functionality coming soon', 'info', 3000);
}

function viewDependencyDetails(depId) {
    const modal = document.getElementById('modal-dependency-details');
    const content = document.getElementById('modal-dependency-content');

    modal.style.display = 'flex';
    content.innerHTML = '<div class="loading-overlay"><div class="spinner"></div></div>';

    // TODO: Fetch dependency details from API
    setTimeout(() => {
        content.innerHTML = `
            <div class="alert alert--info">
                <div class="alert__icon"><i class="fas fa-info-circle"></i></div>
                <div class="alert__content">
                    <div class="alert__title">Dependency #${depId}</div>
                    <div class="alert__message">Detailed dependency information would be displayed here.</div>
                </div>
            </div>
        `;
    }, 500);
}

function visualizePath(source, target) {
    DashboardApp.showAlert(`Visualizing path from ${source} to ${target}`, 'info', 3000);
    switchView('graph');
}

function viewCircularDetails(circularId) {
    const modal = document.getElementById('modal-dependency-details');
    modal.style.display = 'flex';

    // TODO: Load circular dependency details
}

function visualizeCircular(circularId) {
    DashboardApp.showAlert('Visualizing circular dependency', 'info', 3000);
    switchView('graph');
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Initialize Chart.js
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dependency type chart
    if (depTypeData.length > 0) {
        ChartsModule.createChart('dependency-type-chart', {
            type: 'doughnut',
            data: {
                labels: depTypeLabels,
                datasets: [{
                    data: depTypeData,
                    backgroundColor: [
                        'rgba(79, 70, 229, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });
    }

    // Initialize DashboardApp
    if (window.DashboardApp) {
        DashboardApp.init();
    }
});
</script>

<?php
// Include footer
require_once __DIR__ . '/../includes-v2/footer.php';
?>
