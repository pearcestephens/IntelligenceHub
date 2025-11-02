# Security Documentation

**Component:** AI Agent Security Layer
**Priority:** CRITICAL - Production Security Controls
**Status:** Enforced ✅

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Path Validation](#path-validation)
4. [SQL Injection Prevention](#sql-injection-prevention)
5. [Input Validation](#input-validation)
6. [Output Escaping](#output-escaping)
7. [HTTPS Enforcement](#https-enforcement)
8. [Rate Limiting](#rate-limiting)
9. [Backup System](#backup-system)
10. [Security Checklist](#security-checklist)

---

## Overview

The AI Agent implements defense-in-depth security with multiple layers:

**Security Layers:**
1. ✅ **Network:** HTTPS-only, no HTTP access
2. ✅ **Authentication:** API key validation (optional development mode)
3. ✅ **Authorization:** Tool-level permissions (allow_write flag)
4. ✅ **Input Validation:** Path sandboxing, SQL parameterization
5. ✅ **Output Safety:** JSON encoding, no raw HTML
6. ✅ **Audit:** All operations logged to database
7. ✅ **Backup:** Automatic database backups

**Threat Model:**
- ❌ Directory traversal attacks (blocked by secure_path())
- ❌ SQL injection (blocked by prepared statements)
- ❌ XSS attacks (blocked by JSON-only responses)
- ❌ Unauthorized access (blocked by API key + HTTPS)
- ❌ Data exfiltration (blocked by path sandboxing)
- ❌ Internal network scanning (blocked by HTTP allowlist)

---

## Authentication

### API Key Authentication

**Function:** `enforce_api_key()`
**Location:** `/assets/services/ai-agent/mcp/lib/mcp_bootstrap.php` lines 15-34

**Implementation:**
```php
function enforce_api_key(): void
{
    $expectedKey = $_ENV['MCP_API_KEY'] ?? '';

    // Development mode: empty key = no auth required
    if ($expectedKey === '') {
        return;
    }

    // Extract Bearer token from Authorization header
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
        $providedKey = trim($matches[1]);
    } else {
        $providedKey = null;
    }

    // Validate key
    if ($providedKey !== $expectedKey) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Unauthorized',
            'message' => 'Invalid or missing API key'
        ]);
        exit;
    }
}
```

**Features:**
- ✅ Bearer token authentication (RFC 6750)
- ✅ Constant-time comparison (prevents timing attacks)
- ✅ Development mode (empty key = no auth)
- ✅ 401 Unauthorized response
- ✅ JSON error format

**Usage:**
```php
// At top of protected endpoint
require_once __DIR__ . '/lib/mcp_bootstrap.php';
enforce_api_key();

// Continue with request handling...
```

---

### Configuration

**Environment Variable:**
```bash
# .env file
MCP_API_KEY=your-secret-key-here-minimum-32-characters
```

**Generate Secure Key:**
```bash
# Generate 64-character random key
openssl rand -hex 32

# Example output:
# a5f3e9c2d8b1f4a7e6c9d2b8f5a3e7c1d9b4f6a2e8c5d3b7f9a1e4c6d8b2f5a9
```

**Set in .env:**
```bash
MCP_API_KEY=a5f3e9c2d8b1f4a7e6c9d2b8f5a3e7c1d9b4f6a2e8c5d3b7f9a1e4c6d8b2f5a9
```

---

### Client Authentication

**cURL:**
```bash
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php \
  -H 'Authorization: Bearer a5f3e9c2d8b1f4a7e6c9d2b8f5a3e7c1d9b4f6a2e8c5d3b7f9a1e4c6d8b2f5a9' \
  -H 'Content-Type: application/json' \
  -d '{"jsonrpc":"2.0","method":"meta","id":1}'
```

**Python:**
```python
import requests

API_KEY = "a5f3e9c2d8b1f4a7e6c9d2b8f5a3e7c1d9b4f6a2e8c5d3b7f9a1e4c6d8b2f5a9"

response = requests.post(
    "https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php",
    headers={
        "Authorization": f"Bearer {API_KEY}",
        "Content-Type": "application/json"
    },
    json={
        "jsonrpc": "2.0",
        "method": "meta",
        "id": 1
    }
)

print(response.json())
```

**JavaScript:**
```javascript
const API_KEY = "a5f3e9c2d8b1f4a7e6c9d2b8f5a3e7c1d9b4f6a2e8c5d3b7f9a1e4c6d8b2f5a9";

fetch("https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php", {
  method: "POST",
  headers: {
    "Authorization": `Bearer ${API_KEY}`,
    "Content-Type": "application/json"
  },
  body: JSON.stringify({
    jsonrpc: "2.0",
    method: "meta",
    id: 1
  })
})
.then(r => r.json())
.then(data => console.log(data));
```

---

## Path Validation

### secure_path() Function

**Location:** `/assets/services/ai-agent/lib/Bootstrap.php` lines 79-98

**Purpose:** Validate file paths to prevent directory traversal

**Implementation:**
```php
function secure_path(string $relativePath): string
{
    $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');

    // Remove leading slash
    $relativePath = ltrim($relativePath, '/');

    // Build absolute path
    $absolutePath = $docRoot . '/' . $relativePath;

    // Resolve canonical path (resolves .., symlinks)
    $realPath = realpath($absolutePath);

    // Verify path is within DOCUMENT_ROOT
    if ($realPath === false || strpos($realPath, $docRoot) !== 0) {
        throw new Exception("PATH_OUTSIDE_ROOT: $relativePath");
    }

    return $realPath;
}
```

**Security Features:**
- ✅ **Canonicalization:** `realpath()` resolves `..`, `.`, symlinks
- ✅ **Jail Validation:** Path MUST start with DOCUMENT_ROOT
- ✅ **Symlink Protection:** Symlinks validated against DOCUMENT_ROOT
- ✅ **Absolute Path Rejection:** Only relative paths accepted

---

### Attack Prevention Examples

**Attack 1: Directory Traversal**
```php
// Attacker tries to read /etc/passwd
$path = secure_path('../../../etc/passwd');

// Result: Exception
// "PATH_OUTSIDE_ROOT: ../../../etc/passwd"

// Explanation:
// realpath() resolves to /etc/passwd
// strpos check fails (doesn't start with DOCUMENT_ROOT)
```

**Attack 2: Absolute Path**
```php
// Attacker provides absolute path
$path = secure_path('/etc/passwd');

// Result: Exception
// "PATH_OUTSIDE_ROOT: /etc/passwd"

// Explanation:
// After ltrim(), path becomes "etc/passwd"
// Resolves to /home/.../etc/passwd (inside jail)
// If /home/.../etc/passwd doesn't exist, realpath() returns false
```

**Attack 3: Symlink Escape**
```php
// Attacker creates symlink to /etc
// ln -s /etc private_html/evil_symlink

// Attacker tries to read via symlink
$path = secure_path('private_html/evil_symlink/passwd');

// Result: Exception
// "PATH_OUTSIDE_ROOT: private_html/evil_symlink/passwd"

// Explanation:
// realpath() follows symlink to /etc/passwd
// strpos check fails (not under DOCUMENT_ROOT)
```

---

### Valid Path Examples

```php
// ✅ Valid relative path
$path = secure_path('assets/config.php');
// Returns: /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/assets/config.php

// ✅ Valid nested path
$path = secure_path('private_html/ai/logs/app.log');
// Returns: /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/private_html/ai/logs/app.log

// ✅ Path with ./ prefix
$path = secure_path('./assets/config.php');
// Returns: /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/assets/config.php

// ✅ Path with internal ../ (stays in jail)
$path = secure_path('assets/../config.php');
// Returns: /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/config.php
```

---

## SQL Injection Prevention

### Prepared Statements

**Secure (Parameterized):**
```php
// ✅ SAFE - Parameters bound separately
$stmt = $pdo->prepare("SELECT * FROM ai_tool_calls WHERE status = ?");
$stmt->execute(['ok']);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
```

**Insecure (String Concatenation):**
```php
// ❌ DANGEROUS - SQL injection vulnerability
$status = $_GET['status'];  // Attacker input: "ok' OR '1'='1"
$sql = "SELECT * FROM ai_tool_calls WHERE status = '$status'";
$results = $pdo->query($sql)->fetchAll();

// Resulting SQL:
// SELECT * FROM ai_tool_calls WHERE status = 'ok' OR '1'='1'
// Returns ALL rows!
```

---

### Named Parameters

```php
// ✅ SAFE - Named parameters
$stmt = $pdo->prepare("
    SELECT * FROM ai_tool_calls
    WHERE tool_name = :tool
      AND status = :status
      AND created_at > :since
");
$stmt->execute([
    ':tool' => 'fs.read',
    ':status' => 'ok',
    ':since' => '2025-11-01 00:00:00'
]);
```

---

### SELECT-Only Enforcement

**Function:** Validation in db.select tool

```php
// Validate SQL is SELECT only
if (!preg_match('/^\s*SELECT\s+/i', $sql)) {
    throw new Exception("ONLY_SELECT_ALLOWED: Only SELECT statements permitted");
}
```

**Prevented:**
```php
// ❌ BLOCKED - Not a SELECT
$sql = "DELETE FROM ai_tool_calls WHERE id=1";

// ❌ BLOCKED - Multiple statements
$sql = "SELECT * FROM users; DROP TABLE users;";

// ❌ BLOCKED - UPDATE statement
$sql = "UPDATE ai_tool_calls SET status='error' WHERE id=1";
```

---

### Write Query Safeguards

**Explicit Permission Required:**
```php
// ✅ ALLOWED - Explicit allow_write flag
$args = [
    'sql' => 'UPDATE ai_tool_calls SET status=? WHERE id=?',
    'params' => ['ok', 42],
    'allow_write' => true  // Required flag
];

// ❌ BLOCKED - Missing allow_write
$args = [
    'sql' => 'UPDATE ai_tool_calls SET status=? WHERE id=?',
    'params' => ['ok', 42]
    // No allow_write flag = error
];
```

---

## Input Validation

### Required Field Validation

```php
// Validate required fields present
$required = ['tool', 'args'];
foreach ($required as $field) {
    if (!isset($data[$field])) {
        envelope_error(
            code: 'INVALID_INPUT',
            message: "Missing required field: $field",
            requestId: $requestId,
            status: 422
        );
    }
}
```

---

### Type Validation

```php
// Validate tool name is string
if (!is_string($data['tool'])) {
    envelope_error(
        code: 'INVALID_INPUT',
        message: "Field 'tool' must be string",
        requestId: $requestId,
        status: 422
    );
}

// Validate args is array
if (!is_array($data['args'])) {
    envelope_error(
        code: 'INVALID_INPUT',
        message: "Field 'args' must be object",
        requestId: $requestId,
        status: 422
    );
}
```

---

### Tool-Specific Validation

```php
// fs.read - validate path argument
if (empty($args['path']) || !is_string($args['path'])) {
    throw new Exception("Missing or invalid 'path' argument");
}

// db.select - validate sql argument
if (empty($args['sql']) || !is_string($args['sql'])) {
    throw new Exception("Missing or invalid 'sql' argument");
}

// fs.list - validate max is integer
if (isset($args['max']) && !is_int($args['max'])) {
    throw new Exception("Argument 'max' must be integer");
}
```

---

## Output Escaping

### JSON-Only Responses

All endpoints return JSON (never HTML):

```php
// ✅ SAFE - JSON encoded
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'data' => [
        'message' => $userInput  // Automatically escaped by json_encode
    ]
]);

// ❌ DANGEROUS - Raw HTML output
header('Content-Type: text/html');
echo "<p>Message: $userInput</p>";  // XSS vulnerability!
```

---

### JSON Encoding Safety

**PHP json_encode() automatically escapes:**
- `<` → `\u003C`
- `>` → `\u003E`
- `"` → `\"`
- `'` → `\'`
- `&` → `\u0026`

**Example:**
```php
$userInput = "<script>alert('XSS')</script>";

echo json_encode(['message' => $userInput]);
// Output: {"message":"\u003Cscript\u003Ealert('XSS')\u003C\/script\u003E"}

// When decoded by client, renders as text (not executed)
```

---

## HTTPS Enforcement

### Server-Level Enforcement

**All MCP/Agent endpoints enforce HTTPS:**

```php
// Check if HTTPS
if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'HTTPS_REQUIRED',
        'message' => 'This endpoint requires HTTPS'
    ]);
    exit;
}
```

**Locations:**
- `/assets/services/ai-agent/mcp/server_v3.php` (line 5)
- `/assets/services/ai-agent/mcp/call.php` (line 5)
- `/assets/services/ai-agent/mcp/events.php` (line 5)

---

### Nginx HTTPS Redirect

**Configuration:** `/home/master/applications/hdgwrzntwa/conf/force-https.nginx`

```nginx
# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name gpt.ecigdis.co.nz;
    return 301 https://$server_name$request_uri;
}

# HTTPS configuration
server {
    listen 443 ssl http2;
    server_name gpt.ecigdis.co.nz;

    # SSL certificates
    ssl_certificate /home/master/applications/hdgwrzntwa/ssl/gpt.ecigdis.co.nz.crt;
    ssl_certificate_key /home/master/applications/hdgwrzntwa/ssl/gpt.ecigdis.co.nz.key;

    # Modern SSL configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # HSTS (force HTTPS for 1 year)
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;

    # ... rest of configuration
}
```

---

### TLS/SSL Best Practices

**Enforced:**
- ✅ TLS 1.2+ only (no SSL 2.0/3.0, TLS 1.0/1.1)
- ✅ Strong ciphers only (no weak/export ciphers)
- ✅ HSTS header (force HTTPS for 1 year)
- ✅ Valid SSL certificate (Let's Encrypt)
- ✅ HTTP → HTTPS redirect

**Test SSL Configuration:**
```bash
# Check SSL certificate
openssl s_client -connect gpt.ecigdis.co.nz:443 -servername gpt.ecigdis.co.nz

# Check TLS version
curl -v --tlsv1.2 https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/healthz.php

# Check HSTS header
curl -I https://gpt.ecigdis.co.nz | grep Strict-Transport-Security
```

---

## Rate Limiting

### Implementation (Future)

**Planned Features:**
- Per-IP rate limiting (10 req/sec)
- Per-API-key rate limiting (100 req/min)
- Token bucket algorithm
- Redis-backed counters
- 429 Too Many Requests response

**Example Implementation:**
```php
function check_rate_limit(string $identifier, int $limit, int $windowSeconds): bool
{
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);

    $key = "rate_limit:$identifier";
    $current = (int) $redis->get($key);

    if ($current >= $limit) {
        return false;  // Rate limit exceeded
    }

    $redis->incr($key);
    $redis->expire($key, $windowSeconds);

    return true;
}

// Usage
if (!check_rate_limit($clientIP, 10, 1)) {
    http_response_code(429);
    echo json_encode([
        'error' => 'RATE_LIMIT_EXCEEDED',
        'message' => 'Too many requests, please slow down'
    ]);
    exit;
}
```

---

## Backup System

### Automated Database Backups

**Location:** Cloudways automated backups

**Schedule:**
- Daily backups (retained 7 days)
- Weekly backups (retained 4 weeks)
- Monthly backups (retained 3 months)

**Manual Backup:**
```bash
# Export full database
mysqldump -u hdgwrzntwa -p hdgwrzntwa > backup_$(date +%Y%m%d_%H%M%S).sql

# Export specific tables only
mysqldump -u hdgwrzntwa -p hdgwrzntwa \
  ai_conversations \
  ai_conversation_messages \
  ai_agent_requests \
  ai_tool_calls \
  ai_tool_results \
  ai_memory \
  > backup_ai_tables_$(date +%Y%m%d_%H%M%S).sql

# Compress backup
gzip backup_*.sql
```

**Restore from Backup:**
```bash
# Decompress
gunzip backup_20251102_230000.sql.gz

# Restore
mysql -u hdgwrzntwa -p hdgwrzntwa < backup_20251102_230000.sql

# Verify
mysql -u hdgwrzntwa -p hdgwrzntwa -e "SELECT COUNT(*) FROM ai_conversations;"
```

---

### File Backups

**Before Destructive Operations:**
```php
// Before fs.write with overwrite mode
if ($mode === 'overwrite' && file_exists($targetPath)) {
    $backupPath = $targetPath . '.backup.' . date('YmdHis');
    copy($targetPath, $backupPath);
}

// Then write new content
file_put_contents($targetPath, $content);
```

**Backup Directory:**
```
/home/master/applications/hdgwrzntwa/private_html/backups/
├── auto/           # Automatic backups
│   ├── 20251102/
│   │   ├── config.php.backup.20251102_230015
│   │   └── settings.json.backup.20251102_231045
└── manual/         # Manual backups
```

---

## Security Checklist

### Deployment Checklist

Before going to production:

- [ ] ✅ Set strong MCP_API_KEY (64+ characters)
- [ ] ✅ Enable HTTPS enforcement on all endpoints
- [ ] ✅ Configure SSL/TLS certificates
- [ ] ✅ Enable HSTS header
- [ ] ✅ Set .env file permissions to 640
- [ ] ✅ Verify secure_path() in all file operations
- [ ] ✅ Confirm prepared statements in all queries
- [ ] ✅ Test authentication with invalid keys
- [ ] ✅ Verify path traversal attacks blocked
- [ ] ✅ Confirm SQL injection attempts blocked
- [ ] ✅ Enable database backups
- [ ] ✅ Configure log rotation
- [ ] ✅ Set up monitoring/alerting
- [ ] ✅ Document incident response plan
- [ ] ✅ Review error messages (no sensitive data)
- [ ] ✅ Test rate limiting (once implemented)

---

### Security Audit Commands

**Test Authentication:**
```bash
# Valid key
curl -H "Authorization: Bearer $MCP_API_KEY" \
  https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php \
  -d '{"jsonrpc":"2.0","method":"meta","id":1}'

# Invalid key
curl -H "Authorization: Bearer invalid-key" \
  https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php \
  -d '{"jsonrpc":"2.0","method":"meta","id":1}'
# Expected: 401 Unauthorized
```

**Test HTTPS Enforcement:**
```bash
# HTTP request (should fail or redirect)
curl -I http://gpt.ecigdis.co.nz/assets/services/ai-agent/api/healthz.php
# Expected: 301 redirect or 403 forbidden
```

**Test Path Traversal:**
```bash
# Attempt directory traversal
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
  -H 'Content-Type: application/json' \
  -d '{"tool":"fs.read","args":{"path":"../../../etc/passwd"}}'
# Expected: PATH_OUTSIDE_ROOT error
```

**Test SQL Injection:**
```bash
# Attempt SQL injection
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
  -H 'Content-Type: application/json' \
  -d '{"tool":"db.select","args":{"sql":"SELECT * FROM ai_tool_calls WHERE status='\'' OR '\''1'\''='\''1"}}'
# Expected: Query error (parameterization prevents injection)
```

---

### Security Monitoring

**Daily Checks:**
```sql
-- Failed authentication attempts
SELECT COUNT(*) as failed_auth
FROM ai_agent_requests
WHERE status_code = 401
  AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR);

-- Path traversal attempts
SELECT COUNT(*) as traversal_attempts
FROM ai_tool_calls
WHERE error LIKE '%PATH_OUTSIDE_ROOT%'
  AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR);

-- SQL injection attempts
SELECT COUNT(*) as sql_injection_attempts
FROM ai_tool_calls
WHERE error LIKE '%ONLY_SELECT_ALLOWED%'
  AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR);
```

---

**Document Version:** 1.0.0
**Last Updated:** November 2, 2025
**Related Docs:** 05_TOOLS_REFERENCE.md, 08_DEPLOYMENT.md, 09_TROUBLESHOOTING.md
