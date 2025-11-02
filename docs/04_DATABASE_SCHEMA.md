# Database Schema Documentation

**Component:** AI Agent Database Layer  
**Database:** hdgwrzntwa @ gpt.ecigdis.co.nz  
**Engine:** MySQL/MariaDB with InnoDB  
**Status:** Production Ready ✅  

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Table Relationships](#table-relationships)
3. [Core Tables](#core-tables)
4. [Schema Definitions](#schema-definitions)
5. [Foreign Keys](#foreign-keys)
6. [Indexes](#indexes)
7. [Migration History](#migration-history)

---

## Overview

The AI Agent database consists of **8 core tables** managing:

- Conversations and messages
- Tool execution tracking
- Memory storage
- Telemetry and observability
- Stream session management
- Idempotency guarantees

**Design Principles:**
- ✅ Foreign key constraints for referential integrity
- ✅ JSON columns for flexible data storage
- ✅ Enum types for controlled vocabularies
- ✅ Timestamp tracking (created_at, updated_at)
- ✅ Soft deletes where appropriate
- ✅ Indexes on query patterns

---

## Table Relationships

```
┌─────────────────────────┐
│  ai_conversations       │
│  - id (PK)              │
│  - session_key          │
└──────────┬──────────────┘
           │ 1:N
           │
┌──────────▼──────────────┐
│ ai_conversation_messages│
│  - id (PK)              │
│  - conversation_id (FK) │◄─┐
│  - role                 │  │
│  - content              │  │
└──────────┬──────────────┘  │
           │                 │
           │ 1:1             │
           │                 │
┌──────────▼──────────────┐  │
│ ai_agent_requests       │  │
│  - id (PK)              │  │
│  - request_id           │  │
│  - conversation_id (FK) ├──┘
│  - message_id (FK)      ├──┘
└──────────┬──────────────┘
           │ 1:N
           │
┌──────────▼──────────────┐
│ ai_tool_calls           │
│  - id (PK)              │
│  - agent_request_id (FK)│
│  - tool_name            │
│  - status               │
└──────────┬──────────────┘
           │ 1:1
           │
┌──────────▼──────────────┐
│ ai_tool_results         │
│  - id (PK)              │
│  - tool_call_id (FK)    │
│  - result_data          │
└─────────────────────────┘

┌─────────────────────────┐       ┌─────────────────────────┐
│ ai_memory               │       │ mcp_tool_usage          │
│  - id (PK)              │       │  - id (PK)              │
│  - scope                │       │  - tool_name            │
│  - key                  │       │  - status               │
│  - value                │       │  - latency_ms           │
└─────────────────────────┘       └─────────────────────────┘

┌─────────────────────────┐       ┌─────────────────────────┐
│ ai_stream_tickets       │       │ ai_idempotency_keys     │
│  - id (PK)              │       │  - id (PK)              │
│  - ticket               │       │  - idempotency_key      │
│  - conversation_id (FK) │       │  - resource_id          │
└─────────────────────────┘       └─────────────────────────┘
```

---

## Core Tables

### 1. ai_conversations

**Purpose:** Track conversation sessions  
**Key Fields:** session_key (user identifier), provider, model  

**Use Cases:**
- Link messages together
- Track conversation context
- Cost tracking per session
- Session-based memory scoping

---

### 2. ai_conversation_messages

**Purpose:** Store individual messages in conversations  
**Key Fields:** conversation_id (FK), role (user/assistant/system), content  

**Use Cases:**
- Build conversation history
- Display chat interfaces
- Context for AI prompts
- Message-level telemetry

---

### 3. ai_agent_requests

**Purpose:** Track API requests (chat, tools, memory)  
**Key Fields:** request_id (UUID), endpoint, conversation_id (FK), message_id (FK)  

**Use Cases:**
- Request tracing
- Performance monitoring
- Error debugging
- Link requests to conversations

---

### 4. ai_tool_calls

**Purpose:** Log all tool invocations  
**Key Fields:** agent_request_id (FK), tool_name, status (enum), args (JSON)  

**Use Cases:**
- Tool usage analytics
- Performance tracking
- Error analysis
- Cost attribution

**Status Values:**
- `started` - Tool invocation initiated
- `ok` - Tool completed successfully
- `error` - Tool failed with error
- `timeout` - Tool exceeded time limit

---

### 5. ai_tool_results

**Purpose:** Store tool execution results  
**Key Fields:** tool_call_id (FK), result_data (JSON), error_message  

**Use Cases:**
- Result caching
- Debugging failed tools
- Response validation
- Audit trail

---

### 6. ai_memory

**Purpose:** Persistent key-value memory storage  
**Key Fields:** scope (enum), key, value (JSON), confidence  

**Use Cases:**
- Session context persistence
- User preference storage
- Project-specific facts
- Global knowledge base

**Scope Values:**
- `global` - System-wide facts
- `project` - Project-specific context
- `session` - User session memory
- `user` - User profile data

---

### 7. mcp_tool_usage

**Purpose:** MCP-specific telemetry tracking  
**Key Fields:** tool_name, status, latency_ms, tokens_in, tokens_out  

**Use Cases:**
- MCP performance monitoring
- Token usage analytics
- Cost tracking
- Rate limiting

---

### 8. ai_stream_tickets

**Purpose:** Manage SSE streaming sessions  
**Key Fields:** ticket (UUID), conversation_id (FK), expires_at  

**Use Cases:**
- Streaming authentication
- Session management
- Cleanup expired sessions

---

### 9. ai_idempotency_keys

**Purpose:** Prevent duplicate operations  
**Key Fields:** idempotency_key (UUID), resource_id, response_data (JSON)  

**Use Cases:**
- Retry safety
- Duplicate request detection
- Consistent responses

---

## Schema Definitions

### ai_conversations

```sql
CREATE TABLE ai_conversations (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  session_key VARCHAR(255) NOT NULL,
  provider VARCHAR(50) DEFAULT NULL,
  model VARCHAR(100) DEFAULT NULL,
  total_tokens INT UNSIGNED DEFAULT 0,
  cost_nzd_cents INT UNSIGNED DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  INDEX idx_session_key (session_key),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Key Columns:**
- `session_key`: User/session identifier (indexed)
- `provider`: AI provider (openai/anthropic)
- `model`: Model name (gpt-4o-mini, claude-3-5-sonnet)
- `total_tokens`: Cumulative token count
- `cost_nzd_cents`: Estimated cost in NZD cents

---

### ai_conversation_messages

```sql
CREATE TABLE ai_conversation_messages (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  conversation_id INT UNSIGNED NOT NULL,
  role ENUM('system', 'user', 'assistant', 'tool') NOT NULL,
  content TEXT NOT NULL,
  tokens INT UNSIGNED DEFAULT 0,
  cost_nzd_cents INT UNSIGNED DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (conversation_id) 
    REFERENCES ai_conversations(id) 
    ON DELETE CASCADE,
  
  INDEX idx_conversation_id (conversation_id),
  INDEX idx_role (role),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Key Columns:**
- `conversation_id`: Links to ai_conversations (CASCADE delete)
- `role`: Message sender (system/user/assistant/tool)
- `content`: Message text
- `tokens`: Token count for this message
- `cost_nzd_cents`: Cost for this message

**Foreign Keys:**
- conversation_id → ai_conversations.id (ON DELETE CASCADE)

---

### ai_agent_requests

```sql
CREATE TABLE ai_agent_requests (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  request_id VARCHAR(64) NOT NULL UNIQUE,
  endpoint VARCHAR(255) NOT NULL,
  method VARCHAR(10) DEFAULT 'POST',
  conversation_id INT UNSIGNED DEFAULT NULL,
  message_id INT UNSIGNED DEFAULT NULL,
  session_key VARCHAR(255) DEFAULT NULL,
  ip VARCHAR(45) DEFAULT NULL,
  user_agent TEXT DEFAULT NULL,
  status_code SMALLINT UNSIGNED DEFAULT NULL,
  latency_ms INT UNSIGNED DEFAULT NULL,
  error_code VARCHAR(50) DEFAULT NULL,
  error_message TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (conversation_id) 
    REFERENCES ai_conversations(id) 
    ON DELETE SET NULL,
  FOREIGN KEY (message_id) 
    REFERENCES ai_conversation_messages(id) 
    ON DELETE SET NULL,
  
  INDEX idx_request_id (request_id),
  INDEX idx_endpoint (endpoint),
  INDEX idx_conversation_id (conversation_id),
  INDEX idx_message_id (message_id),
  INDEX idx_session_key (session_key),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Key Columns:**
- `request_id`: Unique UUID for request tracking
- `endpoint`: API endpoint path
- `conversation_id`: Optional FK to ai_conversations
- `message_id`: Optional FK to ai_conversation_messages
- `latency_ms`: Total request duration
- `error_code`: Error code if request failed

**Foreign Keys:**
- conversation_id → ai_conversations.id (ON DELETE SET NULL)
- message_id → ai_conversation_messages.id (ON DELETE SET NULL)

---

### ai_tool_calls

```sql
CREATE TABLE ai_tool_calls (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  agent_request_id INT UNSIGNED DEFAULT NULL,
  tool_name VARCHAR(100) NOT NULL,
  status ENUM('started', 'ok', 'error', 'timeout') DEFAULT 'started',
  args JSON DEFAULT NULL,
  latency_ms INT UNSIGNED DEFAULT NULL,
  error TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (agent_request_id) 
    REFERENCES ai_agent_requests(id) 
    ON DELETE CASCADE,
  
  INDEX idx_agent_request_id (agent_request_id),
  INDEX idx_tool_name (tool_name),
  INDEX idx_status (status),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Key Columns:**
- `agent_request_id`: Links to ai_agent_requests (CASCADE delete)
- `tool_name`: Tool identifier (fs.read, db.select, etc.)
- `status`: Execution status (started/ok/error/timeout)
- `args`: JSON tool arguments
- `latency_ms`: Tool execution time

**Foreign Keys:**
- agent_request_id → ai_agent_requests.id (ON DELETE CASCADE)

---

### ai_tool_results

```sql
CREATE TABLE ai_tool_results (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  tool_call_id INT UNSIGNED NOT NULL,
  result_data JSON DEFAULT NULL,
  error_message TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (tool_call_id) 
    REFERENCES ai_tool_calls(id) 
    ON DELETE CASCADE,
  
  UNIQUE KEY (tool_call_id),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Key Columns:**
- `tool_call_id`: Links to ai_tool_calls (CASCADE delete, UNIQUE)
- `result_data`: JSON tool output
- `error_message`: Error text if tool failed

**Foreign Keys:**
- tool_call_id → ai_tool_calls.id (ON DELETE CASCADE)

**Constraints:**
- UNIQUE KEY on tool_call_id (1:1 relationship)

---

### ai_memory

```sql
CREATE TABLE ai_memory (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  scope ENUM('global', 'project', 'session', 'user') NOT NULL,
  `key` VARCHAR(255) NOT NULL,
  value JSON NOT NULL,
  confidence TINYINT UNSIGNED DEFAULT 50 COMMENT '0-100',
  source VARCHAR(50) DEFAULT 'assistant' COMMENT 'user|assistant|system',
  conversation_id INT UNSIGNED DEFAULT NULL,
  session_key VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (conversation_id) 
    REFERENCES ai_conversations(id) 
    ON DELETE SET NULL,
  
  UNIQUE KEY idx_scope_key (scope, `key`),
  INDEX idx_scope (scope),
  INDEX idx_conversation_id (conversation_id),
  INDEX idx_session_key (session_key),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Key Columns:**
- `scope`: Memory scope (global/project/session/user)
- `key`: Memory key (unique per scope)
- `value`: JSON value (any structure)
- `confidence`: Confidence score 0-100
- `source`: Who created this memory (user/assistant/system)

**Foreign Keys:**
- conversation_id → ai_conversations.id (ON DELETE SET NULL)

**Constraints:**
- UNIQUE KEY on (scope, key)

---

### mcp_tool_usage

```sql
CREATE TABLE mcp_tool_usage (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  tool_name VARCHAR(100) NOT NULL,
  status VARCHAR(20) DEFAULT NULL COMMENT 'started|ok|error|timeout',
  args JSON DEFAULT NULL,
  result JSON DEFAULT NULL,
  latency_ms INT UNSIGNED DEFAULT NULL,
  tokens_in INT UNSIGNED DEFAULT 0,
  tokens_out INT UNSIGNED DEFAULT 0,
  error TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_tool_name (tool_name),
  INDEX idx_status (status),
  INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Key Columns:**
- `tool_name`: MCP tool identifier
- `status`: Execution status
- `args`: JSON tool arguments
- `result`: JSON tool result
- `latency_ms`: Execution time
- `tokens_in`: Input tokens (for LLM tools)
- `tokens_out`: Output tokens (for LLM tools)

**No Foreign Keys** (standalone telemetry)

---

### ai_stream_tickets

```sql
CREATE TABLE ai_stream_tickets (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  ticket VARCHAR(64) NOT NULL UNIQUE,
  conversation_id INT UNSIGNED DEFAULT NULL,
  session_key VARCHAR(255) DEFAULT NULL,
  expires_at TIMESTAMP NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (conversation_id) 
    REFERENCES ai_conversations(id) 
    ON DELETE CASCADE,
  
  INDEX idx_ticket (ticket),
  INDEX idx_expires_at (expires_at),
  INDEX idx_conversation_id (conversation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Key Columns:**
- `ticket`: Unique UUID for stream authentication
- `conversation_id`: Optional FK to conversation
- `expires_at`: Ticket expiration timestamp

**Foreign Keys:**
- conversation_id → ai_conversations.id (ON DELETE CASCADE)

---

### ai_idempotency_keys

```sql
CREATE TABLE ai_idempotency_keys (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  idempotency_key VARCHAR(255) NOT NULL UNIQUE,
  endpoint VARCHAR(255) NOT NULL,
  resource_id VARCHAR(255) DEFAULT NULL COMMENT 'conversation_id, message_id, etc.',
  response_data JSON DEFAULT NULL,
  expires_at TIMESTAMP NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  INDEX idx_idempotency_key (idempotency_key),
  INDEX idx_endpoint (endpoint),
  INDEX idx_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Key Columns:**
- `idempotency_key`: Unique client-provided key (UNIQUE)
- `endpoint`: API endpoint this key applies to
- `resource_id`: Created resource identifier
- `response_data`: Cached response (JSON)
- `expires_at`: Key expiration (auto-cleanup)

**Constraints:**
- UNIQUE KEY on idempotency_key

---

## Foreign Keys

### Cascade Behavior

| Parent Table | Child Table | On Delete | Reasoning |
|-------------|-------------|-----------|-----------|
| ai_conversations | ai_conversation_messages | CASCADE | Messages belong to conversation |
| ai_conversations | ai_agent_requests | SET NULL | Preserve requests even if conversation deleted |
| ai_conversation_messages | ai_agent_requests | SET NULL | Preserve requests even if message deleted |
| ai_agent_requests | ai_tool_calls | CASCADE | Tool calls belong to request |
| ai_tool_calls | ai_tool_results | CASCADE | Results belong to tool call |
| ai_conversations | ai_memory | SET NULL | Preserve memory even if conversation deleted |
| ai_conversations | ai_stream_tickets | CASCADE | Tickets belong to conversation |

### Referential Integrity

All foreign keys enforced at database level:

```sql
-- Example: Verify foreign key constraints
SELECT 
  TABLE_NAME,
  COLUMN_NAME,
  CONSTRAINT_NAME,
  REFERENCED_TABLE_NAME,
  REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE
WHERE TABLE_SCHEMA = 'hdgwrzntwa'
  AND REFERENCED_TABLE_NAME IS NOT NULL
  AND TABLE_NAME LIKE 'ai_%'
ORDER BY TABLE_NAME, ORDINAL_POSITION;
```

**Result:**
```
+---------------------------+--------------------+---------------------------------+--------------------------+-----------------------+
| TABLE_NAME                | COLUMN_NAME        | CONSTRAINT_NAME                 | REFERENCED_TABLE_NAME    | REFERENCED_COLUMN_NAME|
+---------------------------+--------------------+---------------------------------+--------------------------+-----------------------+
| ai_agent_requests         | conversation_id    | ai_agent_requests_ibfk_1        | ai_conversations         | id                    |
| ai_agent_requests         | message_id         | ai_agent_requests_ibfk_2        | ai_conversation_messages | id                    |
| ai_conversation_messages  | conversation_id    | ai_conversation_messages_ibfk_1 | ai_conversations         | id                    |
| ai_memory                 | conversation_id    | ai_memory_ibfk_1                | ai_conversations         | id                    |
| ai_stream_tickets         | conversation_id    | ai_stream_tickets_ibfk_1        | ai_conversations         | id                    |
| ai_tool_calls             | agent_request_id   | ai_tool_calls_ibfk_1            | ai_agent_requests        | id                    |
| ai_tool_results           | tool_call_id       | ai_tool_results_ibfk_1          | ai_tool_calls            | id                    |
+---------------------------+--------------------+---------------------------------+--------------------------+-----------------------+
```

---

## Indexes

### Query Optimization

All tables have strategic indexes for common query patterns:

#### ai_conversations
```sql
INDEX idx_session_key (session_key)      -- Find conversations by user
INDEX idx_created_at (created_at)        -- Time-based queries
```

#### ai_conversation_messages
```sql
INDEX idx_conversation_id (conversation_id)  -- Get messages for conversation
INDEX idx_role (role)                        -- Filter by role (user/assistant)
INDEX idx_created_at (created_at)            -- Time-based queries
```

#### ai_agent_requests
```sql
INDEX idx_request_id (request_id)            -- Lookup by request ID
INDEX idx_endpoint (endpoint)                -- Endpoint analytics
INDEX idx_conversation_id (conversation_id)  -- Link to conversation
INDEX idx_message_id (message_id)            -- Link to message
INDEX idx_session_key (session_key)          -- User analytics
INDEX idx_created_at (created_at)            -- Time-based queries
```

#### ai_tool_calls
```sql
INDEX idx_agent_request_id (agent_request_id)  -- Tool calls for request
INDEX idx_tool_name (tool_name)                -- Tool usage analytics
INDEX idx_status (status)                      -- Filter by status (ok/error)
INDEX idx_created_at (created_at)              -- Time-based queries
```

#### ai_memory
```sql
UNIQUE KEY idx_scope_key (scope, `key`)        -- Enforce uniqueness
INDEX idx_scope (scope)                        -- Filter by scope
INDEX idx_conversation_id (conversation_id)    -- Link to conversation
INDEX idx_session_key (session_key)            -- Session memory lookup
INDEX idx_created_at (created_at)              -- Time-based queries
```

#### mcp_tool_usage
```sql
INDEX idx_tool_name (tool_name)                -- Tool analytics
INDEX idx_status (status)                      -- Filter by status
INDEX idx_created_at (created_at)              -- Time-based queries
```

---

## Migration History

### PHASE1_MIGRATION.sql

**Applied:** October 28, 2025  
**Purpose:** Initial schema creation  

**Created Tables:**
- ai_conversations
- ai_conversation_messages
- ai_agent_requests
- ai_tool_calls
- ai_tool_results
- ai_memory
- mcp_tool_usage

**Status:** ✅ Applied successfully

---

### PHASE2_SCHEMA_MIGRATION.sql

**Applied:** October 29, 2025  
**Purpose:** Add stream tickets and idempotency keys  

**Created Tables:**
- ai_stream_tickets
- ai_idempotency_keys

**Added Columns:**
- ai_tool_calls.latency_ms
- ai_tool_results.error_message

**Status:** ✅ Applied successfully

---

### Migration Commands

```bash
# Apply migration
mysql -u [user] -p hdgwrzntwa < migrations/PHASE1_MIGRATION.sql

# Verify tables created
mysql -u [user] -p hdgwrzntwa -e "SHOW TABLES LIKE 'ai_%';"

# Check foreign keys
mysql -u [user] -p hdgwrzntwa < migrations/verify_foreign_keys.sql
```

---

## Query Examples

### Get Conversation with Messages

```sql
SELECT 
  c.id as conversation_id,
  c.session_key,
  m.role,
  m.content,
  m.tokens,
  m.created_at
FROM ai_conversations c
JOIN ai_conversation_messages m ON m.conversation_id = c.id
WHERE c.session_key = 'demo-session'
ORDER BY m.created_at ASC;
```

---

### Tool Usage Analytics

```sql
SELECT 
  tool_name,
  COUNT(*) as total_calls,
  SUM(CASE WHEN status = 'ok' THEN 1 ELSE 0 END) as success_count,
  SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as error_count,
  AVG(latency_ms) as avg_latency_ms,
  MAX(latency_ms) as max_latency_ms
FROM ai_tool_calls
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY tool_name
ORDER BY total_calls DESC;
```

---

### Recent Errors

```sql
SELECT 
  tc.tool_name,
  tc.args,
  tc.error,
  tr.error_message,
  tc.created_at
FROM ai_tool_calls tc
LEFT JOIN ai_tool_results tr ON tr.tool_call_id = tc.id
WHERE tc.status = 'error'
ORDER BY tc.created_at DESC
LIMIT 20;
```

---

### Memory Lookup

```sql
SELECT 
  `key`,
  value,
  confidence,
  source,
  updated_at
FROM ai_memory
WHERE scope = 'session'
  AND session_key = 'demo-session'
ORDER BY updated_at DESC;
```

---

**Document Version:** 1.0.0  
**Last Updated:** November 2, 2025  
**Related Docs:** 01_SYSTEM_OVERVIEW.md, 03_AI_AGENT_ENDPOINTS.md, 06_TELEMETRY_LOGGING.md
