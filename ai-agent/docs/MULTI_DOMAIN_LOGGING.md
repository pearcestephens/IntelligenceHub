# 📊 Multi-Domain Logging Integration Guide

**Version:** 1.0
**Last Updated:** October 29, 2025
**Purpose:** Document how multi-domain logging works with GOD MODE support

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Query Log Table Structure](#query-log-table-structure)
3. [Logging Workflow](#logging-workflow)
4. [GOD MODE Logging Behavior](#god-mode-logging-behavior)
5. [Integration with Agent System](#integration-with-agent-system)
6. [Analytics & Reporting](#analytics--reporting)
7. [Performance Considerations](#performance-considerations)
8. [Usage Examples](#usage-examples)

---

## 🎯 Overview

The multi-domain logging system tracks every knowledge base query with domain context, enabling:
- **Domain-specific analytics** (which domains are most used)
- **GOD MODE audit trail** (security compliance)
- **Performance monitoring** (response times per domain)
- **Usage patterns** (query frequency, result quality)

### Key Components

```
┌─────────────────────────────────────────────────────────────┐
│ ai_kb_domain_query_log                                      │
│   Primary query audit table                                 │
│   • Captures every KB search                                │
│   • Records domain context                                  │
│   • Tracks GOD MODE usage                                   │
│   • Measures response times                                 │
└─────────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ ai_kb_domain_usage_metrics                                  │
│   Aggregated metrics per domain                             │
│   • Hourly/daily query counts                               │
│   • Average response times                                  │
│   • Total results returned                                  │
│   • Auto-updated via stored procedure                       │
└─────────────────────────────────────────────────────────────┘
                         ↓
┌─────────────────────────────────────────────────────────────┐
│ agent_conversations (enhanced)                              │
│   Conversation-level domain tracking                        │
│   • active_domain_id (current domain)                       │
│   • god_mode_enabled (boolean flag)                         │
│   • domain_switch_count (audit counter)                     │
└─────────────────────────────────────────────────────────────┘
```

---

## 🗄️ Query Log Table Structure

### Table Definition

```sql
CREATE TABLE ai_kb_domain_query_log (
    log_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    domain_id INT,
    conversation_id CHAR(36),
    query_text TEXT NOT NULL,
    result_count INT UNSIGNED NOT NULL DEFAULT 0,
    response_time_ms INT UNSIGNED,
    god_mode_active TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_domain_time (domain_id, created_at),
    INDEX idx_conversation (conversation_id),
    INDEX idx_god_mode (god_mode_active, created_at),
    INDEX idx_created (created_at),

    CONSTRAINT fk_query_domain
        FOREIGN KEY (domain_id)
        REFERENCES ai_kb_domain_registry(domain_id)
        ON DELETE SET NULL,

    CONSTRAINT fk_query_conversation
        FOREIGN KEY (conversation_id)
        REFERENCES agent_conversations(conversation_id)
        ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Field Descriptions

| Field             | Type               | Purpose                                      |
|-------------------|--------------------|----------------------------------------------|
| `log_id`          | BIGINT PK          | Unique identifier for each query log entry   |
| `domain_id`       | INT FK             | Which domain was active (1-6, NULL if GOD MODE cross-domain) |
| `conversation_id` | CHAR(36) FK        | Links to specific conversation               |
| `query_text`      | TEXT               | User's search query (for analytics)          |
| `result_count`    | INT UNSIGNED       | Number of KB documents returned              |
| `response_time_ms`| INT UNSIGNED       | Query execution time in milliseconds         |
| `god_mode_active` | TINYINT(1)         | Was GOD MODE enabled? (0 = no, 1 = yes)      |
| `created_at`      | TIMESTAMP          | When the query was executed                  |

### Index Strategy

**1. `idx_domain_time (domain_id, created_at)`**
- **Purpose:** Fast domain-specific time-range queries
- **Use Case:** "Show me all queries in 'staff' domain today"
- **Query Pattern:**
  ```sql
  SELECT * FROM ai_kb_domain_query_log
  WHERE domain_id = 2 AND created_at >= CURDATE()
  ORDER BY created_at DESC;
  ```

**2. `idx_conversation (conversation_id)`**
- **Purpose:** Audit trail for specific conversation
- **Use Case:** "What queries were made in this conversation?"
- **Query Pattern:**
  ```sql
  SELECT query_text, result_count, response_time_ms
  FROM ai_kb_domain_query_log
  WHERE conversation_id = 'uuid-conv-123'
  ORDER BY created_at;
  ```

**3. `idx_god_mode (god_mode_active, created_at)`**
- **Purpose:** Security auditing of GOD MODE usage
- **Use Case:** "Show all GOD MODE queries in the last 24 hours"
- **Query Pattern:**
  ```sql
  SELECT * FROM ai_kb_domain_query_log
  WHERE god_mode_active = 1
    AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
  ORDER BY created_at DESC;
  ```

**4. `idx_created (created_at)`**
- **Purpose:** Time-based queries and partitioning
- **Use Case:** "Archive queries older than 6 months"

---

## 🔄 Logging Workflow

### Step-by-Step Flow

```
1. User submits query
        ↓
2. Agent::searchKnowledge($query, $conversationId)
        ↓
3. Load conversation context
   $conversation = $db->query("
       SELECT active_domain_id, god_mode_enabled
       FROM agent_conversations
       WHERE conversation_id = ?", [$conversationId]
   );
        ↓
4. Execute KB search
   $startTime = microtime(true);
   $results = $embeddings->search($query, [
       'domain_id' => $conversation['active_domain_id'],
       'god_mode' => $conversation['god_mode_enabled']
   ]);
   $responseTime = (microtime(true) - $startTime) * 1000; // ms
        ↓
5. Log query via stored procedure
   CALL sp_log_domain_query(
       domain_id:         $conversation['active_domain_id'],
       conversation_id:   $conversationId,
       query_text:        $query,
       result_count:      count($results),
       response_time_ms:  $responseTime,
       god_mode_active:   $conversation['god_mode_enabled']
   );
        ↓
6. Stored procedure inserts log + updates metrics
   BEGIN
       -- Insert query log
       INSERT INTO ai_kb_domain_query_log (...)
       VALUES (...);

       -- Update domain metrics
       UPDATE ai_kb_domain_usage_metrics
       SET query_count_hourly = query_count_hourly + 1,
           query_count_daily = query_count_daily + 1,
           total_results_returned = total_results_returned + result_count,
           avg_response_time_ms = (
               (avg_response_time_ms * query_count_hourly + response_time_ms) /
               (query_count_hourly + 1)
           ),
           last_updated = NOW()
       WHERE domain_id = p_domain_id;
   END
        ↓
7. Application logger also logs
   Logger::debug('KB search completed', [
       'conversation_id' => $conversationId,
       'domain_id' => $domainId,
       'query' => $query,
       'results' => count($results),
       'response_ms' => $responseTime,
       'god_mode' => $godMode
   ]);
        ↓
8. Return results to user
```

### Dual Logging Strategy

**Why log in TWO places?**

**1. Database (`ai_kb_domain_query_log`)**
- ✅ Structured data for analytics
- ✅ Fast aggregation queries
- ✅ Long-term storage (6 months+)
- ✅ Domain-specific reporting
- ✅ Compliance audit trail

**2. Application Logs (`Logger::debug`)**
- ✅ Real-time debugging
- ✅ Full context (IP, user-agent, request_id)
- ✅ Centralized log aggregation (CloudWatch, Datadog)
- ✅ Correlation with other application events
- ✅ Immediate alerting on errors

**They complement each other:**
```
Database Log: "Domain 2 had 150 queries today"
Application Log: "Query 'user search' by user 42 from IP 203.0.113.45 at 12:34:56 returned 5 results in 120ms"
```

---

## ⚡ GOD MODE Logging Behavior

### What Changes with GOD MODE?

**Normal Mode (domain_id = 1-5, god_mode = 0):**
```sql
-- Query filters by active domain
SELECT * FROM agent_kb_docs docs
JOIN ai_kb_doc_domain_map ddm ON docs.id = ddm.doc_id
WHERE ddm.domain_id = 2  -- Only staff domain docs
ORDER BY ddm.relevance_score DESC
LIMIT 10;

-- Logged as:
INSERT INTO ai_kb_domain_query_log (
    domain_id,        -- 2 (staff)
    conversation_id,  -- uuid-conv-123
    query_text,       -- "payroll process"
    result_count,     -- 7 documents
    response_time_ms, -- 120
    god_mode_active   -- 0 (FALSE)
);
```

**GOD MODE (domain_id = 6, god_mode = 1):**
```sql
-- Query accesses ALL documents
SELECT * FROM agent_kb_docs docs
LEFT JOIN ai_kb_doc_domain_map ddm ON docs.id = ddm.doc_id
WHERE 1 = 1  -- No domain filter!
ORDER BY docs.relevance_base DESC
LIMIT 10;

-- Logged as:
INSERT INTO ai_kb_domain_query_log (
    domain_id,        -- 6 (superadmin)
    conversation_id,  -- uuid-conv-456
    query_text,       -- "system architecture"
    result_count,     -- 342 documents (all docs accessible)
    response_time_ms, -- 250 (slower due to more docs)
    god_mode_active   -- 1 (TRUE) ← Important for security audit
);
```

### GOD MODE Audit Queries

**Security team needs to monitor GOD MODE usage:**

```sql
-- Who activated GOD MODE in the last 30 days?
SELECT
    sal.user_id,
    u.email,
    sal.ip_address,
    sal.created_at as activation_time,
    sal.entity_id as conversation_id
FROM system_audit_log sal
JOIN users u ON sal.user_id = u.id
WHERE sal.action = 'god_mode_enable'
  AND sal.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
ORDER BY sal.created_at DESC;

-- What queries were made in GOD MODE?
SELECT
    qlog.query_text,
    qlog.result_count,
    qlog.response_time_ms,
    qlog.created_at,
    ac.user_id,
    u.email
FROM ai_kb_domain_query_log qlog
JOIN agent_conversations ac ON qlog.conversation_id = ac.conversation_id
JOIN users u ON ac.user_id = u.id
WHERE qlog.god_mode_active = 1
  AND qlog.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY qlog.created_at DESC;

-- GOD MODE usage frequency by user
SELECT
    u.email,
    COUNT(DISTINCT qlog.conversation_id) as conversations_in_god_mode,
    COUNT(qlog.log_id) as total_god_mode_queries,
    MIN(qlog.created_at) as first_use,
    MAX(qlog.created_at) as last_use
FROM ai_kb_domain_query_log qlog
JOIN agent_conversations ac ON qlog.conversation_id = ac.conversation_id
JOIN users u ON ac.user_id = u.id
WHERE qlog.god_mode_active = 1
  AND qlog.created_at >= DATE_SUB(NOW(), INTERVAL 90 DAY)
GROUP BY u.email
ORDER BY total_god_mode_queries DESC;
```

### Compliance Requirements

**GOD MODE logs must:**
- ✅ Be retained for 3 years (regulatory compliance)
- ✅ Include user identification (who activated it)
- ✅ Include timestamp (when it was used)
- ✅ Include query context (what they searched for)
- ✅ Be immutable (no deletions, only archival)
- ✅ Be accessible for audit (indexed, fast queries)
- ✅ Trigger alerts on activation (real-time notification)

**Automated compliance check:**
```sql
-- Verify GOD MODE logs are complete
SELECT
    DATE(created_at) as log_date,
    COUNT(*) as god_mode_queries,
    COUNT(DISTINCT conversation_id) as unique_conversations,
    AVG(result_count) as avg_results,
    MAX(response_time_ms) as max_response_ms
FROM ai_kb_domain_query_log
WHERE god_mode_active = 1
  AND created_at >= DATE_SUB(NOW(), INTERVAL 3 YEAR)
GROUP BY DATE(created_at)
ORDER BY log_date DESC;
```

---

## 🔌 Integration with Agent System

### PHP Integration Pattern

**In `Agent.php` (simplified):**

```php
public function searchKnowledge(string $query, string $conversationId): array
{
    // 1. Load conversation context
    $conversation = $this->db->fetchOne(
        "SELECT active_domain_id, god_mode_enabled
         FROM agent_conversations
         WHERE conversation_id = ?",
        [$conversationId]
    );

    $domainId = $conversation['active_domain_id'];
    $godMode = (bool)$conversation['god_mode_enabled'];

    // 2. Set logging context
    Logger::setContext([
        'conversation_id' => $conversationId,
        'domain_id' => $domainId,
        'god_mode_enabled' => $godMode
    ]);

    Logger::info('KB search initiated', [
        'query' => substr($query, 0, 100),  // Truncate for logs
        'domain' => $this->getDomainName($domainId),
        'god_mode' => $godMode
    ]);

    // 3. Execute search with timing
    $startTime = microtime(true);

    try {
        $results = $this->embeddings->search($query, [
            'domain_id' => $domainId,
            'god_mode' => $godMode,
            'limit' => 10
        ]);

        $responseTimeMs = (int)((microtime(true) - $startTime) * 1000);
        $resultCount = count($results);

        // 4. Log to database via stored procedure
        $this->db->execute(
            "CALL sp_log_domain_query(?, ?, ?, ?, ?, ?)",
            [
                $domainId,
                $conversationId,
                $query,
                $resultCount,
                $responseTimeMs,
                $godMode ? 1 : 0
            ]
        );

        // 5. Log to application logger
        Logger::info('KB search completed', [
            'result_count' => $resultCount,
            'response_ms' => $responseTimeMs,
            'cache_hit' => $results['from_cache'] ?? false
        ]);

        return $results;

    } catch (Exception $e) {
        Logger::error('KB search failed', [
            'error' => $e->getMessage(),
            'query' => substr($query, 0, 100),
            'domain_id' => $domainId
        ]);

        // Still log the failed attempt
        $this->db->execute(
            "INSERT INTO ai_kb_domain_query_log
             (domain_id, conversation_id, query_text, result_count, response_time_ms, god_mode_active)
             VALUES (?, ?, ?, 0, NULL, ?)",
            [$domainId, $conversationId, $query, $godMode ? 1 : 0]
        );

        throw $e;
    }
}

private function getDomainName(int $domainId): string
{
    static $domains = [
        1 => 'global',
        2 => 'staff',
        3 => 'web',
        4 => 'gpt',
        5 => 'wiki',
        6 => 'superadmin'
    ];
    return $domains[$domainId] ?? 'unknown';
}
```

### Stored Procedure Implementation

```sql
DELIMITER $$

CREATE PROCEDURE sp_log_domain_query(
    IN p_domain_id INT,
    IN p_conversation_id CHAR(36),
    IN p_query_text TEXT,
    IN p_result_count INT UNSIGNED,
    IN p_response_time_ms INT UNSIGNED,
    IN p_god_mode_active TINYINT(1)
)
BEGIN
    -- Insert query log
    INSERT INTO ai_kb_domain_query_log (
        domain_id,
        conversation_id,
        query_text,
        result_count,
        response_time_ms,
        god_mode_active
    ) VALUES (
        p_domain_id,
        p_conversation_id,
        p_query_text,
        p_result_count,
        p_response_time_ms,
        p_god_mode_active
    );

    -- Update domain usage metrics
    UPDATE ai_kb_domain_usage_metrics
    SET
        query_count_hourly = query_count_hourly + 1,
        query_count_daily = query_count_daily + 1,
        total_results_returned = total_results_returned + p_result_count,
        avg_response_time_ms = (
            (avg_response_time_ms * (query_count_hourly - 1) + p_response_time_ms) /
            query_count_hourly
        ),
        last_updated = NOW()
    WHERE domain_id = p_domain_id;

    -- If no row exists, insert initial metrics
    IF ROW_COUNT() = 0 THEN
        INSERT INTO ai_kb_domain_usage_metrics (
            domain_id,
            query_count_hourly,
            query_count_daily,
            avg_response_time_ms,
            total_results_returned,
            last_updated
        ) VALUES (
            p_domain_id,
            1,
            1,
            p_response_time_ms,
            p_result_count,
            NOW()
        );
    END IF;
END$$

DELIMITER ;
```

---

## 📊 Analytics & Reporting

### Pre-built Views

**1. Domain Usage Dashboard:**
```sql
CREATE OR REPLACE VIEW v_domain_usage_dashboard AS
SELECT
    dr.domain_id,
    dr.domain_name,
    dr.display_name,
    dum.query_count_daily as queries_today,
    dum.avg_response_time_ms,
    COUNT(DISTINCT qlog.conversation_id) as active_conversations_7d,
    SUM(CASE WHEN qlog.god_mode_active = 1 THEN 1 ELSE 0 END) as god_mode_queries_7d,
    AVG(qlog.result_count) as avg_results_per_query,
    MAX(qlog.created_at) as last_query_at
FROM ai_kb_domain_registry dr
LEFT JOIN ai_kb_domain_usage_metrics dum ON dr.domain_id = dum.domain_id
LEFT JOIN ai_kb_domain_query_log qlog ON dr.domain_id = qlog.domain_id
    AND qlog.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY dr.domain_id
ORDER BY dum.query_count_daily DESC;
```

**2. Query Performance Trends:**
```sql
CREATE OR REPLACE VIEW v_query_performance_trends AS
SELECT
    dr.domain_name,
    DATE(qlog.created_at) as query_date,
    COUNT(*) as total_queries,
    AVG(qlog.response_time_ms) as avg_response_ms,
    MIN(qlog.response_time_ms) as min_response_ms,
    MAX(qlog.response_time_ms) as max_response_ms,
    PERCENTILE_CONT(0.95) WITHIN GROUP (ORDER BY qlog.response_time_ms) as p95_response_ms,
    AVG(qlog.result_count) as avg_results,
    SUM(CASE WHEN qlog.response_time_ms > 500 THEN 1 ELSE 0 END) as slow_queries
FROM ai_kb_domain_registry dr
LEFT JOIN ai_kb_domain_query_log qlog ON dr.domain_id = qlog.domain_id
WHERE qlog.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY dr.domain_id, DATE(qlog.created_at)
ORDER BY query_date DESC, total_queries DESC;
```

**3. GOD MODE Security Audit:**
```sql
CREATE OR REPLACE VIEW v_god_mode_security_audit AS
SELECT
    qlog.log_id,
    qlog.query_text,
    qlog.result_count,
    qlog.response_time_ms,
    qlog.created_at,
    ac.user_id,
    u.email as user_email,
    u.role as user_role,
    ac.conversation_id,
    ac.created_at as conversation_started,
    ac.domain_switch_count
FROM ai_kb_domain_query_log qlog
JOIN agent_conversations ac ON qlog.conversation_id = ac.conversation_id
JOIN users u ON ac.user_id = u.id
WHERE qlog.god_mode_active = 1
ORDER BY qlog.created_at DESC;
```

### Common Analytical Queries

**Query 1: Most popular search terms per domain**
```sql
SELECT
    dr.domain_name,
    qlog.query_text,
    COUNT(*) as query_frequency,
    AVG(qlog.result_count) as avg_results,
    AVG(qlog.response_time_ms) as avg_response_ms
FROM ai_kb_domain_query_log qlog
JOIN ai_kb_domain_registry dr ON qlog.domain_id = dr.domain_id
WHERE qlog.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY dr.domain_id, qlog.query_text
HAVING query_frequency > 5  -- Only frequent queries
ORDER BY dr.domain_name, query_frequency DESC
LIMIT 50;
```

**Query 2: Domain performance comparison**
```sql
SELECT
    dr.domain_name,
    COUNT(qlog.log_id) as total_queries,
    AVG(qlog.response_time_ms) as avg_response_ms,
    AVG(qlog.result_count) as avg_results,
    SUM(CASE WHEN qlog.result_count = 0 THEN 1 ELSE 0 END) as zero_result_queries,
    (SUM(CASE WHEN qlog.result_count = 0 THEN 1 ELSE 0 END) * 100.0 / COUNT(*)) as zero_result_rate
FROM ai_kb_domain_registry dr
LEFT JOIN ai_kb_domain_query_log qlog ON dr.domain_id = qlog.domain_id
WHERE qlog.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY dr.domain_id
ORDER BY total_queries DESC;
```

**Query 3: User query patterns**
```sql
SELECT
    u.email,
    dr.domain_name,
    COUNT(qlog.log_id) as queries_in_domain,
    AVG(qlog.response_time_ms) as avg_response_ms,
    SUM(qlog.result_count) as total_results_received,
    MAX(qlog.created_at) as last_query_at
FROM ai_kb_domain_query_log qlog
JOIN agent_conversations ac ON qlog.conversation_id = ac.conversation_id
JOIN users u ON ac.user_id = u.id
JOIN ai_kb_domain_registry dr ON qlog.domain_id = dr.domain_id
WHERE qlog.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY u.id, dr.domain_id
ORDER BY u.email, queries_in_domain DESC;
```

---

## ⚡ Performance Considerations

### Write Performance

**Concern:** Every KB query writes to database (could be 1000+ writes/day)

**Optimizations:**
1. ✅ **Stored Procedure:** Single call writes log + updates metrics atomically
2. ✅ **Indexes:** Covering indexes for fast lookups, no full table scans
3. ✅ **InnoDB Engine:** Row-level locking, concurrent writes don't block
4. ✅ **Async Logging (Future):** Queue writes to Redis, batch-insert every 5s

### Read Performance

**Concern:** Analytics queries on large log tables (millions of rows)

**Optimizations:**
1. ✅ **Time-based indexes:** `idx_domain_time` for range queries
2. ✅ **Pre-built views:** `v_domain_usage_dashboard` cached
3. ✅ **Partitioning (Future):** Partition by month for fast archive
4. ✅ **Materialized views (Future):** Pre-aggregate daily stats

### Retention & Archival

**Strategy:**
- **Hot Data:** Last 30 days in main table (fast queries)
- **Warm Data:** 1-6 months in main table (indexed)
- **Cold Data:** 6 months+ archived to separate table or S3

**Archival Script (run monthly):**
```sql
-- Move old logs to archive table
INSERT INTO ai_kb_domain_query_log_archive
SELECT * FROM ai_kb_domain_query_log
WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);

-- Delete from main table
DELETE FROM ai_kb_domain_query_log
WHERE created_at < DATE_SUB(NOW(), INTERVAL 6 MONTH);

-- Optimize table (reclaim space)
OPTIMIZE TABLE ai_kb_domain_query_log;
```

### Monitoring Query

**Check table growth:**
```sql
SELECT
    TABLE_NAME,
    ROUND(DATA_LENGTH / 1024 / 1024, 2) as size_mb,
    TABLE_ROWS as row_count,
    ROUND(DATA_LENGTH / TABLE_ROWS, 2) as avg_row_bytes
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'jcepnzzkmj'
  AND TABLE_NAME LIKE '%domain_query_log%';
```

---

## 💻 Usage Examples

### Example 1: Log a Normal Query

```php
// User searches in "staff" domain (domain_id = 2)
$domainId = 2;
$conversationId = 'uuid-conv-123';
$query = 'payroll processing steps';
$godMode = false;

// Execute search
$startTime = microtime(true);
$results = $embeddings->search($query, ['domain_id' => $domainId]);
$responseTimeMs = (int)((microtime(true) - $startTime) * 1000);

// Log query
$db->execute(
    "CALL sp_log_domain_query(?, ?, ?, ?, ?, ?)",
    [
        $domainId,              // 2
        $conversationId,        // 'uuid-conv-123'
        $query,                 // 'payroll processing steps'
        count($results),        // 7 (results found)
        $responseTimeMs,        // 120 (ms)
        $godMode ? 1 : 0        // 0 (normal mode)
    ]
);

// Result in database:
// log_id: 1
// domain_id: 2
// conversation_id: 'uuid-conv-123'
// query_text: 'payroll processing steps'
// result_count: 7
// response_time_ms: 120
// god_mode_active: 0
// created_at: 2025-10-29 12:34:56
```

### Example 2: Log a GOD MODE Query

```php
// Superadmin activates GOD MODE (domain_id = 6)
$domainId = 6;
$conversationId = 'uuid-conv-456';
$query = 'system architecture components';
$godMode = true;

// Execute search (no domain filter)
$startTime = microtime(true);
$results = $embeddings->search($query, ['god_mode' => true]);
$responseTimeMs = (int)((microtime(true) - $startTime) * 1000);

// Log query
$db->execute(
    "CALL sp_log_domain_query(?, ?, ?, ?, ?, ?)",
    [
        $domainId,              // 6 (superadmin)
        $conversationId,        // 'uuid-conv-456'
        $query,                 // 'system architecture components'
        count($results),        // 342 (all docs accessible)
        $responseTimeMs,        // 250 (ms, slower due to more docs)
        1                       // 1 (GOD MODE active)
    ]
);

// Result in database:
// log_id: 2
// domain_id: 6
// conversation_id: 'uuid-conv-456'
// query_text: 'system architecture components'
// result_count: 342
// response_time_ms: 250
// god_mode_active: 1  ← Important for audit!
// created_at: 2025-10-29 12:35:10
```

### Example 3: Query Logs for Analysis

```php
// Get domain usage stats
$stats = $db->query("SELECT * FROM v_domain_usage_dashboard");

foreach ($stats as $domain) {
    echo "{$domain['display_name']}: {$domain['queries_today']} queries today\n";
    echo "  Avg response: {$domain['avg_response_time_ms']}ms\n";
    echo "  Active conversations: {$domain['active_conversations_7d']}\n";
    echo "  GOD MODE queries: {$domain['god_mode_queries_7d']}\n\n";
}

// Output:
// Staff Portal: 45 queries today
//   Avg response: 120ms
//   Active conversations: 8
//   GOD MODE queries: 0
//
// GPT Documentation: 27 queries today
//   Avg response: 150ms
//   Active conversations: 5
//   GOD MODE queries: 0
//
// Superadmin: 3 queries today
//   Avg response: 250ms
//   Active conversations: 1
//   GOD MODE queries: 3
```

### Example 4: Security Audit Report

```php
// Generate GOD MODE usage report
$report = $db->query("
    SELECT
        u.email,
        COUNT(*) as total_god_mode_queries,
        MIN(qlog.created_at) as first_use,
        MAX(qlog.created_at) as last_use,
        GROUP_CONCAT(DISTINCT qlog.query_text ORDER BY qlog.created_at DESC SEPARATOR ' | ') as recent_queries
    FROM ai_kb_domain_query_log qlog
    JOIN agent_conversations ac ON qlog.conversation_id = ac.conversation_id
    JOIN users u ON ac.user_id = u.id
    WHERE qlog.god_mode_active = 1
      AND qlog.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY u.id
");

echo "GOD MODE Usage Report (Last 30 Days)\n";
echo "======================================\n\n";

foreach ($report as $user) {
    echo "User: {$user['email']}\n";
    echo "Total Queries: {$user['total_god_mode_queries']}\n";
    echo "First Use: {$user['first_use']}\n";
    echo "Last Use: {$user['last_use']}\n";
    echo "Recent Queries: {$user['recent_queries']}\n";
    echo "---\n\n";
}

// Output:
// GOD MODE Usage Report (Last 30 Days)
// ======================================
//
// User: admin@company.com
// Total Queries: 3
// First Use: 2025-10-29 12:35:10
// Last Use: 2025-10-29 14:22:45
// Recent Queries: system architecture components | database schema | logging infrastructure
// ---
```

---

## 🎯 Summary

### What Multi-Domain Logging Provides

✅ **Complete Audit Trail**
- Every KB query logged with domain context
- GOD MODE usage tracked for security compliance
- Immutable record for regulatory requirements

✅ **Performance Insights**
- Response time tracking per domain
- Identify slow queries and bottlenecks
- Optimize domain-specific relevance scoring

✅ **Usage Analytics**
- Which domains are most used
- Popular search terms per domain
- User behavior patterns

✅ **Security Monitoring**
- GOD MODE activations audited
- User access patterns tracked
- Anomaly detection support

### Integration Points

1. **Agent.php** → Calls `sp_log_domain_query()` after every KB search
2. **Logger.php** → Logs to application logs in parallel
3. **agent_conversations** → Tracks active_domain_id and god_mode_enabled
4. **Stored Procedure** → Atomically writes log + updates metrics
5. **Views** → Pre-aggregated analytics for dashboards

---

**Document Status:** ✅ COMPLETE
**Related Docs:**
- `/ai-agent/docs/ARCHITECTURE.md` - System architecture overview
- `/MULTI_DOMAIN_DEPLOYMENT_SUCCESS.md` - Multi-domain system deployment guide

**Last Updated:** October 29, 2025
