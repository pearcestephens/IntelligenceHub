<?php
/**
 * Bot Standards Manager (LEGACY)
 *
 * ⚠️ NOTICE: This page has been integrated into the AI Control Center
 * Please use: ?page=ai-control-center&tab=standards
 *
 * All bot standards functionality is now available in the unified
 * AI Control Center with additional features and better integration.
 */

// Optional: Auto-redirect to new page (uncomment to enable)
// header('Location: ?page=ai-control-center#standards-tab');
// exit;

// Check authentication
session_start();
if (!isset($_SESSION['user_id'])) {
    // Redirect to AI Control Center instead of login
    header('Location: ?page=ai-control-center');
    exit;
}

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../../services/BotPromptBuilder.php';

$pageTitle = 'Bot Standards Manager';

// Initialize builder
$builder = new BotPromptBuilder();

// Get all available standards and templates
$allStandards = $builder->listStandards();
$allTemplates = $builder->listTemplates();

// Load user preferences (if saved)
$userPrefsFile = __DIR__ . '/../../private_html/cache/bot_standards_' . $_SESSION['user_id'] . '.json';
$userPrefs = [];
if (file_exists($userPrefsFile)) {
    $userPrefs = json_decode(file_get_contents($userPrefsFile), true) ?? [];
}

// Get enabled standards from prefs or use defaults
$enabledStandards = $userPrefs['enabled_standards'] ?? array_keys($allStandards);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - CIS Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        .rule-card {
            transition: all 0.3s ease;
            border-left: 4px solid #dee2e6;
        }
        .rule-card.enabled {
            border-left-color: #0d6efd;
            background-color: #f8f9fa;
        }
        .rule-card.critical.enabled {
            border-left-color: #dc3545;
        }
        .rule-card.high.enabled {
            border-left-color: #fd7e14;
        }
        .rule-card.medium.enabled {
            border-left-color: #ffc107;
        }
        .rule-item {
            padding: 0.5rem 0;
            border-bottom: 1px solid #dee2e6;
        }
        .rule-item:last-child {
            border-bottom: none;
        }
        .standard-toggle {
            transform: scale(1.3);
        }
        .prompt-output {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 1.5rem;
            border-radius: 0.5rem;
            font-family: 'Courier New', monospace;
            max-height: 600px;
            overflow-y: auto;
        }
        .template-card {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .template-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .template-card.selected {
            border: 2px solid #0d6efd;
            background-color: #e7f1ff;
        }
        .quick-actions {
            position: sticky;
            top: 20px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Header -->
            <div class="col-12 mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><i class="fas fa-robot"></i> Bot Standards Manager</h2>
                        <p class="text-muted mb-0">Enable/disable rules and generate custom bot prompts</p>
                    </div>
                    <div>
                        <button class="btn btn-outline-secondary" onclick="resetDefaults()">
                            <i class="fas fa-undo"></i> Reset to Defaults
                        </button>
                        <button class="btn btn-primary" onclick="savePreferences()">
                            <i class="fas fa-save"></i> Save Preferences
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column - Standards Checklist -->
            <div class="col-lg-8">
                <!-- Summary Stats -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="mb-0 text-primary" id="enabledCount"><?php echo count($enabledStandards); ?></h3>
                                <small class="text-muted">Standards Enabled</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="mb-0 text-danger" id="criticalCount">0</h3>
                                <small class="text-muted">Critical Rules</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="mb-0 text-warning" id="highCount">0</h3>
                                <small class="text-muted">High Priority</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h3 class="mb-0 text-info" id="mediumCount">0</h3>
                                <small class="text-muted">Medium Priority</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Filters -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-filter"></i> Quick Filters</h5>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-sm btn-outline-primary" onclick="enableAll()">
                            <i class="fas fa-check-double"></i> Enable All
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="disableAll()">
                            <i class="fas fa-times"></i> Disable All
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="filterByPriority('CRITICAL')">
                            <i class="fas fa-exclamation-circle"></i> Critical Only
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="filterByPriority('HIGH')">
                            <i class="fas fa-exclamation-triangle"></i> High Priority
                        </button>
                        <button class="btn btn-sm btn-outline-info" onclick="filterByPriority('MEDIUM')">
                            <i class="fas fa-info-circle"></i> Medium Priority
                        </button>
                    </div>
                </div>

                <!-- Standards Cards -->
                <div id="standardsContainer">
                    <?php foreach ($allStandards as $key => $standard):
                        $isEnabled = in_array($key, $enabledStandards);
                        $priorityClass = strtolower($standard['priority']);
                        $details = $builder->getStandard($key);
                    ?>
                    <div class="card rule-card mb-3 <?php echo $isEnabled ? 'enabled ' . $priorityClass : ''; ?>"
                         data-standard="<?php echo $key; ?>"
                         data-priority="<?php echo $standard['priority']; ?>">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="form-check form-switch me-3">
                                    <input class="form-check-input standard-toggle"
                                           type="checkbox"
                                           id="toggle_<?php echo $key; ?>"
                                           data-standard="<?php echo $key; ?>"
                                           <?php echo $isEnabled ? 'checked' : ''; ?>
                                           onchange="toggleStandard('<?php echo $key; ?>')">
                                </div>
                                <div>
                                    <h5 class="mb-0">
                                        <?php
                                        $icon = $standard['priority'] === 'CRITICAL' ? '🔴' :
                                                ($standard['priority'] === 'HIGH' ? '🟠' : '🟡');
                                        echo $icon . ' ' . $standard['name'];
                                        ?>
                                    </h5>
                                    <small class="text-muted">
                                        <?php echo $standard['rule_count']; ?> rules •
                                        <span class="badge bg-<?php
                                            echo $standard['priority'] === 'CRITICAL' ? 'danger' :
                                                 ($standard['priority'] === 'HIGH' ? 'warning' : 'info');
                                        ?>"><?php echo $standard['priority']; ?></span>
                                    </small>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-link"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#rules_<?php echo $key; ?>">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                        <div class="collapse" id="rules_<?php echo $key; ?>">
                            <div class="card-body">
                                <?php if (!empty($details['rules'])): ?>
                                    <?php foreach ($details['rules'] as $i => $rule): ?>
                                        <div class="rule-item">
                                            <span class="badge bg-secondary me-2"><?php echo $i + 1; ?></span>
                                            <?php echo htmlspecialchars(is_array($rule) ? $rule['text'] : $rule); ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right Column - Prompt Generator -->
            <div class="col-lg-4">
                <div class="quick-actions">
                    <!-- Bot Template Selector -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-magic"></i> Generate Bot Prompt</h5>
                        </div>
                        <div class="card-body">
                            <label class="form-label fw-bold">Select Bot Template:</label>
                            <div class="row g-2 mb-3">
                                <?php foreach ($allTemplates as $key => $template): ?>
                                <div class="col-6">
                                    <div class="card template-card"
                                         data-template="<?php echo $key; ?>"
                                         onclick="selectTemplate('<?php echo $key; ?>')">
                                        <div class="card-body p-2 text-center">
                                            <i class="fas fa-robot mb-2"></i>
                                            <div class="small fw-bold"><?php echo $template['name']; ?></div>
                                            <div class="text-muted" style="font-size: 0.75rem;">
                                                <?php echo $template['capabilities']; ?> capabilities
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <label class="form-label fw-bold">Task Description (Optional):</label>
                            <textarea class="form-control mb-3"
                                      id="taskDescription"
                                      rows="3"
                                      placeholder="e.g., Build a new API endpoint for managing users with CRUD operations"></textarea>

                            <label class="form-label fw-bold">Additional Requirements:</label>
                            <input type="text"
                                   class="form-control mb-3"
                                   id="additionalRequirements"
                                   placeholder="e.g., Must support pagination, Add logging">

                            <button class="btn btn-primary w-100 mb-2" onclick="generatePrompt()">
                                <i class="fas fa-magic"></i> Generate Prompt
                            </button>
                            <button class="btn btn-outline-secondary w-100" onclick="copyPrompt()">
                                <i class="fas fa-copy"></i> Copy to Clipboard
                            </button>
                        </div>
                    </div>

                    <!-- Generated Prompt Preview -->
                    <div class="card">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fas fa-file-code"></i> Generated Prompt</h6>
                            <button class="btn btn-sm btn-outline-light" onclick="downloadPrompt()">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="prompt-output" id="promptOutput">
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-magic fa-3x mb-3"></i>
                                    <p>Select a template and generate your prompt</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Summary -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-chart-pie"></i> Current Selection</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Enabled Standards:</span>
                                <strong id="selectionCount"><?php echo count($enabledStandards); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Rules:</span>
                                <strong id="totalRulesCount">0</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Selected Template:</span>
                                <strong id="selectedTemplateName">None</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedTemplate = null;
        let enabledStandards = <?php echo json_encode($enabledStandards); ?>;

        // Update counts on load
        document.addEventListener('DOMContentLoaded', function() {
            updateCounts();
        });

        function toggleStandard(standardKey) {
            const card = document.querySelector(`.rule-card[data-standard="${standardKey}"]`);
            const checkbox = document.getElementById(`toggle_${standardKey}`);

            if (checkbox.checked) {
                if (!enabledStandards.includes(standardKey)) {
                    enabledStandards.push(standardKey);
                }
                card.classList.add('enabled');
            } else {
                enabledStandards = enabledStandards.filter(s => s !== standardKey);
                card.classList.remove('enabled');
            }

            updateCounts();
        }

        function updateCounts() {
            let critical = 0, high = 0, medium = 0, totalRules = 0;

            document.querySelectorAll('.rule-card.enabled').forEach(card => {
                const priority = card.dataset.priority;
                const ruleCount = parseInt(card.querySelector('.text-muted').textContent.match(/\d+/)[0]);
                totalRules += ruleCount;

                if (priority === 'CRITICAL') critical++;
                else if (priority === 'HIGH') high++;
                else if (priority === 'MEDIUM') medium++;
            });

            document.getElementById('enabledCount').textContent = enabledStandards.length;
            document.getElementById('criticalCount').textContent = critical;
            document.getElementById('highCount').textContent = high;
            document.getElementById('mediumCount').textContent = medium;
            document.getElementById('selectionCount').textContent = enabledStandards.length;
            document.getElementById('totalRulesCount').textContent = totalRules;
        }

        function enableAll() {
            document.querySelectorAll('.standard-toggle').forEach(toggle => {
                if (!toggle.checked) {
                    toggle.checked = true;
                    toggleStandard(toggle.dataset.standard);
                }
            });
        }

        function disableAll() {
            document.querySelectorAll('.standard-toggle').forEach(toggle => {
                if (toggle.checked) {
                    toggle.checked = false;
                    toggleStandard(toggle.dataset.standard);
                }
            });
        }

        function filterByPriority(priority) {
            disableAll();
            document.querySelectorAll(`.rule-card[data-priority="${priority}"] .standard-toggle`).forEach(toggle => {
                toggle.checked = true;
                toggleStandard(toggle.dataset.standard);
            });
        }

        function selectTemplate(templateKey) {
            selectedTemplate = templateKey;

            document.querySelectorAll('.template-card').forEach(card => {
                card.classList.remove('selected');
            });

            document.querySelector(`.template-card[data-template="${templateKey}"]`).classList.add('selected');

            const templateName = document.querySelector(`.template-card[data-template="${templateKey}"] .fw-bold`).textContent;
            document.getElementById('selectedTemplateName').textContent = templateName;
        }

        function generatePrompt() {
            if (!selectedTemplate) {
                alert('Please select a bot template first');
                return;
            }

            const taskDesc = document.getElementById('taskDescription').value;
            const additionalReqs = document.getElementById('additionalRequirements').value;

            const formData = new FormData();
            formData.append('action', 'generate');
            formData.append('template', selectedTemplate);
            if (taskDesc) formData.append('task_description', taskDesc);
            if (additionalReqs) formData.append('task_requirements', additionalReqs);
            formData.append('additional_standards', enabledStandards.join(','));

            // Show loading
            document.getElementById('promptOutput').innerHTML = `
                <div class="text-center text-muted py-5">
                    <div class="spinner-border mb-3" role="status"></div>
                    <p>Generating your custom bot prompt...</p>
                </div>
            `;

            fetch('/api/bot-prompt.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayPrompt(data.data.prompt);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to generate prompt');
            });
        }

        function displayPrompt(prompt) {
            const output = document.getElementById('promptOutput');
            output.innerHTML = '<pre class="mb-0" style="color: #f8f8f2; white-space: pre-wrap;">' +
                               escapeHtml(prompt) +
                               '</pre>';
        }

        function copyPrompt() {
            const output = document.getElementById('promptOutput').textContent;
            if (!output || output.includes('Select a template')) {
                alert('No prompt to copy. Generate one first!');
                return;
            }

            navigator.clipboard.writeText(output).then(() => {
                alert('✅ Prompt copied to clipboard!');
            });
        }

        function downloadPrompt() {
            const output = document.getElementById('promptOutput').textContent;
            if (!output || output.includes('Select a template')) {
                alert('No prompt to download. Generate one first!');
                return;
            }

            const blob = new Blob([output], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `bot_prompt_${selectedTemplate}_${Date.now()}.txt`;
            a.click();
        }

        function savePreferences() {
            fetch('/api/bot-prompt.php?action=save_preferences', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    enabled_standards: enabledStandards
                })
            })
            .then(response => response.json())
            .then(data => {
                alert('✅ Preferences saved!');
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to save preferences');
            });
        }

        function resetDefaults() {
            if (confirm('Reset to default standards? This will enable all standards.')) {
                location.reload();
            }
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }
    </script>
</body>
</html>
