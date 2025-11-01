# Intelligence Hub - KB metadata and analysis results

**Database:** `hdgwrzntwa`  
**Application:** hdgwrzntwa (Intelligence Server)  
**Scanned:** 2025-10-25 13:04:49  

## Summary

- **Tables:** 9
- **Total Columns:** 99
- **Total Indexes:** 44
- **Foreign Keys:** 4

---

## Tables

### `activity_logs`

**Rows:** 3 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `user_id` | int(11) | YES | MUL | 0 |  |
| `action` | varchar(100) | NO | MUL | NULL |  |
| `details` | text | YES |  | NULL |  |
| `ip_address` | varchar(45) | YES |  | NULL |  |
| `user_agent` | varchar(255) | YES |  | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_user_id` | BTREE |  | user_id |
| `idx_action` | BTREE |  | action |
| `idx_created_at` | BTREE |  | created_at |

---

### `business_units`

**Rows:** 12 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `unit_id` | int(11) | NO | PRI | NULL | auto_increment |
| `org_id` | int(11) | NO | MUL | NULL |  |
| `unit_name` | varchar(100) | NO |  | NULL |  |
| `unit_type` | enum('manufacturing','retail','wholesale','ecommerce','corporate','technical','logistics','importing','sales_analytics','inventory','financial_ops','customer_service','quality_assurance') | NO | MUL | NULL |  |
| `server_mapping` | varchar(50) | YES | MUL | NULL |  |
| `domain_mapping` | varchar(100) | YES |  | NULL |  |
| `redis_channel` | varchar(50) | NO | MUL | NULL |  |
| `intelligence_level` | enum('basic','advanced','neural','quantum') | YES |  | advanced |  |
| `scan_paths` | longtext | YES |  | NULL |  |
| `ignore_patterns` | longtext | YES |  | NULL |  |
| `is_active` | tinyint(1) | YES |  | 1 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | unit_id |
| `unique_org_unit` | BTREE | ✓ | org_id, unit_name |
| `idx_unit_type` | BTREE |  | unit_type |
| `idx_server_mapping` | BTREE |  | server_mapping |
| `idx_redis_channel` | BTREE |  | redis_channel |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `business_units_ibfk_1` | `org_id` | `organizations`.`org_id` |

---

### `dashboard_users`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `username` | varchar(50) | NO | UNI | NULL |  |
| `password_hash` | varchar(255) | NO |  | NULL |  |
| `email` | varchar(100) | YES |  | NULL |  |
| `role` | varchar(20) | YES | MUL | user |  |
| `permissions` | longtext | YES |  | NULL |  |
| `last_login` | timestamp | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `username` | BTREE | ✓ | username |
| `idx_username` | BTREE |  | username |
| `idx_role` | BTREE |  | role |

---

### `ecig_kb_categories`

**Rows:** 15 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `category_id` | int(11) | NO | PRI | NULL | auto_increment |
| `category_name` | varchar(200) | NO | MUL | NULL |  |
| `parent_category_id` | int(11) | YES | MUL | NULL |  |
| `description` | text | YES |  | NULL |  |
| `priority_weight` | decimal(3,2) | YES | MUL | 1.00 |  |
| `file_count` | int(11) | YES |  | 0 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | category_id |
| `unique_category` | BTREE | ✓ | category_name, parent_category_id |
| `idx_parent` | BTREE |  | parent_category_id |
| `idx_priority` | BTREE |  | priority_weight |

---

### `intelligence_content_types`

**Rows:** 17 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `content_type_id` | int(11) | NO | PRI | NULL | auto_increment |
| `type_name` | varchar(50) | NO | UNI | NULL |  |
| `type_category` | enum('code','documentation','data','media','operational','intelligence') | NO | MUL | NULL |  |
| `file_extensions` | longtext | YES |  | NULL |  |
| `processing_engine` | varchar(50) | YES |  | NULL |  |
| `intelligence_extractors` | longtext | YES |  | NULL |  |
| `redis_cache_strategy` | enum('hot','warm','cold','smart') | YES | MUL | smart |  |
| `description` | text | YES |  | NULL |  |
| `is_active` | tinyint(1) | YES |  | 1 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | content_type_id |
| `type_name` | BTREE | ✓ | type_name |
| `idx_type_category` | BTREE |  | type_category |
| `idx_cache_strategy` | BTREE |  | redis_cache_strategy |

---

### `intelligence_files`

**Rows:** 17,420 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `file_id` | bigint(20) | NO | PRI | NULL | auto_increment |
| `business_unit_id` | int(11) | NO | MUL | NULL |  |
| `server_id` | varchar(50) | NO | MUL | NULL |  |
| `file_path` | text | NO |  | NULL |  |
| `file_name` | varchar(255) | NO | MUL | NULL |  |
| `file_type` | enum('documentation','code_intelligence','business_intelligence','operational_intelligence') | NO | MUL | NULL |  |
| `file_size` | bigint(20) | NO |  | NULL |  |
| `file_content` | longtext | YES |  | NULL |  |
| `metadata` | text | YES |  | NULL |  |
| `intelligence_type` | varchar(100) | NO | MUL | NULL |  |
| `intelligence_data` | longtext | YES |  | NULL |  |
| `content_summary` | text | YES | MUL | NULL |  |
| `extracted_at` | timestamp | NO | MUL | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `is_active` | tinyint(1) | YES | MUL | 1 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | file_id |
| `idx_business_unit` | BTREE |  | business_unit_id |
| `idx_server` | BTREE |  | server_id |
| `idx_file_type` | BTREE |  | file_type |
| `idx_intelligence_type` | BTREE |  | intelligence_type |
| `idx_extracted_at` | BTREE |  | extracted_at |
| `idx_active` | BTREE |  | is_active |
| `idx_server_intelligence` | BTREE |  | server_id, intelligence_type |
| `idx_type_extracted` | BTREE |  | intelligence_type, extracted_at |
| `idx_server_extracted` | BTREE |  | server_id, extracted_at |
| `ft_file_name` | FULLTEXT |  | file_name |
| `ft_content_summary` | FULLTEXT |  | content_summary |

---

### `organizations`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `org_id` | int(11) | NO | PRI | NULL | auto_increment |
| `org_name` | varchar(100) | NO |  | NULL |  |
| `org_type` | enum('parent','subsidiary','division','department') | NO |  | NULL |  |
| `parent_org_id` | int(11) | YES | MUL | NULL |  |
| `org_code` | varchar(20) | NO | UNI | NULL |  |
| `redis_namespace` | varchar(50) | NO |  | NULL |  |
| `intelligence_level` | enum('basic','advanced','neural','quantum') | YES |  | neural |  |
| `is_active` | tinyint(1) | YES |  | 1 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | org_id |
| `org_code` | BTREE | ✓ | org_code |
| `idx_org_code` | BTREE |  | org_code |
| `idx_parent_org` | BTREE |  | parent_org_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `organizations_ibfk_1` | `parent_org_id` | `organizations`.`org_id` |

---

### `redis_cache_config`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `cache_config_id` | int(11) | NO | PRI | NULL | auto_increment |
| `org_id` | int(11) | NO | UNI | NULL |  |
| `cache_namespace` | varchar(50) | NO |  | NULL |  |
| `cache_strategy` | enum('hot','warm','cold','smart','custom') | NO | MUL | NULL |  |
| `default_ttl` | int(11) | NO |  | 3600 |  |
| `max_memory_mb` | int(11) | NO |  | 1024 |  |
| `eviction_policy` | enum('noeviction','allkeys-lru','volatile-lru','allkeys-random','volatile-random','volatile-ttl') | YES |  | allkeys-lru |  |
| `compression_enabled` | tinyint(1) | YES |  | 1 |  |
| `encryption_enabled` | tinyint(1) | YES |  | 0 |  |
| `replication_enabled` | tinyint(1) | YES |  | 1 |  |
| `clustering_enabled` | tinyint(1) | YES |  | 0 |  |
| `monitoring_enabled` | tinyint(1) | YES |  | 1 |  |
| `custom_config` | longtext | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | cache_config_id |
| `unique_org_cache` | BTREE | ✓ | org_id |
| `idx_cache_strategy` | BTREE |  | cache_strategy |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `redis_cache_config_ibfk_1` | `org_id` | `organizations`.`org_id` |

---

### `system_configuration`

**Rows:** 8 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `config_id` | int(11) | NO | PRI | NULL | auto_increment |
| `org_id` | int(11) | YES | MUL | NULL |  |
| `config_section` | varchar(50) | NO | MUL | NULL |  |
| `config_key` | varchar(100) | NO |  | NULL |  |
| `config_value` | text | NO |  | NULL |  |
| `config_type` | enum('string','integer','float','boolean','json','array','encrypted') | NO |  | NULL |  |
| `description` | text | YES |  | NULL |  |
| `validation_rules` | longtext | YES |  | NULL |  |
| `is_sensitive` | tinyint(1) | YES | MUL | 0 |  |
| `requires_restart` | tinyint(1) | YES |  | 0 |  |
| `is_active` | tinyint(1) | YES |  | 1 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | config_id |
| `unique_config` | BTREE | ✓ | org_id, config_section, config_key |
| `idx_config_section` | BTREE |  | config_section |
| `idx_sensitive_configs` | BTREE |  | is_sensitive |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `system_configuration_ibfk_1` | `org_id` | `organizations`.`org_id` |

---

