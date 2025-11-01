<?php
/**
 * Business Units Management Page - PHASE 3 STEP 2
 *
 * View and manage business units
 * Edit unit mappings and configurations
 *
 * @package hdgwrzntwa/dashboard/admin
 * @category Dashboard Page - PHASE 3
 * @created October 31, 2025
 */

declare(strict_types=1);

// Set current page for sidebar highlighting
$_GET['page'] = 'business-units';
$currentPage = 'business-units';

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

// Handle unit mapping add
if ($_POST && isset($_POST['map_project_unit'])) {
    $unitId = (int)($_POST['unit_id'] ?? 0);
    $role = trim($_POST['role'] ?? 'owner');

    if ($projectId && $unitId) {
        try {
            // Check if mapping already exists
            $checkStmt = $pdo->prepare("SELECT id FROM project_unit_mapping WHERE project_id = ? AND unit_id = ? LIMIT 1");
            $checkStmt->execute([$projectId, $unitId]);

            if ($checkStmt->fetch()) {
                $message = '⚠️ This project is already mapped to this unit';
                $messageType = 'warning';
            } else {
                $insertStmt = $pdo->prepare("
                    INSERT INTO project_unit_mapping (project_id, unit_id, role, active, created_at)
                    VALUES (?, ?, ?, 1, NOW())
                ");
                $insertStmt->execute([$projectId, $unitId, $role]);
                $message = '✅ Unit mapping added successfully';
                $messageType = 'success';
            }
        } catch (Exception $e) {
            $message = '❌ Error adding unit mapping: ' . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

// Handle unit mapping delete
if (isset($_GET['action']) && $_GET['action'] === 'remove_unit' && isset($_GET['mapping_id'])) {
    $mappingId = (int)$_GET['mapping_id'];

    try {
        $deleteStmt = $pdo->prepare("DELETE FROM project_unit_mapping WHERE mapping_id = ? LIMIT 1");
        $deleteStmt->execute([$mappingId]);
        $message = '✅ Unit mapping removed successfully';
        $messageType = 'success';
    } catch (Exception $e) {
        $message = '❌ Error removing unit mapping: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

// ============================================================================
// FETCH DATA
// ============================================================================

// Get all business units
$unitsQuery = "
    SELECT
        unit_id as id,
        unit_name,
        unit_type
    FROM business_units
    ORDER BY unit_name ASC
";

$unitsStmt = $pdo->prepare($unitsQuery);
$unitsStmt->execute();
$allUnits = $unitsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get mappings for current project
$mappingsQuery = "
    SELECT
        pum.mapping_id,
        pum.unit_id,
        bu.unit_name,
        bu.unit_type,
        pum.role,
        pum.active,
        pum.created_at
    FROM project_unit_mapping pum
    JOIN business_units bu ON pum.unit_id = bu.unit_id
    WHERE pum.project_id = ?
    ORDER BY bu.unit_name ASC
";

$mappingsStmt = $pdo->prepare($mappingsQuery);
$mappingsStmt->execute([$projectId]);
$mappings = $mappingsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get available units for mapping (not yet mapped to this project)
$availableUnits = [];
foreach ($allUnits as $unit) {
    $isMapped = false;
    foreach ($mappings as $mapping) {
        if ($mapping['unit_id'] === $unit['id']) {
            $isMapped = true;
            break;
        }
    }
    if (!$isMapped) {
        $availableUnits[] = $unit;
    }
}

// Get unit statistics
    $statsQuery = "
    SELECT
        COUNT(*) as total_units,
        SUM(CASE WHEN unit_type = 'corporate' THEN 1 ELSE 0 END) as corporate_units,
        SUM(CASE WHEN unit_type = 'technical' THEN 1 ELSE 0 END) as technical_units,
        SUM(CASE WHEN unit_type = 'retail' THEN 1 ELSE 0 END) as retail_units
    FROM business_units
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
            <h1>Business Units</h1>
            <p class="text-muted">Manage project unit mappings</p>
        </div>
        <?php if (!empty($availableUnits)): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#mapUnitModal">
                <i class="fas fa-link"></i> Map Unit
            </button>
        <?php endif; ?>
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
                    <h3 class="mb-0"><?php echo $stats['total_units']; ?></h3>
                    <p class="text-muted mb-0">Total Units</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?php echo $stats['corporate_units']; ?></h3>
                    <p class="text-muted mb-0">Corporate</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?php echo $stats['technical_units']; ?></h3>
                    <p class="text-muted mb-0">Technical</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="mb-0"><?php echo $stats['retail_units']; ?></h3>
                    <p class="text-muted mb-0">Retail</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Project Mappings -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Project Mappings for "<?php echo htmlspecialchars($_SESSION['current_project_name'] ?? 'Project #' . $projectId); ?>"</h5>
        </div>
        <div class="card-body">
            <?php if (empty($mappings)): ?>
                <p class="text-muted text-center py-4">This project is not yet mapped to any business units.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Unit Name</th>
                                <th>Type</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Mapped</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mappings as $mapping): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($mapping['unit_name']); ?></strong></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($mapping['unit_type']); ?></span></td>
                                <td>
                                    <span class="badge bg-info"><?php echo htmlspecialchars(ucfirst($mapping['role'])); ?></span>
                                </td>
                                <td>
                                    <?php if ($mapping['active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small">
                                    <?php echo date('M d, Y', strtotime($mapping['created_at'])); ?>
                                </td>
                                <td>
                                    <a href="?page=business-units&action=remove_unit&mapping_id=<?php echo $mapping['mapping_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this mapping?')">
                                        <i class="fas fa-unlink"></i> Remove
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- All Business Units -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">All Business Units</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allUnits as $unit): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($unit['unit_name']); ?></strong></td>
                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($unit['unit_type']); ?></span></td>
                            <td class="text-muted">Unit</td>
                            <td>
                                <span class="badge bg-success">Active</span>
                            </td>
                            <td>
                                <?php
                                $isMapped = false;
                                foreach ($mappings as $mapping) {
                                    if ($mapping['unit_id'] === $unit['id']) {
                                        $isMapped = true;
                                        break;
                                    }
                                }
                                if ($isMapped):
                                ?>
                                    <span class="badge bg-primary">Mapped</span>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#mapUnitModal" onclick="document.getElementById('unit_id_select').value = <?php echo $unit['id']; ?>">
                                        <i class="fas fa-link"></i> Map
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Map Unit Modal -->
<div class="modal fade" id="mapUnitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Map Business Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="unit_id_select" class="form-label">Select Unit *</label>
                        <select class="form-select" id="unit_id_select" name="unit_id" required>
                            <option value="">-- Choose a unit --</option>
                            <?php foreach ($availableUnits as $unit): ?>
                                <option value="<?php echo $unit['id']; ?>">
                                    <?php echo htmlspecialchars($unit['unit_name'] . ' (' . $unit['unit_type'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="role_select" class="form-label">Role</label>
                        <select class="form-select" id="role_select" name="role">
                            <option value="owner">Owner</option>
                            <option value="contributor">Contributor</option>
                            <option value="viewer">Viewer</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="map_project_unit" class="btn btn-primary">Map Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>
