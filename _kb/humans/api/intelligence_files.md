# intelligence_files Table Schema

**Purpose:** Binary file storage (PDFs, images, archives, etc.)  
**Current State:** WRONG - Contains 55,357 text files that should be in intelligence_content  
**Target State:** ~750 binary files only

---

## Table Structure

```sql
CREATE TABLE `intelligence_files` (
  `file_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `business_unit_id` varchar(50) NOT NULL,
  `server_id` varchar(50) NOT NULL,
  `file_path` varchar(1000) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,  -- 'code_intelligence', 'documentation', 'business_intelligence'
  `file_size` bigint(20) NOT NULL DEFAULT 0,
  `file_content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `intelligence_type` varchar(100) DEFAULT NULL,
  `intelligence_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`intelligence_data`)),
  `content_summary` text DEFAULT NULL,
  
  -- Intelligence scores (ADDED during conversation)
  `intelligence_score` decimal(5,2) DEFAULT 0.00,
  `complexity_score` decimal(5,2) DEFAULT 0.00,
  `quality_score` decimal(5,2) DEFAULT 0.00,
  `business_value_score` decimal(5,2) DEFAULT 0.00,
  `last_analyzed` timestamp NULL DEFAULT NULL,
  
  `extracted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  
  PRIMARY KEY (`file_id`),
  KEY `idx_business_unit` (`business_unit_id`),
  KEY `idx_server` (`server_id`),
  KEY `idx_file_type` (`file_type`),
  KEY `idx_intelligence_type` (`intelligence_type`),
  KEY `idx_extracted_at` (`extracted_at`),
  KEY `idx_scores` (`intelligence_score`,`quality_score`)  -- Added for scoring queries
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## What SHOULD Be Stored Here

### Binary File Types

| Extension | MIME Type | Purpose | Example |
|-----------|-----------|---------|---------|
| `.pdf` | application/pdf | Reports, invoices, manuals | invoice_1234.pdf |
| `.png` | image/png | Screenshots, logos | logo.png |
| `.jpg` | image/jpeg | Photos, product images | product_123.jpg |
| `.gif` | image/gif | Animated images | loading.gif |
| `.svg` | image/svg+xml | Vector graphics | icon.svg |
| `.zip` | application/zip | Archives | backup_2025.zip |
| `.tar.gz` | application/gzip | Compressed archives | release_v1.tar.gz |
| `.mp4` | video/mp4 | Videos | tutorial.mp4 |
| `.mp3` | audio/mpeg | Audio files | notification.mp3 |
| `.docx` | application/vnd... | Word documents | report.docx |
| `.xlsx` | application/vnd... | Excel spreadsheets | sales_2025.xlsx |
| `.ttf` | font/ttf | Fonts | roboto.ttf |
| `.woff` | font/woff | Web fonts | opensans.woff |

### Example Row (Correct Usage)

```json
{
  "file_id": 1,
  "business_unit_id": "jcepnzzkmj",
  "server_id": "cis_staff",
  "file_path": "/assets/images/logo.png",
  "file_name": "logo.png",
  "file_type": "binary_image",
  "file_size": 45678,
  "file_content": "<binary PNG data>",
  "metadata": {
    "mime_type": "image/png",
    "dimensions": "200x100",
    "color_depth": 24
  },
  "intelligence_type": "visual_asset",
  "intelligence_data": {
    "used_in": ["header", "emails"],
    "versions": 3
  },
  "content_summary": "Company logo - 200x100px PNG",
  "intelligence_score": 0.00,
  "complexity_score": 0.00,
  "quality_score": 0.00,
  "business_value_score": 10.00,
  "extracted_at": "2025-10-25 10:00:00"
}
```

---

## What's CURRENTLY Wrong

### Current Data (INCORRECT)

```sql
mysql> SELECT file_type, COUNT(*) FROM intelligence_files GROUP BY file_type;
+-----------------------+-------+
| file_type             | count |
+-----------------------+-------+
| code_intelligence     | 36315 | ← PHP, JS, PY files (WRONG TABLE!)
| documentation         | 13289 | ← MD, TXT files (WRONG TABLE!)
| business_intelligence |  5753 | ← JSON, YAML files (WRONG TABLE!)
+-----------------------+-------+
Total: 55,357 TEXT files in BINARY table
```

### Example Wrong Data

```json
{
  "file_id": 12345,
  "file_name": "pack.php",  // ← PHP file (should be in intelligence_content!)
  "file_type": "code_intelligence",
  "file_content": "<?php\n/**\n * Pack transfer...",  // ← Text content in BLOB field
  "intelligence_score": 85.50,  // ← Scoring binary table (wrong)
}
```

**Problems:**
1. ❌ Text files stored in binary table
2. ❌ LONGTEXT used for file_content (should be LONGBLOB for binary)
3. ❌ Intelligence scores on wrong table
4. ❌ file_type values don't match content (code/docs/business vs actual binary types)

---

## Migration Required

### Step 1: Identify Text Files

```sql
SELECT file_id, file_name, file_type 
FROM intelligence_files
WHERE file_type IN ('code_intelligence', 'documentation', 'business_intelligence')
  AND file_name REGEXP '\.(php|js|py|md|txt|json|yaml|html|css)$';
-- Expected: ~54,600 files
```

### Step 2: Move to intelligence_content

```php
foreach ($text_files as $file) {
    // Insert into intelligence_content
    INSERT INTO intelligence_content (
        org_id, content_path, content_name, content_hash, 
        content_type_id, file_size, file_content,
        intelligence_score, complexity_score, quality_score, business_value_score
    ) VALUES (
        $org_id, $file->path, $file->name, sha256($file->content),
        $content_type_id, $file->size, $file->content,
        $file->intelligence_score, $file->complexity_score, 
        $file->quality_score, $file->business_value_score
    );
    
    // Insert full text into intelligence_content_text
    INSERT INTO intelligence_content_text (content_id, full_text)
    VALUES (LAST_INSERT_ID(), $file->content);
    
    // Delete from intelligence_files
    DELETE FROM intelligence_files WHERE file_id = $file->id;
}
```

### Step 3: Keep Only Binary Files

```sql
-- After migration, should have:
SELECT file_id, file_name, 
       SUBSTRING_INDEX(file_name, '.', -1) as extension,
       file_size
FROM intelligence_files
WHERE file_name REGEXP '\.(pdf|png|jpg|gif|svg|zip|tar|mp4|docx|xlsx)$';
-- Expected: ~750 files
```

---

## Future Schema Changes

### Make file_content LONGBLOB

```sql
ALTER TABLE intelligence_files 
MODIFY file_content LONGBLOB;
-- After migration, since only storing binary data
```

### Add mime_type Column

```sql
ALTER TABLE intelligence_files 
ADD COLUMN mime_type VARCHAR(100) AFTER file_name;
-- Store proper MIME types like 'image/png', 'application/pdf'
```

### Remove Intelligence Scores

```sql
ALTER TABLE intelligence_files
DROP COLUMN intelligence_score,
DROP COLUMN complexity_score,
DROP COLUMN quality_score,
DROP COLUMN business_value_score,
DROP COLUMN last_analyzed;
-- Binary files don't need code intelligence scores
```

---

## Queries After Migration

### List All Images

```sql
SELECT file_name, file_size, metadata
FROM intelligence_files
WHERE mime_type LIKE 'image/%'
ORDER BY file_size DESC;
```

### List All PDFs

```sql
SELECT file_name, file_path, file_size, content_summary
FROM intelligence_files
WHERE mime_type = 'application/pdf'
ORDER BY extracted_at DESC;
```

### Find Large Files

```sql
SELECT file_name, file_size / 1024 / 1024 as size_mb
FROM intelligence_files
WHERE file_size > 1048576  -- > 1MB
ORDER BY file_size DESC
LIMIT 20;
```

---

## Related Documentation

- `intelligence_content.md` - Where text files SHOULD be stored
- `../decisions/001_intelligence_tables_separation.md` - Why we separate
- `../migrations/migration_plan.md` - How to migrate
