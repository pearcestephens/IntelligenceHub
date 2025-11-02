# ⚙️ ARCHITECTURE ISSUE #3: DASHBOARD CODE CHANGES

**Date:** October 30, 2025
**Focus:** Making dashboard multi-project aware

---

## CHANGE 1: Add Project Selector to Navigation

### New File: `dashboard/admin/includes/project-selector.php`

```php
<?php
/**
 * Project & Business Unit Selector
 * Renders dropdown for switching between projects
 */

// Get business units for dropdown
$unitsQuery = "
    SELECT DISTINCT bu.unit_id, bu.unit_name, COUNT(pum.project_id) as project_count
    FROM business_units bu
    LEFT JOIN project_unit_mapping pum ON bu.unit_id = pum.unit_id
    WHERE bu.active = 1
    GROUP BY bu.unit_id, bu.unit_name
    ORDER BY bu.unit_name ASC
";

$unitsStmt = $pdo->prepare($unitsQuery);
$unitsStmt->execute([]);
$businessUnits = $unitsStmt->fetchAll(PDO::FETCH_ASSOC);

// Get current project
$currentProjectId = $_GET['project'] ?? $_SESSION['current_project_id'] ?? 1;

// Get projects for current unit (or all projects)
$currentUnitId = $_GET['unit'] ?? $_SESSION['current_unit_id'] ?? null;

$projectsQuery = "
    SELECT DISTINCT p.project_id, p.project_name, bu.unit_id, bu.unit_name
    FROM projects p
    LEFT JOIN project_unit_mapping pum ON p.project_id = pum.project_id
    LEFT JOIN business_units bu ON pum.unit_id = bu.unit_id
    WHERE 1=1
";

$projectParams = [];
if ($currentUnitId) {
    $projectsQuery .= " AND bu.unit_id = ?";
    $projectParams[] = $currentUnitId;
}

$projectsQuery .= " ORDER BY p.project_name ASC";

$projectsStmt = $pdo->prepare($projectsQuery);
$projectsStmt->execute($projectParams);
$projects = $projectsStmt->fetchAll(PDO::FETCH_ASSOC);

// Store in session
$_SESSION['current_project_id'] = $currentProjectId;
$_SESSION['current_unit_id'] = $currentUnitId;
?>

<!-- Project Selector UI -->
<div class="project-selector mb-3">
    <div class="row g-2">
        <!-- Business Unit Selector -->
        <div class="col-md-6">
            <label class="form-label">Business Unit</label>
            <select class="form-select" id="unitSelector" onchange="switchUnit(this.value)">
                <option value="">All Units</option>
                <?php foreach ($businessUnits as $unit): ?>
                    <option value="<?php echo $unit['unit_id']; ?>"
                            <?php echo $currentUnitId === $unit['unit_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($unit['unit_name']); ?>
                        (<?php echo $unit['project_count']; ?> projects)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Project Selector -->
        <div class="col-md-6">
            <label class="form-label">Project</label>
            <select class="form-select" id="projectSelector" onchange="switchProject(this.value)">
                <option value="">Select Project...</option>
                <?php foreach ($projects as $project): ?>
                    <option value="<?php echo $project['project_id']; ?>"
                            <?php echo $currentProjectId === $project['project_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($project['project_name']); ?>
                        <?php if ($project['unit_name']): ?>
                            - <?php echo htmlspecialchars($project['unit_name']); ?>
                        <?php endif; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>

<script>
function switchUnit(unitId) {
    const params = new URLSearchParams(window.location.search);
    params.set('unit', unitId);
    params.delete('project'); // Reset project when unit changes
    window.location.href = '?' + params.toString();
}

function switchProject(projectId) {
    const params = new URLSearchParams(window.location.search);
    params.set('project', projectId);
    window.location.href = '?' + params.toString();
}
</script>

<style>
.project-selector {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border-left: 4px solid #0066cc;
}
</style>
```

---

## CHANGE 2: Update All Dashboard Pages

### Before (Current - ALL 7 PAGES)
```php
<?php
$projectId = 1;  // ← HARDCODED
```

### After (New - ALL 7 PAGES)
```php
<?php
// Get project from query param or session
$projectId = (int)($_GET['project'] ?? $_SESSION['current_project_id'] ?? 1);

// Validate project exists
$projectValidation = "SELECT project_id FROM projects WHERE project_id = ?";
$validateStmt = $pdo->prepare($projectValidation);
$validateStmt->execute([$projectId]);
if (!$validateStmt->fetch()) {
    die('Project not found');
}

// Get business unit info
$unitQuery = "
    SELECT bu.* FROM business_units bu
    JOIN project_unit_mapping pum ON bu.unit_id = pum.unit_id
    WHERE pum.project_id = ? AND pum.role = 'primary'
    LIMIT 1
";
$unitStmt = $pdo->prepare($unitQuery);
$unitStmt->execute([$projectId]);
$businessUnit = $unitStmt->fetch(PDO::FETCH_ASSOC);

// Store in session
$_SESSION['current_project_id'] = $projectId;
if ($businessUnit) {
    $_SESSION['current_unit_id'] = $businessUnit['unit_id'];
}
```

---

## CHANGE 3: Include Project Selector in Layout

### File: `dashboard/admin/layouts/header.php`

```php
<?php
// At top of header after opening <body>
include 'includes/project-selector.php';
?>

<!-- Then render rest of header -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <!-- existing nav code -->
</nav>
```

---

## CHANGE 4: Update All Query WHERE Clauses

### Pattern in all pages:

**Before:**
```php
$query = "SELECT * FROM intelligence_files WHERE 1=1";
```

**After:**
```php
$query = "
    SELECT f.* FROM intelligence_files f
    JOIN projects p ON f.project_id = p.project_id
    WHERE p.project_id = ?
";
$params[] = $projectId;
```

This ensures queries are scoped to the selected project.

---

## CHANGE 5: Add Project Path Info to Queries

### Use project.path for selective scanning

```php
// Get project path for relative file filtering
$projectQuery = "SELECT path FROM projects WHERE project_id = ?";
$projectStmt = $pdo->prepare($projectQuery);
$projectStmt->execute([$projectId]);
$projectData = $projectStmt->fetch(PDO::FETCH_ASSOC);

$projectPath = $projectData['path'] ?? '/unknown';

// Now can filter files relative to project path
// Example: Show only files under /src folder
$query = "
    SELECT * FROM intelligence_files
    WHERE project_id = ?
    AND file_path LIKE ?
";
$params = [$projectId, $projectPath . '/src/%'];
```

---

## CHANGE 6: Create New Pages

### `dashboard/admin/pages/projects.php` (Project Management)
- [ ] List all projects with unit assignment
- [ ] Show project metrics (files, violations, etc)
- [ ] Create/Edit/Delete projects
- [ ] Assign to business units

### `dashboard/admin/pages/business-units.php` (Unit Management)
- [ ] List business units
- [ ] Create/Edit/Delete units
- [ ] Assign projects to units
- [ ] Configure URL source mapping

### `dashboard/admin/pages/scan-config.php` (Selective Scanning)
- [ ] Define scan configurations per project
- [ ] Choose folders/files to include/exclude
- [ ] Set scanning schedule
- [ ] Trigger manual scans with specific config

### `dashboard/admin/pages/scan-history.php` (Audit Trail)
- [ ] View all scans for selected project
- [ ] Show scan status, duration, files affected
- [ ] Drill down to scan details
- [ ] Re-run previous scans

---

## IMPLEMENTATION CHECKLIST

- [ ] Create project-selector.php include
- [ ] Update header.php to include selector
- [ ] Remove hardcoded projectId from all 7 pages
- [ ] Add project validation to all 7 pages
- [ ] Update all queries to include project_id WHERE clause
- [ ] Create projects.php management page
- [ ] Create business-units.php management page
- [ ] Create scan-config.php configuration page
- [ ] Create scan-history.php audit page
- [ ] Test switching between projects
- [ ] Test business unit filtering
- [ ] Test selective scanning

---

## NEXT STEPS

1. ✅ Architecture issues identified (Issue #1)
2. ✅ Database schema designed (Issue #2)
3. ✅ Dashboard code changes documented (Issue #3 - THIS)
4. ⏳ Implementation begins (Issue #4)

See: **ARCHITECTURE_ISSUE_04_IMPLEMENTATION_PLAN.md**
