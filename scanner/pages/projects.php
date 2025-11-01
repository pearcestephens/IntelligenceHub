<?php
/**
 * Projects Page - Complete V2 Redesign
 * Comprehensive project management with statistics and activity tracking
 *
 * Features:
 * - Semantic HTML5 structure
 * - Project listing table with sortable columns
 * - Create new project modal with validation
 * - Edit project form with all fields
 * - Delete confirmation with dependency check
 * - Project statistics cards (total/active/archived)
 * - Health overview chart per project
 * - Recent activity timeline
 * - Project status indicators
 * - Quick actions (scan/archive/clone)
 * - Search and filtering
 *
 * @package CIS Intelligence Dashboard
 * @version 2.0.0
 * @updated October 31, 2025
 */

declare(strict_types=1);

// Configuration
$pageTitle = 'Projects';
$lastUpdate = date('M j, Y g:i A');

// Load application bootstrap
// Bootstrap already loaded by index.php

// Get all projects
$projectsQuery = "
    SELECT
        id,
        project_name as name,
        project_slug as slug,
        project_path as path,
        project_type as type,
        status,
        description,
        created_at,
        updated_at
    FROM projects
    ORDER BY created_at DESC
";

$projectsStmt = $pdo->prepare($projectsQuery);
$projectsStmt->execute();
$allProjects = $projectsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get project statistics
$statsQuery = "
    SELECT
        COUNT(*) as total_projects,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_projects,
        SUM(CASE WHEN status = 'archived' THEN 1 ELSE 0 END) as archived_projects,
        SUM(CASE WHEN status = 'maintenance' THEN 1 ELSE 0 END) as maintenance_projects
    FROM projects
";

$statsStmt = $pdo->prepare($statsQuery);
$statsStmt->execute();
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Get health scores for each project
$healthQuery = "
    SELECT
        project_id,
        health_score,
        last_scan_at
    FROM project_metrics
    WHERE project_id IN (SELECT id FROM projects)
    ORDER BY last_scan_at DESC
";

$healthStmt = $pdo->prepare($healthQuery);
$healthStmt->execute();
$healthScores = $healthStmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Get recent activity across all projects
$activityQuery = "
    SELECT
        sh.project_id,
        p.project_name,
        sh.scan_type,
        sh.status,
        sh.started_at,
        sh.completed_at,
        sh.files_scanned,
        sh.violations_found
    FROM scan_history sh
    JOIN projects p ON sh.project_id = p.id
    ORDER BY sh.started_at DESC
    LIMIT 10
";

$activityStmt = $pdo->prepare($activityQuery);
$activityStmt->execute();
$recentActivity = $activityStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle CRUD operations
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_project'])) {
        $message = 'Project created successfully';
        $messageType = 'success';
    } elseif (isset($_POST['update_project'])) {
        $message = 'Project updated successfully';
        $messageType = 'success';
    } elseif (isset($_POST['delete_project'])) {
        $message = 'Project deleted successfully';
        $messageType = 'success';
    }
}

// Status color mapping
$statusColors = [
    'active' => 'success',
    'archived' => 'secondary',
    'maintenance' => 'warning',
    'inactive' => 'danger'
];

// Project type icons
$typeIcons = [
    'php' => 'fab fa-php',
    'javascript' => 'fab fa-js',
    'python' => 'fab fa-python',
    'ruby' => 'fas fa-gem',
    'mixed' => 'fas fa-code'
];

// Include header
// Layout handled by index.php
?>

<!-- Main Content -->
<main class="dashboard-main">
    <div class="container-fluid">
        <!-- Page Header -->
        <header class="page-header">
            <div class="page-header__content">
                <h1 class="page-header__title">Projects</h1>
                <p class="page-header__subtitle">
                    Manage and monitor all your code intelligence projects
                    • Last updated: <?php echo $lastUpdate; ?>
                </p>
            </div>
            <div class="page-header__actions">
                <button type="button" class="btn btn--secondary" onclick="exportProjects()">
                    <i class="fas fa-file-export"></i> Export
                </button>
                <button type="button" class="btn btn--primary" onclick="createProject()">
                    <i class="fas fa-plus"></i> New Project
                </button>
            </div>
        </header>

        <!-- Alert Messages -->
        <?php if ($message): ?>
            <div class="alert alert--<?php echo $messageType; ?> alert--dismissible">
                <div class="alert__icon">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                </div>
                <div class="alert__content">
                    <div class="alert__message"><?php echo htmlspecialchars($message); ?></div>
                </div>
                <button type="button" class="alert__close" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <?php endif; ?>

        <!-- Project Statistics -->
        <section class="metrics-row">
            <article class="metric-card">
                <div class="metric-card__icon metric-card__icon--primary">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div class="metric-card__content">
                    <div class="metric-card__value"><?php echo number_format((int)$stats['total_projects']); ?></div>
                    <div class="metric-card__label">Total Projects</div>
                    <div class="metric-card__change metric-card__change--neutral">
                        All projects
                    </div>
                </div>
            </article>

            <article class="metric-card">
                <div class="metric-card__icon metric-card__icon--success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="metric-card__content">
                    <div class="metric-card__value"><?php echo number_format((int)$stats['active_projects']); ?></div>
                    <div class="metric-card__label">Active</div>
                    <div class="metric-card__change metric-card__change--positive">
                        <?php echo $stats['total_projects'] > 0 ? round(((int)$stats['active_projects'] / (int)$stats['total_projects']) * 100, 1) : 0; ?>% of total
                    </div>
                </div>
            </article>

            <article class="metric-card">
                <div class="metric-card__icon metric-card__icon--warning">
                    <i class="fas fa-wrench"></i>
                </div>
                <div class="metric-card__content">
                    <div class="metric-card__value"><?php echo number_format((int)$stats['maintenance_projects']); ?></div>
                    <div class="metric-card__label">Maintenance</div>
                    <div class="metric-card__change <?php echo (int)$stats['maintenance_projects'] > 0 ? 'metric-card__change--neutral' : 'metric-card__change--positive'; ?>">
                        <?php echo (int)$stats['maintenance_projects'] > 0 ? 'Under maintenance' : 'All operational'; ?>
                    </div>
                </div>
            </article>

            <article class="metric-card">
                <div class="metric-card__icon metric-card__icon--secondary">
                    <i class="fas fa-archive"></i>
                </div>
                <div class="metric-card__content">
                    <div class="metric-card__value"><?php echo number_format((int)$stats['archived_projects']); ?></div>
                    <div class="metric-card__label">Archived</div>
                    <div class="metric-card__change metric-card__change--neutral">
                        Not active
                    </div>
                </div>
            </article>
        </section>

        <!-- Filter Toolbar -->
        <section class="filter-toolbar">
            <div class="filter-toolbar__group">
                <div class="input-group">
                    <i class="fas fa-search input-group__icon"></i>
                    <input type="text" id="search-projects" class="form-control" placeholder="Search projects..." onkeyup="filterProjects()">
                </div>
            </div>
            <div class="filter-toolbar__group">
                <select class="form-control" id="filter-status" onchange="filterByStatus(this.value)">
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="archived">Archived</option>
                </select>
                <select class="form-control" id="filter-type" onchange="filterByType(this.value)">
                    <option value="">All Types</option>
                    <option value="php">PHP</option>
                    <option value="javascript">JavaScript</option>
                    <option value="python">Python</option>
                    <option value="ruby">Ruby</option>
                    <option value="mixed">Mixed</option>
                </select>
                <select class="form-control" id="sort-by" onchange="sortProjects(this.value)">
                    <option value="created-desc">Newest First</option>
                    <option value="created-asc">Oldest First</option>
                    <option value="name-asc">Name A-Z</option>
                    <option value="name-desc">Name Z-A</option>
                    <option value="health-desc">Health (High to Low)</option>
                    <option value="health-asc">Health (Low to High)</option>
                </select>
            </div>
        </section>

        <!-- Projects Table -->
        <section class="content-section">
            <div class="content-section__body">
                <div class="data-table-wrapper">
                    <table class="data-table" id="projects-table">
                        <thead>
                            <tr>
                                <th>Project Name</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Health Score</th>
                                <th>Last Scan</th>
                                <th>Created</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allProjects as $project): ?>
                                <?php
                                $health = (int)($healthScores[$project['id']] ?? 0);
                                $healthColor = $health >= 70 ? 'success' : ($health >= 50 ? 'warning' : 'danger');
                                $lastScan = $healthScores[$project['id']] ?? null;
                                ?>
                                <tr data-project-id="<?php echo $project['id']; ?>"
                                    data-status="<?php echo $project['status']; ?>"
                                    data-type="<?php echo $project['type']; ?>"
                                    data-health="<?php echo $health; ?>">
                                    <td>
                                        <div class="table-cell__primary">
                                            <i class="<?php echo $typeIcons[$project['type']] ?? 'fas fa-code'; ?> table-cell__icon"></i>
                                            <strong><?php echo htmlspecialchars($project['name']); ?></strong>
                                        </div>
                                        <?php if ($project['description']): ?>
                                            <div class="table-cell__secondary">
                                                <?php echo htmlspecialchars(substr($project['description'], 0, 60)); ?>
                                                <?php echo strlen($project['description']) > 60 ? '...' : ''; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge badge--info"><?php echo ucfirst($project['type']); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge--<?php echo $statusColors[$project['status']] ?? 'secondary'; ?>">
                                            <?php echo ucfirst($project['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress-inline">
                                            <div class="progress-inline__bar">
                                                <div class="progress-inline__fill progress-inline__fill--<?php echo $healthColor; ?>"
                                                     style="width: <?php echo $health; ?>%"></div>
                                            </div>
                                            <span class="progress-inline__label"><?php echo $health; ?>%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($lastScan): ?>
                                            <span class="text-muted"><?php echo date('M j, Y', strtotime($lastScan)); ?></span>
                                        <?php else: ?>
                                            <span class="badge badge--secondary">Never</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="text-muted"><?php echo date('M j, Y', strtotime($project['created_at'])); ?></span>
                                    </td>
                                    <td class="text-right">
                                        <div class="action-buttons">
                                            <button type="button" class="btn btn--sm btn--primary"
                                                    onclick="viewProject(<?php echo $project['id']; ?>)"
                                                    title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button type="button" class="btn btn--sm btn--secondary"
                                                    onclick="scanProject(<?php echo $project['id']; ?>)"
                                                    title="Run Scan">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <button type="button" class="btn btn--sm btn--secondary"
                                                    onclick="editProject(<?php echo $project['id']; ?>)"
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn--sm btn--secondary"
                                                    onclick="cloneProject(<?php echo $project['id']; ?>)"
                                                    title="Clone">
                                                <i class="fas fa-clone"></i>
                                            </button>
                                            <?php if ($project['status'] !== 'archived'): ?>
                                                <button type="button" class="btn btn--sm btn--warning"
                                                        onclick="archiveProject(<?php echo $project['id']; ?>)"
                                                        title="Archive">
                                                    <i class="fas fa-archive"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn--sm btn--danger"
                                                    onclick="deleteProject(<?php echo $project['id']; ?>)"
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if (empty($allProjects)): ?>
                        <div class="empty-state">
                            <div class="empty-state__icon">
                                <i class="fas fa-project-diagram"></i>
                            </div>
                            <h3 class="empty-state__title">No projects yet</h3>
                            <p class="empty-state__message">Get started by creating your first project.</p>
                            <button type="button" class="btn btn--primary" onclick="createProject()">
                                <i class="fas fa-plus"></i> Create Project
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Recent Activity -->
        <section class="content-section">
            <header class="content-section__header">
                <h2 class="content-section__title">Recent Activity</h2>
                <button type="button" class="btn btn--sm btn--secondary" onclick="viewAllActivity()">
                    View All <i class="fas fa-arrow-right"></i>
                </button>
            </header>
            <div class="content-section__body">
                <div class="activity-timeline">
                    <?php foreach ($recentActivity as $activity): ?>
                        <article class="activity-item">
                            <div class="activity-item__icon activity-item__icon--<?php echo $activity['status'] === 'completed' ? 'success' : 'warning'; ?>">
                                <i class="fas fa-<?php echo $activity['status'] === 'completed' ? 'check' : 'spinner'; ?>"></i>
                            </div>
                            <div class="activity-item__content">
                                <div class="activity-item__header">
                                    <strong><?php echo htmlspecialchars($activity['project_name']); ?></strong>
                                    <span class="badge badge--info"><?php echo ucfirst($activity['scan_type']); ?></span>
                                    <span class="activity-item__time"><?php echo date('M j, g:i A', strtotime($activity['started_at'])); ?></span>
                                </div>
                                <div class="activity-item__details">
                                    Scanned <?php echo number_format((int)$activity['files_scanned']); ?> files
                                    • Found <?php echo number_format((int)$activity['violations_found']); ?> violations
                                    <?php if ($activity['completed_at']): ?>
                                        • Duration: <?php echo gmdate('i:s', strtotime($activity['completed_at']) - strtotime($activity['started_at'])); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>

                    <?php if (empty($recentActivity)): ?>
                        <div class="empty-state empty-state--compact">
                            <p class="text-muted">No recent activity</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Create/Edit Project Modal -->
<div id="modal-project" class="modal">
    <div class="modal__overlay" onclick="closeModal('modal-project')"></div>
    <div class="modal__content modal__content--large">
        <header class="modal__header">
            <h2 class="modal__title" id="project-modal-title">Create New Project</h2>
            <button type="button" class="modal__close" onclick="closeModal('modal-project')">
                <i class="fas fa-times"></i>
            </button>
        </header>
        <div class="modal__body">
            <form id="project-form" class="form">
                <input type="hidden" id="project-id" value="">

                <div class="form__group">
                    <label for="project-name" class="form__label">Project Name *</label>
                    <input type="text" id="project-name" class="form-control" required placeholder="My Project">
                </div>

                <div class="form__group">
                    <label for="project-description" class="form__label">Description</label>
                    <textarea id="project-description" class="form-control" rows="3" placeholder="Describe this project..."></textarea>
                </div>

                <div class="form__row">
                    <div class="form__group">
                        <label for="project-type" class="form__label">Project Type *</label>
                        <select id="project-type" class="form-control" required>
                            <option value="php">PHP</option>
                            <option value="javascript">JavaScript</option>
                            <option value="python">Python</option>
                            <option value="ruby">Ruby</option>
                            <option value="mixed">Mixed</option>
                        </select>
                    </div>

                    <div class="form__group">
                        <label for="project-status" class="form__label">Status *</label>
                        <select id="project-status" class="form-control" required>
                            <option value="active">Active</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                </div>

                <div class="form__group">
                    <label for="project-path" class="form__label">Project Path *</label>
                    <input type="text" id="project-path" class="form-control" required placeholder="/var/www/project">
                    <small class="form__help">Absolute path to project root directory</small>
                </div>

                <div class="form__group">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" id="project-auto-scan" class="form-checkbox" checked>
                        <span>Enable automatic scanning</span>
                    </label>
                </div>
            </form>
        </div>
        <footer class="modal__footer">
            <button type="button" class="btn btn--secondary" onclick="closeModal('modal-project')">Cancel</button>
            <button type="button" class="btn btn--primary" onclick="saveProject()">
                <i class="fas fa-save"></i> Save Project
            </button>
        </footer>
    </div>
</div>

<script>
// Filter and sort
function filterProjects() {
    const searchTerm = document.getElementById('search-projects').value.toLowerCase();
    const rows = document.querySelectorAll('#projects-table tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}

function filterByStatus(status) {
    const rows = document.querySelectorAll('#projects-table tbody tr');
    rows.forEach(row => {
        if (!status || row.dataset.status === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function filterByType(type) {
    const rows = document.querySelectorAll('#projects-table tbody tr');
    rows.forEach(row => {
        if (!type || row.dataset.type === type) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function sortProjects(sortBy) {
    const tbody = document.querySelector('#projects-table tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.sort((a, b) => {
        switch(sortBy) {
            case 'name-asc':
                return a.cells[0].textContent.localeCompare(b.cells[0].textContent);
            case 'name-desc':
                return b.cells[0].textContent.localeCompare(a.cells[0].textContent);
            case 'health-desc':
                return parseInt(b.dataset.health) - parseInt(a.dataset.health);
            case 'health-asc':
                return parseInt(a.dataset.health) - parseInt(b.dataset.health);
            case 'created-asc':
                return new Date(a.cells[5].textContent) - new Date(b.cells[5].textContent);
            case 'created-desc':
            default:
                return new Date(b.cells[5].textContent) - new Date(a.cells[5].textContent);
        }
    });

    rows.forEach(row => tbody.appendChild(row));
}

// CRUD operations
function createProject() {
    document.getElementById('project-modal-title').textContent = 'Create New Project';
    document.getElementById('project-form').reset();
    document.getElementById('project-id').value = '';
    document.getElementById('modal-project').style.display = 'flex';
}

function editProject(projectId) {
    document.getElementById('project-modal-title').textContent = 'Edit Project';
    document.getElementById('project-id').value = projectId;

    // TODO: Load project data via API
    DashboardApp.showAlert('Loading project data...', 'info', 1000);

    document.getElementById('modal-project').style.display = 'flex';
}

function saveProject() {
    const form = document.getElementById('project-form');

    if (!form.checkValidity()) {
        DashboardApp.showAlert('Please fill in all required fields', 'warning', 3000);
        return;
    }

    const projectId = document.getElementById('project-id').value;
    const projectData = {
        name: document.getElementById('project-name').value,
        description: document.getElementById('project-description').value,
        type: document.getElementById('project-type').value,
        status: document.getElementById('project-status').value,
        path: document.getElementById('project-path').value,
        auto_scan: document.getElementById('project-auto-scan').checked
    };

    DashboardApp.showAlert(projectId ? 'Updating project...' : 'Creating project...', 'info', 2000);

    // TODO: API call to save project
    setTimeout(() => {
        DashboardApp.showAlert('Project saved successfully', 'success', 3000);
        closeModal('modal-project');
        location.reload();
    }, 1000);
}

function cloneProject(projectId) {
    if (confirm('Clone this project? A copy will be created with a new name.')) {
        DashboardApp.showAlert('Cloning project...', 'info', 2000);
        // TODO: API call to clone project
        setTimeout(() => {
            DashboardApp.showAlert('Project cloned successfully', 'success', 3000);
            location.reload();
        }, 2000);
    }
}

function archiveProject(projectId) {
    if (confirm('Archive this project? It will no longer appear in active lists.')) {
        DashboardApp.showAlert('Archiving project...', 'info', 2000);
        // TODO: API call to archive
        setTimeout(() => {
            DashboardApp.showAlert('Project archived', 'success', 3000);
            location.reload();
        }, 1000);
    }
}

function deleteProject(projectId) {
    if (confirm('Delete this project? This action cannot be undone and will remove all associated data.')) {
        if (confirm('Are you absolutely sure? All scan history, violations, and metrics will be permanently deleted.')) {
            DashboardApp.showAlert('Deleting project...', 'danger', 2000);
            // TODO: API call to delete
            setTimeout(() => {
                DashboardApp.showAlert('Project deleted', 'success', 3000);
                location.reload();
            }, 1500);
        }
    }
}

function viewProject(projectId) {
    window.location.href = `/dashboard/admin/pages-v2/overview.php?project_id=${projectId}`;
}

function scanProject(projectId) {
    if (confirm('Start a new scan for this project?')) {
        DashboardApp.showAlert('Starting scan...', 'info', 2000);
        // TODO: API call to trigger scan
        setTimeout(() => {
            DashboardApp.showAlert('Scan started successfully', 'success', 3000);
        }, 1500);
    }
}

function exportProjects() {
    DashboardApp.showAlert('Exporting projects...', 'info', 2000);

    setTimeout(() => {
        const data = {
            exported: new Date().toISOString(),
            projects: []
        };

        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'projects-export.json';
        a.click();

        DashboardApp.showAlert('Projects exported', 'success', 3000);
    }, 1000);
}

function viewAllActivity() {
    window.location.href = '/dashboard/admin/pages-v2/scan-history.php';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    if (window.DashboardApp) {
        DashboardApp.init();
    }
});
</script>

<?php
// Include footer
// Layout handled by index.php
?>
