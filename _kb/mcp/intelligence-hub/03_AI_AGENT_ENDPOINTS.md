# AI Agent Endpoints Documentation

**Component:** AI Agent API Services
**Location:** `/assets/services/ai-agent/api/`
**Protocol:** HTTP/HTTPS with JSON
**Status:** Production Ready ✅

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Chat Endpoints](#chat-endpoints)
3. [Tool Invocation](#tool-invocation)
4. [Memory Management](#memory-management)
5. [Health Checks](#health-checks)
6. [Response Envelope Format](#response-envelope-format)
7. [Error Handling](#error-handling)
8. [Examples](#examples)

---

## Overview

The AI Agent endpoints provide direct access to AI capabilities:

- **Chat:** Conversational AI with GPT/Claude
- **Streaming:** Real-time SSE (Server-Sent Events) responses
- **Tools:** Execute local operations (files, database, HTTP)
- **Memory:** Persistent conversation context
- **Health:** Liveness and readiness monitoring

---

## Chat Endpoints

### 1. Non-Streaming Chat

**Endpoint:** `POST /assets/services/ai-agent/api/chat.php`

**Purpose:** Single-response conversations with AI providers

**Request Body:**
```json
{
  "provider": "openai",           // "openai" or "anthropic"
  "model": "gpt-4o-mini",         // Model identifier
  "session_key": "user-123",      // Session identifier
  "message": "Hello, how are you?", // User message
  "system_prompt": "...",         // Optional system prompt
  "temperature": 0.7,             // Optional (0.0-2.0)
  "max_tokens": 1000             // Optional limit
}
```

**Response (Success):**
```json
{
  "success": true,
  "request_id": "48e8429615a4d75aa6d5dfd568291b5e",
  "data": {
    "session_key": "user-123",
    "conversation_id": 17,
    "user_message_id": 22,
    "assistant_message_id": 23,
    "provider": "openai",
    "model": "gpt-4o-mini",
    "tokens": {
      "in": 27,
      "out": 10,
      "total": 37
    },
    "cost_nzd_cents": 0,
    "latency_ms": 1115,
    "content": "Hi there! How can I assist you today?"
  },
  "meta": {
    "ts": "2025-11-02T23:15:18+13:00",
    "host": "gpt.ecigdis.co.nz",
    "ip": "45.32.241.246",
    "version": "2025.11.02"
  }
}
```

**Response (Error):**
```json
{
  "success": false,
  "request_id": "a472deadc91a8e7f03253970615f3e5a",
  "error": {
    "code": "CHAT_FAILURE",
    "message": "OPENAI_API_KEY not configured",
    "detail": {
      "trace": "#0 {main}"
    }
  },
  "meta": {
    "ts": "2025-11-02T23:15:28+13:00",
    "host": "gpt.ecigdis.co.nz",
    "ip": "45.32.241.246",
    "version": "2025.11.02"
  }
}
```

**Supported Providers:**

| Provider | Models | Environment Variable |
|----------|--------|---------------------|
| OpenAI   | gpt-4o, gpt-4o-mini, gpt-4-turbo | OPENAI_API_KEY |
| Anthropic | claude-3-5-sonnet-20241022 | ANTHROPIC_API_KEY |

**Features:**
- ✅ Conversation history (linked by session_key)
- ✅ Token counting and cost tracking
- ✅ Latency measurement
- ✅ System prompt support
- ✅ Temperature control
- ✅ Max tokens limit

---

### 2. Streaming Chat

**Endpoint:** `POST /assets/services/ai-agent/api/chat_stream.php`

**Purpose:** Real-time streaming responses via SSE

**Request Body:**
```json
{
  "provider": "openai",
  "model": "gpt-4o-mini",
  "session_key": "user-123",
  "message": "Tell me a story",
  "stream": true
}
```

**Response Format (SSE):**
```
HTTP/1.1 200 OK
Content-Type: text/event-stream
Cache-Control: no-cache
Connection: keep-alive

data: {"type":"start","request_id":"abc123"}

data: {"type":"chunk","content":"Once"}

data: {"type":"chunk","content":" upon"}

data: {"type":"chunk","content":" a"}

data: {"type":"chunk","content":" time"}

data: {"type":"done","tokens":{"in":10,"out":50},"latency_ms":2500}
```

**Features:**
- ✅ Real-time token streaming
- ✅ Word-by-word delivery
- ✅ Connection keep-alive
- ✅ Graceful error handling
- ✅ Automatic retry logic (client-side)

---

## Tool Invocation

**Endpoint:** `POST /assets/services/ai-agent/api/tools/invoke.php`

**Purpose:** Execute local tools (file operations, database queries, HTTP requests)

**Request Body:**
```json
{
  "tool": "fs.read",           // Tool name
  "args": {                     // Tool-specific arguments
    "path": "assets/services/ai-agent/lib/Bootstrap.php"
  },
  "session_key": "user-123",    // Optional session tracking
  "conversation_id": 17,        // Optional conversation link
  "message_id": 22              // Optional message link
}
```

**Response (Success):**
```json
{
  "success": true,
  "request_id": "5d7fc38fc28184c4f1b115c3489f0b92",
  "data": {
    "tool": "fs.read",
    "result": {
      "path": "/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/assets/services/ai-agent/lib/Bootstrap.php",
      "bytes": 5248,
      "content": "<?php\ndeclare(strict_types=1);\n..."
    },
    "latency_ms": 5
  },
  "meta": {
    "ts": "2025-11-02T23:16:53+13:00",
    "host": "gpt.ecigdis.co.nz",
    "ip": "45.32.241.246",
    "version": "2025.11.02"
  }
}
```

**Response (Error):**
```json
{
  "success": false,
  "request_id": "a50b0e1bd009ce0ddb17a975d16b9723",
  "error": {
    "code": "TOOL_ERROR",
    "message": "FILE_NOT_FOUND: /invalid/path.php",
    "detail": {
      "trace": "#0 {main}"
    }
  },
  "meta": {
    "ts": "2025-11-02T23:18:34+13:00",
    "host": "gpt.ecigdis.co.nz",
    "ip": "45.32.241.246",
    "version": "2025.11.02"
  }
}
```

### Available Tools

| Tool | Purpose | Key Arguments |
|------|---------|--------------|
| fs.read | Read file contents | path |
| fs.list | List directory | path, max (default 500) |
| fs.write | Write file | path, content, mode |
| db.select | SELECT query | sql, params |
| db.exec | Write query | sql, params, allow_write |
| logs.tail | Tail log file | path, lines (default 200) |
| http.fetch | HTTP request | url |

**See 05_TOOLS_REFERENCE.md for detailed documentation**

---

## Memory Management

**Endpoint:** `POST /assets/services/ai-agent/api/memory_upsert.php`

**Purpose:** Store and retrieve persistent conversation context

**Request Body (Upsert):**
```json
{
  "scope": "session",           // "global", "project", "session", "user"
  "key": "user_preferences",    // Memory key
  "value": {                    // JSON value
    "theme": "dark",
    "language": "en-NZ"
  },
  "session_key": "user-123",    // Required for session scope
  "confidence": 90,             // Optional (0-100)
  "source": "user"              // Optional source tracking
}
```

**Response:**
```json
{
  "success": true,
  "request_id": "abc123",
  "data": {
    "action": "upserted",
    "memory_id": 42,
    "scope": "session",
    "key": "user_preferences"
  },
  "meta": {
    "ts": "2025-11-02T23:20:00+13:00",
    "host": "gpt.ecigdis.co.nz",
    "ip": "45.32.241.246",
    "version": "2025.11.02"
  }
}
```

**Request Body (Retrieve):**
```json
{
  "scope": "session",
  "key": "user_preferences",
  "session_key": "user-123"
}
```

**Response:**
```json
{
  "success": true,
  "request_id": "def456",
  "data": {
    "found": true,
    "value": {
      "theme": "dark",
      "language": "en-NZ"
    },
    "confidence": 90,
    "source": "user",
    "created_at": "2025-11-02T10:00:00+13:00",
    "updated_at": "2025-11-02T23:20:00+13:00"
  },
  "meta": {...}
}
```

### Memory Scopes

| Scope | Use Case | Required Context |
|-------|----------|-----------------|
| global | System-wide facts | None |
| project | Project-specific | project name |
| session | User session | session_key |
| user | User profile | user_id |

### Features
- ✅ JSON value storage (any structure)
- ✅ Confidence scoring (0-100)
- ✅ Source tracking (user/assistant/system)
- ✅ Automatic timestamps (created_at, updated_at)
- ✅ Unique constraint per scope+key
- ✅ Conversation linking (optional)

---

## Health Checks

### 1. Liveness Check

**Endpoint:** `GET /assets/services/ai-agent/api/healthz.php`

**Purpose:** Verify service is alive and database is accessible

**Response:**
```json
{
  "success": true,
  "request_id": "cf069240e7a5d537ee827db5b30244c8",
  "data": {
    "alive": true,
    "db": "ok"
  },
  "meta": {
    "ts": "2025-11-02T23:00:32+13:00",
    "host": "gpt.ecigdis.co.nz",
    "ip": "45.32.241.246",
    "version": "2025.11.02"
  }
}
```

**Features:**
- ✅ Database connectivity test (SELECT 1)
- ✅ Fast response (< 10ms target)
- ✅ No authentication required
- ✅ Suitable for load balancer health checks

**Use Cases:**
- Kubernetes/Docker health probe
- Monitoring system checks (Pingdom, UptimeRobot)
- Load balancer routing decisions

---

### 2. Readiness Check

**Endpoint:** `GET /assets/services/ai-agent/api/readyz.php`

**Purpose:** Verify service is ready to handle requests (deep check)

**Response:**
```json
{
  "success": true,
  "request_id": "xyz789",
  "data": {
    "ready": true,
    "db": "ok",
    "missing_tables": [],
    "fs": "ok",
    "doc_root": "/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html"
  },
  "meta": {
    "ts": "2025-11-02T23:00:45+13:00",
    "host": "gpt.ecigdis.co.nz",
    "ip": "45.32.241.246",
    "version": "2025.11.02"
  }
}
```

**Checks Performed:**
1. Database connectivity (SELECT 1)
2. Required tables exist:
   - ai_conversations
   - ai_conversation_messages
   - ai_agent_requests
   - ai_tool_calls
   - ai_tool_results
   - ai_memory
   - mcp_tool_usage
3. Filesystem writeable (creates+deletes test file)

**Features:**
- ✅ Comprehensive readiness validation
- ✅ Returns 200 if ready, 503 if not ready
- ✅ Lists missing tables (if any)
- ✅ Verifies filesystem write access
- ✅ No authentication required

**Use Cases:**
- Deployment readiness checks
- Pre-traffic validation
- Detailed system diagnostics

---

## Response Envelope Format

### Standard Envelope

All endpoints return a consistent JSON envelope:

```json
{
  "success": true|false,       // Operation result
  "request_id": "...",          // Unique request identifier
  "data": {...}|null,           // Success payload
  "error": {...}|null,          // Error payload (if success=false)
  "meta": {                     // Request metadata
    "ts": "ISO 8601 timestamp",
    "host": "hostname",
    "ip": "client IP",
    "version": "2025.11.02"
  }
}
```

### Success Envelope

```json
{
  "success": true,
  "request_id": "abc123",
  "data": {
    // Endpoint-specific success data
  },
  "meta": {
    "ts": "2025-11-02T23:00:00+13:00",
    "host": "gpt.ecigdis.co.nz",
    "ip": "45.32.241.246",
    "version": "2025.11.02"
  }
}
```

### Error Envelope

```json
{
  "success": false,
  "request_id": "def456",
  "error": {
    "code": "ERROR_CODE",
    "message": "Human-readable error message",
    "detail": {
      "trace": "Stack trace (if available)",
      "field": "Invalid field name (if applicable)"
    }
  },
  "meta": {
    "ts": "2025-11-02T23:00:00+13:00",
    "host": "gpt.ecigdis.co.nz",
    "ip": "45.32.241.246",
    "version": "2025.11.02"
  }
}
```

### HTTP Status Codes

| Status | Meaning | Used For |
|--------|---------|----------|
| 200 | OK | Successful operations |
| 400 | Bad Request | Invalid input |
| 401 | Unauthorized | Missing/invalid auth |
| 404 | Not Found | Resource doesn't exist |
| 405 | Method Not Allowed | Wrong HTTP method |
| 422 | Unprocessable Entity | Validation failed |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Unexpected errors |
| 503 | Service Unavailable | Not ready (readyz) |

---

## Error Handling

### Common Error Codes

| Code | HTTP Status | Meaning | Solution |
|------|------------|---------|----------|
| INVALID_INPUT | 422 | Missing/invalid required field | Check request body |
| METHOD_NOT_ALLOWED | 405 | Wrong HTTP method | Use POST (not GET) |
| CHAT_FAILURE | 500 | AI provider error | Check API keys |
| TOOL_ERROR | 500 | Tool execution failed | Check tool arguments |
| INVOKE_FAILURE | 500 | Tool invocation error | Check tool exists |
| UNKNOWN_TOOL | 500 | Tool not registered | Use valid tool name |
| FILE_NOT_FOUND | 500 | File doesn't exist | Check path |
| DIR_NOT_FOUND | 500 | Directory doesn't exist | Check path |
| ONLY_SELECT_ALLOWED | 500 | Invalid SQL type | Use SELECT only |
| SQL_REQUIRED | 500 | Missing SQL query | Provide sql argument |
| ALLOW_WRITE_REQUIRED | 500 | Write not allowed | Set allow_write=true |
| HOST_NOT_ALLOWED | 500 | HTTP fetch blocked | Add to allowlist |
| HEALTH_FAIL | 500 | Health check failed | Check database |
| READY_FAIL | 500 | Readiness check failed | Check tables/FS |

### Error Response Helper

**Function:** `envelope_error()`
**Location:** `lib/Bootstrap.php` lines 65-77

```php
function envelope_error(
    string $code,
    string $message,
    string $requestId,
    array $detail = [],
    int $status = 500
): void {
    response_json([
        'success' => false,
        'request_id' => $requestId,
        'error' => [
            'code' => $code,
            'message' => $message,
            'detail' => $detail
        ],
        'meta' => [
            'ts' => date('c'),
            'host' => $_SERVER['HTTP_HOST'] ?? 'cli',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'version' => AI_AGENT_VERSION
        ]
    ], $status);
}
```

---

## Examples

### Example 1: Chat with GPT

```bash
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/chat.php \
  -H 'Content-Type: application/json' \
  -d '{
    "provider": "openai",
    "model": "gpt-4o-mini",
    "session_key": "demo-session",
    "message": "What is the capital of New Zealand?"
  }' | jq '.data.content'
```

**Output:**
```
"The capital of New Zealand is Wellington."
```

---

### Example 2: List Files

```bash
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
  -H 'Content-Type: application/json' \
  -d '{
    "tool": "fs.list",
    "args": {
      "path": "assets/services/ai-agent",
      "max": 10
    }
  }' | jq '.data.result.entries[].name'
```

**Output:**
```
"api"
"mcp"
"lib"
"migrations"
"tests"
```

---

### Example 3: Database Query

```bash
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
  -H 'Content-Type: application/json' \
  -d '{
    "tool": "db.select",
    "args": {
      "sql": "SELECT COUNT(*) as total FROM ai_tool_calls WHERE status=?",
      "params": ["ok"]
    }
  }' | jq '.data.result.rows[0].total'
```

**Output:**
```
5
```

---

### Example 4: Store Memory

```bash
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/memory_upsert.php \
  -H 'Content-Type: application/json' \
  -d '{
    "scope": "session",
    "key": "user_name",
    "value": "Alice",
    "session_key": "demo-session",
    "confidence": 95
  }' | jq '.data.action'
```

**Output:**
```
"upserted"
```

---

### Example 5: Check Health

```bash
# Liveness
curl https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/healthz.php | jq '.data.alive'

# Readiness
curl https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/readyz.php | jq '.data.ready'
```

**Output:**
```
true
true
```

---

**Document Version:** 1.0.0
**Last Updated:** November 2, 2025
**Related Docs:** 01_SYSTEM_OVERVIEW.md, 05_TOOLS_REFERENCE.md, 06_TELEMETRY_LOGGING.md
