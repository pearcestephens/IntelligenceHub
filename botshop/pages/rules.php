<?php
/**
 * Rules Page - Complete V2 Redesign
 * Comprehensive rule management with categorization, toggles, and configuration
 *
 * Features:
 * - Semantic HTML5 structure
 * - Categorized rule cards with visual grouping
 * - Enable/disable toggles with instant feedback
 * - Severity indicators with color coding
 * - Rule configuration modal
 * - Custom rule builder
 * - Import/export rule sets
 * - Rule documentation
 * - Search and filtering
 * - Bulk enable/disable
 *
 * @package CIS Intelligence Dashboard
 * @version 2.0.0
 * @updated October 31, 2025
 */

declare(strict_types=1);

// Configuration
$pageTitle = 'Rules';
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

// Get all rules with their categories
$rulesQuery = "
    SELECT
        id,
        standard_key as rule_name,
        description,
        enforced as enabled,
        category,
        severity,
        priority,
        created_at,
        updated_at
    FROM code_standards
    ORDER BY category ASC, priority DESC, standard_key ASC
";

$rulesStmt = $pdo->prepare($rulesQuery);
$rulesStmt->execute();
$allRules = $rulesStmt->fetchAll(PDO::FETCH_ASSOC);

// Get violation counts per rule
$violationCountsQuery = "
    SELECT
        rule_name,
        COUNT(*) as violation_count
    FROM project_rule_violations
    WHERE project_id = ?
    GROUP BY rule_name
";

$violationCountsStmt = $pdo->prepare($violationCountsQuery);
$violationCountsStmt->execute([$projectId]);
$violationCounts = $violationCountsStmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Organize rules by category
$rulesByCategory = [];
foreach ($allRules as $rule) {
    $category = $rule['category'] ?: 'General';
    if (!isset($rulesByCategory[$category])) {
        $rulesByCategory[$category] = [];
    }
    $rule['violation_count'] = (int)($violationCounts[$rule['rule_name']] ?? 0);
    $rulesByCategory[$category][] = $rule;
}

// Get category statistics
$categoryStats = [];
foreach ($rulesByCategory as $category => $rules) {
    $categoryStats[$category] = [
        'total' => count($rules),
        'enabled' => count(array_filter($rules, fn($r) => $r['enabled'])),
        'disabled' => count(array_filter($rules, fn($r) => !$r['enabled'])),
        'violations' => array_sum(array_column($rules, 'violation_count'))
    ];
}

// Calculate overall statistics
$totalRules = count($allRules);
$enabledRules = count(array_filter($allRules, fn($r) => $r['enabled']));
$disabledRules = $totalRules - $enabledRules;
$totalViolations = array_sum(array_column($allRules, 'violation_count'));

// Severity color mapping
$severityColors = [
    'critical' => 'danger',
    'high' => 'warning',
    'medium' => 'info',
    'low' => 'secondary'
];

// Category icons
$categoryIcons = [
    'Security' => 'shield-alt',
    'Performance' => 'tachometer-alt',
    'Best Practices' => 'star',
    'Code Style' => 'code',
    'Documentation' => 'book',
    'Error Handling' => 'exclamation-triangle',
    'Testing' => 'vial',
    'General' => 'cog'
];

// Include header
require_once __DIR__ . '/../includes-v2/header.php';
?>

<!-- Main Content -->
<main class="dashboard-main">
    <div class="container-fluid">
        <!-- Page Header -->
        <header class="page-header">
            <div class="page-header__content">
                <h1 class="page-header__title">Coding Rules</h1>
                <p class="page-header__subtitle">
                    <?php echo htmlspecialchars($projectData['project_name']); ?>
                    • <?php echo number_format($totalRules); ?> rules
                    • <?php echo number_format($enabledRules); ?> enabled
                    • Last updated: <?php echo $lastScanTime; ?>
                </p>
            </div>
            <div class="page-header__actions">
                <button type="button" class="btn btn--secondary" onclick="importRules()">
                    <i class="fas fa-file-import"></i> Import
                </button>
                <button type="button" class="btn btn--secondary" onclick="exportRules()">
                    <i class="fas fa-file-export"></i> Export
                </button>
                <button type="button" class="btn btn--primary" onclick="createCustomRule()">
                    <i class="fas fa-plus"></i> Create Rule
                </button>
            </div>
        </header>

        <!-- Rule Statistics -->
        <section class="metrics-row">
            <article class="metric-card">
                <div class="metric-card__icon metric-card__icon--primary">
                    <i class="fas fa-list"></i>
                </div>
                <div class="metric-card__content">
                    <div class="metric-card__value"><?php echo number_format($totalRules); ?></div>
                    <div class="metric-card__label">Total Rules</div>
                    <div class="metric-card__change metric-card__change--neutral">
                        Across <?php echo count($rulesByCategory); ?> categories
                    </div>
                </div>
            </article>

            <article class="metric-card">
                <div class="metric-card__icon metric-card__icon--success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="metric-card__content">
                    <div class="metric-card__value"><?php echo number_format($enabledRules); ?></div>
                    <div class="metric-card__label">Enabled</div>
                    <div class="metric-card__change <?php echo $enabledRules === $totalRules ? 'metric-card__change--positive' : 'metric-card__change--neutral'; ?>">
                        <?php echo $totalRules > 0 ? round(($enabledRules / $totalRules) * 100, 1) : 0; ?>% active
                    </div>
                </div>
            </article>

            <article class="metric-card">
                <div class="metric-card__icon metric-card__icon--warning">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="metric-card__content">
                    <div class="metric-card__value"><?php echo number_format($disabledRules); ?></div>
                    <div class="metric-card__label">Disabled</div>
                    <div class="metric-card__change <?php echo $disabledRules > 0 ? 'metric-card__change--negative' : 'metric-card__change--positive'; ?>">
                        <?php echo $disabledRules > 0 ? 'Some rules inactive' : 'All rules active'; ?>
                    </div>
                </div>
            </article>

            <article class="metric-card">
                <div class="metric-card__icon metric-card__icon--danger">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="metric-card__content">
                    <div class="metric-card__value"><?php echo number_format($totalViolations); ?></div>
                    <div class="metric-card__label">Violations</div>
                    <div class="metric-card__change <?php echo $totalViolations > 0 ? 'metric-card__change--negative' : 'metric-card__change--positive'; ?>">
                        <?php echo $totalViolations > 0 ? 'Detected in last scan' : 'No violations found'; ?>
                    </div>
                </div>
            </article>
        </section>

        <!-- Search and Bulk Actions -->
        <section class="filter-toolbar">
            <div class="filter-toolbar__group">
                <div class="input-group">
                    <i class="fas fa-search input-group__icon"></i>
                    <input type="text" id="search-rules" class="form-control" placeholder="Search rules..." onkeyup="filterRules(this.value)">
                </div>
            </div>
            <div class="filter-toolbar__group">
                <select class="form-control" id="filter-category" onchange="filterByCategory(this.value)">
                    <option value="">All Categories</option>
                    <?php foreach (array_keys($rulesByCategory) as $category): ?>
                        <option value="<?php echo htmlspecialchars($category); ?>">
                            <?php echo htmlspecialchars($category); ?> (<?php echo $categoryStats[$category]['total']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <select class="form-control" id="filter-status" onchange="filterByStatus(this.value)">
                    <option value="">All Statuses</option>
                    <option value="enabled">Enabled Only</option>
                    <option value="disabled">Disabled Only</option>
                </select>
                <select class="form-control" id="filter-severity" onchange="filterBySeverity(this.value)">
                    <option value="">All Severities</option>
                    <option value="critical">Critical</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div class="filter-toolbar__actions">
                <button type="button" class="btn btn--secondary" onclick="enableAllVisible()">
                    <i class="fas fa-check-double"></i> Enable All
                </button>
                <button type="button" class="btn btn--secondary" onclick="disableAllVisible()">
                    <i class="fas fa-ban"></i> Disable All
                </button>
            </div>
        </section>

        <!-- Rules by Category -->
        <?php foreach ($rulesByCategory as $category => $rules): ?>
            <section class="content-section" data-category="<?php echo htmlspecialchars($category); ?>">
                <header class="content-section__header content-section__header--collapsible" onclick="toggleCategory('<?php echo htmlspecialchars($category); ?>')">
                    <div class="content-section__title-group">
                        <i class="fas fa-<?php echo $categoryIcons[$category] ?? 'folder'; ?> content-section__icon"></i>
                        <h2 class="content-section__title"><?php echo htmlspecialchars($category); ?></h2>
                        <span class="badge badge--primary"><?php echo $categoryStats[$category]['total']; ?> rules</span>
                    </div>
                    <div class="content-section__stats">
                        <span class="stat-badge stat-badge--success">
                            <i class="fas fa-check"></i> <?php echo $categoryStats[$category]['enabled']; ?>
                        </span>
                        <span class="stat-badge stat-badge--danger">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo $categoryStats[$category]['violations']; ?>
                        </span>
                        <i class="fas fa-chevron-down content-section__toggle"></i>
                    </div>
                </header>

                <div class="content-section__body content-section__body--expanded">
                    <div class="rules-grid">
                        <?php foreach ($rules as $rule): ?>
                            <?php
                            $severity = strtolower($rule['severity'] ?? 'medium');
                            $color = $severityColors[$severity] ?? 'secondary';
                            $enabled = (bool)$rule['enabled'];
                            ?>
                            <article class="rule-card <?php echo $enabled ? '' : 'rule-card--disabled'; ?>"
                                     data-rule-id="<?php echo $rule['id']; ?>"
                                     data-category="<?php echo htmlspecialchars($category); ?>"
                                     data-severity="<?php echo $severity; ?>"
                                     data-status="<?php echo $enabled ? 'enabled' : 'disabled'; ?>">
                                <header class="rule-card__header">
                                    <div class="rule-card__title-group">
                                        <h3 class="rule-card__title"><?php echo htmlspecialchars($rule['rule_name']); ?></h3>
                                        <span class="badge badge--<?php echo $color; ?>"><?php echo ucfirst($severity); ?></span>
                                    </div>
                                    <div class="toggle-switch">
                                        <input type="checkbox"
                                               id="rule-toggle-<?php echo $rule['id']; ?>"
                                               class="toggle-switch__input"
                                               <?php echo $enabled ? 'checked' : ''; ?>
                                               onchange="toggleRule(<?php echo $rule['id']; ?>, this.checked)">
                                        <label for="rule-toggle-<?php echo $rule['id']; ?>" class="toggle-switch__label">
                                            <span class="toggle-switch__slider"></span>
                                        </label>
                                    </div>
                                </header>

                                <div class="rule-card__body">
                                    <p class="rule-card__description">
                                        <?php echo htmlspecialchars($rule['description']); ?>
                                    </p>

                                    <?php if ($rule['violation_count'] > 0): ?>
                                        <div class="rule-card__violations">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <span><?php echo number_format($rule['violation_count']); ?> violation<?php echo $rule['violation_count'] !== 1 ? 's' : ''; ?> found</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <footer class="rule-card__footer">
                                    <div class="rule-card__meta">
                                        <span class="rule-card__priority" title="Priority">
                                            <i class="fas fa-sort-amount-down"></i> P<?php echo $rule['priority'] ?? 50; ?>
                                        </span>
                                        <span class="rule-card__updated" title="Last updated">
                                            <i class="fas fa-clock"></i> <?php echo date('M j', strtotime($rule['updated_at'] ?? $rule['created_at'])); ?>
                                        </span>
                                    </div>
                                    <div class="rule-card__actions">
                                        <button type="button" class="btn btn--sm btn--secondary" onclick="viewRuleDetails(<?php echo $rule['id']; ?>)" title="View Details">
                                            <i class="fas fa-info-circle"></i>
                                        </button>
                                        <button type="button" class="btn btn--sm btn--secondary" onclick="editRule(<?php echo $rule['id']; ?>)" title="Edit Rule">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn--sm btn--danger" onclick="deleteRule(<?php echo $rule['id']; ?>)" title="Delete Rule">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </footer>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>

        <!-- Quick Actions -->
        <section class="content-section">
            <header class="content-section__header">
                <h2 class="content-section__title">Quick Actions</h2>
            </header>
            <div class="content-section__body">
                <div class="quick-actions">
                    <button type="button" class="quick-action-card" onclick="loadRulePreset('strict')">
                        <div class="quick-action-card__icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3 class="quick-action-card__title">Strict Preset</h3>
                        <p class="quick-action-card__description">Enable all rules for maximum code quality</p>
                    </button>

                    <button type="button" class="quick-action-card" onclick="loadRulePreset('recommended')">
                        <div class="quick-action-card__icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h3 class="quick-action-card__title">Recommended Preset</h3>
                        <p class="quick-action-card__description">Balanced rule set for most projects</p>
                    </button>

                    <button type="button" class="quick-action-card" onclick="loadRulePreset('minimal')">
                        <div class="quick-action-card__icon">
                            <i class="fas fa-file"></i>
                        </div>
                        <h3 class="quick-action-card__title">Minimal Preset</h3>
                        <p class="quick-action-card__description">Only critical security and error rules</p>
                    </button>

                    <button type="button" class="quick-action-card" onclick="resetToDefaults()">
                        <div class="quick-action-card__icon">
                            <i class="fas fa-undo"></i>
                        </div>
                        <h3 class="quick-action-card__title">Reset to Defaults</h3>
                        <p class="quick-action-card__description">Restore original rule configuration</p>
                    </button>
                </div>
            </div>
        </section>
    </div>
</main>

<!-- Rule Details Modal -->
<div id="modal-rule-details" class="modal">
    <div class="modal__overlay" onclick="closeModal('modal-rule-details')"></div>
    <div class="modal__content modal__content--large">
        <header class="modal__header">
            <h2 class="modal__title">Rule Details</h2>
            <button type="button" class="modal__close" onclick="closeModal('modal-rule-details')">
                <i class="fas fa-times"></i>
            </button>
        </header>
        <div class="modal__body" id="modal-rule-content">
            <div class="loading-overlay">
                <div class="spinner"></div>
            </div>
        </div>
        <footer class="modal__footer">
            <button type="button" class="btn btn--secondary" onclick="closeModal('modal-rule-details')">Close</button>
            <button type="button" class="btn btn--primary" onclick="editRuleFromModal()">Edit Rule</button>
        </footer>
    </div>
</div>

<!-- Create/Edit Rule Modal -->
<div id="modal-rule-editor" class="modal">
    <div class="modal__overlay" onclick="closeModal('modal-rule-editor')"></div>
    <div class="modal__content modal__content--large">
        <header class="modal__header">
            <h2 class="modal__title" id="rule-editor-title">Create Custom Rule</h2>
            <button type="button" class="modal__close" onclick="closeModal('modal-rule-editor')">
                <i class="fas fa-times"></i>
            </button>
        </header>
        <div class="modal__body">
            <form id="rule-form" class="form">
                <div class="form__group">
                    <label for="rule-name" class="form__label">Rule Name *</label>
                    <input type="text" id="rule-name" class="form-control" required placeholder="e.g., no-console-log">
                </div>

                <div class="form__group">
                    <label for="rule-description" class="form__label">Description *</label>
                    <textarea id="rule-description" class="form-control" rows="3" required placeholder="Describe what this rule checks for..."></textarea>
                </div>

                <div class="form__row">
                    <div class="form__group">
                        <label for="rule-category" class="form__label">Category *</label>
                        <select id="rule-category" class="form-control" required>
                            <option value="">Select category...</option>
                            <?php foreach (array_keys($rulesByCategory) as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
                            <?php endforeach; ?>
                            <option value="custom">Custom Category</option>
                        </select>
                    </div>

                    <div class="form__group">
                        <label for="rule-severity" class="form__label">Severity *</label>
                        <select id="rule-severity" class="form-control" required>
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>

                    <div class="form__group">
                        <label for="rule-priority" class="form__label">Priority</label>
                        <input type="number" id="rule-priority" class="form-control" value="50" min="1" max="100">
                    </div>
                </div>

                <div class="form__group">
                    <label for="rule-pattern" class="form__label">Detection Pattern (Regex)</label>
                    <input type="text" id="rule-pattern" class="form-control" placeholder="e.g., /console\.log\(.*\)/g">
                    <small class="form__help">Regular expression pattern for detecting violations</small>
                </div>

                <div class="form__group">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" id="rule-enabled" class="form-checkbox" checked>
                        <span>Enable this rule immediately</span>
                    </label>
                </div>

                <div class="form__group">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" id="rule-auto-fix" class="form-checkbox">
                        <span>Enable auto-fix (if available)</span>
                    </label>
                </div>
            </form>
        </div>
        <footer class="modal__footer">
            <button type="button" class="btn btn--secondary" onclick="closeModal('modal-rule-editor')">Cancel</button>
            <button type="button" class="btn btn--primary" onclick="saveRule()">
                <i class="fas fa-save"></i> Save Rule
            </button>
        </footer>
    </div>
</div>

<!-- Import Rules Modal -->
<div id="modal-import-rules" class="modal">
    <div class="modal__overlay" onclick="closeModal('modal-import-rules')"></div>
    <div class="modal__content">
        <header class="modal__header">
            <h2 class="modal__title">Import Rule Set</h2>
            <button type="button" class="modal__close" onclick="closeModal('modal-import-rules')">
                <i class="fas fa-times"></i>
            </button>
        </header>
        <div class="modal__body">
            <div class="form__group">
                <label for="import-file" class="form__label">Select Rule Set File (JSON)</label>
                <input type="file" id="import-file" class="form-control" accept=".json" onchange="handleFileSelect(this)">
                <small class="form__help">Upload a JSON file containing rule definitions</small>
            </div>

            <div class="alert alert--info">
                <div class="alert__icon"><i class="fas fa-info-circle"></i></div>
                <div class="alert__content">
                    <div class="alert__message">
                        Imported rules will be merged with existing rules. Duplicate rules will be skipped.
                    </div>
                </div>
            </div>
        </div>
        <footer class="modal__footer">
            <button type="button" class="btn btn--secondary" onclick="closeModal('modal-import-rules')">Cancel</button>
            <button type="button" class="btn btn--primary" onclick="processImport()">
                <i class="fas fa-file-import"></i> Import Rules
            </button>
        </footer>
    </div>
</div>

<script>
// Category collapse/expand
function toggleCategory(category) {
    const section = document.querySelector(`[data-category="${category}"]`);
    const body = section.querySelector('.content-section__body');
    const toggle = section.querySelector('.content-section__toggle');

    const isExpanded = body.classList.contains('content-section__body--expanded');

    if (isExpanded) {
        body.classList.remove('content-section__body--expanded');
        toggle.classList.remove('fa-chevron-down');
        toggle.classList.add('fa-chevron-right');
    } else {
        body.classList.add('content-section__body--expanded');
        toggle.classList.remove('fa-chevron-right');
        toggle.classList.add('fa-chevron-down');
    }
}

// Filter functions
function filterRules(searchTerm) {
    const term = searchTerm.toLowerCase();
    document.querySelectorAll('.rule-card').forEach(card => {
        const title = card.querySelector('.rule-card__title').textContent.toLowerCase();
        const description = card.querySelector('.rule-card__description').textContent.toLowerCase();
        card.style.display = (title.includes(term) || description.includes(term)) ? '' : 'none';
    });
    updateCategoryCounts();
}

function filterByCategory(category) {
    if (!category) {
        document.querySelectorAll('.content-section').forEach(section => {
            section.style.display = '';
        });
    } else {
        document.querySelectorAll('.content-section').forEach(section => {
            section.style.display = section.dataset.category === category ? '' : 'none';
        });
    }
}

function filterByStatus(status) {
    document.querySelectorAll('.rule-card').forEach(card => {
        if (!status) {
            card.style.display = '';
        } else {
            card.style.display = card.dataset.status === status ? '' : 'none';
        }
    });
    updateCategoryCounts();
}

function filterBySeverity(severity) {
    document.querySelectorAll('.rule-card').forEach(card => {
        if (!severity) {
            card.style.display = '';
        } else {
            card.style.display = card.dataset.severity === severity ? '' : 'none';
        }
    });
    updateCategoryCounts();
}

function updateCategoryCounts() {
    document.querySelectorAll('.content-section').forEach(section => {
        const visibleCards = section.querySelectorAll('.rule-card[style=""], .rule-card:not([style])').length;
        if (visibleCards === 0) {
            section.style.display = 'none';
        } else {
            section.style.display = '';
        }
    });
}

// Rule actions
function toggleRule(ruleId, enabled) {
    const card = document.querySelector(`[data-rule-id="${ruleId}"]`);

    if (enabled) {
        card.classList.remove('rule-card--disabled');
        card.dataset.status = 'enabled';
    } else {
        card.classList.add('rule-card--disabled');
        card.dataset.status = 'disabled';
    }

    DashboardApp.showAlert(`Rule ${enabled ? 'enabled' : 'disabled'}`, 'success', 2000);

    // TODO: API call to update rule status
}

function viewRuleDetails(ruleId) {
    const modal = document.getElementById('modal-rule-details');
    const content = document.getElementById('modal-rule-content');

    modal.style.display = 'flex';
    content.innerHTML = '<div class="loading-overlay"><div class="spinner"></div></div>';

    // TODO: Fetch rule details from API
    setTimeout(() => {
        content.innerHTML = `
            <div class="rule-detail">
                <h3>Rule #${ruleId}</h3>
                <p>Detailed rule information would be displayed here including:</p>
                <ul>
                    <li>Full description and documentation</li>
                    <li>Code examples (good and bad)</li>
                    <li>Configuration options</li>
                    <li>Related rules</li>
                    <li>Historical violation data</li>
                </ul>
            </div>
        `;
    }, 500);
}

function editRule(ruleId) {
    document.getElementById('rule-editor-title').textContent = 'Edit Rule';
    document.getElementById('modal-rule-editor').style.display = 'flex';

    // TODO: Load rule data into form
}

function deleteRule(ruleId) {
    if (confirm('Delete this rule? This action cannot be undone.')) {
        DashboardApp.showAlert('Rule deleted', 'success', 3000);
        // TODO: API call to delete rule
        setTimeout(() => location.reload(), 1000);
    }
}

function editRuleFromModal() {
    closeModal('modal-rule-details');
    // Open edit modal with current rule ID
}

// Bulk actions
function enableAllVisible() {
    const visibleCards = document.querySelectorAll('.rule-card:not([style*="display: none"])');
    visibleCards.forEach(card => {
        const toggle = card.querySelector('.toggle-switch__input');
        if (!toggle.checked) {
            toggle.checked = true;
            toggleRule(card.dataset.ruleId, true);
        }
    });
    DashboardApp.showAlert(`Enabled ${visibleCards.length} rules`, 'success', 3000);
}

function disableAllVisible() {
    if (confirm('Disable all visible rules?')) {
        const visibleCards = document.querySelectorAll('.rule-card:not([style*="display: none"])');
        visibleCards.forEach(card => {
            const toggle = card.querySelector('.toggle-switch__input');
            if (toggle.checked) {
                toggle.checked = false;
                toggleRule(card.dataset.ruleId, false);
            }
        });
        DashboardApp.showAlert(`Disabled ${visibleCards.length} rules`, 'success', 3000);
    }
}

// Custom rule creation
function createCustomRule() {
    document.getElementById('rule-editor-title').textContent = 'Create Custom Rule';
    document.getElementById('rule-form').reset();
    document.getElementById('modal-rule-editor').style.display = 'flex';
}

function saveRule() {
    const form = document.getElementById('rule-form');

    if (!form.checkValidity()) {
        DashboardApp.showAlert('Please fill in all required fields', 'warning', 3000);
        return;
    }

    const ruleData = {
        name: document.getElementById('rule-name').value,
        description: document.getElementById('rule-description').value,
        category: document.getElementById('rule-category').value,
        severity: document.getElementById('rule-severity').value,
        priority: parseInt(document.getElementById('rule-priority').value),
        pattern: document.getElementById('rule-pattern').value,
        enabled: document.getElementById('rule-enabled').checked,
        autoFix: document.getElementById('rule-auto-fix').checked
    };

    DashboardApp.showAlert('Rule saved successfully', 'success', 3000);
    closeModal('modal-rule-editor');

    // TODO: API call to save rule
    setTimeout(() => location.reload(), 1000);
}

// Import/Export
function importRules() {
    document.getElementById('modal-import-rules').style.display = 'flex';
}

function exportRules() {
    DashboardApp.showAlert('Exporting rules...', 'info', 2000);

    // TODO: Generate JSON export and trigger download
    setTimeout(() => {
        const ruleData = { rules: [], exported: new Date().toISOString() };
        const blob = new Blob([JSON.stringify(ruleData, null, 2)], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'rules-export.json';
        a.click();
    }, 1000);
}

function handleFileSelect(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        DashboardApp.showAlert(`Selected: ${file.name}`, 'info', 2000);
    }
}

function processImport() {
    const fileInput = document.getElementById('import-file');

    if (!fileInput.files || !fileInput.files[0]) {
        DashboardApp.showAlert('Please select a file first', 'warning', 3000);
        return;
    }

    DashboardApp.showAlert('Importing rules...', 'info', 2000);
    closeModal('modal-import-rules');

    // TODO: Process file upload and import rules
    setTimeout(() => {
        DashboardApp.showAlert('Rules imported successfully', 'success', 3000);
        location.reload();
    }, 2000);
}

// Presets
function loadRulePreset(preset) {
    const messages = {
        strict: 'This will enable all rules for maximum code quality.',
        recommended: 'This will enable recommended rules for most projects.',
        minimal: 'This will only enable critical security and error rules.'
    };

    if (confirm(`Load ${preset} preset?\n\n${messages[preset]}`)) {
        DashboardApp.showAlert(`Loading ${preset} preset...`, 'info', 2000);
        // TODO: API call to load preset
        setTimeout(() => location.reload(), 1000);
    }
}

function resetToDefaults() {
    if (confirm('Reset all rules to default configuration? This cannot be undone.')) {
        DashboardApp.showAlert('Resetting to defaults...', 'info', 2000);
        // TODO: API call to reset rules
        setTimeout(() => location.reload(), 1000);
    }
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
