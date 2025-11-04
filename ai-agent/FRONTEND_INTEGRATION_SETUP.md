# 🚀 Frontend Integration Setup Guide

**Complete setup guide for integrating frontend automation tools with AI Agent**

---

## ✅ What We Built

1. **Database Schema** - All tables for approvals, workflows, execution history
2. **Tool Registry** - Frontend tools registered with ToolChainOrchestrator
3. **Approval UI** - Review and approve fixes before applying
4. **Workflow Builder** - Visual drag-drop workflow designer
5. **Execution Engine** - Run workflows via your existing orchestrator
6. **APIs** - Backend endpoints for all operations

---

## 📋 Installation Steps

### Step 1: Run Database Migrations (2 minutes)

```bash
# Navigate to AI Agent directory
cd /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/ai-agent

# Run migration
mysql -u [USERNAME] -p[PASSWORD] [DATABASE] < migrations/frontend_integration_schema.sql
```

Expected output:
```
+---------------------------------------------+---------------------------+
| status                                      | sample_workflows_created  |
+---------------------------------------------+---------------------------+
| Frontend Integration Schema Created Success | 3                         |
+---------------------------------------------+---------------------------+
```

### Step 2: Verify Tool Registration (1 minute)

Create test file to verify tools are registered:

```bash
cd /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/ai-agent

# Create test script
cat > test-tools.php << 'EOF'
<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Tools/Frontend/FrontendToolRegistry.php';

use App\Tools\Frontend\FrontendToolRegistry;
use App\Logger;

$logger = new Logger('test');
$registry = new FrontendToolRegistry($logger);
$tools = $registry->getTools();

echo "✅ Frontend tools registered:\n\n";
foreach ($tools as $name => $tool) {
    echo "  - $name: " . $tool->getDescription() . "\n";
}
echo "\nTotal: " . count($tools) . " tools\n";
EOF

# Run test
php test-tools.php
```

Expected output:
```
✅ Frontend tools registered:

  - frontend_audit_page: Audit any webpage for errors, performance, accessibility...
  - frontend_auto_fix: Automatically fix detected issues using AI...
  - frontend_screenshot: Capture screenshots of webpages...
  - frontend_monitor_start: Start continuous monitoring...
  - frontend_visual_regression: Compare screenshots to detect visual regressions
  - frontend_performance_audit: Run Lighthouse performance audit
  - frontend_accessibility_check: Check WCAG accessibility compliance

Total: 7 tools
```

### Step 3: Update Workflow Builder Page (2 minutes)

Edit `/ai-agent/public/dashboard/workflows.php`:

```php
<?php
$currentPage = 'workflows';
$pageTitle = 'Workflow Builder - AI Agent Dashboard';
require_once __DIR__ . '/templates/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3">
                <i class="bi bi-diagram-3"></i> Visual Workflow Builder
            </h1>
            <p class="text-muted">Create automation workflows with drag-drop</p>
        </div>
    </div>

    <!-- Workflow Builder Container -->
    <div id="workflow-builder"></div>
</div>

<!-- Include workflow builder JS -->
<script src="/ai-agent/public/dashboard/assets/js/workflow-builder.js"></script>

<?php require_once __DIR__ . '/templates/footer.php'; ?>
```

### Step 4: Test Approval UI (1 minute)

Visit: `https://gpt.ecigdis.co.nz/ai-agent/public/dashboard/approvals.php`

You should see:
- Statistics cards (0 pending, 0 approved, etc.)
- "All clear! No pending fixes to review"
- Clean interface ready to receive fixes

### Step 5: Test Workflow Builder (2 minutes)

Visit: `https://gpt.ecigdis.co.nz/ai-agent/public/dashboard/workflows.php`

You should see:
- Toolbar with node buttons (Audit, Fix, Screenshot, etc.)
- Empty canvas with grid background
- Save/Load/Clear/Run buttons

**Test it:**
1. Click "Audit Page" button → Node appears
2. Click "Screenshot" button → Second node appears
3. Drag nodes around → Should move freely
4. Edit node config → Type in input fields
5. Click "Save" → Prompts for name
6. Click "Run Workflow" → Shows confirmation

---

## 🎯 Usage Examples

### Example 1: Quick Page Audit

**Workflow:**
```
[Audit Page] → [Screenshot] → [Upload to Gallery]
```

**Steps:**
1. Click "Audit Page" - Configure URL: `https://staff.vapeshed.co.nz`
2. Click "Screenshot" - Keep default (full_page)
3. Click "Run Workflow"
4. Wait 10-30 seconds
5. View results in alert popup

**Expected Result:**
```
✅ Workflow complete!

Duration: 15234ms
Steps completed: 2/2
Steps failed: 0

View full results: [dashboard link]
```

### Example 2: Auto-Fix Pipeline

**Workflow:**
```
[Audit Page] → [Condition: errors > 0] → [Auto-Fix] → [Screenshot]
```

**Steps:**
1. Click "Audit Page" - URL: your site
2. Click "Condition" - Set `errors.total > 0`
3. Click "Auto-Fix" - Enable `approval_required`
4. Click "Screenshot"
5. Connect nodes (future feature - manual for now)
6. Click "Run Workflow"

**Expected Result:**
- Audit runs
- IF errors found → Creates pending fixes
- Go to Approvals page
- Review fixes
- Click "Approve & Apply"
- Screenshot taken
- Complete!

### Example 3: 24/7 Monitoring

**Workflow:**
```
[Monitor] (repeats every 5 minutes)
```

**Steps:**
1. Click "Monitor"
2. Configure:
   - URL: your production site
   - Interval: `5m`
   - Checks: `errors, performance, uptime`
   - Alert channels: `email, slack`
3. Click "Run Workflow"
4. Monitor runs in background
5. Alerts sent on issues

---

## 🔧 Configuration

### Database Connection

Edit `/ai-agent/config/database.php`:

```php
<?php
$db_host = '127.0.0.1';
$db_name = 'your_database';
$db_user = 'your_user';
$db_pass = 'your_password';

$db = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($db->connect_error) {
    die('Database connection failed: ' . $db->connect_error);
}

$db->set_charset('utf8mb4');
```

### Frontend Tools Path

If your frontend tools are in a different location, edit:

`/ai-agent/src/Tools/Frontend/FrontendToolRegistry.php` (line 26):

```php
$this->frontendToolsPath = '/your/custom/path/to/frontend-tools';
```

### Upload Directory

Default: `/private_html/uploads/frontend`

To change, edit `FrontendToolRegistry.php` (line 27):

```php
$this->uploadPath = '/your/custom/upload/path';
```

---

## 🎮 API Endpoints

### 1. Execute Workflow
```
POST /ai-agent/api/execute-workflow.php
Content-Type: application/json

{
  "workflow_id": 1,
  "nodes": [...],
  "connections": [...]
}

Response:
{
  "success": true,
  "execution_id": "exec_1730736000_abc123",
  "duration_ms": 15234,
  "steps_completed": 3,
  "steps_failed": 0,
  "results": [...],
  "dashboard_url": "..."
}
```

### 2. Approve Fix
```
POST /ai-agent/api/approve-fix.php
Content-Type: application/json

{
  "fix_id": 123,
  "action": "approve"
}

Response:
{
  "success": true,
  "message": "Fix applied successfully",
  "backup_path": "/backups/file.backup"
}
```

### 3. Save Workflow
```
POST /ai-agent/api/save-workflow.php
Content-Type: application/json

{
  "name": "My Workflow",
  "description": "...",
  "workflow_json": {
    "nodes": [...],
    "connections": [...]
  }
}

Response:
{
  "success": true,
  "workflow_id": 42
}
```

---

## 🧪 Testing Checklist

### ✅ Database
- [ ] All 9 tables created
- [ ] Sample workflows inserted
- [ ] Can query `frontend_pending_fixes`
- [ ] Can query `frontend_workflows`

### ✅ Tool Registry
- [ ] 7 tools registered
- [ ] Each tool has name, description, parameters
- [ ] Tools can be instantiated
- [ ] No PHP errors

### ✅ Approval UI
- [ ] Page loads at `/dashboard/approvals.php`
- [ ] Statistics cards show counts
- [ ] No pending fixes initially
- [ ] "All clear" message visible

### ✅ Workflow Builder
- [ ] Page loads at `/dashboard/workflows.php`
- [ ] Toolbar buttons work
- [ ] Can add nodes
- [ ] Can drag nodes
- [ ] Can edit node config
- [ ] Save/Run buttons respond

### ✅ APIs
- [ ] `/api/execute-workflow.php` returns JSON
- [ ] `/api/approve-fix.php` handles approval
- [ ] Errors return proper JSON error messages

---

## 🐛 Troubleshooting

### Database Connection Errors

**Error:** `SQLSTATE[HY000] [2002] Connection refused`

**Fix:**
```bash
# Check MySQL is running
systemctl status mysql

# Test connection
mysql -u your_user -p

# Verify credentials in config/database.php
```

### Tool Not Found Errors

**Error:** `Tool 'frontend_audit_page' not found`

**Fix:**
```php
// In ToolExecutor, ensure tools are registered:
$toolRegistry = new FrontendToolRegistry($logger);
$tools = $toolRegistry->getTools();

foreach ($tools as $name => $tool) {
    $executor->registerTool($name, $tool);
}
```

### Permission Errors

**Error:** `Failed to create backup directory`

**Fix:**
```bash
# Create directories with correct permissions
mkdir -p /home/129337.cloudwaysapps.com/hdgwrzntwa/private_html/backups/frontend-fixes
chmod 755 /home/129337.cloudwaysapps.com/hdgwrzntwa/private_html/backups/frontend-fixes

mkdir -p /home/129337.cloudwaysapps.com/hdgwrzntwa/private_html/uploads/frontend
chmod 755 /home/129337.cloudwaysapps.com/hdgwrzntwa/private_html/uploads/frontend
```

### Node.js Execution Errors

**Error:** `timeout: command not found`

**Fix:**
```bash
# Install timeout utility (usually included in coreutils)
which timeout

# Or modify tool to use different timeout method
node --max-old-space-size=512 script.js
```

---

## 📊 Monitoring & Logs

### View Execution History

```sql
-- Recent workflow executions
SELECT
    e.execution_id,
    w.name as workflow_name,
    e.status,
    e.steps_completed,
    e.steps_failed,
    e.duration_ms,
    e.started_at
FROM frontend_workflow_executions e
LEFT JOIN frontend_workflows w ON w.id = e.workflow_id
ORDER BY e.started_at DESC
LIMIT 10;
```

### View Pending Fixes

```sql
-- All pending fixes
SELECT
    id,
    url,
    file_path,
    line_number,
    fix_type,
    reason,
    created_at
FROM frontend_pending_fixes
WHERE status = 'pending'
ORDER BY created_at DESC;
```

### View Deployment Log

```sql
-- Recent deployments
SELECT
    deployment_id,
    file_path,
    action,
    deployed_by,
    deployed_at,
    rollback_available
FROM frontend_deployment_log
ORDER BY deployed_at DESC
LIMIT 20;
```

---

## 🚀 Next Steps

1. **Create Your First Workflow**
   - Go to workflows.php
   - Add 2-3 nodes
   - Configure each node
   - Save and run

2. **Test Approval Flow**
   - Run audit that finds errors
   - Go to approvals.php
   - Review and approve a fix
   - Verify file was updated

3. **Set Up Monitoring**
   - Create monitoring workflow
   - Configure alert channels
   - Test alerts

4. **Integrate with AIOrchestrator**
   - Add frontend tools to AIOrchestrator's tool list
   - Enable semantic search for frontend queries
   - Test conversational workflow creation

---

## 📝 Advanced Configuration

### Custom Tool Development

Create new tool by implementing `ToolInterface`:

```php
class MyCustomTool implements ToolInterface
{
    public function getName(): string {
        return 'my_custom_tool';
    }

    public function getDescription(): string {
        return 'My custom automation tool';
    }

    public function getParameters(): array {
        return [
            'param1' => [
                'type' => 'string',
                'required' => true,
                'description' => 'First parameter'
            ]
        ];
    }

    public function execute(array $params): array {
        // Your logic here
        return [
            'success' => true,
            'result' => ['data' => 'result']
        ];
    }
}
```

Register in `FrontendToolRegistry.php`:

```php
public function getTools(): array
{
    return [
        // ... existing tools
        'my_custom_tool' => new MyCustomTool($this->logger, $this->frontendToolsPath),
    ];
}
```

### Conditional Branching

Implement in workflow:

```php
$chain->addConditional(
    'node_2',  // After this step
    function($result) {
        return $result['errors']['total'] > 0;  // Condition
    },
    $trueBranch,   // Chain if true
    $falseBranch   // Chain if false (optional)
);
```

### Parallel Execution

```php
$chain = $orchestrator->createChain('parallel_workflow', [
    'parallel' => true,  // Enable parallel execution
    'max_workers' => 4   // Max concurrent workers
]);
```

---

## ✅ Success Criteria

**You're fully operational when:**

✅ All database tables exist and populated
✅ 7 frontend tools registered and working
✅ Approval UI accessible and functional
✅ Workflow builder loads and creates nodes
✅ Can execute workflow end-to-end
✅ Approvals flow works (approve → apply → verify)
✅ No PHP errors in logs
✅ No JavaScript console errors

---

## 🎉 You're Done!

You now have:
- ✅ Frontend automation integrated with AI Agent
- ✅ Visual workflow builder
- ✅ Approval system for all code changes
- ✅ No GitHub dependency
- ✅ Full control and audit trail
- ✅ Autonomous AI agents with human oversight

**Start automating! 🚀**
