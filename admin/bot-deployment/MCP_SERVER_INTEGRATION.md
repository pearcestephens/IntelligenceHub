# Intelligence Hub MCP Server Integration Guide

## 🚨 CRITICAL: Server Uses JSON-RPC 2.0 Protocol

## Server Details

**Base URL:** `https://gpt.ecigdis.co.nz/mcp/server_v3.php`
**Version:** v3.0.0
**API Key:** `31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35`

---

## ✅ CORRECT Request Format (JSON-RPC 2.0)

### HTTP Method
```
POST (directly to base URL)
```

### Required Headers
```http
Content-Type: application/json
X-API-Key: 31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35
User-Agent: BotDeployment/1.0
```

### **JSON-RPC 2.0 Request Structure**

Every request MUST follow this exact format:

```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "method": "tools/call",
  "params": {
    "name": "tool_name",
    "arguments": {
      // Tool-specific parameters here
    }
  }
}
```

**Required Fields (ALL MANDATORY):**
- `jsonrpc`: MUST be exactly `"2.0"` (string)
- `id`: Request ID (integer, string, or `null`)
- `method`: MUST be exactly `"tools/call"` (string)
- `params.name`: The MCP tool name (e.g., `"semantic_search"`)
- `params.arguments`: Tool-specific parameters as an object---

## 🎯 Single Endpoint - All Tools via JSON-RPC

**There is ONLY ONE endpoint:** `https://gpt.ecigdis.co.nz/mcp/server_v3.php`

All MCP tools are called through this single endpoint using JSON-RPC 2.0 protocol.

### ✅ CORRECT Tool Call Examples

#### Example 1: Semantic Search
```bash
curl -X POST "https://gpt.ecigdis.co.nz/mcp/server_v3.php" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: 31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35" \
  -d '{
    "jsonrpc": "2.0",
    "id": 1,
    "method": "tools/call",
    "params": {
      "name": "semantic_search",
      "arguments": {
        "query": "consignment transfer logic",
        "limit": 5
      }
    }
  }'
```

**Success Response:**
```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "result": {
    "content": [
      {
        "type": "text",
        "text": "Found 5 relevant files:\n\n1. modules/consignments/Transfer.php (relevance: 0.95)\n..."
      }
    ]
  }
}
```

#### Example 2: Get Conversation Context
```bash
curl -X POST "https://gpt.ecigdis.co.nz/mcp/server_v3.php" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: 31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35" \
  -d '{
    "jsonrpc": "2.0",
    "id": 2,
    "method": "tools/call",
    "params": {
      "name": "conversation.get_project_context",
      "arguments": {
        "limit": 10,
        "include_messages": true
      }
    }
  }'
```

**Success Response:**
```json
{
  "jsonrpc": "2.0",
  "id": 2,
  "result": {
    "content": [
      {
        "type": "text",
        "text": "Retrieved 10 conversations:\n\n1. Conversation #147: Consignment Transfer Flow\n..."
      }
    ]
  }
}
```

### ❌ ERROR Response Format

When a request fails (missing required fields, wrong format, etc.):

```json
{
  "jsonrpc": "2.0",
  "id": null,
  "error": {
    "code": -32600,
    "message": "Method must be a non-empty string",
    "data": {
      "code": "INVALID_REQUEST",
      "message": "Method must be a non-empty string",
      "details": [],
      "request_id": "mcp-2e08eeb483734f47"
    }
  }
}
```

**Common Error Codes:**
- `-32600` - Invalid Request (malformed JSON or missing required fields)
- `-32601` - Method not found (invalid `method` value)
- `-32602` - Invalid params (wrong parameters for tool)
- `-32603` - Internal error (server-side error)
- `-32700` - Parse error (invalid JSON)

---

## Available MCP Tools

### 1. Conversation Memory Tools

#### conversation.get_project_context
```json
{
  "tool": "conversation.get_project_context",
  "params": {
    "limit": 10
  }
}
```

#### memory.store
```json
{
  "tool": "memory.store",
  "params": {
    "conversation_id": "conv_12345",
    "content": "User requested security audit of consignment module",
    "memory_type": "request",
    "importance": "high",
    "tags": ["security", "consignment", "audit"]
  }
}
```

---

### 2. Knowledge Base Tools

#### kb.search
```json
{
  "tool": "kb.search",
  "params": {
    "query": "security best practices",
    "limit": 10,
    "filters": {
      "type": "security",
      "date_after": "2024-01-01"
    }
  }
}
```

#### kb.add_document
```json
{
  "tool": "kb.add_document",
  "params": {
    "title": "[Security] Consignment Module Audit - 2025-11-04",
    "content": "Full audit report content...",
    "type": "security",
    "metadata": {
      "author": "Security Sentinel Bot",
      "bot_id": 1,
      "tags": ["security", "audit", "consignment"],
      "related_files": ["modules/consignments/Transfer.php"]
    }
  }
}
```

---

### 3. Semantic Search Tools

#### semantic_search
```json
{
  "tool": "semantic_search",
  "params": {
    "query": "consignment transfer validation logic",
    "limit": 10,
    "file_types": ["php", "js"],
    "exclude_paths": ["vendor/", "node_modules/"]
  }
}
```

**Response:**
```json
{
  "success": true,
  "results": [
    {
      "file": "/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/modules/consignments/Transfer.php",
      "relevance_score": 0.95,
      "excerpt": "public function validateTransfer($data) { ... }",
      "line_start": 45,
      "line_end": 78,
      "context": "Transfer validation method with extensive checks"
    }
  ],
  "total_results": 5,
  "search_time_ms": 123.45
}
```

---

### 4. Database Tools

#### db.query
```json
{
  "tool": "db.query",
  "params": {
    "query": "SELECT * FROM bot_deployments WHERE status = ? LIMIT 10",
    "params": ["active"]
  }
}
```

**Response:**
```json
{
  "success": true,
  "results": [
    {
      "bot_id": 1,
      "bot_name": "Security Sentinel",
      "status": "active"
    }
  ],
  "row_count": 3,
  "execution_time_ms": 12.34
}
```

#### db.schema
```json
{
  "tool": "db.schema",
  "params": {
    "table": "bot_deployments"
  }
}
```

---

### 5. File System Tools

#### fs.read
```json
{
  "tool": "fs.read",
  "params": {
    "path": "/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/modules/consignments/Transfer.php",
    "line_start": 1,
    "line_end": 100
  }
}
```

**Response:**
```json
{
  "success": true,
  "content": "<?php\n\nnamespace Modules\\Consignments;\n\nclass Transfer {\n...",
  "file_path": "/home/.../Transfer.php",
  "line_count": 100,
  "file_size": 8192
}
```

#### fs.write
```json
{
  "tool": "fs.write",
  "params": {
    "path": "/path/to/file.php",
    "content": "<?php\n// Updated content",
    "backup": true
  }
}
```

---

### 6. Logging Tools

#### logs.tail
```json
{
  "tool": "logs.tail",
  "params": {
    "log_file": "/var/log/apache/error.log",
    "lines": 100
  }
}
```

#### logs.grep
```json
{
  "tool": "logs.grep",
  "params": {
    "log_file": "/var/log/apache/error.log",
    "pattern": "PHP Fatal Error",
    "lines": 50
  }
}
```

---

## AIAgentService Implementation

The `AIAgentService` in your Bot Deployment system handles all communication with the MCP server:

### Main Query Method
```php
$aiAgent = new AIAgentService();

$result = $aiAgent->query(
    $bot,                           // Bot instance
    'Analyze consignment module',   // Query
    ['module' => 'consignment'],    // Context
    ['semantic_search', 'fs.read'], // Tools
    true                            // Stream
);
```

**Internal cURL Request:**
```php
$ch = curl_init('https://gpt.ecigdis.co.nz/mcp/server_v3.php/api/query');

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode([
        'query' => 'Analyze consignment module',
        'bot_id' => 1,
        'bot_name' => 'Security Sentinel',
        'bot_role' => 'security',
        'system_prompt' => 'You are a security expert...',
        'context' => ['module' => 'consignment'],
        'tools' => ['semantic_search', 'fs.read'],
        'stream' => true,
        'config' => ['timestamp' => time()]
    ]),
    CURLOPT_TIMEOUT => 30,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'X-API-Key: 31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35',
        'User-Agent: BotDeployment/1.0'
    ]
]);

$response = curl_exec($ch);
```

---

### Direct Tool Calls

```php
// Semantic search
$files = $aiAgent->semanticSearch('consignment transfer logic', 10);

// Database query
$data = $aiAgent->dbQuery('SELECT * FROM consignments LIMIT 5');

// Knowledge base search
$docs = $aiAgent->searchKnowledgeBase('security practices');

// Store memory
$aiAgent->storeMemory(
    'conv_12345',
    'User requested audit',
    'request',
    'high',
    ['security', 'audit']
);

// Read file
$content = $aiAgent->readFile('/path/to/file.php');
```

**Each method internally calls:**
```php
private function makeRequest(string $endpoint, array $payload): array
{
    $url = 'https://gpt.ecigdis.co.nz/mcp/server_v3.php' . $endpoint;

    // cURL setup with API key, timeout, headers
    // POST JSON payload
    // Parse and return response
}
```

---

## Error Handling

### MCP Server Error Response
```json
{
  "success": false,
  "error": "Tool execution failed",
  "details": {
    "tool": "semantic_search",
    "reason": "Index not available",
    "timestamp": 1730736000
  }
}
```

### HTTP Error Codes
- **400** - Bad Request (invalid payload)
- **401** - Unauthorized (invalid API key)
- **429** - Too Many Requests (rate limit)
- **500** - Internal Server Error
- **503** - Service Unavailable

### AIAgentService Error Handling
```php
try {
    $result = $aiAgent->query($bot, $input);
} catch (\Exception $e) {
    // Non-retryable errors:
    if (preg_match('/invalid.*api.*key/i', $e->getMessage())) {
        // Don't retry
    }

    // Retryable errors with exponential backoff:
    // - Connection timeout
    // - Temporary server error
    // - Rate limit (with delay)
}
```

---

## Configuration

### In config/config.php
```php
'aiAgent' => [
    'baseUrl' => 'https://gpt.ecigdis.co.nz/mcp/server_v3.php',
    'apiKey' => '31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35',
    'timeout' => 30,
    'maxRetries' => 3,
    'retryDelay' => 1000,      // milliseconds
    'maxRetryDelay' => 30000,  // milliseconds
    'rateLimit' => [
        'maxRequests' => 60,
        'timeWindow' => 60
    ]
]
```

---

## Testing the Connection

### cURL Test
```bash
curl -X POST https://gpt.ecigdis.co.nz/mcp/server_v3.php/api/health \
  -H "Content-Type: application/json" \
  -H "X-API-Key: 31ce0106609a6c5bc4f7ece0deb2f764df90a06167bda83468883516302a6a35" \
  -d '{}'
```

### PHP Test
```php
$aiAgent = new AIAgentService();
$health = $aiAgent->healthCheck();

if ($health['status'] === 'healthy') {
    echo "MCP Server is operational\n";
    echo "Tools available: " . $health['details']['tools_available'] . "\n";
    echo "Indexed files: " . $health['details']['indexed_files'] . "\n";
} else {
    echo "MCP Server error: " . $health['error'] . "\n";
}
```

---

## Complete Request/Response Flow

```
Bot Deployment System → AIAgentService
  ↓
AIAgentService::query($bot, $input)
  ↓
makeRequest('/api/query', [payload])
  ↓
cURL POST to https://gpt.ecigdis.co.nz/mcp/server_v3.php/api/query
  ↓
Intelligence Hub MCP Server
  - Receives request
  - Validates API key
  - Processes query with RAG
  - Executes requested tools
  - Generates response
  ↓
Returns JSON response
  ↓
AIAgentService parses response
  ↓
Returns to Bot Execution Service
  ↓
Returns to API endpoint or CLI
  ↓
Final output to user
```

---

## Rate Limiting

**Server Side:**
- 60 requests per minute per API key
- 429 response with `Retry-After` header

**Client Side (AIAgentService):**
- Tracks requests in memory
- Enforces 60 req/min locally
- Throws exception before hitting server limit

---

## Best Practices

1. **Always use streaming:** `stream: true` prevents response summarization
2. **Specify tools:** Limit to relevant tools for faster responses
3. **Handle retries:** Use exponential backoff for transient errors
4. **Store memories:** Use `memory.store` after each execution
5. **Document solutions:** Use `kb.add_document` for reusable content
6. **Monitor response times:** Track via `getLastResponseTime()`
7. **Respect rate limits:** Don't exceed 60 requests/minute
8. **Use health checks:** Verify server availability before heavy operations
9. **Include context:** Provide execution_id, bot_id, and relevant metadata
10. **Cache results:** Store frequently accessed data locally when appropriate

---

## Troubleshooting

### Issue: "Connection timeout"
**Solution:** Increase timeout in config.php, check network connectivity

### Issue: "Invalid API key"
**Solution:** Verify API key matches server configuration

### Issue: "Rate limit exceeded"
**Solution:** Reduce request frequency, implement backoff logic

### Issue: "Tool not found"
**Solution:** Check tool name spelling, verify tool is available via health check

### Issue: "Index not available"
**Solution:** Wait for semantic search index rebuild, use alternative tools

---

## Summary

The Intelligence Hub MCP Server expects:
- **POST** requests to specific endpoints
- **JSON** payloads with tool name and parameters
- **X-API-Key** header with valid API key
- **Proper error handling** with retry logic
- **Rate limiting** compliance (60 req/min)

The `AIAgentService` handles all of this automatically, providing a clean interface for bot execution.
