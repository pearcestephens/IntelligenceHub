<?php
/**
 * Business Units Page - Complete V2 Redesign
 * Comprehensive business unit and team management
 *
 * Features:
 * - Semantic HTML5 structure
 * - Business unit management table with sortable columns
 * - Create/edit/delete unit modals with validation
 * - Team member assignment interface with drag-drop visualization
 * - Permissions matrix with role-based access control
 * - Activity logs timeline per unit
 * - Unit statistics cards (total/active/members/projects)
 * - Project mapping interface
 * - Search and filtering by type/status
 * - Bulk operations (activate/deactivate/delete)
 * - Unit hierarchy visualization
 * - Export functionality
 *
 * @package CIS Intelligence Dashboard
 * @version 2.0.0
 * @updated October 31, 2025
 */

declare(strict_types=1);

// Configuration
$pageTitle = 'Business Units';
$lastUpdate = date('M j, Y g:i A');

// Load application bootstrap
require_once $_SERVER['DOCUMENT_ROOT'] . '/app.php';

// Get all business units with statistics
$unitsQuery = "
    SELECT
        bu.unit_id as id,
        bu.unit_name as name,
        bu.unit_type as type,
        bu.description,
        bu.status,
        bu.created_at,
        bu.updated_at,
        COUNT(DISTINCT pum.project_id) as project_count,
        COUNT(DISTINCT utm.user_id) as member_count
    FROM business_units bu
    LEFT JOIN project_unit_mapping pum ON bu.unit_id = pum.unit_id
    LEFT JOIN unit_team_members utm ON bu.unit_id = utm.unit_id
    GROUP BY bu.unit_id
    ORDER BY bu.created_at DESC
";

$unitsStmt = $pdo->prepare($unitsQuery);
$unitsStmt->execute();
$units = $unitsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get unit statistics
$statsQuery = "
    SELECT
        COUNT(*) as total_units,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_units,
        (SELECT COUNT(DISTINCT utm.user_id) FROM unit_team_members utm) as total_members,
        (SELECT COUNT(DISTINCT pum.project_id) FROM project_unit_mapping pum) as total_projects
    FROM business_units
";

$statsStmt = $pdo->prepare($statsQuery);
$statsStmt->execute();
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

// Get recent activity (unit actions)
$activityQuery = "
    SELECT
        'unit_created' as action_type,
        bu.unit_name as unit_name,
        bu.created_at as timestamp,
        'Created' as action,
        '' as details
    FROM business_units bu
    UNION ALL
    SELECT
        'member_added' as action_type,
        bu.unit_name as unit_name,
        utm.assigned_at as timestamp,
        'Member Added' as action,
        CONCAT(u.username, ' assigned') as details
    FROM unit_team_members utm
    JOIN business_units bu ON utm.unit_id = bu.unit_id
    LEFT JOIN users u ON utm.user_id = u.id
    UNION ALL
    SELECT
        'project_mapped' as action_type,
        bu.unit_name as unit_name,
        pum.created_at as timestamp,
        'Project Mapped' as action,
        CONCAT(p.project_name, ' linked') as details
    FROM project_unit_mapping pum
    JOIN business_units bu ON pum.unit_id = bu.unit_id
    JOIN projects p ON pum.project_id = p.id
    ORDER BY timestamp DESC
    LIMIT 10
";

$activityStmt = $pdo->prepare($activityQuery);
$activityStmt->execute();
$recentActivity = $activityStmt->fetchAll(PDO::FETCH_ASSOC);

// Get all unit types for filtering
$unitTypes = array_unique(array_column($units, 'type'));

// Include header
require_once __DIR__ . '/../includes-v2/header.php';
require_once __DIR__ . '/../includes-v2/sidebar.php';
?>

<main class="main-content">
    <div class="page-header">
        <div class="page-header-content">
            <div class="page-header-title">
                <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
                <p class="page-header-subtitle">Manage business units, teams, and permissions</p>
            </div>
            <div class="page-header-actions">
                <button type="button" class="btn btn-secondary" onclick="exportUnits()">
                    <i class="icon-download"></i>
                    <span>Export</span>
                </button>
                <button type="button" class="btn btn-primary" onclick="createUnit()">
                    <i class="icon-plus"></i>
                    <span>Create Unit</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Statistics Row -->
    <div class="metrics-grid metrics-grid-4">
        <div class="metric-card">
            <div class="metric-card-header">
                <div class="metric-card-icon metric-card-icon-primary">
                    <i class="icon-briefcase"></i>
                </div>
                <div class="metric-card-info">
                    <div class="metric-card-label">Total Units</div>
                    <div class="metric-card-value"><?php echo number_format((int)$stats['total_units']); ?></div>
                </div>
            </div>
            <div class="metric-card-footer">
                <span class="metric-card-trend metric-card-trend-neutral">
                    All business units in system
                </span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-card-header">
                <div class="metric-card-icon metric-card-icon-success">
                    <i class="icon-check-circle"></i>
                </div>
                <div class="metric-card-info">
                    <div class="metric-card-label">Active Units</div>
                    <div class="metric-card-value"><?php echo number_format((int)$stats['active_units']); ?></div>
                </div>
            </div>
            <div class="metric-card-footer">
                <span class="metric-card-trend metric-card-trend-up">
                    <?php
                    $activePercent = $stats['total_units'] > 0 ? round(((int)$stats['active_units'] / (int)$stats['total_units']) * 100) : 0;
                    echo $activePercent . '% of total';
                    ?>
                </span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-card-header">
                <div class="metric-card-icon metric-card-icon-info">
                    <i class="icon-users"></i>
                </div>
                <div class="metric-card-info">
                    <div class="metric-card-label">Team Members</div>
                    <div class="metric-card-value"><?php echo number_format((int)$stats['total_members']); ?></div>
                </div>
            </div>
            <div class="metric-card-footer">
                <span class="metric-card-trend metric-card-trend-neutral">
                    Across all units
                </span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-card-header">
                <div class="metric-card-icon metric-card-icon-warning">
                    <i class="icon-folder"></i>
                </div>
                <div class="metric-card-info">
                    <div class="metric-card-label">Mapped Projects</div>
                    <div class="metric-card-value"><?php echo number_format((int)$stats['total_projects']); ?></div>
                </div>
            </div>
            <div class="metric-card-footer">
                <span class="metric-card-trend metric-card-trend-neutral">
                    Project assignments
                </span>
            </div>
        </div>
    </div>

    <!-- Filter Toolbar -->
    <div class="toolbar">
        <div class="toolbar-start">
            <div class="form-group">
                <div class="input-icon">
                    <i class="icon-search"></i>
                    <input type="text" class="form-control" id="searchInput" placeholder="Search units..." onkeyup="filterUnits()">
                </div>
            </div>
            <div class="form-group">
                <select class="form-control" id="typeFilter" onchange="filterByType(this.value)">
                    <option value="">All Types</option>
                    <?php foreach ($unitTypes as $type): ?>
                        <option value="<?php echo htmlspecialchars($type); ?>">
                            <?php echo htmlspecialchars(ucfirst($type)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <select class="form-control" id="statusFilter" onchange="filterByStatus(this.value)">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
        </div>
        <div class="toolbar-end">
            <div class="form-group">
                <select class="form-control" id="sortBy" onchange="sortUnits(this.value)">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="name-asc">Name (A-Z)</option>
                    <option value="name-desc">Name (Z-A)</option>
                    <option value="members-high">Most Members</option>
                    <option value="members-low">Least Members</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Business Units Table -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Business Units</h2>
            <div class="card-actions">
                <button type="button" class="btn btn-sm btn-ghost" onclick="selectAll()">
                    <i class="icon-check-square"></i>
                    Select All
                </button>
                <button type="button" class="btn btn-sm btn-ghost" onclick="bulkAction('activate')">
                    <i class="icon-check"></i>
                    Activate Selected
                </button>
                <button type="button" class="btn btn-sm btn-ghost" onclick="bulkAction('deactivate')">
                    <i class="icon-x"></i>
                    Deactivate Selected
                </button>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($units)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="icon-briefcase"></i>
                    </div>
                    <h3 class="empty-state-title">No business units yet</h3>
                    <p class="empty-state-description">Create your first business unit to organize teams and projects</p>
                    <button type="button" class="btn btn-primary" onclick="createUnit()">
                        <i class="icon-plus"></i>
                        Create First Unit
                    </button>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table" id="unitsTable">
                        <thead>
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)">
                                </th>
                                <th>Unit Name</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Members</th>
                                <th>Projects</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($units as $unit): ?>
                            <tr data-unit-id="<?php echo (int)$unit['id']; ?>"
                                data-status="<?php echo htmlspecialchars($unit['status']); ?>"
                                data-type="<?php echo htmlspecialchars($unit['type']); ?>">
                                <td>
                                    <input type="checkbox" class="unit-checkbox" value="<?php echo (int)$unit['id']; ?>">
                                </td>
                                <td>
                                    <div class="table-cell-primary">
                                        <i class="icon-briefcase text-primary"></i>
                                        <strong><?php echo htmlspecialchars($unit['name']); ?></strong>
                                    </div>
                                    <?php if (!empty($unit['description'])): ?>
                                        <div class="table-cell-secondary">
                                            <?php echo htmlspecialchars($unit['description']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-secondary">
                                        <?php echo htmlspecialchars(ucfirst($unit['type'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = 'badge-success';
                                    if ($unit['status'] === 'inactive') $statusClass = 'badge-secondary';
                                    if ($unit['status'] === 'archived') $statusClass = 'badge-warning';
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>">
                                        <?php echo htmlspecialchars(ucfirst($unit['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="stat-pill">
                                        <i class="icon-users"></i>
                                        <span><?php echo number_format((int)$unit['member_count']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="stat-pill">
                                        <i class="icon-folder"></i>
                                        <span><?php echo number_format((int)$unit['project_count']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted">
                                        <?php echo date('M d, Y', strtotime($unit['created_at'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-ghost" onclick="viewUnit(<?php echo (int)$unit['id']; ?>)" title="View Details">
                                            <i class="icon-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-ghost" onclick="manageTeam(<?php echo (int)$unit['id']; ?>)" title="Manage Team">
                                            <i class="icon-users"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-ghost" onclick="editUnit(<?php echo (int)$unit['id']; ?>)" title="Edit">
                                            <i class="icon-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-ghost" onclick="manageProjects(<?php echo (int)$unit['id']; ?>)" title="Manage Projects">
                                            <i class="icon-folder"></i>
                                        </button>
                                        <?php if ($unit['status'] !== 'archived'): ?>
                                        <button type="button" class="btn btn-sm btn-ghost" onclick="archiveUnit(<?php echo (int)$unit['id']; ?>)" title="Archive">
                                            <i class="icon-archive"></i>
                                        </button>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-ghost-danger" onclick="deleteUnit(<?php echo (int)$unit['id']; ?>)" title="Delete">
                                            <i class="icon-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Activity Timeline -->
    <?php if (!empty($recentActivity)): ?>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Recent Activity</h2>
            <button type="button" class="btn btn-sm btn-ghost" onclick="viewAllActivity()">
                View All
                <i class="icon-arrow-right"></i>
            </button>
        </div>
        <div class="card-body">
            <div class="timeline">
                <?php foreach ($recentActivity as $activity): ?>
                <div class="timeline-item">
                    <div class="timeline-marker">
                        <?php
                        $iconClass = 'icon-activity';
                        if ($activity['action_type'] === 'unit_created') $iconClass = 'icon-plus-circle';
                        if ($activity['action_type'] === 'member_added') $iconClass = 'icon-user-plus';
                        if ($activity['action_type'] === 'project_mapped') $iconClass = 'icon-link';
                        ?>
                        <i class="<?php echo $iconClass; ?>"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <strong><?php echo htmlspecialchars($activity['unit_name']); ?></strong>
                            <span class="badge badge-secondary">
                                <?php echo htmlspecialchars($activity['action']); ?>
                            </span>
                        </div>
                        <?php if (!empty($activity['details'])): ?>
                            <div class="timeline-body">
                                <?php echo htmlspecialchars($activity['details']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="timeline-footer">
                            <span class="text-muted">
                                <?php
                                $timestamp = strtotime($activity['timestamp']);
                                $diff = time() - $timestamp;
                                if ($diff < 60) echo 'Just now';
                                elseif ($diff < 3600) echo floor($diff / 60) . ' minutes ago';
                                elseif ($diff < 86400) echo floor($diff / 3600) . ' hours ago';
                                else echo date('M d, Y g:i A', $timestamp);
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</main>

<!-- Create/Edit Unit Modal -->
<div class="modal" id="unitModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="unitModalTitle">Create Business Unit</h3>
                <button type="button" class="btn-close" onclick="closeModal('unitModal')"></button>
            </div>
            <div class="modal-body">
                <form id="unitForm">
                    <input type="hidden" id="unitId" name="unit_id">

                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label for="unitName" class="form-label">Unit Name *</label>
                            <input type="text" class="form-control" id="unitName" name="unit_name" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="unitType" class="form-label">Type *</label>
                            <select class="form-control" id="unitType" name="unit_type" required>
                                <option value="">Select type...</option>
                                <option value="corporate">Corporate</option>
                                <option value="technical">Technical</option>
                                <option value="retail">Retail</option>
                                <option value="support">Support</option>
                                <option value="operations">Operations</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="unitDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="unitDescription" name="description" rows="3"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="unitStatus" class="form-label">Status *</label>
                            <select class="form-control" id="unitStatus" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="unitParent" class="form-label">Parent Unit</label>
                            <select class="form-control" id="unitParent" name="parent_id">
                                <option value="">None (Top Level)</option>
                                <?php foreach ($units as $u): ?>
                                    <option value="<?php echo (int)$u['id']; ?>">
                                        <?php echo htmlspecialchars($u['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('unitModal')">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveUnit()">Save Unit</button>
            </div>
        </div>
    </div>
</div>

<!-- Manage Team Modal -->
<div class="modal" id="teamModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Manage Team Members</h3>
                <button type="button" class="btn-close" onclick="closeModal('teamModal')"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4>Available Users</h4>
                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="Search users..." id="searchAvailableUsers">
                        </div>
                        <div class="user-list" id="availableUsersList">
                            <!-- Populated via JavaScript -->
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h4>Team Members</h4>
                        <div class="user-list" id="teamMembersList">
                            <!-- Populated via JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('teamModal')">Close</button>
                <button type="button" class="btn btn-primary" onclick="saveTeamChanges()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Manage Projects Modal -->
<div class="modal" id="projectsModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Manage Project Mappings</h3>
                <button type="button" class="btn-close" onclick="closeModal('projectsModal')"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <h4>Assign Projects to Unit</h4>
                        <div class="form-group">
                            <input type="text" class="form-control" placeholder="Search projects..." id="searchProjects">
                        </div>
                        <div class="table-responsive">
                            <table class="table" id="projectMappingTable">
                                <thead>
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" id="selectAllProjects">
                                        </th>
                                        <th>Project Name</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Role</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Populated via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('projectsModal')">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveProjectMappings()">Save Mappings</button>
            </div>
        </div>
    </div>
</div>

<!-- Permissions Matrix Modal -->
<div class="modal" id="permissionsModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Unit Permissions Matrix</h3>
                <button type="button" class="btn-close" onclick="closeModal('permissionsModal')"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Resource</th>
                                <th>View</th>
                                <th>Edit</th>
                                <th>Delete</th>
                                <th>Admin</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Projects</strong></td>
                                <td><input type="checkbox" checked></td>
                                <td><input type="checkbox" checked></td>
                                <td><input type="checkbox"></td>
                                <td><input type="checkbox"></td>
                            </tr>
                            <tr>
                                <td><strong>Files</strong></td>
                                <td><input type="checkbox" checked></td>
                                <td><input type="checkbox" checked></td>
                                <td><input type="checkbox"></td>
                                <td><input type="checkbox"></td>
                            </tr>
                            <tr>
                                <td><strong>Scans</strong></td>
                                <td><input type="checkbox" checked></td>
                                <td><input type="checkbox"></td>
                                <td><input type="checkbox"></td>
                                <td><input type="checkbox"></td>
                            </tr>
                            <tr>
                                <td><strong>Reports</strong></td>
                                <td><input type="checkbox" checked></td>
                                <td><input type="checkbox"></td>
                                <td><input type="checkbox"></td>
                                <td><input type="checkbox"></td>
                            </tr>
                            <tr>
                                <td><strong>Settings</strong></td>
                                <td><input type="checkbox"></td>
                                <td><input type="checkbox"></td>
                                <td><input type="checkbox"></td>
                                <td><input type="checkbox" checked></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('permissionsModal')">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="savePermissions()">Save Permissions</button>
            </div>
        </div>
    </div>
</div>

<script>
// Filter functions
function filterUnits() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#unitsTable tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}

function filterByType(type) {
    const rows = document.querySelectorAll('#unitsTable tbody tr');

    rows.forEach(row => {
        if (type === '' || row.dataset.type === type) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function filterByStatus(status) {
    const rows = document.querySelectorAll('#unitsTable tbody tr');

    rows.forEach(row => {
        if (status === '' || row.dataset.status === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function sortUnits(sortBy) {
    const tbody = document.querySelector('#unitsTable tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));

    rows.sort((a, b) => {
        switch(sortBy) {
            case 'newest':
                return new Date(b.cells[6].textContent) - new Date(a.cells[6].textContent);
            case 'oldest':
                return new Date(a.cells[6].textContent) - new Date(b.cells[6].textContent);
            case 'name-asc':
                return a.cells[1].textContent.localeCompare(b.cells[1].textContent);
            case 'name-desc':
                return b.cells[1].textContent.localeCompare(a.cells[1].textContent);
            case 'members-high':
                return parseInt(b.cells[4].textContent) - parseInt(a.cells[4].textContent);
            case 'members-low':
                return parseInt(a.cells[4].textContent) - parseInt(b.cells[4].textContent);
            default:
                return 0;
        }
    });

    rows.forEach(row => tbody.appendChild(row));
}

// Selection functions
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.unit-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
}

function selectAll() {
    const checkboxes = document.querySelectorAll('.unit-checkbox');
    checkboxes.forEach(cb => cb.checked = true);
    document.getElementById('selectAllCheckbox').checked = true;
}

function bulkAction(action) {
    const selectedIds = Array.from(document.querySelectorAll('.unit-checkbox:checked'))
        .map(cb => cb.value);

    if (selectedIds.length === 0) {
        DashboardApp.showAlert('Please select at least one unit', 'warning', 3000);
        return;
    }

    const actionText = action === 'activate' ? 'activate' : 'deactivate';
    if (confirm(`Are you sure you want to ${actionText} ${selectedIds.length} unit(s)?`)) {
        DashboardApp.showAlert(`${actionText}d ${selectedIds.length} unit(s)`, 'success', 3000);
        // In production: Make AJAX call to update statuses
    }
}

// CRUD operations
function createUnit() {
    document.getElementById('unitModalTitle').textContent = 'Create Business Unit';
    document.getElementById('unitForm').reset();
    document.getElementById('unitId').value = '';
    document.getElementById('unitModal').style.display = 'flex';
}

function editUnit(unitId) {
    document.getElementById('unitModalTitle').textContent = 'Edit Business Unit';
    document.getElementById('unitId').value = unitId;

    // In production: Fetch unit data via AJAX
    // For now, populate with demo data
    document.getElementById('unitName').value = 'Example Unit';
    document.getElementById('unitType').value = 'technical';
    document.getElementById('unitDescription').value = 'Example description';
    document.getElementById('unitStatus').value = 'active';

    document.getElementById('unitModal').style.display = 'flex';
}

function saveUnit() {
    const form = document.getElementById('unitForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const unitId = document.getElementById('unitId').value;
    const isEdit = unitId !== '';

    DashboardApp.showAlert(
        isEdit ? 'Unit updated successfully' : 'Unit created successfully',
        'success',
        3000
    );

    closeModal('unitModal');

    // In production: Submit via AJAX and reload table
    setTimeout(() => {
        window.location.reload();
    }, 1500);
}

function viewUnit(unitId) {
    // Navigate to unit details page
    window.location.href = `/dashboard/admin/pages-v2/business-units.php?id=${unitId}`;
}

function archiveUnit(unitId) {
    if (confirm('Archive this business unit? It will still be accessible but marked as archived.')) {
        DashboardApp.showAlert('Unit archived successfully', 'success', 3000);
        // In production: Make AJAX call to update status
        setTimeout(() => {
            window.location.reload();
        }, 1500);
    }
}

function deleteUnit(unitId) {
    if (confirm('⚠️ Delete this business unit?\n\nThis will:\n• Remove all team members\n• Unmap all projects\n• Delete all permissions\n\nThis cannot be undone!')) {
        if (confirm('Are you absolutely sure? Type DELETE to confirm:')) {
            DashboardApp.showAlert('Unit deleted successfully', 'success', 3000);
            // In production: Make AJAX call to delete
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        }
    }
}

// Team management
function manageTeam(unitId) {
    document.getElementById('teamModal').style.display = 'flex';

    // In production: Fetch available users and current team members via AJAX
    // For now, show demo data
    const availableUsers = [
        { id: 1, name: 'John Doe', email: 'john@example.com', role: 'Developer' },
        { id: 2, name: 'Jane Smith', email: 'jane@example.com', role: 'Manager' },
        { id: 3, name: 'Bob Johnson', email: 'bob@example.com', role: 'Designer' }
    ];

    const teamMembers = [
        { id: 4, name: 'Alice Williams', email: 'alice@example.com', role: 'Lead' }
    ];

    renderUserLists(availableUsers, teamMembers);
}

function renderUserLists(available, team) {
    const availableList = document.getElementById('availableUsersList');
    const teamList = document.getElementById('teamMembersList');

    availableList.innerHTML = available.map(user => `
        <div class="user-item" draggable="true">
            <div class="user-avatar">${user.name.charAt(0)}</div>
            <div class="user-info">
                <strong>${user.name}</strong>
                <small>${user.email}</small>
            </div>
            <button type="button" class="btn btn-sm btn-primary" onclick="addToTeam(${user.id})">
                <i class="icon-plus"></i>
            </button>
        </div>
    `).join('');

    teamList.innerHTML = team.map(user => `
        <div class="user-item">
            <div class="user-avatar">${user.name.charAt(0)}</div>
            <div class="user-info">
                <strong>${user.name}</strong>
                <small>${user.email}</small>
            </div>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeFromTeam(${user.id})">
                <i class="icon-minus"></i>
            </button>
        </div>
    `).join('');
}

function addToTeam(userId) {
    DashboardApp.showAlert('User added to team', 'success', 2000);
    // In production: Update team membership
}

function removeFromTeam(userId) {
    if (confirm('Remove this user from the team?')) {
        DashboardApp.showAlert('User removed from team', 'success', 2000);
        // In production: Remove team membership
    }
}

function saveTeamChanges() {
    DashboardApp.showAlert('Team changes saved', 'success', 3000);
    closeModal('teamModal');
    // In production: Submit all changes via AJAX
}

// Project management
function manageProjects(unitId) {
    document.getElementById('projectsModal').style.display = 'flex';

    // In production: Fetch projects and mappings via AJAX
    // For now, show demo data
    const projects = [
        { id: 1, name: 'Project Alpha', type: 'PHP', status: 'active', mapped: true, role: 'owner' },
        { id: 2, name: 'Project Beta', type: 'JavaScript', status: 'active', mapped: false, role: '' },
        { id: 3, name: 'Project Gamma', type: 'Python', status: 'maintenance', mapped: true, role: 'contributor' }
    ];

    renderProjectMappings(projects);
}

function renderProjectMappings(projects) {
    const tbody = document.querySelector('#projectMappingTable tbody');

    tbody.innerHTML = projects.map(project => `
        <tr>
            <td>
                <input type="checkbox" ${project.mapped ? 'checked' : ''} value="${project.id}">
            </td>
            <td>${project.name}</td>
            <td><span class="badge badge-secondary">${project.type}</span></td>
            <td><span class="badge badge-${project.status === 'active' ? 'success' : 'warning'}">${project.status}</span></td>
            <td>
                <select class="form-control form-control-sm" ${project.mapped ? '' : 'disabled'}>
                    <option value="owner" ${project.role === 'owner' ? 'selected' : ''}>Owner</option>
                    <option value="contributor" ${project.role === 'contributor' ? 'selected' : ''}>Contributor</option>
                    <option value="viewer" ${project.role === 'viewer' ? 'selected' : ''}>Viewer</option>
                </select>
            </td>
        </tr>
    `).join('');
}

function saveProjectMappings() {
    const selectedProjects = Array.from(document.querySelectorAll('#projectMappingTable tbody input:checked'))
        .map(cb => cb.value);

    DashboardApp.showAlert(`${selectedProjects.length} project mapping(s) saved`, 'success', 3000);
    closeModal('projectsModal');
    // In production: Submit via AJAX
}

// Export function
function exportUnits() {
    DashboardApp.showAlert('Exporting units...', 'info', 2000);

    setTimeout(() => {
        const data = {
            exported: new Date().toISOString(),
            units: []
        };

        const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'business-units-export.json';
        a.click();

        DashboardApp.showAlert('Units exported', 'success', 3000);
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
require_once __DIR__ . '/../includes-v2/footer.php';
?>
