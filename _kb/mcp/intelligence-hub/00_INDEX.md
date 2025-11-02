# Intelligence Hub - Complete Documentation Index

**Location:** `_kb/mcp/intelligence-hub/`  
**Last Updated:** November 2, 2025  
**Status:** ✅ Complete & Organized

---

## 📚 Core Documentation (Read in Order)

### Foundation
1. **[01_SYSTEM_OVERVIEW.md](01_SYSTEM_OVERVIEW.md)**
   - System architecture and components
   - Directory structure
   - Key concepts and terminology
   - ~220 lines

2. **[02_MCP_SERVER.md](02_MCP_SERVER.md)**
   - Model Context Protocol implementation
   - JSON-RPC 2.0 specification
   - Tool registry and management
   - Meta endpoint documentation
   - ~450 lines

3. **[03_AI_AGENT_ENDPOINTS.md](03_AI_AGENT_ENDPOINTS.md)**
   - Chat API (chat.php)
   - Streaming API (chat_stream.php)
   - Tool invocation (invoke.php)
   - Memory management (memory_upsert.php)
   - Health checks (healthz.php, readyz.php)
   - ~540 lines

### Implementation Details
4. **[04_DATABASE_SCHEMA.md](04_DATABASE_SCHEMA.md)**
   - Complete database schema (11 tables)
   - Foreign key relationships
   - Index definitions
   - Example queries
   - ~500 lines

5. **[05_TOOLS_REFERENCE.md](05_TOOLS_REFERENCE.md)**
   - All 8 local tools documented
   - Tool schemas and parameters
   - Security constraints
   - Usage examples
   - ~800 lines

6. **[06_TELEMETRY_LOGGING.md](06_TELEMETRY_LOGGING.md)**
   - Telemetry class and methods
   - Logging patterns
   - Monitoring queries
   - Retention policies
   - ~500 lines

### Operations & Maintenance
7. **[07_SECURITY.md](07_SECURITY.md)**
   - Authentication mechanisms
   - Path validation (secure_path)
   - SQL injection prevention
   - HTTPS enforcement
   - Backup procedures
   - ~650 lines

8. **[08_DEPLOYMENT.md](08_DEPLOYMENT.md)**
   - Server requirements
   - Nginx configuration
   - .env setup reference
   - Smoke test procedures
   - Deployment checklist
   - ~700 lines

9. **[09_TROUBLESHOOTING.md](09_TROUBLESHOOTING.md)**
   - Common errors and solutions
   - Debug techniques
   - Log file locations
   - FAQ with answers
   - ~850 lines

### Code Examples & Setup
10. **[10_API_EXAMPLES.md](10_API_EXAMPLES.md)**
    - Python examples
    - PHP examples
    - JavaScript examples
    - cURL commands
    - Full integration examples
    - ~950 lines

11. **[11_VSCODE_MCP_SETUP.md](11_VSCODE_MCP_SETUP.md)** ⭐ NEW
    - VS Code MCP configuration
    - Complete independence from GitHub Copilot
    - User settings configuration
    - Environment variables setup
    - Custom extension building guide
    - Testing procedures
    - ~1,000 lines

---

## 📋 Supporting Documentation

### Project Reports
- **[DOCUMENTATION_COMPLETE.md](DOCUMENTATION_COMPLETE.md)** - Documentation completion summary
- **[MASTER_INDEX.md](MASTER_INDEX.md)** - Original master index
- **[README.md](README.md)** - Quick start guide

### Audits & Analysis
- **[INTELLIGENCE_HUB_COMPLETE_AUDIT.md](INTELLIGENCE_HUB_COMPLETE_AUDIT.md)** - Complete system audit
- **[COMPREHENSIVE_AUDIT_REPORT.md](COMPREHENSIVE_AUDIT_REPORT.md)** - Comprehensive audit results
- **[MCP_DOMAIN_VERIFICATION.md](MCP_DOMAIN_VERIFICATION.md)** - Domain verification results

---

## 🎯 Quick Navigation

### By Use Case

**Getting Started:**
→ Start with [01_SYSTEM_OVERVIEW.md](01_SYSTEM_OVERVIEW.md)

**Building Integrations:**
→ [10_API_EXAMPLES.md](10_API_EXAMPLES.md) for code samples  
→ [02_MCP_SERVER.md](02_MCP_SERVER.md) for MCP protocol

**VS Code Setup:**
→ [11_VSCODE_MCP_SETUP.md](11_VSCODE_MCP_SETUP.md) for complete guide ⭐

**Troubleshooting Issues:**
→ [09_TROUBLESHOOTING.md](09_TROUBLESHOOTING.md) for solutions

**Understanding Tools:**
→ [05_TOOLS_REFERENCE.md](05_TOOLS_REFERENCE.md) for all tools

**Deployment:**
→ [08_DEPLOYMENT.md](08_DEPLOYMENT.md) for deployment guide

**Database Work:**
→ [04_DATABASE_SCHEMA.md](04_DATABASE_SCHEMA.md) for schema

**Security:**
→ [07_SECURITY.md](07_SECURITY.md) for security practices

---

## 📊 Documentation Statistics

- **Total Files:** 18
- **Core Documentation:** 11 files (6,160+ lines)
- **Supporting Docs:** 7 files
- **Code Examples:** 4 languages (Python, PHP, JavaScript, cURL)
- **Total Coverage:** Architecture, APIs, Database, Tools, Security, Deployment, Troubleshooting, Examples, VS Code Setup

---

## 🔗 Related Documentation

### Other _kb Locations

- **Main Index:** `_kb/INDEX.md`
- **MCP Tools:** `_kb/MCP_TOOLS_COMPLETE_GUIDE.md`
- **System Knowledge:** `_kb/COMPLETE_SYSTEM_KNOWLEDGE.md`
- **Architecture:** `_kb/architecture/`
- **Audits:** `_kb/audits/`
- **Completed Projects:** `_kb/documentation/completed-projects/intelligence-hub/`

### External Resources

- **GitHub Repository:** https://github.com/pearcestephens/IntelligenceHub.git
- **Live Server:** https://gpt.ecigdis.co.nz
- **MCP Endpoint:** https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php

---

## 🚀 Quick Start Commands

### Test MCP Server
```bash
curl -X POST https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php \
  -H "Authorization: Bearer $INTELLIGENCE_HUB_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"jsonrpc":"2.0","method":"tools/list","id":1}' | jq
```

### Test Chat Endpoint
```bash
curl -X POST https://gpt.ecigdis.co.nz/api/chat.php \
  -H "Authorization: Bearer $INTELLIGENCE_HUB_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{"provider":"openai","model":"gpt-4o-mini","session_key":"test","messages":[{"role":"user","content":"Hello!"}]}' | jq
```

### Setup VS Code
```bash
# Set API key
export INTELLIGENCE_HUB_API_KEY="your_mcp_api_key_here"

# Create VS Code config
cat > .vscode/mcp.json << 'EOF'
{
  "mcpServers": {
    "intelligence-hub": {
      "type": "http",
      "url": "https://gpt.ecigdis.co.nz/assets/services/ai-agent/mcp/server_v3.php",
      "headers": {
        "Authorization": "Bearer ${INTELLIGENCE_HUB_API_KEY}"
      }
    }
  }
}
EOF
```

---

## 📅 Version History

- **v1.0** (Nov 2, 2025) - Initial complete documentation (files 01-10)
- **v1.1** (Nov 2, 2025) - Added VS Code MCP setup guide (file 11)
- **v1.2** (Nov 2, 2025) - Reorganized into _kb structure

---

## 💡 Tips

1. **Read in order** for first-time users (01 → 11)
2. **Use search** for specific topics (Ctrl+F in files)
3. **Check examples** before writing code (10_API_EXAMPLES.md)
4. **Test thoroughly** using the troubleshooting guide (09_TROUBLESHOOTING.md)
5. **Keep docs updated** as system evolves

---

**Everything you need to build, deploy, and integrate with Intelligence Hub is here! 🎉**

*Documentation maintained by: Intelligence Hub Team*  
*Contact: pearce.stephens@ecigdis.co.nz*
