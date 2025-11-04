# GitHub Copilot MCP Integration Setup Guide

## 🎯 Overview

This guide helps you connect GitHub Copilot to the Intelligence Hub MCP server, giving it access to:
- Complete system knowledge across 4 satellites
- 78+ database tables with 83,000+ records
- 22,185 indexed files with semantic search
- Real-time business intelligence
- Automated context awareness

## 📋 Prerequisites

1. GitHub Copilot enabled on your account
2. VS Code with GitHub Copilot extension
3. Access to Intelligence Hub API key
4. Network access to `https://gpt.ecigdis.co.nz`

## 🚀 Quick Setup (5 Minutes)

### Step 1: Get Your API Key

```bash
# On Intelligence Hub server
cat ~/private_html/config/.env | grep MCP_API_KEY

# Or retrieve via web:
curl -s https://gpt.ecigdis.co.nz/api/get_api_key.php?auth=YOUR_AUTH_TOKEN
```

### Step 2: Set Environment Variable

**Linux/Mac:**
```bash
export INTELLIGENCE_HUB_API_KEY="your-api-key-here"
echo 'export INTELLIGENCE_HUB_API_KEY="your-api-key-here"' >> ~/.bashrc
source ~/.bashrc
```

**Windows (PowerShell):**
```powershell
$env:INTELLIGENCE_HUB_API_KEY="your-api-key-here"
[Environment]::SetEnvironmentVariable("INTELLIGENCE_HUB_API_KEY", "your-api-key-here", "User")
```

### Step 3: Configure VS Code

Add to your `.vscode/settings.json`:

```json
{
  "github.copilot.advanced": {
    "mcp.enabled": true,
    "mcp.servers": {
      "intelligence-hub": {
        "url": "https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=rpc",
        "description": "Intelligence Hub - Full System Context",
        "transport": {
          "type": "http",
          "method": "POST"
        },
        "authentication": {
          "type": "bearer",
          "token": "${INTELLIGENCE_HUB_API_KEY}"
        }
      }
    }
  }
}
```

### Step 4: Verify Connection

1. Open VS Code
2. Open GitHub Copilot Chat (Ctrl+Shift+I or Cmd+Shift+I)
3. Type: `@workspace /api Use MCP to search for inventory management code`
4. If working, Copilot will query Intelligence Hub and return results

## 🧪 Test Commands

Try these in GitHub Copilot Chat:

```
# Search knowledge base
@workspace Use MCP kb.search to find "stock transfer validation"

# Query database
@workspace Use MCP db.query to show today's sales count

# Read file
@workspace Use MCP fs.read to show config/database.php

# List satellites
@workspace Use MCP satellite.list to show all connected systems

# Get conversation context
@workspace Use MCP memory.get_context to show our conversation history
```

## 📊 Available MCP Tools

### Database Tools
- `db.query` - Execute SELECT queries with parameter binding
- `db.schema` - Get table schema information
- `db.tables` - List all database tables
- `db.explain` - Get query execution plan

### File System Tools
- `fs.read` - Read files (jailed to project)
- `fs.write` - Write files with automatic backup
- `fs.list` - List directory contents
- `fs.info` - Get file/directory metadata

### Knowledge Base Tools
- `kb.search` - Semantic search across 22,185 files
- `kb.add_document` - Add new documentation
- `kb.list_documents` - List KB documents
- `kb.get_document` - Fetch specific document

### Context & Memory Tools
- `memory.get_context` - Retrieve conversation history
- `memory.store` - Store conversation context
- `context.enhance` - Enhance query with system knowledge

### Satellite Coordination Tools
- `satellite.list` - Show all 4 satellites status
- `satellite.sync` - Trigger data synchronization
- `satellite.deploy` - Deploy systems to satellites
- `satellite.health` - Check satellite health

## 🔧 Advanced Configuration

### Multi-Repository Setup

If working across multiple projects, create workspace-level config:

**`workspace.code-workspace`:**
```json
{
  "folders": [
    { "path": "intelligence-hub" },
    { "path": "cis-staff-system" },
    { "path": "retail-website" }
  ],
  "settings": {
    "github.copilot.advanced": {
      "mcp.enabled": true,
      "mcp.servers": {
        "intelligence-hub": {
          "url": "https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=rpc",
          "authentication": {
            "type": "bearer",
            "token": "${INTELLIGENCE_HUB_API_KEY}"
          }
        },
        "cis-direct": {
          "url": "https://staff.vapeshed.co.nz/mcp/server.php?action=rpc",
          "authentication": {
            "type": "bearer",
            "token": "${CIS_MCP_API_KEY}"
          }
        }
      }
    }
  }
}
```

### Custom Tool Mappings

Create shortcuts for frequently used queries:

**`.vscode/mcp-shortcuts.json`:**
```json
{
  "shortcuts": {
    "today-sales": {
      "tool": "db.query",
      "args": {
        "query": "SELECT COUNT(*) FROM vend_sales WHERE DATE(sale_date) = CURDATE()"
      }
    },
    "inventory-search": {
      "tool": "kb.search",
      "args": {
        "query": "inventory management",
        "limit": 10
      }
    },
    "satellite-status": {
      "tool": "satellite.list",
      "args": {}
    }
  }
}
```

## 🔐 Security Best Practices

### API Key Management

1. **Never commit API keys to git:**
   ```bash
   echo "INTELLIGENCE_HUB_API_KEY=*" >> .gitignore
   echo ".vscode/settings.json" >> .gitignore  # If it contains keys
   ```

2. **Use environment variables only:**
   - Store in `.env` file (gitignored)
   - Or use system environment variables
   - Never hardcode in configuration files

3. **Rotate keys regularly:**
   ```bash
   # Generate new key via API
   curl -X POST https://gpt.ecigdis.co.nz/api/rotate_api_key.php \
     -H "Authorization: Bearer $OLD_KEY"
   ```

### Rate Limiting

Intelligence Hub MCP has rate limits:
- **100 requests per minute** per API key
- **1,000 requests per hour** per API key
- **10,000 requests per day** per API key

If exceeded, you'll receive a 429 response.

## 🐛 Troubleshooting

### Connection Issues

**Problem:** MCP not connecting

**Solutions:**
1. Verify API key is set:
   ```bash
   echo $INTELLIGENCE_HUB_API_KEY
   ```

2. Test direct connection:
   ```bash
   curl -X POST https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=health
   ```

3. Check VS Code output logs (View → Output → GitHub Copilot)

### Authentication Errors

**Problem:** 401 Unauthorized

**Solutions:**
1. Regenerate API key
2. Verify environment variable is loaded
3. Restart VS Code to reload environment

### Slow Responses

**Problem:** MCP queries taking >5 seconds

**Solutions:**
1. Check satellite status: `satellite.list`
2. Use more specific queries to reduce result size
3. Add unit_id filter to limit search scope
4. Check network connectivity to gpt.ecigdis.co.nz

## 📚 Usage Examples

### Example 1: Find All Stock Transfer Code
```
@workspace Use MCP to search the knowledge base for "stock transfer"
and show me the top 5 most relevant files with their purposes
```

### Example 2: Query Today's Sales
```
@workspace Query the database using MCP to show:
1. Total sales today
2. Sales by outlet
3. Top 5 products sold
Format as a nice table
```

### Example 3: Read and Analyze Config
```
@workspace Use MCP to:
1. Read config/database.php
2. Read config/automation.json
3. Tell me if the database credentials match
```

### Example 4: Deploy System to CIS
```
@workspace Use MCP satellite tools to:
1. Check CIS (unit_id=2) health
2. Sync latest knowledge base
3. Deploy scanner
4. Confirm deployment status
```

## 🎯 Best Practices

### Effective Prompting

**Good prompts:**
- "Use MCP kb.search to find code related to inventory validation"
- "Query the database using MCP to show sales trends for last 7 days"
- "Use MCP satellite.list to check all system status"

**Poor prompts:**
- "Search for stuff" (too vague)
- "Get me data" (no context)
- "Fix the thing" (no specifics)

### Context Building

Start conversations with context:
```
@workspace I'm working on the CIS staff system (unit_id=2).
Use MCP to:
1. Show me the database schema for user tables
2. Find related authentication code
3. Show recent changes to login functionality
```

## 🔄 Updates & Maintenance

### Keeping Config Updated

Intelligence Hub MCP is continuously updated. Check for updates:

```bash
# Check current version
curl https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=health | jq '.version'

# Get latest configuration
curl https://gpt.ecigdis.co.nz/config/github-copilot-mcp.json > .vscode/mcp-config.json
```

### Auto-Update Script

Add to your project:

**`scripts/update-mcp-config.sh`:**
```bash
#!/bin/bash
echo "Updating MCP configuration..."
curl -s https://gpt.ecigdis.co.nz/config/github-copilot-mcp.json \
  -o .vscode/mcp-config.json
echo "✅ MCP config updated"
echo "Restart VS Code to apply changes"
```

## 📖 Additional Resources

- [Intelligence Hub Documentation](https://gpt.ecigdis.co.nz/_kb/)
- [MCP Protocol Specification](https://gpt.ecigdis.co.nz/_kb/MCP_TOOLS_COMPLETE_GUIDE.md)
- [API Reference](https://gpt.ecigdis.co.nz/_kb/API_REFERENCE.md)
- [Database Schema](https://gpt.ecigdis.co.nz/_kb/DATABASE_ARCHITECTURE_ANALYSIS.md)

## 🆘 Support

If you encounter issues:

1. Check logs: `/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/logs/mcp/`
2. Test health: `curl https://gpt.ecigdis.co.nz/mcp/server_v3.php?action=health`
3. Contact: IT Support or check internal wiki

---

**Version:** 1.0.0
**Last Updated:** 2025-11-04
**Status:** ✅ Production Ready
