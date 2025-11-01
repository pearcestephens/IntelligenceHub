# Intelligence Hub System Architecture Overview

**Version:** 1.0.0  
**Date:** October 25, 2025  
**Status:** DOCUMENTATION OF INTENDED DESIGN vs CURRENT STATE

---

## 🎯 System Purpose

Intelligence Hub is a centralized knowledge repository that:
- Scans 4 business application servers
- Extracts and stores intelligence from code, docs, and business data
- Provides AI-powered search and analysis
- Enables agents/bots to query knowledge across all systems

---

## 🏗️ High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                   Intelligence Hub Server                    │
│                   (hdgwrzntwa - gpt.ecigdis.co.nz)          │
└─────────────────────────────────────────────────────────────┘
                              ▲
                              │
                    ┌─────────┼─────────┐
                    │         │         │
            ┌───────▼───┐ ┌───▼────┐ ┌──▼─────┐
            │   CIS     │ │ Retail │ │Wholsle │
            │jcepnzzkmj │ │dvaxgvsx│ │fhrehrp │
            └───────────┘ └────────┘ └────────┘
```

---

## 📊 Data Flow (INTENDED DESIGN)

```
Source Files (on remote servers)
         │
         ▼
┌─────────────────┐
│ Neural Scanner  │ ◄── Scans code, docs, business data
└────────┬────────┘
         │
         ├──► Binary files?
         │    │
         │    ▼
         │    intelligence_files
         │    ├── PDFs
         │    ├── Images (PNG, JPG)
         │    ├── Archives (ZIP)
         │    └── file_content: BLOB
         │
         └──► Text files?
              │
              ▼
              intelligence_content
              ├── PHP, JS, HTML, CSS
              ├── JSON, YAML, MD
              ├── metadata + scores
              │
              ├──► intelligence_content_text
              │    └── Full searchable text
              │
              ├──► neural_patterns
              │    ├── Functions
              │    ├── Classes
              │    ├── API endpoints
              │    └── DB tables
              │
              └──► neural_pattern_relationships
                   ├── Function calls
                   ├── Class inheritance
                   └── Cross-module deps
```

---

## 🚨 CURRENT STATE (BROKEN)

### ❌ Problem: Everything in intelligence_files

**Current Reality:**
```sql
SELECT file_type, COUNT(*) FROM intelligence_files GROUP BY file_type;

-- Results:
-- code_intelligence: 36,315 files ← WRONG TABLE
-- documentation: 13,289 files     ← WRONG TABLE
-- business_intelligence: 5,753    ← WRONG TABLE
-- Total: 55,357 TEXT files in BINARY table
```

**What SHOULD be:**
```sql
-- intelligence_files (binary only)
-- Images: ~500 files
-- PDFs: ~200 files
-- Archives: ~50 files

-- intelligence_content (text files)
-- Code: 36,315 files ✓
-- Documentation: 13,289 files ✓
-- Business data: 5,753 files ✓
```

### ❌ Problem: Empty intelligent tables

```sql
SELECT COUNT(*) FROM intelligence_content;        -- 1 row (WRONG)
SELECT COUNT(*) FROM intelligence_content_text;   -- 0 rows (WRONG)
SELECT COUNT(*) FROM neural_patterns;             -- 0 rows (WRONG)
SELECT COUNT(*) FROM neural_pattern_relationships; -- 0 rows (WRONG)
```

**Should be:**
- intelligence_content: 55,000+ rows
- intelligence_content_text: 55,000+ rows
- neural_patterns: 50,000+ patterns
- neural_pattern_relationships: 200,000+ relationships

---

## 🔧 Core Components

### 1. Neural Scanner (api_neural_scanner.php)

**Current Behavior:**
```php
// Scans files and puts EVERYTHING in intelligence_files
if (in_array($extension, ['md', 'txt', 'rst'])) {
    $this->extractDocumentation($file, $file_relative); // → intelligence_files
} elseif (in_array($extension, ['php', 'js', 'py', 'java'])) {
    $this->extractCodeIntelligence($file, $file_relative); // → intelligence_files
} elseif (in_array($extension, ['json', 'yaml', 'yml', 'xml'])) {
    $this->extractBusinessIntelligence($file, $file_relative); // → intelligence_files
}
```

**Should Be:**
```php
// Route to correct table based on file type
if (isBinaryFile($extension)) {
    $this->storeInIntelligenceFiles($file); // Binary → intelligence_files
} else {
    $this->storeInIntelligenceContent($file); // Text → intelligence_content
    $this->extractFullText($file); // → intelligence_content_text
    $this->extractPatterns($file); // → neural_patterns
}
```

### 2. Intelligence Scorer (intelligent_scorer.php)

**Current Behavior:**
```php
// Scores files IN-PLACE in intelligence_files
UPDATE intelligence_files 
SET intelligence_score = ?, complexity_score = ?, 
    quality_score = ?, business_value_score = ?
WHERE file_id = ?
```

**Should Score:**
- intelligence_content (text files) - scores metadata
- intelligence_files (binary) - scores based on file properties (size, type, usage)

### 3. Pattern Extractor (NOT YET BUILT)

**Needs to:**
```php
// Extract functions from PHP/JS/Python
INSERT INTO neural_patterns (pattern_type, pattern_name, file_id, ...)
VALUES ('function', 'getUserById', 12345, ...);

// Extract classes
INSERT INTO neural_patterns (pattern_type, pattern_name, ...)
VALUES ('class', 'UserController', ...);

// Map relationships
INSERT INTO neural_pattern_relationships (source_id, target_id, ...)
VALUES (func_id_A, func_id_B, 'calls');
```

---

## 🗄️ Database Tables

### Primary Storage

| Table | Purpose | Current Rows | Should Have |
|-------|---------|--------------|-------------|
| `intelligence_files` | Binary file storage | 55,357 | ~750 |
| `intelligence_content` | Text content + metadata | 1 | ~55,000 |
| `intelligence_content_text` | Searchable full text | 0 | ~55,000 |
| `intelligence_content_types` | Content type lookup | 17 | 17 ✓ |

### Neural Intelligence

| Table | Purpose | Current Rows | Should Have |
|-------|---------|--------------|-------------|
| `neural_patterns` | Functions, classes, APIs | 0 | ~50,000 |
| `neural_pattern_relationships` | Call graphs, deps | 0 | ~200,000 |

### Configuration

| Table | Purpose | Status |
|-------|---------|--------|
| `scanner_ignore_config` | Central ignore patterns | Empty (needs import) |
| `organizations` | Org hierarchy | ✓ Working |
| `business_units` | Server mapping | ✓ Working |

---

## 🔄 Data Transformation Pipeline

### Phase 1: Scanning (WORKING but WRONG)
```
Source Files → Scanner → intelligence_files (ALL FILES)
```

### Phase 2: Scoring (PARTIALLY WORKING)
```
intelligence_files → intelligent_scorer.php → Add scores (10K of 55K done)
```

### Phase 3: Pattern Extraction (NOT BUILT)
```
intelligence_content → Pattern Extractor → neural_patterns + relationships
```

### Phase 4: Search Indexing (NOT BUILT)
```
intelligence_content_text → Full-text indexer → Searchable corpus
```

---

## 🎯 Migration Strategy

### Step 1: Create Separator Script
```php
// migrate_to_correct_tables.php
// - Read intelligence_files
// - If binary → keep in intelligence_files
// - If text → move to intelligence_content + intelligence_content_text
```

### Step 2: Fix Scanner
```php
// api_neural_scanner.php
// - Add isBinaryFile() method
// - Route to correct table based on type
```

### Step 3: Run Migration
```bash
php migrate_to_correct_tables.php --dry-run  # Test first
php migrate_to_correct_tables.php --execute  # Do it
```

### Step 4: Rescan Everything
```bash
php api_neural_scanner.php --server=jcepnzzkmj --full
php api_neural_scanner.php --server=dvaxgvsxmz --full
php api_neural_scanner.php --server=fhrehrpjmu --full
php api_neural_scanner.php --server=hdgwrzntwa --full
```

### Step 5: Score All Content
```bash
php intelligent_scorer.php jcepnzzkmj
php intelligent_scorer.php dvaxgvsxmz
php intelligent_scorer.php fhrehrpjmu
php intelligent_scorer.php hdgwrzntwa
```

### Step 6: Extract Patterns
```bash
php neural_pattern_extractor.php --all
```

---

## 📈 Success Metrics

After migration, we should see:

```sql
-- Binary files only
SELECT COUNT(*) FROM intelligence_files WHERE mime_type LIKE '%image%' OR mime_type LIKE '%pdf%';
-- Expected: ~750 files

-- Text content properly stored
SELECT COUNT(*) FROM intelligence_content;
-- Expected: ~55,000 files

-- Full text indexed
SELECT COUNT(*) FROM intelligence_content_text;
-- Expected: ~55,000 files

-- Patterns extracted
SELECT COUNT(*) FROM neural_patterns;
-- Expected: ~50,000 patterns

-- Relationships mapped
SELECT COUNT(*) FROM neural_pattern_relationships;
-- Expected: ~200,000 relationships
```

---

## 🔐 Security Considerations

- Scanner runs with limited permissions
- Database credentials stored in .env
- API endpoints require authentication
- File content sanitized before storage
- Binary files virus-scanned before storage

---

## 🚀 Performance Targets

- Scanner: 1,000 files/minute
- Scorer: 1,000 files/second
- Pattern extraction: 500 files/minute
- Search: < 200ms for most queries
- Full text search: < 500ms

---

## 📚 Related Documentation

- `DATABASE_DESIGN.md` - Detailed schema docs
- `FILE_CLASSIFICATION.md` - How files are categorized
- `SCORING_SYSTEM.md` - Intelligence scoring algorithms
- `decisions/001_intelligence_tables_separation.md` - Why we separate tables

---

**Last Updated:** October 25, 2025  
**Next Review:** When migration is complete
