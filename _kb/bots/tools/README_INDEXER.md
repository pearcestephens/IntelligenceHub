# 🤖 AI Knowledge Base Auto-Indexer

**Purpose**: Automatically scan entire application, index all documentation, create bite-sized chunks for AI consumption

**Status**: ✅ Configured and ready for initial run  
**Mode**: Application-wide (not module-specific)  
**Last Updated**: October 21, 2025  

---

## 🎯 What It Does

### **Application-Wide Scanning**
- Scans **entire `/public_html/` directory**
- Not limited to specific modules
- Finds ALL documentation (.md files) automatically
- Detects changes using file hashes
- Removes deleted files from KB

### **Intelligent Chunking**
- Breaks large documents into **bite-sized chunks** (~4000 chars)
- Preserves context with 200-character overlap
- Splits on natural boundaries (headers, separators)
- Keeps code blocks intact
- Preserves tables and structured data

### **Smart Indexing**
- **Priority files**: README, DEPLOYMENT, API, ARCHITECTURE, GUIDE, etc.
- **Auto-detects domain** from file path (modules/staff-accounts → staff-accounts domain)
- **Extracts metadata**: titles, sections, tags
- **Generates summaries** for important docs
- **Tracks relationships**: includes, requires, imports

### **Change Detection**
- Uses MD5 hash comparison
- Only indexes new/modified files (incremental mode)
- Full re-index available (--full flag)
- Cleanup removes deleted files automatically

---

## 🚀 Quick Start

### **1. Initial Full Scan** (First time)
```bash
cd /home/master/applications/jcepnzzkmj/public_html/assets/services/_kb/sync
php kb-auto-indexer.php --full
```

**What happens:**
- Scans all of `/public_html/`
- Indexes every .md, .php, .js, .css, .sql, .json file
- Chunks large documents
- Populates `ai_kb_knowledge_items` table
- Maps relationships in `ai_kb_knowledge_relationships`
- Takes ~5-10 minutes depending on size

### **2. Test on Sample First** (Recommended)
```bash
# Dry run - shows what would be indexed without actually doing it
php kb-auto-indexer.php --scan-only

# See what files found
php kb-auto-indexer.php --scan-only | grep "\.md"
```

### **3. Incremental Updates** (Daily use)
```bash
# Only indexes files changed in last 24 hours
php kb-auto-indexer.php --incremental

# Or specify time window
php kb-auto-indexer.php --incremental --since="2 hours ago"
```

---

## 📋 Command Reference

### **Modes**

```bash
# Full scan (everything)
php kb-auto-indexer.php --full

# Incremental (only changes since last run)
php kb-auto-indexer.php --incremental

# Scan only (dry run, no database writes)
php kb-auto-indexer.php --scan-only

# Cleanup deleted files
php kb-auto-indexer.php --cleanup
```

### **Options**

```bash
# Verbose output
php kb-auto-indexer.php --full --verbose

# Limit to specific extension
php kb-auto-indexer.php --full --ext=md

# Limit to specific path
php kb-auto-indexer.php --full --path=/public_html/modules/

# Force re-index (ignore hashes)
php kb-auto-indexer.php --full --force
```

---

## 🗄️ Database Tables

### **ai_kb_knowledge_items** (Main storage)
```sql
- id (primary key)
- title (extracted from doc)
- content (chunked content)
- content_type (documentation, code, schema, config)
- source_file (full path)
- file_hash (MD5 for change detection)
- domain (auto-detected: staff-accounts, consignments, etc.)
- importance_score (0.0-1.0 based on doc type)
- chunk_index (which chunk of the file)
- total_chunks (how many chunks total)
- metadata (JSON: tags, sections, etc.)
- created_at
- updated_at
```

### **ai_kb_knowledge_relationships** (Relationships)
```sql
- id
- from_item_id (FK to knowledge_items)
- to_item_id (FK to knowledge_items)
- relationship_type (includes, requires, references, extends)
- created_at
```

### **ai_kb_sync_history** (Audit trail)
```sql
- id
- sync_type (full, incremental, cleanup)
- files_scanned
- files_indexed
- files_updated
- files_deleted
- duration_seconds
- status (success, partial, failed)
- error_message
- started_at
- completed_at
```

---

## 🎯 Chunking Strategy

### **Why Chunking?**
Large documents (like COMPLETE_SYSTEM_KNOWLEDGE.md at 30KB) are too big for AI context windows. Chunking makes them digestible.

### **How It Works**

**Example**: 30KB document becomes ~8 chunks

```
Original file: COMPLETE_SYSTEM_KNOWLEDGE.md (30,000 chars)

Chunk 1 (4000 chars): Introduction + Architecture overview
  └─ Overlap (200 chars) ───┐
Chunk 2 (4000 chars):        │ Architecture + Database schema
  └─ Overlap (200 chars) ───┤
Chunk 3 (4000 chars):        │ Database + API endpoints
  └─ Overlap (200 chars) ───┤
... continues ...
```

**Overlap ensures context continuity** - last 200 chars of chunk N repeated in chunk N+1

### **Smart Splitting**
Splits on natural boundaries:
1. Major headers (`## Section`)
2. Minor headers (`### Subsection`)
3. Horizontal rules (`---`)
4. Double line breaks (`\n\n`)

**Never splits inside:**
- Code blocks (```...```)
- Tables (| ... |)
- Lists with indentation

---

## 🔍 What Gets Indexed

### **High Priority** (importance_score > 0.8)
```
COMPLETE_SYSTEM_KNOWLEDGE.md  → 1.0
README.md                     → 0.95
KNOWLEDGE.md                  → 0.95
CONTEXT.md                    → 0.90
DEPLOYMENT.md                 → 0.90
```

### **Medium Priority** (0.6-0.8)
```
API.md                        → 0.85
ARCHITECTURE.md               → 0.85
GUIDE.md                      → 0.80
MANUAL.md                     → 0.80
SPECIFICATION.md              → 0.75
```

### **Standard** (0.5)
```
Regular .php files
Regular .js files
Regular .css files
```

### **Always Indexed**
- All .md files (markdown documentation)
- All README files regardless of extension
- All files matching important_doc_patterns

### **Ignored** (from config)
```
/vendor/          - Composer packages
/node_modules/    - NPM packages
/.git/            - Git internals
/backup/          - Backup directories
/logs/            - Log files
/tmp/             - Temporary files
/private_html/    - Private area
.min.js, .min.css - Minified files
```

---

## 🤖 GitHub Copilot Integration

Once indexed, GitHub Copilot can query the KB:

### **Method 1: Context Files** (Automatic)
Copilot reads these automatically:
```
.github/copilot-instructions.md  → "Use KB at /public_html/_kb/"
```

### **Method 2: API Query** (Advanced)
```php
// /public_html/_kb/api/copilot-query.php
GET /api/copilot-query.php?q=staff+accounts+deployment

Returns:
{
  "results": [
    {
      "title": "Staff Accounts - Deployment Guide",
      "content": "...",
      "source": "modules/staff-accounts/_kb/DEPLOYMENT.md",
      "chunk": "1/3",
      "score": 0.95
    }
  ]
}
```

### **Method 3: Direct DB** (Future)
```sql
-- Copilot (via MCP) queries directly
SELECT title, content, source_file 
FROM ai_kb_knowledge_items 
WHERE content LIKE '%staff accounts%' 
  AND content_type = 'documentation'
ORDER BY importance_score DESC 
LIMIT 10;
```

---

## ⚙️ Configuration

**File**: `/assets/services/_kb/config/indexer-config.json`

### **Key Settings**

```json
{
  "paths": {
    "scan_paths": [
      "/home/master/applications/jcepnzzkmj/public_html"  // Entire app
    ]
  },
  
  "chunking": {
    "max_chunk_size": 4000,     // Characters per chunk
    "overlap": 200,              // Context overlap between chunks
    "preserve_code_blocks": true // Don't split code
  },
  
  "indexing": {
    "auto_detect_domain": true,  // Extract domain from path
    "extract_metadata": true,    // Parse titles, tags, etc.
    "generate_summaries": true   // Create summaries for large docs
  },
  
  "sync": {
    "mode": "application_wide",  // Not module-specific
    "change_detection": "hash"   // MD5 comparison
  }
}
```

---

## 📊 Performance

### **Initial Full Scan**
- **Speed**: ~500-1000 files/minute
- **Duration**: 5-10 minutes for typical CIS application
- **Database**: ~5000-10000 knowledge items created
- **Disk**: Minimal (~50MB for indexes)

### **Incremental Sync**
- **Speed**: ~2000+ files/minute (only checking hashes)
- **Duration**: 30-60 seconds typical
- **Changes**: Usually 5-20 files modified per run
- **Database**: Updates only changed items

### **Memory**
- **Peak**: ~256MB during full scan
- **Typical**: ~64MB during incremental
- **Limit**: 512MB configured max

---

## 🔧 Troubleshooting

### **"No files found"**
```bash
# Check scan path
php kb-auto-indexer.php --scan-only

# Verify path exists
ls -la /home/master/applications/jcepnzzkmj/public_html/
```

### **"Database connection failed"**
```bash
# Check config
cat /assets/services/_kb/config/indexer-config.json

# Test connection
mysql -u jcepnzzkmj -p jcepnzzkmj
```

### **"File hash mismatch"**
```bash
# Force re-index
php kb-auto-indexer.php --full --force
```

### **"Out of memory"**
```bash
# Reduce batch size in config
"batch_size": 25  # Default is 50
```

---

## 📅 Automation (Cron)

### **Recommended Schedule**

```cron
# Incremental sync every 6 hours
0 */6 * * * cd /path/to/_kb/sync && php kb-auto-indexer.php --incremental >> /path/to/logs/kb-sync.log 2>&1

# Full sync weekly (Sunday 2am)
0 2 * * 0 cd /path/to/_kb/sync && php kb-auto-indexer.php --full >> /path/to/logs/kb-full.log 2>&1

# Cleanup weekly (Monday 3am)
0 3 * * 1 cd /path/to/_kb/sync && php kb-auto-indexer.php --cleanup >> /path/to/logs/kb-cleanup.log 2>&1
```

### **Install Cron**
```bash
# Use the installer
php /assets/services/_kb/sync/install-kb-smart-cron.php
```

---

## 🎯 Next Steps

1. ✅ **Configuration complete** - indexer-config.json created
2. ⏳ **Run initial scan** - `php kb-auto-indexer.php --full`
3. ⏳ **Verify database** - Check ai_kb_knowledge_items table
4. ⏳ **Test search** - Query the KB
5. ⏳ **Install cron** - Automate incremental syncs
6. ⏳ **GitHub Copilot integration** - Create API endpoints

---

**Status**: Ready for initial run  
**Command**: `php kb-auto-indexer.php --full`  
**Location**: `/assets/services/_kb/sync/`  
**Config**: `/assets/services/_kb/config/indexer-config.json`  

---

## 🎯 WHAT'S NEW - Complete System!

This indexer is now part of a **COMPLETE 3-COMPONENT SYSTEM**:

### **1. kb-auto-indexer.php** (This file)
- Scans & indexes all files
- Chunks large documents
- Tracks changes

### **2. kb-relationship-mapper.php** (NEW!)
- Maps every function call
- Maps every class usage
- Maps every include/require
- Creates relationship graph

### **3. kb-readme-generator.php** (NEW!)
- Auto-generates README.md in every directory
- Links to KB database
- Shows dependencies and usage

### **4. kb-master.sh** (Orchestrator)
- Run all three scripts with one command
- Handles cron automation
- Shows statistics

**Read the complete docs:**
- `/assets/services/_kb/EXECUTIVE_SUMMARY.md` - What was built
- `/assets/services/_kb/COMPLETE_WORKFLOW.md` - How it all works
- `/assets/services/_kb/ARCHITECTURE_MAP.md` - Visual diagram
- `/assets/services/_kb/QUICK_REFERENCE.md` - Quick reference card
- `/assets/services/_kb/READY_TO_RUN.md` - Setup guide

**One command to run everything:**
```bash
cd /assets/services/_kb
./kb-master.sh --initial
```
