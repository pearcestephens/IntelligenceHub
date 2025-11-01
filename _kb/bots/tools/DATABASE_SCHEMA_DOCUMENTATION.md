# Intelligence Database Schema Documentation

**Database:** `hdgwrzntwa`  
**Last Updated:** October 24, 2025  
**Total Tables:** 35  
**Organization:** Ecigdis Limited (org_id: 1)

---

## 📊 Database Overview

### Current Data Statistics

- **Total Intelligence Files:** 29,808 (active)
- **Total Storage:** ~360 MB
- **Primary Server Coverage:** jcepnzzkmj (28,856 files, 346.9 MB)
- **Secondary Servers:** dvaxgvsxmz (652 files), fhrehrpjmu (300 files)

### Intelligence Breakdown by Type

| Intelligence Type | Count | Category |
|------------------|-------|----------|
| code_php | 14,441 | Code Intelligence |
| documentation_txt | 6,250 | Documentation |
| code_js | 2,873 | Code Intelligence |
| business_data_json | 2,626 | Business Intelligence |
| documentation_md | 2,060 | Documentation |
| code_intelligence | 1,519 | Code Intelligence |
| business_data_xml | 25 | Business Intelligence |
| business_data_yaml | 10 | Business Intelligence |
| business_data_yml | 4 | Business Intelligence |

---

## 🏢 Business Units Hierarchy

### Active Business Units (12 units)

| Unit ID | Name | Type | Server | Domain | Intelligence Level |
|---------|------|------|--------|--------|-------------------|
| 1 | Corporate Intelligence Hub | corporate | hdgwrzntwa | gpt.ecigdis.co.nz | advanced |
| 2 | CIS Technical Intelligence | technical | jcepnzzkmj | staff.vapeshed.co.nz | advanced |
| 3 | Retail Intelligence Network | retail | dvaxgvsxmz | vapeshed.co.nz | advanced |
| 4 | Wholesale Intelligence Portal | wholesale | fhrehrpjmu | ecigdis.co.nz | advanced |
| 5 | Juice Manufacturing Intelligence | manufacturing | - | - | advanced |
| 6 | Logistics & Supply Chain Intelligence | logistics | jcepnzzkmj | staff.vapeshed.co.nz | neural |
| 7 | Importing & Customs Intelligence | importing | hdgwrzntwa | gpt.ecigdis.co.nz | neural |
| 8 | Sales Analytics & Forecasting Intelligence | sales_analytics | dvaxgvsxmz | vapeshed.co.nz | quantum |
| 9 | Inventory & Warehouse Intelligence | inventory | jcepnzzkmj | staff.vapeshed.co.nz | neural |
| 10 | Financial Operations Intelligence | financial_ops | hdgwrzntwa | gpt.ecigdis.co.nz | quantum |
| 11 | Customer Service Intelligence | customer_service | dvaxgvsxmz | vapeshed.co.nz | advanced |
| 12 | Quality Assurance Intelligence | quality_assurance | - | - | neural |

### Server Mapping

**Primary Intelligence Hub:** `hdgwrzntwa` (gpt.ecigdis.co.nz)
- Corporate Intelligence Hub (Unit 1)
- Importing & Customs Intelligence (Unit 7)
- Financial Operations Intelligence (Unit 10)

**CIS Technical Server:** `jcepnzzkmj` (staff.vapeshed.co.nz)
- CIS Technical Intelligence (Unit 2)
- Logistics & Supply Chain Intelligence (Unit 6)
- Inventory & Warehouse Intelligence (Unit 9)

**Retail Server:** `dvaxgvsxmz` (vapeshed.co.nz)
- Retail Intelligence Network (Unit 3)
- Sales Analytics & Forecasting Intelligence (Unit 8)
- Customer Service Intelligence (Unit 11)

**Wholesale Server:** `fhrehrpjmu` (ecigdis.co.nz)
- Wholesale Intelligence Portal (Unit 4)

---

## 📋 Core Tables Schema

### 1. `intelligence_files` (PRIMARY INTELLIGENCE STORAGE)

**Purpose:** Main storage for all extracted intelligence files  
**Records:** 29,808  
**Engine:** InnoDB with AUTO_INCREMENT

#### Schema

```sql
CREATE TABLE `intelligence_files` (
  `file_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `business_unit_id` int(11) NOT NULL,
  `server_id` varchar(50) NOT NULL,
  `file_path` text NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` enum('documentation','code_intelligence','business_intelligence','operational_intelligence'),
  `file_size` bigint(20) NOT NULL,
  `file_content` longtext DEFAULT NULL,
  `metadata` text DEFAULT NULL,
  `intelligence_type` varchar(100) NOT NULL,
  `intelligence_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`intelligence_data`)),
  `content_summary` text DEFAULT NULL,
  `extracted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`file_id`),
  KEY `idx_business_unit` (`business_unit_id`),
  KEY `idx_server` (`server_id`),
  KEY `idx_file_type` (`file_type`),
  KEY `idx_intelligence_type` (`intelligence_type`),
  KEY `idx_extracted_at` (`extracted_at`),
  KEY `idx_active` (`is_active`),
  FULLTEXT KEY `ft_file_name` (`file_name`),
  FULLTEXT KEY `ft_content_summary` (`content_summary`)
) ENGINE=InnoDB;
```

#### Key Features

- **JSON Storage:** `intelligence_data` field stores structured analysis results
- **Full-Text Search:** Indexed on `file_name` and `content_summary`
- **Composite Indexes:** Optimized for server+intelligence type queries
- **Temporal Tracking:** `extracted_at` and `updated_at` timestamps
- **Soft Deletes:** `is_active` flag for logical deletion

#### File Type Distribution

- **code_intelligence:** 18,833 files (PHP, JS, general code)
- **documentation:** 8,310 files (MD, TXT)
- **business_intelligence:** 2,665 files (JSON, XML, YAML)

---

### 2. `intelligence_content` (MULTI-ORG CONTENT TRACKING)

**Purpose:** Advanced content tracking with quality scoring and Redis integration  
**Records:** 0 (currently empty - new implementation)  
**Engine:** InnoDB with Foreign Keys

#### Schema

```sql
CREATE TABLE `intelligence_content` (
  `content_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `org_id` int(11) NOT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `content_type_id` int(11) NOT NULL,
  `source_system` varchar(50) NOT NULL COMMENT 'Source system identifier',
  `content_path` varchar(1000) NOT NULL COMMENT 'Full path to content',
  `content_name` varchar(255) NOT NULL COMMENT 'Content filename',
  `content_hash` varchar(64) NOT NULL COMMENT 'SHA-256 hash for deduplication',
  `file_size` bigint(20) NOT NULL DEFAULT 0,
  `mime_type` varchar(100) DEFAULT NULL,
  `language_detected` varchar(20) DEFAULT NULL,
  `encoding` varchar(20) DEFAULT 'UTF-8',
  `intelligence_score` decimal(5,2) DEFAULT 0.00 COMMENT 'AI-calculated intelligence value',
  `complexity_score` decimal(5,2) DEFAULT 0.00 COMMENT 'Content complexity rating',
  `quality_score` decimal(5,2) DEFAULT 0.00 COMMENT 'Content quality assessment',
  `business_value_score` decimal(5,2) DEFAULT 0.00 COMMENT 'Business importance rating',
  `redis_cached` tinyint(1) DEFAULT 0 COMMENT 'Is content cached in Redis',
  `redis_cache_key` varchar(255) DEFAULT NULL COMMENT 'Redis cache key if cached',
  `last_analyzed` timestamp NULL DEFAULT NULL COMMENT 'Last AI analysis timestamp',
  `last_accessed` timestamp NULL DEFAULT NULL COMMENT 'Last access for cache optimization',
  `access_frequency` int(11) DEFAULT 0 COMMENT 'Access frequency counter',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`content_id`),
  UNIQUE KEY `content_hash` (`content_hash`),
  UNIQUE KEY `unique_org_content` (`org_id`,`content_path`) USING HASH,
  KEY `idx_intelligence_scores` (`intelligence_score`,`quality_score`,`business_value_score`),
  KEY `idx_last_analyzed` (`last_analyzed`),
  KEY `idx_access_patterns` (`access_frequency`,`last_accessed`),
  KEY `idx_redis_cached` (`redis_cached`),
  CONSTRAINT FOREIGN KEY (`org_id`) REFERENCES `organizations` (`org_id`) ON DELETE CASCADE,
  CONSTRAINT FOREIGN KEY (`unit_id`) REFERENCES `business_units` (`unit_id`) ON DELETE SET NULL
) ENGINE=InnoDB;
```

#### Key Features

- **Quality Scoring System:** Four-dimensional scoring (intelligence, complexity, quality, business value)
- **Redis Integration:** Built-in cache tracking and key management
- **Deduplication:** SHA-256 hash-based duplicate detection
- **Access Analytics:** Tracks frequency and last access for optimization
- **Multi-tenancy:** Supports multiple organizations via foreign keys

#### Scoring Dimensions

1. **Intelligence Score:** AI-calculated value of the content
2. **Complexity Score:** Technical complexity rating
3. **Quality Score:** Code/documentation quality assessment
4. **Business Value Score:** Strategic importance to business

---

### 3. `neural_patterns` (AI PATTERN DETECTION)

**Purpose:** Stores detected patterns from AI analysis  
**Records:** 0 (currently empty - new implementation)  
**Engine:** InnoDB

#### Schema

```sql
CREATE TABLE `neural_patterns` (
  `pattern_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `org_id` int(11) NOT NULL,
  `pattern_type` enum('code_structure','business_process','user_behavior','system_performance','quality_issue','security_risk','optimization_opportunity'),
  `pattern_category` enum('design_pattern','anti_pattern','best_practice','anomaly','trend','correlation'),
  `pattern_signature` varchar(255) NOT NULL COMMENT 'Unique pattern identifier',
  `pattern_name` varchar(100) NOT NULL,
  `pattern_description` text DEFAULT NULL,
  `pattern_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`pattern_data`)),
  `detection_algorithm` varchar(50) NOT NULL COMMENT 'Algorithm used to detect pattern',
  `confidence_score` decimal(5,2) NOT NULL COMMENT 'Pattern detection confidence',
  `frequency_score` int(11) DEFAULT 1 COMMENT 'How often pattern occurs',
  `importance_score` decimal(5,2) DEFAULT 0.00 COMMENT 'Business importance rating',
  `impact_score` decimal(5,2) DEFAULT 0.00 COMMENT 'Potential impact rating',
  `recommendation` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`recommendation`)),
  `redis_pattern_key` varchar(255) DEFAULT NULL COMMENT 'Redis cache key for real-time access',
  `first_detected` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_seen` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `times_detected` int(11) DEFAULT 1,
  PRIMARY KEY (`pattern_id`),
  UNIQUE KEY `unique_pattern` (`org_id`,`pattern_type`,`pattern_signature`),
  KEY `idx_pattern_scores` (`confidence_score`,`importance_score`,`impact_score`),
  KEY `idx_pattern_category` (`pattern_category`),
  KEY `idx_detection_frequency` (`frequency_score`,`times_detected`),
  CONSTRAINT FOREIGN KEY (`org_id`) REFERENCES `organizations` (`org_id`) ON DELETE CASCADE
) ENGINE=InnoDB;
```

#### Pattern Types

- **code_structure:** Design patterns, anti-patterns in code
- **business_process:** Process workflows and inefficiencies
- **user_behavior:** User interaction patterns
- **system_performance:** Performance bottlenecks
- **quality_issue:** Code quality problems
- **security_risk:** Security vulnerabilities
- **optimization_opportunity:** Improvement possibilities

#### Pattern Categories

- **design_pattern:** Known good patterns
- **anti_pattern:** Known bad patterns
- **best_practice:** Industry best practices
- **anomaly:** Unusual behaviors
- **trend:** Emerging patterns over time
- **correlation:** Related patterns across systems

---

### 4. `business_units` (ORGANIZATIONAL STRUCTURE)

**Purpose:** Defines business units and their intelligence configuration  
**Records:** 12 active units  
**Engine:** InnoDB

#### Schema

```sql
CREATE TABLE `business_units` (
  `unit_id` int(11) NOT NULL AUTO_INCREMENT,
  `org_id` int(11) NOT NULL,
  `unit_name` varchar(100) NOT NULL,
  `unit_type` enum('manufacturing','retail','wholesale','ecommerce','corporate','technical','logistics','importing','sales_analytics','inventory','financial_ops','customer_service','quality_assurance'),
  `server_mapping` varchar(50) DEFAULT NULL COMMENT 'Maps to server applications',
  `domain_mapping` varchar(100) DEFAULT NULL COMMENT 'Maps to domain endpoints',
  `redis_channel` varchar(50) NOT NULL COMMENT 'Redis pub/sub channel',
  `intelligence_level` enum('basic','advanced','neural','quantum') DEFAULT 'advanced',
  `scan_paths` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`scan_paths`)),
  `ignore_patterns` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`ignore_patterns`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`unit_id`),
  UNIQUE KEY `unique_org_unit` (`org_id`,`unit_name`),
  CONSTRAINT FOREIGN KEY (`org_id`) REFERENCES `organizations` (`org_id`) ON DELETE CASCADE
) ENGINE=InnoDB;
```

#### Intelligence Levels

- **basic:** Simple file indexing and search
- **advanced:** Full code analysis, relationship mapping (6 units)
- **neural:** AI pattern detection, predictive analytics (4 units)
- **quantum:** Advanced forecasting, multi-dimensional analysis (2 units)

---

### 5. Supporting Tables

#### `organizations`
- **Purpose:** Top-level organization entity
- **Current:** 1 organization (Ecigdis Limited, org_id: 1)

#### `kb_files` (VIEW)
- **Purpose:** View over `ecig_kb_files` table
- **Provides:** Simplified access to KB file metadata

#### `kb_content` (VIEW)
- **Purpose:** View over `ecig_kb_documentation` table
- **Provides:** Documentation content access

#### Additional Support Tables
- `intelligence_alerts` - Alert configuration and history
- `intelligence_automation` - Automation job definitions
- `intelligence_automation_executions` - Automation execution logs
- `intelligence_metrics` - Performance and quality metrics
- `intelligence_content_types` - Content type definitions
- `kb_search_index` - Search indexing for fast queries
- `kb_statistics` - Statistical summaries
- `kb_quality` - Quality tracking
- `activity_logs` - System activity tracking
- `dashboard_*` tables - Dashboard configuration
- `system_*` tables - System configuration and health

---

## 🔍 Key Indexes and Performance

### Most Used Indexes

1. **intelligence_files**
   - `idx_server_intelligence` - Server + intelligence type lookups
   - `idx_type_extracted` - Type + date range queries
   - `ft_file_name` - Full-text search on filenames
   - `ft_content_summary` - Full-text search on content

2. **intelligence_content**
   - `idx_intelligence_scores` - Multi-score sorting
   - `idx_access_patterns` - Cache optimization queries
   - `unique_org_content` - Prevents duplicate imports

3. **neural_patterns**
   - `idx_pattern_scores` - Pattern ranking
   - `unique_pattern` - Prevents duplicate pattern detection

### Performance Optimization Features

- **Composite Indexes:** Reduce query execution time
- **Full-Text Search:** Fast content discovery
- **Hash Indexes:** Unique constraint enforcement
- **JSON Validation:** Data integrity at DB level
- **Temporal Indexes:** Time-series query optimization

---

## 📈 Growth Trends

### Current State
- **Daily Intake:** ~500-1000 new files
- **Storage Growth:** ~10-20 MB/day
- **Server Distribution:** 96% on jcepnzzkmj, 4% on other servers

### Projections
- **6 Month Storage:** ~400 MB (manageable)
- **1 Year Storage:** ~750 MB (no issues)
- **Scale Limit:** Current schema supports billions of records

---

## 🔐 Security & Access

### Database Credentials
- **Host:** localhost
- **Database:** hdgwrzntwa
- **User:** hdgwrzntwa
- **Access:** Local only (no remote connections)

### Data Protection
- **Foreign Keys:** Cascade deletes maintain referential integrity
- **JSON Validation:** Built-in schema validation
- **Soft Deletes:** `is_active` flags preserve history
- **Temporal Tracking:** Full audit trail via timestamps

---

## 🛠️ Maintenance

### Regular Tasks
- **Daily:** New file ingestion via KB scripts
- **Weekly:** Index optimization (if needed)
- **Monthly:** Statistics recalculation
- **Quarterly:** Schema review and optimization

### Monitoring Points
- Table growth rates
- Index usage statistics
- Query performance metrics
- Storage utilization

---

## 📊 Query Examples

### Get Intelligence Summary by Server
```sql
SELECT 
    server_id,
    intelligence_type,
    COUNT(*) as file_count,
    ROUND(SUM(file_size)/1048576, 2) as total_mb
FROM intelligence_files
WHERE is_active = 1
GROUP BY server_id, intelligence_type
ORDER BY server_id, file_count DESC;
```

### Find Recent Extractions
```sql
SELECT 
    server_id,
    file_name,
    intelligence_type,
    DATE(extracted_at) as extracted_date
FROM intelligence_files
WHERE is_active = 1
  AND extracted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
ORDER BY extracted_at DESC
LIMIT 50;
```

### Business Unit Coverage
```sql
SELECT 
    bu.unit_name,
    bu.server_mapping,
    COUNT(if.file_id) as file_count,
    ROUND(SUM(if.file_size)/1048576, 2) as total_mb
FROM business_units bu
LEFT JOIN intelligence_files if ON bu.unit_id = if.business_unit_id AND if.is_active = 1
WHERE bu.is_active = 1
GROUP BY bu.unit_id
ORDER BY file_count DESC;
```

---

## 🎯 Future Enhancements

### Planned Improvements
1. **Populate `intelligence_content`** - Migrate to new scoring system
2. **Activate `neural_patterns`** - Enable AI pattern detection
3. **Redis Integration** - Implement caching layer
4. **Cross-server Queries** - Unified intelligence views
5. **Real-time Sync** - Pub/sub pattern implementation

### Schema Evolution
- Additional pattern types as AI capabilities grow
- Enhanced scoring algorithms
- New relationship tracking tables
- Performance optimization based on usage patterns

---

**Last Schema Update:** October 24, 2025  
**Documentation Maintained By:** Intelligence Server Manager  
**Review Schedule:** Monthly
