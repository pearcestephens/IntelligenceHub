# ADR 001: Intelligence Tables Separation

**Date:** October 25, 2025  
**Status:** ACCEPTED (but NOT YET IMPLEMENTED)  
**Deciders:** AI Agent, User (Pearce)

---

## Context

The Intelligence Hub was designed to store and analyze content from 4 business servers. During implementation, we discovered that:

1. **Scanner puts EVERYTHING in intelligence_files** (55,357 rows)
2. **intelligence_content is empty** (1 row only)
3. **User questioned:** "ARE YOU SURE INTELLIGENCE FILES IS NOT THINGS LIKE PDF AND IMAGES AND INTELLIGENCE CONTENT IS HTML AND PHP"

This revealed a **fundamental misunderstanding** of the table design.

---

## Decision

We will **separate storage by file nature**, not by file type category:

### intelligence_files → Binary File Storage

**Purpose:** Store binary/non-text files that cannot be parsed for code intelligence

**Contents:**
- PDFs (invoices, reports, manuals)
- Images (PNG, JPG, GIF, SVG, ICO)
- Videos (MP4, AVI, MOV, WebM)
- Archives (ZIP, TAR, GZ, RAR, 7Z)
- Office docs (DOCX, XLSX, PPTX)
- Executables (EXE, DLL, SO, BIN)
- Fonts (TTF, WOFF, WOFF2, EOT)
- Audio (MP3, WAV, OGG)

**Storage Method:**
```sql
CREATE TABLE intelligence_files (
    file_id BIGINT PRIMARY KEY,
    file_path VARCHAR(1000),
    file_name VARCHAR(255),
    file_content LONGBLOB,  -- Actual binary data
    mime_type VARCHAR(100),  -- 'application/pdf', 'image/png', etc.
    file_size BIGINT,
    -- No intelligence scores (not code)
    -- No complexity analysis (can't parse)
);
```

**Example Row:**
```sql
INSERT INTO intelligence_files VALUES (
    1,
    '/invoices/2025/invoice_1234.pdf',
    'invoice_1234.pdf',
    <binary PDF data>,
    'application/pdf',
    524288,  -- 512 KB
    NOW()
);
```

---

### intelligence_content → Text-Based Content with Intelligence

**Purpose:** Store parseable text files that can be analyzed for intelligence, patterns, and relationships

**Contents:**
- **Code:** PHP, JavaScript, Python, Java, Go, Ruby, C++, TypeScript
- **Web:** HTML, CSS, SCSS, LESS, Vue, React JSX
- **Data:** JSON, YAML, XML, CSV, SQL
- **Documentation:** Markdown, TXT, RST, AsciiDoc
- **Configuration:** INI, CONF, ENV, TOML
- **Scripts:** Bash, PowerShell, Batch

**Storage Method:**
```sql
CREATE TABLE intelligence_content (
    content_id BIGINT PRIMARY KEY,
    content_path VARCHAR(1000),
    content_name VARCHAR(255),
    content_hash VARCHAR(64),  -- For deduplication
    content_type_id INT,  -- FK to intelligence_content_types
    mime_type VARCHAR(100),  -- 'text/x-php', 'application/json'
    language_detected VARCHAR(20),  -- 'PHP', 'JavaScript', etc.
    
    -- Intelligence Scores
    intelligence_score DECIMAL(5,2),
    complexity_score DECIMAL(5,2),
    quality_score DECIMAL(5,2),
    business_value_score DECIMAL(5,2),
    
    -- Metadata
    file_size BIGINT,
    encoding VARCHAR(20),  -- 'UTF-8', 'ISO-8859-1'
    last_analyzed TIMESTAMP,
    
    -- Cache optimization
    redis_cached TINYINT(1),
    redis_cache_key VARCHAR(255),
    access_frequency INT,
    last_accessed TIMESTAMP
);
```

**Example Row:**
```sql
INSERT INTO intelligence_content VALUES (
    1,
    '/modules/transfers/pack.php',
    'pack.php',
    'a3f5e9c2d8b1f456...',  -- SHA-256 hash
    1,  -- PHP Code Intelligence
    'text/x-php',
    'PHP',
    85.50,  -- High intelligence (has functions, classes, docs)
    42.30,  -- Moderate complexity
    78.00,  -- Good quality (type hints, error handling)
    65.00,  -- Business value (core transfer system)
    15420,  -- ~15KB
    'UTF-8',
    NOW(),
    1,  -- Cached in Redis
    'intel:content:1',
    247,  -- Accessed 247 times
    NOW()
);
```

---

## Consequences

### Positive

✅ **Correct Semantic Separation**
- Binary files clearly separated from text content
- Easier to understand what each table stores

✅ **Better Intelligence Analysis**
- Can skip binary files entirely in code analysis
- Focus intelligence scoring on parseable content

✅ **Improved Search**
- Full-text search only on intelligence_content (not meaningless binary)
- Better relevance scoring

✅ **Cleaner Codebase**
- Scanner logic clearer: `isBinaryFile() ? storeInFiles() : storeInContent()`
- No confusion about where to store what

✅ **Performance Optimization**
- Don't waste CPU scoring PDFs/images
- intelligence_content_text only needs text files
- Smaller tables → faster queries

### Negative

❌ **Migration Required**
- 55,357 files currently in wrong table
- Need to move 54,600+ text files to intelligence_content
- Need to re-score after migration

❌ **Scanner Rewrite**
- Need to update api_neural_scanner.php
- Add binary detection logic
- Route to correct table based on file nature

❌ **Existing Code Breaks**
- intelligent_scorer.php currently scores intelligence_files
- Need to update to score intelligence_content

---

## Implementation Plan

### Phase 1: Create Migration Script (1 hour)

```php
// migrate_intelligence_tables.php
foreach (intelligence_files as $file) {
    if (isBinaryFile($file)) {
        // Keep in intelligence_files
        continue;
    } else {
        // Move to intelligence_content
        INSERT INTO intelligence_content (...);
        INSERT INTO intelligence_content_text (full text);
        DELETE FROM intelligence_files WHERE file_id = $file->id;
    }
}
```

### Phase 2: Update Scanner (30 min)

```php
// api_neural_scanner.php
private function processFile($file) {
    if ($this->isBinaryFile($file)) {
        $this->storeInIntelligenceFiles($file);
    } else {
        $this->storeInIntelligenceContent($file);
        $this->storeFullText($file);
        $this->extractPatterns($file);
    }
}
```

### Phase 3: Update Scorer (15 min)

```php
// intelligent_scorer.php
// Change FROM clause
$files = $db->query("SELECT * FROM intelligence_content WHERE intelligence_score = 0");
```

### Phase 4: Run Migration (5 min)

```bash
php migrate_intelligence_tables.php --dry-run  # Verify
php migrate_intelligence_tables.php --execute  # Do it
```

### Phase 5: Verify (5 min)

```sql
-- Check binary files
SELECT COUNT(*), GROUP_CONCAT(DISTINCT mime_type) 
FROM intelligence_files;
-- Expected: ~750 files, MIME types like 'image/%', 'application/pdf'

-- Check text content
SELECT COUNT(*), GROUP_CONCAT(DISTINCT language_detected) 
FROM intelligence_content;
-- Expected: ~54,600 files, languages like 'PHP', 'JavaScript', 'Markdown'
```

---

## Alternatives Considered

### Alternative 1: Keep Everything in intelligence_files

**Rejected because:**
- Violates semantic separation (mixing binary and text)
- Makes intelligence analysis harder (need to filter out binary constantly)
- Wastes resources (trying to score PDFs/images)

### Alternative 2: Split by Category (documentation/code/business)

**Rejected because:**
- Doesn't solve the binary vs text problem
- Still need to filter out images in each category
- More complex query logic

### Alternative 3: Single Table with Type Flag

**Rejected because:**
- Large BLOB columns slow down text queries
- Index bloat from mixed content types
- Can't optimize table storage (InnoDB compressed text vs binary)

---

## Related Decisions

- ADR 002: Scoring Methodology (depends on this separation)
- ADR 003: Ignore Configuration (affects both tables)

---

## Validation

**Success Criteria:**
1. intelligence_files < 1,000 rows (binary only)
2. intelligence_content > 50,000 rows (text only)
3. No text files (php, js, md) in intelligence_files
4. No binary files (pdf, png, zip) in intelligence_content
5. All intelligence_content rows have scores
6. All intelligence_content rows have full text in intelligence_content_text

**Validation Query:**
```sql
-- Verify no text files in intelligence_files
SELECT COUNT(*) FROM intelligence_files 
WHERE mime_type LIKE 'text/%' OR mime_type = 'application/json';
-- Expected: 0

-- Verify no binary in intelligence_content
SELECT COUNT(*) FROM intelligence_content
WHERE mime_type LIKE 'image/%' OR mime_type LIKE 'application/pdf';
-- Expected: 0
```

---

## Notes

User's question **"ARE YOU SURE INTELLIGENCE FILES IS NOT THINGS LIKE PDF AND IMAGES AND INTELLIGENCE CONTENT IS HTML AND PHP"** was the key insight that revealed this design intent.

The table names actually make sense when understood correctly:
- **intelligence_files** = Generic file storage (PDFs, images) - LOW intelligence potential
- **intelligence_content** = Parseable content (code, docs) - HIGH intelligence potential

---

**Status:** Accepted  
**Implementation:** Pending migration script creation  
**Risk:** Medium (data migration always risky, but can be rolled back)
