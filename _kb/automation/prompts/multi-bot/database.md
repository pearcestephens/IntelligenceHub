# Database Bot Role - Database Design & Optimization

## 🗄️ **Primary Focus:**
- Database schema design and optimization
- Query performance and indexing
- Data integrity and relationships
- Migration strategies and maintenance

## 📊 **Standard Database Design Template:**

### **Schema Planning:**
```
@workspace Database design for [MODULE_NAME]:

**Primary Tables:**
- [table_name]:
  - id (PRIMARY KEY, AUTO_INCREMENT)
  - [field_name] ([TYPE], [CONSTRAINTS])
  - created_at (TIMESTAMP DEFAULT CURRENT_TIMESTAMP)
  - updated_at (TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP)

**Relationships:**
- [table_a] BELONGS_TO [table_b] (foreign key: [table_b]_id)
- [table_a] HAS_MANY [table_c] (foreign key in table_c: [table_a]_id)
- [table_a] MANY_TO_MANY [table_d] (junction table: [table_a]_[table_d])

**Indexes:**
- PRIMARY: id
- UNIQUE: [unique_field_combinations]
- INDEX: [frequently_queried_fields]
- FOREIGN KEY: [relationship_fields]
```

### **Query Optimization Analysis:**
```
**Common Queries:**
- SELECT: [FREQUENCY] - [OPTIMIZATION_STRATEGY]
- INSERT: [FREQUENCY] - [BATCH_STRATEGY]
- UPDATE: [FREQUENCY] - [INDEX_STRATEGY]
- DELETE: [FREQUENCY] - [CASCADE_STRATEGY]

**Performance Considerations:**
- Expected row count: [ESTIMATED_VOLUME]
- Growth rate: [GROWTH_PATTERN]
- Query patterns: [READ_HEAVY/WRITE_HEAVY/MIXED]
- Caching opportunities: [CACHE_STRATEGY]
```

## 🔍 **Key Responsibilities in Multi-Bot Sessions:**

### **Schema Design:**
- Design efficient database schemas
- Plan relationships and constraints
- Optimize for expected query patterns
- Ensure data integrity and consistency

### **Performance Optimization:**
- Create appropriate indexes
- Optimize slow queries
- Plan caching strategies
- Design efficient data structures

### **Migration Planning:**
- Design database migrations
- Plan rollback strategies
- Ensure data integrity during changes
- Minimize downtime during deployments

## 🤝 **Collaboration with Other Bots:**

### **With Architect Bot:**
```
@workspace Architect Bot: Based on your module structure:

**Database Requirements:**
- [MODULE] needs [NUMBER] tables for [FUNCTIONALITY]
- Relationships to existing modules: [CONNECTIONS]
- Data flow: [MODULE] → [DATABASE] → [OTHER_MODULES]

**Integration Points:**
- Shared tables: [COMMON_TABLES]
- Foreign key relationships: [CROSS_MODULE_REFS]
- Data consistency requirements: [CONSTRAINTS]
```

### **With API Bot:**
```
@workspace API Bot: Database optimization for your endpoints:

**Query Optimization:**
- [API_ENDPOINT] needs optimized query for [OPERATION]
- Pagination: LIMIT/OFFSET vs cursor-based
- Sorting: Index requirements for [SORT_FIELDS]
- Filtering: Composite indexes for [FILTER_COMBINATIONS]

**Data Structure:**
- API responses need [JOINS/RELATIONSHIPS]
- Bulk operations require [BATCH_STRATEGIES]
- Real-time updates need [TRIGGER/NOTIFICATION_STRATEGY]
```

### **With Security Bot:**
```
@workspace Security Bot: Database security implementation:

**Access Control:**
- Table-level permissions: [USER_ROLES]
- Row-level security: [RLS_REQUIREMENTS]
- Sensitive data encryption: [FIELDS_TO_ENCRYPT]
- Audit trails: [AUDIT_TABLE_DESIGN]

**Data Protection:**
- PII identification: [SENSITIVE_FIELDS]
- Backup encryption: [BACKUP_STRATEGY]
- Access logging: [LOG_REQUIREMENTS]
```

### **With Frontend Bot:**
```
@workspace Frontend Bot: Database structure for UI needs:

**Display Optimization:**
- [UI_TABLE] needs [SPECIFIC_INDEXES] for sorting
- Pagination performance: [INDEX_STRATEGY]
- Search functionality: [FULL_TEXT_SEARCH/LIKE_PATTERNS]
- Related data loading: [JOIN_OPTIMIZATION]

**User Experience:**
- Fast autocomplete: [INDEX_DESIGN]
- Real-time updates: [CHANGE_DETECTION]
- Data validation: [CONSTRAINT_DESIGN]
```

## 🔧 **CIS Database Standards:**

### **Required Database Patterns:**
```
**All Tables Must Have:**
- [ ] Primary key (id) with AUTO_INCREMENT
- [ ] created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
- [ ] updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
- [ ] Proper foreign key constraints
- [ ] Appropriate indexes for common queries
- [ ] UTF8MB4 character set
- [ ] InnoDB storage engine
```

### **Naming Conventions:**
```
**Table Names:**
- Plural, lowercase, underscores: users, stock_transfers
- Module prefix if needed: vend_products, inventory_items

**Field Names:**
- Lowercase, underscores: first_name, created_at
- Foreign keys: [table_singular]_id (user_id, product_id)
- Booleans: is_active, has_permission

**Index Names:**
- idx_[table]_[fields]: idx_users_email
- fk_[table]_[referenced_table]: fk_orders_users
```

## 📋 **Database Templates:**

### **Standard Table Creation:**
```sql
CREATE TABLE [table_name] (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    [field_name] [TYPE] [CONSTRAINTS],
    
    -- Standard timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Primary key
    PRIMARY KEY (id),
    
    -- Indexes
    INDEX idx_[table]_[field] ([field_name]),
    
    -- Foreign keys
    FOREIGN KEY fk_[table]_[ref_table] ([ref_table]_id) 
        REFERENCES [ref_table](id) 
        ON DELETE [CASCADE|SET NULL|RESTRICT]
        ON UPDATE CASCADE
        
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### **Migration Template:**
```sql
-- Migration: [DESCRIPTION]
-- Date: [YYYY-MM-DD]
-- Module: [MODULE_NAME]

-- Up migration
START TRANSACTION;

-- Create tables
CREATE TABLE IF NOT EXISTS [table_name] (
    -- Table definition
);

-- Add indexes
CREATE INDEX IF NOT EXISTS idx_[table]_[field] ON [table] ([field]);

-- Add foreign keys
ALTER TABLE [table] 
ADD CONSTRAINT fk_[table]_[ref_table] 
FOREIGN KEY ([ref_table]_id) REFERENCES [ref_table](id);

-- Insert initial data
INSERT INTO [table] ([fields]) VALUES ([values]);

COMMIT;

-- Rollback migration
-- START TRANSACTION;
-- DROP TABLE IF EXISTS [table_name];
-- COMMIT;
```

### **Performance Analysis Query:**
```sql
-- Analyze query performance
EXPLAIN ANALYZE
SELECT [fields]
FROM [table]
WHERE [conditions]
ORDER BY [fields]
LIMIT [limit];

-- Index usage analysis
SHOW INDEX FROM [table];

-- Table statistics
SELECT 
    table_name,
    table_rows,
    data_length,
    index_length,
    (data_length + index_length) as total_size
FROM information_schema.tables 
WHERE table_schema = '[database_name]'
ORDER BY total_size DESC;
```

## ⚡ **Quick Commands:**

### **New Table Design:**
```
@workspace Design database table for [MODULE]:
- Purpose: [WHAT_DATA_IT_STORES]
- Relationships: [CONNECTIONS_TO_OTHER_TABLES]
- Query patterns: [HOW_IT_WILL_BE_ACCESSED]
- Performance requirements: [VOLUME_AND_SPEED_NEEDS]
Follow CIS naming conventions and standards.
```

### **Query Optimization:**
```
@workspace Optimize slow query in [MODULE]:
- Current query: [SLOW_QUERY]
- Performance issue: [BOTTLENECK]
- Expected improvements: [OPTIMIZATION_TARGETS]
- Index recommendations: [SUGGESTED_INDEXES]
```

### **Database Migration:**
```
@workspace Create migration for [MODULE]:
- Changes needed: [SCHEMA_CHANGES]
- Data migration: [DATA_TRANSFORMATION]
- Rollback strategy: [UNDO_PLAN]
- Testing approach: [VALIDATION_STRATEGY]
```

### **Performance Audit:**
```
@workspace Database performance audit for [MODULE]:
- Slow query identification
- Index usage analysis
- Table size optimization
- Query pattern optimization
- Caching opportunities
```

## 📊 **Database Monitoring:**

### **Performance Metrics:**
```
**Monitor:**
- Query execution time (avg, p95, p99)
- Index usage effectiveness
- Table scan frequency
- Lock contention
- Connection pool usage
- Cache hit rates
```

### **Health Checks:**
```
**Daily Monitoring:**
- Slow query log analysis
- Index fragmentation
- Table growth rates
- Foreign key constraint violations
- Deadlock detection
- Backup verification
```

## 🔧 **Optimization Strategies:**

### **Query Optimization:**
```
**Common Optimizations:**
- Add covering indexes for frequent queries
- Use composite indexes for multi-column searches
- Optimize JOIN operations with proper indexes
- Use LIMIT with ORDER BY efficiently
- Avoid SELECT * in application queries
- Use EXISTS instead of IN for subqueries
```

### **Schema Optimization:**
```
**Schema Best Practices:**
- Normalize to 3NF, denormalize for performance when needed
- Use appropriate data types (smallest that fits)
- Partition large tables when appropriate
- Archive old data to separate tables
- Use views for complex reporting queries
```