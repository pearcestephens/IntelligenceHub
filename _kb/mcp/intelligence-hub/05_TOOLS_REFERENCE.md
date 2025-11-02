# Tools Reference Documentation

**Component:** AI Agent Local Tools
**Location:** `/assets/services/ai-agent/api/tools/invoke.php`
**Registry:** Tool definitions with schemas
**Status:** 8 Tools Operational ✅

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Tool Invocation](#tool-invocation)
3. [Filesystem Tools](#filesystem-tools)
4. [Database Tools](#database-tools)
5. [System Tools](#system-tools)
6. [Network Tools](#network-tools)
7. [Security Constraints](#security-constraints)
8. [Error Handling](#error-handling)
9. [Usage Examples](#usage-examples)

---

## Overview

The AI Agent provides **8 local tools** for executing operations:

| Tool | Category | Purpose | Risk Level |
|------|----------|---------|-----------|
| fs.read | Filesystem | Read file contents | Low |
| fs.list | Filesystem | List directory entries | Low |
| fs.write | Filesystem | Write file contents | **High** |
| db.select | Database | SELECT queries | Low |
| db.exec | Database | Write queries (INSERT/UPDATE/DELETE) | **High** |
| logs.tail | System | Tail log files | Low |
| http.fetch | Network | HTTP GET requests | Medium |
| devkit.* | Proxy | Forward to external Devkit | **Varies** |

**Design Principles:**
- ✅ Sandboxed execution (secure_path() validation)
- ✅ Explicit security gates (allow_write flag)
- ✅ Host allowlist for HTTP requests
- ✅ Detailed error messages
- ✅ Telemetry logging (ai_tool_calls table)

---

## Tool Invocation

### Endpoint

```
POST /assets/services/ai-agent/api/tools/invoke.php
```

### Request Format

```json
{
  "tool": "fs.read",              // Tool name (required)
  "args": {                       // Tool-specific arguments (required)
    "path": "assets/config.php"
  },
  "session_key": "user-123",      // Optional session tracking
  "conversation_id": 17,          // Optional conversation link
  "message_id": 22                // Optional message link
}
```

### Response Format

```json
{
  "success": true,
  "request_id": "abc123",
  "data": {
    "tool": "fs.read",
    "result": {
      // Tool-specific result data
    },
    "latency_ms": 5
  },
  "meta": {
    "ts": "2025-11-02T23:00:00+13:00",
    "host": "gpt.ecigdis.co.nz",
    "ip": "45.32.241.246",
    "version": "2025.11.02"
  }
}
```

### Tool Registry

Tools are registered with JSON schemas:

```php
$tools = [
    'fs.read' => [
        'description' => 'Read a file from the filesystem',
        'args' => [
            'path' => ['type' => 'string', 'required' => true]
        ]
    ],
    // ... more tools
];
```

---

## Filesystem Tools

### 1. fs.read

**Purpose:** Read file contents from the filesystem

**Arguments:**
```json
{
  "path": "relative/path/to/file.php"  // Required, relative to DOCUMENT_ROOT
}
```

**Security:**
- ✅ Path validated with `secure_path()` function
- ✅ Only files within DOCUMENT_ROOT accessible
- ✅ Directory traversal attacks blocked (../)
- ✅ Symbolic link validation

**Example Request:**
```bash
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
  -H 'Content-Type: application/json' \
  -d '{
    "tool": "fs.read",
    "args": {
      "path": "assets/services/ai-agent/lib/Bootstrap.php"
    }
  }'
```

**Success Response:**
```json
{
  "success": true,
  "request_id": "5d7fc38fc28184c4f1b115c3489f0b92",
  "data": {
    "tool": "fs.read",
    "result": {
      "path": "/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/assets/services/ai-agent/lib/Bootstrap.php",
      "bytes": 5248,
      "content": "<?php\ndeclare(strict_types=1);\n\ndefine('AI_AGENT_VERSION', '2025.11.02');\n..."
    },
    "latency_ms": 5
  },
  "meta": {...}
}
```

**Error Response (File Not Found):**
```json
{
  "success": false,
  "request_id": "abc123",
  "error": {
    "code": "FILE_NOT_FOUND",
    "message": "FILE_NOT_FOUND: /home/.../invalid.php",
    "detail": {...}
  },
  "meta": {...}
}
```

**Use Cases:**
- Read configuration files
- Inspect source code
- Load JSON/YAML data files
- Review log file contents

**Limitations:**
- Maximum file size: 10MB (practical limit)
- Binary files returned as base64 (optional)
- No streaming support (loads entire file)

---

### 2. fs.list

**Purpose:** List directory contents (files and subdirectories)

**Arguments:**
```json
{
  "path": "assets/services",    // Required, relative path
  "max": 500                    // Optional, max entries (default 500)
}
```

**Security:**
- ✅ Path validated with `secure_path()`
- ✅ Only directories within DOCUMENT_ROOT
- ✅ Directory traversal blocked
- ✅ Hidden files included (starting with .)

**Example Request:**
```bash
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
  -H 'Content-Type: application/json' \
  -d '{
    "tool": "fs.list",
    "args": {
      "path": "assets/services/ai-agent",
      "max": 10
    }
  }'
```

**Success Response:**
```json
{
  "success": true,
  "request_id": "xyz789",
  "data": {
    "tool": "fs.list",
    "result": {
      "path": "/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/assets/services/ai-agent",
      "entries": [
        {
          "name": "api",
          "type": "dir",
          "size": null,
          "modified": null
        },
        {
          "name": "lib",
          "type": "dir",
          "size": null,
          "modified": null
        },
        {
          "name": "mcp",
          "type": "dir",
          "size": null,
          "modified": null
        },
        {
          "name": ".env.example",
          "type": "file",
          "size": 512,
          "modified": "2025-10-28T15:30:00+13:00"
        }
      ],
      "total": 4,
      "truncated": false
    },
    "latency_ms": 0
  },
  "meta": {...}
}
```

**Error Response (Directory Not Found):**
```json
{
  "success": false,
  "request_id": "def456",
  "error": {
    "code": "DIR_NOT_FOUND",
    "message": "DIR_NOT_FOUND: /home/.../invalid-dir",
    "detail": {...}
  },
  "meta": {...}
}
```

**Response Fields:**
- `entries`: Array of directory entries
  - `name`: File/directory name
  - `type`: "file" or "dir"
  - `size`: File size in bytes (null for directories)
  - `modified`: ISO 8601 timestamp (null for directories)
- `total`: Total entries returned
- `truncated`: True if more entries exist than `max`

**Use Cases:**
- Explore directory structure
- Find files matching pattern
- List configuration directories
- Discover available modules

**Limitations:**
- Maximum 500 entries per request (configurable)
- No recursive listing (single level only)
- No sorting options (alphabetical by default)

---

### 3. fs.write

**Purpose:** Write content to a file (create or overwrite)

**Arguments:**
```json
{
  "path": "private_html/logs/custom.log",  // Required, relative path
  "content": "Log entry text",             // Required, file content
  "mode": "overwrite"                      // Optional: "overwrite" or "append"
}
```

**Security:**
- ⚠️ **HIGH RISK** - Can modify/create files
- ✅ Path validated with `secure_path()`
- ✅ Only paths within DOCUMENT_ROOT
- ✅ Requires explicit permission (future: admin-only)
- ✅ Atomic write (temp file + rename)

**Example Request:**
```bash
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
  -H 'Content-Type: application/json' \
  -d '{
    "tool": "fs.write",
    "args": {
      "path": "private_html/ai/test_output.txt",
      "content": "Test content from AI agent",
      "mode": "overwrite"
    }
  }'
```

**Success Response:**
```json
{
  "success": true,
  "request_id": "write123",
  "data": {
    "tool": "fs.write",
    "result": {
      "path": "/home/.../private_html/ai/test_output.txt",
      "bytes_written": 27,
      "mode": "overwrite"
    },
    "latency_ms": 3
  },
  "meta": {...}
}
```

**Error Response (Permission Denied):**
```json
{
  "success": false,
  "request_id": "write456",
  "error": {
    "code": "WRITE_FAILED",
    "message": "WRITE_FAILED: Permission denied",
    "detail": {...}
  },
  "meta": {...}
}
```

**Modes:**
- `overwrite` (default): Replace entire file contents
- `append`: Add content to end of existing file

**Use Cases:**
- Create log files
- Generate reports
- Save configuration changes
- Write API responses to disk

**Limitations:**
- Maximum content size: 10MB
- No partial file updates (full write only)
- No file locking (concurrent writes may conflict)

**Safety Recommendations:**
- ✅ Always validate content before writing
- ✅ Use unique filenames to avoid conflicts
- ✅ Write to private_html/ for sensitive data
- ✅ Implement backup before overwrite (future)

---

## Database Tools

### 4. db.select

**Purpose:** Execute SELECT queries against the database

**Arguments:**
```json
{
  "sql": "SELECT * FROM ai_tool_calls WHERE status=? ORDER BY created_at DESC LIMIT ?",
  "params": ["ok", 10]  // Optional, bound parameters
}
```

**Security:**
- ✅ **READ-ONLY** enforced (only SELECT allowed)
- ✅ Prepared statements (SQL injection protection)
- ✅ Bound parameters required for variables
- ✅ Connection pooling via PDO
- ✅ Row count limit: 1000 (soft limit)

**Example Request:**
```bash
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
  -H 'Content-Type: application/json' \
  -d '{
    "tool": "db.select",
    "args": {
      "sql": "SELECT COUNT(*) as total FROM ai_tool_calls WHERE status=?",
      "params": ["ok"]
    }
  }'
```

**Success Response:**
```json
{
  "success": true,
  "request_id": "db123",
  "data": {
    "tool": "db.select",
    "result": {
      "rows": [
        {"total": 5}
      ],
      "row_count": 1,
      "columns": ["total"]
    },
    "latency_ms": 2
  },
  "meta": {...}
}
```

**Complex Query Example:**
```json
{
  "tool": "db.select",
  "args": {
    "sql": "SELECT tool_name, COUNT(*) as calls, AVG(latency_ms) as avg_latency FROM ai_tool_calls WHERE created_at > ? GROUP BY tool_name ORDER BY calls DESC",
    "params": ["2025-11-01 00:00:00"]
  }
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "tool": "db.select",
    "result": {
      "rows": [
        {"tool_name": "fs.list", "calls": 5, "avg_latency": 0.8},
        {"tool_name": "fs.read", "calls": 3, "avg_latency": 4.2},
        {"tool_name": "db.select", "calls": 2, "avg_latency": 1.5}
      ],
      "row_count": 3,
      "columns": ["tool_name", "calls", "avg_latency"]
    },
    "latency_ms": 8
  },
  "meta": {...}
}
```

**Error Response (Invalid SQL):**
```json
{
  "success": false,
  "request_id": "db456",
  "error": {
    "code": "ONLY_SELECT_ALLOWED",
    "message": "ONLY_SELECT_ALLOWED: Only SELECT statements permitted",
    "detail": {...}
  },
  "meta": {...}
}
```

**Use Cases:**
- Query conversation history
- Analyze tool usage statistics
- Search memory store
- Generate reports

**Limitations:**
- SELECT queries only (no INSERT/UPDATE/DELETE)
- Maximum 1000 rows returned (configurable)
- No transaction support
- Results always returned as JSON

**Query Best Practices:**
- ✅ Always use prepared statements with params
- ✅ Include LIMIT clause for large result sets
- ✅ Use indexes for performance (see 04_DATABASE_SCHEMA.md)
- ✅ Test queries in MySQL client first

---

### 5. db.exec

**Purpose:** Execute write queries (INSERT, UPDATE, DELETE)

**Arguments:**
```json
{
  "sql": "UPDATE ai_tool_calls SET status=? WHERE id=?",
  "params": ["ok", 42],
  "allow_write": true  // Required explicit flag
}
```

**Security:**
- ⚠️ **HIGH RISK** - Can modify database
- ✅ Requires `allow_write: true` flag
- ✅ Prepared statements (SQL injection protection)
- ✅ Transaction support (future)
- ✅ Row count returned
- ✅ Audit logging to ai_tool_calls

**Example Request (UPDATE):**
```bash
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
  -H 'Content-Type: application/json' \
  -d '{
    "tool": "db.exec",
    "args": {
      "sql": "UPDATE ai_tool_calls SET status=? WHERE id=?",
      "params": ["ok", 42],
      "allow_write": true
    }
  }'
```

**Success Response:**
```json
{
  "success": true,
  "request_id": "exec123",
  "data": {
    "tool": "db.exec",
    "result": {
      "affected_rows": 1,
      "last_insert_id": 0
    },
    "latency_ms": 3
  },
  "meta": {...}
}
```

**Example Request (INSERT):**
```json
{
  "tool": "db.exec",
  "args": {
    "sql": "INSERT INTO ai_memory (scope, `key`, value, confidence, source) VALUES (?, ?, ?, ?, ?)",
    "params": ["session", "test_key", "{\"data\":\"test\"}", 90, "system"],
    "allow_write": true
  }
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "tool": "db.exec",
    "result": {
      "affected_rows": 1,
      "last_insert_id": 123
    },
    "latency_ms": 4
  },
  "meta": {...}
}
```

**Error Response (Missing allow_write):**
```json
{
  "success": false,
  "request_id": "exec456",
  "error": {
    "code": "ALLOW_WRITE_REQUIRED",
    "message": "ALLOW_WRITE_REQUIRED: Must set allow_write=true for write operations",
    "detail": {...}
  },
  "meta": {...}
}
```

**Supported Operations:**
- INSERT - Add new records
- UPDATE - Modify existing records
- DELETE - Remove records
- REPLACE - Insert or update
- CREATE TABLE - Schema changes (use with caution)
- ALTER TABLE - Schema modifications (use with caution)

**Use Cases:**
- Update tool call status
- Insert memory records
- Delete old log entries
- Bulk data updates

**Limitations:**
- No transaction support (single statement only)
- No rollback capability
- Schema changes risky (no migration tracking)

**Safety Recommendations:**
- ⚠️ **Always backup before bulk updates**
- ✅ Test queries with SELECT first
- ✅ Use transactions for multi-step operations (future)
- ✅ Limit scope with WHERE clauses
- ✅ Verify affected_rows matches expectations

---

## System Tools

### 6. logs.tail

**Purpose:** Read last N lines from a log file

**Arguments:**
```json
{
  "path": "logs/apache_phpstack.error.log",  // Required, relative path
  "lines": 200                                 // Optional, default 200
}
```

**Security:**
- ✅ Path validated with `secure_path()`
- ✅ Only files within DOCUMENT_ROOT
- ✅ Read-only access
- ✅ Maximum 1000 lines per request

**Example Request:**
```bash
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
  -H 'Content-Type: application/json' \
  -d '{
    "tool": "logs.tail",
    "args": {
      "path": "logs/apache_phpstack-129337-5615757.cloudwaysapps.com.error.log",
      "lines": 50
    }
  }'
```

**Success Response:**
```json
{
  "success": true,
  "request_id": "logs123",
  "data": {
    "tool": "logs.tail",
    "result": {
      "path": "/home/.../logs/apache_phpstack-129337-5615757.cloudwaysapps.com.error.log",
      "lines_requested": 50,
      "lines_returned": 50,
      "content": "[Sat Nov 02 23:00:01 2025] [error] [client 45.32.241.246] PHP Notice: Undefined variable...\n[Sat Nov 02 23:00:15 2025] [error] [client 45.32.241.246] PHP Warning: Division by zero...\n..."
    },
    "latency_ms": 8
  },
  "meta": {...}
}
```

**Error Response (File Not Found):**
```json
{
  "success": false,
  "request_id": "logs456",
  "error": {
    "code": "FILE_NOT_FOUND",
    "message": "FILE_NOT_FOUND: Log file does not exist",
    "detail": {...}
  },
  "meta": {...}
}
```

**Use Cases:**
- Debug application errors
- Monitor recent activity
- Analyze request patterns
- Troubleshoot failures

**Limitations:**
- Maximum 1000 lines per request
- No real-time streaming (snapshot only)
- Large log files may be slow (> 100MB)
- No filtering/grep support (use db.select for structured logs)

**Common Log Paths:**
- Apache Error: `logs/apache_phpstack-129337-5615757.cloudwaysapps.com.error.log`
- Apache Access: `logs/apache_phpstack-129337-5615757.cloudwaysapps.com.access.log`
- PHP-FPM: `logs/php-app.access.log`
- Nginx Error: `logs/nginx-app.error.log`
- Application: `private_html/logs/*.log`

---

## Network Tools

### 7. http.fetch

**Purpose:** Fetch content from HTTP/HTTPS URLs

**Arguments:**
```json
{
  "url": "https://api.example.com/data",  // Required, full URL
  "method": "GET",                         // Optional, default GET
  "headers": {                             // Optional headers
    "Authorization": "Bearer token123"
  },
  "body": "{\"key\":\"value\"}"           // Optional, for POST/PUT
}
```

**Security:**
- ⚠️ **MEDIUM RISK** - Can access external APIs
- ✅ Host allowlist enforcement (configurable)
- ✅ HTTPS recommended
- ✅ Timeout: 30 seconds
- ✅ Maximum response size: 10MB
- ✅ No file:// or internal IPs allowed

**Example Request:**
```bash
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
  -H 'Content-Type: application/json' \
  -d '{
    "tool": "http.fetch",
    "args": {
      "url": "https://api.github.com/repos/octocat/Hello-World",
      "method": "GET",
      "headers": {
        "User-Agent": "AI-Agent/1.0"
      }
    }
  }'
```

**Success Response:**
```json
{
  "success": true,
  "request_id": "http123",
  "data": {
    "tool": "http.fetch",
    "result": {
      "status": 200,
      "headers": {
        "content-type": "application/json; charset=utf-8",
        "content-length": "1234"
      },
      "body": "{\"id\":1296269,\"name\":\"Hello-World\",\"full_name\":\"octocat/Hello-World\"...}",
      "latency_ms": 250
    },
    "latency_ms": 251
  },
  "meta": {...}
}
```

**Error Response (Host Not Allowed):**
```json
{
  "success": false,
  "request_id": "http456",
  "error": {
    "code": "HOST_NOT_ALLOWED",
    "message": "HOST_NOT_ALLOWED: example.com not in allowlist",
    "detail": {...}
  },
  "meta": {...}
}
```

**Allowed Hosts (Default):**
- api.github.com
- api.openai.com
- api.anthropic.com
- staff.vapeshed.co.nz (internal)
- gpt.ecigdis.co.nz (internal)

**Use Cases:**
- Fetch external API data
- Webhook notifications
- Integration with external services
- Download remote files

**Limitations:**
- 30-second timeout
- 10MB response limit
- No cookie/session management
- No redirect following (manual handling required)

**Safety Recommendations:**
- ✅ Validate URLs before fetching
- ✅ Use HTTPS for sensitive data
- ✅ Add authentication headers when needed
- ✅ Handle timeout errors gracefully

---

## Security Constraints

### Path Validation (secure_path)

**Function:** `secure_path($relativePath)`
**Location:** `lib/Bootstrap.php` lines 79-98

**Purpose:** Validate and resolve file paths safely

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

**Protections:**
- ✅ Directory traversal blocked (`../` resolved to absolute path)
- ✅ Symbolic links validated (must stay within DOCUMENT_ROOT)
- ✅ Absolute paths rejected (must be relative)
- ✅ Non-existent paths return false from realpath()

**Example Attacks Blocked:**
```php
secure_path('../../../etc/passwd');        // BLOCKED
secure_path('/etc/passwd');                 // BLOCKED
secure_path('symlink-to-etc');              // BLOCKED (if outside root)
secure_path('valid/path/../../../etc');     // BLOCKED
```

**Valid Paths:**
```php
secure_path('assets/config.php');           // ✅
secure_path('private_html/logs/app.log');   // ✅
secure_path('./relative/path.php');         // ✅
```

---

### Database Query Validation

**Prepared Statements:**
```php
// ✅ SAFE - Parameterized query
$stmt = $pdo->prepare("SELECT * FROM ai_tool_calls WHERE status=?");
$stmt->execute([$status]);

// ❌ UNSAFE - SQL injection risk (NOT USED)
$result = $pdo->query("SELECT * FROM ai_tool_calls WHERE status='$status'");
```

**SELECT-Only Enforcement:**
```php
if (!preg_match('/^\s*SELECT\s+/i', $sql)) {
    throw new Exception("ONLY_SELECT_ALLOWED: Only SELECT statements permitted");
}
```

**Write Query Protection:**
```php
if (!($args['allow_write'] ?? false)) {
    throw new Exception("ALLOW_WRITE_REQUIRED: Must set allow_write=true");
}
```

---

### HTTP Fetch Host Allowlist

**Allowed Hosts Configuration:**
```php
$allowedHosts = [
    'api.github.com',
    'api.openai.com',
    'api.anthropic.com',
    'staff.vapeshed.co.nz',
    'gpt.ecigdis.co.nz'
];

$parsed = parse_url($url);
$host = $parsed['host'] ?? '';

if (!in_array($host, $allowedHosts)) {
    throw new Exception("HOST_NOT_ALLOWED: $host not in allowlist");
}
```

**Internal IP Protection:**
```php
// Block private IP ranges
$ip = gethostbyname($host);
if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
    throw new Exception("INTERNAL_IP_BLOCKED: $ip");
}
```

---

## Error Handling

### Common Tool Errors

| Error Code | HTTP Status | Cause | Solution |
|-----------|------------|-------|----------|
| FILE_NOT_FOUND | 500 | File doesn't exist | Check path spelling |
| DIR_NOT_FOUND | 500 | Directory doesn't exist | Verify directory exists |
| PATH_OUTSIDE_ROOT | 500 | Path escapes DOCUMENT_ROOT | Use relative path within root |
| WRITE_FAILED | 500 | Write permission denied | Check file/dir permissions |
| ONLY_SELECT_ALLOWED | 500 | Non-SELECT query | Use db.select for SELECT only |
| SQL_REQUIRED | 500 | Missing sql argument | Provide sql parameter |
| ALLOW_WRITE_REQUIRED | 500 | Missing allow_write flag | Set allow_write=true |
| HOST_NOT_ALLOWED | 500 | HTTP host not allowlisted | Add host to allowlist |
| FETCH_TIMEOUT | 500 | HTTP request timeout | Check URL availability |
| FETCH_FAILED | 500 | HTTP request failed | Check network/URL |

### Error Response Format

All tool errors return consistent envelope:

```json
{
  "success": false,
  "request_id": "abc123",
  "error": {
    "code": "ERROR_CODE",
    "message": "Human-readable description",
    "detail": {
      "trace": "Stack trace",
      "tool": "fs.read",
      "args": {...}
    }
  },
  "meta": {...}
}
```

---

## Usage Examples

### Example 1: Read and Parse Configuration File

```bash
# Step 1: Read file
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
  -H 'Content-Type: application/json' \
  -d '{
    "tool": "fs.read",
    "args": {"path": "assets/config.json"}
  }' | jq -r '.data.result.content' > config.json

# Step 2: Parse with jq
cat config.json | jq '.database'
```

---

### Example 2: List and Analyze Tool Usage

```bash
# Step 1: Query tool usage stats
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
  -H 'Content-Type: application/json' \
  -d '{
    "tool": "db.select",
    "args": {
      "sql": "SELECT tool_name, COUNT(*) as calls, AVG(latency_ms) as avg_ms FROM ai_tool_calls WHERE created_at > ? GROUP BY tool_name",
      "params": ["2025-11-01 00:00:00"]
    }
  }' | jq '.data.result.rows'
```

**Output:**
```json
[
  {"tool_name": "fs.list", "calls": 5, "avg_ms": 0.8},
  {"tool_name": "fs.read", "calls": 3, "avg_ms": 4.2},
  {"tool_name": "db.select", "calls": 2, "avg_ms": 1.5}
]
```

---

### Example 3: Tail Logs and Find Errors

```bash
# Get last 100 log lines
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
  -H 'Content-Type: application/json' \
  -d '{
    "tool": "logs.tail",
    "args": {
      "path": "logs/apache_phpstack-129337-5615757.cloudwaysapps.com.error.log",
      "lines": 100
    }
  }' | jq -r '.data.result.content' | grep 'error'
```

---

### Example 4: Fetch External API Data

```bash
# Fetch GitHub repo data
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
  -H 'Content-Type: application/json' \
  -d '{
    "tool": "http.fetch",
    "args": {
      "url": "https://api.github.com/repos/octocat/Hello-World",
      "method": "GET",
      "headers": {"User-Agent": "AI-Agent/1.0"}
    }
  }' | jq '.data.result.body | fromjson | {name, stars: .stargazers_count}'
```

---

### Example 5: Write Report File

```bash
# Generate and save report
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
  -H 'Content-Type: application/json' \
  -d '{
    "tool": "fs.write",
    "args": {
      "path": "private_html/ai/daily_report.txt",
      "content": "Daily Report - November 2, 2025\n\nTotal Requests: 150\nSuccess Rate: 98.5%\nAverage Latency: 120ms\n",
      "mode": "overwrite"
    }
  }' | jq '.data.result'
```

---

**Document Version:** 1.0.0
**Last Updated:** November 2, 2025
**Related Docs:** 03_AI_AGENT_ENDPOINTS.md, 07_SECURITY.md, 09_TROUBLESHOOTING.md
