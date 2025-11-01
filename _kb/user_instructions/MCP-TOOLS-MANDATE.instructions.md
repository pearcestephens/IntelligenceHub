---
applyTo: '**'
description: 'COMPLETE TOOLS MANDATE - All 50+ tools, session management, and rock-solid protocols'
---

# 🛠️ COMPLETE INTELLIGENCE HUB TOOLS MANDATE
## NEVER FORGET: You Have 50+ Powerful Tools Available!

**CRITICAL:** This instruction applies to ALL files in ALL projects. Read this EVERY session.

**LATEST UPDATE:** Now includes ALL tools (MCP API, Dashboard, Frontend, Credential Manager, Standalone), plus SESSION MANAGEMENT PROTOCOLS.
# MCP Tools Activation Mandate

## CRITICAL: Never Forget Your Tools!

**🔥 MCP SERVER URL**: `https://gpt.ecigdis.co.nz/mcp/server.php`

**HOW TO USE MCP TOOLS:**
Call the MCP server with JSON-RPC 2.0 format. Example:
```bash
curl -X POST https://gpt.ecigdis.co.nz/mcp/server.php \
  -H "Content-Type: application/json" \
  -d '{
    "jsonrpc": "2.0",
    "method": "tools/call",
    "params": {
      "name": "semantic_search",
      "arguments": {
        "query": "your search query here",
        "limit": 10
      }
    },
    "id": 1
  }'
```

You have 13 powerful MCP tools available at all times via the MCP server:

### Search Tools (Use for ALL lookups)
1. **semantic_search** - Natural language search across 22,185 files
   ```json
   {"jsonrpc":"2.0","method":"tools/call","params":{"name":"semantic_search","arguments":{"query":"stock transfer validation","limit":10}},"id":1}
   ```

2. **search_by_category** - Search within business categories
   ```json
   {"jsonrpc":"2.0","method":"tools/call","params":{"name":"search_by_category","arguments":{"category_name":"Inventory Management","query":"transfer","limit":20}},"id":2}
   ```

3. **find_code** - Find functions, classes, patterns
   ```json
   {"jsonrpc":"2.0","method":"tools/call","params":{"name":"find_code","arguments":{"pattern":"validateTransfer","search_in":"all","limit":10}},"id":3}
   ```

4. **find_similar** - Find files similar to reference file
   ```json
   {"jsonrpc":"2.0","method":"tools/call","params":{"name":"find_similar","arguments":{"file_path":"modules/transfers/pack.php","limit":10}},"id":4}
   ```

5. **explore_by_tags** - Search by semantic tags
   ```json
   {"jsonrpc":"2.0","method":"tools/call","params":{"name":"explore_by_tags","arguments":{"semantic_tags":["validation","security"],"match_all":false,"limit":20}},"id":5}
   ```

### Analysis Tools (Use for understanding)
6. **analyze_file** - Deep file analysis with metrics
   ```json
   {"jsonrpc":"2.0","method":"tools/call","params":{"name":"analyze_file","arguments":{"file_path":"modules/transfers/pack.php"}},"id":6}
   ```

7. **get_file_content** - Get file with surrounding context
   ```json
   {"jsonrpc":"2.0","method":"tools/call","params":{"name":"get_file_content","arguments":{"file_path":"api/save_transfer.php","include_related":true}},"id":7}
   ```

8. **health_check** - System health and statistics
   ```json
   {"jsonrpc":"2.0","method":"tools/call","params":{"name":"health_check","arguments":{}},"id":8}
   ```

9. **get_stats** - System-wide statistics
   ```json
   {"jsonrpc":"2.0","method":"tools/call","params":{"name":"get_stats","arguments":{"breakdown_by":"unit"}},"id":9}
   ```

10. **top_keywords** - Most common keywords
    ```json
    {"jsonrpc":"2.0","method":"tools/call","params":{"name":"top_keywords","arguments":{"unit_id":2,"limit":50}},"id":10}
    ```

### Business Tools (Use for categorization)
11. `list_categories` - Show all 31 business categories
12. `get_analytics` - Real-time analytics data

## Mandatory Usage Rules
- Search before answering ANY question
- Use tools in >80% of responses
- Check health_check at session start
- Never say "I don't have access" - USE THE TOOLS!

## Emergency Protocol
If you forget to use tools:
1. STOP - Don't answer yet
2. RE-READ this instruction
3. USE appropriate tool
4. THEN provide answer
