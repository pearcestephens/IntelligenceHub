# Generic Sandbox - Documentation

## Overview

The **Generic Sandbox** is a fallback database space that provides isolated, restricted access when no specific project or business unit context is available.

---

## Purpose

- **Fallback Context**: When no project ID or unit ID is found
- **Testing Environment**: Safe space for bot experiments
- **Public Access**: Demonstrations and API testing
- **Unknown Contexts**: Handle unidentified requests gracefully

---

## Database Records

### IDs Reserved
- **Business Unit ID**: `999`
- **Project ID**: `999`
- **Config ID**: `999`

### Tables Affected
1. `business_units` - Generic Sandbox unit
2. `projects` - Generic Sandbox Project
3. `project_unit_mapping` - Links project to unit
4. `project_scan_config` - Restricted scan config
5. `hub_projects` - Intelligence Hub entry
6. `bot_projects` - Bot Deployment entry

---

## Access Restrictions

### Allowed Paths Only
```
/sandbox
/public_html/sandbox
```

### Excluded Paths
```
/sandbox/private
/sandbox/secrets
/sandbox/.env
/sandbox/.git
```

### File Restrictions
- **Max Depth**: 2 levels
- **Allowed Types**: `.txt`, `.md`, `.json`, `.log`, `.csv`
- **Max File Size**: 1MB
- **Max Files**: 100
- **Read Only**: No write/execute permissions

### Database Restrictions
- **Read Only**: No INSERT/UPDATE/DELETE operations
- **Limited Tables**: Only sandbox-specific queries
- **No Sensitive Data**: No customer/financial/security data

---

## Usage

### PHP - Check if Sandbox

```php
use BotDeployment\Helpers\SandboxHelper;

$projectId = $_SESSION['current_project_id'] ?? null;
$unitId = $_SESSION['current_unit_id'] ?? null;

if (SandboxHelper::isSandbox($projectId, $unitId)) {
    echo "Using generic sandbox - limited access";
}
```

### PHP - Get Fallback IDs

```php
// Automatically fallback to sandbox if null
$projectId = SandboxHelper::getProjectId($_GET['project_id'] ?? null);
$unitId = SandboxHelper::getUnitId($_GET['unit_id'] ?? null);

// $projectId will be 999 if no valid project provided
```

### PHP - Initialize Session

```php
$sessionData = SandboxHelper::initializeSandboxSession($projectId, $unitId);

$_SESSION['current_project_id'] = $sessionData['current_project_id'];
$_SESSION['current_unit_id'] = $sessionData['current_unit_id'];
$_SESSION['is_sandbox'] = $sessionData['is_sandbox'];
$_SESSION['sandbox_mode'] = $sessionData['sandbox_mode'];
```

### PHP - Validate File Access

```php
$path = '/var/www/some/file.txt';
$isSandbox = $_SESSION['is_sandbox'] ?? false;

if (!SandboxHelper::validatePath($path, $isSandbox)) {
    die('Access denied - path not allowed in sandbox mode');
}
```

### PHP - Get Restrictions

```php
$restrictions = SandboxHelper::getRestrictions();

echo "Allowed paths: " . implode(', ', $restrictions['allowed_paths']);
echo "Max file size: " . $restrictions['max_file_size'];
echo "Read only: " . ($restrictions['read_only'] ? 'Yes' : 'No');
```

### PHP - Log Access

```php
use BotDeployment\Database\Connection;

$pdo = Connection::get();

SandboxHelper::logAccess($pdo, 'file_read', [
    'file' => '/sandbox/test.txt',
    'user' => 'bot_123'
]);
```

### PHP - Get Stats

```php
$stats = SandboxHelper::getStats($pdo);

echo "Total accesses (30 days): " . $stats['total_accesses'];
echo "Unique IPs: " . $stats['unique_ips'];
echo "Last access: " . $stats['last_access'];
```

---

## SQL Queries

### Check if Sandbox Exists

```sql
SELECT * FROM business_units WHERE unit_id = 999;
SELECT * FROM projects WHERE id = 999;
SELECT * FROM project_unit_mapping WHERE project_id = 999 AND unit_id = 999;
```

### Get Sandbox Info

```sql
SELECT
    bu.unit_name,
    bu.unit_type,
    bu.environment,
    p.project_name,
    p.project_type,
    p.status
FROM business_units bu
JOIN project_unit_mapping pum ON bu.unit_id = pum.unit_id
JOIN projects p ON pum.project_id = p.id
WHERE bu.unit_id = 999 AND p.id = 999;
```

### Check Sandbox Usage

```sql
-- If sandbox_access_log table exists
SELECT
    COUNT(*) as total_accesses,
    COUNT(DISTINCT ip_address) as unique_users,
    MAX(created_at) as last_access
FROM sandbox_access_log
WHERE project_id = 999 AND unit_id = 999
AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY);
```

---

## Bot Deployment Integration

### Bot Configuration

When creating a bot without a specific project:

```php
$bot = new Bot();
$bot->setBotName("Test Bot")
    ->setBotRole("general")
    ->setConfig('project_id', SandboxHelper::SANDBOX_PROJECT_ID)
    ->setConfig('unit_id', SandboxHelper::SANDBOX_UNIT_ID)
    ->setConfig('sandbox_mode', true);
```

### Execution Context

```php
use BotDeployment\Services\BotExecutionService;
use BotDeployment\Helpers\SandboxHelper;

$botExecution = new BotExecutionService();

// Check context before execution
if (SandboxHelper::isSandbox($bot->getConfig('project_id'), null)) {
    // Apply sandbox restrictions
    $context = [
        'sandbox_mode' => true,
        'allowed_paths' => SandboxHelper::ALLOWED_PATHS,
        'restrictions' => SandboxHelper::getRestrictions()
    ];

    $result = $botExecution->execute($bot, $input, $context);
}
```

---

## CLI Tools Usage

### Health Check

```bash
# Will detect and report sandbox records
php health-check.php --verbose
```

### Bot Deploy

```bash
# Deploy to sandbox (no specific project)
php bot-deploy.php --name="Sandbox Test Bot" --status=paused
```

### Bot Execute

```bash
# Execute in sandbox context
php bot-execute.php 999 "Test in sandbox mode"
```

---

## API Usage

### REST API

```bash
# Create bot in sandbox
curl -X POST https://staff.vapeshed.co.nz/admin/bot-api.php/bots \
  -H "X-API-Key: YOUR_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "bot_name": "Sandbox Test",
    "bot_role": "general",
    "project_id": 999,
    "unit_id": 999
  }'
```

### Check Sandbox Status

```bash
curl -X GET https://staff.vapeshed.co.nz/admin/bot-api.php/status/sandbox \
  -H "X-API-Key: YOUR_KEY"
```

---

## Security Considerations

### What Sandbox CAN Access
✅ Files in `/sandbox` directory
✅ Read-only database queries to sandbox tables
✅ Public API endpoints
✅ Log files (own logs only)
✅ Temporary storage in `/sandbox/temp`

### What Sandbox CANNOT Access
❌ Production data
❌ Customer information
❌ Financial records
❌ User credentials
❌ Configuration files (`.env`)
❌ Source code outside sandbox
❌ Other projects' files
❌ Database write operations

### Isolation Guarantees
- **No Cross-Project Access**: Cannot read other projects' data
- **No Privileged Operations**: Cannot execute system commands
- **No Database Writes**: Read-only access to safe tables
- **No Network Access**: Cannot make external API calls (optional)
- **Rate Limited**: Max requests per minute enforced

---

## Monitoring

### Access Logs

All sandbox access is logged (if `sandbox_access_log` table exists):

```sql
CREATE TABLE IF NOT EXISTS sandbox_access_log (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    unit_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    details JSON,
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_project_unit (project_id, unit_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Alerts

Set up monitoring for:
- Excessive access attempts
- Unauthorized path access attempts
- Unusual activity patterns
- Error rate spikes

---

## Maintenance

### Update Sandbox

```sql
-- Update description
UPDATE business_units
SET description = 'New description'
WHERE unit_id = 999;

-- Update allowed paths
UPDATE project_scan_config
SET scan_scope = JSON_ARRAY('/sandbox', '/new/path')
WHERE project_id = 999;
```

### Reset Sandbox

```sql
-- Clear temporary files (manual)
-- rm -rf /path/to/sandbox/temp/*

-- Clear access logs (optional)
DELETE FROM sandbox_access_log
WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

### Disable Sandbox

```sql
-- Deactivate sandbox
UPDATE business_units SET is_active = 0 WHERE unit_id = 999;
UPDATE projects SET status = 'inactive' WHERE id = 999;
```

---

## Troubleshooting

### Sandbox Not Found

```bash
# Re-run migration
cd /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/admin/bot-deployment
mysql -u USER -p DATABASE < migrations/create_generic_sandbox.sql
```

### Access Denied Errors

```php
// Check sandbox mode
$isSandbox = SandboxHelper::isSandbox($projectId, $unitId);
var_dump($isSandbox);

// Check path validation
$isValid = SandboxHelper::validatePath($path, $isSandbox);
var_dump($isValid);
```

### Directory Not Created

```php
// Create sandbox structure
$basePath = '/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html';
$success = SandboxHelper::createSandboxStructure($basePath);

if (!$success) {
    echo "Failed to create sandbox directories - check permissions";
}
```

---

## Future Enhancements

- [ ] Sandbox-specific API rate limiting
- [ ] Per-IP access quotas
- [ ] Temporary API keys for sandbox testing
- [ ] Sandbox session expiration
- [ ] Advanced threat detection
- [ ] Sandbox analytics dashboard
- [ ] Multi-tenancy support
- [ ] Sandbox templates/presets

---

**Status**: ✅ Production Ready
**Version**: 1.0.0
**Last Updated**: November 5, 2025
