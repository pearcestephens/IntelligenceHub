<?php
/**
 * Dynamic CIS Standards & Prompt Manager
 * Self-updating system that learns from changes and maintains live standards
 */

declare(strict_types=1);

class DynamicCISStandardsManager 
{
    private const STANDARDS_DB = '_automation/live-standards.json';
    private const CHANGES_LOG = '_automation/logs/system-changes.log';
    private const ANNOUNCEMENTS = '_automation/announcements.json';
    
    private array $currentStandards = [];
    private array $detectedChanges = [];
    
    public function __construct() 
    {
        $this->loadCurrentStandards();
        $this->detectSystemChanges();
    }
    
    /**
     * Scan entire CIS system for architectural changes
     */
    public function scanForChanges(): array 
    {
        $changes = [
            'new_modules' => $this->detectNewModules(),
            'new_patterns' => $this->detectNewPatterns(),
            'changed_standards' => $this->detectChangedStandards(),
            'new_apis' => $this->detectNewApis(),
            'deprecated_features' => $this->detectDeprecatedFeatures(),
            'updated_dependencies' => $this->detectDependencyChanges()
        ];
        
        $this->processChanges($changes);
        $this->updatePrompts($changes);
        $this->createAnnouncements($changes);
        
        return $changes;
    }
    
    /**
     * Detect new modules following base/shared MVC pattern
     */
    private function detectNewModules(): array 
    {
        $newModules = [];
        $modulesDirs = glob('modules/*', GLOB_ONLYDIR);
        
        foreach ($modulesDirs as $moduleDir) {
            $moduleName = basename($moduleDir);
            
            if (!isset($this->currentStandards['modules'][$moduleName])) {
                $moduleStructure = $this->analyzeModuleStructure($moduleDir);
                
                if ($this->validateMVCPattern($moduleStructure)) {
                    $newModules[$moduleName] = [
                        'path' => $moduleDir,
                        'structure' => $moduleStructure,
                        'follows_mvc' => true,
                        'detected_at' => date('Y-m-d H:i:s'),
                        'status' => 'pending_approval'
                    ];
                }
            }
        }
        
        return $newModules;
    }
    
    /**
     * Analyze module structure to ensure MVC compliance
     */
    private function analyzeModuleStructure(string $moduleDir): array 
    {
        return [
            'has_controllers' => is_dir($moduleDir . '/controllers'),
            'has_models' => is_dir($moduleDir . '/models'),
            'has_views' => is_dir($moduleDir . '/views'),
            'has_lib' => is_dir($moduleDir . '/lib'),
            'has_api' => is_dir($moduleDir . '/api'),
            'uses_base_classes' => $this->checkBaseClassUsage($moduleDir),
            'follows_naming' => $this->checkNamingConventions($moduleDir),
            'has_documentation' => file_exists($moduleDir . '/README.md')
        ];
    }
    
    /**
     * Check if module uses base/shared classes correctly
     */
    private function checkBaseClassUsage(string $moduleDir): bool 
    {
        $phpFiles = glob($moduleDir . '/**/*.php', GLOB_BRACE);
        $usesBase = false;
        
        foreach ($phpFiles as $file) {
            $content = file_get_contents($file);
            if (strpos($content, 'require_once $_SERVER[\'DOCUMENT_ROOT\'] . \'/app.php\'') !== false) {
                $usesBase = true;
                break;
            }
        }
        
        return $usesBase;
    }
    
    /**
     * Detect new coding patterns being used
     */
    private function detectNewPatterns(): array 
    {
        $patterns = [];
        $recentFiles = $this->getRecentlyModifiedFiles();
        
        foreach ($recentFiles as $file) {
            $detectedPatterns = $this->analyzeFilePatterns($file);
            $patterns = array_merge($patterns, $detectedPatterns);
        }
        
        return array_unique($patterns);
    }
    
    /**
     * Create live prompts based on current system state
     */
    public function generateLivePrompts(): array 
    {
        $modulesList = implode(', ', array_keys($this->currentStandards['modules'] ?? []));
        $activePatterns = implode(', ', $this->currentStandards['patterns'] ?? []);
        
        return [
            'daily_start' => $this->generateDailyStartPrompt(),
            'new_module' => $this->generateNewModulePrompt(),
            'api_development' => $this->generateApiPrompt(),
            'debugging' => $this->generateDebuggingPrompt(),
            'security_audit' => $this->generateSecurityPrompt()
        ];
    }
    
    /**
     * Generate dynamic daily start prompt with current system state
     */
    private function generateDailyStartPrompt(): string 
    {
        $activeModules = array_keys($this->currentStandards['modules'] ?? []);
        $recentChanges = $this->getRecentChanges();
        $pendingItems = $this->getPendingApprovals();
        
        $prompt = "@workspace Review the current CIS system state:\n\n";
        $prompt .= "**Active Modules:** " . implode(', ', $activeModules) . "\n";
        
        if (!empty($recentChanges)) {
            $prompt .= "**Recent Changes:** " . implode(', ', $recentChanges) . "\n";
        }
        
        if (!empty($pendingItems)) {
            $prompt .= "**Pending Approvals:** " . count($pendingItems) . " items need review\n";
        }
        
        $prompt .= "\nShow me:\n";
        $prompt .= "1. Any critical issues\n";
        $prompt .= "2. Modules needing attention\n";
        $prompt .= "3. Today's priorities\n";
        $prompt .= "4. System health status";
        
        return $prompt;
    }
    
    /**
     * Generate dynamic new module prompt with current standards
     */
    private function generateNewModulePrompt(): string 
    {
        $baseStructure = $this->getBaseModuleStructure();
        
        $prompt = "@workspace Create a new module following CIS MVC standards:\n\n";
        $prompt .= "**Required Structure:**\n";
        $prompt .= "- controllers/ (HTTP request handlers)\n";
        $prompt .= "- models/ (Data access layer)\n";
        $prompt .= "- views/ (UI templates)\n";
        $prompt .= "- lib/ (Module utilities)\n";
        $prompt .= "- api/ (JSON endpoints)\n";
        $prompt .= "- README.md (Documentation)\n\n";
        $prompt .= "**Must Include:**\n";
        $prompt .= "- require_once \$_SERVER['DOCUMENT_ROOT'] . '/app.php';\n";
        $prompt .= "- Follow PSR-12 coding standards\n";
        $prompt .= "- Use prepared statements for all SQL\n";
        $prompt .= "- Include CSRF protection on forms\n\n";
        $prompt .= "**Reference Existing Modules:**\n";
        $prompt .= "#file:modules/base/ (Base patterns)\n";
        
        foreach (array_keys($this->currentStandards['modules'] ?? []) as $module) {
            $prompt .= "#file:modules/{$module}/ (Example)\n";
        }
        
        return $prompt;
    }
    
    /**
     * User approval system for detected changes
     */
    public function submitForApproval(string $changeType, string $item, array $details): void 
    {
        $approval = [
            'id' => uniqid(),
            'type' => $changeType,
            'item' => $item,
            'details' => $details,
            'submitted_at' => date('Y-m-d H:i:s'),
            'status' => 'pending'
        ];
        
        $this->addPendingApproval($approval);
        $this->createAnnouncement("New {$changeType} detected: {$item} - requires approval");
    }
    
    /**
     * Process user decisions (true/false/delete)
     */
    public function processUserDecision(string $approvalId, string $decision): array 
    {
        $approval = $this->getPendingApproval($approvalId);
        
        if (!$approval) {
            return ['success' => false, 'error' => 'Approval not found'];
        }
        
        switch ($decision) {
            case 'true':
            case 'approve':
                $this->approveChange($approval);
                $this->updateStandards($approval);
                $this->createAnnouncement("✅ Approved: {$approval['item']} is now part of CIS standards");
                break;
                
            case 'false':
            case 'reject':
                $this->rejectChange($approval);
                $this->createAnnouncement("❌ Rejected: {$approval['item']} will not be added to standards");
                break;
                
            case 'delete':
                $this->deleteChange($approval);
                $this->createAnnouncement("🗑️ Deleted: {$approval['item']} removed from consideration");
                break;
        }
        
        $this->removePendingApproval($approvalId);
        $this->regeneratePrompts();
        
        return ['success' => true, 'decision' => $decision];
    }
    
    /**
     * Create announcements for bots
     */
    private function createAnnouncement(string $message): void 
    {
        $announcements = $this->loadAnnouncements();
        
        $announcements[] = [
            'id' => uniqid(),
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s'),
            'delivered' => false,
            'priority' => 'normal'
        ];
        
        $this->saveAnnouncements($announcements);
    }
    
    /**
     * Get undelivered announcements for bots
     */
    public function getAnnouncementsForBots(): array 
    {
        $announcements = $this->loadAnnouncements();
        $undelivered = array_filter($announcements, fn($a) => !$a['delivered']);
        
        // Mark as delivered
        foreach ($announcements as &$announcement) {
            if (!$announcement['delivered']) {
                $announcement['delivered'] = true;
            }
        }
        
        $this->saveAnnouncements($announcements);
        
        return $undelivered;
    }
    
    /**
     * Dashboard interface for managing approvals
     */
    public function renderApprovalsDashboard(): string 
    {
        $pending = $this->getPendingApprovals();
        $recent = $this->getRecentDecisions();
        
        ob_start();
        ?>
        <div class="card">
            <div class="card-header">
                <h4>🤖 CIS Standards Management</h4>
                <small class="text-muted">Live system learning and approval</small>
            </div>
            <div class="card-body">
                
                <?php if (!empty($pending)): ?>
                <h5>📋 Pending Approvals (<?= count($pending) ?>)</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Item</th>
                                <th>Details</th>
                                <th>Detected</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending as $item): ?>
                            <tr>
                                <td><span class="badge badge-info"><?= $item['type'] ?></span></td>
                                <td><strong><?= $item['item'] ?></strong></td>
                                <td><small><?= json_encode($item['details']) ?></small></td>
                                <td><small><?= $item['submitted_at'] ?></small></td>
                                <td>
                                    <button class="btn btn-success btn-xs" onclick="processDecision('<?= $item['id'] ?>', 'approve')">
                                        ✅ Approve
                                    </button>
                                    <button class="btn btn-warning btn-xs" onclick="processDecision('<?= $item['id'] ?>', 'reject')">
                                        ❌ Reject
                                    </button>
                                    <button class="btn btn-danger btn-xs" onclick="processDecision('<?= $item['id'] ?>', 'delete')">
                                        🗑️ Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-success">
                    ✅ No pending approvals - system is up to date!
                </div>
                <?php endif; ?>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <h5>📊 System Status</h5>
                        <ul class="list-unstyled">
                            <li>📁 Active Modules: <?= count($this->currentStandards['modules'] ?? []) ?></li>
                            <li>📋 Patterns: <?= count($this->currentStandards['patterns'] ?? []) ?></li>
                            <li>🔧 APIs: <?= count($this->currentStandards['apis'] ?? []) ?></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5>🔄 Actions</h5>
                        <button class="btn btn-primary btn-sm" onclick="scanForChanges()">
                            🔍 Scan for Changes
                        </button>
                        <button class="btn btn-info btn-sm" onclick="regeneratePrompts()">
                            📝 Regenerate Prompts
                        </button>
                        <button class="btn btn-success btn-sm" onclick="exportStandards()">
                            📤 Export Standards
                        </button>
                    </div>
                </div>
                
            </div>
        </div>
        
        <script>
        function processDecision(approvalId, decision) {
            fetch('/api/automation/process-decision', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({approval_id: approvalId, decision: decision})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Decision processed: ' + decision);
                    location.reload();
                } else {
                    alert('❌ Error: ' + data.error);
                }
            });
        }
        
        function scanForChanges() {
            fetch('/api/automation/scan-changes')
                .then(response => response.json())
                .then(data => {
                    alert('🔍 Scan complete. Found ' + Object.keys(data).length + ' change types.');
                    location.reload();
                });
        }
        </script>
        <?php
        return ob_get_clean();
    }
    
    // Helper methods for data persistence
    private function loadCurrentStandards(): void { /* Implementation */ }
    private function saveCurrentStandards(): void { /* Implementation */ }
    private function loadAnnouncements(): array { /* Implementation */ }
    private function saveAnnouncements(array $announcements): void { /* Implementation */ }
    private function getPendingApprovals(): array { /* Implementation */ }
    private function addPendingApproval(array $approval): void { /* Implementation */ }
    private function removePendingApproval(string $id): void { /* Implementation */ }
}
?>

<style>
.badge { font-size: 0.7em; }
.btn-xs { padding: 2px 6px; font-size: 0.7em; }
</style>