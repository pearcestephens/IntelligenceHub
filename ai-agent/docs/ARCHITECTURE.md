# 🏗️ AI Agent System Architecture

**Version:** 2.0 (Multi-Domain Enhanced)
**Last Updated:** October 29, 2025
**Status:** PRODUCTION DEPLOYED

---

## 📋 Table of Contents

1. [System Overview](#system-overview)
2. [Component Architecture](#component-architecture)
3. [Logging Infrastructure](#logging-infrastructure)
4. [Multi-Domain Pipeline](#multi-domain-pipeline)
5. [Database Architecture](#database-architecture)
6. [Data Flow Diagrams](#data-flow-diagrams)
7. [Audit Trail Design](#audit-trail-design)
8. [Context Tracking](#context-tracking)
9. [Performance & Monitoring](#performance--monitoring)
10. [Security Architecture](#security-architecture)

---

## 🎯 System Overview

### What This Is
The AI Agent is a conversational AI system that provides intelligent responses by leveraging:
- **Knowledge Base:** 342 documents across 6 domain-specific contexts
- **Multi-Domain Intelligence:** Domain-aware search with GOD MODE capability
- **Contextual Memory:** Conversation history and context cards
- **Tool Execution:** Structured MCP tool calls
- **Real-time Streaming:** Server-Sent Events (SSE) for live responses

### Core Technologies
- **Language:** PHP 8.1+ (strict types)
- **Database:** MariaDB 10.5 (InnoDB, UTF-8mb4)
- **Logging:** Monolog 2.x with JSON formatting
- **AI Providers:** OpenAI GPT-4, Anthropic Claude
- **Caching:** Redis for embeddings and summaries
- **Transport:** HTTP/HTTPS with SSE

### System Boundaries
```
┌─────────────────────────────────────────────────────────────┐
│                    EXTERNAL CLIENTS                         │
│  (Web UI, API Consumers, Live Chat Interface)               │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                   AI AGENT SYSTEM                           │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  Entry Point: /ai-agent/agent-message-stream.php     │  │
│  │  Core: Agent.php (orchestrator)                      │  │
│  │  Components: DB, Redis, OpenAI, Claude, Embeddings   │  │
│  │  Intelligence: KB Search, Summarizer, ContextCards   │  │
│  └───────────────────────────────────────────────────────┘  │
└────────────────────┬────────────────────────────────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────────┐
│                  DATA LAYER                                 │
│  ┌──────────────┐  ┌──────────────┐  ┌─────────────────┐   │
│  │  MariaDB     │  │   Redis      │  │  Log Files      │   │
│  │  100+ tables │  │  Cache       │  │  operations.log │   │
│  │  Agent data  │  │  Embeddings  │  │  chat.log       │   │
│  └──────────────┘  └──────────────┘  └─────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

---

## 🧩 Component Architecture

### 1. Core Orchestrator: `Agent.php`

**Responsibility:** Central coordinator for all AI operations

**Dependencies Injected:**
```php
$this->logger       // Contextual logging (Monolog)
$this->db           // Database operations (PDO wrapper)
$this->redis        // Caching layer (Redis client)
$this->openai       // OpenAI API client
$this->claude       // Anthropic Claude API client
$this->sse          // Server-Sent Events handler
$this->embeddings   // Vector embeddings for semantic search
$this->summarizer   // Conversation summarization
$this->contextCards // Context card management
```

**Key Methods:**
- `handleMessage()` - Process user input, generate response
- `streamResponse()` - Send chunks via SSE
- `executeTool()` - Handle MCP tool calls
- `searchKnowledge()` - Query knowledge base with domain filtering
- `buildPrompt()` - Construct LLM prompt with context

**Logging Points:**
- Request received (conversation_id, user_id, domain_id)
- KB search initiated (query, domain, god_mode_active)
- Tool execution (tool_name, args, duration_ms, success)
- Response streaming (chunk_count, total_tokens, duration)
- Errors and exceptions (stack trace, context)

---

### 2. Logging Infrastructure: `Logger.php`

**Architecture:** Monolog wrapper with fallback mode

#### Primary Mode (Monolog Available):
```
User Request → Logger::info()
                    ↓
            [Context Enrichment]
         (conversation_id, request_id, user_ip)
                    ↓
            [Monolog Pipeline]
         (StreamHandler → JSON format)
                    ↓
            [Output Destinations]
         • stderr (JSON structured)
         • operations.log (file handler)
         • Error log handlers
```

#### Fallback Mode (Monolog Missing):
```
User Request → Logger::info()
                    ↓
        [Lightweight Fallback]
      (Basic PHP error_log wrapper)
                    ↓
         [JSON formatting via fallback]
                    ↓
            error_log() → stderr
```

#### Context Injection Pattern:
```php
// Set conversation context (persists across log calls)
Logger::setContext([
    'conversation_id' => $conversationId,
    'user_id' => $userId,
    'domain_id' => $domainId,
    'god_mode_enabled' => $godMode
]);

// All subsequent logs include this context automatically
Logger::info('Processing message', ['query' => $text]);
Logger::debug('KB search started', ['filters' => $filters]);
Logger::error('Tool execution failed', ['tool' => $name]);
```

#### Log Levels & Usage:
| Level    | Usage                          | Example                          |
|----------|--------------------------------|----------------------------------|
| DEBUG    | Development traces             | "Context assembled: 5 messages"  |
| INFO     | Normal operations              | "Message processed successfully" |
| WARNING  | Recoverable issues             | "Cache miss, fetching from DB"   |
| ERROR    | Failures, exceptions           | "OpenAI API timeout"             |
| CRITICAL | System-level failures          | "Database connection lost"       |

#### Data Sanitization:
```php
// Automatic redaction of sensitive fields
Logger::sanitizeForLog([
    'password' => 'secret123',      // → '[REDACTED]'
    'api_key' => 'sk-abc123',       // → '[REDACTED]'
    'user_input' => 'long text...'  // → truncated to 1000 chars
]);
```

#### Specialized Log Methods:
```php
// Tool execution logging
Logger::logTool($toolName, $args, $result, $durationMs, $success);

// OpenAI API logging
Logger::logOpenAI($endpoint, $request, $response, $durationMs, $success);
```

---

### 3. Database Layer: `DB.php`

**Architecture:** PDO wrapper with query logging

**Connection Pool:**
```
Config (dbconfig.php) → PDO Connection
                             ↓
                     [UTF-8mb4 charset]
                     [InnoDB transactions]
                             ↓
                      DB.php (wrapper)
                             ↓
                    [Query execution]
                    [Parameter binding]
                    [Result formatting]
                             ↓
                    [Query logging]*
```

*Query logging available at multiple levels:
1. **Application Level:** DB.php logs via Logger
2. **Database Level:** Slow query log (300ms+ threshold)
3. **Audit Level:** ai_kb_domain_query_log table

**Transaction Support:**
```php
$db->beginTransaction();
try {
    $db->execute("INSERT INTO agent_messages ...");
    $db->execute("UPDATE agent_conversations SET updated_at = NOW()");
    $db->commit();
} catch (Exception $e) {
    $db->rollback();
    Logger::error('Transaction failed', ['error' => $e->getMessage()]);
    throw $e;
}
```

---

### 4. Knowledge Base Search: `Embeddings.php`

**Architecture:** Semantic search with Redis caching

```
User Query → Embeddings::search($query, $filters)
                    ↓
            [Check Redis Cache]
         (embedding:{query_hash})
                    ↓
          Cache Hit?   Cache Miss?
               ↓            ↓
         Return cached   OpenAI API
         embeddings      (text-embedding-3-small)
               ↓            ↓
               └────────────┘
                    ↓
            [Vector Similarity]
         (cosine distance < 0.3)
                    ↓
        [Domain Filtering Applied]*
         (active_domain_id = ? OR god_mode = 1)
                    ↓
            [Relevance Scoring]
         (base_relevance × domain_relevance_score)
                    ↓
            [Top N Results]
         (ORDER BY final_score DESC LIMIT 10)
                    ↓
        [Log Query to Database]
         (ai_kb_domain_query_log)
                    ↓
            Return results
```

*Multi-Domain Filtering Logic:
```sql
-- Normal mode: filter by active domain
WHERE ddm.domain_id = :active_domain_id

-- GOD MODE: no domain filter (access all 342 docs)
WHERE :god_mode_enabled = 1 OR ddm.domain_id = :active_domain_id
```

---

### 5. Multi-Domain System: Schema & Logic

**Tables:**
```
┌─────────────────────────────────────────────────────────────┐
│  ai_kb_domain_registry (6 domains)                          │
│  ┌────────────┬──────────────┬────────────┬──────────────┐  │
│  │ domain_id  │ domain_name  │ is_active  │ description  │  │
│  ├────────────┼──────────────┼────────────┼──────────────┤  │
│  │     1      │ global       │     1      │ Company-wide │  │
│  │     2      │ staff        │     1      │ HR/Staff     │  │
│  │     3      │ web          │     1      │ Website docs │  │
│  │     4      │ gpt          │     1      │ AI/ML docs   │  │
│  │     5      │ wiki         │     1      │ Wiki content │  │
│  │     6      │ superadmin   │     1      │ GOD MODE     │  │
│  └────────────┴──────────────┴────────────┴──────────────┘  │
└─────────────────────────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│  ai_kb_doc_domain_map (737 mappings)                        │
│  ┌─────────┬─────────┬───────────┬───────────────────────┐  │
│  │ map_id  │ doc_id  │ domain_id │ relevance_score (%)   │  │
│  ├─────────┼─────────┼───────────┼───────────────────────┤  │
│  │   ...   │ uuid-1  │     1     │        55.8           │  │
│  │   ...   │ uuid-1  │     6     │       100.0 (GOD)     │  │
│  │   ...   │ uuid-2  │     2     │        90.0           │  │
│  │   ...   │ uuid-2  │     6     │       100.0 (GOD)     │  │
│  └─────────┴─────────┴───────────┴───────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│  agent_conversations (enhanced with domain columns)         │
│  ┌────────────┬────────────┬──────────┬─────────────────┐   │
│  │ conv_id    │ domain_id  │ god_mode │ switch_count    │   │
│  ├────────────┼────────────┼──────────┼─────────────────┤   │
│  │ uuid-conv1 │     1      │    0     │       0         │   │
│  │ uuid-conv2 │     4      │    1     │       3         │   │
│  └────────────┴────────────┴──────────┴─────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│  ai_kb_domain_query_log (query audit trail)                │
│  ┌────────┬───────────┬──────────┬───────────┬──────────┐   │
│  │ log_id │ domain_id │ conv_id  │ query     │ god_mode │   │
│  ├────────┼───────────┼──────────┼───────────┼──────────┤   │
│  │   1    │     1     │ uuid-c1  │ "search"  │    0     │   │
│  │   2    │     6     │ uuid-c2  │ "query"   │    1     │   │
│  └────────┴───────────┴──────────┴───────────┴──────────┘   │
└─────────────────────────────────────────────────────────────┘
```

**Stored Procedures:**

**1. `sp_switch_domain(conversation_id, new_domain_id)`**
```sql
-- Atomically switch conversation to new domain
-- Increments switch_count
-- Returns domain info
CALL sp_switch_domain('uuid-conv-123', 4);
```

**2. `sp_enable_god_mode(conversation_id)`**
```sql
-- Enable GOD MODE (superadmin domain with all 342 docs)
-- Sets domain_id = 6, god_mode_enabled = 1
CALL sp_enable_god_mode('uuid-conv-123');
```

**3. `sp_log_domain_query(domain_id, conversation_id, query_text, result_count, response_time_ms, god_mode_active)`**
```sql
-- Log KB query with domain context
-- Updates usage metrics (query_count, total_results)
CALL sp_log_domain_query(1, 'uuid-conv-123', 'search term', 5, 120, 0);
```

**Views:**

**1. `v_domain_stats_live`**
```sql
SELECT
    d.domain_name,
    COUNT(DISTINCT ddm.doc_id) as total_docs,
    SUM(ddm.is_primary) as primary_docs,
    AVG(ddm.relevance_score) as avg_relevance,
    m.query_count_24h,
    m.avg_response_time_ms
FROM ai_kb_domain_registry d
LEFT JOIN ai_kb_doc_domain_map ddm ON d.domain_id = ddm.domain_id
LEFT JOIN ai_kb_domain_usage_metrics m ON d.domain_id = m.domain_id
GROUP BY d.domain_id;
```

**2. `v_god_mode_overview`**
```sql
SELECT
    COUNT(DISTINCT d.domain_id) as total_domains,
    COUNT(DISTINCT doc.id) as total_docs,
    COUNT(DISTINCT ddm.map_id) as active_mappings,
    COUNT(DISTINCT CASE WHEN qlog.god_mode_active = 1 THEN qlog.log_id END) as god_mode_queries_24h,
    COUNT(DISTINCT CASE WHEN conv.god_mode_enabled = 1 THEN conv.conversation_id END) as conversations_in_god_mode
FROM ai_kb_domain_registry d
CROSS JOIN agent_kb_docs doc
LEFT JOIN ai_kb_doc_domain_map ddm ON doc.id = ddm.doc_id
LEFT JOIN ai_kb_domain_query_log qlog ON qlog.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
LEFT JOIN agent_conversations conv ON conv.god_mode_enabled = 1;
```

---

## 📊 Database Architecture

### Agent Tables (12 core tables)

**Conversations & Messages:**
```
agent_conversations (conversation metadata)
    ├── conversation_id (CHAR(36) PK - UUID)
    ├── user_id (INT)
    ├── active_domain_id (INT FK → ai_kb_domain_registry) *NEW*
    ├── god_mode_enabled (TINYINT) *NEW*
    ├── domain_switch_count (INT) *NEW*
    ├── created_at
    └── updated_at

agent_messages (individual messages)
    ├── id (BIGINT PK)
    ├── conversation_id (CHAR(36) FK → agent_conversations)
    ├── role (enum: 'user', 'assistant', 'system')
    ├── content (TEXT)
    ├── created_at
    └── tokens (INT)

agent_compressed_messages_archive (old compressed messages)
    ├── archive_id (BIGINT PK)
    ├── conversation_id (CHAR(36) FK)
    ├── compressed_data (MEDIUMBLOB - gzipped JSON)
    ├── original_message_count (INT)
    └── archived_at
```

**Knowledge Base:**
```
agent_kb_docs (342 documents)
    ├── id (CHAR(36) PK - UUID)
    ├── title (VARCHAR(500))
    ├── content (LONGTEXT)
    ├── embedding (BLOB - vector data)
    ├── source_file (VARCHAR(1000))
    ├── doc_type (VARCHAR(50))
    └── indexed_at

ai_kb_doc_domain_map (737 mappings) *NEW*
    ├── map_id (BIGINT PK)
    ├── doc_id (CHAR(36) FK → agent_kb_docs)
    ├── domain_id (INT FK → ai_kb_domain_registry)
    ├── relevance_score (DECIMAL 0-100)
    ├── is_primary (TINYINT)
    └── mapped_at

ai_kb_domain_registry (6 domains) *NEW*
    ├── domain_id (INT PK)
    ├── domain_name (VARCHAR(50))
    ├── display_name (VARCHAR(100))
    ├── description (TEXT)
    ├── is_active (TINYINT)
    └── created_at
```

**Tools & Context:**
```
agent_tool_calls (MCP tool execution history)
    ├── id (BIGINT PK)
    ├── conversation_id (CHAR(36) FK)
    ├── tool_name (VARCHAR(100))
    ├── arguments (JSON)
    ├── result (JSON)
    ├── executed_at
    └── duration_ms (INT)

agent_importance_scores (message importance ratings)
    ├── id (BIGINT PK)
    ├── message_id (BIGINT FK → agent_messages)
    ├── score (DECIMAL 0-1)
    └── calculated_at

agent_conversation_tags (conversation categorization)
    ├── id (BIGINT PK)
    ├── conversation_id (CHAR(36) FK)
    ├── tag (VARCHAR(50))
    └── tagged_at

agent_conversation_clusters (conversation grouping)
    ├── id (BIGINT PK)
    ├── conversation_id (CHAR(36) FK)
    ├── cluster_id (INT)
    └── clustered_at
```

**Metrics & Analytics:**
```
agent_metrics_hourly (per-hour stats)
    ├── id (BIGINT PK)
    ├── hour (DATETIME)
    ├── conversation_count (INT)
    ├── message_count (INT)
    ├── avg_response_time_ms (INT)
    └── total_tokens (BIGINT)

agent_metrics_daily (per-day rollup)
agent_metrics_weekly (per-week rollup)
agent_metrics_monthly (per-month rollup)

ai_kb_domain_usage_metrics (domain query stats) *NEW*
    ├── metric_id (BIGINT PK)
    ├── domain_id (INT FK)
    ├── query_count_hourly (INT)
    ├── query_count_daily (INT)
    ├── avg_response_time_ms (INT)
    ├── total_results_returned (BIGINT)
    └── last_updated
```

### Audit & Logging Tables (100+ tables)

**Categories of Audit Tables:**

**1. AI Agent Specific (13 tables):**
```
ai_assistant_logs           - General assistant operations
ai_context_log              - Context card operations
ai_kb_domain_query_log      - Multi-domain query logging *NEW*
agent_tool_calls            - Tool execution audit
gpt_tool_audit              - GPT-specific tool tracking
...
```

**2. System Level (15+ tables):**
```
system_audit_log            - System-wide events
user_activity_log           - User action tracking
action_audit                - Generic action audit
api_audit                   - API endpoint access
config_audit_log            - Configuration changes
login_audit_log             - Authentication events
session_audit_log           - Session lifecycle
...
```

**3. Business Domain (70+ tables):**
```
consignment_audit_log       - Consignment operations
inventory_audit_log         - Inventory changes
payroll_audit_log           - Payroll processing
purchase_order_audit_log    - PO lifecycle
stock_transfer_audit_log    - Transfer tracking
xero_audit_log              - Xero integration events
...
```

**Common Audit Table Structure:**
```sql
CREATE TABLE {module}_audit_log (
    audit_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(50),
    entity_type VARCHAR(50),
    entity_id VARCHAR(100),
    changes JSON,                -- Before/after values
    ip_address VARCHAR(45),
    user_agent TEXT,
    request_id VARCHAR(36),      -- Correlation ID
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_action (user_id, action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created (created_at),
    INDEX idx_request (request_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 🔄 Data Flow Diagrams

### 1. User Query Processing Flow

```
User submits message
        ↓
[agent-message-stream.php]
        ↓
Agent::handleMessage($input)
        ↓
┌───────────────────────────────────────────────────────────┐
│ 1. CONTEXT LOADING                                        │
│    • Load conversation history from agent_messages        │
│    • Load context cards from agent_context_cards          │
│    • Check active_domain_id and god_mode_enabled          │
│    Logger::info('Context loaded', [                       │
│        'conversation_id' => $id,                          │
│        'message_count' => $count,                         │
│        'domain_id' => $domainId,                          │
│        'god_mode' => $godMode                             │
│    ])                                                     │
└───────────────────────────────────────────────────────────┘
        ↓
┌───────────────────────────────────────────────────────────┐
│ 2. KNOWLEDGE BASE SEARCH                                  │
│    Embeddings::search($query, [                           │
│        'domain_id' => $activeDomainId,                    │
│        'god_mode' => $godModeEnabled,                     │
│        'limit' => 10                                      │
│    ])                                                     │
│    ↓                                                      │
│    • Generate embedding via OpenAI API                    │
│    • Check Redis cache (hit/miss logged)                  │
│    • Query agent_kb_docs with domain filter               │
│    • Apply GOD MODE logic if enabled                      │
│    • Score results by relevance                           │
│    ↓                                                      │
│    CALL sp_log_domain_query(                              │
│        domain_id, conversation_id, query,                 │
│        result_count, response_time_ms, god_mode           │
│    )                                                      │
│    Logger::debug('KB search completed', [                 │
│        'domain' => $domain,                               │
│        'results' => $count,                               │
│        'duration_ms' => $duration,                        │
│        'god_mode' => $godMode                             │
│    ])                                                     │
└───────────────────────────────────────────────────────────┘
        ↓
┌───────────────────────────────────────────────────────────┐
│ 3. PROMPT CONSTRUCTION                                    │
│    • System instructions                                  │
│    • Domain context (if not GOD MODE)                     │
│    • KB results (formatted as context)                    │
│    • Conversation history (last N messages)               │
│    • User query                                           │
│    Logger::debug('Prompt built', [                        │
│        'total_tokens' => $estimate,                       │
│        'kb_docs_included' => $count                       │
│    ])                                                     │
└───────────────────────────────────────────────────────────┘
        ↓
┌───────────────────────────────────────────────────────────┐
│ 4. LLM API CALL                                           │
│    OpenAI::chat($messages, [                              │
│        'model' => 'gpt-4-turbo',                          │
│        'stream' => true,                                  │
│        'tools' => $mcpTools                               │
│    ])                                                     │
│    ↓                                                      │
│    Logger::logOpenAI('chat/completions', $request,        │
│        $response, $durationMs, $success)                  │
│    ↓                                                      │
│    [TOOL CALLS DETECTED]?                                 │
│         ↓ YES                                             │
│    Agent::executeTool($toolName, $args)                   │
│         ↓                                                 │
│    INSERT INTO agent_tool_calls (...)                     │
│    Logger::logTool($name, $args, $result, $ms, $success)  │
│         ↓                                                 │
│    [Continue with tool results]                           │
└───────────────────────────────────────────────────────────┘
        ↓
┌───────────────────────────────────────────────────────────┐
│ 5. RESPONSE STREAMING (SSE)                               │
│    SSE::sendChunk($data)                                  │
│         ↓                                                 │
│    data: {"type":"content","delta":"Hello..."}            │
│    data: {"type":"content","delta":" world"}              │
│    data: {"type":"done","tokens":150}                     │
│         ↓                                                 │
│    Logger::info('Response streamed', [                    │
│        'conversation_id' => $id,                          │
│        'chunks' => $count,                                │
│        'total_tokens' => $tokens,                         │
│        'duration_ms' => $duration                         │
│    ])                                                     │
└───────────────────────────────────────────────────────────┘
        ↓
┌───────────────────────────────────────────────────────────┐
│ 6. PERSISTENCE                                            │
│    $db->beginTransaction();                               │
│    INSERT INTO agent_messages (conversation_id, role,     │
│        content, tokens) VALUES (?, 'user', ?, ?);         │
│    INSERT INTO agent_messages (conversation_id, role,     │
│        content, tokens) VALUES (?, 'assistant', ?, ?);    │
│    UPDATE agent_conversations SET                         │
│        updated_at = NOW(), message_count = message_count+2│
│    WHERE conversation_id = ?;                             │
│    $db->commit();                                         │
│         ↓                                                 │
│    Logger::debug('Messages persisted', [                  │
│        'conversation_id' => $id,                          │
│        'user_tokens' => $userTokens,                      │
│        'assistant_tokens' => $assistantTokens             │
│    ])                                                     │
└───────────────────────────────────────────────────────────┘
        ↓
   [COMPLETE]
```

---

### 2. Domain Switching Flow

```
User clicks "Switch to Staff Domain"
        ↓
API: POST /ai-agent/api/switch-domain.php
    Body: { conversation_id: "uuid", domain_id: 2 }
        ↓
┌───────────────────────────────────────────────────────────┐
│ STORED PROCEDURE EXECUTION                                │
│                                                           │
│ CALL sp_switch_domain('uuid-conv-123', 2);                │
│                                                           │
│ BEGIN                                                     │
│     UPDATE agent_conversations                            │
│     SET active_domain_id = 2,                             │
│         domain_switch_count = domain_switch_count + 1,    │
│         updated_at = NOW()                                │
│     WHERE conversation_id = 'uuid-conv-123';              │
│                                                           │
│     SELECT dr.domain_id, dr.domain_name, dr.display_name, │
│            COUNT(ddm.doc_id) as available_docs            │
│     FROM ai_kb_domain_registry dr                         │
│     LEFT JOIN ai_kb_doc_domain_map ddm ON dr.domain_id=2  │
│     WHERE dr.domain_id = 2;                               │
│ END                                                       │
└───────────────────────────────────────────────────────────┘
        ↓
┌───────────────────────────────────────────────────────────┐
│ LOGGING                                                   │
│                                                           │
│ Logger::info('Domain switched', [                         │
│     'conversation_id' => $id,                             │
│     'from_domain' => $oldDomain,                          │
│     'to_domain' => $newDomain,                            │
│     'available_docs' => $count,                           │
│     'switch_count' => $totalSwitches                      │
│ ]);                                                       │
│                                                           │
│ INSERT INTO system_audit_log (user_id, action,            │
│     entity_type, entity_id, changes, ip_address)          │
│ VALUES (?, 'domain_switch', 'conversation', ?,            │
│     JSON_OBJECT('from', ?, 'to', ?), ?);                  │
└───────────────────────────────────────────────────────────┘
        ↓
Return JSON: {
    "success": true,
    "domain": {
        "id": 2,
        "name": "staff",
        "display_name": "Staff Portal",
        "available_docs": 7,
        "avg_relevance": 90.0
    },
    "switch_count": 3
}
        ↓
UI updates domain badge and available doc count
```

---

### 3. GOD MODE Activation Flow

```
Admin clicks "Enable GOD MODE"
        ↓
API: POST /ai-agent/api/god-mode.php
    Body: { conversation_id: "uuid", action: "enable" }
        ↓
┌───────────────────────────────────────────────────────────┐
│ AUTHORIZATION CHECK                                       │
│                                                           │
│ IF user_role != 'superadmin' THEN                         │
│     Logger::warning('GOD MODE denied', [                  │
│         'user_id' => $userId,                             │
│         'role' => $userRole,                              │
│         'ip' => $_SERVER['REMOTE_ADDR']                   │
│     ]);                                                   │
│     RETURN 403 Forbidden                                  │
│ END IF                                                    │
└───────────────────────────────────────────────────────────┘
        ↓
┌───────────────────────────────────────────────────────────┐
│ STORED PROCEDURE EXECUTION                                │
│                                                           │
│ CALL sp_enable_god_mode('uuid-conv-123');                 │
│                                                           │
│ BEGIN                                                     │
│     UPDATE agent_conversations                            │
│     SET active_domain_id = 6,  -- superadmin domain       │
│         god_mode_enabled = 1,                             │
│         domain_switch_count = domain_switch_count + 1,    │
│         updated_at = NOW()                                │
│     WHERE conversation_id = 'uuid-conv-123';              │
│                                                           │
│     SELECT 'GOD MODE ACTIVATED' as status,                │
│            342 as total_docs_accessible,                  │
│            'All domains unlocked' as message;             │
│ END                                                       │
└───────────────────────────────────────────────────────────┘
        ↓
┌───────────────────────────────────────────────────────────┐
│ AUDIT LOGGING (HIGH-SECURITY EVENT)                       │
│                                                           │
│ Logger::critical('GOD MODE enabled', [                    │
│     'conversation_id' => $id,                             │
│     'user_id' => $userId,                                 │
│     'user_email' => $userEmail,                           │
│     'ip_address' => $_SERVER['REMOTE_ADDR'],              │
│     'user_agent' => $_SERVER['HTTP_USER_AGENT'],          │
│     'timestamp' => date('c')                              │
│ ]);                                                       │
│                                                           │
│ INSERT INTO system_audit_log (user_id, action,            │
│     entity_type, entity_id, changes, severity,            │
│     ip_address, user_agent)                               │
│ VALUES (?, 'god_mode_enable', 'conversation', ?,          │
│     JSON_OBJECT('previous_domain', ?, 'total_docs', 342), │
│     'critical', ?, ?);                                    │
│                                                           │
│ INSERT INTO gpt_tool_audit (user_id, tool_name,           │
│     context, metadata)                                    │
│ VALUES (?, 'god_mode_activation', ?, ?);                  │
└───────────────────────────────────────────────────────────┘
        ↓
┌───────────────────────────────────────────────────────────┐
│ KNOWLEDGE BASE FILTER UPDATE                              │
│                                                           │
│ Future KB searches in this conversation will now use:     │
│                                                           │
│ SELECT * FROM agent_kb_docs docs                          │
│ LEFT JOIN ai_kb_doc_domain_map ddm ON docs.id = ddm.doc_id│
│ WHERE 1 = 1  -- GOD MODE: no domain filter applied        │
│ -- Instead of: WHERE ddm.domain_id = :active_domain_id    │
│                                                           │
│ Result: All 342 documents accessible at 100% relevance    │
└───────────────────────────────────────────────────────────┘
        ↓
Return JSON: {
    "success": true,
    "god_mode": {
        "enabled": true,
        "total_docs": 342,
        "message": "All domains unlocked",
        "warning": "GOD MODE grants access to all system knowledge"
    }
}
        ↓
UI displays GOD MODE badge (⚡ ALL DOMAINS ⚡)
```

---

## 🔍 Audit Trail Design

### Audit Trail Requirements

**1. What Gets Logged:**
- ✅ All user authentication events (login, logout, failed attempts)
- ✅ Domain switches and GOD MODE activations
- ✅ Knowledge base queries (query text, domain, result count, response time)
- ✅ Tool executions (name, args, result, duration, success/failure)
- ✅ OpenAI/Claude API calls (endpoint, tokens, duration, cost estimate)
- ✅ Database operations on sensitive tables (conversations, messages, KB docs)
- ✅ Configuration changes (system settings, domain registry updates)
- ✅ Message send/receive events (conversation_id, tokens, timestamp)
- ✅ Context card operations (create, update, delete, relevance scoring)
- ✅ Error events (exceptions, API failures, timeouts)

**2. Audit Trail Architecture:**

```
Application Event
        ↓
┌─────────────────────────────────────────────────┐
│ MULTI-LAYER LOGGING                             │
│                                                 │
│ Layer 1: Application Logger (Logger.php)        │
│     • Monolog → JSON → stderr                   │
│     • operations.log file                       │
│     • Context enrichment                        │
│                                                 │
│ Layer 2: Database Audit Tables                  │
│     • system_audit_log (generic events)         │
│     • {module}_audit_log (domain-specific)      │
│     • ai_kb_domain_query_log (KB queries)       │
│     • agent_tool_calls (tool executions)        │
│                                                 │
│ Layer 3: External Log Aggregation               │
│     • Syslog forwarding (optional)              │
│     • CloudWatch/Datadog (optional)             │
│     • SIEM integration (optional)               │
└─────────────────────────────────────────────────┘
```

**3. Retention Policies:**

| Log Type                  | Retention Period | Archive Strategy          |
|---------------------------|------------------|---------------------------|
| Application logs (stderr) | 30 days          | Rotate daily, compress    |
| operations.log            | 90 days          | Rotate weekly, compress   |
| chat.log                  | 90 days          | Rotate weekly, compress   |
| system_audit_log          | 1 year           | Partition by month        |
| ai_kb_domain_query_log    | 6 months         | Archive to S3/Glacier     |
| agent_tool_calls          | 6 months         | Partition by quarter      |
| {module}_audit_log        | 1 year           | Depends on compliance req |
| GOD MODE activations      | 3 years          | Never delete (compliance) |

**4. Query Performance Optimization:**

```sql
-- All audit tables have these indexes for fast queries:

INDEX idx_created_at (created_at)           -- Time-range queries
INDEX idx_user_id (user_id)                 -- User activity tracking
INDEX idx_conversation_id (conversation_id) -- Conversation audit trails
INDEX idx_action (action)                   -- Event type filtering
INDEX idx_severity (severity)               -- Alert filtering
INDEX idx_composite (user_id, created_at)   -- Combined lookups

-- Example fast query:
SELECT * FROM system_audit_log
WHERE user_id = 123
  AND created_at >= '2025-10-01'
  AND action IN ('domain_switch', 'god_mode_enable')
ORDER BY created_at DESC
LIMIT 100;
-- Uses: idx_composite (user_id, created_at)
```

**5. Audit Log Analysis Views:**

```sql
-- Recent GOD MODE activations (security monitoring)
CREATE OR REPLACE VIEW v_god_mode_audit AS
SELECT
    sal.audit_id,
    sal.user_id,
    u.email as user_email,
    sal.entity_id as conversation_id,
    sal.ip_address,
    sal.user_agent,
    sal.created_at
FROM system_audit_log sal
JOIN users u ON sal.user_id = u.id
WHERE sal.action = 'god_mode_enable'
ORDER BY sal.created_at DESC;

-- Domain usage statistics (business analytics)
CREATE OR REPLACE VIEW v_domain_usage_analytics AS
SELECT
    dr.domain_name,
    COUNT(DISTINCT qlog.conversation_id) as unique_conversations,
    COUNT(qlog.log_id) as total_queries,
    AVG(qlog.response_time_ms) as avg_response_ms,
    AVG(qlog.result_count) as avg_results,
    SUM(CASE WHEN qlog.god_mode_active = 1 THEN 1 ELSE 0 END) as god_mode_queries,
    DATE(qlog.created_at) as query_date
FROM ai_kb_domain_registry dr
LEFT JOIN ai_kb_domain_query_log qlog ON dr.domain_id = qlog.domain_id
WHERE qlog.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY dr.domain_id, DATE(qlog.created_at)
ORDER BY query_date DESC, total_queries DESC;

-- Tool execution success rate (reliability monitoring)
CREATE OR REPLACE VIEW v_tool_reliability AS
SELECT
    tool_name,
    COUNT(*) as total_executions,
    SUM(CASE WHEN result->>'$.success' = 'true' THEN 1 ELSE 0 END) as successful,
    SUM(CASE WHEN result->>'$.success' = 'false' THEN 1 ELSE 0 END) as failed,
    ROUND(SUM(CASE WHEN result->>'$.success' = 'true' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as success_rate,
    AVG(duration_ms) as avg_duration_ms,
    MAX(duration_ms) as max_duration_ms
FROM agent_tool_calls
WHERE executed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY tool_name
ORDER BY total_executions DESC;
```

---

## 🎯 Context Tracking

### Request Context Flow

```
HTTP Request arrives
        ↓
Generate/Extract request_id
    (X-Request-ID header or UUID)
        ↓
Logger::setContext([
    'request_id' => $requestId,
    'user_id' => $userId,
    'user_ip' => $_SERVER['REMOTE_ADDR'],
    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
    'conversation_id' => $conversationId,  // If available
    'domain_id' => $activeDomainId,        // If available
    'god_mode_enabled' => $godMode          // If available
])
        ↓
All Logger calls automatically include this context
        ↓
Example log output:
{
    "level": "info",
    "message": "KB search completed",
    "context": {
        "domain": "staff",
        "results": 7,
        "duration_ms": 120
    },
    "extra": {
        "request_id": "uuid-req-123",
        "conversation_id": "uuid-conv-456",
        "user_id": 42,
        "user_ip": "203.0.113.45",
        "domain_id": 2,
        "god_mode_enabled": false,
        "timestamp": "2025-10-29T12:34:56+00:00"
    }
}
```

### Correlation ID Usage

**Purpose:** Track a single request through multiple services/components

**Pattern:**
```php
// Generate at entry point
$requestId = $_SERVER['HTTP_X_REQUEST_ID'] ?? Uuid::uuid4()->toString();

// Pass through all layers
Logger::setContext(['request_id' => $requestId]);

// Database queries include it
$db->execute("INSERT INTO agent_messages (..., request_id) VALUES (?, ?)",
    [$content, $requestId]);

// API responses include it
header("X-Request-ID: $requestId");

// Tool executions logged with it
Logger::logTool($toolName, $args, $result, $duration, $success);
// Output includes: "request_id": "uuid-req-123"
```

**Benefits:**
- Trace entire request lifecycle across logs
- Debug issues by searching for single request_id
- Measure end-to-end latency
- Identify bottlenecks in multi-step operations

---

## ⚡ Performance & Monitoring

### Key Metrics Tracked

**1. Application Metrics:**
```sql
-- agent_metrics_hourly captures:
• conversation_count       (INT)
• message_count            (INT)
• avg_response_time_ms     (INT)
• total_tokens             (BIGINT)
• error_count              (INT)
• api_call_count           (INT)
• cache_hit_rate           (DECIMAL)

-- Aggregated to daily, weekly, monthly views
```

**2. Domain Metrics:**
```sql
-- ai_kb_domain_usage_metrics captures:
• query_count_hourly       (INT)
• query_count_daily        (INT)
• avg_response_time_ms     (INT)
• total_results_returned   (BIGINT)
• unique_conversations     (INT)
• god_mode_query_count     (INT)
• last_updated             (TIMESTAMP)
```

**3. Real-time Monitoring Queries:**

```sql
-- Current system health
SELECT
    COUNT(DISTINCT ac.conversation_id) as active_conversations,
    COUNT(DISTINCT CASE WHEN ac.updated_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                   THEN ac.conversation_id END) as recent_activity,
    AVG(am.tokens) as avg_tokens_per_message,
    (SELECT COUNT(*) FROM agent_tool_calls WHERE executed_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)) as tools_last_hour
FROM agent_conversations ac
LEFT JOIN agent_messages am ON ac.conversation_id = am.conversation_id;

-- Domain performance comparison
SELECT * FROM v_domain_stats_live ORDER BY query_count_24h DESC;

-- GOD MODE usage
SELECT * FROM v_god_mode_overview;
```

**4. Performance Budgets:**

| Operation               | Target      | Warning | Critical |
|-------------------------|-------------|---------|----------|
| KB search               | < 200ms     | 300ms   | 500ms    |
| Embedding generation    | < 100ms     | 150ms   | 250ms    |
| LLM API call (no stream)| < 3000ms    | 5000ms  | 8000ms   |
| Database query          | < 50ms      | 100ms   | 200ms    |
| Full message processing | < 5000ms    | 8000ms  | 12000ms  |
| Domain switch           | < 100ms     | 150ms   | 300ms    |

**5. Alerting Thresholds:**

```php
// Example monitoring code (would run via cron)
$stats = $db->query("SELECT * FROM agent_metrics_hourly WHERE hour = DATE_FORMAT(NOW(), '%Y-%m-%d %H:00:00')");

if ($stats['error_count'] > 10) {
    Logger::critical('High error rate detected', [
        'error_count' => $stats['error_count'],
        'hour' => $stats['hour']
    ]);
    sendAlert('AI Agent: High error rate', $stats);
}

if ($stats['avg_response_time_ms'] > 5000) {
    Logger::warning('Slow response times', [
        'avg_ms' => $stats['avg_response_time_ms']
    ]);
}

if ($stats['cache_hit_rate'] < 0.70) {
    Logger::info('Low cache hit rate', [
        'hit_rate' => $stats['cache_hit_rate']
    ]);
}
```

---

## 🔐 Security Architecture

### Authentication & Authorization

**1. User Authentication:**
```
User login → credentials
        ↓
Password hash verification (bcrypt/argon2)
        ↓
Session creation
        ↓
JWT token generation (optional)
        ↓
Log to login_audit_log
        ↓
Set user context in Logger
```

**2. Role-Based Access Control (RBAC):**

```sql
-- User roles hierarchy
user
    ├── role: 'user'         (default, limited access)
    ├── role: 'staff'        (staff domain access)
    ├── role: 'admin'        (most domains, no GOD MODE)
    └── role: 'superadmin'   (GOD MODE capability)

-- Permission checks
FUNCTION canAccessDomain($userId, $domainId): bool {
    IF user.role = 'superadmin' THEN RETURN true;
    IF user.role = 'admin' AND domainId IN (1,2,3,4,5) THEN RETURN true;
    IF user.role = 'staff' AND domainId = 2 THEN RETURN true;
    IF domainId = 1 THEN RETURN true;  // Everyone can access global
    RETURN false;
}

FUNCTION canEnableGodMode($userId): bool {
    RETURN user.role = 'superadmin';
}
```

**3. API Security:**
- ✅ HTTPS required (TLS 1.2+)
- ✅ CORS headers configured
- ✅ Rate limiting (100 req/min per user)
- ✅ Request validation (input sanitization)
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS prevention (output escaping)
- ✅ CSRF tokens for state-changing operations

**4. Sensitive Data Handling:**

```php
// Automatic redaction in logs
Logger::sanitizeForLog([
    'password' => 'secret123',       // → '[REDACTED]'
    'api_key' => 'sk-abc123',        // → '[REDACTED]'
    'openai_key' => 'sk-xyz',        // → '[REDACTED]'
    'message' => 'User query text'   // → kept as-is (not sensitive)
]);

// Database encryption for sensitive fields
$encryptedApiKey = openssl_encrypt($apiKey, 'AES-256-CBC', $key, 0, $iv);
$db->execute("UPDATE config SET api_key = ? WHERE id = 1", [$encryptedApiKey]);

// Token expiration
$jwt = JWT::encode(['user_id' => $userId, 'exp' => time() + 3600], $secret);
```

---

## 🎨 Summary: What EXISTS vs What DOESN'T

### ✅ WHAT EXISTS (Comprehensive Logging Infrastructure)

**Application Layer:**
- ✅ `Logger.php` (288 lines) - Full Monolog wrapper with fallback
- ✅ Automatic context injection (request_id, conversation_id, user_id, IP, user-agent)
- ✅ JSON structured logging to stderr
- ✅ Multiple log levels (debug, info, warning, error, critical)
- ✅ Specialized log methods (logTool, logOpenAI)
- ✅ Sensitive data sanitization

**Database Layer:**
- ✅ **100+ audit/log/tracking tables** across all business domains
- ✅ **13 agent-specific tables** (conversations, messages, KB docs, tools, metrics)
- ✅ **ai_kb_domain_query_log** (NEW - multi-domain query logging with GOD MODE support)
- ✅ Real-time views (v_domain_stats_live, v_god_mode_overview)
- ✅ Stored procedures for audit logging (sp_log_domain_query)
- ✅ Comprehensive indexes for fast audit queries
- ✅ Retention policies and partitioning strategies

**Integration Points:**
- ✅ Logger injected into all Agent components (DB, Redis, OpenAI, Claude, SSE, Embeddings, Summarizer, ContextCards)
- ✅ Extensive logging calls throughout codebase
- ✅ Context tracking in Summarizer, Agent, all major operations
- ✅ Tool execution logging with duration and success/failure tracking

**Log Files:**
- ✅ `/ai-agent/logs/operations.log` (general operations)
- ✅ `/ai-agent/logs/chat.log` (chat-specific logs)
- ✅ `/ai-agent/logs/api-tests-*.log` (API test results)
- ✅ stderr output (JSON formatted, redirected to system logs)

---

### ❌ WHAT DOESN'T EXIST (Documentation Gaps)

**Architecture Documentation:**
- ❌ No `/ai-agent/docs/ARCHITECTURE.md` (UNTIL NOW - being created)
- ❌ No component architecture diagrams
- ❌ No data flow diagrams (visual)
- ❌ No system boundary documentation

**Logging Pipeline Documentation:**
- ❌ No logging layer architecture documentation
- ❌ No explanation of how logs flow through the system
- ❌ No log aggregation strategy documentation
- ❌ No retention policy documentation

**Multi-Domain Integration Guide:**
- ❌ No multi-domain logging integration guide
- ❌ No explanation of how ai_kb_domain_query_log works
- ❌ No GOD MODE logging behavior documentation
- ❌ No domain switching audit trail documentation

**Operational Guides:**
- ❌ No log analysis guide (how to query audit tables efficiently)
- ❌ No troubleshooting guide using logs
- ❌ No performance monitoring guide
- ❌ No alerting setup documentation

**Visual Documentation:**
- ❌ No PlantUML/Mermaid diagrams for architecture
- ❌ No sequence diagrams for key flows
- ❌ No entity relationship diagrams (ERD) for audit tables

---

## 📚 Related Documentation

**Deployment & Setup:**
- `MULTI_DOMAIN_DEPLOYMENT_SUCCESS.md` - Multi-domain system deployment guide
- `PRODUCTION_SETUP_COMPLETE_GUIDE.md` - Production setup instructions
- `DEPLOYMENT_README.md` - General deployment procedures
- `AI_AGENT_DASHBOARD_COMPLETE.md` - Dashboard features and usage

**Knowledge Base:**
- `_kb/ai-agent/MASTER_AI_AGENT_KB.md` - Comprehensive KB documentation
- `_kb/ai-agent/EXECUTIVE_SUMMARY.md` - High-level system overview
- `_kb/ai-agent/TOOLS-CATALOG.yaml` - MCP tools catalog

**Configuration:**
- `dbconfig.php` - Database configuration
- `.env.example` - Environment variables template
- `config.php` - Application configuration

---

## 🚀 Next Steps

### Immediate Actions:
1. ✅ **Architecture documentation created** (this file)
2. 📋 Create visual diagrams (Mermaid format)
3. 📋 Document log analysis best practices
4. 📋 Create troubleshooting guide using logs

### Integration Tasks:
1. 📋 Build Live Chat UI with domain switcher
2. 📋 Create domain management API endpoints
3. 📋 Integrate domain filtering into agent search
4. 📋 Test GOD MODE functionality end-to-end

### Monitoring Setup:
1. 📋 Configure log aggregation (CloudWatch/Datadog)
2. 📋 Set up alerting for critical events (GOD MODE, high error rates)
3. 📋 Create dashboards for domain usage analytics
4. 📋 Implement automated health checks

---

**Document Status:** ✅ COMPLETE
**Last Updated:** October 29, 2025
**Maintained By:** AI Agent Development Team
**Next Review:** November 2025
