# 📚 MCP API REFERENCE DOCUMENTATION

**Version:** 1.0.0
**Last Updated:** November 2, 2025
**Endpoint:** https://gpt.ecigdis.co.nz/mcp/server_v3.php
**Protocol:** JSON-RPC 2.0

---

## 🔐 Authentication

All requests require API key authentication via header:

```bash
X-API-Key: bFUdRjh4Jx
```

Or via Authorization header:

```bash
Authorization: Bearer bFUdRjh4Jx
```

---

## 📡 JSON-RPC 2.0 Format

### Request Structure
```json
{
  "jsonrpc": "2.0",
  "method": "tools/call",
  "params": {
    "name": "tool_name",
    "arguments": {
      "param1": "value1",
      "param2": "value2"
    }
  },
  "id": 1
}
```

### Response Structure (Success)
```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "result": {
    "success": true,
    "data": { ... },
    "meta": {
      "execution_time_ms": 123,
      "timestamp": "2025-11-02T10:30:00Z"
    }
  }
}
```

### Response Structure (Error)
```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "error": {
    "code": -32600,
    "message": "Invalid Request",
    "data": {
      "details": "Missing required parameter"
    }
  }
}
```

---

## 🛠️ AVAILABLE TOOLS (14 Total)

### 1. semantic_search
**Purpose:** Search content using natural language queries
**Phase:** Production (to be enhanced in Phase 2)

**Parameters:**
```json
{
  "query": "string (required)",     // Search query
  "limit": "integer (optional)",     // Max results (default: 20)
  "unit_id": "integer (optional)",   // Filter by business unit
  "category_id": "integer (optional)" // Filter by category
}
```

**Example Request:**
```bash
curl -X POST "https://gpt.ecigdis.co.nz/mcp/server_v3.php" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: bFUdRjh4Jx" \
  -d '{
    "jsonrpc": "2.0",
    "method": "tools/call",
    "params": {
      "name": "semantic_search",
      "arguments": {
        "query": "inventory transfer validation",
        "limit": 10
      }
    },
    "id": 1
  }'
```

**Example Response:**
```json
{
  "jsonrpc": "2.0",
  "id": 1,
  "result": {
    "success": true,
    "data": {
      "results": [
        {
          "content_id": 12345,
          "content_name": "transfer_validation.php",
          "content_path": "modules/transfers/lib/Validation.php",
          "relevance_score": 87.5,
          "intelligence_score": 65.2,
          "business_value_score": 72.0,
          "preview": "Function validates transfer items...",
          "category": "Inventory Management",
          "unit_name": "CIS"
        }
      ],
      "total_results": 42,
      "returned": 10
    },
    "meta": {
      "execution_time_ms": 145,
      "cache_hit": false
    }
  }
}
```

---

### 2. find_code
**Purpose:** Find specific code patterns (functions, classes, variables)

**Parameters:**
```json
{
  "pattern": "string (required)",    // Code pattern to find
  "search_in": "string (optional)",  // 'all', 'files', 'content' (default: 'all')
  "limit": "integer (optional)"      // Max results (default: 20)
}
```

**Example:**
```json
{
  "jsonrpc": "2.0",
  "method": "tools/call",
  "params": {
    "name": "find_code",
    "arguments": {
      "pattern": "calculateTotal",
      "search_in": "content",
      "limit": 5
    }
  },
  "id": 1
}
```

---

### 3. search_by_category
**Purpose:** Search within specific business categories

**Parameters:**
```json
{
  "query": "string (required)",
  "category_name": "string (required)",  // e.g., "Inventory Management"
  "limit": "integer (optional)"
}
```

**Available Categories:**
- Inventory Management
- Sales & Orders
- Financial Operations
- User Management
- Reporting & Analytics
- System Configuration
- API Integration
- Security & Permissions
- [... 23 more categories]

---

### 4. find_similar
**Purpose:** Find files similar to a reference file

**Parameters:**
```json
{
  "file_path": "string (required)",  // Reference file path
  "limit": "integer (optional)"       // Max results (default: 10)
}
```

---

### 5. explore_by_tags
**Purpose:** Search using semantic tags

**Parameters:**
```json
{
  "semantic_tags": "array (required)",  // Tags to search for
  "match_all": "boolean (optional)",    // AND vs OR logic (default: false)
  "limit": "integer (optional)"
}
```

**Example:**
```json
{
  "semantic_tags": ["validation", "security", "authentication"],
  "match_all": false,
  "limit": 20
}
```

---

### 6. analyze_file
**Purpose:** Deep analysis of a specific file

**Parameters:**
```json
{
  "file_path": "string (required)"
}
```

**Returns:**
- File metadata
- Quality metrics
- Complexity analysis
- Dependencies
- Related files

---

### 7. get_file_content
**Purpose:** Retrieve file content with context

**Parameters:**
```json
{
  "file_path": "string (required)",
  "include_related": "boolean (optional)"  // Include related files (default: false)
}
```

---

### 8. db.query
**Purpose:** Execute read-only SQL queries

**Parameters:**
```json
{
  "sql": "string (required)",     // SQL SELECT query
  "params": "array (optional)"     // Parameterized values
}
```

**Security:** Only SELECT queries allowed, no INSERT/UPDATE/DELETE

---

### 9. db.schema
**Purpose:** Describe database table structures

**Parameters:**
```json
{
  "table": "string (optional)"  // Specific table or all tables
}
```

---

### 10. db.tables
**Purpose:** List all database tables

**Parameters:** None

---

### 11. health_check
**Purpose:** Check MCP system health

**Parameters:** None

**Returns:**
```json
{
  "status": "healthy",
  "uptime": "5d 12h 34m",
  "database": "connected",
  "redis": "available",
  "satellites": {
    "intelligence_hub": "online",
    "cis": "online",
    "vapeshed": "online",
    "wholesale": "online"
  },
  "performance": {
    "avg_response_ms": 172,
    "cache_hit_rate": 0.0,
    "searches_today": 1247
  }
}
```

---

### 12. get_stats
**Purpose:** System-wide statistics

**Parameters:**
```json
{
  "breakdown_by": "string (optional)"  // 'unit', 'category', 'type'
}
```

---

### 13. top_keywords
**Purpose:** Most common keywords

**Parameters:**
```json
{
  "unit_id": "integer (optional)",
  "limit": "integer (optional)"  // Default: 50
}
```

---

### 14. list_satellites
**Purpose:** Show all satellite servers

**Parameters:** None

---

## 🆕 NEW TOOLS (TO BE ADDED BY AI AGENT)

### Phase 2: federated_search
**Purpose:** Search across all satellites in parallel

**Parameters:**
```json
{
  "query": "string (required)",
  "units": "array (optional)",      // Filter specific units
  "limit": "integer (optional)"
}
```

**Expected Response:**
```json
{
  "results": [
    {
      "content_id": 12345,
      "source_unit": "CIS",
      "source_satellite": "https://staff.vapeshed.co.nz",
      "relevance_score": 92.3,
      "...": "..."
    }
  ],
  "satellite_responses": {
    "intelligence_hub": { "count": 15, "time_ms": 145 },
    "cis": { "count": 8, "time_ms": 320 },
    "vapeshed": { "count": 2, "time_ms": 180 },
    "wholesale": { "count": 0, "time_ms": 450 }
  }
}
```

---

### Phase 4: generate_semantic_tags
**Purpose:** Generate AI tags for content

**Parameters:**
```json
{
  "content_id": "integer (required)"
}
```

---

### Phase 4: get_search_analytics
**Purpose:** Search analytics and metrics

**Parameters:**
```json
{
  "timeframe": "string (optional)",  // '24h', '7d', '30d'
  "metric": "string (optional)"       // 'ctr', 'zero_results', 'popular'
}
```

---

## 📊 ERROR CODES

| Code | Meaning | Description |
|------|---------|-------------|
| -32700 | Parse error | Invalid JSON |
| -32600 | Invalid Request | Missing required fields |
| -32601 | Method not found | Tool doesn't exist |
| -32602 | Invalid params | Wrong parameter types |
| -32603 | Internal error | Server error |
| -32000 | Auth failed | Invalid API key |
| -32001 | Rate limited | Too many requests |
| -32002 | Database error | Database connection/query failed |
| -32003 | Cache error | Redis unavailable (non-fatal) |
| -32004 | Satellite timeout | Satellite didn't respond in time |

---

## 🔄 RATE LIMITS

- **Standard queries:** 100 requests/minute
- **Heavy queries (db.query):** 20 requests/minute
- **Batch operations:** 10 requests/minute

**Rate Limit Headers:**
```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 87
X-RateLimit-Reset: 1699012345
```

---

## 🎯 BEST PRACTICES

### 1. Use Appropriate Tools
- **Simple keyword search:** `semantic_search`
- **Specific code lookup:** `find_code`
- **Category exploration:** `search_by_category`
- **Cross-satellite search:** `federated_search` (Phase 3+)

### 2. Optimize Queries
- Specify `limit` to reduce payload
- Use filters (`unit_id`, `category_id`)
- Cache results client-side when possible

### 3. Error Handling
```javascript
try {
  const response = await fetch(MCP_URL, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-API-Key': API_KEY
    },
    body: JSON.stringify(request)
  });

  const data = await response.json();

  if (data.error) {
    console.error('MCP Error:', data.error.message);
    // Handle specific error codes
    if (data.error.code === -32000) {
      // Auth failed - check API key
    }
  } else {
    // Process data.result
  }
} catch (err) {
  console.error('Request failed:', err);
}
```

### 4. Monitoring
- Track response times (`meta.execution_time_ms`)
- Monitor cache hit rates (`meta.cache_hit`)
- Log zero-result queries for content gap analysis
- Watch error rates

---

## 📈 PERFORMANCE TARGETS

| Metric | Current | Target (Post-Implementation) |
|--------|---------|------------------------------|
| Avg Response Time | 172ms | <50ms (cached) |
| Cache Hit Rate | 0% | 80%+ |
| Search Coverage | 50.9% | 95%+ |
| Zero Result Rate | Unknown | <5% |
| Satellite Timeout | N/A | <1% |

---

## 🧪 TESTING

### Health Check
```bash
curl -s "https://gpt.ecigdis.co.nz/mcp/health_v3.php"
```

### Tools List
```bash
curl -X POST "https://gpt.ecigdis.co.nz/mcp/server_v3.php" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: bFUdRjh4Jx" \
  -d '{"jsonrpc":"2.0","method":"tools/list","params":{},"id":1}' | jq .
```

### Simple Search Test
```bash
curl -X POST "https://gpt.ecigdis.co.nz/mcp/server_v3.php" \
  -H "Content-Type: application/json" \
  -H "X-API-Key: bFUdRjh4Jx" \
  -d '{
    "jsonrpc":"2.0",
    "method":"tools/call",
    "params":{
      "name":"semantic_search",
      "arguments":{"query":"test","limit":1}
    },
    "id":1
  }' | jq .
```

---

## 📞 SUPPORT

**Issues:** https://github.com/pearcestephens/IntelligenceHub/issues
**Documentation:** /home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/_kb/mcp/
**Health Endpoint:** https://gpt.ecigdis.co.nz/mcp/health_v3.php

---

**Version:** 1.0.0
**Status:** Production (to be enhanced by AI Agent)
**Last Updated:** November 2, 2025
