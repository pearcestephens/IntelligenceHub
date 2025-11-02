# MCP Server Implementation Guide

**Component:** Model Context Protocol (MCP) Server
**Location:** `/mcp/server_v3.php`
**Protocol:** JSON-RPC 2.0 over HTTP
**Status:** Production Ready ✅

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Endpoints](#endpoints)
4. [Authentication](#authentication)
5. [Tool Registry](#tool-registry)
6. [JSON-RPC Protocol](#json-rpc-protocol)
7. [Error Handling](#error-handling)
8. [Configuration](#configuration)
9. [Integration Examples](#integration-examples)

---

## Overview

The MCP Server is a JSON-RPC 2.0 gateway that exposes tools to AI assistants (VS Code Copilot, Claude Desktop, etc.). It provides:

- **Tool Discovery:** Dynamic catalog of available tools with schemas
- **Tool Invocation:** Execute tools via JSON-RPC requests
- **Health Monitoring:** System health and readiness checks
- **Authentication:** Optional API key protection
- **HTTPS:** All communication forced to secure protocol

---

## Architecture

### Request Flow

```
┌─────────────┐
│  AI Client  │ (VS Code, Claude Desktop, etc.)
└──────┬──────┘
       │ HTTP GET ?action=meta
       │ (Discover tools)
       ↓
┌─────────────────────┐
│ server_v3.php       │
│ ?action=meta        │ ← Returns tool catalog
└─────────────────────┘

       │ HTTP POST ?action=rpc
       │ (Invoke tool)
       ↓
┌─────────────────────┐
│ server_v3.php       │
│ ?action=rpc         │ ← Processes JSON-RPC
│                     │
│ 1. Validate API key │
│ 2. Parse JSON-RPC   │
│ 3. Route to handler │
│ 4. Execute tool     │
│ 5. Return result    │
└──────┬──────────────┘
       │
       ↓
┌─────────────────────┐
│ Tool Handler        │
│ (invoke.php via     │
│  HTTP call)         │
└─────────────────────┘
```

### File Dependencies

```
mcp/server_v3.php
├── mcp_tools_turbo.php       # Helper functions (envv, http_raw)
├── assets/services/ai-agent/
│   └── api/tools/invoke.php  # Tool execution endpoint
└── .env                      # MCP_API_KEY configuration
```

---

## Endpoints

### 1. Meta Endpoint (Tool Catalog)

**URL:** `GET https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=meta`

**Purpose:** Returns complete tool catalog with schemas

**Response Format:**
```json
{
  "name": "ecigdis-mcp",
  "version": "3.0",
  "description": "Ecigdis AI Agent MCP Server",
  "server_info": {
    "url": "https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=rpc"
  },
  "capabilities": {
    "tools": true
  },
  "tools": [
    {
      "name": "fs.read",
      "description": "Read file contents",
      "inputSchema": {
        "type": "object",
        "properties": {
          "path": {
            "type": "string",
            "description": "Relative or absolute path"
          }
        },
        "required": ["path"]
      }
    }
    // ... more tools
  ]
}
```

**Headers:**
```
Content-Type: application/json; charset=utf-8
Cache-Control: no-store, no-cache, must-revalidate, max-age=0
X-Content-Type-Options: nosniff
```

**Authentication:** ❌ Not required (public catalog)

---

### 2. Health Endpoint

**URL:** `GET https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=health`

**Purpose:** Quick health check for monitoring

**Response Format:**
```json
{
  "status": "ok",
  "timestamp": "2025-11-02T23:00:00+13:00"
}
```

**Authentication:** ❌ Not required

---

### 3. JSON-RPC Endpoint

**URL:** `POST https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=rpc`

**Purpose:** Execute tools via JSON-RPC 2.0 protocol

**Request Format:**
```json
{
  "jsonrpc": "2.0",
  "id": "req-12345",
  "method": "tools/call",
  "params": {
    "name": "fs.read",
    "arguments": {
      "path": "assets/services/ai-agent/lib/Bootstrap.php"
    }
  }
}
```

**Success Response:**
```json
{
  "jsonrpc": "2.0",
  "id": "req-12345",
  "result": {
    "content": [
      {
        "type": "text",
        "text": "<?php\ndeclare(strict_types=1);\n..."
      }
    ]
  }
}
```

**Error Response:**
```json
{
  "jsonrpc": "2.0",
  "id": "req-12345",
  "error": {
    "code": -32000,
    "message": "TOOL_ERROR: FILE_NOT_FOUND",
    "data": {
      "request_id": "abc123"
    }
  }
}
```

**Authentication:** ✅ Optional (MCP_API_KEY)

---

## Authentication

### API Key Configuration

**Environment Variable:**
```ini
# .env
MCP_API_KEY=your-secret-key-here
```

**Empty Key = Development Mode:**
If `MCP_API_KEY` is empty or not set, **all requests are allowed** (for development).

### Bearer Token Support

**Header Format:**
```
Authorization: Bearer your-secret-key-here
```

**Query Parameter (Fallback):**
```
?api_key=your-secret-key-here
```

### Function: `enforce_api_key()`

**Location:** `server_v3.php` lines 16-31

**Logic:**
```php
function enforce_api_key(string $providedKey): void {
    $expectedKey = envv('MCP_API_KEY', '');

    // Development mode: empty key = allow all
    if (empty($expectedKey)) {
        return;
    }

    // Check Authorization header first
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
        $providedKey = $matches[1];
    }

    if ($providedKey !== $expectedKey) {
        throw new UnauthorizedException('Invalid or missing API key');
    }
}
```

**When Called:**
- ✅ Every RPC request (line 62)
- ❌ Meta endpoint (public catalog)
- ❌ Health endpoint (monitoring)

---

## Tool Registry

### How Tools Are Registered

**Function:** `build_meta_manifest()`
**Location:** `server_v3.php` lines 159-268

**Tool Definitions:**
```php
$tools = [
    'fs.read' => [
        'description' => 'Read file contents',
        'endpoint' => '/assets/services/ai-agent/api/tools/invoke.php',
        'inputSchema' => [
            'type' => 'object',
            'properties' => [
                'path' => [
                    'type' => 'string',
                    'description' => 'Relative or absolute path'
                ]
            ],
            'required' => ['path']
        ]
    ],
    // ... more tools
];
```

### Tool Schema Format

Each tool MUST have:
- `description`: Human-readable purpose
- `endpoint`: HTTP endpoint for execution
- `inputSchema`: JSON Schema for validation

**JSON Schema Types:**
- `string` - Text values
- `number` - Numeric values
- `integer` - Whole numbers
- `boolean` - true/false
- `object` - Nested structures
- `array` - Lists

**Example: Complex Schema**
```json
{
  "type": "object",
  "properties": {
    "sql": {
      "type": "string",
      "description": "SELECT query to execute"
    },
    "params": {
      "type": "array",
      "items": {"type": "string"},
      "description": "Query parameters"
    }
  },
  "required": ["sql"]
}
```

---

## JSON-RPC Protocol

### Request Structure

**Required Fields:**
```json
{
  "jsonrpc": "2.0",        // Protocol version (fixed)
  "id": "<unique-id>",     // Request identifier
  "method": "tools/call",  // Method name (fixed)
  "params": {              // Method parameters
    "name": "<tool-name>",
    "arguments": {
      // Tool-specific arguments
    }
  }
}
```

### Method: `tools/call`

**Purpose:** Execute a registered tool

**Parameters:**
- `name` (string, required): Tool identifier (e.g., "fs.read")
- `arguments` (object, optional): Tool-specific arguments

**Example:**
```json
{
  "jsonrpc": "2.0",
  "id": "1",
  "method": "tools/call",
  "params": {
    "name": "db.select",
    "arguments": {
      "sql": "SELECT * FROM users LIMIT 5",
      "params": []
    }
  }
}
```

### Response Formats

**Success (Result):**
```json
{
  "jsonrpc": "2.0",
  "id": "1",
  "result": {
    "content": [
      {
        "type": "text",
        "text": "Tool output here"
      }
    ]
  }
}
```

**Error:**
```json
{
  "jsonrpc": "2.0",
  "id": "1",
  "error": {
    "code": -32000,
    "message": "Error description",
    "data": {
      "request_id": "abc123"
    }
  }
}
```

### Error Codes

| Code | Meaning | Example |
|------|---------|---------|
| -32700 | Parse error | Invalid JSON |
| -32600 | Invalid Request | Missing required fields |
| -32601 | Method not found | Unknown method name |
| -32602 | Invalid params | Wrong argument types |
| -32603 | Internal error | Server exception |
| -32000 | Tool error | Tool execution failed |

---

## Error Handling

### Exception Classes

**Location:** `server_v3.php` lines 112-138

```php
class McpException extends RuntimeException {
    private int $status;
    private array $details;

    public function __construct(
        string $message,
        string $code = 'UNKNOWN',
        int $status = 500,
        array $details = []
    );
}

class InvalidRequestException extends RuntimeException {}
class UnauthorizedException extends RuntimeException {}
```

### Error Flow

```
1. Request arrives
   ↓
2. Authentication check (enforce_api_key)
   → UnauthorizedException (401) if invalid
   ↓
3. JSON-RPC parsing
   → Parse error (-32700) if invalid JSON
   → Invalid Request (-32600) if missing fields
   ↓
4. Method routing
   → Method not found (-32601) if unknown method
   ↓
5. Tool execution
   → Tool error (-32000) if tool fails
   → Internal error (-32603) for unexpected exceptions
   ↓
6. Response formatting
```

### Error Response Function

**Function:** `respond_error()`
**Location:** `server_v3.php` lines 165-180

```php
function respond_error(
    $id,
    int $code,
    string $message,
    ?string $rid = null
): void {
    $err = [
        'jsonrpc' => '2.0',
        'id' => $id,
        'error' => [
            'code' => $code,
            'message' => $message
        ]
    ];
    if ($rid) {
        $err['error']['data'] = ['request_id' => $rid];
    }
    respond($err, $code === -32000 ? 500 : 400);
}
```

---

## Configuration

### Environment Variables

**File:** `.env`

```ini
# MCP Server Configuration
MCP_API_KEY=                  # Optional API key (empty = allow all)

# Database (required for tool execution)
DB_HOST=127.0.0.1
DB_NAME=hdgwrzntwa
DB_USER=hdgwrzntwa
DB_PASS=<secret>

# AI Providers (for chat tools)
OPENAI_API_KEY=sk-...
ANTHROPIC_API_KEY=sk-...
```

### Loading Configuration

**Automatic .env Loading:**

The server uses `mcp_tools_turbo.php`'s `envv()` function which checks:
1. `getenv()` - System environment
2. `$_ENV` - PHP environment array
3. Default value (if provided)

**Example:**
```php
$apiKey = envv('MCP_API_KEY', ''); // Empty string if not set
```

---

## Integration Examples

### VS Code Copilot Agent Mode

**File:** `.vscode/mcp.json`

```json
{
  "servers": {
    "ecigdis-tools": {
      "type": "http",
      "url": "https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=rpc",
      "registry": "https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=meta",
      "headers": {
        "Authorization": "Bearer ${COPILOT_MCP_ECIGDIS_TOKEN}"
      }
    }
  }
}
```

**Environment Variable:**
```bash
export COPILOT_MCP_ECIGDIS_TOKEN="your-api-key-here"
```

### Claude Desktop

**File:** `~/Library/Application Support/Claude/claude_desktop_config.json`

```json
{
  "mcpServers": {
    "ecigdis": {
      "command": "curl",
      "args": [
        "-X", "POST",
        "-H", "Content-Type: application/json",
        "-H", "Authorization: Bearer YOUR_API_KEY",
        "https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=rpc"
      ]
    }
  }
}
```

### cURL Examples

**Get Tool Catalog:**
```bash
curl -sS "https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=meta" | jq
```

**Execute Tool (without auth):**
```bash
curl -X POST "https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=rpc" \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "id": "1",
    "method": "tools/call",
    "params": {
      "name": "fs.list",
      "arguments": {"path": "assets"}
    }
  }' | jq
```

**Execute Tool (with Bearer token):**
```bash
curl -X POST "https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=rpc" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer your-api-key" \
  -d '{
    "jsonrpc": "2.0",
    "id": "2",
    "method": "tools/call",
    "params": {
      "name": "db.select",
      "arguments": {
        "sql": "SELECT COUNT(*) as total FROM ai_tool_calls"
      }
    }
  }' | jq
```

---

## Troubleshooting

### Common Issues

**1. "Invalid or missing API key"**
```
Cause: MCP_API_KEY is set but request has no Authorization header
Fix: Add header or set MCP_API_KEY='' for development
```

**2. "TOOL_ERROR: FILE_NOT_FOUND"**
```
Cause: Path doesn't exist or is outside allowed directory
Fix: Use secure_path() validation and check file exists
```

**3. "Parse error: Invalid JSON"**
```
Cause: Malformed JSON in request body
Fix: Validate JSON before sending (use jq or json_encode)
```

**4. "Method not found"**
```
Cause: Using wrong method name (must be "tools/call")
Fix: Use exact method name "tools/call"
```

### Debug Mode

**Enable Verbose Errors:**
```php
// In server_v3.php, add at top:
ini_set('display_errors', '1');
error_reporting(E_ALL);
```

**Check Logs:**
```bash
tail -100 /home/master/applications/hdgwrzntwa/logs/apache_*.error.log
```

---

**Document Version:** 1.0.0
**Last Updated:** November 2, 2025
**Related Docs:** 01_SYSTEM_OVERVIEW.md, 05_TOOLS_REFERENCE.md
