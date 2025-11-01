<?php
/**
 * Projects Management Page - PHASE 3 STEP 1
 *
 * CRUD operations for projects table
 * List, add, edit, delete projects
 * Multi-project architecture management
 *
 * @package hdgwrzntwa/dashboard/admin
 * @category Dashboard Page - PHASE 3
 * @created October 31, 2025
 */

declare(strict_types=1);

// Set current page for sidebar highlighting
$_GET['page'] = 'projects';
$currentPage = 'projects';

// Load application bootstrap
require_once $_SERVER['DOCUMENT_ROOT'] . '/app.php';

// Load project selector component
require_once $_SERVER['DOCUMENT_ROOT'] . '/dashboard/admin/includes/project-selector.php';

// Get current project ID from session (set by project-selector.php)
$projectId = (int)($_SESSION['current_project_id'] ?? $_GET['project_id'] ?? 1);

// Validate project exists and is active
$valQuery = "SELECT id FROM projects WHERE id = ? AND status = 'active' LIMIT 1";
$valStmt = $pdo->prepare($valQuery);
$valStmt->execute([$projectId]);
if (!$valStmt->fetch()) {
    $projectId = 1;
    $_SESSION['current_project_id'] = $projectId;
}

// ============================================================================
// HANDLE CRUD OPERATIONS
// ============================================================================

$message = '';
$messageType = '';

// Handle DELETE
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $deleteId = (int)$_GET['id'];

    // Prevent deleting current project
    if ($deleteId === $projectId) {
        $message = '❌ Cannot delete the currently selected project';
        $messageType = 'danger';
    } else {
        try {
            $deleteStmt = $pdo->prepare("DELETE FROM projects WHERE id = ? LIMIT 1");
            $deleteStmt->execute([$deleteId]);
            $message = '✅ Project deleted successfully';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = '❌ Error deleting project: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

// Handle ADD/EDIT
if ($_POST && isset($_POST['save_project'])) {
    $projectName = trim($_POST['name'] ?? '');
    $projectPath = trim($_POST['path'] ?? '');
    $projectType = trim($_POST['type'] ?? 'php');
    $projectStatus = trim($_POST['status'] ?? 'active');
    $projectDescription = trim($_POST['description'] ?? '');
    $editId = isset($_POST['edit_id']) && $_POST['edit_id'] ? (int)$_POST['edit_id'] : 0;

    // Validation
    if (!$projectName) {
        $message = '❌ Project name is required';
        $messageType = 'danger';
    } elseif (!$projectPath) {
        $message = '❌ Project path is required';
        $messageType = 'danger';
    } else {
        try {
            if ($editId) {
                // Update existing project
                $updateStmt = $pdo->prepare("
                    UPDATE projects
                    SET project_name = ?, project_path = ?, project_type = ?, status = ?, description = ?, updated_at = NOW()
                    WHERE id = ? LIMIT 1
                ");
                $updateStmt->execute([$projectName, $projectPath, $projectType, $projectStatus, $projectDescription, $editId]);
                $message = '✅ Project updated successfully';
            } else {
                // Create new project
                $slug = strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9 ]/', '', $projectName)));
                $insertStmt = $pdo->prepare("
                    INSERT INTO projects (project_name, project_slug, project_type, status, project_path, description, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                $insertStmt->execute([$projectName, $slug, $projectType, $projectStatus, $projectPath, $projectDescription]);
                $message = '✅ Project created successfully';
            }
            $messageType = 'success';
        } catch (Exception $e) {
            $message = '❌ Error saving project: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}// ============================================================================
// FETCH DATA
// ============================================================================

// Get all projects
$projectsQuery = "
    SELECT
        id,
        project_name as name,
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

// Get edit project if specified
$editProject = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editId = (int)$_GET['id'];
    $editStmt = $pdo->prepare("SELECT id, project_name as name, project_path as path, project_type as type, status, description FROM projects WHERE id = ? LIMIT 1");
    $editStmt->execute([$editId]);
    $editProject = $editStmt->fetch(PDO::FETCH_ASSOC);
}

// Get project statistics
$statsQuery = "
    SELECT
        COUNT(*) as total_projects,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_projects,
        SUM(CASE WHEN status = 'archived' OR status = 'inactive' THEN 1 ELSE 0 END) as inactive_projects,
        COUNT(DISTINCT project_type) as project_types
    FROM projects
";

$statsStmt = $pdo->prepare($statsQuery);
$statsStmt->execute();
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="dashboard-container">
    <!-- Project Selector Component (PHASE 3) -->
    <?php echo renderProjectSelector($pdo, $_SESSION['current_unit_id'], $projectId); ?>

    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <div>
            <h1>Projects Management</h1>
            <p class="text-muted">Manage projects and scanning configurations</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#projectModal" onclick="resetProjectForm()">
            <i class="fas fa-plus"></i> Add Project
        </button>
    </div>

    <!-- Messages -->
    <?php if ($message): ?>
        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?php echo $stats['total_projects']; ?></h3>
                    <p class="text-muted mb-0">Total Projects</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-success"><?php echo $stats['active_projects']; ?></h3>
                    <p class="text-muted mb-0">Active</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-warning"><?php echo $stats['inactive_projects']; ?></h3>
                    <p class="text-muted mb-0">Inactive</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?php echo $stats['project_types']; ?></h3>
                    <p class="text-muted mb-0">Project Types</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Projects Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">All Projects</h5>
        </div>
        <div class="card-body">
            <?php if (empty($allProjects)): ?>
                <p class="text-muted text-center py-4">No projects found. <a href="#" data-bs-toggle="modal" data-bs-target="#projectModal">Create one now</a></p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Path</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allProjects as $proj): ?>
                            <tr>
                                <td><code><?php echo htmlspecialchars((string)$proj['id']); ?></code></td>
                                <td>
                                    <strong><?php echo htmlspecialchars((string)$proj['name']); ?></strong>
                                    <?php if ($proj['description']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars(substr((string)$proj['description'], 0, 50)); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><code class="text-muted small"><?php echo htmlspecialchars((string)$proj['path']); ?></code></td>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars((string)$proj['type']); ?></span>
                                </td>
                                <td>
                                    <?php if ($proj['status'] === 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small">
                                    <?php echo date('M d, Y', strtotime($proj['created_at'])); ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#projectModal" onclick="editProject(<?php echo htmlspecialchars(json_encode($proj)); ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <?php if ($proj['id'] !== $projectId): ?>
                                        <a href="?page=projects&action=delete&id=<?php echo $proj['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    <?php endif; ?>
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

<!-- Project Modal -->
<div class="modal fade" id="projectModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="projectModalTitle">Add Project</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="edit_id" value="0">

                    <div class="mb-3">
                        <label for="project_name" class="form-label">Project Name *</label>
                        <input type="text" class="form-control" id="project_name" name="name" placeholder="e.g., My Project" required>
                    </div>

                    <div class="mb-3">
                        <label for="project_path" class="form-label">Project Path *</label>
                        <input type="text" class="form-control" id="project_path" name="path" placeholder="e.g., /var/www/my-project" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="project_type" class="form-label">Project Type</label>
                                <select class="form-select" id="project_type" name="type">
                                    <option value="php">PHP</option>
                                    <option value="javascript">JavaScript</option>
                                    <option value="python">Python</option>
                                    <option value="java">Java</option>
                                    <option value="cpp">C++</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="project_status" class="form-label">Status</label>
                                <select class="form-select" id="project_status" name="status">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="project_description" class="form-label">Description</label>
                        <textarea class="form-control" id="project_description" name="description" rows="3" placeholder="Project description (optional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_project" class="btn btn-primary">Save Project</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetProjectForm() {
    document.getElementById('edit_id').value = '0';
    document.getElementById('project_name').value = '';
    document.getElementById('project_path').value = '';
    document.getElementById('project_type').value = 'php';
    document.getElementById('project_status').value = 'active';
    document.getElementById('project_description').value = '';
    document.getElementById('projectModalTitle').textContent = 'Add Project';
}

function editProject(project) {
    document.getElementById('edit_id').value = project.id;
    document.getElementById('project_name').value = project.name;
    document.getElementById('project_path').value = project.path;
    document.getElementById('project_type').value = project.type;
    document.getElementById('project_status').value = project.status;
    document.getElementById('project_description').value = project.description || '';
    document.getElementById('projectModalTitle').textContent = 'Edit Project';
}
</script>
