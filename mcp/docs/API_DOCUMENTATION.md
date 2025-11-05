# 🚀 MCP API DOCUMENTATION - COMPLETE REFERENCE

**Version:** 3.0.0
**Base URL:** `https://gpt.ecigdis.co.nz/mcp/`
**Authentication:** X-API-Key header or Authorization Bearer
**API Key:** `31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35`

---

## 📋 TABLE OF CONTENTS

1. [Server Endpoints](#server-endpoints)
2. [Conversation API](#conversation-api)
3. [Database API](#database-api)
4. [Semantic Search API](#semantic-search-api)
5. [Ultra Scanner API](#ultra-scanner-api)
6. [Knowledge Base API](#knowledge-base-api)
7. [File Operations API](#file-operations-api)
8. [Logs API](#logs-api)
9. [AI Agent API](#ai-agent-api)
10. [Response Format](#response-format)

---

## 🌐 SERVER ENDPOINTS

### 1. Health Check

**Endpoint:** `GET /server_v3.php?action=health`

**Description:** Check if MCP server is running and operational.

**Request:**
```bash
curl -X GET "https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=health"
```

**Response:**
```json
{
  "ok": true,
  "name": "Ecigdis MCP",
  "version": "3.0.0",
  "time": "2025-11-05T09:30:00+13:00",
  "php": "8.2.0",
  "host": "hdgwrzntwa",
  "request_id": "req_20251105_093000_a1b2c3d4"
}
```

---

### 2. Meta/Manifest

**Endpoint:** `GET /server_v3.php?action=meta`

**Description:** Get full list of available tools and their schemas.

**Request:**
```bash
curl -X GET "https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=meta"
```

**Response:**
```json
{
  "name": "IntelligenceHub MCP v3.0.0",
  "version": "3.0.0",
  "tools": [
    {
      "name": "ai_agent.query",
      "description": "Query GPT-5 AI Agent with RAG",
      "parameters": { ... }
    },
    ...50+ tools
  ]
}
```

---

## 💬 CONVERSATION API

### 1. Save/Update Conversation

**Endpoint:** `POST /api/conversation-save.php`

**Description:** Save or update a conversation with messages, topics, and tracking.

**Headers:**
```
Content-Type: application/json
X-API-Key: 31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35
```

**Request Body:**
```json
{
  "session_id": "mcp-workspace-20251105-abc123",
  "platform": "github_copilot",
  "user_identifier": "pearce.stephens@ecigdis.co.nz",
  "conversation_title": "Database Schema Migration",
  "conversation_context": "Discussion about intelligence table foreign keys and CASCADE DELETE",
  "status": "active",
  "org_id": 1,
  "unit_id": 1,
  "project_id": 1,
  "bot_id": 101,
  "server_id": "hdgwrzntwa",
  "source": "github_copilot",
  "messages": [
    {
      "role": "user",
      "content": "Can you add CASCADE DELETE to intelligence tables?",
      "tokens": 15
    },
    {
      "role": "assistant",
      "content": "Yes! I'll add CASCADE DELETE constraints...",
      "tokens": 50,
      "tool_calls": [
        {"tool": "db.query", "duration_ms": 250}
      ]
    }
  ],
  "topics": [
    {"topic": "database", "confidence": 0.95},
    {"topic": "foreign_keys", "confidence": 0.85},
    {"topic": "cascade_delete", "confidence": 0.90}
  ],
  "metadata": {
    "workspace_root": "/home/project",
    "current_file": "schema.sql"
  }
}
```

**cURL Example:**
```bash
curl -X POST "https://gpt.ecigdis.co.nz/mcp/api/conversation-save.php" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: 31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35" \
  -d '{
    "session_id": "test-session-123",
    "platform": "github_copilot",
    "conversation_title": "Test Conversation",
    "messages": [
      {"role": "user", "content": "Hello", "tokens": 5}
    ]
  }'
```

**Response (Success):**
```json
{
  "status": "success",
  "data": {
    "conversation_id": 42,
    "session_id": "mcp-workspace-20251105-abc123",
    "action": "created",
    "stats": {
      "messages_saved": 2,
      "topics_saved": 3,
      "total_tokens": 65
    },
    "tracking": {
      "org_id": 1,
      "unit_id": 1,
      "project_id": 1,
      "bot_id": 101,
      "server_id": "hdgwrzntwa",
      "source": "github_copilot"
    }
  },
  "request_id": "req_20251105_093000_a1b2c3d4",
  "timestamp": "2025-11-05T09:30:00+13:00"
}
```

**Response (Error):**
```json
{
  "status": "error",
  "error": {
    "code": "INVALID_INPUT",
    "message": "session_id is required"
  },
  "request_id": "req_20251105_093000_a1b2c3d4",
  "timestamp": "2025-11-05T09:30:00+13:00"
}
```

---

### 2. Get Conversation

**Endpoint:** `POST /api/conversations.php`

**Description:** Retrieve conversation(s) by session_id or list recent conversations.

**Request Body:**
```json
{
  "session_key": "mcp-workspace-20251105-abc123",
  "platform": "github_copilot",
  "limit": 10
}
```

**cURL Example:**
```bash
curl -X POST "https://gpt.ecigdis.co.nz/mcp/api/conversations.php" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: 31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35" \
  -d '{"session_key": "test-session-123", "limit": 5}'
```

**Response:**
```json
{
  "status": "success",
  "data": {
    "conversations": [
      {
        "id": 42,
        "session_id": "mcp-workspace-20251105-abc123",
        "platform": "github_copilot",
        "org_id": 1,
        "unit_id": 1,
        "project_id": 1,
        "source": "github_copilot",
        "status": "active",
        "created_at": "2025-11-05 09:00:00",
        "updated_at": "2025-11-05 09:30:00"
      }
    ]
  },
  "request_id": "req_20251105_093000_xyz",
  "timestamp": "2025-11-05T09:30:00+13:00"
}
```

---

## 🗄️ DATABASE API

### 1. Execute Query

**Tool:** `db.query`

**Description:** Execute SELECT queries on hdgwrzntwa database.

**JSON-RPC Request:**
```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "tools/call",
  "params": {
    "name": "db.query",
    "arguments": {
      "sql": "SELECT * FROM intelligence_files LIMIT 5",
      "params": []
    }
  }
}
```

**cURL Example:**
```bash
curl -X POST "https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=rpc" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer 31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35" \
  -d '{
    "jsonrpc": "2.0",
    "id": 1,
    "method": "tools/call",
    "params": {
      "name": "db.query",
      "arguments": {
        "sql": "SELECT COUNT(*) as total FROM intelligence_files"
      }
    }
  }'
```

**Response:**
```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "result": {
    "status": "ok",
    "data": {
      "rows": [
        {"total": 8645}
      ],
      "count": 1,
      "query_time_ms": 5
    }
  }
}
```

---

### 2. Get Schema

**Tool:** `db.schema`

**Description:** Get table schema information.

**JSON-RPC Request:**
```json
{
  "jsonrpc": "2.0",
  "id": 2,
  "method": "tools/call",
  "params": {
    "name": "db.schema",
    "arguments": {
      "table": "intelligence_files"
    }
  }
}
```

**Response:**
```json
{
  "jsonrpc": "2.0",
  "id": 2,
  "result": {
    "status": "ok",
    "data": {
      "table": "intelligence_files",
      "columns": [
        {
          "name": "file_id",
          "type": "bigint(20)",
          "null": "NO",
          "key": "PRI"
        },
        ...
      ],
      "indexes": [...],
      "foreign_keys": [...]
    }
  }
}
```

---

## 🔍 SEMANTIC SEARCH API

### 1. Search Indexed Files

**Tool:** `semantic_search`

**Description:** Semantic search across 8,645 indexed files.

**JSON-RPC Request:**
```json
{
  "jsonrpc": "2.0",
  "id": 3,
  "method": "tools/call",
  "params": {
    "name": "semantic_search",
    "arguments": {
      "query": "CASCADE DELETE foreign keys",
      "limit": 10
    }
  }
}
```

**cURL Example:**
```bash
curl -X POST "https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=rpc" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer 31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35" \
  -d '{
    "jsonrpc": "2.0",
    "id": 3,
    "method": "tools/call",
    "params": {
      "name": "semantic_search",
      "arguments": {
        "query": "database migration",
        "limit": 5
      }
    }
  }'
```

**Response:**
```json
{
  "jsonrpc": "2.0",
  "id": 3,
  "result": {
    "status": "ok",
    "data": {
      "results": [
        {
          "file_path": "/mcp/sql/create_extended_intelligence_tables.sql",
          "relevance_score": 0.95,
          "content_preview": "CREATE TABLE intelligence_functions...",
          "file_type": "sql",
          "size_bytes": 12580
        },
        ...
      ],
      "total_results": 42,
      "query": "database migration",
      "search_time_ms": 15
    }
  }
}
```

---

## 🔬 ULTRA SCANNER API

### 1. Scan Directory

**Tool:** `ultra_scanner.scan_directory`

**Description:** Scan directory and populate ALL 18 intelligence tables.

**JSON-RPC Request:**
```json
{
  "jsonrpc": "2.0",
  "id": 4,
  "method": "tools/call",
  "params": {
    "name": "ultra_scanner.scan_directory",
    "arguments": {
      "directory": "/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html",
      "recursive": true,
      "extensions": ["php", "js", "sql"]
    }
  }
}
```

**Response:**
```json
{
  "jsonrpc": "2.0",
  "id": 4,
  "result": {
    "status": "ok",
    "data": {
      "files_scanned": 8645,
      "functions_extracted": 1523,
      "classes_found": 342,
      "methods_found": 2106,
      "dependencies_mapped": 450,
      "patterns_detected": 89,
      "kb_categories_assigned": 125,
      "intelligence_tables_populated": 18,
      "scan_time_seconds": 45.3
    }
  }
}
```

---

## 📚 KNOWLEDGE BASE API

### 1. Search KB

**Tool:** `kb.search`

**Description:** Search knowledge base documents.

**JSON-RPC Request:**
```json
{
  "jsonrpc": "2.0",
  "id": 5,
  "method": "tools/call",
  "params": {
    "name": "kb.search",
    "arguments": {
      "query": "foreign key constraints",
      "type": "code",
      "limit": 5
    }
  }
}
```

**Response:**
```json
{
  "jsonrpc": "2.0",
  "id": 5,
  "result": {
    "status": "ok",
    "data": {
      "documents": [
        {
          "doc_id": 15,
          "title": "CASCADE DELETE Implementation Guide",
          "content": "Step-by-step guide to adding CASCADE DELETE...",
          "type": "code",
          "relevance": 0.92,
          "created_at": "2025-11-05 08:00:00"
        },
        ...
      ],
      "total": 8,
      "query": "foreign key constraints"
    }
  }
}
```

---

## 📁 FILE OPERATIONS API

### 1. Read File

**Tool:** `fs.read`

**Description:** Read file contents.

**JSON-RPC Request:**
```json
{
  "jsonrpc": "2.0",
  "id": 6,
  "method": "tools/call",
  "params": {
    "name": "fs.read",
    "arguments": {
      "path": "/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/mcp/.env",
      "start_line": 1,
      "end_line": 50
    }
  }
}
```

---

## 📊 LOGS API

### 1. Tail Logs

**Tool:** `logs.tail`

**Description:** Get last N lines from log file.

**JSON-RPC Request:**
```json
{
  "jsonrpc": "2.0",
  "id": 7,
  "method": "tools/call",
  "params": {
    "name": "logs.tail",
    "arguments": {
      "file": "php-error",
      "lines": 100
    }
  }
}
```

---

## 🤖 AI AGENT API

### 1. Query AI Agent

**Tool:** `ai_agent.query`

**Description:** Query GPT-5 AI Agent with RAG and streaming.

**JSON-RPC Request:**
```json
{
  "jsonrpc": "2.0",
  "id": 8,
  "method": "tools/call",
  "params": {
    "name": "ai_agent.query",
    "arguments": {
      "message": "Explain how CASCADE DELETE works in our intelligence tables",
      "conversation_id": "mcp-workspace-20251105-abc123",
      "stream": true,
      "include_context": true
    }
  }
}
```

**Response (Streaming):**
```json
{
  "jsonrpc": "2.0",
  "id": 8,
  "result": {
    "status": "ok",
    "data": {
      "message": "CASCADE DELETE in your intelligence tables works as follows...",
      "conversation_id": "mcp-workspace-20251105-abc123",
      "tokens_used": 250,
      "context_files_used": 5,
      "response_time_ms": 1500
    }
  }
}
```

---

## 📦 RESPONSE FORMAT

### Success Response

**Standard Envelope:**
```json
{
  "status": "success",
  "data": { ... },
  "request_id": "req_20251105_093000_a1b2c3d4",
  "timestamp": "2025-11-05T09:30:00+13:00"
}
```

### Error Response

**Standard Envelope:**
```json
{
  "status": "error",
  "error": {
    "code": "ERROR_CODE",
    "message": "Human readable error message"
  },
  "request_id": "req_20251105_093000_a1b2c3d4",
  "timestamp": "2025-11-05T09:30:00+13:00"
}
```

### Error Codes

| Code | HTTP Status | Description |
|------|-------------|-------------|
| `INVALID_INPUT` | 422 | Missing or invalid input parameters |
| `UNAUTHORIZED` | 401 | Missing or invalid API key |
| `FORBIDDEN` | 403 | API key valid but access denied |
| `NOT_FOUND` | 404 | Resource not found |
| `METHOD_NOT_ALLOWED` | 405 | HTTP method not supported |
| `INTERNAL_ERROR` | 500 | Server error |
| `DATABASE_ERROR` | 500 | Database query failed |
| `CONVERSATION_SAVE_FAILURE` | 500 | Failed to save conversation |

---

## 🔑 AUTHENTICATION

All API requests require authentication via one of:

### Option 1: X-API-Key Header
```bash
curl -H "X-API-Key: 31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35" ...
```

### Option 2: Authorization Bearer
```bash
curl -H "Authorization: Bearer 31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35" ...
```

---

## 📝 NOTES

- All timestamps are in **Pacific/Auckland** timezone (NZDT/NZST)
- Request IDs follow format: `req_YYYYMMDD_HHMMSS_<random>`
- Session IDs follow format: `mcp-<workspace>-<date>-<random>`
- Token estimates use ~4 characters = 1 token approximation
- All JSON-RPC requests use `jsonrpc: "2.0"`
- Streaming responses use chunked transfer encoding
- Database queries have 5-second timeout
- File operations respect workspace boundaries
- Auto-logging can be disabled via `X-Auto-Log: false` header

---

**🚀 IntelligenceHub MCP v3.0.0 - Full API Documentation**
