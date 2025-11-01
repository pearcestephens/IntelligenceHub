<?php
/**
 * Project Selector Component
 *
 * Displays business units and projects for multi-project navigation
 * Handles session storage of current project/unit selection
 *
 * File: /dashboard/admin/includes/project-selector.php
 * Created: October 30, 2025
 */

declare(strict_types=1);

// Ensure app.php is loaded for database access
if (!isset($pdo)) {
    require_once $_SERVER['DOCUMENT_ROOT'] . '/app.php';
}

// Initialize session variables if not set
if (!isset($_SESSION['current_unit_id'])) {
    $_SESSION['current_unit_id'] = 1; // Default to Intelligence Hub
}
if (!isset($_SESSION['current_project_id'])) {
    $_SESSION['current_project_id'] = 1; // Default to first project
}

// ============================================================================
// HANDLER: Update project/unit selection via AJAX or form submission
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'select_unit') {
        $unitId = (int)($_POST['unit_id'] ?? 1);

        // Validate unit exists
        $validateUnit = "SELECT unit_id FROM business_units WHERE unit_id = ? AND is_active = 1";
        $stmt = $pdo->prepare($validateUnit);
        $stmt->execute([$unitId]);

        if ($stmt->fetch()) {
            $_SESSION['current_unit_id'] = $unitId;

            // Also update project to first one from this unit
            $firstProject = "
                SELECT p.id FROM projects p
                LEFT JOIN project_unit_mapping m ON p.id = m.project_id
                WHERE (m.unit_id = ? OR m.unit_id IS NULL) AND p.status = 'active'
                LIMIT 1
            ";
            $pStmt = $pdo->prepare($firstProject);
            $pStmt->execute([$unitId]);
            if ($proj = $pStmt->fetch(PDO::FETCH_ASSOC)) {
                $_SESSION['current_project_id'] = $proj['id'];
            }

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'unit_id' => $unitId]);
            exit;
        }
    }

    if ($_POST['action'] === 'select_project') {
        $projectId = (int)($_POST['project_id'] ?? 1);

        // Validate project exists and is accessible to current unit
        $validateProject = "
            SELECT p.id FROM projects p
            LEFT JOIN project_unit_mapping m ON p.id = m.project_id
            WHERE p.id = ? AND p.status = 'active'
            AND (m.unit_id = ? OR m.unit_id IS NULL)
        ";
        $stmt = $pdo->prepare($validateProject);
        $stmt->execute([$projectId, $_SESSION['current_unit_id']]);

        if ($stmt->fetch()) {
            $_SESSION['current_project_id'] = $projectId;

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'project_id' => $projectId]);
            exit;
        }
    }
}

// ============================================================================
// FUNCTION: Get all active business units
// ============================================================================

function getBusinessUnits($pdo): array {
    $query = "
        SELECT
            unit_id,
            unit_name,
            unit_type,
            domain_mapping,
            intelligence_level,
            is_active
        FROM business_units
        WHERE is_active = 1
        ORDER BY unit_name ASC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

// ============================================================================
// FUNCTION: Get projects for a specific unit
// ============================================================================

function getProjectsForUnit($pdo, int $unitId): array {
    $query = "
        SELECT DISTINCT
            p.id,
            p.project_name,
            p.project_type,
            p.status,
            p.project_path,
            m.url_source,
            m.role
        FROM projects p
        LEFT JOIN project_unit_mapping m ON p.id = m.project_id
        WHERE (m.unit_id = ? OR m.unit_id IS NULL)
        AND p.status = 'active'
        ORDER BY m.role DESC, p.project_name ASC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$unitId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

// ============================================================================
// FUNCTION: Get all active projects (no unit filter)
// ============================================================================

function getAllProjects($pdo): array {
    $query = "
        SELECT
            id,
            project_name,
            project_type,
            status,
            project_path
        FROM projects
        WHERE status = 'active'
        ORDER BY project_name ASC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

// ============================================================================
// FUNCTION: Get current unit info
// ============================================================================

function getCurrentUnitInfo($pdo, int $unitId): ?array {
    $query = "
        SELECT
            unit_id,
            unit_name,
            unit_type,
            domain_mapping,
            intelligence_level
        FROM business_units
        WHERE unit_id = ?
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$unitId]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// ============================================================================
// FUNCTION: Get current project info
// ============================================================================

function getCurrentProjectInfo($pdo, int $projectId): ?array {
    $query = "
        SELECT
            id,
            project_name,
            project_type,
            status,
            project_path
        FROM projects
        WHERE id = ?
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$projectId]);

    return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
}

// ============================================================================
// RENDER: Unit/Project Navigation UI
// ============================================================================

function renderProjectSelector($pdo, int $currentUnitId, int $currentProjectId): string {
    $units = getBusinessUnits($pdo);
    $projects = getProjectsForUnit($pdo, $currentUnitId);
    $currentUnit = getCurrentUnitInfo($pdo, $currentUnitId);
    $currentProject = getCurrentProjectInfo($pdo, $currentProjectId);

    $html = '<div class="project-selector-widget" id="project-selector">';

    // Unit Selector
    $html .= '<div class="unit-selector">';
    $html .= '<label for="unit-dropdown">Business Unit:</label>';
    $html .= '<select id="unit-dropdown" class="form-control form-control-sm" onchange="selectUnit(this.value)">';

    foreach ($units as $unit) {
        $selected = ($unit['unit_id'] === $currentUnitId) ? 'selected' : '';
        $html .= sprintf(
            '<option value="%d" %s>%s (%s)</option>',
            $unit['unit_id'],
            $selected,
            htmlspecialchars($unit['unit_name']),
            htmlspecialchars($unit['unit_type'])
        );
    }

    $html .= '</select>';
    $html .= '</div>';

    // Project Selector
    $html .= '<div class="project-selector">';
    $html .= '<label for="project-dropdown">Project:</label>';
    $html .= '<select id="project-dropdown" class="form-control form-control-sm" onchange="selectProject(this.value)">';

    foreach ($projects as $project) {
        $selected = ($project['id'] === $currentProjectId) ? 'selected' : '';
        $badge = $project['role'] ? sprintf(' <span class="badge badge-info">%s</span>', htmlspecialchars($project['role'])) : '';
        $html .= sprintf(
            '<option value="%d" %s>%s %s</option>',
            $project['id'],
            $selected,
            htmlspecialchars($project['project_name']),
            $badge
        );
    }

    $html .= '</select>';
    $html .= '</div>';

    // Status Info
    $html .= '<div class="selector-status">';
    $html .= sprintf(
        '<small>Selected: <strong>%s</strong> → <strong>%s</strong></small>',
        htmlspecialchars($currentUnit['unit_name'] ?? 'N/A'),
        htmlspecialchars($currentProject['project_name'] ?? 'N/A')
    );
    $html .= '</div>';

    $html .= '</div>';

    // Add JavaScript handler
    $html .= '<script>
function selectUnit(unitId) {
    fetch("' . htmlspecialchars($_SERVER['REQUEST_URI']) . '", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "action=select_unit&unit_id=" + encodeURIComponent(unitId)
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            location.reload();
        }
    })
    .catch(e => console.error("Unit selection failed:", e));
}

function selectProject(projectId) {
    fetch("' . htmlspecialchars($_SERVER['REQUEST_URI']) . '", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: "action=select_project&project_id=" + encodeURIComponent(projectId)
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            location.reload();
        }
    })
    .catch(e => console.error("Project selection failed:", e));
}
    </script>';

    // Add CSS styles
    $html .= '<style>
.project-selector-widget {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 12px;
    margin-bottom: 16px;
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 12px;
    align-items: flex-end;
}

.project-selector-widget label {
    margin-bottom: 4px;
    font-weight: 600;
    font-size: 0.875rem;
}

.unit-selector, .project-selector {
    display: flex;
    flex-direction: column;
}

.selector-status {
    padding: 8px 12px;
    background: white;
    border-radius: 3px;
    border-left: 3px solid #007bff;
    text-align: center;
}

@media (max-width: 768px) {
    .project-selector-widget {
        grid-template-columns: 1fr;
    }
}
    </style>';

    return $html;
}

// Export current project/unit ID for use in dashboard pages
$currentProjectId = $_SESSION['current_project_id'] ?? 1;
$currentUnitId = $_SESSION['current_unit_id'] ?? 1;
