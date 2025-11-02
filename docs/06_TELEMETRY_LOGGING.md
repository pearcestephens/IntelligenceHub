# Telemetry & Logging Documentation

**Component:** AI Agent Observability System  
**Location:** `/assets/services/ai-agent/lib/Telemetry.php`  
**Database:** hdgwrzntwa (logging tables)  
**Status:** Production Ready ✅  

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Telemetry Class](#telemetry-class)
3. [Request Tracking](#request-tracking)
4. [Tool Call Logging](#tool-call-logging)
5. [Conversation Tracking](#conversation-tracking)
6. [Performance Metrics](#performance-metrics)
7. [Log Aggregation](#log-aggregation)
8. [Monitoring Queries](#monitoring-queries)
9. [Retention Policies](#retention-policies)

---

## Overview

The AI Agent implements comprehensive observability through:

- ✅ Request ID generation (UUID v4)
- ✅ Correlation across all operations
- ✅ Structured database logging
- ✅ Latency measurement (millisecond precision)
- ✅ Token counting and cost tracking
- ✅ Error capture with stack traces
- ✅ Tool execution telemetry

**Key Metrics Tracked:**
- Request latency (API response time)
- Tool execution time (per tool)
- Token usage (input/output/total)
- Cost attribution (NZD cents)
- Error rates and types
- Conversation length
- Tool success rates

---

## Telemetry Class

**Location:** `/assets/services/ai-agent/lib/Telemetry.php`

### Class Overview

```php
<?php
declare(strict_types=1);

class Telemetry
{
    private PDO $pdo;
    private string $requestId;
    private float $startTime;
    
    public function __construct(PDO $pdo, ?string $requestId = null)
    {
        $this->pdo = $pdo;
        $this->requestId = $requestId ?? $this->generateRequestId();
        $this->startTime = microtime(true);
    }
    
    // Methods documented below...
}
```

### Core Methods

#### 1. generateRequestId()

**Purpose:** Generate unique request identifier (UUID v4)

```php
private function generateRequestId(): string
{
    return sprintf(
        '%04x%04x%04x%04x%04x%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}
```

**Example Output:**
```
48e8429615a4d75aa6d5dfd568291b5e
```

**Use Cases:**
- Correlate logs across services
- Track request lifecycle
- Debug distributed operations
- Link requests to conversations

---

#### 2. logRequest()

**Purpose:** Log API request details

**Signature:**
```php
public function logRequest(
    string $endpoint,
    string $method = 'POST',
    ?int $conversationId = null,
    ?int $messageId = null,
    ?string $sessionKey = null
): int
```

**Parameters:**
- `endpoint`: API endpoint path (e.g., "/assets/services/ai-agent/api/chat.php")
- `method`: HTTP method (default: POST)
- `conversationId`: Optional FK to ai_conversations
- `messageId`: Optional FK to ai_conversation_messages
- `sessionKey`: Optional session identifier

**Returns:** `ai_agent_requests.id` (auto-increment)

**Example Usage:**
```php
$telemetry = new Telemetry($pdo);
$requestDbId = $telemetry->logRequest(
    endpoint: '/assets/services/ai-agent/api/chat.php',
    method: 'POST',
    conversationId: 17,
    messageId: 22,
    sessionKey: 'user-123'
);
```

**Database Record:**
```sql
INSERT INTO ai_agent_requests (
    request_id, endpoint, method, conversation_id, message_id, 
    session_key, ip, user_agent, created_at
) VALUES (
    '48e8429615a4d75aa6d5dfd568291b5e',
    '/assets/services/ai-agent/api/chat.php',
    'POST',
    17,
    22,
    'user-123',
    '45.32.241.246',
    'curl/7.68.0',
    '2025-11-02 23:00:00'
);
```

---

#### 3. updateRequestStatus()

**Purpose:** Update request with completion status

**Signature:**
```php
public function updateRequestStatus(
    int $requestDbId,
    int $statusCode,
    ?string $errorCode = null,
    ?string $errorMessage = null
): void
```

**Parameters:**
- `requestDbId`: ID from logRequest()
- `statusCode`: HTTP status code (200, 400, 500, etc.)
- `errorCode`: Optional error code (CHAT_FAILURE, TOOL_ERROR, etc.)
- `errorMessage`: Optional error message

**Example Usage:**
```php
// Success
$telemetry->updateRequestStatus(
    requestDbId: $requestDbId,
    statusCode: 200
);

// Error
$telemetry->updateRequestStatus(
    requestDbId: $requestDbId,
    statusCode: 500,
    errorCode: 'CHAT_FAILURE',
    errorMessage: 'OpenAI API timeout'
);
```

**Database Update:**
```sql
UPDATE ai_agent_requests 
SET 
    status_code = 200,
    latency_ms = 1115,
    error_code = NULL,
    error_message = NULL
WHERE id = ?;
```

---

#### 4. logToolCall()

**Purpose:** Log tool invocation start

**Signature:**
```php
public function logToolCall(
    string $toolName,
    array $args,
    ?int $agentRequestId = null
): int
```

**Parameters:**
- `toolName`: Tool identifier (fs.read, db.select, etc.)
- `args`: Tool arguments (stored as JSON)
- `agentRequestId`: Optional FK to ai_agent_requests

**Returns:** `ai_tool_calls.id` (auto-increment)

**Example Usage:**
```php
$toolCallId = $telemetry->logToolCall(
    toolName: 'fs.read',
    args: ['path' => 'assets/config.php'],
    agentRequestId: $requestDbId
);
```

**Database Record:**
```sql
INSERT INTO ai_tool_calls (
    agent_request_id, tool_name, status, args, created_at
) VALUES (
    42,
    'fs.read',
    'started',
    '{"path":"assets/config.php"}',
    '2025-11-02 23:00:01'
);
```

---

#### 5. updateToolCallStatus()

**Purpose:** Update tool call with result

**Signature:**
```php
public function updateToolCallStatus(
    int $toolCallId,
    string $status,
    ?array $result = null,
    ?string $error = null,
    ?int $latencyMs = null
): void
```

**Parameters:**
- `toolCallId`: ID from logToolCall()
- `status`: 'ok', 'error', 'timeout'
- `result`: Optional result data (stored as JSON in ai_tool_results)
- `error`: Optional error message
- `latencyMs`: Optional execution time

**Example Usage (Success):**
```php
$telemetry->updateToolCallStatus(
    toolCallId: $toolCallId,
    status: 'ok',
    result: [
        'path' => '/home/.../assets/config.php',
        'bytes' => 1024,
        'content' => '<?php...'
    ],
    latencyMs: 5
);
```

**Example Usage (Error):**
```php
$telemetry->updateToolCallStatus(
    toolCallId: $toolCallId,
    status: 'error',
    error: 'FILE_NOT_FOUND: /invalid/path.php',
    latencyMs: 2
);
```

**Database Updates:**
```sql
-- Update ai_tool_calls
UPDATE ai_tool_calls 
SET 
    status = 'ok',
    latency_ms = 5,
    error = NULL,
    updated_at = NOW()
WHERE id = ?;

-- Insert ai_tool_results
INSERT INTO ai_tool_results (
    tool_call_id, result_data, created_at
) VALUES (
    ?,
    '{"path":"/home/.../assets/config.php","bytes":1024,"content":"<?php..."}',
    NOW()
);
```

---

#### 6. getElapsedMs()

**Purpose:** Get request elapsed time

**Signature:**
```php
public function getElapsedMs(): int
```

**Returns:** Milliseconds since Telemetry object created

**Example Usage:**
```php
$telemetry = new Telemetry($pdo);

// ... do work ...

$latency = $telemetry->getElapsedMs();  // e.g., 1115
```

**Implementation:**
```php
public function getElapsedMs(): int
{
    return (int) round((microtime(true) - $this->startTime) * 1000);
}
```

---

#### 7. getRequestId()

**Purpose:** Get current request UUID

**Signature:**
```php
public function getRequestId(): string
```

**Example Usage:**
```php
$requestId = $telemetry->getRequestId();  // "48e8429615a4d75aa6d5dfd568291b5e"
```

---

## Request Tracking

### Full Request Lifecycle

```php
// 1. Initialize telemetry
$telemetry = new Telemetry($pdo);
$requestId = $telemetry->getRequestId();

try {
    // 2. Log request start
    $requestDbId = $telemetry->logRequest(
        endpoint: $_SERVER['PHP_SELF'],
        method: $_SERVER['REQUEST_METHOD'],
        sessionKey: $data['session_key'] ?? null
    );
    
    // 3. Do work (chat, tools, etc.)
    $result = doWork($data);
    
    // 4. Log success
    $telemetry->updateRequestStatus(
        requestDbId: $requestDbId,
        statusCode: 200
    );
    
    // 5. Return response
    envelope_success($result, $requestId);
    
} catch (Exception $e) {
    // 6. Log error
    $telemetry->updateRequestStatus(
        requestDbId: $requestDbId,
        statusCode: 500,
        errorCode: 'UNEXPECTED_ERROR',
        errorMessage: $e->getMessage()
    );
    
    // 7. Return error
    envelope_error(
        code: 'UNEXPECTED_ERROR',
        message: $e->getMessage(),
        requestId: $requestId
    );
}
```

### Request Correlation

All telemetry linked by `request_id`:

```sql
-- Find all data for a request
SELECT 
    ar.request_id,
    ar.endpoint,
    ar.status_code,
    ar.latency_ms,
    tc.tool_name,
    tc.status as tool_status,
    tc.latency_ms as tool_latency
FROM ai_agent_requests ar
LEFT JOIN ai_tool_calls tc ON tc.agent_request_id = ar.id
WHERE ar.request_id = '48e8429615a4d75aa6d5dfd568291b5e';
```

**Result:**
```
+----------------------------------+-------------------+-------------+------------+-----------+-------------+--------------+
| request_id                       | endpoint          | status_code | latency_ms | tool_name | tool_status | tool_latency |
+----------------------------------+-------------------+-------------+------------+-----------+-------------+--------------+
| 48e8429615a4d75aa6d5dfd568291b5e | /api/chat.php     | 200         | 1115       | NULL      | NULL        | NULL         |
+----------------------------------+-------------------+-------------+------------+-----------+-------------+--------------+
```

---

## Tool Call Logging

### Tool Execution Lifecycle

```php
// 1. Log tool call start
$toolCallId = $telemetry->logToolCall(
    toolName: 'fs.read',
    args: ['path' => 'assets/config.php'],
    agentRequestId: $requestDbId
);

$startTime = microtime(true);

try {
    // 2. Execute tool
    $result = executeTool('fs.read', ['path' => 'assets/config.php']);
    
    // 3. Calculate latency
    $latencyMs = (int) round((microtime(true) - $startTime) * 1000);
    
    // 4. Log success
    $telemetry->updateToolCallStatus(
        toolCallId: $toolCallId,
        status: 'ok',
        result: $result,
        latencyMs: $latencyMs
    );
    
} catch (Exception $e) {
    // 5. Log error
    $latencyMs = (int) round((microtime(true) - $startTime) * 1000);
    
    $telemetry->updateToolCallStatus(
        toolCallId: $toolCallId,
        status: 'error',
        error: $e->getMessage(),
        latencyMs: $latencyMs
    );
    
    throw $e;
}
```

### Tool Status Tracking

**Status Enum Values:**
- `started` - Tool invocation initiated
- `ok` - Tool completed successfully
- `error` - Tool failed with error
- `timeout` - Tool exceeded time limit

**Status Transitions:**
```
started → ok       (success)
started → error    (failure)
started → timeout  (exceeded limit)
```

---

## Conversation Tracking

### Conversation Creation

```php
// Create conversation
$stmt = $pdo->prepare("
    INSERT INTO ai_conversations (session_key, provider, model) 
    VALUES (?, ?, ?)
");
$stmt->execute(['user-123', 'openai', 'gpt-4o-mini']);
$conversationId = (int) $pdo->lastInsertId();

// Link to request
$telemetry->logRequest(
    endpoint: '/api/chat.php',
    conversationId: $conversationId,
    sessionKey: 'user-123'
);
```

### Message Tracking

```php
// User message
$stmt = $pdo->prepare("
    INSERT INTO ai_conversation_messages (conversation_id, role, content) 
    VALUES (?, ?, ?)
");
$stmt->execute([$conversationId, 'user', 'Hello!']);
$userMessageId = (int) $pdo->lastInsertId();

// Link to request
$telemetry->logRequest(
    endpoint: '/api/chat.php',
    conversationId: $conversationId,
    messageId: $userMessageId,
    sessionKey: 'user-123'
);

// Assistant message
$stmt->execute([$conversationId, 'assistant', 'Hi there!']);
$assistantMessageId = (int) $pdo->lastInsertId();
```

### Token and Cost Tracking

```php
// Update conversation totals
$stmt = $pdo->prepare("
    UPDATE ai_conversations 
    SET 
        total_tokens = total_tokens + ?,
        cost_nzd_cents = cost_nzd_cents + ?,
        updated_at = NOW()
    WHERE id = ?
");
$stmt->execute([37, 0, $conversationId]);

// Update message tokens
$stmt = $pdo->prepare("
    UPDATE ai_conversation_messages 
    SET tokens = ?, cost_nzd_cents = ?
    WHERE id = ?
");
$stmt->execute([10, 0, $assistantMessageId]);
```

---

## Performance Metrics

### Latency Measurement

**Request Latency:**
```php
// Captured automatically by Telemetry class
$telemetry = new Telemetry($pdo);
// ... do work ...
$latencyMs = $telemetry->getElapsedMs();  // Total request time
```

**Tool Latency:**
```php
$start = microtime(true);
$result = executeTool('fs.read', $args);
$toolLatencyMs = (int) round((microtime(true) - $start) * 1000);
```

### Token Counting

**OpenAI Response:**
```json
{
  "usage": {
    "prompt_tokens": 27,
    "completion_tokens": 10,
    "total_tokens": 37
  }
}
```

**Stored in Database:**
```sql
INSERT INTO ai_conversation_messages (tokens) VALUES (10);  -- Assistant message
UPDATE ai_conversations SET total_tokens = total_tokens + 37;
```

### Cost Attribution

**Formula (OpenAI GPT-4o-mini):**
```
Input Cost:  $0.150 / 1M tokens = $0.00000015 / token
Output Cost: $0.600 / 1M tokens = $0.00000060 / token

Example:
27 input tokens  × $0.00000015 = $0.00000405
10 output tokens × $0.00000060 = $0.00000600
Total: $0.00001005 (≈ 0 NZD cents)
```

**Cost Tracking:**
```php
$inputCost = $tokensIn * 0.00000015;
$outputCost = $tokensOut * 0.00000060;
$totalCostUSD = $inputCost + $outputCost;
$totalCostNZD = $totalCostUSD * 1.65;  // USD to NZD
$costNZDCents = (int) round($totalCostNZD * 100);
```

---

## Log Aggregation

### Daily Request Statistics

```sql
SELECT 
    DATE(created_at) as date,
    COUNT(*) as total_requests,
    SUM(CASE WHEN status_code = 200 THEN 1 ELSE 0 END) as success_count,
    SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as error_count,
    AVG(latency_ms) as avg_latency,
    MAX(latency_ms) as max_latency,
    MIN(latency_ms) as min_latency
FROM ai_agent_requests
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY DATE(created_at)
ORDER BY date DESC;
```

**Example Output:**
```
+------------+----------------+---------------+-------------+-------------+-------------+-------------+
| date       | total_requests | success_count | error_count | avg_latency | max_latency | min_latency |
+------------+----------------+---------------+-------------+-------------+-------------+-------------+
| 2025-11-02 | 150            | 147           | 3           | 245         | 3500        | 5           |
| 2025-11-01 | 132            | 130           | 2           | 230         | 2800        | 8           |
+------------+----------------+---------------+-------------+-------------+-------------+-------------+
```

---

### Tool Usage Analytics

```sql
SELECT 
    tool_name,
    COUNT(*) as total_calls,
    SUM(CASE WHEN status = 'ok' THEN 1 ELSE 0 END) as success_count,
    SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as error_count,
    ROUND(SUM(CASE WHEN status = 'ok' THEN 1 ELSE 0 END) * 100.0 / COUNT(*), 2) as success_rate,
    AVG(latency_ms) as avg_latency,
    MAX(latency_ms) as max_latency,
    MIN(latency_ms) as min_latency
FROM ai_tool_calls
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY tool_name
ORDER BY total_calls DESC;
```

**Example Output:**
```
+-----------+-------------+---------------+-------------+--------------+-------------+-------------+-------------+
| tool_name | total_calls | success_count | error_count | success_rate | avg_latency | max_latency | min_latency |
+-----------+-------------+---------------+-------------+--------------+-------------+-------------+-------------+
| fs.list   | 5           | 5             | 0           | 100.00       | 0.8         | 2           | 0           |
| fs.read   | 3           | 3             | 0           | 100.00       | 4.2         | 8           | 2           |
| db.select | 2           | 2             | 0           | 100.00       | 1.5         | 2           | 1           |
+-----------+-------------+---------------+-------------+--------------+-------------+-------------+-------------+
```

---

### Error Rate Monitoring

```sql
SELECT 
    error_code,
    COUNT(*) as occurrences,
    GROUP_CONCAT(DISTINCT endpoint SEPARATOR ', ') as affected_endpoints,
    MAX(created_at) as last_occurrence
FROM ai_agent_requests
WHERE error_code IS NOT NULL
  AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
GROUP BY error_code
ORDER BY occurrences DESC;
```

**Example Output:**
```
+----------------+-------------+--------------------+---------------------+
| error_code     | occurrences | affected_endpoints | last_occurrence     |
+----------------+-------------+--------------------+---------------------+
| CHAT_FAILURE   | 2           | /api/chat.php      | 2025-11-02 23:15:00 |
| FILE_NOT_FOUND | 1           | /api/tools/invoke  | 2025-11-02 22:45:00 |
+----------------+-------------+--------------------+---------------------+
```

---

## Monitoring Queries

### Active Conversations

```sql
SELECT 
    c.id,
    c.session_key,
    c.provider,
    c.model,
    COUNT(m.id) as message_count,
    c.total_tokens,
    c.cost_nzd_cents,
    c.created_at,
    c.updated_at,
    TIMESTAMPDIFF(MINUTE, c.created_at, c.updated_at) as duration_minutes
FROM ai_conversations c
LEFT JOIN ai_conversation_messages m ON m.conversation_id = c.id
WHERE c.updated_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY c.id
ORDER BY c.updated_at DESC;
```

---

### Slow Requests (> 1 second)

```sql
SELECT 
    request_id,
    endpoint,
    method,
    status_code,
    latency_ms,
    error_code,
    created_at
FROM ai_agent_requests
WHERE latency_ms > 1000
  AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY latency_ms DESC
LIMIT 20;
```

---

### Failed Tool Calls

```sql
SELECT 
    tc.tool_name,
    tc.args,
    tc.error,
    tc.latency_ms,
    tc.created_at,
    ar.request_id,
    ar.endpoint
FROM ai_tool_calls tc
LEFT JOIN ai_agent_requests ar ON ar.id = tc.agent_request_id
WHERE tc.status = 'error'
  AND tc.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY tc.created_at DESC
LIMIT 20;
```

---

## Retention Policies

### Recommended Retention

| Table | Hot Data | Warm Data | Cold Data | Deletion |
|-------|----------|-----------|-----------|----------|
| ai_agent_requests | 7 days | 30 days | 90 days | After 1 year |
| ai_tool_calls | 7 days | 30 days | 90 days | After 1 year |
| ai_tool_results | 7 days | 30 days | 90 days | After 1 year |
| ai_conversations | 30 days | 90 days | 1 year | After 2 years |
| ai_conversation_messages | 30 days | 90 days | 1 year | After 2 years |
| ai_memory | Never | Never | Never | Manual only |
| mcp_tool_usage | 7 days | 30 days | 90 days | After 1 year |

**Hot Data:** Frequently accessed, keep in main tables  
**Warm Data:** Archived to `_archive` tables, compressed  
**Cold Data:** Moved to object storage (S3/R2), compressed  
**Deletion:** Permanently removed  

---

### Cleanup Scripts

**Archive Old Requests (90 days):**
```sql
-- Create archive table (one-time)
CREATE TABLE ai_agent_requests_archive LIKE ai_agent_requests;

-- Move old records
INSERT INTO ai_agent_requests_archive
SELECT * FROM ai_agent_requests
WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);

DELETE FROM ai_agent_requests
WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY);

-- Optimize table
OPTIMIZE TABLE ai_agent_requests;
```

**Delete Ancient Records (1 year):**
```sql
DELETE FROM ai_agent_requests_archive
WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);

DELETE FROM ai_tool_calls
WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);

DELETE FROM mcp_tool_usage
WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

---

### Automated Cleanup (Cron)

```bash
# Daily cleanup at 3 AM
0 3 * * * cd /home/master/applications/hdgwrzntwa/public_html && php assets/services/ai-agent/scripts/cleanup_old_telemetry.php >> logs/cleanup.log 2>&1
```

**cleanup_old_telemetry.php:**
```php
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/app.php';

$pdo = get_pdo();

// Archive requests older than 90 days
$stmt = $pdo->prepare("
    INSERT IGNORE INTO ai_agent_requests_archive
    SELECT * FROM ai_agent_requests
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)
");
$stmt->execute();
$archived = $stmt->rowCount();

// Delete archived requests
$stmt = $pdo->prepare("
    DELETE FROM ai_agent_requests
    WHERE created_at < DATE_SUB(NOW(), INTERVAL 90 DAY)
");
$stmt->execute();
$deleted = $stmt->rowCount();

echo "[" . date('Y-m-d H:i:s') . "] Archived: $archived, Deleted: $deleted\n";
```

---

**Document Version:** 1.0.0  
**Last Updated:** November 2, 2025  
**Related Docs:** 03_AI_AGENT_ENDPOINTS.md, 04_DATABASE_SCHEMA.md, 09_TROUBLESHOOTING.md
