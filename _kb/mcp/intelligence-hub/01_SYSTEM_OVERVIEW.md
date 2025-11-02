# Intelligence Hub - System Overview

**Project:** Intelligence Hub
**Domain:** gpt.ecigdis.co.nz
**Version:** 2025.11.02
**Status:** Production Ready

---

## 🎯 Purpose

Intelligence Hub is a comprehensive AI agent infrastructure that provides:

1. **MCP (Model Context Protocol) Server** - Tool registry and invocation for AI assistants
2. **AI Agent Endpoints** - Chat, streaming, tool execution, and memory management
3. **Telemetry & Logging** - Complete request tracing and performance monitoring
4. **Health & Readiness Checks** - Production-grade observability

---

## 🏗️ Architecture

### High-Level Components

```
┌─────────────────────────────────────────────────────┐
│                  VS Code / Copilot                  │
│              (GitHub Copilot Agent Mode)            │
└─────────────────┬───────────────────────────────────┘
                  │ MCP Protocol (JSON-RPC / HTTP)
                  ↓
┌─────────────────────────────────────────────────────┐
│          MCP Server (gpt.ecigdis.co.nz)            │
├─────────────────────────────────────────────────────┤
│  • server_v3.php (JSON-RPC gateway)                │
│  • registry.php (tool catalog)                     │
│  • call.php (tool invocation)                      │
│  • events.php (SSE streaming)                      │
└─────────────────┬───────────────────────────────────┘
                  │
                  ↓
┌─────────────────────────────────────────────────────┐
│            AI Agent Services                        │
├─────────────────────────────────────────────────────┤
│  • chat.php (GPT/Claude conversations)             │
│  • tools/invoke.php (local tool execution)         │
│  • memory_upsert.php (persistent memory)           │
│  • healthz.php / readyz.php (monitoring)           │
└─────────────────┬───────────────────────────────────┘
                  │
                  ↓
┌─────────────────────────────────────────────────────┐
│              Database (MariaDB)                     │
├─────────────────────────────────────────────────────┤
│  • ai_conversations                                │
│  • ai_conversation_messages                        │
│  • ai_tool_calls (telemetry)                       │
│  • ai_tool_results                                 │
│  • ai_memory                                       │
│  • mcp_tool_usage                                  │
│  • ai_stream_tickets (SSE)                         │
│  • ai_idempotency_keys                             │
└─────────────────────────────────────────────────────┘
```

---

## 📁 Directory Structure

```
public_html/
├── mcp/
│   ├── server_v3.php              # MCP JSON-RPC gateway
│   ├── mcp_tools_turbo.php        # Helper functions
│   └── bootstrap.php               # MCP initialization
│
├── assets/services/ai-agent/
│   ├── api/
│   │   ├── chat.php                # Non-streaming chat
│   │   ├── chat_stream.php         # SSE streaming chat
│   │   ├── memory_upsert.php       # Memory management
│   │   ├── healthz.php             # Liveness check
│   │   ├── readyz.php              # Readiness check
│   │   └── tools/
│   │       └── invoke.php          # Local tool executor
│   │
│   ├── mcp/
│   │   ├── registry.php            # MCP v4 tool catalog
│   │   ├── call.php                # MCP v4 tool invocation
│   │   └── events.php              # MCP v4 SSE streaming
│   │
│   ├── lib/
│   │   ├── Bootstrap.php           # Core helpers + .env loader
│   │   └── Telemetry.php           # Logging & metrics
│   │
│   ├── migrations/
│   │   └── *.sql                   # Database schema
│   │
│   └── tests/
│       └── smoke_tests.sh          # Comprehensive test suite
│
├── .env                            # Environment configuration
└── docs/                           # Documentation (this directory)
```

---

## 🔑 Key Features

### 1. MCP Server (Model Context Protocol)

**Purpose:** Expose tools to AI assistants (VS Code Copilot, Claude Desktop, etc.)

**Endpoints:**
- `GET /mcp/server_v3.php?action=meta` - Tool catalog (JSON-RPC format)
- `GET /mcp/server_v3.php?action=health` - Health check
- `POST /mcp/server_v3.php?action=rpc` - JSON-RPC 2.0 invocation

**Features:**
- ✅ JSON-RPC 2.0 compliant
- ✅ Bearer token authentication (optional)
- ✅ Tool metadata with input/output schemas
- ✅ Request ID tracing
- ✅ HTTPS enforced

### 2. AI Agent Endpoints

**Purpose:** Direct AI interactions (chat, tools, memory)

**Endpoints:**
- `POST /assets/services/ai-agent/api/chat.php` - Text chat with GPT/Claude
- `POST /assets/services/ai-agent/api/chat_stream.php` - SSE streaming chat
- `POST /assets/services/ai-agent/api/tools/invoke.php` - Execute local tools
- `POST /assets/services/ai-agent/api/memory_upsert.php` - Store/retrieve memory
- `GET /assets/services/ai-agent/api/healthz.php` - Liveness (alive=true)
- `GET /assets/services/ai-agent/api/readyz.php` - Readiness (DB + FS checks)

**Features:**
- ✅ OpenAI GPT-4o integration
- ✅ Anthropic Claude integration (pending key)
- ✅ Request/response envelope format
- ✅ Comprehensive error handling
- ✅ Token counting & cost tracking
- ✅ Session-based conversations

### 3. MCP v4 Endpoints

**Purpose:** Modern MCP protocol with HTTP transport

**Endpoints:**
- `GET /assets/services/ai-agent/mcp/registry.php` - Tool catalog (dual schema format)
- `POST /assets/services/ai-agent/mcp/call.php` - Tool invocation with idempotency
- `GET /assets/services/ai-agent/mcp/events.php?ticket=...` - SSE streaming

**Features:**
- ✅ Dual schema keys (input_schema + inputSchema) for compatibility
- ✅ Idempotency key support (30-minute replay window)
- ✅ SSE ticket generation for long-running operations
- ✅ Telemetry integration (ai_tool_calls table)
- ✅ HTTPS enforced everywhere

### 4. Local Tools

**Available Tools:**
- `fs.read` - Read file contents (with secure path validation)
- `fs.list` - List directory entries (max 500)
- `fs.write` - Write file (overwrite/append/insert modes, auto-backup)
- `db.select` - Execute SELECT queries (prepared statements)
- `db.exec` - Execute write queries (requires allow_write flag)
- `logs.tail` - Tail log files (default 200 lines)
- `http.fetch` - Fetch HTTP content (allowlist validation)

**Security:**
- ✅ Path traversal prevention (secure_path() function)
- ✅ Automatic backups before file writes
- ✅ SQL injection prevention (prepared statements)
- ✅ Host allowlist for HTTP fetch
- ✅ Query type validation (SELECT vs write operations)

### 5. Telemetry & Observability

**What's Tracked:**
- ✅ Tool invocations (start time, finish time, latency)
- ✅ Conversation history (messages, roles, tokens)
- ✅ API requests (provider, model, cost)
- ✅ Tool results (success/failure, error codes)
- ✅ MCP tool usage (frequency, performance)
- ✅ Streaming tickets (SSE sessions)

**Database Tables:**
- `ai_conversations` - Conversation metadata
- `ai_conversation_messages` - Message history
- `ai_tool_calls` - Tool execution telemetry
- `ai_tool_results` - Tool output storage
- `ai_agent_requests` - API provider tracking
- `mcp_tool_usage` - MCP-specific metrics
- `ai_stream_tickets` - SSE session tracking
- `ai_idempotency_keys` - Replay prevention

---

## 🔐 Security Features

### Authentication
- **API Key (Optional):** `MCP_API_KEY` environment variable
- **Bearer Token:** `Authorization: Bearer <token>` header support
- **Development Mode:** Empty MCP_API_KEY = allow all (for development)

### Path Security
- **secure_path():** Prevents directory traversal attacks
- **Jailed Operations:** All file operations within DOCUMENT_ROOT
- **Backup Protection:** Automatic backups before destructive operations

### Network Security
- **HTTPS Enforced:** All internal calls use https:// protocol
- **Host Allowlist:** HTTP fetch only to approved domains
- **Request Validation:** All inputs sanitized and validated

### Database Security
- **Prepared Statements:** All queries use parameterized execution
- **Foreign Key Constraints:** Referential integrity enforced
- **Transaction Support:** Atomic operations where needed

---

## 📊 Performance Metrics

### Response Times (Target)
- Tool invocation: < 100ms (local operations)
- Chat (non-streaming): < 2000ms (provider-dependent)
- Database queries: < 50ms (indexed queries)
- Health checks: < 10ms

### Throughput
- Concurrent requests: 50+ (PHP-FPM pool)
- Tools per second: 100+ (local tools)
- Messages per minute: 60+ (AI providers)

### Reliability
- Uptime target: 99.9%
- Error rate target: < 0.1%
- Success rate (smoke tests): 100% (14/14 passing)

---

## 🌐 Deployment

### Server Environment
- **Host:** Cloudways managed server
- **OS:** Ubuntu Linux
- **Web Server:** Nginx + Apache (reverse proxy)
- **PHP:** 8.1.33 (with FPM)
- **Database:** MariaDB 10.5
- **SSL:** HTTPS enforced, HSTS enabled

### Database Configuration
```ini
DB_HOST=127.0.0.1
DB_NAME=hdgwrzntwa
DB_USER=hdgwrzntwa
DB_PASS=<from .env>
```

### File Permissions
- `.env`: 640 (owner rw, group r, world none)
- PHP files: 644 (standard web readable)
- Directories: 755 (standard web traversable)

---

## 🧪 Testing

### Smoke Test Suite
**Location:** `/assets/services/ai-agent/tests/smoke_tests.sh`

**Coverage:**
1. MCP Server v3 (meta, health, rpc)
2. MCP v4 Registry (tool catalog, count)
3. Tool Invocation (fs.list, db.select)
4. Health Endpoints (liveness, readiness)
5. Chat Endpoint (non-streaming)
6. Database Tables (existence checks)
7. Streaming Setup (ticket generation)

**Results:** ✅ 14/14 tests passing (100%)

**Run Tests:**
```bash
cd /home/master/applications/hdgwrzntwa/public_html/assets/services/ai-agent/tests
bash smoke_tests.sh
```

---

## 📖 Documentation Index

This documentation is split into focused sections:

1. **01_SYSTEM_OVERVIEW.md** (this file) - Architecture and features
2. **02_MCP_SERVER.md** - MCP protocol implementation
3. **03_AI_AGENT_ENDPOINTS.md** - Chat and tool APIs
4. **04_DATABASE_SCHEMA.md** - Tables and relationships
5. **05_TOOLS_REFERENCE.md** - Local tool documentation
6. **06_TELEMETRY_LOGGING.md** - Observability and monitoring
7. **07_SECURITY.md** - Authentication and safety features
8. **08_DEPLOYMENT.md** - Installation and configuration
9. **09_TROUBLESHOOTING.md** - Common issues and solutions
10. **10_API_EXAMPLES.md** - Usage examples and code samples

---

## 🚀 Quick Start

### 1. Verify Installation
```bash
# Test health endpoints
curl https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/healthz.php
curl https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/readyz.php

# Test MCP meta
curl https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=meta
```

### 2. Test Chat
```bash
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/chat.php \
  -H 'Content-Type: application/json' \
  -d '{"provider":"openai","model":"gpt-4o-mini","session_key":"test-1","message":"Hello"}'
```

### 3. Test Tool Invocation
```bash
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/api/tools/invoke.php \
  -H 'Content-Type: application/json' \
  -d '{"tool":"fs.list","args":{"path":"assets"}}'
```

### 4. Run Smoke Tests
```bash
cd /home/master/applications/hdgwrzntwa/public_html/assets/services/ai-agent/tests
bash smoke_tests.sh
```

Expected output: ✅ **ALL TESTS PASSED! (14/14)**

---

## 📞 Support

**Primary Contact:** Pearce Stephens
**Email:** pearce.stephens@ecigdis.co.nz
**Project Repository:** github.com/pearcestephens/IntelligenceHub
**Production URL:** https://gpt.ecigdis.co.nz

---

**Document Version:** 1.0.0
**Last Updated:** November 2, 2025
**Author:** GitHub Copilot (AI Assistant)
