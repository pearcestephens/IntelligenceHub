# 🎯 MCP Tools Quick Reference Card

**Base URL:** `https://gpt.ecigdis.co.nz/mcp/dispatcher.php`

## 📊 All 13 Tools at a Glance

| Tool | Quick Test Command |
|------|-------------------|
| `search` | `curl "BASE_URL?tool=search&query=authentication"` |
| `analytics` | `curl "BASE_URL?tool=analytics&timeframe=24h"` |
| `health` | `curl "BASE_URL?tool=health"` |
| `stats` | `curl "BASE_URL?tool=stats"` |
| `fuzzy` | `curl "BASE_URL?tool=fuzzy&query=authentikation"` |
| `mysql` | `curl "BASE_URL?tool=mysql&action=query&query=SELECT+1"` |
| `password` | `curl "BASE_URL?tool=password&action=list"` |
| `browser` | `curl "BASE_URL?tool=browser&action=fetch&url=https://example.com"` |
| `crawler` | `curl "BASE_URL?tool=crawler&mode=quick&url=https://example.com"` |
| `database` | `curl "BASE_URL?tool=database&action=analyze"` |
| `redis` | `curl "BASE_URL?tool=redis&action=stats"` |
| `file` | `curl "BASE_URL?tool=file&action=list&path=dashboard"` |
| `logs` | `curl "BASE_URL?tool=logs&action=errors&log_file=php&lines=20"` |

## 🚀 Most Common Use Cases

### Find Code
```bash
curl "https://gpt.ecigdis.co.nz/mcp/dispatcher.php?tool=search&query=YOUR_SEARCH&limit=10"
```

### Check System Health
```bash
curl "https://gpt.ecigdis.co.nz/mcp/dispatcher.php?tool=health"
```

### Find Errors in Logs
```bash
curl "https://gpt.ecigdis.co.nz/mcp/dispatcher.php?tool=logs&action=errors&log_file=php&lines=50"
```

### Analyze Database Table
```bash
curl "https://gpt.ecigdis.co.nz/mcp/dispatcher.php?tool=database&action=analyze&table=TABLE_NAME"
```

### List Files in Directory
```bash
curl "https://gpt.ecigdis.co.nz/mcp/dispatcher.php?tool=file&action=list&path=dashboard/pages"
```

### Check Cache Stats
```bash
curl "https://gpt.ecigdis.co.nz/mcp/dispatcher.php?tool=redis&action=stats"
```

## 📝 Response Format (All Tools)

```json
{
  "success": true,
  "timestamp": "2025-10-29 09:30:15",
  "data": {
    // Tool-specific data here
  },
  "message": "Operation completed successfully"
}
```

## 🎨 Visual Testing

**Open this in your browser:**
https://gpt.ecigdis.co.nz/dashboard/pages/ai-control-center.php

Click **MCP Tools** tab → Test all 13 tools with visual interface!

## 💡 Pro Tips

1. **Pipe to jq for pretty JSON:**
   ```bash
   curl -s "URL" | jq '.'
   ```

2. **Extract specific field:**
   ```bash
   curl -s "URL" | jq '.data.status'
   ```

3. **Save to file:**
   ```bash
   curl -s "URL" > output.json
   ```

4. **Chain tools:**
   ```bash
   FILE=$(curl -s "URL?tool=search&query=auth" | jq -r '.data.results[0].file_path')
   curl "URL?tool=file&action=read&path=$FILE"
   ```

## 🔥 Emergency Debug Workflow

```bash
# 1. Check health
curl "BASE_URL?tool=health"

# 2. Find errors
curl "BASE_URL?tool=logs&action=errors&log_file=php&lines=50"

# 3. Search for error in code
curl "BASE_URL?tool=search&query=ERROR_MESSAGE"

# 4. Analyze problematic file
curl "BASE_URL?tool=file&action=analyze&path=FOUND_FILE_PATH"
```

## 📚 Full Documentation

See: `/home/master/applications/hdgwrzntwa/public_html/MCP_TOOLS_USAGE_GUIDE.md`

---

**All 13 tools are operational! 🎉**
**Login:** admin / admin123
