# AI Conversation Tracking - Full Integration Guide

**Status**: Schema Deployed ✅ | Services Created ✅ | Integration Pending
**Version**: 1.0.0
**Created**: 2025-01-27

## 📋 Overview

This document describes the complete conversation tracking, tool logging, and memory storage system for AI agents (OpenAI, Anthropic, etc.) integrated with the Intelligence Hub.

### What Was Built

#### ✅ Database Schema (COMPLETE)
- **ai_conversations** - Conversation container with platform tracking
- **ai_conversation_messages** - Individual messages (user/assistant/system/tool) with provider metadata
- **ai_conversation_topics** - Topic classification
- **ai_agent_requests** - Provider telemetry (API calls, tokens, costs)
- **ai_tool_calls** - Tool execution tracking (1:N from message)
- **ai_tool_results** - Tool outputs (1:N from tool_call)
- **ai_memory** - Scoped persistent memory (user/session/conversation/project/global)
- **ai_message_files** - File attachments linked to intelligence_content
- **mcp_tool_usage** - Unified tool usage log
- Views: `v_ai_conversation_trace`, `v_ai_tool_calls`, `v_ai_provider_usage`, `v_ai_memory_active`

#### ✅ Services (COMPLETE)
- **ConversationLogger** - Unified conversation + tool tracking service
  - Location: `ai-agent/src/Services/ConversationLogger.php`
  - Methods: `logUserMessage()`, `logAssistantMessage()`, `logProviderRequest()`, `logToolCallStart()`, `logToolCallComplete()`, `linkMessageFile()`

- **MemoryService** - Scoped persistent memory
  - Location: `ai-agent/src/Services/MemoryService.php`
  - Methods: `store()`, `get()`, `getAll()`, `delete()`, `cleanExpired()`, `updateImportance()`

#### ⏳ Integration Pending
- Update chat controller to use ConversationLogger
- Wire MemoryService into existing MemoryTool.php
- Update tool gateway for tool call logging
- Scanner v3 rewrite (file → intelligence_content → ai_message_files)

---

## 🔧 Integration Steps

### Step 1: Update Chat Controller (ai-agent/api/chat.php)

**Current State**: Likely using basic message storage
**Target State**: Full conversation tracking with provider telemetry

```php
<?php
// At top of chat.php
require_once __DIR__ . '/../src/Services/ConversationLogger.php';
use AiAgent\Services\ConversationLogger;

// Initialize (after PDO connection)
$conversationLogger = new ConversationLogger($pdo);

// When receiving user message:
$conversationId = $_POST['conversation_id'] ?? generateConversationId();
$messageSequence = getNextMessageSequence($conversationId, $pdo); // Implement this
$userContent = $_POST['message'] ?? '';

$userMessageId = $conversationLogger->logUserMessage(
    $conversationId,
    $userContent,
    $messageSequence,
    ['platform' => 'web', 'ip' => $_SERVER['REMOTE_ADDR']]
);

// BEFORE calling OpenAI/Anthropic:
$requestUuid = generateUuid(); // Use ramsey/uuid or similar
$requestStart = microtime(true);

// Make API call to provider
$response = callOpenAI($messages); // Your existing OpenAI call

// AFTER receiving response:
$requestEnd = microtime(true);
$responseTimeMs = (int) (($requestEnd - $requestStart) * 1000);

// Log provider request
$conversationLogger->logProviderRequest(
    provider: 'openai',
    requestUuid: $requestUuid,
    conversationId: $conversationId,
    messageId: $userMessageId,
    model: $response->model ?? 'gpt-4o-mini',
    endpoint: 'https://api.openai.com/v1/chat/completions',
    promptTokens: $response->usage->prompt_tokens ?? 0,
    completionTokens: $response->usage->completion_tokens ?? 0,
    totalTokens: $response->usage->total_tokens ?? 0,
    costNzdCents: calculateCost($response->model, $response->usage), // Implement cost calc
    responseTimeMs: $responseTimeMs,
    status: 'success',
    errorMessage: null,
    requestHeaders: ['Content-Type' => 'application/json'], // Redact auth
    requestPayload: ['messages' => $messages], // Minified
    responseBody: json_decode(json_encode($response), true) // Minified
);

// Log assistant message
$assistantMessageId = $conversationLogger->logAssistantMessage(
    conversationId: $conversationId,
    content: $response->choices[0]->message->content ?? '',
    sequence: $messageSequence + 1,
    provider: 'openai',
    model: $response->model ?? 'gpt-4o-mini',
    tokensIn: $response->usage->prompt_tokens ?? null,
    tokensOut: $response->usage->completion_tokens ?? null,
    requestId: $requestUuid,
    responseId: $response->id ?? null
);

// If response includes tool calls:
foreach ($response->choices[0]->message->tool_calls ?? [] as $toolCall) {
    $toolCallId = $conversationLogger->logToolCallStart(
        conversationId: $conversationId,
        messageId: $assistantMessageId,
        toolName: $toolCall->function->name,
        requestId: $toolCall->id,
        requestArgs: json_decode($toolCall->function->arguments, true)
    );

    // Execute tool
    $toolStart = microtime(true);
    $toolResult = executeToolFunction($toolCall->function->name, $toolCall->function->arguments);
    $toolEnd = microtime(true);

    // Log tool result
    $conversationLogger->logToolCallComplete(
        toolCallId: $toolCallId,
        status: $toolResult['success'] ? 'ok' : 'error',
        result: $toolResult,
        latencyMs: (int) (($toolEnd - $toolStart) * 1000),
        errorCode: $toolResult['error_code'] ?? null
    );
}
```

### Step 2: Wire MemoryService into MemoryTool.php

**File**: `ai-agent/public/api/MemoryTool.php` (exists)
**Current State**: Uses `App\Tools\MemoryTool`
**Target**: Add database persistence via `MemoryService`

```php
<?php
// In MemoryTool.php, after existing imports
require_once __DIR__ . '/../../src/Services/MemoryService.php';
use AiAgent\Services\MemoryService;

// In the run() method or similar:
$pdo = getPdoConnection(); // Use your existing DB connection helper
$memoryService = new MemoryService($pdo);

// For action 'get_context':
if ($action === 'get_context') {
    $scope = $arguments['scope'] ?? 'conversation';
    $scopeId = $arguments['scope_identifier'] ?? 'default';
    $memories = $memoryService->getAll($scope, $scopeId);

    return [
        'success' => true,
        'memories' => $memories
    ];
}

// For action 'store_memory':
if ($action === 'store') {
    $memoryId = $memoryService->store(
        scope: $arguments['scope'] ?? 'conversation',
        scopeIdentifier: $arguments['scope_identifier'] ?? 'default',
        keyName: $arguments['key'] ?? throw new \Exception('Missing key'),
        value: $arguments['value'] ?? throw new \Exception('Missing value'),
        importance: $arguments['importance'] ?? 'medium',
        ttlSeconds: $arguments['ttl_seconds'] ?? null
    );

    return [
        'success' => true,
        'memory_id' => $memoryId
    ];
}
```

### Step 3: Update MCP server_v3.php Routes for Memory

**File**: `mcp/server_v3.php`
**Current Routes**: Lines 615-656

```php
// In tool_routes() function, ensure memory tools point correctly:
'memory.get_context' => ['endpoint'=>'public/api/MemoryTool.php','action'=>'get_context'],
'memory.store'       => ['endpoint'=>'public/api/MemoryTool.php','action'=>'store'],

// Add new memory operations:
'memory.delete'      => ['endpoint'=>'public/api/MemoryTool.php','action'=>'delete'],
'memory.clean'       => ['endpoint'=>'public/api/MemoryTool.php','action'=>'clean_expired'],
```

### Step 4: Tool Gateway Integration

**File**: Tool gateway/dispatcher (location TBD - likely in ai-agent/src/)
**Purpose**: Wrap all tool executions with ai_tool_calls logging

```php
<?php
// Pseudocode for tool gateway wrapper
function executeToolWithLogging(
    string $conversationId,
    int $messageId,
    string $toolName,
    array $arguments,
    ConversationLogger $logger
): array {
    // Start logging
    $requestId = generateUuid();
    $toolCallId = $logger->logToolCallStart(
        $conversationId,
        $messageId,
        $toolName,
        $requestId,
        $arguments
    );

    $start = microtime(true);

    try {
        // Execute actual tool
        $result = ToolRegistry::execute($toolName, $arguments);
        $end = microtime(true);

        // Log success
        $logger->logToolCallComplete(
            toolCallId: $toolCallId,
            status: 'ok',
            result: $result,
            latencyMs: (int) (($end - $start) * 1000)
        );

        return $result;

    } catch (\Exception $e) {
        $end = microtime(true);

        // Log error
        $logger->logToolCallComplete(
            toolCallId: $toolCallId,
            status: 'error',
            result: ['error' => $e->getMessage()],
            latencyMs: (int) (($end - $start) * 1000),
            errorCode: get_class($e)
        );

        throw $e;
    }
}
```

### Step 5: File Attachment Integration (Scanner v3)

**Goal**: Link conversation message attachments to intelligence_content

```php
<?php
// When user uploads file in chat:
$uploadedFile = $_FILES['attachment'];

// 1) Save to intelligence_content (via existing scanner or direct insert)
$contentId = saveToIntelligenceContent(
    filePath: '/path/to/uploaded/file.pdf',
    contentType: 'document',
    metadata: ['original_name' => $uploadedFile['name']]
);

// 2) Link to message
$conversationLogger->linkMessageFile(
    messageId: $userMessageId,
    intelligenceContentId: $contentId,
    filePath: null, // Already in intelligence_content
    fileType: 'document',
    metadata: ['mime_type' => $uploadedFile['type']]
);

// When AI references file:
// Query: SELECT ic.* FROM intelligence_content ic
//        JOIN ai_message_files mf ON mf.intelligence_content_id = ic.id
//        WHERE mf.message_id = ?
```

---

## 📊 Data Flow Diagrams

### Conversation Flow

```
User Message → chat.php
    ↓
ConversationLogger::logUserMessage()
    ↓ (writes to ai_conversation_messages)
OpenAI/Anthropic API Call
    ↓ (timed)
ConversationLogger::logProviderRequest()
    ↓ (writes to ai_agent_requests)
Response with tool_calls?
    ↓ YES
    ├─ logToolCallStart() → ai_tool_calls (status='started')
    ├─ Execute tool
    └─ logToolCallComplete() → ai_tool_results + UPDATE ai_tool_calls
    ↓ NO
ConversationLogger::logAssistantMessage()
    ↓ (writes to ai_conversation_messages, links to ai_agent_requests via request_id)
Return to user
```

### Memory Flow

```
User: "Remember I prefer dark mode"
    ↓
MemoryService::store(
    scope: 'user',
    scopeIdentifier: 'user123',
    keyName: 'ui_preferences',
    value: {'theme': 'dark'},
    importance: 'medium'
)
    ↓ (UPSERT into ai_memory)
Later session: "What are my preferences?"
    ↓
MemoryService::get('user', 'user123', 'ui_preferences')
    ↓ (SELECT FROM ai_memory WHERE scope='user' AND scope_identifier='user123')
Return: {'theme': 'dark'}
```

### Tool Call Flow

```
Assistant response includes tool_calls: [
    {id: "call_abc123", function: {name: "db.query", arguments: "{\"sql\":\"SELECT...\"}"}}
]
    ↓
logToolCallStart('conv_xyz', msgId, 'db.query', 'call_abc123', {sql: "SELECT..."})
    ↓ (INSERT INTO ai_tool_calls: status='started', started_at=NOW())
Execute db.query(...)
    ↓ (actual tool execution)
    ↓ (returns {rows: [...], count: 5})
logToolCallComplete(toolCallId, 'ok', {rows: [...], count: 5}, latency: 45ms)
    ↓ (UPDATE ai_tool_calls: status='ok', finished_at=NOW(), latency_ms=45)
    ↓ (INSERT INTO ai_tool_results: result=JSON(...))
```

---

## 🔍 Query Examples

### Get Full Conversation Trace

```sql
SELECT *
FROM v_ai_conversation_trace
WHERE conversation_id = 'conv_abc123'
ORDER BY message_sequence;
```

**Returns**: All messages + provider requests + costs in chronological order

### Get Tool Calls for Conversation

```sql
SELECT t.tool_name, t.status, t.latency_ms, r.result
FROM ai_tool_calls t
LEFT JOIN ai_tool_results r ON r.tool_call_id = t.id
WHERE t.conversation_id = 'conv_abc123'
ORDER BY t.started_at;
```

### Get User's Memories

```sql
SELECT *
FROM v_ai_memory_active
WHERE scope = 'user' AND scope_identifier = 'user123'
ORDER BY importance DESC, updated_at DESC;
```

### Provider Cost Summary (Today)

```sql
SELECT provider, model,
       SUM(total_tokens) as total_tokens,
       SUM(cost_nzd_cents)/100 as total_cost_nzd,
       COUNT(*) as request_count
FROM ai_agent_requests
WHERE DATE(created_at) = CURDATE()
GROUP BY provider, model;
```

---

## 🧪 Testing Checklist

### OpenAI Integration
- [ ] User message logged with metadata
- [ ] OpenAI API call recorded in ai_agent_requests
- [ ] Assistant response logged with tokens + model
- [ ] Tool calls tracked (start + complete)
- [ ] Tool results stored
- [ ] request_id linkage correct (message → provider request)
- [ ] Cost calculation accurate

### Anthropic Integration
- [ ] Same checklist as OpenAI but with Anthropic-specific fields
- [ ] Handle different response format (content blocks vs message.content)
- [ ] Map Anthropic model names correctly

### Memory System
- [ ] store() creates new memory
- [ ] store() updates existing memory (ON DUPLICATE KEY UPDATE)
- [ ] get() retrieves correct value
- [ ] getAll() filters by scope correctly
- [ ] delete() removes memory
- [ ] cleanExpired() removes expired memories
- [ ] Scope isolation working (user memories don't leak to other users)

### File Attachments
- [ ] File uploaded → intelligence_content entry created
- [ ] ai_message_files link created
- [ ] Query retrieves attached files for message
- [ ] Scanner v3 updates intelligence_content on file change

### Views
- [ ] v_ai_conversation_trace returns complete history
- [ ] v_ai_tool_calls shows tool execution details
- [ ] v_ai_provider_usage aggregates costs correctly
- [ ] v_ai_memory_active filters expired memories

---

## 🚀 Deployment Steps

### Phase 1: Schema (COMPLETE ✅)
1. ✅ Backup database
2. ✅ Run SQL migration (`/tmp/ai_schema_upgrade.sql`)
3. ✅ Verify tables created
4. ✅ Verify views created

### Phase 2: Services (COMPLETE ✅)
1. ✅ Deploy ConversationLogger.php
2. ✅ Deploy MemoryService.php
3. ⏳ Test services in isolation (unit tests)

### Phase 3: Integration (PENDING)
1. ⏳ Update chat controller
2. ⏳ Wire MemoryService into MemoryTool.php
3. ⏳ Update tool gateway
4. ⏳ Test OpenAI flow end-to-end
5. ⏳ Test Anthropic flow end-to-end
6. ⏳ Test memory storage/retrieval
7. ⏳ Test file attachments

### Phase 4: Dashboard (FUTURE)
1. ⏳ Create conversation viewer UI
2. ⏳ Create tool call debugger UI
3. ⏳ Create cost analytics dashboard
4. ⏳ Create memory browser UI

---

## 📝 Configuration

### Database Connection

Services expect a PDO instance:

```php
// Example: ai-agent/config/database.php
return new PDO(
    'mysql:host=127.0.0.1;dbname=jcepnzzkmj;charset=utf8mb4',
    'jcepnzzkmj',
    'wprKh9Jq63',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]
);
```

### Cost Calculation

Implement cost calculator based on provider pricing:

```php
function calculateCost(string $model, object $usage): int {
    // Prices in NZD cents per 1M tokens
    $priceMap = [
        'gpt-4o-mini' => [
            'input' => 20,  // $0.20 NZD / 1M tokens input
            'output' => 60  // $0.60 NZD / 1M tokens output
        ],
        'gpt-4o' => [
            'input' => 350,
            'output' => 1050
        ],
        'claude-3-5-sonnet-latest' => [
            'input' => 450,
            'output' => 2250
        ]
    ];

    $prices = $priceMap[$model] ?? ['input' => 0, 'output' => 0];

    $inputCost = ($usage->prompt_tokens / 1_000_000) * $prices['input'];
    $outputCost = ($usage->completion_tokens / 1_000_000) * $prices['output'];

    return (int) round($inputCost + $outputCost);
}
```

### Memory TTL Defaults

```php
const MEMORY_TTL = [
    'session' => 3600,        // 1 hour
    'conversation' => 86400,  // 24 hours
    'user' => null,           // Never expire
    'project' => null,        // Never expire
    'global' => null          // Never expire
];
```

---

## 🛠️ Maintenance

### Cron Jobs

```bash
# Clean expired memories daily
0 2 * * * cd /path/to/project && php -r "require 'ai-agent/src/Services/MemoryService.php'; (new MemoryService($pdo))->cleanExpired();"

# Archive old conversations monthly
0 3 1 * * mysql -u jcepnzzkmj -p'wprKh9Jq63' jcepnzzkmj -e "UPDATE ai_conversations SET status='archived' WHERE updated_at < DATE_SUB(NOW(), INTERVAL 6 MONTH) AND status='active';"

# Cleanup old tool results quarterly (optional - keep for debugging)
0 4 1 1,4,7,10 * mysql -u jcepnzzkmj -p'wprKh9Jq63' jcepnzzkmj -e "DELETE FROM ai_tool_results WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);"
```

---

## 📚 API Reference

### ConversationLogger Methods

```php
// Log messages
logUserMessage(string $conversationId, string $content, int $sequence, ?array $metadata = null): int
logAssistantMessage(string $conversationId, string $content, int $sequence, ...): int
logSystemMessage(string $conversationId, string $content, int $sequence, ?array $metadata = null): int

// Log provider requests
logProviderRequest(string $provider, string $requestUuid, ...): int

// Log tool calls
logToolCallStart(string $conversationId, int $messageId, string $toolName, string $requestId, array $requestArgs): int
logToolCallComplete(int $toolCallId, string $status, array $result, ?int $latencyMs = null, ...): void

// Link files
linkMessageFile(int $messageId, ?int $intelligenceContentId = null, ...): int

// Retrieve
getConversationHistory(string $conversationId, int $limit = 50): array
getConversationTrace(string $conversationId): array
```

### MemoryService Methods

```php
// Store/retrieve
store(string $scope, string $scopeIdentifier, string $keyName, array $value, string $importance = 'medium', ?int $ttlSeconds = null): int
get(string $scope, string $scopeIdentifier, string $keyName): ?array
getAll(string $scope, string $scopeIdentifier, ?string $minImportance = null): array

// Delete
delete(string $scope, string $scopeIdentifier, string $keyName): bool
deleteAll(string $scope, string $scopeIdentifier): int

// Maintenance
cleanExpired(): int
updateImportance(string $scope, string $scopeIdentifier, string $keyName, string $newImportance): bool
extendExpiration(string $scope, string $scopeIdentifier, string $keyName, int $additionalSeconds): bool
```

---

## 🎯 Next Steps

1. **Immediate** (this session if time permits):
   - Update chat controller to use ConversationLogger
   - Test OpenAI integration end-to-end
   - Verify data flowing into all tables

2. **Short-term** (next session):
   - Wire MemoryService into MemoryTool.php
   - Update tool gateway for tool call logging
   - Test Anthropic integration

3. **Medium-term** (next sprint):
   - Build conversation viewer dashboard
   - Build cost analytics dashboard
   - Implement Scanner v3 (file → intelligence_content → ai_message_files)

4. **Long-term** (ongoing):
   - Fine-tune cost calculation with real usage data
   - Optimize queries (add indexes if slow)
   - Build memory recommendation system ("AI suggests storing this as memory")
   - Export conversation transcripts feature

---

**Status Summary**:
- ✅ Schema deployed
- ✅ Services created
- ⏳ Integration pending
- ⏳ Testing needed
- ⏳ Dashboard not started

**Files Created**:
- `/tmp/ai_schema_upgrade.sql` - Database migration
- `ai-agent/src/Services/ConversationLogger.php` - Service class
- `ai-agent/src/Services/MemoryService.php` - Service class
- `_kb/AI_CONVERSATION_TRACKING_INTEGRATION.md` - This guide

**Database Changes**: 9 tables modified/created, 5 views created

**Ready for**: Integration into chat controller and tool gateway
