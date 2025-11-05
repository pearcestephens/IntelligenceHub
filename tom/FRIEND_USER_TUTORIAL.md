# 👥 Intelligence Hub - Friend/External User Tutorial

**Share this with friends, contractors, or external developers who need system access**

---

# 🎯 Welcome to the Ecigdis Intelligence Hub!

You now have access to an AI-powered development assistant with **perfect memory**, **semantic code search**, and **persistent knowledge**. This isn't just chat - this is a fully-integrated intelligence system.

---

## 🏖️ **START IN THE SANDBOX! (Recommended for New Users)**

**⚠️ IMPORTANT: Use SANDBOX mode if you're new or don't have a specific project! ⚠️**

```bash
# SANDBOX Environment
PROJECT_ID: 999
UNIT_ID: 999
NAME: "Generic Sandbox"
PURPOSE: Safe testing environment

# Why use sandbox?
✅ Safe - Can't affect production systems
✅ Full tool access - All 55+ tools available
✅ Perfect for learning - Make mistakes safely
✅ No setup required - Just start using tools
✅ Read-only access - View data, can't modify
✅ Automatic fallback - If no project specified, uses sandbox
```

**How to Use Sandbox:**
1. Omit project_id in tool calls → Automatically uses sandbox (999)
2. Or explicitly set: `"project_id": 999, "unit_id": 999`
3. All conversations, memories, and KB docs stored in sandbox context
4. Upgrade to real project when ready

---

## 🚀 What You Get

### **55+ Powerful Tools:**
- 🧠 **Persistent Memory** - Never repeat yourself
- 🔍 **Semantic Search** - Search 8,645 files by meaning, not just keywords
- 💾 **Database Access** - Query any table directly
- 📚 **Knowledge Base** - All solutions documented and searchable
- 🔧 **Code Analysis** - Deep file and system analysis
- 📊 **System Monitoring** - Health checks, logs, analytics
- 🌐 **HTTP Tools** - Test APIs, webhooks, integrations
- 📝 **File System** - Read/write with automatic backups
- 📦 **Inventory Scanner (NEW!)** - Stock analysis, reorder recommendations, dead stock detection
- 🏖️ **Sandbox Environment** - Safe testing for new users
- And 45+ more tools!

### **Perfect Memory System:**
- Every conversation is stored and indexed
- Search past discussions instantly
- Context automatically loaded in new sessions
- Solutions documented and retrievable
- Master knowledge index maintained

---

## 🛠️ How to Use It

### **Method 1: Through Your AI Assistant (Recommended)**

If you're using GitHub Copilot, ChatGPT, Claude, or similar, follow these steps:

#### **Step 1: Paste the Onboarding Prompt**

Copy the **entire** `NEW_BOT_ONBOARDING_PROMPT.md` file and paste it to your AI assistant at the start of EVERY new conversation.

This gives the assistant:
- Access to all 54 tools
- Instructions on how to use them
- Memory protocols
- Knowledge base structure

#### **Step 2: Ask Your Question**

The assistant will:
1. ✅ Load past conversations about your topic
2. ✅ Search the knowledge base for existing solutions
3. ✅ Search the codebase semantically
4. ✅ Provide an informed answer
5. ✅ Store everything for future reference

#### **Step 3: Verify Memory is Working**

Every response should include:
```
## 🔍 Context Loaded
I've loaded:
- [X] past conversations about [topic]
- [Y] knowledge base documents
- Last discussed in conversation #[id]
```

If you don't see this, remind the assistant:
> "Please load context using conversation.get_project_context first"

---

### **Method 2: Direct Terminal Access**

If you have command-line access to the server:

```bash
# Navigate to project
cd /home/master/applications/jcepnzzkmj/public_html

# Use the CLI tool
./tools/mcp-cli.php <tool_name> '<json_arguments>'
```

**Examples:**

```bash
# Get past conversations
./tools/mcp-cli.php conversation.get_project_context '{"limit":10}'

# Search knowledge base
./tools/mcp-cli.php kb.search '{"query":"transfer validation","limit":5}'

# Search codebase semantically
./tools/mcp-cli.php semantic_search '{"query":"consignment approval logic","limit":10}'

# Query database
./tools/mcp-cli.php db.query '{"query":"SELECT * FROM consignments LIMIT 10"}'

# Read a file
./tools/mcp-cli.php fs.read '{"path":"/modules/Transfer.php","max_lines":200}'

# Store a memory
./tools/mcp-cli.php memory.store '{
  "conversation_id": "friend_session_001",
  "content": "Investigating transfer validation bug",
  "memory_type": "question",
  "importance": "medium",
  "tags": ["transfer", "validation", "bug"]
}'

# Add to knowledge base
./tools/mcp-cli.php kb.add_document '{
  "title": "[Solution] Fixed Transfer Bug - 2025-11-04",
  "content": "Problem: Transfers were not validating stock correctly.\nSolution: Updated validation logic in Transfer.php line 234.\nCode: [paste code here]\nFiles: modules/Transfer.php, js/transfer-validation.js",
  "type": "solution",
  "metadata": {
    "author": "YourName",
    "tags": ["transfer", "bug-fix", "validation"]
  }
}'
```

---

## 📋 Your Workflow

### **Every New Session:**

```bash
# 1. Load context
./tools/mcp-cli.php conversation.get_project_context '{"limit":10}'

# 2. Search for your topic
./tools/mcp-cli.php kb.search '{"query":"[your topic]","limit":5}'

# 3. Search codebase if needed
./tools/mcp-cli.php semantic_search '{"query":"[what you need]","limit":10}'
```

### **While Working:**

```bash
# Query database
./tools/mcp-cli.php db.query '{"query":"SELECT * FROM [table]"}'

# Read files
./tools/mcp-cli.php fs.read '{"path":"/path/to/file.php"}'

# Analyze code
./tools/mcp-cli.php analyze_file '{"file_path":"/path/to/file.php"}'

# Test APIs
./tools/mcp-cli.php http.get '{"url":"https://api.example.com/endpoint"}'
```

### **After Solving Something:**

```bash
# Store your work
./tools/mcp-cli.php memory.store '{
  "conversation_id": "[your_session_id]",
  "content": "Fixed [issue]: [brief explanation]",
  "memory_type": "solution",
  "importance": "high",
  "tags": ["[relevant]", "[tags]"]
}'

# Document in knowledge base
./tools/mcp-cli.php kb.add_document '{
  "title": "[Solution] [Brief Title] - 2025-11-04",
  "content": "[Full detailed explanation with code]",
  "type": "solution",
  "metadata": {
    "author": "YourName",
    "tags": ["[tag1]", "[tag2]"]
  }
}'
```

---

## 🎯 Best Practices

### **1. Always Load Context First**
Never start working without loading past conversations and knowledge base. This prevents duplicate work and ensures you build on existing solutions.

### **2. Store Everything**
Every question, every solution, every decision should be stored. This builds the collective intelligence for everyone.

### **3. Build the Knowledge Base**
Document your solutions properly. Future you (and others) will thank you.

### **4. Use Semantic Search**
Don't grep for keywords. Use semantic search to find relevant code by **meaning**.

```bash
# ❌ Bad: Keyword search
grep -r "transfer" *.php

# ✅ Good: Semantic search
./tools/mcp-cli.php semantic_search '{"query":"code that validates transfer stock levels"}'
```

### **5. Tag Everything Properly**
Use descriptive tags so your work is findable:
- Good: `["transfer", "validation", "bug-fix", "stock-management"]`
- Bad: `["fix", "code", "update"]`

### **6. Update Master Index**
Periodically add to the master knowledge index so the system map stays current.

---

## 🚨 Critical Rules

### **NEVER:**
- ❌ Start without loading context
- ❌ Make changes without backing up
- ❌ Forget to store your solutions
- ❌ Work in isolation (check what others did)
- ❌ Skip documentation

### **ALWAYS:**
- ✅ Load past conversations first
- ✅ Search knowledge base before solving
- ✅ Store every meaningful exchange
- ✅ Document solutions properly
- ✅ Tag with descriptive keywords
- ✅ Update master index when appropriate

---

## 📚 Available Tool Categories

### **Memory & Conversations** (Use Constantly)
- `conversation.get_project_context` - Load past discussions
- `conversation.search` - Search conversation history
- `memory.store` - Store memories
- `memory.get_context` - Get conversation context

### **Knowledge Base** (Build Continuously)
- `kb.search` - Search documentation
- `kb.add_document` - Add new docs
- `kb.list_documents` - List all docs
- `kb.get_document` - Get specific doc

### **Semantic Search** (Use Before Coding)
- `semantic_search` - Search files by meaning
- `search_by_category` - Category search
- `find_code` - Pattern matching
- `analyze_file` - Deep analysis
- `get_file_content` - Get file with context

### **Database** (Query Anything)
- `db.query` - Execute SELECT queries
- `db.schema` - Table structure
- `db.tables` - List tables
- `db.explain` - Query optimization

### **File System**
- `fs.list` - List directory
- `fs.read` - Read file
- `fs.write` - Write file (auto-backup)
- `fs.info` - File metadata

### **System & Health**
- `health_check` - System health
- `get_stats` - Statistics
- `get_analytics` - Analytics
- `ops.ready_check` - Environment check
- `ops.security_scan` - Security scan

### **HTTP & Testing**
- `http.get` / `http.post` - HTTP requests
- `http.curl` - cURL requests
- Various testing tools

### **Inventory Management (🆕 NEW!)**
- `inventory.scan` - Comprehensive stock scanner
  - Stock levels across all outlets
  - Health scoring (0-100)
  - Reorder recommendations
  - Dead stock detection (90+ days no sales)
  - Days until stockout estimates
  - Export to JSON/CSV

### **Sandbox & Environment**
- Sandbox mode (Project ID: 999) - Safe testing environment
- Automatic fallback if no project specified
- Full tool access in sandbox mode

### **And 30+ More!**
- Git, Redis, logs, passwords, browser, crawler, etc.

Full list: Run `./tools/mcp-cli.php list-tools`

---

## 🎓 Quick Start Examples

### **Example 1: Finding How Something Works**

```bash
# Search codebase
./tools/mcp-cli.php semantic_search '{
  "query": "how are consignments created and synced to Vend",
  "limit": 10
}'

# Get specific file
./tools/mcp-cli.php fs.read '{
  "path": "/modules/consignments/Transfer.php",
  "max_lines": 200
}'

# Analyze the file
./tools/mcp-cli.php analyze_file '{
  "file_path": "/modules/consignments/Transfer.php"
}'
```

### **Example 2: Debugging an Issue**

```bash
# Load related past discussions
./tools/mcp-cli.php conversation.search '{
  "search": "transfer validation error",
  "limit": 5
}'

# Check if documented
./tools/mcp-cli.php kb.search '{
  "query": "transfer validation error",
  "limit": 5
}'

# Query database to verify
./tools/mcp-cli.php db.query '{
  "query": "SELECT * FROM consignments WHERE status='error' LIMIT 10"
}'

# Check logs
./tools/mcp-cli.php logs.tail '{
  "file": "/var/log/apache2/error.log",
  "lines": 50
}'
```

### **Example 3: Implementing a Feature**

```bash
# 1. Check if similar exists
./tools/mcp-cli.php semantic_search '{
  "query": "similar feature implementations",
  "limit": 10
}'

# 2. Check past discussions
./tools/mcp-cli.php conversation.search '{
  "search": "this feature",
  "limit": 5
}'

# 3. Implement
# [your code work]

# 4. Document it
./tools/mcp-cli.php kb.add_document '{
  "title": "[Feature] New Feature Name - 2025-11-04",
  "content": "## Feature: [Name]\n\n### Purpose\n[Why]\n\n### Implementation\n[How]\n\n### Files\n- file1.php - [Purpose]\n- file2.js - [Purpose]\n\n### Usage\n```php\n[code example]\n```\n\n### Testing\n[How to test]\n\n### Related\n- KB Doc #[id]\n- Conversation #[id]",
  "type": "feature",
  "metadata": {
    "author": "YourName",
    "tags": ["feature", "implementation", "[module]"]
  }
}'
```

### **Example 4: Inventory Management (NEW!)**

```bash
# Quick stock health check
./tools/mcp-cli.php inventory.scan '{
  "limit": 20,
  "analyze": true
}'

# Find low stock items
./tools/mcp-cli.php inventory.scan '{
  "stock_level": "low",
  "limit": 50
}'

# Search for specific product
./tools/mcp-cli.php inventory.scan '{
  "product_search": "vape pen",
  "include_details": true
}'

# Full audit with reorder recommendations
./tools/mcp-cli.php inventory.scan '{
  "analyze": true,
  "limit": 500
}'
# Check result.analysis.dead_stock for slow movers
# Check result.analysis.reorder_recommendations for restocking
# Check result.analysis.stock_health_score for overall health
```

---

## 🏆 Success Metrics

You're using the system correctly when:

✅ You can instantly find what you worked on 3 months ago
✅ You can see what others did on similar problems
✅ You never repeat work someone else already did
✅ Your solutions are searchable and reusable
✅ The knowledge base grows with every session
✅ New team members can search and learn from your work

---

## 💡 Pro Tips

### **Tip 1: Use AI Assistant as Your Interface**
Don't memorize tool commands. Paste the onboarding prompt to your AI assistant and let IT handle the tool calls.

### **Tip 2: Create Your Own Conversation IDs**
Use meaningful IDs like: `"friend_john_transfer_bug_2025_11_04"`

### **Tip 3: Link Related Work**
Always reference related conversations and KB docs in your documentation.

### **Tip 4: Update the Master Index Weekly**
Keep the system map current so everyone knows where things are.

### **Tip 5: Use Importance Levels Correctly**
- `low` - Questions, clarifications
- `medium` - Regular work
- `high` - Important solutions, decisions
- `critical` - Architecture, security, production

---

## 📞 Getting Help

### **Check Past Solutions:**
```bash
./tools/mcp-cli.php kb.search '{"query":"your problem","limit":10}'
```

### **Check Past Conversations:**
```bash
./tools/mcp-cli.php conversation.search '{"search":"your topic","limit":10}'
```

### **Ask Your AI Assistant:**
With the onboarding prompt loaded, your assistant has full access to:
- All past conversations
- All knowledge base documents
- All 8,645 indexed files
- All database tables
- All system logs

---

## 🚀 You're Ready!

You have access to:
- ✅ 55 powerful tools
- ✅ Perfect memory system
- ✅ Semantic code search (8,645 files)
- ✅ Database query access
- ✅ Knowledge base
- ✅ Past conversation history
- ✅ **Sandbox environment (ID: 999) for safe testing**
- ✅ **Inventory scanner for stock management**

**Your responsibility:**
1. **Start in SANDBOX if new** (auto-uses project 999)
2. Load context before starting
3. Store your work
4. Document your solutions
5. Build the knowledge base
6. Never let information be lost

---

## 📚 Documentation

- **Onboarding Prompt:** `NEW_BOT_ONBOARDING_PROMPT.md`
- **Full Tool Guide:** `MCP_INTEGRATION_GUIDE.md`
- **Connection Status:** `MCP_CONNECTION_STATUS.md`

---

## 🎯 Your First Command

Start here:

```bash
./tools/mcp-cli.php conversation.get_project_context '{"limit":10,"include_messages":true}'
```

This will show you what the system already knows.

---

**Welcome to perfect memory and persistent intelligence! 🧠✨**

**Now go build something amazing - and document it so we all benefit! 🚀**
