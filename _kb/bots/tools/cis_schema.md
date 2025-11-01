# CIS - Central Information System (staff.vapeshed.co.nz)

**Database:** `jcepnzzkmj`  
**Application:** jcepnzzkmj (CIS Production)  
**Scanned:** 2025-10-25 13:04:49  

## Summary

- **Tables:** 385
- **Total Columns:** 4345
- **Total Indexes:** 1476
- **Foreign Keys:** 119

---

## Tables

### `Session`

**Rows:** 7,069 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `Session_Id` | varbinary(64) | NO | PRI | NULL |  |
| `Session_Expires` | datetime | NO | MUL | NULL |  |
| `Session_Data` | mediumblob | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | Session_Id |
| `idx_session_expires` | BTREE |  | Session_Expires |

---

### `_migrations`

**Rows:** 3 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `filename` | varchar(255) | NO | PRI | NULL |  |
| `applied_at` | datetime | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | filename |

---

### `ai_assistant_logs`

**Rows:** 203 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `session_id` | varchar(64) | YES |  | NULL |  |
| `user_id` | int(11) | YES |  | NULL |  |
| `user_name` | varchar(100) | YES |  | NULL |  |
| `frontend_path` | varchar(255) | YES |  | NULL |  |
| `message_type` | enum('user','assistant','function_call','error') | YES |  | NULL |  |
| `message_content` | mediumtext | YES |  | NULL |  |
| `tool_called` | varchar(100) | YES |  | NULL |  |
| `api_response` | longtext | YES |  | NULL |  |
| `created_at` | datetime | YES |  | current_timestamp() |  |
| `ip_address` | varchar(45) | YES |  | NULL |  |
| `user_agent` | varchar(255) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `ai_cis_activity`

**Rows:** 2,208 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) | NO | PRI | NULL | auto_increment |
| `type` | varchar(64) | NO |  | NULL |  |
| `message` | varchar(512) | NO |  | NULL |  |
| `payload` | longtext | YES |  | NULL |  |
| `created_at` | datetime | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `ai_cis_error_events`

**Rows:** 1,994 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) | NO | PRI | NULL | auto_increment |
| `group_id` | bigint(20) | NO | MUL | NULL |  |
| `occurred_at` | datetime | NO |  | NULL |  |
| `file` | varchar(512) | YES |  | NULL |  |
| `line_no` | int(11) | YES |  | NULL |  |
| `url` | varchar(1024) | YES |  | NULL |  |
| `http_status` | int(11) | YES |  | NULL |  |
| `message` | text | YES |  | NULL |  |
| `context` | longtext | YES |  | NULL |  |
| `trace` | text | YES |  | NULL |  |
| `remote_addr` | varchar(64) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_group_at` | BTREE |  | group_id, occurred_at |

---

### `ai_cis_error_groups`

**Rows:** 125 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) | NO | PRI | NULL | auto_increment |
| `signature_hash` | varchar(40) | NO | UNI | NULL |  |
| `message_sample` | varchar(1024) | YES |  | NULL |  |
| `severity` | varchar(32) | NO |  | unknown |  |
| `status` | varchar(32) | NO |  | open |  |
| `first_seen` | datetime | NO |  | current_timestamp() |  |
| `last_seen` | datetime | NO |  | current_timestamp() |  |
| `count` | bigint(20) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `signature_hash` | BTREE | ✓ | signature_hash |

---

### `ai_cis_log_positions`

**Rows:** 6 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) | NO | PRI | NULL | auto_increment |
| `source` | varchar(64) | NO | MUL | NULL |  |
| `path` | varchar(512) | NO |  | NULL |  |
| `inode` | bigint(20) | YES |  | NULL |  |
| `offset` | bigint(20) | NO |  | 0 |  |
| `mtime` | bigint(20) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uniq_source_path` | BTREE | ✓ | source, path |

---

### `ai_freight_decisions`

**Rows:** 4 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) | NO | PRI | NULL | auto_increment |
| `transfer_id` | bigint(20) | NO | MUL | NULL |  |
| `features_json` | longtext | NO |  | NULL |  |
| `plan_json` | longtext | NO |  | NULL |  |
| `baseline_cost` | decimal(10,2) | NO |  | NULL |  |
| `optimized_cost` | decimal(10,2) | NO |  | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |
| `idempotency_key` | char(64) | YES | UNI | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uq_ai_decisions_idem` | BTREE | ✓ | idempotency_key |
| `ix_decisions_transfer` | BTREE |  | transfer_id |
| `ix_decisions_created` | BTREE |  | created_at |

---

### `ai_kb_config`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `domain_key` | varchar(50) | NO | MUL | global |  |
| `agent_name` | varchar(100) | NO | UNI | NULL |  |
| `api_url` | varchar(500) | NO |  | NULL |  |
| `api_key_encrypted` | varchar(500) | YES |  | NULL |  |
| `agent_id` | varchar(100) | YES |  | NULL |  |
| `is_active` | tinyint(1) | YES | MUL | 1 |  |
| `context_synced` | tinyint(1) | YES |  | 0 |  |
| `last_sync_at` | datetime | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `agent_name` | BTREE | ✓ | agent_name |
| `idx_active` | BTREE |  | is_active |
| `idx_domain_agent` | BTREE |  | domain_key, agent_name |

---

### `ai_kb_conversations`

**Rows:** 7 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `domain_key` | varchar(50) | NO | MUL | global |  |
| `agent_id` | int(10) unsigned | NO | MUL | NULL |  |
| `conversation_id` | varchar(100) | NO | UNI | NULL |  |
| `started_at` | datetime | NO |  | current_timestamp() |  |
| `ended_at` | datetime | YES |  | NULL |  |
| `total_messages` | int(11) | YES |  | 0 |  |
| `total_user_messages` | int(11) | YES |  | 0 |  |
| `total_agent_messages` | int(11) | YES |  | 0 |  |
| `avg_response_time_ms` | int(11) | YES |  | NULL |  |
| `topics_discussed` | longtext | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `total_orchestrations` | int(11) | YES |  | 0 |  |
| `avg_knowledge_items` | decimal(5,2) | YES |  | 0.00 |  |
| `primary_intents` | longtext | YES |  | NULL |  |
| `last_orchestrated_at` | datetime | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `conversation_id` | BTREE | ✓ | conversation_id |
| `agent_id` | BTREE |  | agent_id |
| `idx_conversation` | BTREE |  | conversation_id |
| `idx_domain_conversation` | BTREE |  | domain_key, conversation_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `ai_kb_conversations_ibfk_1` | `agent_id` | `ai_kb_config`.`id` |

---

### `ai_kb_domain_inheritance`

**Rows:** 10 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `child_domain` | varchar(50) | NO | MUL | NULL |  |
| `parent_domain` | varchar(50) | NO | MUL | NULL |  |
| `inheritance_priority` | int(11) | YES | MUL | 0 |  |
| `filters` | longtext | YES |  | NULL |  |
| `is_active` | tinyint(1) | YES | MUL | 1 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uniq_inheritance` | BTREE | ✓ | child_domain, parent_domain |
| `idx_child` | BTREE |  | child_domain |
| `idx_parent` | BTREE |  | parent_domain |
| `idx_priority` | BTREE |  | inheritance_priority |
| `idx_active` | BTREE |  | is_active |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `ai_kb_domain_inheritance_ibfk_1` | `child_domain` | `ai_kb_domain_registry`.`domain_key` |
| `ai_kb_domain_inheritance_ibfk_2` | `parent_domain` | `ai_kb_domain_registry`.`domain_key` |

---

### `ai_kb_domain_registry`

**Rows:** 6 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `domain_key` | varchar(50) | NO | UNI | NULL |  |
| `domain_name` | varchar(255) | NO | UNI | NULL |  |
| `application_name` | varchar(100) | YES |  | NULL |  |
| `kb_scope` | enum('isolated','shared','hybrid') | YES |  | hybrid |  |
| `inherit_from` | longtext | YES |  | NULL |  |
| `is_active` | tinyint(1) | YES | MUL | 1 |  |
| `config` | longtext | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uniq_domain_key` | BTREE | ✓ | domain_key |
| `uniq_domain_name` | BTREE | ✓ | domain_name |
| `idx_active` | BTREE |  | is_active |

---

### `ai_kb_errors`

**Rows:** 7 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `domain_key` | varchar(50) | NO | MUL | global |  |
| `agent_id` | int(10) unsigned | YES | MUL | NULL |  |
| `error_type` | varchar(100) | NO | MUL | NULL |  |
| `error_code` | varchar(50) | YES |  | NULL |  |
| `error_message` | text | NO |  | NULL |  |
| `stack_trace` | text | YES |  | NULL |  |
| `context` | longtext | YES |  | NULL |  |
| `severity` | enum('low','medium','high','critical') | YES | MUL | medium |  |
| `resolved` | tinyint(1) | YES |  | 0 |  |
| `resolved_at` | datetime | YES |  | NULL |  |
| `occurred_at` | datetime | NO |  | current_timestamp() |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `agent_id` | BTREE |  | agent_id |
| `idx_type` | BTREE |  | error_type |
| `idx_severity` | BTREE |  | severity |
| `idx_domain_error` | BTREE |  | domain_key, error_type |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `ai_kb_errors_ibfk_1` | `agent_id` | `ai_kb_config`.`id` |

---

### `ai_kb_knowledge_items`

**Rows:** 11,815 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `domain_key` | varchar(50) | NO | MUL | global |  |
| `source_file` | varchar(500) | NO | MUL | NULL |  |
| `item_type` | enum('fact','table','api','workflow','config','other') | NO | MUL | NULL |  |
| `item_key` | varchar(255) | NO |  | NULL |  |
| `item_content` | text | NO |  | NULL |  |
| `category` | varchar(100) | YES |  | NULL |  |
| `importance_score` | decimal(3,2) | YES |  | 0.50 |  |
| `times_referenced` | int(11) | YES |  | 0 |  |
| `last_referenced_at` | datetime | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `unique_item` | BTREE | ✓ | source_file, item_type, item_key |
| `idx_type_category` | BTREE |  | item_type, category |
| `idx_domain_item_type` | BTREE |  | domain_key, item_type |

---

### `ai_kb_queries`

**Rows:** 4 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `domain_key` | varchar(50) | NO | MUL | global |  |
| `agent_id` | int(10) unsigned | NO | MUL | NULL |  |
| `conversation_id` | varchar(100) | YES |  | NULL |  |
| `query_text` | text | NO |  | NULL |  |
| `query_hash` | char(32) | NO | MUL | NULL |  |
| `response_text` | longtext | YES |  | NULL |  |
| `response_sources` | longtext | YES |  | NULL |  |
| `confidence_score` | decimal(3,2) | YES |  | NULL |  |
| `response_time_ms` | int(11) | YES |  | NULL |  |
| `query_mode` | enum('test','sync','query','chat') | YES |  | query |  |
| `status` | enum('success','failed','timeout') | YES |  | success |  |
| `queried_at` | datetime | NO |  | current_timestamp() |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `orchestration_id` | int(10) unsigned | YES | MUL | NULL |  |
| `intent_detected` | varchar(50) | YES | MUL | NULL |  |
| `tools_used` | longtext | YES |  | NULL |  |
| `knowledge_items_count` | int(11) | YES |  | 0 |  |
| `orchestration_time_ms` | int(11) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_agent_date` | BTREE |  | agent_id, queried_at |
| `idx_query_hash` | BTREE |  | query_hash |
| `idx_queries_orchestration` | BTREE |  | orchestration_id |
| `idx_queries_intent` | BTREE |  | intent_detected |
| `idx_domain_query` | BTREE |  | domain_key, queried_at |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `ai_kb_queries_ibfk_1` | `agent_id` | `ai_kb_config`.`id` |

---

### `ai_kb_rate_limits`

**Rows:** 12 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `domain_key` | varchar(50) | NO | MUL | global |  |
| `rate_key` | varchar(255) | NO | UNI | NULL |  |
| `requests` | int(10) unsigned | NO |  | 0 |  |
| `window_start` | datetime | NO |  | NULL |  |
| `expires_at` | datetime | NO | MUL | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_rate_key` | BTREE | ✓ | rate_key |
| `idx_expires` | BTREE |  | expires_at |
| `idx_domain_ratelimit` | BTREE |  | domain_key, window_start |

---

### `ai_kb_sync_history`

**Rows:** 14 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `domain_key` | varchar(50) | NO | MUL | global |  |
| `agent_id` | int(10) unsigned | NO | MUL | NULL |  |
| `sync_started_at` | datetime | NO |  | NULL |  |
| `sync_completed_at` | datetime | YES |  | NULL |  |
| `files_scanned` | int(11) | YES |  | 0 |  |
| `items_created` | int(11) | YES |  | 0 |  |
| `items_updated` | int(11) | YES |  | 0 |  |
| `items_deleted` | int(11) | YES |  | 0 |  |
| `relationships_found` | int(11) | YES |  | 0 |  |
| `errors_count` | int(11) | YES |  | 0 |  |
| `status` | enum('success','failed','partial','pending') | YES |  | pending |  |
| `files_processed` | int(11) | YES |  | 0 |  |
| `facts_synced` | int(11) | YES |  | 0 |  |
| `topics_synced` | int(11) | YES |  | 0 |  |
| `kb_size_bytes` | bigint(20) | YES |  | 0 |  |
| `error_message` | text | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_agent_date` | BTREE |  | agent_id, sync_started_at |
| `idx_domain_sync` | BTREE |  | domain_key, sync_started_at |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `ai_kb_sync_history_ibfk_1` | `agent_id` | `ai_kb_config`.`id` |

---

### `ai_notifications`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `notification_type` | varchar(50) | NO | MUL | NULL |  |
| `title` | varchar(200) | NO |  | NULL |  |
| `message` | text | NO |  | NULL |  |
| `urgency_level` | enum('low','medium','high','critical') | YES | MUL | medium |  |
| `action_buttons` | longtext | YES |  | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |
| `sent_at` | timestamp | YES |  | NULL |  |
| `acknowledged_at` | timestamp | YES |  | NULL |  |
| `dismissed_at` | timestamp | YES |  | NULL |  |
| `status` | enum('pending','sent','acknowledged','dismissed','expired') | YES | MUL | pending |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_type` | BTREE |  | notification_type |
| `idx_urgency` | BTREE |  | urgency_level |
| `idx_status` | BTREE |  | status |
| `idx_created` | BTREE |  | created_at |

---

### `ai_predictions`

**Rows:** 9,120 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `type` | varchar(50) | NO | MUL | NULL |  |
| `message` | text | NO |  | NULL |  |
| `urgency_score` | int(11) | NO | MUL | 0 |  |
| `context_data` | longtext | YES |  | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `status` | enum('active','dismissed','completed','critical','immediate') | YES | MUL | active |  |
| `user_action` | varchar(100) | YES |  | NULL |  |
| `action_taken_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_type` | BTREE |  | type |
| `idx_urgency` | BTREE |  | urgency_score |
| `idx_status` | BTREE |  | status |
| `idx_created` | BTREE |  | created_at |

---

### `ai_training_feedback`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `detection_id` | varchar(100) | NO | UNI | NULL |  |
| `camera_id` | varchar(50) | NO | MUL | NULL |  |
| `outlet_id` | varchar(45) | NO | MUL | NULL |  |
| `original_detection` | longtext | NO |  | NULL |  |
| `gpt_analysis` | longtext | YES |  | NULL |  |
| `human_feedback` | enum('approve','reject','modify') | NO | MUL | NULL |  |
| `human_notes` | text | YES |  | NULL |  |
| `corrected_classification` | varchar(100) | YES |  | NULL |  |
| `confidence_before` | decimal(5,4) | YES |  | NULL |  |
| `confidence_after` | decimal(5,4) | YES |  | NULL |  |
| `image_path` | varchar(500) | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `feedback_at` | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| `training_used` | tinyint(1) | YES |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `detection_id` | BTREE | ✓ | detection_id |
| `idx_feedback_type` | BTREE |  | human_feedback, training_used |
| `idx_camera_feedback` | BTREE |  | camera_id, created_at |
| `idx_outlet_feedback` | BTREE |  | outlet_id, created_at |

---

### `automated_supplier_orders`

**Rows:** 19 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `supplier_id` | varchar(36) | NO |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `weeks_forecasted` | int(11) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `automated_supplier_orders_products`

**Rows:** 998 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `supplier_order_id` | int(11) | NO | MUL | NULL |  |
| `product_id` | varchar(36) | NO |  | NULL |  |
| `total_items_sold` | int(11) | YES |  | 0 |  |
| `current_inventory` | int(11) | YES |  | 0 |  |
| `monthly_average_sales` | int(11) | YES |  | 0 |  |
| `last_30_days` | int(11) | YES |  | NULL |  |
| `ai_forecasted_order_amount` | int(11) | YES |  | 0 |  |
| `average_order_qty` | int(11) | YES |  | NULL |  |
| `qty_to_order` | int(11) | YES |  | 0 |  |
| `user_qty_input` | int(11) | YES |  | NULL |  |
| `plot_binary` | longblob | YES |  | NULL |  |
| `prophet_json_data` | mediumtext | YES |  | NULL |  |
| `advanced_qty_to_order` | varchar(45) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `automatedSupplierID_idx` | BTREE |  | supplier_order_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `automatedSupplierID` | `supplier_order_id` | `automated_supplier_orders`.`id` |

---

### `award_types`

**Rows:** 21 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `type_id` | int(11) | NO | PRI | NULL | auto_increment |
| `type_title` | mediumtext | NO |  | NULL |  |
| `type_desc` | mediumtext | YES |  | NULL |  |
| `type_points` | varchar(45) | NO |  | NULL |  |
| `sort_order` | int(1) | NO |  | NULL |  |
| `active` | int(1) | NO |  | 1 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | type_id |

---

### `batching_bottle`

**Rows:** 6 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `bottle_id` | int(11) | NO | PRI | NULL | auto_increment |
| `bottle_name` | varchar(100) | NO |  | NULL |  |
| `bottle_supplier_sku` | varchar(100) | YES |  | NULL |  |
| `bottle_supplier_id` | int(11) | NO |  | NULL |  |
| `bottle_notes` | varchar(45) | YES |  | NULL |  |
| `bottle_created_at` | timestamp | NO |  | current_timestamp() |  |
| `bottle_deleted_at` | timestamp | YES |  | NULL |  |
| `bottle_created_by_staff` | int(11) | NO |  | NULL |  |
| `bottle_deleted_by_staff` | int(11) | YES |  | NULL |  |
| `bottle_size` | int(11) | NO |  | NULL |  |
| `bottle_colour` | int(11) | NO |  | NULL |  |
| `bottle_lidcap_colour` | int(11) | NO |  | NULL |  |
| `bottle_label_width` | double | NO |  | NULL |  |
| `bottle_label_height` | double | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | bottle_id |

---

### `batching_bottle_run`

**Rows:** 2,067 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `batching_bottle_run_id` | int(11) | NO | PRI | NULL | auto_increment |
| `batching_bottle_run_batch_id` | int(11) | NO |  | NULL |  |
| `batching_bottle_run_label_id` | int(11) | NO |  | NULL |  |
| `batching_bottle_run_bottle_amount` | int(11) | NO |  | NULL |  |
| `battching_bottle_run_notes` | mediumtext | YES |  | NULL |  |
| `batching_bottle_run_batch_object` | mediumtext | NO |  | NULL |  |
| `batching_bottle_run_label_object` | mediumtext | NO |  | NULL |  |
| `batching_bottle_run_label_created_at` | timestamp | NO |  | current_timestamp() |  |
| `batching_bottle_run_label_deleted_at` | timestamp | YES |  | NULL |  |
| `batching_bottle_run_label_created_by` | int(11) | NO |  | NULL |  |
| `batching_bottle_run_label_deleted_by` | int(11) | YES |  | NULL |  |
| `batching_bottle_run_status` | int(11) | NO |  | 0 |  |
| `batching_bottle_create_mode` | int(11) | NO |  | 0 |  |
| `batching_bottle_run_bottle_id` | int(11) | YES |  | NULL |  |
| `batching_bottle_run_bottle_object` | mediumtext | YES |  | NULL |  |
| `batching_bottle_run_labels_printed` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | batching_bottle_run_id |

---

### `batching_bottle_suppliers`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `bottle_supplier_id` | int(11) | NO | PRI | NULL | auto_increment |
| `bottle_supplier_name` | varchar(45) | NO |  | NULL |  |
| `bottle_supplier_created_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `bottle_supplier_deleted_at` | timestamp | YES |  | NULL |  |
| `bottle_supplier_created_by_staff` | int(11) | NO |  | NULL |  |
| `bottle_supplier_deleted_by_staff` | int(11) | YES |  | NULL |  |
| `bottle_supplier_notes` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | bottle_supplier_id |

---

### `batching_brands`

**Rows:** 9 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `brand_id` | int(11) | NO | PRI | NULL | auto_increment |
| `brand_name` | varchar(45) | NO |  | NULL |  |
| `brand_created_at` | timestamp | NO |  | current_timestamp() |  |
| `brand_deleted_at` | timestamp | YES |  | NULL |  |
| `brand_created_by_staff` | int(11) | NO |  | NULL |  |
| `brand_deleted_by_staff` | int(11) | YES |  | NULL |  |
| `brand_notes` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | brand_id |

---

### `batching_concentrate`

**Rows:** 188 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `concentrate_id` | int(11) | NO | PRI | NULL | auto_increment |
| `concentrate_name` | varchar(100) | NO |  | NULL |  |
| `concentrate_supplier_sku` | varchar(100) | YES |  | NULL |  |
| `concentrate_supplier_id` | int(11) | NO |  | NULL |  |
| `concentrate_notes` | varchar(45) | YES |  | NULL |  |
| `concentrate_created_at` | timestamp | NO |  | current_timestamp() |  |
| `concentrate_deleted_at` | timestamp | YES |  | NULL |  |
| `concentrate_created_by_staff` | int(11) | NO |  | NULL |  |
| `concentrate_deleted_by_staff` | int(11) | YES |  | NULL |  |
| `concentrate_liquid_base` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | concentrate_id |

---

### `batching_concentrate_batch`

**Rows:** 231 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `batching_concentrate_batch_id` | int(11) | NO | PRI | NULL | auto_increment |
| `batching_concentrate_batch_size` | int(11) | NO |  | NULL |  |
| `batching_concentrate_batch_recipe_id` | int(11) | NO |  | NULL |  |
| `batching_concentrate_batch_batch_notes` | mediumtext | YES |  | NULL |  |
| `batching_concentrate_batch_batch_created_at` | timestamp | NO |  | current_timestamp() |  |
| `batching_concentrate_batch_batch_deleted_at` | timestamp | YES |  | NULL |  |
| `batching_concentrate_batch_batch_created_by` | int(11) | NO |  | NULL |  |
| `batching_concentrate_batch_batch_deleted_by` | int(11) | YES |  | NULL |  |
| `batching_concentrate_batch_batch_saved_template` | int(11) | NO |  | 0 |  |
| `batching_concentrate_batch_saved_template_filename` | varchar(100) | YES |  | NULL |  |
| `batching_concentrate_batch_recipe_object` | mediumtext | NO |  | NULL |  |
| `batching_concentrate_batch_active` | int(11) | NO |  | 1 |  |
| `batching_concentrate_batch_pg_batch_number` | varchar(100) | YES |  | NULL |  |
| `batching_concentrate_batch_vg_batch_number` | varchar(100) | YES |  | NULL |  |
| `batching_concentrate_batch_flavours_batch_numbers` | mediumtext | YES |  | NULL |  |
| `batching_concentrate_batch_pg_id` | int(11) | YES |  | NULL |  |
| `batching_concentrate_batch_pg_object` | mediumtext | YES |  | NULL |  |
| `batching_concentrate_batch_pg_ml` | int(11) | YES |  | NULL |  |
| `batching_concentrate_batch_vg_ml` | int(11) | YES |  | NULL |  |
| `batching_concentrate_batch_concentrate_ml` | int(11) | YES |  | NULL |  |
| `batching_concentrate_batch_total_ml` | int(11) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | batching_concentrate_batch_id |

---

### `batching_concentrate_suppliers`

**Rows:** 12 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `concentrate_supplier_id` | int(11) | NO | PRI | NULL | auto_increment |
| `concentrate_supplier_name` | varchar(45) | NO |  | NULL |  |
| `concentrate_supplier_created_at` | timestamp | NO |  | current_timestamp() |  |
| `concentrate_supplier_deleted_at` | timestamp | YES |  | NULL |  |
| `concentrate_supplier_created_by_staff` | int(11) | NO |  | NULL |  |
| `concentrate_supplier_deleted_by_staff` | int(11) | YES |  | NULL |  |
| `concentrate_supplier_notes` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | concentrate_supplier_id |

---

### `batching_full_batch`

**Rows:** 1,353 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `full_batch_id` | int(11) | NO | PRI | NULL | auto_increment |
| `full_batch_size` | int(11) | NO |  | NULL |  |
| `full_batch_creation_mode` | int(11) | NO |  | NULL |  |
| `full_batch_desired_nic_strength` | decimal(5,2) | NO |  | NULL |  |
| `full_batch_recipe_id` | int(11) | YES |  | NULL |  |
| `full_batch_concentrate_id` | int(11) | YES |  | NULL |  |
| `full_batch_nicotine_selction_mode` | int(11) | YES |  | NULL |  |
| `full_batch_nicotine_product_id` | int(11) | YES |  | NULL |  |
| `full_batch_batched_nicotine_id` | int(11) | YES |  | NULL |  |
| `full_batch_pg_product_id` | int(11) | YES |  | NULL |  |
| `full_batch_pg_percentage` | decimal(5,2) | YES |  | NULL |  |
| `full_batch_vg_product_id` | int(11) | YES |  | NULL |  |
| `full_batch_vg_percentage` | decimal(5,2) | YES |  | NULL |  |
| `full_batch_notes` | mediumtext | YES |  | NULL |  |
| `full_batch_template_name` | varchar(100) | YES |  | NULL |  |
| `full_batch_saved_template` | int(11) | YES |  | NULL |  |
| `full_batch_recipe_object` | mediumtext | YES |  | NULL |  |
| `full_batch_nicotine_object` | mediumtext | YES |  | NULL |  |
| `full_batch_nicotine_batch_object` | mediumtext | YES |  | NULL |  |
| `full_batch_vg_object` | mediumtext | YES |  | NULL |  |
| `full_batch_pg_object` | mediumtext | YES |  | NULL |  |
| `full_batch_concentrate_object` | mediumtext | YES |  | NULL |  |
| `full_batch_created_at` | timestamp | NO |  | current_timestamp() |  |
| `full_batch_deleted_at` | timestamp | YES |  | NULL |  |
| `full_batch_created_by` | int(11) | NO |  | NULL |  |
| `full_batch_deleted_by` | int(11) | YES |  | NULL |  |
| `full_batch_active` | int(11) | NO |  | 1 |  |
| `full_batch_batch_numbers_object` | mediumtext | NO |  | NULL |  |
| `full_batch_vg_ml` | int(11) | YES |  | NULL |  |
| `full_batch_pg_ml` | int(11) | YES |  | NULL |  |
| `full_batch_nicotine_ml` | int(11) | YES |  | NULL |  |
| `full_batch_concentrate_ml` | int(11) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | full_batch_id |

---

### `batching_labels`

**Rows:** 228 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `batching_label_id` | int(11) | NO | PRI | NULL | auto_increment |
| `batching_label_recipe_id` | int(11) | NO |  | NULL |  |
| `batching_label_bottle_nicotine_strength` | decimal(5,2) | NO |  | NULL |  |
| `batching_label_template_filename` | varchar(100) | NO |  | NULL |  |
| `batching_label_has_batch_no` | int(11) | NO |  | 1 |  |
| `batching_label_has_expire_no` | int(11) | NO |  | 1 |  |
| `batching_label_batch_no_x` | int(11) | YES |  | NULL |  |
| `batching_label_batch_no_y` | int(11) | YES |  | NULL |  |
| `batching_label_batch_expire_x` | int(11) | YES |  | NULL |  |
| `batching_label_batch_expire_y` | int(11) | YES |  | NULL |  |
| `batching_label_batch_no_font_size` | int(11) | YES |  | NULL |  |
| `batching_label_batch_expire_font_size` | int(11) | YES |  | NULL |  |
| `batching_label_batch_expire_rotate` | int(11) | NO |  | 0 |  |
| `batching_label_batch_rotate` | int(11) | NO |  | 0 |  |
| `batching_label_bottle_id` | int(11) | YES |  | NULL |  |
| `batching_label_created` | timestamp | NO |  | current_timestamp() |  |
| `batching_label_deleted` | varchar(45) | YES |  | NULL |  |
| `batching_label_created_by` | int(11) | YES |  | NULL |  |
| `batching_label_deleted_by` | int(11) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | batching_label_id |

---

### `batching_nicotine`

**Rows:** 5 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `nicotine_id` | int(11) | NO | PRI | NULL | auto_increment |
| `nicotine_supplier_sku` | varchar(100) | YES |  | NULL |  |
| `nicotine_supplier_id` | int(11) | NO |  | NULL |  |
| `nicotine_notes` | varchar(45) | YES |  | NULL |  |
| `nicotine_created_at` | timestamp | NO |  | current_timestamp() |  |
| `nicotine_deleted_at` | timestamp | YES |  | NULL |  |
| `nicotine_created_by_staff` | int(11) | NO |  | NULL |  |
| `nicotine_deleted_by_staff` | int(11) | YES |  | NULL |  |
| `nicotine_type` | int(11) | NO |  | NULL |  |
| `nicotine_pg_percent` | int(11) | NO |  | NULL |  |
| `nicotine_vg_percent` | int(11) | NO |  | NULL |  |
| `nicotine_level` | int(11) | NO |  | NULL |  |
| `nicotine_ntn` | int(11) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | nicotine_id |

---

### `batching_nicotine_batch`

**Rows:** 39 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `batching_nicotine_mix_id` | int(11) | NO | PRI | NULL | auto_increment |
| `batching_nicotine_mix_batch_size` | int(11) | NO |  | NULL |  |
| `batching_nicotine_mix_nicotine_id` | int(11) | NO |  | NULL |  |
| `batching_nicotine_mix_pg_id` | int(11) | YES |  | NULL |  |
| `batching_nicotine_mix_vg_id` | int(11) | YES |  | NULL |  |
| `batching_nicotine_mix_nicotine_percentage` | decimal(5,2) | NO |  | NULL |  |
| `batching_nicotine_mix_pg_percentage` | decimal(5,2) | YES |  | NULL |  |
| `batching_nicotine_mix_vg_percentage` | decimal(5,2) | YES |  | 0.00 |  |
| `batching_nicotine_mix_time_created_at` | timestamp | NO |  | current_timestamp() |  |
| `batching_nicotine_mix_time_deleted_at` | timestamp | YES |  | NULL |  |
| `batching_nicotine_mix_added_by_staff` | int(11) | NO |  | NULL |  |
| `batching_nicotine_mix_deleted_by_staff` | int(11) | YES |  | NULL |  |
| `batching_nicotine_mix_notes` | mediumtext | YES |  | NULL |  |
| `batching_nicotine_mix_saved_template` | int(11) | NO |  | 0 |  |
| `batching_nicotine_mix_saved_template_saved_name` | varchar(100) | YES |  | NULL |  |
| `batching_nicotine_batch_nicotine_object` | mediumtext | NO |  | NULL |  |
| `batching_nicotine_batch_pg_object` | mediumtext | YES |  | NULL |  |
| `batching_nicotine_batch_vg_object` | mediumtext | YES |  | NULL |  |
| `batching_nicotine_batch_nic_strength` | decimal(5,2) | NO |  | NULL |  |
| `batching_nicotine_batch_active` | int(11) | NO |  | 1 |  |
| `batching_nicotine_batch_pg_batch_no` | varchar(100) | YES |  | NULL |  |
| `batching_nicotine_batch_vg_batch_no` | varchar(100) | YES |  | NULL |  |
| `batching_nicotine_batch_nic_batch_no` | varchar(100) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | batching_nicotine_mix_id |

---

### `batching_nicotine_suppliers`

**Rows:** 5 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `nicotine_supplier_id` | int(11) | NO | PRI | NULL | auto_increment |
| `nicotine_supplier_name` | varchar(45) | NO |  | NULL |  |
| `nicotine_supplier_created_at` | timestamp | NO |  | current_timestamp() |  |
| `nicotine_supplier_deleted_at` | timestamp | YES |  | NULL |  |
| `nicotine_supplier_created_by_staff` | int(11) | NO |  | NULL |  |
| `nicotine_supplier_deleted_by_staff` | int(11) | YES |  | NULL |  |
| `nicotine_supplier_notes` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | nicotine_supplier_id |

---

### `batching_pg`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `pg_id` | int(11) | NO | PRI | NULL | auto_increment |
| `pg_name` | varchar(100) | NO |  | NULL |  |
| `pg_supplier_sku` | varchar(100) | YES |  | NULL |  |
| `pg_supplier_id` | int(11) | NO |  | NULL |  |
| `pg_notes` | varchar(45) | YES |  | NULL |  |
| `pg_created_at` | timestamp | NO |  | current_timestamp() |  |
| `pg_deleted_at` | timestamp | YES |  | NULL |  |
| `pg_created_by_staff` | int(11) | NO |  | NULL |  |
| `pg_deleted_by_staff` | int(11) | YES |  | NULL |  |
| `pg_type` | int(11) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | pg_id |

---

### `batching_pg_suppliers`

**Rows:** 4 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `pg_supplier_id` | int(11) | NO | PRI | NULL | auto_increment |
| `pg_supplier_name` | varchar(45) | NO |  | NULL |  |
| `pg_supplier_created_at` | timestamp | NO |  | current_timestamp() |  |
| `pg_supplier_deleted_at` | timestamp | YES |  | NULL |  |
| `pg_supplier_created_by_staff` | int(11) | NO |  | NULL |  |
| `pg_supplier_deleted_by_staff` | int(11) | YES |  | NULL |  |
| `pg_supplier_notes` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | pg_supplier_id |

---

### `batching_recipe`

**Rows:** 60 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `recipe_id` | int(11) | NO | PRI | NULL | auto_increment |
| `recipe_name` | varchar(100) | NO |  | NULL |  |
| `recipe_desired_vg` | decimal(5,2) | NO |  | NULL |  |
| `recipe_desired_pg` | decimal(5,2) | NO |  | NULL |  |
| `recipe_notes` | mediumtext | YES |  | NULL |  |
| `recipe_created_at` | timestamp | NO |  | current_timestamp() |  |
| `recipe_deleted_at` | timestamp | YES |  | NULL |  |
| `recipe_created_by_staff` | int(11) | NO |  | NULL |  |
| `recipe_deleted_by_staff` | int(11) | YES |  | NULL |  |
| `recipe_version` | decimal(5,1) | NO |  | 1.0 |  |
| `recipe_brand_id` | int(11) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | recipe_id |

---

### `batching_recipe_changes`

**Rows:** 101 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `batching_recipe_changes_id` | int(11) | NO | PRI | NULL | auto_increment |
| `batching_recipe_changes_recipe_id` | int(11) | NO |  | NULL |  |
| `batching_recipe_changes_field_changed` | varchar(100) | NO |  | NULL |  |
| `batching_recipe_changes_old_value` | varchar(100) | NO |  | NULL |  |
| `batching_recipe_changes_new_value` | varchar(100) | NO |  | NULL |  |
| `batching_recipe_changes_date_changed` | timestamp | NO |  | current_timestamp() |  |
| `batching_recipe_changes_version` | decimal(5,1) | NO |  | NULL |  |
| `batching_recipe_changed_by_staff` | int(11) | NO |  | NULL |  |
| `batching_recipe_changed_original_json` | mediumtext | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | batching_recipe_changes_id |

---

### `batching_recipe_flavours`

**Rows:** 445 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `batching_recipe_flavours_id` | int(11) | NO | PRI | NULL | auto_increment |
| `batching_recipe_flavours_concentrate_id` | int(11) | NO |  | NULL |  |
| `batching_recipe_id` | int(11) | YES |  | NULL |  |
| `batching_recipe_flavours_created_at` | timestamp | NO |  | current_timestamp() |  |
| `batching_recipe_flavours_deleted_at` | timestamp | YES |  | NULL |  |
| `batching_recipe_flavours_created_by_staff` | int(11) | NO |  | NULL |  |
| `batching_recipe_flavours_deleted_by_staff` | int(11) | YES |  | NULL |  |
| `batching_recipe_flavours_concentrate_percentage` | decimal(5,2) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | batching_recipe_flavours_id |

---

### `batching_vend_products`

**Rows:** 218 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `batching_vend_products_id` | int(11) | NO | PRI | NULL | auto_increment |
| `batching_vend_products_product_id` | varchar(45) | NO |  | NULL |  |
| `batching_vend_products_label_id` | int(11) | YES |  | NULL |  |
| `batching_vend_products_active` | int(11) | NO |  | 1 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | batching_vend_products_id |

---

### `batching_vg`

**Rows:** 4 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `vg_id` | int(11) | NO | PRI | NULL | auto_increment |
| `vg_supplier_sku` | varchar(100) | YES |  | NULL |  |
| `vg_supplier_id` | int(11) | NO |  | NULL |  |
| `vg_notes` | varchar(45) | YES |  | NULL |  |
| `vg_created_at` | timestamp | NO |  | current_timestamp() |  |
| `vg_deleted_at` | timestamp | YES |  | NULL |  |
| `vg_created_by_staff` | int(11) | NO |  | NULL |  |
| `vg_deleted_by_staff` | int(11) | YES |  | NULL |  |
| `vg_type` | int(11) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | vg_id |

---

### `batching_vg_suppliers`

**Rows:** 3 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `vg_supplier_id` | int(11) | NO | PRI | NULL | auto_increment |
| `vg_supplier_name` | varchar(45) | NO |  | NULL |  |
| `vg_supplier_created_at` | timestamp | NO |  | current_timestamp() |  |
| `vg_supplier_deleted_at` | timestamp | YES |  | NULL |  |
| `vg_supplier_created_by_staff` | int(11) | NO |  | NULL |  |
| `vg_supplier_deleted_by_staff` | int(11) | YES |  | NULL |  |
| `vg_supplier_notes` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | vg_supplier_id |

---

### `carrier_service_options`

**Rows:** 55 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `service_id` | int(11) | NO | PRI | NULL |  |
| `option_code` | varchar(32) | NO | PRI | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | service_id, option_code |
| `fk_cso_option` | BTREE |  | option_code |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_cso_option` | `option_code` | `delivery_options`.`option_code` |
| `fk_cso_service` | `service_id` | `carrier_services`.`service_id` |

---

### `carrier_services`

**Rows:** 11 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `service_id` | int(11) | NO | PRI | NULL | auto_increment |
| `carrier_id` | int(11) | NO | MUL | NULL |  |
| `code` | varchar(64) | NO |  | NULL |  |
| `name` | varchar(150) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | service_id |
| `uniq_carrier_service` | BTREE | ✓ | carrier_id, code |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_cs_carrier` | `carrier_id` | `carriers`.`carrier_id` |
| `fk_services_carrier` | `carrier_id` | `carriers`.`carrier_id` |

---

### `carriers`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `carrier_id` | int(11) | NO | PRI | NULL | auto_increment |
| `code` | varchar(32) | NO | UNI | NULL |  |
| `name` | varchar(100) | NO |  | NULL |  |
| `active` | tinyint(1) | NO |  | 1 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `volumetric_factor` | int(11) | NO |  | 200 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | carrier_id |
| `code` | BTREE | ✓ | code |

---

### `categories`

**Rows:** 188 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | varchar(50) | NO | PRI | NULL |  |
| `name` | varchar(255) | NO |  | NULL |  |
| `parent_id` | varchar(50) | YES | MUL | NULL |  |
| `depth` | int(11) | NO | MUL | 0 |  |
| `lft` | int(11) | YES | MUL | NULL |  |
| `rgt` | int(11) | YES | MUL | NULL |  |
| `slug` | varchar(160) | YES |  | NULL |  |
| `is_active` | tinyint(1) | NO |  | 1 |  |
| `path_labels` | longtext | YES |  | NULL |  |
| `path_ids` | longtext | YES |  | NULL |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `ix_cat_parent` | BTREE |  | parent_id |
| `ix_cat_depth` | BTREE |  | depth |
| `ix_cat_lft` | BTREE |  | lft |
| `ix_cat_rgt` | BTREE |  | rgt |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_cat_parent` | `parent_id` | `categories`.`id` |

---

### `category_dimensions`

**Rows:** 61 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `category_id` | varchar(50) | NO | PRI | NULL |  |
| `category_code` | varchar(100) | YES | MUL | NULL |  |
| `avg_length_mm` | int(11) | YES |  | NULL |  |
| `avg_width_mm` | int(11) | YES |  | NULL |  |
| `avg_height_mm` | int(11) | YES |  | NULL |  |
| `avg_volume_cm3` | int(11) | YES |  | NULL |  |
| `product_count` | int(11) | YES |  | 0 |  |
| `confidence_score` | decimal(3,2) | YES | MUL | 0.00 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | category_id |
| `idx_category_code` | BTREE |  | category_code |
| `idx_confidence` | BTREE |  | confidence_score |

---

### `category_pack_rules`

**Rows:** 9 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `category_code` | varchar(100) | NO | PRI | NULL |  |
| `category_id` | varchar(50) | YES |  | NULL |  |
| `default_pack_size` | int(11) | NO |  | 1 |  |
| `default_outer_multiple` | int(11) | NO |  | 1 |  |
| `enforce_outer` | tinyint(1) | NO |  | 1 |  |
| `rounding_mode` | enum('floor','ceil','round') | NO |  | floor |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `ship_pack_min` | int(11) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | category_code |

---

### `category_weights`

**Rows:** 71 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `category_code` | varchar(100) | NO | PRI | NULL |  |
| `category_id` | varchar(50) | YES |  | NULL |  |
| `avg_weight_grams` | int(11) | YES |  | 0 |  |
| `avg_volume_cm3` | int(11) | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | category_code |

---

### `chat_channel_participants`

**Rows:** 3 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `channel_id` | int(11) | NO | MUL | NULL |  |
| `user_id` | int(11) | NO | MUL | NULL |  |
| `role` | enum('owner','admin','member') | YES |  | member |  |
| `joined_at` | timestamp | NO |  | current_timestamp() |  |
| `is_muted` | tinyint(1) | YES |  | 0 |  |
| `last_read_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `unique_participant` | BTREE | ✓ | channel_id, user_id |
| `idx_user_id` | BTREE |  | user_id |
| `idx_channel_id` | BTREE |  | channel_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `chat_channel_participants_ibfk_1` | `channel_id` | `chat_channels`.`id` |

---

### `chat_channels`

**Rows:** 3 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `name` | varchar(100) | NO |  | NULL |  |
| `description` | text | YES |  | NULL |  |
| `channel_type` | enum('direct','group','department','store','announcement') | YES | MUL | group |  |
| `created_by` | int(11) | NO | MUL | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `is_archived` | tinyint(1) | YES |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_type` | BTREE |  | channel_type |
| `idx_created_by` | BTREE |  | created_by |

---

### `chat_message_reads`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `message_id` | int(11) | NO | MUL | NULL |  |
| `user_id` | int(11) | NO | MUL | NULL |  |
| `read_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `unique_read` | BTREE | ✓ | message_id, user_id |
| `idx_message_id` | BTREE |  | message_id |
| `idx_user_id` | BTREE |  | user_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `chat_message_reads_ibfk_1` | `message_id` | `chat_messages`.`id` |

---

### `chat_messages`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `channel_id` | int(11) | NO | MUL | NULL |  |
| `user_id` | int(11) | NO | MUL | NULL |  |
| `message` | text | NO | MUL | NULL |  |
| `message_type` | enum('text','file','image','voice','system') | YES |  | text |  |
| `parent_message_id` | int(11) | YES | MUL | NULL |  |
| `is_priority` | tinyint(1) | YES |  | 0 |  |
| `is_pinned` | tinyint(1) | YES |  | 0 |  |
| `is_edited` | tinyint(1) | YES |  | 0 |  |
| `edited_at` | timestamp | YES |  | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |
| `deleted_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_channel_id` | BTREE |  | channel_id |
| `idx_user_id` | BTREE |  | user_id |
| `idx_created_at` | BTREE |  | created_at |
| `idx_parent` | BTREE |  | parent_message_id |
| `ft_message` | FULLTEXT |  | message |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `chat_messages_ibfk_1` | `channel_id` | `chat_channels`.`id` |

---

### `chat_presence`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `user_id` | int(11) | NO | PRI | NULL |  |
| `status` | enum('online','away','busy','offline') | YES | MUL | offline |  |
| `last_seen` | timestamp | NO | MUL | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | user_id |
| `idx_status` | BTREE |  | status |
| `idx_last_seen` | BTREE |  | last_seen |

---

### `cis_queue_jobs`

**Rows:** 4 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `lane` | enum('critical','default') | NO | MUL | default |  |
| `priority` | tinyint(4) | NO |  | 5 |  |
| `status` | enum('READY','CLAIMED','RUNNING','DONE','FAILED','DLQ','CANCELLED') | NO | MUL | READY |  |
| `not_before` | datetime | YES | MUL | NULL |  |
| `contract_type` | varchar(64) | NO | MUL | NULL |  |
| `contract_version` | smallint(5) unsigned | NO |  | 1 |  |
| `idempotency_key` | varchar(96) | NO | UNI | NULL |  |
| `payload_json` | longtext | NO |  | NULL |  |
| `attempts` | smallint(5) unsigned | NO |  | 0 |  |
| `max_attempts` | smallint(5) unsigned | NO |  | 10 |  |
| `claimed_by` | varchar(64) | YES | MUL | NULL |  |
| `claimed_at` | datetime | YES |  | NULL |  |
| `last_attempt_at` | datetime | YES |  | NULL |  |
| `completed_at` | datetime | YES |  | NULL |  |
| `error_code` | varchar(64) | YES |  | NULL |  |
| `error_message` | varchar(512) | YES |  | NULL |  |
| `external_ref` | varchar(96) | YES |  | NULL |  |
| `created_at` | datetime | NO | MUL | current_timestamp() |  |
| `updated_at` | datetime | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uq_idem` | BTREE | ✓ | idempotency_key |
| `ix_status_priority` | BTREE |  | status, priority, id |
| `ix_lane_status` | BTREE |  | lane, status, not_before, id |
| `ix_not_before` | BTREE |  | not_before |
| `ix_contract` | BTREE |  | contract_type, contract_version |
| `ix_claimed` | BTREE |  | claimed_by, claimed_at |
| `ix_performance` | BTREE |  | created_at, completed_at, attempts |
| `ix_cis_queue_worker_performance` | BTREE |  | lane, status, not_before, priority, id |

---

### `cis_system_flags`

**Rows:** 6 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `flag_name` | varchar(64) | NO | PRI | NULL |  |
| `flag_value` | varchar(32) | NO |  | NULL |  |
| `updated_at` | datetime | NO | MUL | current_timestamp() | on update current_timestamp() |
| `updated_by` | varchar(64) | YES |  | NULL |  |
| `meta_json` | longtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | flag_name |
| `ix_updated` | BTREE |  | updated_at |

---

### `cis_system_switches`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `k` | varchar(64) | NO | PRI | NULL |  |
| `v` | varchar(255) | NO |  | NULL |  |
| `note` | varchar(255) | YES |  | NULL |  |
| `updated_at` | datetime | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | k |

---

### `claude_automation_config`

**Rows:** 5 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `config_key` | varchar(100) | NO | UNI | NULL |  |
| `config_value` | longtext | NO |  | NULL |  |
| `description` | text | YES |  | NULL |  |
| `is_active` | tinyint(1) | YES | MUL | 1 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `config_key` | BTREE | ✓ | config_key |
| `idx_config_key` | BTREE |  | config_key |
| `idx_is_active` | BTREE |  | is_active |

---

### `claudia_ideas`

**Rows:** 5 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `category` | enum('hr','inventory','sales','communications','system','revolutionary') | YES | MUL | NULL |  |
| `title` | varchar(200) | YES |  | NULL |  |
| `description` | text | YES |  | NULL |  |
| `implementation_difficulty` | enum('easy','medium','hard','revolutionary') | YES |  | NULL |  |
| `potential_impact` | enum('low','medium','high','game_changing') | YES |  | NULL |  |
| `status` | enum('idea','proposed','in_progress','implemented','rejected') | YES | MUL | NULL |  |
| `sass_comment` | text | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_claudia_ideas_category` | BTREE |  | category |
| `idx_claudia_ideas_status` | BTREE |  | status |

---

### `claudia_knowledge`

**Rows:** 5 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `topic` | varchar(100) | YES | MUL | NULL |  |
| `category` | varchar(50) | YES | MUL | NULL |  |
| `knowledge_text` | text | YES |  | NULL |  |
| `confidence_level` | int(11) | YES |  | 95 |  |
| `last_updated` | datetime | YES |  | current_timestamp() |  |
| `source` | varchar(100) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_claudia_knowledge_topic` | BTREE |  | topic |
| `idx_claudia_knowledge_category` | BTREE |  | category |

---

### `claudia_logs`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `timestamp` | datetime | YES | MUL | current_timestamp() |  |
| `action` | varchar(255) | NO | MUL | NULL |  |
| `details` | text | YES |  | NULL |  |
| `sass_level` | int(11) | YES |  | 8 |  |
| `user_interaction` | varchar(100) | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_claudia_logs_timestamp` | BTREE |  | timestamp |
| `idx_claudia_logs_action` | BTREE |  | action |

---

### `competitive_intelligence_sites`

**Rows:** 11 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `name` | varchar(100) | NO |  | NULL |  |
| `url` | varchar(500) | NO | UNI | NULL |  |
| `priority` | enum('high','medium','low') | YES | MUL | medium |  |
| `status` | enum('active','inactive','error') | YES | MUL | active |  |
| `last_crawled` | timestamp | YES | MUL | NULL |  |
| `success_count` | int(11) | YES |  | 0 |  |
| `error_count` | int(11) | YES |  | 0 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `url` | BTREE | ✓ | url |
| `idx_status` | BTREE |  | status |
| `idx_priority` | BTREE |  | priority |
| `idx_last_crawled` | BTREE |  | last_crawled |

---

### `competitor_profiles`

**Rows:** 3 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `competitor_id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `competitor_name` | varchar(200) | NO | UNI | NULL |  |
| `website_url` | varchar(500) | NO |  | NULL |  |
| `market_segment` | enum('direct','indirect','substitute') | YES | MUL | direct |  |
| `priority_level` | enum('low','medium','high','critical') | YES | MUL | medium |  |
| `threat_assessment` | enum('minimal','moderate','significant','critical') | YES | MUL | moderate |  |
| `focus_areas` | longtext | YES |  | NULL |  |
| `crawl_frequency` | enum('hourly','daily','weekly','monthly') | YES |  | weekly |  |
| `last_crawled` | datetime | YES | MUL | NULL |  |
| `crawl_settings` | longtext | YES |  | NULL |  |
| `data_selectors` | longtext | YES |  | NULL |  |
| `notes` | text | YES |  | NULL |  |
| `is_active` | tinyint(1) | YES | MUL | 1 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `last_updated` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | competitor_id |
| `competitor_name` | BTREE | ✓ | competitor_name |
| `idx_competitor_name` | BTREE |  | competitor_name |
| `idx_priority_level` | BTREE |  | priority_level |
| `idx_is_active` | BTREE |  | is_active |
| `idx_last_crawled` | BTREE |  | last_crawled |
| `idx_market_segment` | BTREE |  | market_segment |
| `idx_threat_assessment` | BTREE |  | threat_assessment |

---

### `config_definitions`

**Rows:** 28 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `namespace` | varchar(190) | NO | MUL | NULL |  |
| `key` | varchar(190) | NO |  | NULL |  |
| `config_key` | varchar(191) | NO |  |  |  |
| `type` | enum('string','int','bool','json','secret') | NO |  | string |  |
| `default_value` | longtext | YES |  | NULL |  |
| `description` | text | YES |  | NULL |  |
| `is_sensitive` | tinyint(1) | NO |  | 0 |  |
| `created_at` | timestamp | YES |  | current_timestamp() |  |
| `updated_at` | timestamp | YES |  | current_timestamp() | on update current_timestamp() |
| `is_mutable` | tinyint(1) | NO |  | 1 |  |
| `validate_regex` | varchar(255) | YES |  | NULL |  |
| `deprecated_at` | timestamp | YES |  | NULL |  |
| `namespace_id` | int(10) unsigned | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uq_ns_key` | BTREE | ✓ | namespace, key |
| `idx_ns` | BTREE |  | namespace |

---

### `config_items`

**Rows:** 27 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `namespace_id` | int(10) unsigned | NO |  | NULL |  |
| `namespace` | varchar(190) | NO | MUL | NULL |  |
| `key` | varchar(190) | NO |  | NULL |  |
| `config_key` | varchar(191) | NO |  | NULL |  |
| `config_value` | text | YES |  | NULL |  |
| `value` | longtext | YES |  | NULL |  |
| `type` | varchar(32) | NO |  | string |  |
| `is_sensitive` | tinyint(1) | NO |  | 0 |  |
| `version` | int(10) unsigned | NO |  | 1 |  |
| `updated_by` | varchar(190) | YES |  | NULL |  |
| `created_at` | timestamp | YES |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uq_ns_key` | BTREE | ✓ | namespace, key |
| `idx_ns` | BTREE |  | namespace |

---

### `config_namespaces`

**Rows:** 5 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `namespace` | varchar(190) | NO | UNI | NULL |  |
| `owner_group` | varchar(191) | YES |  | NULL |  |
| `owner_email` | varchar(191) | YES |  | NULL |  |
| `owner_team` | varchar(100) | YES |  | NULL |  |
| `owner_contact` | varchar(200) | YES |  | NULL |  |
| `module_type` | varchar(64) | YES |  | NULL |  |
| `description` | text | YES |  | NULL |  |
| `is_active` | tinyint(1) | NO |  | 1 |  |
| `autoload_enabled` | tinyint(1) | NO |  | 1 |  |
| `health_url` | varchar(255) | YES |  | NULL |  |
| `monitor_url` | varchar(255) | YES |  | NULL |  |
| `docs_url` | varchar(255) | YES |  | NULL |  |
| `created_at` | timestamp | YES |  | current_timestamp() |  |
| `updated_at` | timestamp | YES |  | current_timestamp() | on update current_timestamp() |
| `name` | varchar(191) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uk_ns_namespace` | BTREE | ✓ | namespace |

---

### `configuration`

**Rows:** 194 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `config_label` | varchar(100) | YES | UNI | NULL |  |
| `config_value` | longtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `config_label_UNIQUE` | BTREE | ✓ | config_label |
| `ux_configuration_label` | BTREE | ✓ | config_label |
| `idx_config_label` | BTREE |  | config_label |

---

### `containers`

**Rows:** 26 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `container_id` | int(11) | NO | PRI | NULL | auto_increment |
| `carrier_id` | int(11) | NO | MUL | NULL |  |
| `service_id` | int(11) | YES | MUL | NULL |  |
| `code` | varchar(64) | NO | MUL | NULL |  |
| `name` | varchar(150) | NO |  | NULL |  |
| `kind` | enum('bag','box','document','unknown') | NO | MUL | unknown |  |
| `length_mm` | int(11) | YES |  | NULL |  |
| `width_mm` | int(11) | YES |  | NULL |  |
| `height_mm` | int(11) | YES |  | NULL |  |
| `max_weight_grams` | int(11) | YES |  | NULL |  |
| `max_units` | int(11) | YES |  | NULL |  |
| `volume_m3` | decimal(10,6) | YES |  | NULL | STORED GENERATED |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | container_id |
| `uniq_container` | BTREE | ✓ | carrier_id, code |
| `fk_containers_service` | BTREE |  | service_id |
| `ix_containers_kind` | BTREE |  | kind |
| `ix_containers_code` | BTREE |  | code |
| `ix_containers_dims` | BTREE |  | kind, length_mm, width_mm, height_mm |
| `ix_containers_carrier_service` | BTREE |  | carrier_id, service_id |
| `ix_containers_kind_code` | BTREE |  | kind, code |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_containers_carrier` | `carrier_id` | `carriers`.`carrier_id` |
| `fk_containers_service` | `service_id` | `carrier_services`.`service_id` |

---

### `contract_uploads`

**Rows:** 116 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `user_id` | varchar(45) | NO |  | NULL |  |
| `file_name` | varchar(45) | NO |  | NULL |  |
| `date_uploaded` | timestamp | NO |  | current_timestamp() |  |
| `original_file_name` | mediumtext | NO |  | NULL |  |
| `removed` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `conversation_knowledge_capture`

**Rows:** 5 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `conversation_uuid` | varchar(36) | NO | MUL | NULL |  |
| `session_date` | date | NO | MUL | NULL |  |
| `participant_role` | enum('owner','ai_assistant','system') | NO |  | NULL |  |
| `knowledge_category` | varchar(100) | YES | MUL | NULL |  |
| `extracted_insights` | longtext | YES |  | NULL |  |
| `action_items` | longtext | YES |  | NULL |  |
| `decisions_made` | longtext | YES |  | NULL |  |
| `system_improvements` | longtext | YES |  | NULL |  |
| `priority_level` | enum('low','medium','high','critical') | YES | MUL | medium |  |
| `implementation_status` | enum('captured','planned','in_progress','completed') | YES |  | captured |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_conversation` | BTREE |  | conversation_uuid |
| `idx_date` | BTREE |  | session_date |
| `idx_category` | BTREE |  | knowledge_category |
| `idx_priority` | BTREE |  | priority_level |

---

### `conversations`

**Rows:** 118 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `conversation_id` | char(36) | NO | PRI | NULL |  |
| `title` | varchar(255) | YES | MUL | NULL |  |
| `created_at` | datetime | NO |  | current_timestamp() |  |
| `updated_at` | datetime | NO | MUL | current_timestamp() | on update current_timestamp() |
| `metadata` | longtext | YES |  | NULL |  |
| `importance_score` | decimal(3,2) | YES | MUL | 5.00 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | conversation_id |
| `idx_conv_updated` | BTREE |  | updated_at |
| `idx_conv_title` | BTREE |  | title |
| `idx_importance_score` | BTREE |  | importance_score |

---

### `courier_claims_products`

**Rows:** 5 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `claim_id` | int(11) | NO | MUL | NULL |  |
| `vend_id` | varchar(45) | YES | MUL | NULL |  |
| `website_product_order_id` | int(11) | YES |  | NULL |  |
| `website_product_id` | int(11) | YES |  | NULL |  |
| `product_qty` | int(11) | NO |  | NULL |  |
| `product_cost` | varchar(45) | NO |  | NULL |  |
| `damaged` | int(11) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `claims_idx` | BTREE |  | claim_id |
| `productID` | BTREE |  | vend_id, website_product_order_id, website_product_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `claims` | `claim_id` | `couriers_claims`.`id` |

---

### `courier_queries`

**Rows:** 11 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `time_created_at` | timestamp | NO |  | current_timestamp() |  |
| `time_deleted_at` | timestamp | YES |  | NULL |  |
| `created_by_user` | int(11) | NO |  | NULL |  |
| `order_id` | int(11) | YES |  | NULL |  |
| `shipment_id` | int(11) | YES |  | NULL |  |
| `tracking_id` | varchar(45) | YES |  | NULL |  |
| `created_at_notes` | mediumtext | YES |  | NULL |  |
| `nz_courier_query_reference` | varchar(45) | YES |  | NULL |  |
| `nz_courier_query_submitted_at` | timestamp | YES |  | NULL |  |
| `nz_courier_query_submitted_by` | varchar(45) | YES |  | NULL |  |
| `query_status` | int(11) | NO |  | 0 |  |
| `deleted_by_user` | int(11) | YES |  | NULL |  |
| `manual_created` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `couriers_claims`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `date_created` | timestamp | NO |  | current_timestamp() |  |
| `created_by` | int(11) | NO | MUL | NULL |  |
| `package_type` | int(11) | NO |  | NULL |  |
| `claim_type` | int(11) | NO |  | NULL |  |
| `order_id` | varchar(45) | YES | MUL | NULL |  |
| `website_shipment_object_json` | mediumtext | YES |  | NULL |  |
| `website_shipment_id` | int(11) | YES |  | NULL |  |
| `vend_outlet_sent_from` | varchar(45) | NO |  | NULL |  |
| `description_of_damage` | mediumtext | NO |  | NULL |  |
| `cost_to_repair` | decimal(13,5) | NO |  | 0.00000 |  |
| `tracking_number` | mediumtext | NO |  | NULL |  |
| `description_of_goods` | mediumtext | NO |  | NULL |  |
| `action_taken_to_date` | mediumtext | NO |  | NULL |  |
| `packaging_details` | mediumtext | NO |  | NULL |  |
| `number_of_packages` | int(11) | NO |  | 0 |  |
| `value_of_claim` | decimal(13,5) | NO |  | 0.00000 |  |
| `staff_notes` | mediumtext | YES |  | NULL |  |
| `status` | int(11) | NO |  | NULL |  |
| `deleted_at` | timestamp | YES |  | NULL |  |
| `deleted_by` | int(11) | YES |  | NULL |  |
| `vend_outlet_sent_to` | varchar(45) | YES |  | NULL |  |
| `website_order_object_json` | mediumtext | YES |  | NULL |  |
| `gss_shipment_object_json` | mediumtext | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `orderID` | BTREE |  | order_id |
| `staffID` | BTREE |  | created_by |

---

### `cron_alerts`

**Rows:** 5 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `alert_name` | varchar(100) | NO | UNI | NULL |  |
| `alert_type` | enum('failure_rate','duration_spike','memory_spike','missing_execution','custom') | NO |  | NULL |  |
| `task_name` | varchar(100) | YES | MUL | NULL |  |
| `condition_config` | longtext | NO |  | NULL |  |
| `is_active` | tinyint(1) | NO | MUL | 1 |  |
| `cooldown_minutes` | int(10) unsigned | NO |  | 15 |  |
| `last_triggered_at` | datetime | YES |  | NULL |  |
| `created_at` | datetime | NO |  | current_timestamp() |  |
| `updated_at` | datetime | YES |  | NULL | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_alert_name` | BTREE | ✓ | alert_name |
| `idx_active` | BTREE |  | is_active |
| `idx_task` | BTREE |  | task_name |

---

### `cron_heartbeat`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | 1 |  |
| `last_beat` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `server_hostname` | varchar(255) | YES |  | NULL |  |
| `php_version` | varchar(50) | YES |  | NULL |  |
| `memory_usage_mb` | decimal(10,2) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `cron_metrics`

**Rows:** 17,447 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `task_name` | varchar(255) | NO | MUL | NULL |  |
| `executed_at` | timestamp | NO | MUL | current_timestamp() |  |
| `duration_seconds` | decimal(10,3) | NO |  | NULL |  |
| `memory_peak_mb` | decimal(10,2) | NO |  | NULL |  |
| `cpu_peak_percent` | decimal(5,2) | YES |  | NULL |  |
| `exit_code` | int(11) | NO |  | NULL |  |
| `success` | tinyint(1) | NO | MUL | NULL |  |
| `error_message` | text | YES |  | NULL |  |
| `output_json` | text | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_task_executed` | BTREE |  | task_name, executed_at |
| `idx_executed_at` | BTREE |  | executed_at |
| `idx_success` | BTREE |  | success |
| `idx_task_name_executed_at` | BTREE |  | task_name, executed_at |
| `idx_success_executed_at` | BTREE |  | success, executed_at |

---

### `cron_notifications`

**Rows:** 12,090 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `severity` | enum('debug','info','warning','error','critical') | NO | MUL | info |  |
| `channel` | enum('slack','email','database','webhook') | NO | MUL | database |  |
| `message` | text | NO |  | NULL |  |
| `context` | longtext | YES |  | NULL |  |
| `task_name` | varchar(100) | YES | MUL | NULL |  |
| `sent_at` | datetime | YES |  | NULL |  |
| `created_at` | datetime | NO | MUL | current_timestamp() |  |
| `status` | enum('pending','sent','failed') | NO | MUL | pending |  |
| `retry_count` | tinyint(3) unsigned | NO |  | 0 |  |
| `error_message` | text | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_severity` | BTREE |  | severity |
| `idx_channel` | BTREE |  | channel |
| `idx_task_name` | BTREE |  | task_name |
| `idx_status` | BTREE |  | status |
| `idx_created_at` | BTREE |  | created_at |

---

### `cron_output_logs`

**Rows:** 353,835 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `identifier` | varchar(100) | YES |  | NULL |  |
| `time_started` | timestamp | NO |  | current_timestamp() |  |
| `time_ended` | timestamp | YES |  | NULL |  |
| `output` | mediumtext | YES |  | NULL |  |
| `command` | varchar(200) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `cron_task_history`

**Rows:** 4 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `task_name` | varchar(100) | NO | MUL | NULL |  |
| `script_path` | varchar(500) | NO |  | NULL |  |
| `executed_at` | datetime | NO | PRI | NULL |  |
| `completed_at` | datetime | YES | MUL | NULL |  |
| `duration_seconds` | decimal(10,3) | YES | MUL | NULL |  |
| `exit_code` | int(11) | YES |  | NULL |  |
| `success` | tinyint(1) | NO | MUL | 0 |  |
| `output` | longtext | YES |  | NULL |  |
| `error_output` | longtext | YES |  | NULL |  |
| `memory_peak_mb` | decimal(10,2) | YES |  | NULL |  |
| `cpu_peak_percent` | decimal(5,2) | YES |  | NULL |  |
| `hostname` | varchar(255) | YES |  | NULL |  |
| `pid` | int(10) unsigned | YES |  | NULL |  |
| `retry_attempt` | tinyint(3) unsigned | NO |  | 0 |  |
| `triggered_by` | varchar(100) | YES |  | NULL |  |
| `metadata` | longtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id, executed_at |
| `idx_task_executed` | BTREE |  | task_name, executed_at |
| `idx_success` | BTREE |  | success |
| `idx_completed_at` | BTREE |  | completed_at |
| `idx_duration` | BTREE |  | duration_seconds |

---

### `debug_data_table`

**Rows:** 20 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `name` | varchar(45) | YES |  | NULL |  |
| `value` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `delivery_options`

**Rows:** 5 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `option_code` | varchar(32) | NO | PRI | NULL |  |
| `name` | varchar(100) | NO |  | NULL |  |
| `description` | varchar(255) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | option_code |

---

### `deposit_transactions`

**Rows:** 12,915 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `transaction_name` | varchar(200) | NO |  | NULL |  |
| `transaction_amount` | varchar(200) | NO |  | NULL |  |
| `transaction_date` | datetime | NO |  | NULL |  |
| `transaction_reference` | varchar(200) | NO |  | NULL |  |
| `store_deposit` | int(11) | NO |  | 0 |  |
| `card_id` | int(11) | YES |  | NULL |  |
| `bag_number` | int(11) | YES |  | NULL |  |
| `reference` | varchar(45) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `deposit_transactions_new`

**Rows:** 37,874 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `transaction_name` | varchar(200) | NO |  | NULL |  |
| `transaction_amount` | decimal(13,2) | NO |  | NULL |  |
| `transaction_reference` | varchar(200) | NO |  | NULL |  |
| `transaction_date` | timestamp | NO |  | current_timestamp() |  |
| `store_deposit` | int(11) | NO | MUL | 0 |  |
| `card_id` | int(11) | YES |  | NULL |  |
| `bag_number` | int(11) | YES | MUL | NULL |  |
| `reference` | varchar(45) | YES | MUL | NULL |  |
| `transaction_fetched_date` | timestamp | NO |  | current_timestamp() |  |
| `original_transaction_description` | varchar(200) | YES |  | NULL |  |
| `transaction_id` | varchar(45) | YES | UNI | NULL |  |
| `json_object` | mediumtext | YES |  | NULL |  |
| `order_id` | int(11) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idReferenceUnique` | BTREE | ✓ | transaction_id |
| `bagNumberIndex` | BTREE |  | bag_number |
| `bagReferenceIndex` | BTREE |  | reference |
| `storeDepositIndex` | BTREE |  | store_deposit |

---

### `dev_ai_knowledge`

**Rows:** 4 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `platform` | varchar(100) | NO | MUL | NULL |  |
| `technology` | varchar(100) | YES | MUL | NULL |  |
| `knowledge_type` | enum('framework','database','api','integration','troubleshooting','architecture') | NO | MUL | NULL |  |
| `title` | varchar(255) | NO |  | NULL |  |
| `content` | longtext | NO |  | NULL |  |
| `code_examples` | longtext | YES |  | NULL |  |
| `related_files` | longtext | YES |  | NULL |  |
| `difficulty_level` | enum('beginner','intermediate','advanced','expert') | YES | MUL | intermediate |  |
| `last_updated` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `verified_by` | int(11) | YES |  | NULL |  |
| `confidence_score` | decimal(3,2) | YES |  | 0.85 |  |
| `is_active` | tinyint(1) | YES |  | 1 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_platform` | BTREE |  | platform |
| `idx_technology` | BTREE |  | technology |
| `idx_type` | BTREE |  | knowledge_type |
| `idx_difficulty` | BTREE |  | difficulty_level |

---

### `dev_conversations`

**Rows:** 4 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `session_id` | varchar(100) | YES | MUL | NULL |  |
| `user_input` | mediumtext | YES | MUL | NULL |  |
| `ai_response` | mediumtext | YES |  | NULL |  |
| `context_data` | longtext | YES |  | NULL |  |
| `algorithms_mentioned` | longtext | YES |  | NULL |  |
| `technologies_discussed` | longtext | YES |  | NULL |  |
| `implementation_priority` | int(11) | YES | MUL | 1 |  |
| `development_phase` | enum('concept','planning','development','testing','deployment') | YES | MUL | concept |  |
| `vector_embedding` | mediumtext | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_session` | BTREE |  | session_id |
| `idx_phase` | BTREE |  | development_phase |
| `idx_priority` | BTREE |  | implementation_priority |
| `user_input` | FULLTEXT |  | user_input, ai_response |

---

### `employee_hr_notes`

**Rows:** 61 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `date` | varchar(45) | YES |  | NULL |  |
| `time` | varchar(45) | YES |  | NULL |  |
| `created` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `by_user` | int(11) | NO |  | NULL |  |
| `subject` | mediumtext | NO |  | NULL |  |
| `note` | mediumtext | NO |  | NULL |  |
| `for_user` | int(11) | NO |  | NULL |  |
| `file_original_name` | mediumtext | YES |  | NULL |  |
| `file_stored_name` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `employee_of_month_submissions`

**Rows:** 116 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `date_submitted` | timestamp | NO |  | current_timestamp() |  |
| `staff_id` | int(11) | NO |  | NULL |  |
| `reason` | mediumtext | NO |  | NULL |  |
| `nominating_staff_id` | int(11) | YES |  | NULL |  |
| `submission_first_name` | varchar(45) | NO |  | NULL |  |
| `submission_last_name` | varchar(45) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `employee_other_documents`

**Rows:** 6 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `created` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `by_user` | int(11) | NO |  | NULL |  |
| `for_user` | int(11) | NO |  | NULL |  |
| `file_original_name` | mediumtext | NO |  | NULL |  |
| `file_stored_name` | mediumtext | NO |  | NULL |  |
| `removed` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `employee_review_employee_eval_answers`

**Rows:** 2,232 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `eval_id` | int(11) | NO |  | NULL |  |
| `question_id` | int(11) | NO |  | NULL |  |
| `answer` | mediumtext | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `employee_review_employee_evaluations`

**Rows:** 72 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `review_id` | int(11) | NO |  | NULL |  |
| `date_created` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `employee_review_manager_eval_answers`

**Rows:** 1,554 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `eval_id` | int(11) | NO |  | NULL |  |
| `question_id` | int(11) | NO |  | NULL |  |
| `answer` | mediumtext | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `employee_review_manager_evaluations`

**Rows:** 58 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `review_id` | int(11) | NO |  | NULL |  |
| `date_created` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `employee_review_question_categories`

**Rows:** 5 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `title` | varchar(45) | NO |  | NULL |  |
| `active` | int(11) | NO |  | 1 |  |
| `input_type` | varchar(45) | NO |  | ranking |  |
| `employee_only` | int(11) | YES |  | 1 |  |
| `manager_only` | int(11) | YES |  | 1 |  |
| `supervisor_only` | int(11) | YES |  | 1 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `employee_review_questions`

**Rows:** 33 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `employee_review_questions_id` | int(11) | NO | PRI | NULL | auto_increment |
| `type` | varchar(45) | NO |  | NULL |  |
| `name` | varchar(45) | NO |  | NULL |  |
| `desc` | mediumtext | NO |  | NULL |  |
| `category` | int(11) | NO |  | NULL |  |
| `active` | int(11) | NO |  | 1 |  |
| `employee_only` | int(11) | NO |  | 1 |  |
| `manager_only` | int(11) | NO |  | 0 |  |
| `supervisor_only` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | employee_review_questions_id |

---

### `employee_review_supervisor_eval_answers`

**Rows:** 4 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `eval_id` | int(11) | NO |  | NULL |  |
| `question_id` | int(11) | NO |  | NULL |  |
| `answer` | mediumtext | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `employee_review_supervisor_evaluations`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `review_id` | int(11) | NO |  | NULL |  |
| `date_created` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `employee_reviews`

**Rows:** 66 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `employee_review_id` | int(11) | NO | PRI | NULL | auto_increment |
| `staff_id` | varchar(45) | NO |  | NULL |  |
| `review_created` | timestamp | NO |  | current_timestamp() |  |
| `date_to_be_performed` | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| `review_completed_date` | timestamp | YES |  | NULL |  |
| `review_completed_by_staff` | int(11) | YES |  | NULL |  |
| `employee_to_do_review` | int(11) | NO |  | NULL |  |
| `status` | int(11) | NO |  | 0 |  |
| `supervisor_to_do_review` | int(11) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | employee_review_id |

---

### `employee_scores`

**Rows:** 186,637 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `staff_id` | int(11) | NO | MUL | NULL |  |
| `award_type` | int(11) | NO | MUL | NULL |  |
| `date_awarded` | timestamp | NO | MUL | current_timestamp() | on update current_timestamp() |
| `value` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_award_staff_date` | BTREE |  | award_type, staff_id, date_awarded |
| `idx_staff_award` | BTREE |  | staff_id, award_type |
| `idx_date_awarded` | BTREE |  | date_awarded |

---

### `employee_sign_up`

**Rows:** 101 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `first_name` | varchar(100) | NO |  | NULL |  |
| `middle_name` | varchar(100) | NO |  | NULL |  |
| `last_name` | varchar(100) | NO |  | NULL |  |
| `physical_address` | varchar(100) | NO |  | NULL |  |
| `dob` | varchar(100) | NO |  | NULL |  |
| `email` | varchar(100) | NO |  | NULL |  |
| `mobile` | varchar(100) | NO |  | NULL |  |
| `emergency_name` | varchar(100) | NO |  | NULL |  |
| `emergency_phone` | varchar(100) | NO |  | NULL |  |
| `emergency_relationship` | varchar(100) | NO |  | NULL |  |
| `ird_number` | varchar(100) | NO |  | NULL |  |
| `tax_code` | varchar(100) | NO |  | NULL |  |
| `bank_account_no` | varchar(100) | NO |  | NULL |  |
| `bank_account_name` | varchar(100) | NO |  | NULL |  |
| `staff_password` | varchar(100) | NO |  | NULL |  |
| `user_id` | int(11) | NO | PRI | NULL | auto_increment |
| `archived` | int(11) | NO |  | 0 |  |
| `deleted` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | user_id |

---

### `faulty_product_media_uploads`

**Rows:** 6,083 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `fileName` | varchar(255) | NO |  | NULL |  |
| `upload_time` | timestamp | NO |  | current_timestamp() |  |
| `fault_id` | int(11) | NO | MUL | NULL |  |
| `tempFileName` | varchar(255) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `faultID_idx` | BTREE |  | fault_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `faultID` | `fault_id` | `faulty_products`.`id` |

---

### `faulty_product_notes`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `faulty_product_id` | int(11) | NO | MUL | NULL |  |
| `supplier_id` | varchar(36) | NO | MUL | NULL |  |
| `note` | text | NO |  | NULL |  |
| `action` | varchar(50) | YES | MUL | NULL |  |
| `internal_ref` | varchar(100) | YES |  | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |
| `created_by` | varchar(100) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_faulty_product_id` | BTREE |  | faulty_product_id |
| `idx_supplier_id` | BTREE |  | supplier_id |
| `idx_created_at` | BTREE |  | created_at |
| `idx_fault_supplier` | BTREE |  | faulty_product_id, supplier_id |
| `idx_action` | BTREE |  | action |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_fpn_faulty_product_id` | `faulty_product_id` | `faulty_products`.`id` |
| `fk_fpn_supplier_id` | `supplier_id` | `vend_suppliers`.`id` |

---

### `faulty_products`

**Rows:** 3,468 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `product_id` | varchar(45) | NO |  | NULL |  |
| `serial_number` | varchar(100) | YES |  | NULL |  |
| `fault_desc` | mediumtext | NO |  | NULL |  |
| `staff_member` | varchar(45) | NO |  | NULL |  |
| `store_location` | varchar(100) | NO |  | NULL |  |
| `time_created` | timestamp | NO |  | current_timestamp() |  |
| `status` | int(11) | NO |  | 1 |  |
| `supplier_status` | int(11) | NO |  | 0 |  |
| `supplier_update_status` | int(11) | NO |  | 0 |  |
| `supplier_status_timestamp` | timestamp | YES |  | NULL |  |
| `wholesale_customer_submitted_id` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `faulty_products_store_reminders`

**Rows:** 3,241 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `store_id` | varchar(45) | NO |  | NULL |  |
| `user_id` | int(11) | NO |  | NULL |  |
| `time_created` | timestamp | NO |  | current_timestamp() |  |
| `status` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `file_tracker`

**Rows:** 3 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `file_id` | varchar(16) | NO | UNI | NULL |  |
| `neutral_id` | varchar(16) | NO | UNI | NULL |  |
| `scope` | varchar(32) | NO |  | NULL |  |
| `filename` | varchar(255) | NO |  | NULL |  |
| `filepath` | mediumtext | NO |  | NULL |  |
| `full_path` | mediumtext | NO |  | NULL |  |
| `version` | varchar(16) | YES |  | 1.0.0 |  |
| `file_size` | int(11) | YES |  | NULL |  |
| `line_count` | int(11) | YES |  | NULL |  |
| `comment_lines` | int(11) | YES |  | NULL |  |
| `hash_crc32` | varchar(16) | YES |  | NULL |  |
| `last_updated` | datetime | YES |  | current_timestamp() |  |
| `created_by` | varchar(64) | YES |  | interpreter |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `file_id` | BTREE | ✓ | file_id |
| `unique_neutral` | BTREE | ✓ | neutral_id |

---

### `flagged_products`

**Rows:** 316,866 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `product_id` | varchar(100) | NO | MUL | NULL |  |
| `outlet` | varchar(100) | NO | MUL | NULL |  |
| `reason` | varchar(100) | YES |  | NULL |  |
| `date_flagged` | timestamp | NO |  | current_timestamp() |  |
| `date_completed_stocktake` | timestamp | YES |  | NULL |  |
| `completed_by_staff` | int(11) | YES | MUL | NULL |  |
| `qty_before` | int(11) | YES |  | 0 |  |
| `qty_after` | int(11) | YES |  | NULL |  |
| `dummy_product` | int(11) | YES |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `staffCompletedByID` | BTREE |  | completed_by_staff |
| `idx_flagged_products_staff_date` | BTREE |  | completed_by_staff, date_completed_stocktake |
| `idx_flagged_products_outlet_completed` | BTREE |  | outlet, date_completed_stocktake, dummy_product |
| `idx_flagged_products_product_outlet` | BTREE |  | product_id, outlet, date_completed_stocktake |

---

### `foundation_health_reports`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `foundation_id` | varchar(50) | NO | MUL | NULL |  |
| `health_data` | longtext | NO |  | NULL |  |
| `overall_score` | decimal(5,2) | NO | MUL | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_foundation_id` | BTREE |  | foundation_id |
| `idx_overall_score` | BTREE |  | overall_score |
| `idx_created_at` | BTREE |  | created_at |

---

### `freight_rules`

**Rows:** 23 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `container_id` | int(11) | NO | PRI | NULL |  |
| `max_weight_grams` | int(11) | YES | MUL | NULL |  |
| `max_units` | int(11) | YES |  | NULL |  |
| `cost` | decimal(10,2) | NO | MUL | 0.01 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | container_id |
| `idx_fr_container_id` | BTREE |  | container_id |
| `ix_fr_cap_cost` | BTREE |  | max_weight_grams, cost |
| `ix_fr_cost` | BTREE |  | cost |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_fr_container` | `container_id` | `containers`.`container_id` |

---

### `gf_challenge_categories`

**Rows:** 10 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `category_name` | varchar(100) | NO |  | NULL |  |
| `category_code` | varchar(50) | NO | UNI | NULL |  |
| `description` | mediumtext | YES |  | NULL |  |
| `icon_class` | varchar(100) | YES |  | NULL |  |
| `color_scheme` | varchar(50) | YES |  | NULL |  |
| `is_active` | tinyint(1) | YES |  | 1 |  |
| `sort_order` | int(11) | YES |  | 0 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `category_code` | BTREE | ✓ | category_code |

---

### `gf_challenge_types`

**Rows:** 8 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `category_id` | int(11) | NO | MUL | NULL |  |
| `type_name` | varchar(100) | NO |  | NULL |  |
| `type_code` | varchar(50) | NO | UNI | NULL |  |
| `description` | mediumtext | YES |  | NULL |  |
| `difficulty_range` | longtext | YES |  | NULL |  |
| `point_range` | longtext | YES |  | NULL |  |
| `duration_range` | longtext | YES |  | NULL |  |
| `requirements` | longtext | YES |  | NULL |  |
| `is_active` | tinyint(1) | YES | MUL | 1 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `type_code` | BTREE | ✓ | type_code |
| `category_id` | BTREE |  | category_id |
| `idx_challenge_active` | BTREE |  | is_active, category_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `gf_challenge_types_ibfk_1` | `category_id` | `gf_challenge_categories`.`id` |

---

### `gf_challenges`

**Rows:** 10 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `challenge_code` | varchar(100) | NO | UNI | NULL |  |
| `type_id` | int(11) | NO | MUL | NULL |  |
| `title` | varchar(255) | NO |  | NULL |  |
| `description` | mediumtext | YES |  | NULL |  |
| `instructions` | mediumtext | YES |  | NULL |  |
| `success_criteria` | longtext | YES |  | NULL |  |
| `difficulty` | enum('beginner','easy','medium','hard','expert','legendary') | YES | MUL | medium |  |
| `points_value` | int(11) | NO |  | 0 |  |
| `bonus_points` | longtext | YES |  | NULL |  |
| `time_limit_minutes` | int(11) | YES |  | NULL |  |
| `max_attempts` | int(11) | YES |  | NULL |  |
| `is_repeatable` | tinyint(1) | YES |  | 0 |  |
| `repeat_interval_days` | int(11) | YES |  | NULL |  |
| `target_audience` | longtext | YES |  | NULL |  |
| `prerequisites` | longtext | YES |  | NULL |  |
| `resources` | longtext | YES |  | NULL |  |
| `created_by` | varchar(50) | YES |  | NULL |  |
| `approved_by` | varchar(50) | YES |  | NULL |  |
| `approval_status` | enum('draft','pending','approved','rejected') | YES |  | draft |  |
| `is_active` | tinyint(1) | YES | MUL | 1 |  |
| `start_date` | timestamp | YES | MUL | NULL |  |
| `end_date` | timestamp | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `challenge_code` | BTREE | ✓ | challenge_code |
| `type_id` | BTREE |  | type_id |
| `idx_challenge_active` | BTREE |  | is_active, approval_status |
| `idx_challenge_dates` | BTREE |  | start_date, end_date |
| `idx_challenge_difficulty` | BTREE |  | difficulty, points_value |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `gf_challenges_ibfk_1` | `type_id` | `gf_challenge_types`.`id` |

---

### `gf_collective_knowledge`

**Rows:** 6 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `knowledge_type` | enum('pattern','solution','best_practice','insight','tip','warning') | NO | MUL | NULL |  |
| `title` | varchar(255) | NO |  | NULL |  |
| `description` | mediumtext | YES |  | NULL |  |
| `knowledge_data` | longtext | YES |  | NULL |  |
| `source_type` | enum('user_behavior','challenge_completion','performance_analysis','neural_sensor','manual') | NO |  | NULL |  |
| `confidence_score` | decimal(3,2) | YES |  | 0.50 |  |
| `usage_count` | int(11) | YES | MUL | 0 |  |
| `success_rate` | decimal(5,2) | YES |  | 0.00 |  |
| `applicable_to` | longtext | YES |  | NULL |  |
| `created_from_staff_id` | varchar(50) | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `last_used` | timestamp | NO |  | 0000-00-00 00:00:00 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_knowledge_type` | BTREE |  | knowledge_type, confidence_score |
| `idx_knowledge_usage` | BTREE |  | usage_count, success_rate |

---

### `gf_leaderboards`

**Rows:** 4 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `leaderboard_name` | varchar(255) | NO |  | NULL |  |
| `leaderboard_type` | enum('points','badges','challenges','custom') | NO |  | NULL |  |
| `scope` | enum('global','outlet','department','role') | NO | MUL | NULL |  |
| `scope_value` | varchar(100) | YES |  | NULL |  |
| `time_period` | enum('daily','weekly','monthly','quarterly','yearly','all_time') | NO |  | NULL |  |
| `calculation_method` | longtext | YES |  | NULL |  |
| `display_settings` | longtext | YES |  | NULL |  |
| `is_active` | tinyint(1) | YES |  | 1 |  |
| `auto_update` | tinyint(1) | YES |  | 1 |  |
| `last_updated` | timestamp | NO |  | current_timestamp() |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_leaderboard_scope` | BTREE |  | scope, scope_value, time_period |

---

### `gf_neural_sensor_data`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) | NO | PRI | NULL | auto_increment |
| `sensor_id` | int(11) | NO | MUL | NULL |  |
| `staff_id` | varchar(50) | NO |  | NULL |  |
| `outlet_id` | int(11) | YES | MUL | NULL |  |
| `sensor_reading` | longtext | NO |  | NULL |  |
| `context_data` | longtext | YES |  | NULL |  |
| `timestamp` | timestamp | NO |  | current_timestamp() |  |
| `processed` | tinyint(1) | YES | MUL | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_sensor_data` | BTREE |  | sensor_id, staff_id, timestamp |
| `idx_sensor_processed` | BTREE |  | processed, timestamp |
| `idx_sensor_outlet` | BTREE |  | outlet_id, timestamp |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `gf_neural_sensor_data_ibfk_1` | `sensor_id` | `gf_neural_sensors`.`id` |

---

### `gf_neural_sensors`

**Rows:** 21 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `sensor_name` | varchar(255) | NO |  | NULL |  |
| `sensor_type` | enum('behavior','performance','emotion','engagement','learning','biometric') | NO | MUL | NULL |  |
| `description` | mediumtext | YES |  | NULL |  |
| `data_schema` | longtext | YES |  | NULL |  |
| `collection_frequency` | enum('real_time','hourly','daily','weekly','on_demand') | YES |  | real_time |  |
| `privacy_level` | enum('public','anonymized','restricted','confidential') | YES |  | anonymized |  |
| `is_active` | tinyint(1) | YES |  | 1 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_sensor_type` | BTREE |  | sensor_type, is_active |

---

### `gf_point_balances`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `staff_id` | varchar(50) | NO | PRI | NULL |  |
| `total_points` | int(11) | YES | MUL | 0 |  |
| `available_points` | int(11) | YES |  | 0 |  |
| `lifetime_earned` | int(11) | YES |  | 0 |  |
| `lifetime_spent` | int(11) | YES |  | 0 |  |
| `current_level` | int(11) | YES | MUL | 1 |  |
| `level_progress` | decimal(5,2) | YES |  | 0.00 |  |
| `last_activity` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | staff_id |
| `idx_total_points` | BTREE |  | total_points |
| `idx_level` | BTREE |  | current_level, level_progress |

---

### `gf_projects`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `project_name` | varchar(255) | NO |  | NULL |  |
| `project_code` | varchar(50) | NO | UNI | NULL |  |
| `description` | mediumtext | YES |  | NULL |  |
| `project_type` | enum('gamification','ai_learning','neural_sensor','hybrid') | YES |  | gamification |  |
| `status` | enum('planning','active','paused','completed','archived') | YES | MUL | planning |  |
| `priority` | enum('low','medium','high','critical') | YES |  | medium |  |
| `start_date` | date | YES | MUL | NULL |  |
| `end_date` | date | YES |  | NULL |  |
| `budget` | decimal(10,2) | YES |  | NULL |  |
| `created_by` | varchar(50) | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `project_code` | BTREE | ✓ | project_code |
| `idx_project_status` | BTREE |  | status, priority |
| `idx_project_dates` | BTREE |  | start_date, end_date |

---

### `gf_system_settings`

**Rows:** 8 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `setting_category` | varchar(100) | NO | MUL | NULL |  |
| `setting_key` | varchar(100) | NO |  | NULL |  |
| `setting_value` | longtext | YES |  | NULL |  |
| `description` | mediumtext | YES |  | NULL |  |
| `data_type` | enum('string','number','boolean','array','object') | YES |  | string |  |
| `is_public` | tinyint(1) | YES |  | 0 |  |
| `updated_by` | varchar(50) | YES |  | NULL |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `unique_category_key` | BTREE | ✓ | setting_category, setting_key |
| `idx_settings_category` | BTREE |  | setting_category, is_public |

---

### `github_agents`

**Rows:** 3 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `agent_id` | varchar(255) | NO | UNI | NULL |  |
| `agent_type` | enum('copilot_pr','copilot_review','copilot_debug','copilot_custom') | YES |  | copilot_pr |  |
| `repository_name` | varchar(255) | NO | MUL | NULL |  |
| `repository_id` | int(11) | YES |  | NULL |  |
| `pr_number` | int(11) | YES | MUL | NULL |  |
| `pr_title` | text | YES |  | NULL |  |
| `pr_url` | varchar(500) | YES |  | NULL |  |
| `branch_name` | varchar(255) | YES |  | NULL |  |
| `author_username` | varchar(255) | YES |  | NULL |  |
| `author_avatar_url` | varchar(500) | YES |  | NULL |  |
| `status` | enum('initializing','active','paused','completed','error') | YES | MUL | initializing |  |
| `health_score` | decimal(5,2) | YES |  | 100.00 |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `last_activity_at` | timestamp | YES |  | NULL |  |
| `metadata` | longtext | YES |  | NULL |  |
| `configuration` | longtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `agent_id` | BTREE | ✓ | agent_id |
| `idx_agent_id` | BTREE |  | agent_id |
| `idx_repository` | BTREE |  | repository_name |
| `idx_status` | BTREE |  | status |
| `idx_pr_number` | BTREE |  | pr_number |
| `idx_created` | BTREE |  | created_at |
| `idx_agent_repo_status` | BTREE |  | repository_name, status |

---

### `github_webhooks`

**Rows:** 3 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `webhook_id` | varchar(255) | YES |  | NULL |  |
| `event_type` | varchar(100) | NO | MUL | NULL |  |
| `action` | varchar(100) | YES |  | NULL |  |
| `repository_name` | varchar(255) | YES | MUL | NULL |  |
| `repository_id` | int(11) | YES |  | NULL |  |
| `sender_username` | varchar(255) | YES |  | NULL |  |
| `pr_number` | int(11) | YES |  | NULL |  |
| `commit_sha` | varchar(255) | YES |  | NULL |  |
| `payload` | longtext | YES |  | NULL |  |
| `headers` | longtext | YES |  | NULL |  |
| `processing_status` | enum('received','processing','processed','failed') | YES | MUL | received |  |
| `agent_triggered` | tinyint(1) | YES |  | 0 |  |
| `received_at` | timestamp | NO | MUL | current_timestamp() |  |
| `processed_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_event_type` | BTREE |  | event_type |
| `idx_repository` | BTREE |  | repository_name |
| `idx_status` | BTREE |  | processing_status |
| `idx_received` | BTREE |  | received_at |
| `idx_webhook_processing` | BTREE |  | processing_status, received_at |

---

### `gmail_invoice_scans`

**Rows:** 10 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `message_id` | varchar(255) | NO | MUL | NULL |  |
| `thread_id` | varchar(255) | YES |  | NULL |  |
| `subject` | text | YES |  | NULL |  |
| `sender` | varchar(255) | YES | MUL | NULL |  |
| `recipient` | varchar(255) | YES |  | NULL |  |
| `date_received` | timestamp | YES | MUL | NULL |  |
| `body_text` | text | YES |  | NULL |  |
| `body_html` | longtext | YES |  | NULL |  |
| `attachment_count` | int(11) | YES |  | 0 |  |
| `attachment_name` | varchar(255) | YES |  | NULL |  |
| `attachment_type` | varchar(100) | YES |  | NULL |  |
| `attachment_size` | int(11) | YES |  | NULL |  |
| `attachment_content` | longblob | YES |  | NULL |  |
| `company_mentions` | text | YES |  | NULL |  |
| `confidence_score` | int(11) | YES |  | 0 |  |
| `invoice_indicators` | text | YES |  | NULL |  |
| `processed` | tinyint(1) | YES | MUL | 0 |  |
| `pdf_processed` | tinyint(1) | YES | MUL | 0 |  |
| `extraction_confidence` | int(11) | YES |  | 0 |  |
| `extracted_data` | longtext | YES |  | NULL |  |
| `matched_transaction_id` | int(11) | YES |  | NULL |  |
| `match_confidence` | decimal(5,2) | YES |  | NULL |  |
| `match_status` | enum('pending','matched','no_match','manual_review') | YES | MUL | pending |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `processed_at` | timestamp | YES |  | NULL |  |
| `ai_confidence` | decimal(3,2) | YES |  | NULL |  |
| `last_processed` | timestamp | YES | MUL | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_message_id` | BTREE |  | message_id |
| `idx_sender` | BTREE |  | sender |
| `idx_date_received` | BTREE |  | date_received |
| `idx_processed` | BTREE |  | processed |
| `idx_pdf_processed` | BTREE |  | pdf_processed |
| `idx_match_status` | BTREE |  | match_status |
| `idx_last_processed` | BTREE |  | last_processed |

---

### `google_api`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `token` | mediumtext | NO |  | NULL |  |
| `refresh_token` | mediumtext | YES |  | NULL |  |
| `auth_code` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `google_reviews`

**Rows:** 6,055 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `reviewID` | varchar(200) | NO |  | NULL |  |
| `reviewer_photo` | varchar(500) | YES |  | NULL |  |
| `reviewer_name` | varchar(45) | YES |  | NULL |  |
| `starRating` | varchar(45) | YES |  | NULL |  |
| `comment` | varchar(2048) | YES |  | NULL |  |
| `created` | varchar(45) | YES |  | NULL |  |
| `name` | varchar(200) | YES |  | NULL |  |
| `location_id` | varchar(100) | YES |  | NULL |  |
| `scanned_by_employee_points` | int(11) | NO |  | 0 |  |
| `hide_from_website` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `gpt_memory`

**Rows:** 5 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `namespace` | varchar(100) | YES |  | NULL |  |
| `memory_key` | varchar(100) | YES |  | NULL |  |
| `memory_value` | mediumtext | YES |  | NULL |  |
| `last_updated` | datetime | YES |  | current_timestamp() | on update current_timestamp() |
| `notes` | mediumtext | YES |  | NULL |  |
| `gpt_version` | varchar(50) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `gpt_tool_policies`

**Rows:** 17 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `tool_id` | bigint(20) | NO | PRI | NULL |  |
| `rbac_roles` | longtext | YES |  | NULL |  |
| `ip_allow` | longtext | YES |  | NULL |  |
| `rate_cap_min` | int(11) | YES |  | NULL |  |
| `rate_cap_day` | int(11) | YES |  | NULL |  |
| `allow_tables` | longtext | YES |  | NULL |  |
| `jail_base` | varchar(255) | YES |  | NULL |  |
| `timeout_ms` | int(11) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | tool_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_gtp_tool` | `tool_id` | `gpt_tools`.`id` |

---

### `gpt_tool_versions`

**Rows:** 18 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) | NO | PRI | NULL | auto_increment |
| `tool_id` | bigint(20) | NO | MUL | NULL |  |
| `version` | int(11) | NO |  | NULL |  |
| `code_php` | mediumtext | NO |  | NULL |  |
| `code_sha256` | char(64) | NO |  | NULL |  |
| `meta_json` | longtext | YES |  | NULL |  |
| `published` | tinyint(1) | NO |  | 0 |  |
| `created_by` | varchar(120) | NO |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `tool_ver_unique` | BTREE | ✓ | tool_id, version |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_gtv_tool` | `tool_id` | `gpt_tools`.`id` |

---

### `gpt_tools`

**Rows:** 18 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) | NO | PRI | NULL | auto_increment |
| `name` | varchar(100) | NO | UNI | NULL |  |
| `title` | varchar(200) | NO |  | NULL |  |
| `enabled` | tinyint(1) | NO |  | 1 |  |
| `current_ver` | bigint(20) | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `name` | BTREE | ✓ | name |

---

### `harp_cas_number_database`

**Rows:** 166 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `cas_number` | varchar(45) | NO | UNI | NULL |  |
| `cid_number` | int(11) | NO |  | NULL |  |
| `label` | varchar(200) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `cas_number_UNIQUE` | BTREE | ✓ | cas_number |

---

### `harp_container_size`

**Rows:** 22 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `label` | varchar(45) | NO |  | NULL |  |
| `value` | decimal(6,1) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `harp_flavour_list`

**Rows:** 87 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `label` | varchar(200) | NO |  | NULL |  |
| `active` | int(11) | NO |  | 1 |  |
| `deleted_at` | timestamp | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `harp_msds_uploads`

**Rows:** 39 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `filename` | mediumtext | NO |  | NULL |  |
| `new_filename` | mediumtext | NO |  | NULL |  |
| `uploaded_at` | timestamp | NO |  | current_timestamp() |  |
| `uploaded_by` | int(11) | NO |  | NULL |  |
| `harp_product_id` | int(11) | NO |  | NULL |  |
| `deleted_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `harp_nicotine_strengths`

**Rows:** 25 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `label` | varchar(45) | NO |  | NULL |  |
| `value` | decimal(6,1) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `harp_notified_products`

**Rows:** 1,223 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `register_id` | varchar(200) | NO | UNI | NULL |  |
| `code` | varchar(200) | NO |  | NULL |  |
| `status` | varchar(200) | NO |  | NULL |  |
| `pgvg_ratio` | varchar(200) | YES |  | NULL |  |
| `product_brand` | varchar(200) | YES |  | NULL |  |
| `product_type` | varchar(200) | YES |  | NULL |  |
| `product_variant` | varchar(200) | YES |  | NULL |  |
| `product_upc` | varchar(200) | YES |  | NULL |  |
| `notified_date` | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| `notified_date_expired` | timestamp | NO |  | 0000-00-00 00:00:00 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uniqueIndex` | BTREE | ✓ | register_id |

---

### `harp_pgvg_ratios`

**Rows:** 8 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `vg` | int(11) | NO |  | NULL |  |
| `pg` | int(11) | NO |  | NULL |  |
| `active` | int(11) | NO |  | 1 |  |
| `label` | varchar(45) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `harp_product_amendments`

**Rows:** 271 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `harp_product_id` | int(11) | NO |  | NULL |  |
| `old_value` | mediumtext | YES |  | NULL |  |
| `new_value` | mediumtext | YES |  | NULL |  |
| `label` | mediumtext | NO |  | NULL |  |
| `created_at` | timestamp | YES |  | current_timestamp() |  |
| `other_data` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `harp_product_components`

**Rows:** 1,273 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `harp_product_id` | int(11) | NO |  | NULL |  |
| `component_name` | varchar(100) | NO |  | NULL |  |
| `quantity` | int(11) | NO |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `harp_product_flavour_list`

**Rows:** 423 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `harp_product_id` | varchar(45) | YES | UNI | NULL |  |
| `flavour_one_id` | int(11) | NO |  | NULL |  |
| `flavour_two_id` | int(11) | YES |  | NULL |  |
| `created_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `flavourProductIDIndex` | BTREE | ✓ | harp_product_id |

---

### `harp_product_ingredients`

**Rows:** 8,429 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `harp_product_id` | int(11) | NO |  | NULL |  |
| `cas_number` | varchar(100) | YES |  | NULL |  |
| `ingredient_name` | varchar(100) | NO |  | NULL |  |
| `quantity_weight_per_ml` | varchar(45) | NO |  | NULL |  |
| `proprietary` | int(11) | NO |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `harp_product_status`

**Rows:** 6 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `label` | varchar(100) | NO |  | NULL |  |
| `active` | int(11) | NO |  | 1 |  |
| `sort` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `harp_product_types`

**Rows:** 5 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `label` | varchar(45) | NO |  | NULL |  |
| `active` | int(11) | NO |  | 1 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `harp_product_variants`

**Rows:** 5,988 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `harp_product_id` | int(11) | NO | PRI | NULL |  |
| `vend_id` | varchar(45) | NO | PRI | NULL |  |
| `harp_container_size_id` | int(11) | YES |  | NULL |  |
| `harp_nicotine_strength_id` | int(11) | YES |  | NULL |  |
| `use_vend_upc` | int(11) | NO |  | 1 |  |
| `custom_upc` | varchar(100) | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | harp_product_id, vend_id |

---

### `harp_products`

**Rows:** 2,307 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `product_type_id` | int(11) | NO |  | NULL |  |
| `vend_brand_id` | varchar(45) | NO |  | NULL |  |
| `product_name_label` | varchar(100) | NO |  | NULL |  |
| `harp_pgvg_ratios_id` | int(11) | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `status` | int(11) | NO |  | NULL |  |
| `deleted_at` | timestamp | YES |  | NULL |  |
| `harp_code_id` | varchar(45) | YES | UNI | NULL |  |
| `uploaded_via_csv` | int(11) | NO |  | 0 |  |
| `internal_note` | mediumtext | YES |  | NULL |  |
| `is_disposable` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `HARPCodeUniqueIndex` | BTREE | ✓ | harp_code_id |

---

### `harp_vend_products`

**Rows:** 1,169 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `harp_id` | int(11) | NO | PRI | NULL |  |
| `vend_id` | varchar(45) | NO | PRI | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | harp_id, vend_id |

---

### `idempotency_keys`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `idem_key` | varbinary(64) | NO | UNI | NULL |  |
| `request_hash` | varbinary(64) | NO |  | NULL |  |
| `response_json` | longtext | NO |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `u_idem` | BTREE | ✓ | idem_key |

---

### `imap_mail`

**Rows:** 671 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `date` | varchar(100) | NO |  | NULL |  |
| `subject` | mediumtext | NO |  | NULL |  |
| `in_reply_to` | varchar(100) | NO |  | NULL |  |
| `message_id` | int(11) | NO |  | NULL |  |
| `to_name` | varchar(100) | NO |  | NULL |  |
| `to_email` | varchar(100) | NO |  | NULL |  |
| `from_name` | varchar(100) | NO |  | NULL |  |
| `from_email` | varchar(100) | YES |  | NULL |  |
| `reply_name` | varchar(100) | NO |  | NULL |  |
| `reply_email` | varchar(100) | NO |  | NULL |  |
| `recent` | tinyint(4) | NO |  | NULL |  |
| `unseen` | tinyint(4) | NO |  | NULL |  |
| `flagged` | tinyint(4) | NO |  | NULL |  |
| `answered` | tinyint(4) | NO |  | NULL |  |
| `deleted` | tinyint(4) | NO |  | NULL |  |
| `draft` | tinyint(4) | NO |  | NULL |  |
| `msg_no` | int(11) | NO |  | NULL |  |
| `mail_date` | varchar(45) | NO |  | NULL |  |
| `size` | int(11) | NO |  | NULL |  |
| `udate` | varchar(45) | NO |  | NULL |  |
| `object_json` | mediumtext | NO |  | NULL |  |
| `body` | mediumtext | YES |  | NULL |  |
| `html` | tinyint(4) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `incoming_order_products`

**Rows:** 10,588 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `incoming_product_primary_id` | int(11) | NO | PRI | NULL | auto_increment |
| `incoming_order_id` | int(11) | NO | MUL | NULL |  |
| `incoming_product_id` | mediumtext | YES | MUL | NULL |  |
| `incoming_qty` | int(11) | NO |  | NULL |  |
| `incoming_sku` | mediumtext | YES |  | NULL |  |
| `incoming_handle` | mediumtext | YES |  | NULL |  |
| `incoming_name` | mediumtext | YES |  | NULL |  |
| `incoming_retail_price` | decimal(13,5) | YES |  | NULL |  |
| `incoming_supply_price` | decimal(13,5) | YES |  | NULL |  |
| `incoming_new_product` | int(11) | YES |  | 0 |  |
| `incoming_usd_price` | decimal(13,5) | YES |  | NULL |  |
| `incoming_brand` | varchar(45) | YES |  | NULL |  |
| `has_updated_vend` | int(11) | NO |  | 0 |  |
| `has_created_new_products` | int(11) | NO |  | 0 |  |
| `has_updated_inventory` | int(11) | NO |  | 0 |  |
| `has_updated_cost` | int(11) | NO |  | 0 |  |
| `product_selected` | int(11) | NO |  | 0 |  |
| `delayed` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | incoming_product_primary_id |
| `IncomingOrdersKey_idx` | BTREE |  | incoming_order_id |
| `idx_product_id` | BTREE |  | incoming_product_id |
| `ix_incoming_products_prod` | BTREE |  | incoming_product_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `incomingOrders` | `incoming_order_id` | `incoming_orders`.`id` |

---

### `incoming_orders`

**Rows:** 627 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `supplier_id` | varchar(45) | NO | MUL | NULL |  |
| `order_no` | mediumtext | YES |  | NULL |  |
| `date_ordered` | timestamp | YES |  | NULL |  |
| `date_created` | timestamp | NO |  | current_timestamp() |  |
| `deleted` | int(11) | NO |  | 0 |  |
| `vend_sync_status` | int(11) | NO | MUL | 0 |  |
| `sync_date` | timestamp | YES | MUL | NULL |  |
| `file_name` | varchar(45) | YES |  | NULL |  |
| `outlet_to_stock` | varchar(45) | NO |  | NULL |  |
| `markup` | int(11) | NO |  | 0 |  |
| `customs_cost` | decimal(13,5) | NO |  | 0.00000 |  |
| `additional_costs` | decimal(13,5) | NO |  | 0.00000 |  |
| `total_order_cost_nz` | decimal(13,5) | NO |  | 0.00000 |  |
| `total_order_cost_us` | decimal(13,5) | NO |  | 0.00000 |  |
| `estimated_date_arrival` | timestamp | YES |  | NULL |  |
| `notes` | mediumtext | YES |  | NULL |  |
| `shipment_id` | int(11) | NO |  | 0 |  |
| `order_weight_in_kg` | decimal(13,5) | NO |  | 0.00000 |  |
| `order_credit_nzd` | decimal(13,5) | NO |  | 0.00000 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `id_UNIQUE` | BTREE | ✓ | id |
| `idx_sync_status` | BTREE |  | vend_sync_status, sync_date |
| `ix_incoming_orders_sync` | BTREE |  | sync_date |
| `ix_incoming_orders_sync_status` | BTREE |  | vend_sync_status |
| `ix_incoming_orders_supplier` | BTREE |  | supplier_id |

---

### `incoming_shipments`

**Rows:** 77 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `date_created` | timestamp | NO |  | current_timestamp() |  |
| `estimated_arrival` | timestamp | YES |  | NULL |  |
| `status` | int(11) | NO |  | 0 |  |
| `shipment_freight_cost_nz` | decimal(13,5) | NO |  | 0.00000 |  |
| `shipment_customs_cost_nz` | decimal(13,5) | NO |  | 0.00000 |  |
| `shipment_pricing_method` | int(11) | NO |  | 0 |  |
| `shipment_number` | varchar(200) | YES |  | NULL |  |
| `shipment_notes` | blob | YES |  | NULL |  |
| `product_pricing_method` | int(11) | NO |  | 0 |  |
| `excel_data` | longtext | YES |  | NULL |  |
| `excel_last_edited` | timestamp | YES |  | NULL |  |
| `excel_last_edited_user_id` | int(11) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `intelligent_email_analysis`

**Rows:** 5 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `message_id` | varchar(255) | NO |  | NULL |  |
| `subject` | text | NO |  | NULL |  |
| `sender` | varchar(255) | NO |  | NULL |  |
| `date_received` | datetime | NO | MUL | NULL |  |
| `body_text` | text | YES |  | NULL |  |
| `category` | varchar(100) | NO | MUL | general |  |
| `priority` | enum('critical','high','medium','low') | YES | MUL | medium |  |
| `urgency_score` | decimal(3,2) | YES | MUL | 0.00 |  |
| `business_relevance` | decimal(3,2) | YES |  | 0.00 |  |
| `gpt_analysis` | longtext | YES |  | NULL |  |
| `processing_status` | enum('pending','analyzed','actioned','archived') | YES | MUL | pending |  |
| `has_attachments` | tinyint(1) | YES |  | 0 |  |
| `attachment_count` | int(11) | YES |  | 0 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_category` | BTREE |  | category |
| `idx_priority` | BTREE |  | priority |
| `idx_urgency` | BTREE |  | urgency_score |
| `idx_date_received` | BTREE |  | date_received |
| `idx_processing_status` | BTREE |  | processing_status |

---

### `invoice_system_config`

**Rows:** 11 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `config_key` | varchar(100) | YES | UNI | NULL |  |
| `config_value` | text | YES |  | NULL |  |
| `config_type` | enum('string','integer','boolean','json','email','password') | YES |  | NULL |  |
| `description` | text | YES |  | NULL |  |
| `is_sensitive` | tinyint(1) | YES |  | 0 |  |
| `last_updated` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `updated_by` | varchar(100) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `config_key` | BTREE | ✓ | config_key |
| `idx_config_key` | BTREE |  | config_key |

---

### `juice`

**Rows:** 63 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `name` | varchar(100) | NO |  | NULL |  |
| `brand` | varchar(100) | NO |  | NULL |  |
| `active` | int(11) | NO |  | 1 |  |
| `phasing_out` | int(11) | NO |  | 0 |  |
| `thirty_ml` | varchar(45) | YES |  | NULL |  |
| `fifty_ml` | varchar(45) | YES |  | NULL |  |
| `hundred_ml` | varchar(45) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `juice_transfers_backup_20251023`

**Rows:** 3,822 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO |  | 0 |  |
| `status` | int(11) | NO |  | 0 |  |
| `outlet_from` | mediumtext | NO |  | NULL |  |
| `outlet_to` | mediumtext | NO |  | NULL |  |
| `created` | timestamp | NO |  | current_timestamp() |  |
| `packed` | timestamp | YES |  | NULL |  |
| `received` | timestamp | YES |  | NULL |  |
| `packed_by` | int(11) | YES |  | NULL |  |
| `received_by` | int(11) | YES |  | NULL |  |
| `packed_notes` | mediumtext | YES |  | NULL |  |
| `received_notes` | mediumtext | YES |  | NULL |  |
| `created_by` | int(11) | NO |  | NULL |  |
| `nicotine_in_shipment` | int(11) | NO |  | 0 |  |
| `tracking_number` | varchar(100) | YES |  | NULL |  |
| `partial_transfer_staff_member` | int(11) | YES |  | NULL |  |
| `partial_transfer_timestamp` | timestamp | YES |  | NULL |  |

---

### `juice_transfers_items`

**Rows:** 103,012 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `juice_transfer_id` | int(11) | NO |  | NULL |  |
| `product_id` | varchar(45) | NO |  | NULL |  |
| `qty_to_send` | int(11) | NO |  | NULL |  |
| `status` | int(11) | NO |  | 0 |  |
| `qty_sent` | int(11) | YES |  | NULL |  |
| `qty_received` | int(11) | YES |  | NULL |  |
| `qty_in_stock_source` | int(11) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `leave_requests`

**Rows:** 2,193 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `date_created` | timestamp | NO |  | current_timestamp() |  |
| `date_from` | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| `date_to` | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| `reason` | mediumtext | NO |  | NULL |  |
| `leaveTypeID` | varchar(45) | NO |  | NULL |  |
| `LeaveTypeName` | varchar(45) | NO |  | NULL |  |
| `status` | int(11) | NO |  | 0 |  |
| `date_decision_made` | timestamp | YES |  | NULL |  |
| `staff_id` | int(11) | NO |  | NULL |  |
| `hours_requested` | int(11) | YES |  | NULL |  |
| `leave_decided_by_user` | int(11) | YES |  | NULL |  |
| `denied_reason` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `log_types`

**Rows:** 19 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `title` | varchar(100) | NO | UNI | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uniq_title` | BTREE | ✓ | title |

---

### `logs`

**Rows:** 563,170 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `user_id` | int(10) unsigned | NO | MUL | NULL |  |
| `created` | timestamp | NO |  | current_timestamp() |  |
| `log_id_type` | int(11) | NO | MUL | NULL |  |
| `data` | mediumtext | YES |  | NULL |  |
| `data_2` | mediumtext | YES |  | NULL |  |
| `data_3` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `logIDType` | BTREE |  | log_id_type |
| `idx_logs_user_date_type` | BTREE |  | user_id, created, log_id_type |
| `idx_logs_user_created` | BTREE |  | user_id, created |
| `idx_logs_type` | BTREE |  | log_id_type |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `logs_ibfk_1` | `user_id` | `users`.`id` |

---

### `ls_id_sequences`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `seq_type` | varchar(32) | NO | PRI | NULL |  |
| `period` | varchar(10) | NO | PRI | NULL |  |
| `next_value` | bigint(20) unsigned | NO |  | 1 |  |
| `updated_at` | datetime | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | seq_type, period |

---

### `ls_job_logs`

**Rows:** 7,005 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `log_id` | char(36) | NO | PRI | NULL |  |
| `job_id` | char(36) | NO | MUL | NULL |  |
| `level` | enum('debug','info','warning','error') | NO | MUL | info |  |
| `message` | text | NO |  | NULL |  |
| `context` | longtext | YES |  | NULL |  |
| `created_at` | datetime | NO | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | log_id |
| `idx_logs_job` | BTREE |  | job_id, created_at |
| `idx_logs_level` | BTREE |  | level, created_at |
| `idx_job` | BTREE |  | job_id |
| `idx_created` | BTREE |  | created_at |
| `idx_job_logs_created` | BTREE |  | created_at |

---

### `ls_jobs`

**Rows:** 10,578 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `job_id` | char(36) | NO |  | NULL |  |
| `type` | varchar(64) | NO | MUL | NULL |  |
| `payload` | longtext | NO |  | NULL |  |
| `status` | enum('pending','running','completed','failed') | NO | MUL | pending |  |
| `locked_by` | varchar(64) | YES |  | NULL |  |
| `locked_at` | datetime | YES |  | NULL |  |
| `heartbeat_at` | datetime | YES |  | NULL |  |
| `priority` | int(11) | NO | MUL | 0 |  |
| `max_attempts` | int(11) | NO |  | 3 |  |
| `attempts` | int(11) | NO |  | 0 |  |
| `scheduled_at` | datetime | NO |  | current_timestamp() |  |
| `started_at` | datetime | YES |  | NULL |  |
| `finished_at` | datetime | YES | MUL | NULL |  |
| `completed_at` | datetime | YES |  | NULL |  |
| `created_at` | datetime | NO |  | current_timestamp() |  |
| `updated_at` | datetime | NO |  | current_timestamp() | on update current_timestamp() |
| `idempotency_key` | varchar(255) | YES | UNI | NULL |  |
| `leased_until` | datetime | YES |  | NULL |  |
| `result_meta` | longtext | YES |  | NULL |  |
| `error_meta` | longtext | YES |  | NULL |  |
| `last_error` | text | YES |  | NULL |  |
| `dlq_reason` | varchar(255) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uk_jobs_idemp` | BTREE | ✓ | idempotency_key |
| `uniq_idempotency` | BTREE | ✓ | idempotency_key |
| `idx_jobs_claim` | BTREE |  | status, type, updated_at |
| `idx_jobs_type` | BTREE |  | type, created_at |
| `idx_jobs_status` | BTREE |  | status, created_at |
| `idx_jobs_priority` | BTREE |  | priority, created_at |
| `idx_jobs_status_priority` | BTREE |  | status, priority, updated_at |
| `idx_status_type` | BTREE |  | status, type, updated_at |
| `idx_finished_at` | BTREE |  | finished_at |
| `idx_jobs_status_created` | BTREE |  | status, created_at |
| `idx_jobs_status_finished` | BTREE |  | status, finished_at |
| `idx_jobs_type_status` | BTREE |  | type, status |
| `idx_jobs_status_type_started` | BTREE |  | status, type, started_at |
| `idx_jobs_queued` | BTREE |  | status, type, locked_at, started_at |
| `idx_jobs_status_updated` | BTREE |  | status, updated_at |
| `idx_status_created` | BTREE |  | status, created_at |

---

### `ls_jobs_dlq`

**Rows:** 9 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL |  |
| `created_at` | datetime | NO | MUL | NULL |  |
| `type` | varchar(64) | NO |  | NULL |  |
| `payload` | longtext | YES |  | NULL |  |
| `idempotency_key` | varchar(128) | YES |  | NULL |  |
| `fail_code` | varchar(64) | YES |  | NULL |  |
| `fail_message` | text | YES |  | NULL |  |
| `attempts` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_created` | BTREE |  | created_at |

---

### `ls_jobs_map`

**Rows:** 7,735 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `job_id` | char(36) | NO | UNI | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uniq_job` | BTREE | ✓ | job_id |

---

### `ls_rate_limits`

**Rows:** 3 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `rl_key` | varchar(191) | NO | PRI | NULL |  |
| `window_start` | datetime | NO | MUL | NULL |  |
| `counter` | int(11) | NO |  | 0 |  |
| `updated_at` | datetime | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | rl_key |
| `idx_window` | BTREE |  | window_start |

---

### `medical_device_registration`

**Rows:** 271 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `product_id` | varchar(45) | NO |  | NULL |  |
| `date_created` | timestamp | NO |  | current_timestamp() |  |
| `first_name` | varchar(45) | NO |  | NULL |  |
| `last_name` | varchar(45) | NO |  | NULL |  |
| `address` | varchar(100) | NO |  | NULL |  |
| `email` | varchar(45) | NO |  | NULL |  |
| `phone` | varchar(45) | NO |  | NULL |  |
| `serial` | varchar(200) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `messages`

**Rows:** 211 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `message_id` | char(36) | NO | PRI | NULL |  |
| `conversation_id` | char(36) | NO | MUL | NULL |  |
| `role` | enum('system','user','assistant','tool') | NO | MUL | NULL |  |
| `content` | mediumtext | YES |  | NULL |  |
| `created_at` | datetime | NO |  | current_timestamp() |  |
| `metadata` | longtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | message_id |
| `idx_msg_conv_created` | BTREE |  | conversation_id, created_at |
| `idx_msg_role` | BTREE |  | role, created_at |
| `idx_messages_conversation_role` | BTREE |  | conversation_id, role |
| `idx_messages_conv_created` | BTREE |  | conversation_id, created_at |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_msg_conv` | `conversation_id` | `conversations`.`conversation_id` |

---

### `navigation`

**Rows:** 7 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `title` | varchar(45) | NO |  | NULL |  |
| `active` | varchar(45) | NO |  | 1 |  |
| `show_title_nav_bar` | varchar(45) | NO |  | 1 |  |
| `sort_order` | int(11) | NO |  | 1 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `negative_product_logs`

**Rows:** 45,074 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `product_id` | varchar(45) | NO |  | NULL |  |
| `outlet_id` | varchar(45) | NO |  | NULL |  |
| `qty` | varchar(45) | NO |  | NULL |  |
| `time` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `neural_ai_agents`

**Rows:** 6 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `indexed_tags` | varchar(2000) | YES | MUL |  |  |
| `agent_code` | varchar(50) | NO | UNI | NULL |  |
| `agent_name` | varchar(100) | NO |  | NULL |  |
| `agent_type` | enum('problem_solver','optimizer','specialist','coordinator','assistant') | NO | MUL | NULL |  |
| `skill_level` | int(11) | NO | MUL | 1 |  |
| `memory_access_level` | enum('project_only','cross_project_read','cross_project_full') | YES | MUL | project_only |  |
| `specialization` | mediumtext | YES |  | NULL |  |
| `status` | enum('active','training','disabled') | YES |  | active |  |
| `total_memories_created` | int(11) | YES |  | 0 |  |
| `total_solutions_contributed` | int(11) | YES |  | 0 |  |
| `avg_solution_confidence` | decimal(3,3) | YES |  | 0.000 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `agent_code` | BTREE | ✓ | agent_code |
| `idx_agent_code` | BTREE |  | agent_code |
| `idx_agent_type` | BTREE |  | agent_type |
| `idx_skill_level` | BTREE |  | skill_level |
| `idx_access_level` | BTREE |  | memory_access_level |
| `idx_neural_ai_agents_tags` | BTREE |  | indexed_tags |

---

### `neural_bot_context_snapshots`

**Rows:** 4 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `indexed_tags` | varchar(2000) | YES | MUL |  |  |
| `context_id` | varchar(100) | NO | MUL | NULL |  |
| `snapshot_type` | enum('session_start','major_milestone','error_occurred','manual_save') | YES | MUL | session_start |  |
| `context_data` | longtext | YES |  | NULL |  |
| `files_referenced` | longtext | YES |  | NULL |  |
| `code_changes` | longtext | YES |  | NULL |  |
| `timestamp` | datetime | YES |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_context_id` | BTREE |  | context_id |
| `idx_snapshot_type` | BTREE |  | snapshot_type |
| `idx_neural_bot_context_snapshots_tags` | BTREE |  | indexed_tags |

---

### `neural_bot_messages`

**Rows:** 82 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `indexed_tags` | varchar(2000) | YES | MUL |  |  |
| `session_id` | varchar(100) | NO | MUL | NULL |  |
| `context_id` | varchar(100) | NO | MUL | NULL |  |
| `message_id` | varchar(100) | NO | UNI | NULL |  |
| `message_type` | enum('user_input','bot_response','system_note','context_update') | YES | MUL | user_input |  |
| `content` | longtext | YES | MUL | NULL |  |
| `metadata` | longtext | YES |  | NULL |  |
| `timestamp` | datetime | YES | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `message_id` | BTREE | ✓ | message_id |
| `idx_session_id` | BTREE |  | session_id |
| `idx_context_id` | BTREE |  | context_id |
| `idx_message_type` | BTREE |  | message_type |
| `idx_timestamp` | BTREE |  | timestamp |
| `idx_neural_bot_messages_tags` | BTREE |  | indexed_tags |
| `content` | FULLTEXT |  | content |

---

### `neural_bot_registry`

**Rows:** 9 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `indexed_tags` | varchar(2000) | YES | MUL |  |  |
| `bot_id` | varchar(100) | NO | UNI | NULL |  |
| `bot_name` | varchar(200) | YES |  | NULL |  |
| `first_seen` | datetime | YES |  | current_timestamp() |  |
| `last_active` | datetime | YES |  | current_timestamp() |  |
| `total_interactions` | int(11) | YES |  | 0 |  |
| `metadata` | longtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `bot_id` | BTREE | ✓ | bot_id |
| `idx_bot_id` | BTREE |  | bot_id |
| `idx_neural_bot_registry_tags` | BTREE |  | indexed_tags |

---

### `neural_bot_sessions`

**Rows:** 26 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `indexed_tags` | varchar(2000) | YES | MUL |  |  |
| `session_id` | varchar(100) | NO | UNI | NULL |  |
| `bot_id` | varchar(100) | NO | MUL | NULL |  |
| `context_id` | varchar(100) | NO | MUL | NULL |  |
| `title` | varchar(500) | YES |  | NULL |  |
| `started_at` | datetime | YES |  | current_timestamp() |  |
| `last_message` | datetime | YES |  | current_timestamp() |  |
| `message_count` | int(11) | YES |  | 0 |  |
| `status` | enum('active','paused','completed','archived') | YES |  | active |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `session_id` | BTREE | ✓ | session_id |
| `idx_session_id` | BTREE |  | session_id |
| `idx_bot_id` | BTREE |  | bot_id |
| `idx_context_id` | BTREE |  | context_id |
| `idx_neural_bot_sessions_tags` | BTREE |  | indexed_tags |

---

### `neural_conversations`

**Rows:** 5 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `staff_id` | int(11) | NO | MUL | NULL |  |
| `session_id` | varchar(255) | NO |  | NULL |  |
| `conversation_type` | varchar(100) | YES | MUL | general |  |
| `user_message` | text | NO | MUL | NULL |  |
| `ai_response` | text | NO |  | NULL |  |
| `context_data` | longtext | YES |  | NULL |  |
| `metadata` | longtext | YES |  | NULL |  |
| `learning_value` | decimal(3,2) | YES |  | 0.50 |  |
| `success_score` | decimal(3,2) | YES |  | 0.50 |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_staff_session` | BTREE |  | staff_id, session_id |
| `idx_conversation_type` | BTREE |  | conversation_type |
| `idx_created_at` | BTREE |  | created_at |
| `user_message` | FULLTEXT |  | user_message, ai_response |

---

### `neural_domains`

**Rows:** 7 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `indexed_tags` | varchar(2000) | YES | MUL |  |  |
| `domain_code` | varchar(50) | NO | UNI | NULL |  |
| `domain_name` | varchar(255) | NO |  | NULL |  |
| `description` | text | YES |  | NULL |  |
| `domain_owner` | varchar(100) | YES |  | NULL |  |
| `domain_type` | enum('business','personal','technical','client','system') | NO | MUL | NULL |  |
| `base_path` | varchar(500) | YES |  | NULL |  |
| `is_active` | tinyint(1) | YES | MUL | 1 |  |
| `scanning_enabled` | tinyint(1) | YES | MUL | 1 |  |
| `scan_frequency` | enum('realtime','hourly','daily','weekly') | YES |  | daily |  |
| `last_scan_at` | timestamp | YES | MUL | NULL |  |
| `total_projects` | int(11) | YES |  | 0 |  |
| `total_files_tracked` | bigint(20) | YES |  | 0 |  |
| `total_size_bytes` | bigint(20) | YES |  | 0 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `domain_code` | BTREE | ✓ | domain_code |
| `idx_domain_code` | BTREE |  | domain_code |
| `idx_domain_type` | BTREE |  | domain_type |
| `idx_active` | BTREE |  | is_active |
| `idx_scanning` | BTREE |  | scanning_enabled |
| `idx_last_scan` | BTREE |  | last_scan_at |
| `idx_neural_domains_tags` | BTREE |  | indexed_tags |

---

### `neural_execution_logs`

**Rows:** 6 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `session_id` | varchar(100) | NO | MUL | NULL |  |
| `execution_phase` | varchar(50) | NO | MUL | NULL |  |
| `operation_type` | varchar(50) | NO | MUL | NULL |  |
| `operation_data` | longtext | YES |  | NULL |  |
| `performance_metrics` | longtext | YES |  | NULL |  |
| `memory_usage_mb` | decimal(10,2) | YES | MUL | NULL |  |
| `execution_time_ms` | decimal(10,3) | YES | MUL | NULL |  |
| `query_count` | int(11) | YES |  | 0 |  |
| `success_status` | tinyint(1) | YES |  | 1 |  |
| `error_message` | text | YES |  | NULL |  |
| `stack_trace` | text | YES |  | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `project_code` | varchar(100) | YES | MUL | newtransferv3 |  |
| `project_id` | int(11) | YES |  | 1 |  |
| `transfer_mode` | varchar(50) | YES | MUL | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_session` | BTREE |  | session_id |
| `idx_phase` | BTREE |  | execution_phase |
| `idx_operation` | BTREE |  | operation_type |
| `idx_created` | BTREE |  | created_at |
| `idx_performance` | BTREE |  | execution_time_ms, memory_usage_mb |
| `idx_performance_composite` | BTREE |  | session_id, execution_phase, operation_type |
| `idx_time_analysis` | BTREE |  | created_at, execution_time_ms |
| `idx_memory_analysis` | BTREE |  | memory_usage_mb, query_count |
| `idx_project_tracking` | BTREE |  | project_code, project_id |
| `idx_transfer_mode` | BTREE |  | transfer_mode, session_id |
| `idx_search` | FULLTEXT |  | operation_type, error_message |

---

### `neural_file_registry`

**Rows:** 353 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) | NO | PRI | NULL | auto_increment |
| `indexed_tags` | varchar(2000) | YES | MUL |  |  |
| `project_id` | int(11) | NO | MUL | NULL |  |
| `domain_id` | int(11) | NO | MUL | NULL |  |
| `file_path` | varchar(500) | NO |  | NULL |  |
| `file_name` | varchar(255) | NO |  | NULL |  |
| `file_extension` | varchar(10) | YES | MUL | NULL |  |
| `file_size_bytes` | bigint(20) | YES | MUL | 0 |  |
| `file_hash` | varchar(64) | YES |  | NULL |  |
| `file_type` | enum('code','config','documentation','data','image','other') | YES | MUL | other |  |
| `last_modified` | timestamp | YES |  | NULL |  |
| `is_tracked` | tinyint(1) | YES | MUL | 1 |  |
| `scan_priority` | enum('low','medium','high','critical') | YES | MUL | medium |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO | MUL | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `unique_file` | BTREE | ✓ | project_id, file_path |
| `domain_id` | BTREE |  | domain_id |
| `idx_extension` | BTREE |  | file_extension |
| `idx_size` | BTREE |  | file_size_bytes |
| `idx_type` | BTREE |  | file_type |
| `idx_priority` | BTREE |  | scan_priority |
| `idx_updated` | BTREE |  | updated_at |
| `idx_tracked` | BTREE |  | is_tracked |
| `idx_neural_file_registry_tags` | BTREE |  | indexed_tags |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `neural_file_registry_ibfk_1` | `project_id` | `neural_projects`.`id` |
| `neural_file_registry_ibfk_2` | `domain_id` | `neural_domains`.`id` |

---

### `neural_memory_core`

**Rows:** 353 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) | NO | PRI | NULL | auto_increment |
| `indexed_tags` | varchar(2000) | YES | MUL |  |  |
| `session_id` | varchar(64) | NO | MUL | NULL |  |
| `neural_session_id` | bigint(20) | YES | MUL | NULL |  |
| `memory_type` | enum('decision','pattern','optimization','error','solution','insight','workflow') | NO | MUL | NULL |  |
| `system_context` | varchar(100) | YES | MUL | NewTransferV3 |  |
| `project_id` | int(11) | YES | MUL | NULL |  |
| `title` | varchar(255) | NO | MUL | NULL |  |
| `memory_content` | longtext | NO |  | NULL |  |
| `summary` | text | YES |  | NULL |  |
| `tags` | longtext | YES | MUL | NULL |  |
| `parent_memory_id` | bigint(20) | YES | MUL | NULL |  |
| `confidence_score` | decimal(5,3) | YES | MUL | 0.000 |  |
| `importance_weight` | decimal(5,3) | YES |  | 0.500 |  |
| `access_count` | int(11) | YES | MUL | 0 |  |
| `success_rate` | decimal(5,3) | YES |  | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |
| `last_accessed_at` | timestamp | YES |  | NULL |  |
| `last_validated_at` | timestamp | YES |  | NULL |  |
| `expires_at` | timestamp | YES |  | NULL |  |
| `is_active` | tinyint(1) | YES | MUL | 1 |  |
| `created_by_agent` | varchar(100) | YES |  | GitHub Copilot |  |
| `validation_status` | enum('unvalidated','validated','deprecated','superseded') | YES | MUL | unvalidated |  |
| `metadata` | longtext | YES |  | NULL |  |
| `collection_version` | varchar(10) | YES | MUL | 1.0 |  |
| `intelligence_score` | decimal(5,4) | YES | MUL | 0.0000 |  |
| `learning_weight` | decimal(5,4) | YES | MUL | 0.0000 |  |
| `enhanced_metadata` | longtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_session_type` | BTREE |  | session_id, memory_type |
| `idx_system_context` | BTREE |  | system_context |
| `idx_memory_type` | BTREE |  | memory_type |
| `idx_confidence_importance` | BTREE |  | confidence_score, importance_weight |
| `idx_created_at` | BTREE |  | created_at |
| `idx_access_count` | BTREE |  | access_count |
| `idx_active_valid` | BTREE |  | is_active, validation_status |
| `idx_tags` | BTREE |  | tags |
| `idx_project_id` | BTREE |  | project_id |
| `idx_neural_session_id` | BTREE |  | neural_session_id |
| `idx_neural_memory_core_tags` | BTREE |  | indexed_tags |
| `neural_memory_core_ibfk_1` | BTREE |  | parent_memory_id |
| `idx_collection_version` | BTREE |  | collection_version |
| `idx_intelligence` | BTREE |  | intelligence_score |
| `idx_learning_weight` | BTREE |  | learning_weight |
| `idx_validation` | BTREE |  | validation_status |
| `ft_search` | FULLTEXT |  | title, summary |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_neural_memory_project` | `project_id` | `neural_projects`.`id` |
| `fk_neural_memory_session` | `neural_session_id` | `neural_sessions`.`id` |
| `neural_memory_core_ibfk_1` | `parent_memory_id` | `neural_memory_core`.`id` |

---

### `neural_performance_metrics`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `session_id` | varchar(100) | NO | MUL | NULL |  |
| `metric_category` | varchar(50) | NO | MUL | NULL |  |
| `metric_name` | varchar(100) | NO | MUL | NULL |  |
| `metric_value` | decimal(15,6) | YES | MUL | NULL |  |
| `metric_unit` | varchar(20) | YES |  | NULL |  |
| `benchmark_comparison` | decimal(10,4) | YES | MUL | NULL |  |
| `trend_analysis` | longtext | YES |  | NULL |  |
| `optimization_suggestions` | text | YES | MUL | NULL |  |
| `collection_timestamp` | timestamp(6) | NO | MUL | current_timestamp(6) |  |
| `analysis_version` | varchar(20) | YES |  | 2.0 |  |
| `project_code` | varchar(100) | YES | MUL | newtransferv3 |  |
| `project_id` | int(11) | YES |  | 1 |  |
| `transfer_mode` | varchar(50) | YES | MUL | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_session` | BTREE |  | session_id |
| `idx_category` | BTREE |  | metric_category |
| `idx_name` | BTREE |  | metric_name |
| `idx_value` | BTREE |  | metric_value |
| `idx_benchmark` | BTREE |  | benchmark_comparison |
| `idx_timestamp` | BTREE |  | collection_timestamp |
| `idx_composite` | BTREE |  | session_id, metric_category, metric_name |
| `idx_metric_analysis` | BTREE |  | metric_category, metric_name, metric_value |
| `idx_benchmark_analysis` | BTREE |  | benchmark_comparison, collection_timestamp |
| `idx_trend_analysis` | BTREE |  | session_id, metric_category, collection_timestamp |
| `idx_project_tracking` | BTREE |  | project_code, project_id |
| `idx_transfer_mode` | BTREE |  | transfer_mode, session_id |
| `idx_suggestions` | FULLTEXT |  | optimization_suggestions |

---

### `neural_projects`

**Rows:** 8 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `indexed_tags` | varchar(2000) | YES | MUL |  |  |
| `domain_id` | int(11) | NO | MUL | 2 |  |
| `project_code` | varchar(50) | NO | UNI | NULL |  |
| `project_name` | varchar(100) | NO |  | NULL |  |
| `description` | mediumtext | YES |  | NULL |  |
| `base_path` | varchar(500) | YES |  | NULL |  |
| `status` | enum('active','maintenance','archived') | YES | MUL | active |  |
| `deploy_status` | enum('deployed','pending','disabled') | YES |  | pending |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `priority_level` | enum('critical','high','medium','low') | YES | MUL | medium |  |
| `total_files` | int(11) | YES |  | 0 |  |
| `total_size_bytes` | bigint(20) | YES |  | 0 |  |
| `last_file_scan` | timestamp | YES | MUL | NULL |  |
| `scan_frequency` | enum('realtime','hourly','daily','weekly') | YES |  | daily |  |
| `tags` | longtext | YES |  | NULL |  |
| `keywords` | longtext | YES |  | NULL |  |
| `technologies` | longtext | YES |  | NULL |  |
| `dependencies` | longtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `project_code` | BTREE | ✓ | project_code |
| `idx_project_code` | BTREE |  | project_code |
| `idx_status` | BTREE |  | status, deploy_status |
| `idx_domain` | BTREE |  | domain_id |
| `idx_priority` | BTREE |  | priority_level |
| `idx_last_file_scan` | BTREE |  | last_file_scan |
| `idx_neural_projects_tags` | BTREE |  | indexed_tags |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_projects_domain` | `domain_id` | `neural_domains`.`id` |

---

### `neural_sessions`

**Rows:** 86 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) | NO | PRI | NULL | auto_increment |
| `indexed_tags` | varchar(2000) | YES | MUL |  |  |
| `session_id` | varchar(64) | NO | UNI | NULL |  |
| `project_id` | int(11) | YES | MUL | NULL |  |
| `session_type` | varchar(50) | YES |  | neural_memory |  |
| `status` | enum('active','completed','archived') | YES | MUL | active |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `session_id` | BTREE | ✓ | session_id |
| `idx_session_id` | BTREE |  | session_id |
| `idx_project_id` | BTREE |  | project_id |
| `idx_status` | BTREE |  | status |
| `idx_neural_sessions_tags` | BTREE |  | indexed_tags |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `neural_sessions_ibfk_1` | `project_id` | `neural_projects`.`id` |

---

### `neural_system_events`

**Rows:** 9 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) | NO | PRI | NULL | auto_increment |
| `indexed_tags` | varchar(2000) | YES | MUL |  |  |
| `project_id` | int(11) | YES | MUL | NULL |  |
| `ai_agent_id` | int(11) | YES | MUL | NULL |  |
| `session_id` | varchar(64) | YES | MUL | NULL |  |
| `event_type` | varchar(100) | NO |  | NULL |  |
| `event_description` | mediumtext | YES |  | NULL |  |
| `event_data` | longtext | YES |  | NULL |  |
| `severity` | enum('info','warning','error') | YES |  | info |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_project_event` | BTREE |  | project_id, event_type |
| `idx_session` | BTREE |  | session_id |
| `idx_agent` | BTREE |  | ai_agent_id |
| `idx_created_at` | BTREE |  | created_at |
| `idx_neural_system_events_tags` | BTREE |  | indexed_tags |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `neural_system_events_ibfk_1` | `project_id` | `neural_projects`.`id` |
| `neural_system_events_ibfk_2` | `ai_agent_id` | `neural_ai_agents`.`id` |

---

### `nicotine_checks`

**Rows:** 6,376 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `outlet_id` | varchar(100) | NO |  | NULL |  |
| `date_checked` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `ml` | int(11) | NO |  | NULL |  |
| `confirmed_by_user` | int(11) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `notification_alert_rules`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `rule_name` | varchar(100) | NO |  | NULL |  |
| `trigger_type` | varchar(50) | NO | MUL | NULL |  |
| `conditions` | longtext | NO |  | NULL |  |
| `actions` | longtext | NO |  | NULL |  |
| `enabled` | tinyint(1) | NO |  | 1 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `created_by` | int(11) | NO | MUL | NULL |  |
| `last_triggered` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_trigger_enabled` | BTREE |  | trigger_type, enabled |
| `idx_created_by` | BTREE |  | created_by |

---

### `notification_computers`

**Rows:** 26 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `computer_name` | mediumtext | YES |  | NULL |  |
| `ip` | mediumtext | YES |  | NULL |  |
| `last_pinged` | timestamp | YES |  | NULL |  |
| `outlet_id` | mediumtext | YES |  | NULL |  |
| `web_order_notification` | int(11) | NO |  | 1 |  |
| `take_screenshots` | int(11) | YES |  | 0 |  |
| `screenshot_seconds` | int(11) | YES |  | 30 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `notification_messages`

**Rows:** 65,700 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `message` | mediumtext | NO |  | NULL |  |
| `created` | timestamp | NO | MUL | current_timestamp() | on update current_timestamp() |
| `link` | mediumtext | YES |  | NULL |  |
| `specific_store_id` | mediumtext | YES |  | NULL |  |
| `specific_computer_id` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `messageCreated` | BTREE |  | created |

---

### `notification_preferences`

**Rows:** 7 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `user_id` | varchar(50) | YES | MUL | pearce |  |
| `notification_type` | varchar(50) | NO | MUL | NULL |  |
| `enabled` | tinyint(1) | YES |  | 1 |  |
| `frequency` | enum('realtime','hourly','daily','disabled') | YES |  | realtime |  |
| `channels` | longtext | YES |  | NULL |  |
| `quiet_hours_start` | time | YES |  | 22:00:00 |  |
| `quiet_hours_end` | time | YES |  | 07:00:00 |  |
| `weekend_notifications` | tinyint(1) | YES |  | 0 |  |
| `urgency_threshold` | int(11) | YES |  | 70 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `unique_user_type` | BTREE | ✓ | user_id, notification_type |
| `idx_user` | BTREE |  | user_id |
| `idx_type` | BTREE |  | notification_type |

---

### `notification_read`

**Rows:** 1,159,840 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `message_id` | int(11) | NO | MUL | NULL |  |
| `computer_id` | int(11) | NO | MUL | NULL |  |
| `message_read` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `MessageID` | BTREE |  | message_id |
| `ComputerID` | BTREE |  | computer_id |

---

### `nz_vape_websites`

**Rows:** 10 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `domain` | varchar(100) | NO | UNI | NULL |  |
| `website_name` | varchar(200) | NO |  | NULL |  |
| `site_type` | enum('retailer','wholesale','manufacturer','distributor') | YES |  | retailer |  |
| `market_tier` | enum('premium','mid_market','budget','specialty') | YES |  | mid_market |  |
| `crawling_enabled` | tinyint(1) | YES | MUL | 1 |  |
| `crawl_priority` | int(11) | YES |  | 50 |  |
| `stealth_level` | enum('low','medium','high','ultra') | YES |  | ultra |  |
| `crawl_delay_seconds` | int(11) | YES |  | 45 |  |
| `product_list_urls` | longtext | YES |  | NULL |  |
| `search_url_template` | varchar(500) | YES |  | NULL |  |
| `pagination_pattern` | varchar(200) | YES |  | NULL |  |
| `requires_javascript` | tinyint(1) | YES |  | 0 |  |
| `has_bot_protection` | tinyint(1) | YES |  | 0 |  |
| `login_required` | tinyint(1) | YES |  | 0 |  |
| `cloudflare_protected` | tinyint(1) | YES |  | 0 |  |
| `avg_response_time_ms` | int(11) | YES |  | 0 |  |
| `success_rate_percent` | decimal(5,2) | YES |  | 100.00 |  |
| `last_successful_crawl` | datetime | YES | MUL | NULL |  |
| `last_failed_crawl` | datetime | YES |  | NULL |  |
| `consecutive_failures` | int(11) | YES |  | 0 |  |
| `product_count_estimate` | int(11) | YES |  | 0 |  |
| `pricing_accuracy` | enum('low','medium','high') | YES |  | medium |  |
| `stock_info_available` | tinyint(1) | YES |  | 0 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `domain` | BTREE | ✓ | domain |
| `idx_crawling_enabled` | BTREE |  | crawling_enabled, crawl_priority |
| `idx_domain` | BTREE |  | domain |
| `idx_last_crawl` | BTREE |  | last_successful_crawl |

---

### `pack_rules`

**Rows:** 41 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `scope` | enum('product','category','brand','supplier') | NO | PRI | NULL |  |
| `scope_id` | varchar(100) | NO | PRI | NULL |  |
| `pack_size` | int(11) | YES |  | NULL |  |
| `outer_multiple` | int(11) | YES |  | NULL |  |
| `enforce_outer` | tinyint(1) | NO |  | 0 |  |
| `rounding_mode` | enum('floor','ceil','round') | NO |  | round |  |
| `source` | enum('human','vendor','gpt','inferred') | NO |  | human |  |
| `confidence` | decimal(4,2) | NO |  | 1.00 |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `must_outer_pack` | tinyint(1) | NO |  | 0 |  |
| `min_outer_multiple` | int(11) | YES |  | NULL |  |
| `max_units_per_pack` | int(11) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | scope, scope_id |
| `ix_pack_rules_scope` | BTREE |  | scope, scope_id |
| `ix_pack_rules_scope_scopeid` | BTREE |  | scope, scope_id |

---

### `payroll_audit_log`

**Rows:** 1,252 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |
| `level` | varchar(20) | NO | MUL | info |  |
| `message` | text | NO |  | NULL |  |
| `ctx_json` | longtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `created_at_idx` | BTREE |  | created_at |
| `level_idx` | BTREE |  | level |

---

### `performance_tests`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `test_id` | varchar(255) | NO | UNI | NULL |  |
| `performance_score` | int(11) | NO |  | NULL |  |
| `performance_grade` | char(1) | NO |  | NULL |  |
| `duration_seconds` | decimal(10,2) | NO |  | NULL |  |
| `results` | longtext | NO |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `test_id` | BTREE | ✓ | test_id |

---

### `permissions`

**Rows:** 55 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `name` | varchar(100) | YES |  | NULL |  |
| `show_in_sidemenu` | int(11) | NO |  | 1 |  |
| `filename` | varchar(100) | NO |  | NULL |  |
| `navigation_id` | int(11) | NO | MUL | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `navID_idx` | BTREE |  | navigation_id |

---

### `petty_cash_expenses`

**Rows:** 446 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `outlet_id` | varchar(45) | NO | MUL | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `total_cash` | decimal(10,2) | NO |  | NULL |  |
| `xero_invoice_id` | varchar(45) | NO |  | NULL |  |
| `deleted_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `outletIDIndex` | BTREE |  | outlet_id |

---

### `petty_cash_receipts_upload`

**Rows:** 513 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `amount` | decimal(10,2) | NO |  | NULL |  |
| `notes` | mediumtext | YES |  | NULL |  |
| `uploaded_at` | timestamp | NO |  | current_timestamp() |  |
| `uploaded_by` | int(11) | NO |  | NULL |  |
| `assigned_to_expense` | varchar(45) | YES | MUL | NULL |  |
| `deleted_at` | timestamp | YES |  | NULL |  |
| `filename` | varchar(100) | NO |  | NULL |  |
| `original_filename` | mediumtext | NO |  | NULL |  |
| `outlet_id` | varchar(45) | NO | MUL | NULL |  |
| `assigned_to_expense_timestamp` | timestamp | YES |  | NULL |  |
| `assigned_by` | int(11) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `assignedExpenseIndex` | BTREE |  | assigned_to_expense |
| `outletIDIndex` | BTREE |  | outlet_id |

---

### `po_receiving_sim_lines`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `sim_id` | int(11) | NO | MUL | NULL |  |
| `product_id` | varchar(64) | NO |  | NULL |  |
| `expected_qty` | int(11) | NO |  | 0 |  |
| `received_qty` | int(11) | NO |  | 0 |  |
| `flags_json` | longtext | YES |  | NULL |  |
| `line_note` | text | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_sim` | BTREE |  | sim_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_sim_header` | `sim_id` | `po_receiving_sim_sessions`.`sim_id` |

---

### `po_receiving_sim_sessions`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `sim_id` | int(11) | NO | PRI | NULL | auto_increment |
| `purchase_order_id` | int(11) | NO | MUL | NULL |  |
| `user_id` | int(11) | NO | MUL | NULL |  |
| `notes` | text | YES |  | NULL |  |
| `quality` | enum('poor','fair','good','excellent') | YES |  | NULL |  |
| `items_count` | int(11) | NO |  | 0 |  |
| `is_partial` | tinyint(1) | NO |  | 0 |  |
| `created_at` | datetime | YES | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | sim_id |
| `idx_po` | BTREE |  | purchase_order_id |
| `idx_user` | BTREE |  | user_id |
| `idx_created` | BTREE |  | created_at |

---

### `post_codes`

**Rows:** 50,994 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `post_code` | int(11) | YES |  | NULL |  |
| `city` | varchar(100) | YES | MUL | NULL |  |
| `suburb` | varchar(100) | YES |  | NULL |  |
| `territory` | varchar(100) | YES |  | NULL |  |
| `region` | varchar(100) | YES |  | NULL |  |
| `island` | varchar(100) | YES |  | NULL |  |
| `street_name` | varchar(100) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `mainIndex` | BTREE |  | city, territory, region, island |

---

### `pricing_rules`

**Rows:** 11 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `rule_id` | int(11) | NO | PRI | NULL | auto_increment |
| `carrier_id` | int(11) | NO | MUL | NULL |  |
| `service_id` | int(11) | YES | MUL | NULL |  |
| `container_id` | int(11) | YES | MUL | NULL |  |
| `price` | decimal(10,2) | NO |  | NULL |  |
| `currency` | char(3) | NO |  | NZD |  |
| `effective_from` | date | YES |  | NULL |  |
| `effective_to` | date | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | rule_id |
| `ix_pr_carrier_service_container` | BTREE |  | carrier_id, service_id, container_id |
| `fk_pr_service` | BTREE |  | service_id |
| `fk_pr_container` | BTREE |  | container_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_pr_carrier` | `carrier_id` | `carriers`.`carrier_id` |
| `fk_pr_container` | `container_id` | `containers`.`container_id` |
| `fk_pr_service` | `service_id` | `carrier_services`.`service_id` |
| `fk_pricing_carrier` | `carrier_id` | `carriers`.`carrier_id` |
| `fk_pricing_container` | `container_id` | `containers`.`container_id` |
| `fk_pricing_service` | `service_id` | `carrier_services`.`service_id` |

---

### `product_categorization_data`

**Rows:** 1,320 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `product_id` | varchar(100) | NO | UNI | NULL |  |
| `lightspeed_category_id` | varchar(255) | YES | MUL | NULL |  |
| `category_id` | varchar(50) | NO | MUL | NULL |  |
| `category_code` | varchar(50) | YES |  | NULL |  |
| `pack_quantity` | int(11) | YES |  | NULL |  |
| `outer_packaging` | int(11) | YES |  | NULL |  |
| `categorization_confidence` | decimal(3,2) | YES |  | NULL |  |
| `categorization_method` | varchar(50) | YES |  | NULL |  |
| `categorization_reasoning` | text | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `unique_product` | BTREE | ✓ | product_id |
| `ix_product_categorization_prod` | BTREE |  | product_id |
| `ix_pcd_category` | BTREE |  | category_id |
| `ix_pcd_vendor_lightspeed` | BTREE |  | lightspeed_category_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_pcd_cat` | `category_id` | `categories`.`id` |
| `fk_pcd_product` | `product_id` | `vend_products`.`id` |

---

### `product_categorization_queue`

**Rows:** 7 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) | NO | PRI | NULL | auto_increment |
| `product_id` | varchar(255) | NO | UNI | NULL |  |
| `product_name` | varchar(500) | YES |  | NULL |  |
| `status` | enum('queued','processing','done','failed') | NO | MUL | queued |  |
| `attempt_count` | int(11) | NO |  | 0 |  |
| `last_error` | text | YES |  | NULL |  |
| `requested_by` | varchar(100) | YES |  | NULL |  |
| `locked_at` | datetime | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uq_pcq_product` | BTREE | ✓ | product_id |
| `idx_pcq_status` | BTREE |  | status |

---

### `product_category_dimensions`

**Rows:** 8 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `category_id` | char(36) | NO | UNI | NULL |  |
| `category_name` | varchar(255) | YES | MUL | NULL |  |
| `typical_length_mm` | int(10) unsigned | YES |  | 0 |  |
| `typical_width_mm` | int(10) unsigned | YES |  | 0 |  |
| `typical_height_mm` | int(10) unsigned | YES |  | 0 |  |
| `typical_weight_g` | int(10) unsigned | YES |  | 0 |  |
| `min_weight_g` | int(10) unsigned | YES |  | 0 |  |
| `max_weight_g` | int(10) unsigned | YES |  | 0 |  |
| `density_g_per_cm3` | decimal(6,3) | YES |  | 0.500 |  |
| `fragile` | tinyint(1) | YES |  | 0 |  |
| `stackable` | tinyint(1) | YES |  | 1 |  |
| `pack_efficiency` | decimal(4,2) | YES |  | 0.70 |  |
| `notes` | text | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_category` | BTREE | ✓ | category_id |
| `idx_category_name` | BTREE |  | category_name |

---

### `product_classification`

**Rows:** 8,254 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `product_id` | varchar(255) | NO | PRI | NULL |  |
| `product_type_code` | varchar(50) | NO |  | unknown |  |
| `category_id` | varchar(50) | NO | MUL | NULL |  |
| `confidence` | decimal(4,2) | NO |  | 0.00 |  |
| `method` | enum('unknown','lightspeed','vend_map','keyword','ai','human','rule') | NO |  | unknown |  |
| `reasoning` | text | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | product_id |
| `ux_product_classification_prod` | BTREE | ✓ | product_id |
| `ix_product_classification_cat` | BTREE |  | category_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_pc_category` | `category_id` | `categories`.`id` |

---

### `product_classification_unified`

**Rows:** 8,648 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `product_id` | varchar(100) | NO | PRI | NULL |  |
| `product_type_code` | varchar(50) | NO |  | NULL |  |
| `category_id` | varchar(50) | NO | MUL | NULL |  |
| `category_code` | varchar(100) | YES |  | NULL |  |
| `external_source_id` | varchar(255) | YES |  | NULL |  |
| `confidence` | decimal(4,2) | YES |  | 0.00 |  |
| `method` | varchar(50) | YES |  | unknown |  |
| `reasoning` | text | YES |  | NULL |  |
| `created_at` | timestamp | YES |  | NULL |  |
| `updated_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | product_id |
| `ix_pcu_category` | BTREE |  | category_id |
| `ix_pcu_prod_cat` | BTREE |  | product_id, category_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_pcu_category` | `category_id` | `categories`.`id` |
| `fk_pcu_product` | `product_id` | `vend_products`.`id` |

---

### `product_dimensions`

**Rows:** 8,835 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `product_id` | char(36) | NO | UNI | NULL |  |
| `sku` | varchar(255) | YES | MUL | NULL |  |
| `length_mm` | int(10) unsigned | YES |  | 0 |  |
| `width_mm` | int(10) unsigned | YES |  | 0 |  |
| `height_mm` | int(10) unsigned | YES |  | 0 |  |
| `weight_g` | int(10) unsigned | YES | MUL | 0 |  |
| `volume_cm3` | int(10) unsigned | YES | MUL | NULL | STORED GENERATED |
| `fragile` | tinyint(1) | YES |  | 0 |  |
| `stackable` | tinyint(1) | YES |  | 1 |  |
| `must_outer_pack` | tinyint(1) | YES |  | 0 |  |
| `pack_quantity` | int(10) unsigned | YES |  | 1 |  |
| `outer_multiple` | int(10) unsigned | YES |  | 1 |  |
| `data_source` | enum('measured','supplier','estimated','category_default','manual') | YES | MUL | estimated |  |
| `confidence` | enum('high','medium','low') | YES | MUL | medium |  |
| `measured_by` | varchar(100) | YES |  | NULL |  |
| `measured_at` | datetime | YES |  | NULL |  |
| `shipping_notes` | text | YES |  | NULL |  |
| `hazmat` | tinyint(1) | YES |  | 0 |  |
| `requires_signature` | tinyint(1) | YES |  | 0 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_product` | BTREE | ✓ | product_id |
| `idx_sku` | BTREE |  | sku |
| `idx_data_source` | BTREE |  | data_source |
| `idx_confidence` | BTREE |  | confidence |
| `idx_volume` | BTREE |  | volume_cm3 |
| `idx_weight` | BTREE |  | weight_g |

---

### `product_types`

**Rows:** 8 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `code` | varchar(50) | NO | UNI | NULL |  |
| `label` | varchar(100) | YES |  | NULL |  |
| `description` | mediumtext | YES |  | NULL |  |
| `active` | tinyint(1) | YES |  | 1 |  |
| `default_seed_qty` | int(10) unsigned | NO |  | 3 |  |
| `avg_weight_grams` | int(11) | YES |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `code` | BTREE | ✓ | code |

---

### `projects`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `name` | varchar(255) | NO |  | NULL |  |
| `slug` | varchar(100) | NO | UNI | NULL |  |
| `description` | text | YES |  | NULL |  |
| `template_contract` | varchar(64) | NO | MUL | CIS_TEMPLATE |  |
| `template_version` | varchar(16) | NO |  | 1.0.0 |  |
| `status` | enum('active','archived','suspended') | YES | MUL | active |  |
| `created_by_user` | varchar(100) | NO | MUL | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `slug` | BTREE | ✓ | slug |
| `idx_slug` | BTREE |  | slug |
| `idx_status` | BTREE |  | status |
| `idx_created_by` | BTREE |  | created_by_user |
| `idx_template` | BTREE |  | template_contract, template_version |

---

### `proposed_transactions`

**Rows:** 91 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `StatementLineID` | varchar(100) | YES | UNI | NULL |  |
| `InvoiceDate` | date | YES | MUL | NULL |  |
| `ContactName` | varchar(255) | YES |  | NULL |  |
| `Reference` | varchar(255) | YES |  | NULL |  |
| `Description` | varchar(255) | YES |  | NULL |  |
| `bankAccountID` | varchar(100) | YES |  | NULL |  |
| `ledgerAccountID` | varchar(100) | YES |  | NULL |  |
| `GSTCode` | varchar(50) | YES |  | NULL |  |
| `Spent` | decimal(10,2) | YES |  | NULL |  |
| `Received` | decimal(10,2) | YES |  | NULL |  |
| `confidence_score` | float | YES | MUL | NULL |  |
| `categorized_by` | varchar(50) | YES |  | ai |  |
| `reviewed` | tinyint(1) | YES | MUL | 0 |  |
| `approved` | tinyint(1) | YES |  | 0 |  |
| `review_date` | timestamp | YES |  | NULL |  |
| `xero_submitted` | tinyint(1) | YES | MUL | 0 |  |
| `submission_date` | timestamp | YES | MUL | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `comment` | varchar(255) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `unique_transaction` | HASH | ✓ | InvoiceDate, ContactName, Description, Reference, Spent, bankAccountID |
| `unique_statement_line` | BTREE | ✓ | StatementLineID |
| `idx_reviewed_approved` | BTREE |  | reviewed, approved |
| `idx_xero_submitted` | BTREE |  | xero_submitted |
| `idx_submission_date` | BTREE |  | submission_date |
| `idx_proposed_transactions_workflow` | BTREE |  | reviewed, approved, xero_submitted |
| `idx_proposed_transactions_confidence_date` | BTREE |  | confidence_score, created_at |

---

### `purchase_order_line_items`

**Rows:** 254,404 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `product_id` | varchar(100) | NO | PRI | NULL |  |
| `substitution_product_id` | varchar(100) | YES | MUL | NULL |  |
| `purchase_order_id` | int(11) | NO | PRI | NULL |  |
| `order_qty` | int(11) | NO |  | NULL |  |
| `slip_qty` | int(11) | YES |  | NULL |  |
| `order_purchase_price` | decimal(10,4) | NO |  | NULL |  |
| `qty_in_stock_before` | int(11) | YES |  | NULL |  |
| `qty_arrived` | int(11) | YES |  | NULL |  |
| `damaged_qty` | int(11) | NO |  | 0 |  |
| `discrepancy_type` | enum('OK','MISSING','SENT_LOW','SENT_HIGH','SUBSTITUTED','DAMAGED','UNORDERED') | NO |  | OK |  |
| `unit_cost_ex_gst` | decimal(10,4) | YES |  | NULL |  |
| `line_note` | varchar(255) | YES |  | NULL |  |
| `received_by` | varchar(45) | YES | MUL | NULL |  |
| `received_at` | timestamp | YES |  | NULL |  |
| `barcode_scanned` | tinyint(1) | NO | MUL | 0 |  |
| `photo_evidence_count` | int(11) | NO | MUL | 0 |  |
| `receiving_notes` | mediumtext | YES |  | NULL |  |
| `added_product` | int(11) | NO |  | 0 |  |
| `unexpected_product` | tinyint(1) | NO | MUL | 0 |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `deleted_by` | int(11) | YES |  | NULL |  |
| `deleted_at` | timestamp | YES |  | NULL |  |
| `qty_ordered` | int(11) | YES |  | NULL | VIRTUAL GENERATED |
| `qty_received` | int(11) | YES |  | NULL | VIRTUAL GENERATED |
| `has_damage` | tinyint(1) | YES |  | NULL |  |
| `is_substitute` | tinyint(1) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | product_id, purchase_order_id |
| `purchaseOrderID_idx` | BTREE |  | purchase_order_id |
| `ix_poli_product` | BTREE |  | product_id |
| `idx_poli_unexpected` | BTREE |  | unexpected_product |
| `ix_poli_substitute` | BTREE |  | substitution_product_id |
| `idx_poli_received` | BTREE |  | received_by, received_at |
| `idx_poli_barcode` | BTREE |  | barcode_scanned |
| `idx_poli_photos` | BTREE |  | photo_evidence_count |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `purchaseOrderID` | `purchase_order_id` | `purchase_orders`.`purchase_order_id` |

---

### `purchase_orders`

**Rows:** 11,170 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `purchase_order_id` | int(11) | NO | PRI | NULL | auto_increment |
| `vend_consignment_id` | varchar(100) | YES | MUL | NULL |  |
| `consignment_id` | bigint(20) unsigned | YES | MUL | NULL |  |
| `outlet_id` | varchar(100) | NO | PRI | NULL |  |
| `supplier_id` | varchar(100) | NO | PRI | NULL |  |
| `supplier_name_cache` | varchar(150) | YES |  | NULL |  |
| `date_created` | timestamp | NO | MUL | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `deleted_by` | int(11) | YES |  | NULL |  |
| `deleted_at` | timestamp | YES |  | NULL |  |
| `status` | int(11) | YES | MUL | 0 |  |
| `created_by` | varchar(45) | NO |  | NULL |  |
| `completed_by` | varchar(100) | YES |  | NULL |  |
| `completed_timestamp` | timestamp | YES |  | NULL |  |
| `completed_notes` | mediumtext | YES |  | NULL |  |
| `receiving_started_by` | varchar(45) | YES | MUL | NULL |  |
| `receiving_started_at` | timestamp | YES |  | NULL |  |
| `mobile_session_token` | varchar(96) | YES | MUL | NULL |  |
| `receiving_device_info` | mediumtext | YES |  | NULL |  |
| `receive_summary_json` | longtext | YES |  | NULL |  |
| `api_id` | varchar(45) | YES | MUL | NULL |  |
| `partial_delivery` | int(11) | NO | MUL | 0 |  |
| `partial_delivery_time` | timestamp | YES |  | NULL |  |
| `partial_delivery_by` | varchar(45) | YES |  | NULL |  |
| `packing_slip_no` | varchar(80) | YES |  | NULL |  |
| `invoice_no` | varchar(80) | YES |  | NULL |  |
| `no_packing_slip` | tinyint(1) | NO |  | 0 |  |
| `totals_mode` | enum('EX_GST','INC_GST') | YES |  | NULL |  |
| `subtotal_ex_gst` | decimal(12,4) | YES |  | NULL |  |
| `gst` | decimal(12,4) | YES |  | NULL |  |
| `total_inc_gst` | decimal(12,4) | YES |  | NULL |  |
| `id` | int(11) | YES |  | NULL | VIRTUAL GENERATED |
| `created_at` | datetime | YES |  | NULL | VIRTUAL GENERATED |
| `completed_at` | datetime | YES |  | NULL | VIRTUAL GENERATED |
| `last_received_by` | int(11) | YES |  | NULL |  |
| `last_received_at` | datetime | YES |  | NULL |  |
| `unlocked_by` | int(11) | YES |  | NULL |  |
| `unlocked_at` | datetime | YES |  | NULL |  |
| `receiving_notes` | mediumtext | YES |  | NULL |  |
| `receiving_quality` | enum('poor','fair','good','excellent') | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | purchase_order_id, outlet_id, supplier_id |
| `id_UNIQUE` | BTREE | ✓ | purchase_order_id |
| `ix_po_supplier_status_completed` | BTREE |  | supplier_id, status, completed_timestamp |
| `ix_po_outlet_status_created` | BTREE |  | outlet_id, status, date_created |
| `idx_po_status_created` | BTREE |  | status, date_created |
| `idx_po_api` | BTREE |  | api_id |
| `ix_po_status` | BTREE |  | status |
| `ix_po_date_created` | BTREE |  | date_created |
| `ix_po_partial` | BTREE |  | partial_delivery |
| `idx_po_receiving_started` | BTREE |  | receiving_started_by, receiving_started_at |
| `idx_po_mobile_token` | BTREE |  | mobile_session_token |
| `idx_vend_consignment_id` | BTREE |  | vend_consignment_id |
| `idx_consignment_id` | BTREE |  | consignment_id |

---

### `queue_config`

**Rows:** 8 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `config_key` | varchar(255) | NO | PRI | NULL |  |
| `config_value` | text | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | config_key |

---

### `queue_consignment_actions`

**Rows:** 8,006 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `consignment_id` | bigint(20) unsigned | NO | MUL | NULL |  |
| `action_type` | varchar(100) | NO | MUL | NULL |  |
| `action_payload` | longtext | NO |  | NULL |  |
| `action_result` | longtext | YES |  | NULL |  |
| `is_reversible` | tinyint(1) | YES | MUL | 0 |  |
| `is_reversed` | tinyint(1) | YES |  | 0 |  |
| `reversed_by_action_id` | bigint(20) unsigned | YES | MUL | NULL |  |
| `reverse_action_type` | varchar(100) | YES |  | NULL |  |
| `reverse_reason` | text | YES |  | NULL |  |
| `status` | enum('pending','executing','completed','failed','reversed') | NO | MUL | pending |  |
| `error_message` | text | YES |  | NULL |  |
| `error_stack` | text | YES |  | NULL |  |
| `job_id` | bigint(20) unsigned | YES | MUL | NULL |  |
| `retry_count` | int(10) unsigned | YES |  | 0 |  |
| `max_retries` | int(10) unsigned | YES |  | 3 |  |
| `triggered_by_user_id` | int(10) unsigned | YES |  | NULL |  |
| `trace_id` | varchar(64) | YES | MUL | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |
| `executed_at` | timestamp | YES |  | NULL |  |
| `completed_at` | timestamp | YES |  | NULL |  |
| `reversed_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `reversed_by_action_id` | BTREE |  | reversed_by_action_id |
| `idx_consignment` | BTREE |  | consignment_id |
| `idx_status` | BTREE |  | status |
| `idx_is_reversible` | BTREE |  | is_reversible, is_reversed |
| `idx_job` | BTREE |  | job_id |
| `idx_trace_id` | BTREE |  | trace_id |
| `idx_action_type` | BTREE |  | action_type |
| `idx_created_at` | BTREE |  | created_at |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `queue_consignment_actions_ibfk_1` | `consignment_id` | `queue_consignments`.`id` |
| `queue_consignment_actions_ibfk_2` | `reversed_by_action_id` | `queue_consignment_actions`.`id` |

---

### `queue_consignment_products`

**Rows:** 581,993 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `consignment_id` | bigint(20) unsigned | NO | MUL | NULL |  |
| `vend_product_id` | varchar(100) | NO | MUL | NULL |  |
| `vend_consignment_product_id` | varchar(100) | YES | MUL | NULL |  |
| `product_name` | varchar(500) | YES |  | NULL |  |
| `product_sku` | varchar(255) | YES | MUL | NULL |  |
| `product_supplier_code` | varchar(255) | YES |  | NULL |  |
| `count_ordered` | int(10) unsigned | NO |  | 0 |  |
| `count_received` | int(10) unsigned | NO |  | 0 |  |
| `count_damaged` | int(10) unsigned | NO |  | 0 |  |
| `cost_per_unit` | decimal(10,2) | YES |  | NULL |  |
| `cost_total` | decimal(10,2) | YES |  | NULL |  |
| `cis_product_id` | int(10) unsigned | YES | MUL | NULL |  |
| `inventory_updated` | tinyint(1) | YES | MUL | 0 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `received_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_consignment` | BTREE |  | consignment_id |
| `idx_vend_product` | BTREE |  | vend_product_id |
| `idx_vend_consignment_product` | BTREE |  | vend_consignment_product_id |
| `idx_cis_product` | BTREE |  | cis_product_id |
| `idx_inventory_updated` | BTREE |  | inventory_updated |
| `idx_product_sku` | BTREE |  | product_sku |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `queue_consignment_products_ibfk_1` | `consignment_id` | `queue_consignments`.`id` |

---

### `queue_consignment_state_transitions`

**Rows:** 8,052 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `consignment_id` | bigint(20) unsigned | NO | MUL | NULL |  |
| `from_status` | varchar(50) | YES | MUL | NULL |  |
| `to_status` | varchar(50) | NO |  | NULL |  |
| `trigger_type` | enum('user_action','webhook','auto_transition','api_sync','system') | NO |  | NULL |  |
| `trigger_user_id` | int(10) unsigned | YES | MUL | NULL |  |
| `trigger_job_id` | bigint(20) unsigned | YES | MUL | NULL |  |
| `trigger_webhook_id` | bigint(20) unsigned | YES | MUL | NULL |  |
| `is_valid` | tinyint(1) | YES |  | 1 |  |
| `validation_error` | text | YES |  | NULL |  |
| `api_request_url` | varchar(500) | YES |  | NULL |  |
| `api_request_method` | varchar(10) | YES |  | NULL |  |
| `api_request_payload` | text | YES |  | NULL |  |
| `api_response_code` | int(11) | YES |  | NULL |  |
| `api_response_body` | text | YES |  | NULL |  |
| `api_response_time_ms` | int(11) | YES |  | NULL |  |
| `api_error` | text | YES |  | NULL |  |
| `trace_id` | varchar(64) | YES | MUL | NULL |  |
| `notes` | text | YES |  | NULL |  |
| `transitioned_at` | timestamp | NO | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_consignment` | BTREE |  | consignment_id |
| `idx_trigger_user` | BTREE |  | trigger_user_id |
| `idx_trigger_job` | BTREE |  | trigger_job_id |
| `idx_trigger_webhook` | BTREE |  | trigger_webhook_id |
| `idx_trace_id` | BTREE |  | trace_id |
| `idx_transitioned_at` | BTREE |  | transitioned_at |
| `idx_from_to` | BTREE |  | from_status, to_status |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `queue_consignment_state_transitions_ibfk_1` | `consignment_id` | `queue_consignments`.`id` |

---

### `queue_consignments`

**Rows:** 25,645 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `vend_consignment_id` | varchar(100) | NO | UNI | NULL |  |
| `lightspeed_consignment_id` | varchar(100) | YES | MUL | NULL |  |
| `vend_version` | int(10) unsigned | YES |  | 0 |  |
| `type` | enum('SUPPLIER','OUTLET','RETURN','STOCKTAKE') | NO | MUL | NULL |  |
| `status` | enum('OPEN','SENT','DISPATCHED','RECEIVED','CANCELLED','STOCKTAKE','STOCKTAKE_SCHEDULED','STOCKTAKE_IN_PROGRESS','STOCKTAKE_IN_PROGRESS_PROCESSED','STOCKTAKE_COMPLETE') | NO | MUL | OPEN |  |
| `reference` | varchar(255) | YES |  | NULL |  |
| `name` | text | YES |  | NULL |  |
| `source_outlet_id` | varchar(100) | YES | MUL | NULL |  |
| `destination_outlet_id` | varchar(100) | YES | MUL | NULL |  |
| `supplier_id` | varchar(100) | YES | MUL | NULL |  |
| `cis_user_id` | int(10) unsigned | YES | MUL | NULL |  |
| `cis_purchase_order_id` | int(10) unsigned | YES |  | NULL |  |
| `cis_transfer_id` | int(10) unsigned | YES |  | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `sent_at` | timestamp | YES |  | NULL |  |
| `dispatched_at` | timestamp | YES |  | NULL |  |
| `received_at` | timestamp | YES | MUL | NULL |  |
| `delivery_date` | date | YES |  | NULL |  |
| `due_at` | datetime | YES |  | NULL |  |
| `completed_at` | timestamp | YES |  | NULL |  |
| `trace_id` | varchar(64) | YES | MUL | NULL |  |
| `last_sync_at` | timestamp | YES |  | NULL |  |
| `is_migrated` | tinyint(1) | NO |  | 0 |  |
| `sync_source` | enum('CIS','LIGHTSPEED','MIGRATION') | NO |  | CIS |  |
| `sync_last_pulled_at` | datetime | YES |  | NULL |  |
| `sync_last_pushed_at` | datetime | YES |  | NULL |  |
| `created_by_user_id` | bigint(20) unsigned | YES |  | NULL |  |
| `approved_for_lightspeed` | tinyint(1) | YES |  | 0 |  |
| `approved_by_user_id` | bigint(20) unsigned | YES |  | NULL |  |
| `approved_at` | datetime | YES |  | NULL |  |
| `pushed_to_lightspeed_at` | datetime | YES |  | NULL |  |
| `lightspeed_push_attempts` | int(11) | YES |  | 0 |  |
| `lightspeed_push_error` | text | YES |  | NULL |  |
| `upload_session_id` | varchar(255) | YES |  | NULL |  |
| `upload_progress` | int(11) | YES |  | 0 |  |
| `upload_status` | enum('pending','processing','completed','failed') | YES |  | pending |  |
| `upload_started_at` | timestamp | YES |  | NULL |  |
| `upload_completed_at` | timestamp | YES |  | NULL |  |
| `tracking_number` | varchar(255) | YES |  | TRK-PENDING |  |
| `carrier` | varchar(100) | YES |  | CourierPost |  |
| `delivery_type` | enum('pickup','dropoff') | YES |  | dropoff |  |
| `pickup_location` | varchar(255) | YES |  | NULL |  |
| `dropoff_location` | varchar(255) | YES |  | NULL |  |
| `webhook_data` | longtext | YES |  | NULL |  |
| `received_by` | varchar(100) | YES |  | NULL |  |
| `total_value` | decimal(10,2) | YES |  | NULL |  |
| `total_cost` | decimal(10,2) | YES |  | NULL |  |
| `item_count` | int(11) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `vend_consignment_id` | BTREE | ✓ | vend_consignment_id |
| `idx_vend_consignment_id` | BTREE |  | vend_consignment_id |
| `idx_type_status` | BTREE |  | type, status |
| `idx_destination_outlet` | BTREE |  | destination_outlet_id |
| `idx_source_outlet` | BTREE |  | source_outlet_id |
| `idx_supplier` | BTREE |  | supplier_id |
| `idx_cis_user` | BTREE |  | cis_user_id |
| `idx_created_at` | BTREE |  | created_at |
| `idx_status_updated` | BTREE |  | status, updated_at |
| `idx_trace_id` | BTREE |  | trace_id |
| `idx_lightspeed_consignment_id` | BTREE |  | lightspeed_consignment_id |
| `idx_trace_id_webhook` | BTREE |  | trace_id |
| `idx_received_at` | BTREE |  | received_at |

---

### `queue_dashboard_users`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `username` | varchar(120) | NO | UNI | NULL |  |
| `password_hash` | varchar(255) | NO |  | NULL |  |
| `display_name` | varchar(190) | YES |  | NULL |  |
| `role` | varchar(32) | NO |  | admin |  |
| `is_active` | tinyint(1) | NO |  | 1 |  |
| `last_login_at` | timestamp | YES |  | NULL |  |
| `last_login_ip` | varchar(45) | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uk_queue_dashboard_users_username` | BTREE | ✓ | username |

---

### `queue_feature_endpoints`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) | NO | PRI | NULL | auto_increment |
| `key` | varchar(120) | NO | UNI | NULL |  |
| `title` | varchar(160) | NO |  | NULL |  |
| `status` | enum('enabled','disabled') | NO |  | enabled |  |
| `http_method` | enum('POST','GET','PUT','PATCH') | NO |  | POST |  |
| `handler_type` | enum('queue','sync') | NO |  | queue |  |
| `job_type` | varchar(120) | YES |  | NULL |  |
| `payload_schema` | longtext | YES |  | NULL |  |
| `idempotency_mode` | enum('header','field','off') | NO |  | header |  |
| `auth_mode` | enum('open','bearer') | NO |  | bearer |  |
| `secret` | varchar(255) | YES |  | NULL |  |
| `notes` | text | YES |  | NULL |  |
| `created_at` | datetime | NO |  | current_timestamp() |  |
| `updated_at` | datetime | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `key` | BTREE | ✓ | key |

---

### `queue_health`

**Rows:** 13 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `sampled_at` | datetime | NO | MUL | NULL |  |
| `tier` | enum('green','amber','red') | NO |  | NULL |  |
| `damage_score` | decimal(5,2) | NO |  | 0.00 |  |
| `metrics` | longtext | YES |  | NULL |  |
| `notes` | text | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_sampled` | BTREE |  | sampled_at |

---

### `queue_jobs`

**Rows:** 4,588 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `job_id` | varchar(80) | NO | UNI | NULL |  |
| `job_type` | varchar(80) | NO | MUL | NULL |  |
| `queue_name` | varchar(32) | NO | MUL | default |  |
| `payload` | longtext | NO |  | NULL |  |
| `priority` | tinyint(3) unsigned | NO |  | 5 |  |
| `status` | enum('pending','processing','completed','failed','cancelled','dead_letter') | NO | MUL | pending |  |
| `attempts` | int(10) unsigned | NO |  | 0 |  |
| `max_attempts` | int(10) unsigned | NO |  | 3 |  |
| `available_at` | datetime | NO |  | current_timestamp() |  |
| `started_at` | datetime | YES |  | NULL |  |
| `completed_at` | datetime | YES |  | NULL |  |
| `finished_at` | timestamp | YES |  | NULL |  |
| `failed_at` | datetime | YES |  | NULL |  |
| `next_retry_at` | timestamp | YES | MUL | NULL |  |
| `worker_id` | varchar(128) | YES | MUL | NULL |  |
| `heartbeat_at` | datetime | YES |  | NULL |  |
| `heartbeat_timeout` | int(10) unsigned | NO |  | 300 |  |
| `leased_until` | timestamp | YES |  | NULL |  |
| `last_error` | text | YES |  | NULL |  |
| `result` | longtext | YES |  | NULL |  |
| `idempotency_key` | varchar(128) | YES | UNI | NULL |  |
| `trace_id` | varchar(64) | YES | MUL | NULL |  |
| `legacy_job_id` | bigint(20) unsigned | YES | MUL | NULL |  |
| `error_details` | longtext | YES |  | NULL |  |
| `created_by_user` | int(10) unsigned | YES | MUL | NULL |  |
| `processing_log` | longtext | YES |  | NULL |  |
| `result_meta` | longtext | YES |  | NULL |  |
| `created_at` | datetime | NO | MUL | current_timestamp() |  |
| `updated_at` | datetime | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `job_id` | BTREE | ✓ | job_id |
| `uq_job_id` | BTREE | ✓ | job_id |
| `uq_idem` | BTREE | ✓ | idempotency_key |
| `idx_status_available` | BTREE |  | status, available_at |
| `idx_job_type_status` | BTREE |  | job_type, status |
| `idx_queue_name_priority` | BTREE |  | queue_name, priority, available_at |
| `idx_worker_heartbeat` | BTREE |  | worker_id, heartbeat_at |
| `idx_retry_schedule` | BTREE |  | next_retry_at, status |
| `idx_created_user` | BTREE |  | created_by_user |
| `idx_job_lookup` | BTREE |  | job_id, job_type |
| `idx_queue_jobs_created_at` | BTREE |  | created_at |
| `idx_queue_jobs_claim` | BTREE |  | status, available_at, priority, created_at |
| `idx_status_avail_prio_id` | BTREE |  | status, available_at, priority, id |
| `idx_worker_status` | BTREE |  | worker_id, status |
| `idx_job_type` | BTREE |  | job_type |
| `idx_claim` | BTREE |  | status, available_at, priority, id |
| `idx_queue_claim` | BTREE |  | queue_name, status, available_at, priority, id |
| `idx_worker` | BTREE |  | worker_id |
| `idx_trace` | BTREE |  | trace_id |
| `idx_legacy` | BTREE |  | legacy_job_id |

---

### `queue_metrics`

**Rows:** 127,353 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `metric_name` | varchar(255) | NO | MUL | NULL |  |
| `value` | decimal(20,6) | NO |  | NULL |  |
| `labels` | longtext | YES |  | NULL |  |
| `timestamp` | int(10) unsigned | NO | MUL | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_metric_name` | BTREE |  | metric_name |
| `idx_timestamp` | BTREE |  | timestamp |
| `idx_metric_time` | BTREE |  | metric_name, timestamp |
| `idx_created_at` | BTREE |  | created_at |

---

### `queue_migrations`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `migration` | varchar(255) | NO | UNI | NULL |  |
| `executed_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `migration` | BTREE | ✓ | migration |

---

### `queue_pipelines`

**Rows:** 3 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `pipeline_id` | varchar(255) | NO | UNI | NULL |  |
| `name` | varchar(255) | NO | UNI | NULL |  |
| `label` | varchar(255) | NO |  | NULL |  |
| `description` | text | YES |  | NULL |  |
| `handler_class` | varchar(255) | NO | MUL | NULL |  |
| `execution_mode` | enum('sync','async','background','wait_for_completion') | YES | MUL | async |  |
| `timeout_seconds` | int(10) unsigned | YES |  | 300 |  |
| `retry_attempts` | tinyint(3) unsigned | YES |  | 3 |  |
| `config_json` | longtext | YES |  | NULL |  |
| `tags` | longtext | YES |  | NULL |  |
| `enabled` | tinyint(1) | YES | MUL | 1 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `pipeline_id` | BTREE | ✓ | pipeline_id |
| `uk_pipeline_id` | BTREE | ✓ | pipeline_id |
| `uk_name` | BTREE | ✓ | name |
| `idx_enabled` | BTREE |  | enabled |
| `idx_execution_mode` | BTREE |  | execution_mode |
| `idx_handler_class` | BTREE |  | handler_class |

---

### `queue_rate_limits`

**Rows:** 7 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `limit_key` | varchar(255) | NO | UNI | NULL |  |
| `tokens` | decimal(10,2) | NO |  | 0.00 |  |
| `max_tokens` | int(10) unsigned | NO |  | NULL |  |
| `refill_rate` | decimal(10,4) | NO |  | NULL |  |
| `last_refill` | int(10) unsigned | NO | MUL | NULL |  |
| `request_count` | int(10) unsigned | YES | MUL | 0 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `last_request` | timestamp | YES | MUL | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uk_limit_key` | BTREE | ✓ | limit_key |
| `idx_last_request` | BTREE |  | last_request |
| `idx_last_refill` | BTREE |  | last_refill |
| `idx_rate_limits_key_refill` | BTREE |  | limit_key, last_refill |
| `idx_rate_limits_request_count` | BTREE |  | request_count |

---

### `queue_recurring_jobs`

**Rows:** 3 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `name` | varchar(255) | NO | UNI | NULL |  |
| `job_type` | varchar(255) | NO | MUL | NULL |  |
| `payload` | longtext | YES |  | NULL |  |
| `cron_expression` | varchar(100) | NO |  | NULL |  |
| `priority` | tinyint(3) unsigned | YES |  | 5 |  |
| `max_attempts` | tinyint(3) unsigned | YES |  | 3 |  |
| `enabled` | tinyint(1) | YES | MUL | 1 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `last_run` | timestamp | YES | MUL | NULL |  |
| `next_run` | timestamp | YES | MUL | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uk_name` | BTREE | ✓ | name |
| `idx_job_type` | BTREE |  | job_type |
| `idx_enabled` | BTREE |  | enabled |
| `idx_next_run` | BTREE |  | next_run |
| `idx_last_run` | BTREE |  | last_run |

---

### `queue_shadow_checkpoint`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | tinyint(4) | NO | PRI | 1 |  |
| `last_legacy_id` | bigint(20) unsigned | NO |  | 0 |  |
| `updated_at` | datetime | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `queue_sync_history`

**Rows:** 3 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `sync_type` | varchar(50) | NO | MUL | NULL |  |
| `date_from` | datetime | NO | MUL | NULL |  |
| `date_to` | datetime | NO |  | NULL |  |
| `outlets` | longtext | YES |  | NULL |  |
| `initiated_by` | bigint(20) unsigned | NO | MUL | NULL |  |
| `status` | enum('queued','processing','completed','failed','cancelled') | NO | MUL | queued |  |
| `items_processed` | int(11) | YES |  | 0 |  |
| `items_failed` | int(11) | YES |  | 0 |  |
| `error_message` | text | YES |  | NULL |  |
| `started_at` | datetime | YES |  | NULL |  |
| `completed_at` | datetime | YES |  | NULL |  |
| `created_at` | datetime | NO | MUL | current_timestamp() |  |
| `updated_at` | datetime | YES |  | NULL | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_sync_type` | BTREE |  | sync_type |
| `idx_status` | BTREE |  | status |
| `idx_initiated_by` | BTREE |  | initiated_by |
| `idx_date_range` | BTREE |  | date_from, date_to |
| `idx_created_at` | BTREE |  | created_at |

---

### `queue_system_health`

**Rows:** 5,589 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `check_type` | varchar(50) | NO | MUL | NULL |  |
| `status` | enum('healthy','warning','critical') | NO | MUL | NULL |  |
| `message` | text | YES |  | NULL |  |
| `metrics` | longtext | YES |  | NULL |  |
| `checked_at` | timestamp | NO | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_check_type` | BTREE |  | check_type |
| `idx_status` | BTREE |  | status |
| `idx_checked_at` | BTREE |  | checked_at |

---

### `queue_trace`

**Rows:** 2,302 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `trace_id` | varchar(64) | NO | MUL | NULL |  |
| `subject` | enum('webhook','job','system') | NO | MUL | NULL |  |
| `subject_id` | varchar(64) | YES |  | NULL |  |
| `stage` | varchar(64) | NO |  | NULL |  |
| `level` | varchar(16) | NO |  | info |  |
| `data` | longtext | YES |  | NULL |  |
| `created_at` | datetime | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_trace` | BTREE |  | trace_id |
| `idx_subject` | BTREE |  | subject, subject_id |

---

### `queue_webhook_endpoints`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) | NO | PRI | NULL | auto_increment |
| `name` | varchar(120) | NO | UNI | NULL |  |
| `token` | char(24) | NO |  | NULL |  |
| `status` | enum('enabled','disabled') | NO |  | enabled |  |
| `mode` | enum('open','hmac','bearer') | NO |  | open |  |
| `secret` | varchar(255) | YES |  | NULL |  |
| `tolerance_sec` | int(11) | NO |  | 300 |  |
| `fanout_enabled` | tinyint(1) | NO |  | 1 |  |
| `notes` | text | YES |  | NULL |  |
| `created_at` | datetime | NO |  | current_timestamp() |  |
| `updated_at` | datetime | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `name` | BTREE | ✓ | name |

---

### `queue_webhook_events`

**Rows:** 17,846 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `webhook_id` | varchar(64) | NO | UNI | NULL |  |
| `webhook_type` | varchar(100) | NO | MUL | NULL |  |
| `status` | enum('received','processing','completed','failed') | NO | MUL | received |  |
| `received_at` | datetime | NO | MUL | current_timestamp() |  |
| `processed_at` | datetime | YES |  | NULL |  |
| `queue_job_id` | bigint(20) unsigned | YES | MUL | NULL |  |
| `hmac_valid` | tinyint(1) | YES |  | NULL |  |
| `payload_json` | longtext | YES |  | NULL |  |
| `headers_json` | longtext | YES |  | NULL |  |
| `source_ip` | varchar(45) | YES |  | NULL |  |
| `user_agent` | varchar(255) | YES |  | NULL |  |
| `error_message` | text | YES |  | NULL |  |
| `created_at` | datetime | NO | MUL | current_timestamp() |  |
| `updated_at` | datetime | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uq_webhook_id` | BTREE | ✓ | webhook_id |
| `idx_type_status` | BTREE |  | webhook_type, status |
| `idx_received` | BTREE |  | received_at |
| `idx_qwe_status` | BTREE |  | status |
| `idx_qwe_created_at` | BTREE |  | created_at |
| `idx_qwe_queue_job_id` | BTREE |  | queue_job_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_qwe_queue_job` | `queue_job_id` | `queue_jobs`.`id` |

---

### `queue_worker_heartbeats`

**Rows:** 19 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `worker_id` | varchar(50) | NO | PRI | NULL |  |
| `hostname` | varchar(100) | NO |  | NULL |  |
| `process_id` | int(11) | NO |  | NULL |  |
| `last_heartbeat` | timestamp | NO | MUL | current_timestamp() | on update current_timestamp() |
| `jobs_processed` | int(11) | YES |  | 0 |  |
| `memory_usage_mb` | decimal(10,2) | YES |  | 0.00 |  |
| `cpu_usage_percent` | decimal(5,2) | YES |  | 0.00 |  |
| `status` | enum('active','idle','stopping','dead') | YES | MUL | active |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | worker_id |
| `idx_last_heartbeat` | BTREE |  | last_heartbeat |
| `idx_status` | BTREE |  | status |

---

### `queue_worker_status`

**Rows:** 129 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `worker_id` | varchar(50) | NO | MUL | NULL |  |
| `event_type` | enum('started','stopped','heartbeat','job_started','job_completed','error') | NO | MUL | NULL |  |
| `event_data` | longtext | YES |  | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_worker_id` | BTREE |  | worker_id |
| `idx_event_type` | BTREE |  | event_type |
| `idx_created_at` | BTREE |  | created_at |

---

### `quiz_answers`

**Rows:** 10 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `quiz_id` | int(11) | NO |  | NULL |  |
| `title` | mediumtext | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `quiz_questions`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `title` | mediumtext | NO |  | NULL |  |
| `question_type` | varchar(45) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `rate_limits`

**Rows:** 57 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `ip` | varchar(45) | NO | MUL | NULL |  |
| `timestamp` | int(11) | NO | MUL | NULL |  |
| `user_agent` | varchar(255) | YES |  |  |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_ip_time` | BTREE |  | ip, timestamp |
| `idx_timestamp` | BTREE |  | timestamp |

---

### `recently_stocktaked`

**Rows:** 1,256,976 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `product_id` | varchar(100) | NO | MUL | NULL |  |
| `outlet_id` | varchar(100) | NO |  | NULL |  |
| `staff_id` | int(11) | NO |  | NULL |  |
| `time_stocktaked` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `index` | BTREE |  | product_id, outlet_id, time_stocktaked |

---

### `reconciliation_rules`

**Rows:** 18 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `name` | varchar(100) | NO |  | NULL |  |
| `contact_pattern` | varchar(255) | YES |  | NULL |  |
| `description_pattern` | varchar(255) | YES |  | NULL |  |
| `reference_pattern` | varchar(255) | YES |  | NULL |  |
| `min_amount` | decimal(10,2) | YES |  | NULL |  |
| `max_amount` | decimal(10,2) | YES |  | NULL |  |
| `account_id` | varchar(100) | NO |  | NULL |  |
| `gst_code` | varchar(50) | YES |  | GST |  |
| `description` | varchar(255) | YES |  | NULL |  |
| `confidence_threshold` | float | YES |  | 0.8 |  |
| `priority` | int(11) | YES | MUL | 1 |  |
| `active` | tinyint(1) | YES | MUL | 1 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_active` | BTREE |  | active |
| `idx_priority` | BTREE |  | priority |

---

### `refunds`

**Rows:** 52 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `order_number` | varchar(45) | YES |  | NULL |  |
| `name` | varchar(45) | YES |  | NULL |  |
| `email` | varchar(45) | YES |  | NULL |  |
| `bank_account` | varchar(45) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `register_closure`

**Rows:** 26,967 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | varchar(100) | NO | PRI | NULL |  |
| `open_count_sequence` | varchar(45) | NO |  | NULL |  |
| `register_open_time` | varchar(45) | NO |  | NULL |  |
| `register_close_time` | varchar(45) | NO |  | NULL |  |
| `register_id` | varchar(45) | NO |  | NULL |  |
| `register_name` | varchar(45) | NO |  | NULL |  |
| `register_outlet_id` | varchar(45) | NO |  | NULL |  |
| `register_outlet_name` | varchar(45) | NO |  | NULL |  |
| `user_id` | varchar(255) | YES |  | NULL |  |
| `user_name` | varchar(255) | YES |  | NULL |  |
| `created_at` | varchar(255) | YES |  | NULL |  |
| `updated_at` | varchar(255) | YES |  | NULL |  |
| `deleted_at` | varchar(255) | YES |  | NULL |  |
| `version` | varchar(255) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `register_closure_bank_deposits`

**Rows:** 3,850 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `outlet_id` | varchar(45) | NO | MUL | NULL |  |
| `notes` | mediumtext | YES |  | NULL |  |
| `first_name` | varchar(45) | NO |  | NULL |  |
| `last_name` | varchar(45) | NO |  | NULL |  |
| `created` | timestamp | NO |  | current_timestamp() |  |
| `expected_cash_total` | decimal(15,5) | NO |  | NULL |  |
| `actual_cash_total` | decimal(15,5) | NO |  | NULL |  |
| `user_created_by` | int(11) | NO |  | NULL |  |
| `reference` | varchar(45) | NO |  | NULL |  |
| `bag_number` | varchar(45) | NO |  | NULL |  |
| `matched_transaction_id` | int(11) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `bankDepositIndex` | BTREE |  | outlet_id, reference, bag_number, user_created_by |

---

### `register_closure_daily_totals`

**Rows:** 27,999 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `total_cash_count` | decimal(15,5) | NO |  | NULL |  |
| `remainder_in_till` | decimal(15,5) | NO |  | NULL |  |
| `total_cash_banked` | decimal(15,5) | NO |  | NULL |  |
| `first_name` | varchar(45) | NO |  | NULL |  |
| `last_name` | varchar(45) | NO |  | NULL |  |
| `submitted_user_id` | int(11) | NO |  | NULL |  |
| `created` | timestamp | NO |  | current_timestamp() |  |
| `notes` | mediumtext | YES |  | NULL |  |
| `register_closure_id` | varchar(45) | NO |  | NULL |  |
| `missing` | int(11) | NO |  | 0 |  |
| `bank_deposit_id` | int(11) | YES |  | NULL |  |
| `actual_cash_banked_deposited` | decimal(15,5) | YES |  | NULL |  |
| `bag_number` | varchar(45) | NO |  | NULL |  |
| `bag_number_confirmed` | varchar(45) | YES |  | NULL |  |
| `cashup_calc_data` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `register_closure_payments_counts`

**Rows:** 747,920 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | varchar(45) | NO |  | NULL |  |
| `start_total` | decimal(15,5) | YES |  | 0.00000 |  |
| `payments_total` | decimal(15,5) | YES |  | 0.00000 |  |
| `withdrawals_total` | decimal(15,5) | YES |  | 0.00000 |  |
| `close_total` | decimal(15,5) | YES |  | 0.00000 |  |
| `payment_type_id` | varchar(45) | YES |  | NULL |  |
| `payment_type_name` | varchar(45) | YES |  | NULL |  |
| `payment_type_id_2` | varchar(45) | YES |  |  |  |
| `config` | mediumtext | YES |  | NULL |  |
| `closure_id` | varchar(45) | YES |  | NULL |  |
| `payment_type_config` | mediumtext | YES |  | NULL |  |
| `payment_type_payment_type_id` | bigint(20) | YES |  | NULL |  |

---

### `register_closure_payments_summary`

**Rows:** 749,598 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | varchar(45) | NO | PRI | NULL |  |
| `summary_id` | varchar(45) | NO | PRI | NULL |  |
| `payment_type_name` | varchar(45) | NO |  | NULL |  |
| `total` | decimal(15,5) | NO |  | NULL |  |
| `discrepancy` | decimal(15,5) | NO |  | NULL |  |
| `closure_id` | varchar(100) | YES |  | NULL |  |
| `payment_type_key` | varchar(100) | YES |  | NULL |  |
| `payment_type_id` | int(11) | YES |  | NULL |  |
| `payment_uuid` | varchar(45) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id, summary_id |

---

### `register_closure_taxes`

**Rows:** 26,252 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | varchar(45) | NO | PRI | NULL |  |
| `tax_id` | varchar(45) | NO |  | NULL |  |
| `name` | varchar(45) | NO |  | NULL |  |
| `rate` | decimal(15,5) | NO |  | NULL |  |
| `total` | decimal(15,5) | NO |  | NULL |  |
| `taxable` | varchar(255) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `register_closure_totals`

**Rows:** 28,665 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | varchar(100) | NO | PRI | NULL |  |
| `sales` | decimal(15,5) | NO |  | NULL |  |
| `onaccount` | decimal(15,5) | NO |  | NULL |  |
| `laybys` | decimal(15,5) | NO |  | NULL |  |
| `tax` | decimal(15,5) | NO |  | NULL |  |
| `discounts` | decimal(15,5) | NO |  | NULL |  |
| `payments` | decimal(15,5) | NO |  | NULL |  |
| `regular_payments` | decimal(15,5) | NO |  | NULL |  |
| `onaccount_payments` | decimal(15,5) | NO |  | NULL |  |
| `layby_payments` | decimal(15,5) | NO |  | NULL |  |
| `loyalty` | decimal(15,5) | NO |  | NULL |  |
| `cost_of_goods_sold` | decimal(15,5) | NO |  | NULL |  |
| `returns` | int(11) | YES |  | NULL |  |
| `cash_movements` | int(11) | YES |  | NULL |  |
| `paid_in` | int(11) | YES |  | NULL |  |
| `paid_out` | int(11) | YES |  | NULL |  |
| `tips` | int(11) | YES |  | NULL |  |
| `expected_cash` | int(11) | YES |  | NULL |  |
| `actual_cash` | int(11) | YES |  | NULL |  |
| `cash_difference` | int(11) | YES |  | NULL |  |
| `opening_cash` | int(11) | YES |  | NULL |  |
| `closing_cash` | int(11) | YES |  | NULL |  |
| `net_sales` | int(11) | YES |  | NULL |  |
| `gross_sales` | int(11) | YES |  | NULL |  |
| `refunds` | int(11) | YES |  | NULL |  |
| `voided_sales` | int(11) | YES |  | NULL |  |
| `transaction_count` | int(11) | YES |  | NULL |  |
| `customer_count` | int(11) | YES |  | NULL |  |
| `average_sale` | int(11) | YES |  | NULL |  |
| `profit` | int(11) | YES |  | NULL |  |
| `margin` | int(11) | YES |  | NULL |  |
| `fulfillments` | int(11) | YES |  | NULL |  |
| `fulfillments_advance_revenue` | int(11) | YES |  | NULL |  |
| `fulfillments_payments` | int(11) | YES |  | NULL |  |
| `fulfillments_revenue` | decimal(15,5) | YES |  | NULL |  |
| `gift_card_sales` | int(11) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `remuneration_request_answers`

**Rows:** 240 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `question_id` | int(11) | NO | PRI | NULL |  |
| `request_id` | int(11) | NO | PRI | NULL |  |
| `answer` | mediumtext | YES |  | NULL |  |
| `question_text` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | request_id, question_id |

---

### `remuneration_request_questions`

**Rows:** 25 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `question_text` | mediumtext | NO |  | NULL |  |
| `active` | int(11) | NO |  | 1 |  |
| `required` | int(11) | NO |  | 1 |  |
| `sort` | int(11) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `remuneration_requests`

**Rows:** 21 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `user_id` | int(11) | NO |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `status` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `roles`

**Rows:** 3 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `name` | varchar(100) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `rtsp_cameras`

**Rows:** 96 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `cred_id` | int(11) | NO | MUL | NULL |  |
| `camera_id` | int(11) | NO |  | NULL |  |
| `zone_label` | varchar(100) | YES |  | NULL |  |
| `channel` | varchar(4) | NO |  | NULL |  |
| `is_default` | tinyint(1) | NO |  | 0 |  |
| `active` | tinyint(1) | NO |  | 1 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uq_cred_camera` | BTREE | ✓ | cred_id, camera_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `rtsp_cameras_ibfk_1` | `cred_id` | `rtsp_credentials`.`id` |

---

### `rtsp_credentials`

**Rows:** 17 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `outlet_id` | varchar(45) | NO | UNI | NULL |  |
| `rtsp_username` | varchar(45) | NO |  | NULL |  |
| `rtsp_password` | varchar(45) | NO |  | NULL |  |
| `port` | int(11) | NO |  | 554 |  |
| `rtsp_format_example` | varchar(255) | YES |  | NULL |  |
| `active` | tinyint(1) | NO |  | 1 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uq_outlet` | BTREE | ✓ | outlet_id |

---

### `rtsp_feeds`

**Rows:** 17 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `outlet_id` | varchar(45) | NO |  | NULL |  |
| `rtsp_username` | varchar(45) | NO |  | NULL |  |
| `rtsp_password` | varchar(45) | NO |  | NULL |  |
| `channel` | varchar(45) | NO |  | NULL |  |
| `default` | int(11) | NO |  | 0 |  |
| `active` | int(11) | NO |  | 0 |  |
| `port` | int(11) | NO |  | 554 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `sales_aggregation_log`

**Rows:** 137 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `aggregation_type` | varchar(50) | NO | MUL | NULL |  |
| `run_date` | timestamp | NO |  | current_timestamp() |  |
| `records_processed` | int(11) | YES |  | 0 |  |
| `records_inserted` | int(11) | YES |  | 0 |  |
| `records_updated` | int(11) | YES |  | 0 |  |
| `execution_time_ms` | int(11) | YES |  | 0 |  |
| `status` | varchar(20) | YES | MUL | success |  |
| `error_message` | text | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_type_date` | BTREE |  | aggregation_type, run_date |
| `idx_status` | BTREE |  | status |

---

### `sales_product_outlet_summary`

**Rows:** 15,848 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `product_id` | varchar(36) | NO | MUL | NULL |  |
| `outlet_id` | varchar(36) | NO | MUL | NULL |  |
| `units_sold_7d` | int(11) | YES |  | 0 |  |
| `avg_daily_7d` | decimal(10,2) | YES |  | 0.00 |  |
| `units_sold_30d` | int(11) | YES |  | 0 |  |
| `avg_daily_30d` | decimal(10,2) | YES |  | 0.00 |  |
| `units_sold_60d` | int(11) | YES |  | 0 |  |
| `avg_daily_60d` | decimal(10,2) | YES |  | 0.00 |  |
| `units_sold_90d` | int(11) | YES |  | 0 |  |
| `avg_daily_90d` | decimal(10,2) | YES |  | 0.00 |  |
| `velocity` | varchar(20) | YES | MUL | none |  |
| `velocity_score` | decimal(10,4) | YES |  | 0.0000 |  |
| `trend` | varchar(20) | YES |  | stable |  |
| `trend_score` | decimal(10,4) | YES |  | 0.0000 |  |
| `last_sale_date` | date | YES | MUL | NULL |  |
| `days_since_last_sale` | int(11) | YES |  | NULL |  |
| `is_stale` | tinyint(1) | YES | MUL | 0 |  |
| `demand_target` | int(11) | YES |  | 0 |  |
| `replenishment_priority` | int(11) | YES | MUL | 0 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `last_calculated_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_product_outlet` | BTREE | ✓ | product_id, outlet_id |
| `idx_outlet` | BTREE |  | outlet_id |
| `idx_product` | BTREE |  | product_id |
| `idx_velocity` | BTREE |  | velocity |
| `idx_is_stale` | BTREE |  | is_stale |
| `idx_last_sale` | BTREE |  | last_sale_date |
| `idx_priority` | BTREE |  | replenishment_priority |

---

### `sales_summary_90d`

**Rows:** 27,569 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `product_id` | varchar(36) | NO | PRI | NULL |  |
| `outlet_id` | varchar(36) | NO | PRI | NULL |  |
| `qty_sold` | int(11) | YES |  | 0 |  |
| `last_updated` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | product_id, outlet_id |
| `ix_sales_summary_90d_prod_outlet` | BTREE |  | product_id, outlet_id |

---

### `sales_velocity_daily`

**Rows:** 62,832 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `product_id` | varchar(36) | NO | MUL | NULL |  |
| `outlet_id` | varchar(36) | NO | MUL | NULL |  |
| `sale_date` | date | NO | MUL | NULL |  |
| `units_sold` | int(11) | YES |  | 0 |  |
| `transaction_count` | int(11) | YES |  | 0 |  |
| `total_revenue` | decimal(10,2) | YES |  | 0.00 |  |
| `avg_price` | decimal(10,2) | YES |  | 0.00 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_product_outlet_date` | BTREE | ✓ | product_id, outlet_id, sale_date |
| `idx_outlet_date` | BTREE |  | outlet_id, sale_date |
| `idx_product_date` | BTREE |  | product_id, sale_date |
| `idx_date` | BTREE |  | sale_date |

---

### `sales_velocity_monthly`

**Rows:** 30,132 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `product_id` | varchar(36) | NO | MUL | NULL |  |
| `outlet_id` | varchar(36) | NO | MUL | NULL |  |
| `month_start_date` | date | NO |  | NULL |  |
| `month` | int(11) | NO |  | NULL |  |
| `year` | int(11) | NO | MUL | NULL |  |
| `units_sold` | int(11) | YES |  | 0 |  |
| `transaction_count` | int(11) | YES |  | 0 |  |
| `total_revenue` | decimal(10,2) | YES |  | 0.00 |  |
| `avg_daily_units` | decimal(10,2) | YES |  | 0.00 |  |
| `days_with_sales` | int(11) | YES |  | 0 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_product_outlet_month` | BTREE | ✓ | product_id, outlet_id, month_start_date |
| `idx_outlet_month` | BTREE |  | outlet_id, month_start_date |
| `idx_product_month` | BTREE |  | product_id, month_start_date |
| `idx_year_month` | BTREE |  | year, month |

---

### `sales_velocity_weekly`

**Rows:** 46,543 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `product_id` | varchar(36) | NO | MUL | NULL |  |
| `outlet_id` | varchar(36) | NO | MUL | NULL |  |
| `week_start_date` | date | NO |  | NULL |  |
| `week_end_date` | date | NO |  | NULL |  |
| `week_number` | int(11) | NO |  | NULL |  |
| `year` | int(11) | NO | MUL | NULL |  |
| `units_sold` | int(11) | YES |  | 0 |  |
| `transaction_count` | int(11) | YES |  | 0 |  |
| `total_revenue` | decimal(10,2) | YES |  | 0.00 |  |
| `avg_daily_units` | decimal(10,2) | YES |  | 0.00 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_product_outlet_week` | BTREE | ✓ | product_id, outlet_id, week_start_date |
| `idx_outlet_week` | BTREE |  | outlet_id, week_start_date |
| `idx_product_week` | BTREE |  | product_id, week_start_date |
| `idx_year_week` | BTREE |  | year, week_number |

---

### `schema_migrations`

**Rows:** 12 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `filename` | varchar(255) | NO | MUL | NULL |  |
| `checksum_sha256` | char(64) | NO |  | NULL |  |
| `executed_at` | timestamp | NO | MUL | current_timestamp() |  |
| `executed_by` | varchar(64) | YES |  | NULL |  |
| `success` | tinyint(1) | NO | MUL | 1 |  |
| `error_message` | text | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `ux_schema_migrations_file_checksum` | BTREE | ✓ | filename, checksum_sha256 |
| `idx_schema_migrations_time` | BTREE |  | executed_at |
| `idx_schema_migrations_success` | BTREE |  | success, executed_at |

---

### `security_scans`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `scan_id` | varchar(255) | NO | UNI | NULL |  |
| `overall_score` | int(11) | NO |  | NULL |  |
| `risk_level` | varchar(50) | NO |  | NULL |  |
| `results` | longtext | NO |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `scan_id` | BTREE | ✓ | scan_id |

---

### `sessions`

**Rows:** 19,901 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | varchar(128) | NO | PRI | NULL |  |
| `data` | longtext | NO |  | NULL |  |
| `expires` | datetime | NO | MUL | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_expires` | BTREE |  | expires |

---

### `smart_cron_tasks`

**Rows:** 49 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `task_name` | varchar(100) | NO | UNI | NULL |  |
| `script_path` | varchar(500) | NO |  | NULL |  |
| `description` | text | YES |  | NULL |  |
| `frequency` | varchar(100) | NO |  | NULL |  |
| `cron_expression` | varchar(100) | YES |  | NULL |  |
| `enabled` | tinyint(1) | YES | MUL | 1 |  |
| `baseline_duration_seconds` | decimal(10,3) | YES |  | NULL |  |
| `baseline_memory_mb` | decimal(10,2) | YES |  | NULL |  |
| `timeout_seconds` | int(11) | YES |  | 3600 |  |
| `max_memory_mb` | int(11) | YES |  | 512 |  |
| `max_retries` | int(11) | YES |  | 3 |  |
| `retry_delay_seconds` | int(11) | YES |  | 60 |  |
| `consecutive_failures` | int(11) | YES |  | 0 |  |
| `last_success_at` | datetime | YES |  | NULL |  |
| `last_failure_at` | datetime | YES |  | NULL |  |
| `alert_on_failure` | tinyint(1) | YES |  | 1 |  |
| `alert_on_timeout` | tinyint(1) | YES |  | 1 |  |
| `alert_threshold_failures` | int(11) | YES |  | 3 |  |
| `alert_emails` | longtext | YES |  | NULL |  |
| `is_critical` | tinyint(1) | YES | MUL | 0 |  |
| `maintenance_mode` | tinyint(1) | YES |  | 0 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `task_name` | BTREE | ✓ | task_name |
| `idx_enabled` | BTREE |  | enabled |
| `idx_critical` | BTREE |  | is_critical |
| `idx_name` | BTREE |  | task_name |

---

### `staff_ai_knowledge`

**Rows:** 8 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `knowledge_type` | varchar(50) | YES | MUL | NULL |  |
| `title` | varchar(255) | YES |  | NULL |  |
| `content` | longtext | YES |  | NULL |  |
| `applicable_roles` | longtext | YES |  | NULL |  |
| `store_specific` | tinyint(1) | YES |  | 0 |  |
| `store_location` | varchar(100) | YES | MUL | NULL |  |
| `confidence_score` | decimal(3,2) | YES |  | NULL |  |
| `created_by` | int(11) | YES | MUL | NULL |  |
| `verified_by` | int(11) | YES |  | NULL |  |
| `is_active` | tinyint(1) | YES | MUL | 1 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_type` | BTREE |  | knowledge_type |
| `idx_store` | BTREE |  | store_location |
| `idx_active` | BTREE |  | is_active |
| `idx_created_by` | BTREE |  | created_by |

---

### `staff_ai_profiles`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `user_id` | int(11) | NO | MUL | NULL |  |
| `ai_personality_type` | enum('manager','sales','inventory','admin','analyst','delivery') | NO | MUL | NULL |  |
| `store_location` | varchar(100) | YES | MUL | NULL |  |
| `specializations` | longtext | YES |  | NULL |  |
| `ai_preferences` | longtext | YES |  | NULL |  |
| `learning_data` | longtext | YES |  | NULL |  |
| `performance_metrics` | longtext | YES |  | NULL |  |
| `is_active` | tinyint(1) | YES | MUL | 1 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_user_id` | BTREE |  | user_id |
| `idx_personality` | BTREE |  | ai_personality_type |
| `idx_store` | BTREE |  | store_location |
| `idx_active` | BTREE |  | is_active |

---

### `staff_members`

**Rows:** 5 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `staff_id` | varchar(50) | NO | UNI | NULL |  |
| `employee_number` | varchar(50) | YES |  | NULL |  |
| `display_name` | varchar(100) | NO |  | NULL |  |
| `first_name` | varchar(50) | NO |  | NULL |  |
| `last_name` | varchar(50) | NO |  | NULL |  |
| `position` | varchar(100) | YES |  | NULL |  |
| `department` | varchar(100) | YES |  | NULL |  |
| `outlet_id` | varchar(50) | YES | MUL | NULL |  |
| `face_vectors` | longtext | YES |  | NULL |  |
| `photos` | longtext | YES |  | NULL |  |
| `overlay_color` | varchar(7) | YES |  | #00FF00 |  |
| `recognition_enabled` | tinyint(1) | YES |  | 1 |  |
| `privacy_level` | enum('full','name_only','position_only','anonymous') | YES |  | full |  |
| `status` | enum('active','inactive','suspended') | YES | MUL | active |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `staff_id` | BTREE | ✓ | staff_id |
| `idx_status` | BTREE |  | status |
| `idx_outlet` | BTREE |  | outlet_id |

---

### `staff_transfers`

**Rows:** 1,937 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) unsigned | NO | PRI | NULL | auto_increment |
| `created_by_user` | int(11) | NO |  | NULL |  |
| `timestamp_created` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `outlet_from` | varchar(100) | NO | MUL | NULL |  |
| `status` | int(11) | NO |  | 0 |  |
| `notes` | longtext | YES |  | NULL |  |
| `deleted_by` | int(11) | YES |  | NULL |  |
| `deleted_at` | timestamp | YES |  | NULL |  |
| `customer_id` | varchar(45) | YES | MUL | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_staff_transfers_outlet_status` | BTREE |  | outlet_from, status |
| `idx_staff_customer` | BTREE |  | customer_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_staff_customer` | `customer_id` | `vend_customers`.`id` |

---

### `staff_transfers_products`

**Rows:** 11,555 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `staff_transfer_id` | int(11) | NO |  | NULL |  |
| `source_outlet_id` | varchar(90) | NO |  | NULL |  |
| `product_id` | varchar(90) | YES |  | NULL |  |
| `qty_requested` | int(11) | NO |  | NULL |  |
| `qty_sent` | int(11) | YES |  | NULL |  |
| `qty_received` | int(11) | YES |  | NULL |  |
| `qty_at_source_after_transfer` | int(11) | YES |  | NULL |  |
| `qty_at_destination_before_transfer` | int(11) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `staff_transfers_shipments`

**Rows:** 3,022 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `transferID` | int(11) | NO |  | NULL |  |
| `outletID` | varchar(45) | NO |  | NULL |  |
| `notes` | mediumtext | YES |  | NULL |  |
| `tracking_number` | mediumtext | YES |  | NULL |  |
| `timestamp_packed` | timestamp | YES |  | NULL |  |
| `timestamp_received` | timestamp | YES |  | NULL |  |
| `packed_by` | int(11) | YES |  | NULL |  |
| `received_by` | int(11) | YES |  | NULL |  |
| `received_notes` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `stock_accuracy_history`

**Rows:** 26,814 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `date_created` | timestamp | NO |  | current_timestamp() |  |
| `outlet_id` | varchar(45) | NO |  | NULL |  |
| `percentage` | decimal(13,2) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `stock_products_to_transfer`

**Rows:** 255,557 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `primary_key` | int(11) | NO | PRI | NULL | auto_increment |
| `transfer_id` | int(11) | NO | MUL | NULL |  |
| `product_id` | varchar(45) | NO | MUL | NULL |  |
| `donor_outlet_id` | varchar(100) | YES |  | NULL |  |
| `qty_to_transfer` | int(11) | NO |  | NULL |  |
| `min_qty_to_remain` | int(11) | NO |  | NULL |  |
| `qty_transferred_at_source` | int(11) | YES |  | NULL |  |
| `qty_counted_at_destination` | int(11) | YES |  | NULL |  |
| `new_total_qty_in_stock` | int(11) | YES |  | NULL |  |
| `new_total_at_destination` | int(11) | YES |  | NULL |  |
| `unexpected_product_added` | int(11) | NO |  | 0 |  |
| `staff_added_product` | int(11) | NO |  | 0 |  |
| `validation_flags` | longtext | YES |  | NULL |  |
| `validation_notes` | varchar(255) | YES |  | NULL |  |
| `deleted_at` | datetime | YES |  | NULL |  |
| `demand_forecast` | int(11) | YES |  | NULL |  |
| `stockout_risk` | decimal(5,2) | YES | MUL | NULL |  |
| `overstock_risk` | decimal(5,2) | YES |  | NULL |  |
| `optimal_qty` | int(11) | YES |  | NULL |  |
| `sales_velocity` | decimal(10,2) | YES |  | NULL |  |
| `abc_classification` | enum('A','B','C','D') | YES | MUL | NULL |  |
| `profit_impact` | decimal(12,2) | YES |  | NULL |  |
| `ml_priority_score` | decimal(5,2) | YES | MUL | NULL |  |
| `last_sale_date` | date | YES |  | NULL |  |
| `days_of_stock` | int(11) | YES |  | NULL |  |
| `pack_size` | int(11) | YES |  | 1 |  |
| `outer_multiple` | int(11) | YES |  | NULL |  |
| `enforce_outer` | tinyint(1) | YES |  | 0 |  |
| `pack_compliance_status` | enum('compliant','broken','forced') | YES | MUL | compliant |  |
| `weight_per_unit_grams` | int(11) | YES |  | NULL |  |
| `total_weight_grams` | int(11) | YES | MUL | NULL |  |
| `value_per_gram` | decimal(10,4) | YES |  | NULL |  |
| `shipping_priority` | tinyint(1) | YES | MUL | 0 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | primary_key |
| `uniq_transfer_product` | BTREE | ✓ | transfer_id, product_id |
| `product_id` | BTREE |  | product_id |
| `idx_spt_transfer_active` | BTREE |  | transfer_id, deleted_at |
| `ix_spt_transfer` | BTREE |  | transfer_id |
| `ix_spt_product` | BTREE |  | product_id |
| `idx_stockout_risk` | BTREE |  | stockout_risk |
| `idx_abc_class` | BTREE |  | abc_classification |
| `idx_priority` | BTREE |  | ml_priority_score |
| `idx_products_transfer` | BTREE |  | transfer_id, product_id |
| `idx_transfer_product` | BTREE |  | transfer_id, product_id |
| `idx_ml_priority` | BTREE |  | ml_priority_score |
| `idx_pack_compliance` | BTREE |  | pack_compliance_status, enforce_outer |
| `idx_shipping_priority` | BTREE |  | shipping_priority, value_per_gram |
| `idx_weight_optimization` | BTREE |  | total_weight_grams, weight_per_unit_grams |
| `idx_transfer_id` | BTREE |  | transfer_id |
| `idx_product_id` | BTREE |  | product_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `stockTransferKey` | `transfer_id` | `stock_transfers`.`transfer_id` |

---

### `stock_transfers_backup_20251023`

**Rows:** 2,413 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `transfer_id` | int(11) | NO |  | 0 |  |
| `date_created` | timestamp | NO |  | current_timestamp() |  |
| `status` | int(11) | NO |  | 0 |  |
| `micro_status` | varchar(50) | YES |  | NULL |  |
| `receive_confidence` | tinyint(4) | YES |  | NULL |  |
| `receive_quality_notes` | varchar(255) | YES |  | NULL |  |
| `transfer_created_by_user` | int(11) | NO |  | NULL |  |
| `transfer_completed_by_user` | int(11) | YES |  | NULL |  |
| `transfer_completed` | timestamp | YES |  | NULL |  |
| `outlet_from` | varchar(100) | NO |  | NULL |  |
| `outlet_to` | varchar(100) | NO |  | NULL |  |
| `transfer_json` | mediumtext | YES |  | NULL |  |
| `transfer_json_destination` | mediumtext | YES |  | NULL |  |
| `receive_summary_json` | mediumtext | YES |  | NULL |  |
| `recieve_completed` | timestamp | YES |  | NULL |  |
| `transfer_notes` | mediumtext | YES |  | NULL |  |
| `completed_notes` | mediumtext | YES |  | NULL |  |
| `require_source_stocktake` | int(11) | NO |  | 1 |  |
| `transfer_received_by_user` | int(11) | YES |  | NULL |  |
| `tracking_number` | mediumtext | YES |  | NULL |  |
| `transfer_partially_received_timestamp` | timestamp | YES |  | NULL |  |
| `transfer_partially_received_by_user` | int(11) | YES |  | NULL |  |
| `source_module` | varchar(64) | NO |  | automatic_stock_transfers_v4 |  |
| `delivery_mode` | varchar(64) | YES |  | NULL |  |
| `deleted_at` | datetime | YES |  | NULL |  |
| `ai_confidence_score` | decimal(5,2) | YES |  | NULL |  |
| `urgency_score` | decimal(5,2) | YES |  | NULL |  |
| `automation_triggered` | tinyint(1) | YES |  | 0 |  |
| `business_impact_score` | decimal(10,2) | YES |  | NULL |  |
| `risk_assessment` | enum('low','medium','high','critical') | YES |  | NULL |  |
| `predicted_success_rate` | decimal(5,2) | YES |  | NULL |  |
| `seasonal_factor` | decimal(5,2) | YES |  | NULL |  |
| `ml_recommendations` | longtext | YES |  | NULL |  |
| `run_id` | varchar(100) | YES |  | NULL |  |
| `priority` | int(11) | YES |  | 50 |  |
| `product_count` | int(11) | YES |  | 0 |  |
| `total_quantity` | int(11) | YES |  | 0 |  |
| `created_by_system` | varchar(100) | YES |  | automatic_stock_transfers_v4 |  |
| `transfer_type` | enum('manual','auto_seed','rebalance','emergency') | YES |  | manual |  |
| `algorithm_version` | varchar(20) | YES |  | NULL |  |
| `total_items` | int(11) | YES |  | 0 |  |
| `total_weight_grams` | int(11) | YES |  | NULL |  |
| `shipping_cost` | decimal(10,2) | YES |  | NULL |  |
| `notes` | text | YES |  | NULL |  |
| `synced_to_vend` | tinyint(1) | NO |  | 0 |  |

---

### `store_audit`

**Rows:** 5 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `outlet_id` | varchar(100) | NO | MUL | NULL |  |
| `version_id` | int(11) | NO | MUL | NULL |  |
| `performed_by_user` | int(10) unsigned | NO | MUL | NULL |  |
| `performed_at` | datetime | NO |  | NULL |  |
| `status` | enum('in_progress','submitted','void') | NO |  | in_progress |  |
| `summary_notes` | text | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | YES |  | NULL | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `fk_audit_version` | BTREE |  | version_id |
| `fk_audit_user` | BTREE |  | performed_by_user |
| `ix_audit_outlet_dt` | BTREE |  | outlet_id, performed_at |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_audit_outlet` | `outlet_id` | `vend_outlets`.`id` |
| `fk_audit_user` | `performed_by_user` | `users`.`id` |
| `fk_audit_version` | `version_id` | `store_audit_checklist_version`.`id` |

---

### `store_audit_ai_rule`

**Rows:** 40 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `version_id` | int(11) | NO | MUL | NULL |  |
| `question_code` | varchar(128) | NO |  | NULL |  |
| `mapping_kind` | enum('yesno','five_point','advisory','non_visual') | NO |  | NULL |  |
| `yes_condition` | longtext | YES |  | NULL |  |
| `five_point_rules` | longtext | YES |  | NULL |  |
| `notes` | text | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uq_version_code` | BTREE | ✓ | version_id, question_code |
| `ix_sairule__version` | BTREE |  | version_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_ai_rule_version` | `version_id` | `store_audit_checklist_version`.`id` |

---

### `store_audit_checklist_question`

**Rows:** 86 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `version_id` | int(11) | NO | MUL | NULL |  |
| `section_id` | int(11) | NO | MUL | NULL |  |
| `code` | varchar(64) | NO |  | NULL |  |
| `label` | varchar(255) | NO |  | NULL |  |
| `help` | text | YES |  | NULL |  |
| `input_kind` | enum('scale','yes_no','number','text','multiselect') | NO |  | NULL |  |
| `scale_id` | int(11) | YES | MUL | NULL |  |
| `min_value` | decimal(10,2) | YES |  | NULL |  |
| `max_value` | decimal(10,2) | YES |  | NULL |  |
| `step` | decimal(10,2) | YES |  | NULL |  |
| `required` | tinyint(1) | NO |  | 0 |  |
| `weight` | decimal(6,2) | NO |  | 1.00 |  |
| `display_order` | int(11) | NO |  | 0 |  |
| `active` | tinyint(1) | NO |  | 1 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uq_version_code` | BTREE | ✓ | version_id, code |
| `fk_question_section` | BTREE |  | section_id |
| `fk_question_scale` | BTREE |  | scale_id |
| `ix_sa_question__ver_sec_order` | BTREE |  | version_id, section_id, display_order |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_question_scale` | `scale_id` | `store_audit_rating_scale`.`id` |
| `fk_question_section` | `section_id` | `store_audit_checklist_section`.`id` |
| `fk_question_version` | `version_id` | `store_audit_checklist_version`.`id` |

---

### `store_audit_checklist_question_ai_spec`

**Rows:** 40 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `question_id` | int(11) | NO | MUL | NULL |  |
| `vision_prompt_template` | text | NO |  | NULL |  |
| `required_shots` | int(11) | NO |  | 1 |  |
| `shot_list` | longtext | YES |  | NULL |  |
| `accept_criteria` | longtext | YES |  | NULL |  |
| `failure_examples` | text | YES |  | NULL |  |
| `bbox_required` | tinyint(1) | NO |  | 0 |  |
| `tags` | longtext | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `fk_ai_question` | BTREE |  | question_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_ai_question` | `question_id` | `store_audit_checklist_question`.`id` |

---

### `store_audit_checklist_section`

**Rows:** 14 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `version_id` | int(11) | NO | MUL | NULL |  |
| `name` | varchar(120) | NO |  | NULL |  |
| `display_order` | int(11) | NO |  | 0 |  |
| `weight` | decimal(6,2) | NO |  | 1.00 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `fk_section_version` | BTREE |  | version_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_section_version` | `version_id` | `store_audit_checklist_version`.`id` |

---

### `store_audit_checklist_version`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `name` | varchar(120) | NO |  | NULL |  |
| `status` | enum('draft','active','archived') | NO |  | draft |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `activated_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `store_audit_media`

**Rows:** 4 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `audit_id` | int(11) | NO | MUL | NULL |  |
| `question_id` | int(11) | YES | MUL | NULL |  |
| `path` | text | NO |  | NULL |  |
| `mime` | varchar(64) | YES |  | NULL |  |
| `size_bytes` | int(11) | YES |  | NULL |  |
| `taken_at` | datetime | YES |  | NULL |  |
| `caption` | varchar(255) | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `ix_media_audit` | BTREE |  | audit_id |
| `ix_media_question` | BTREE |  | question_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_media_audit` | `audit_id` | `store_audit`.`id` |
| `fk_media_question` | `question_id` | `store_audit_checklist_question`.`id` |

---

### `store_audit_media_ai_result`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) | NO | PRI | NULL | auto_increment |
| `media_id` | int(11) | NO | MUL | NULL |  |
| `question_id` | int(11) | YES | MUL | NULL |  |
| `model` | varchar(100) | NO |  | NULL |  |
| `run_at` | datetime | NO |  | NULL |  |
| `status` | enum('pending','ok','error') | NO |  | pending |  |
| `summary` | text | YES |  | NULL |  |
| `suggested_option_id` | int(11) | YES | MUL | NULL |  |
| `suggested_numeric` | decimal(10,2) | YES |  | NULL |  |
| `confidence` | decimal(4,3) | YES |  | NULL |  |
| `signals` | longtext | YES |  | NULL |  |
| `prompt_used` | text | YES |  | NULL |  |
| `latency_ms` | int(11) | YES |  | NULL |  |
| `error_message` | text | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `ix_ai_media` | BTREE |  | media_id |
| `ix_ai_question` | BTREE |  | question_id |
| `ix_ai_suggest` | BTREE |  | suggested_option_id |
| `ix_samai__question_runat` | BTREE |  | question_id, run_at |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_aires_media` | `media_id` | `store_audit_media`.`id` |
| `fk_aires_question` | `question_id` | `store_audit_checklist_question`.`id` |
| `fk_aires_suggest` | `suggested_option_id` | `store_audit_rating_scale_option`.`id` |

---

### `store_audit_rating_scale`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `name` | varchar(120) | NO |  | NULL |  |
| `description` | text | YES |  | NULL |  |
| `min_score` | decimal(6,2) | NO |  | 0.00 |  |
| `max_score` | decimal(6,2) | NO |  | 1.00 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `store_audit_rating_scale_option`

**Rows:** 7 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `scale_id` | int(11) | NO | MUL | NULL |  |
| `code` | varchar(64) | NO |  | NULL |  |
| `label` | varchar(100) | NO |  | NULL |  |
| `score_value` | decimal(6,2) | NO |  | NULL |  |
| `display_order` | int(11) | NO |  | 0 |  |
| `color_hex` | char(7) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uq_scale_code` | BTREE | ✓ | scale_id, code |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_rso_scale` | `scale_id` | `store_audit_rating_scale`.`id` |

---

### `store_audit_response`

**Rows:** 120 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `audit_id` | int(11) | NO | MUL | NULL |  |
| `question_id` | int(11) | NO | MUL | NULL |  |
| `is_na` | tinyint(1) | NO |  | 0 |  |
| `scale_option_id` | int(11) | YES | MUL | NULL |  |
| `numeric_value` | decimal(10,2) | YES |  | NULL |  |
| `text_value` | text | YES |  | NULL |  |
| `notes` | text | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uq_audit_question` | BTREE | ✓ | audit_id, question_id |
| `ix_resp_audit` | BTREE |  | audit_id |
| `ix_resp_question` | BTREE |  | question_id |
| `ix_resp_scaleopt` | BTREE |  | scale_option_id |
| `ix_sa_resp__audit_q` | BTREE |  | audit_id, question_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_resp_audit` | `audit_id` | `store_audit`.`id` |
| `fk_resp_question` | `question_id` | `store_audit_checklist_question`.`id` |
| `fk_resp_scaleopt` | `scale_option_id` | `store_audit_rating_scale_option`.`id` |

---

### `store_bonus_history`

**Rows:** 16,531 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `outlet_id` | varchar(45) | NO |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `json_object` | mediumtext | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `store_customer_notes`

**Rows:** 130 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `outlet_id` | varchar(45) | NO |  | NULL |  |
| `subject` | mediumtext | NO |  | NULL |  |
| `message` | mediumtext | NO |  | NULL |  |
| `date_created` | timestamp | NO |  | current_timestamp() |  |
| `written_by` | int(11) | NO |  | NULL |  |
| `deleted` | int(11) | NO |  | 0 |  |
| `done` | int(11) | NO |  | 0 |  |
| `email_address` | varchar(100) | YES |  | NULL |  |
| `phone` | varchar(100) | YES |  | NULL |  |
| `customer_name` | varchar(100) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `store_email_accounts`

**Rows:** 4 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `store_id` | varchar(50) | NO | UNI | NULL |  |
| `store_name` | varchar(255) | NO |  | NULL |  |
| `email_address` | varchar(255) | NO | UNI | NULL |  |
| `display_name` | varchar(255) | NO |  | NULL |  |
| `imap_host` | varchar(255) | NO |  | NULL |  |
| `imap_port` | int(11) | YES |  | 993 |  |
| `imap_encryption` | enum('ssl','tls','none') | YES |  | ssl |  |
| `imap_username` | varchar(255) | NO |  | NULL |  |
| `imap_password_encrypted` | text | NO |  | NULL |  |
| `smtp_host` | varchar(255) | NO |  | NULL |  |
| `smtp_port` | int(11) | YES |  | 587 |  |
| `smtp_encryption` | enum('ssl','tls','none') | YES |  | tls |  |
| `smtp_username` | varchar(255) | NO |  | NULL |  |
| `smtp_password_encrypted` | text | NO |  | NULL |  |
| `sync_enabled` | tinyint(1) | YES | MUL | 1 |  |
| `sync_interval_minutes` | int(11) | YES |  | 5 |  |
| `last_sync` | timestamp | YES |  | NULL |  |
| `storage_quota_mb` | int(11) | YES |  | 5000 |  |
| `current_usage_mb` | decimal(10,2) | YES |  | 0.00 |  |
| `is_active` | tinyint(1) | YES | MUL | 1 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `email_address` | BTREE | ✓ | email_address |
| `store_id` | BTREE | ✓ | store_id |
| `idx_sync_enabled` | BTREE |  | sync_enabled |
| `idx_active` | BTREE |  | is_active |

---

### `store_feedback`

**Rows:** 526 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `customer_name` | varchar(45) | NO |  | NULL |  |
| `customer_email` | varchar(45) | YES |  | NULL |  |
| `customer_phone` | varchar(45) | YES |  | NULL |  |
| `store_id` | varchar(45) | YES |  | NULL |  |
| `rating` | int(11) | NO |  | NULL |  |
| `comment` | mediumtext | NO |  | NULL |  |
| `staff_name` | varchar(45) | YES |  | NULL |  |
| `timestamp` | timestamp | YES |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `store_quality`

**Rows:** 298 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `outlet_id` | varchar(45) | NO |  | NULL |  |
| `performed_by_user` | int(11) | NO |  | NULL |  |
| `date_performed` | timestamp | YES |  | NULL |  |
| `date_created` | timestamp | NO |  | current_timestamp() |  |
| `percentage` | int(11) | YES |  | -1 |  |
| `other_notes` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `store_quality_images`

**Rows:** 4,156 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `store_quality_id` | int(11) | NO |  | NULL |  |
| `filename` | mediumtext | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `store_quality_score_checklist`

**Rows:** 39 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `type` | varchar(45) | NO |  | select |  |
| `status` | varchar(45) | NO |  | 1 |  |
| `input_type` | varchar(45) | NO |  | select |  |
| `name` | varchar(45) | NO |  | NULL |  |
| `desc` | mediumtext | YES |  | NULL |  |
| `score_points` | int(11) | NO |  | 1 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `store_quality_scores`

**Rows:** 10,990 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `store_quality_id` | int(11) | NO |  | NULL |  |
| `score_id` | int(11) | NO |  | NULL |  |
| `rating` | varchar(45) | NO |  | NULL |  |
| `notes` | mediumtext | YES |  | NULL |  |
| `not_applicable` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `supplier_activity_log`

**Rows:** 4 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `supplier_id` | varchar(100) | NO | MUL | NULL |  |
| `order_id` | int(11) | YES | MUL | NULL |  |
| `action_type` | enum('login','logout','tracking_updated','note_added','info_requested','order_viewed','report_generated','csv_exported') | NO | MUL | NULL |  |
| `action_details` | text | YES |  | NULL |  |
| `ip_address` | varchar(45) | YES |  | NULL |  |
| `user_agent` | varchar(255) | YES |  | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_supplier_id` | BTREE |  | supplier_id |
| `idx_order_id` | BTREE |  | order_id |
| `idx_action_type` | BTREE |  | action_type |
| `idx_created_at` | BTREE |  | created_at |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `supplier_activity_log_ibfk_1` | `order_id` | `vend_consignments`.`id` |

---

### `supplier_info_requests`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `supplier_id` | varchar(100) | NO | MUL | NULL |  |
| `order_id` | int(11) | NO | MUL | NULL |  |
| `request_message` | text | NO |  | NULL |  |
| `response_message` | text | YES |  | NULL |  |
| `status` | enum('pending','answered','closed') | YES | MUL | pending |  |
| `responded_by` | int(11) | YES |  | NULL |  |
| `responded_at` | timestamp | YES |  | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_supplier_id` | BTREE |  | supplier_id |
| `idx_order_id` | BTREE |  | order_id |
| `idx_status` | BTREE |  | status |
| `idx_created_at` | BTREE |  | created_at |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `supplier_info_requests_ibfk_1` | `order_id` | `vend_consignments`.`id` |

---

### `supplier_portal_notifications`

**Rows:** 8 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) | NO | PRI | NULL | auto_increment |
| `supplier_id` | varchar(100) | NO | MUL | NULL |  |
| `type` | varchar(50) | NO | MUL | NULL |  |
| `title` | varchar(255) | NO |  | NULL |  |
| `message` | text | YES |  | NULL |  |
| `link` | varchar(255) | YES |  | NULL |  |
| `related_type` | varchar(50) | YES | MUL | NULL |  |
| `related_id` | varchar(100) | YES | MUL | NULL |  |
| `is_read` | tinyint(4) | YES |  | 0 |  |
| `read_at` | timestamp | YES |  | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_supplier_unread` | BTREE |  | supplier_id, is_read, created_at |
| `idx_type` | BTREE |  | type |
| `idx_created` | BTREE |  | created_at |
| `idx_related_type` | BTREE |  | related_type |
| `idx_related_id` | BTREE |  | related_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `supplier_portal_notifications_ibfk_1` | `supplier_id` | `vend_suppliers`.`id` |

---

### `supplier_portal_sessions`

**Rows:** 3 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `supplier_id` | varchar(100) | NO | MUL | NULL |  |
| `session_token` | varchar(64) | NO | UNI | NULL |  |
| `ip_address` | varchar(45) | YES |  | NULL |  |
| `user_agent` | varchar(255) | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `expires_at` | timestamp | NO | MUL | 0000-00-00 00:00:00 |  |
| `last_activity` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `session_token` | BTREE | ✓ | session_token |
| `idx_session_token` | BTREE |  | session_token |
| `idx_supplier_active` | BTREE |  | supplier_id, expires_at |
| `idx_expires` | BTREE |  | expires_at |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `supplier_portal_sessions_ibfk_1` | `supplier_id` | `vend_suppliers`.`id` |

---

### `supplier_warranty_notes`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `fault_id` | int(11) | NO | MUL | NULL |  |
| `supplier_id` | varchar(100) | NO | MUL | NULL |  |
| `note` | text | NO |  | NULL |  |
| `action_taken` | varchar(50) | YES | MUL | NULL |  |
| `internal_ref` | varchar(100) | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_fault` | BTREE |  | fault_id, created_at |
| `idx_supplier` | BTREE |  | supplier_id, created_at |
| `idx_action` | BTREE |  | action_taken |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `supplier_warranty_notes_ibfk_1` | `fault_id` | `faulty_products`.`id` |
| `supplier_warranty_notes_ibfk_2` | `supplier_id` | `vend_suppliers`.`id` |

---

### `support_desk`

**Rows:** 153 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `user_id` | int(10) unsigned | NO |  | NULL |  |
| `summary` | varchar(180) | NO |  | NULL |  |
| `description` | text | NO |  | NULL |  |
| `impact` | varchar(32) | NO |  | NULL |  |
| `status` | enum('open','in_progress','resolved','closed') | YES | MUL | open |  |
| `priority` | int(1) unsigned | NO |  | 2 |  |
| `ai_summary` | text | YES |  | NULL |  |
| `clarify_log` | text | YES |  | NULL |  |
| `ticket_type_id` | int(10) unsigned | NO | MUL | 1 |  |
| `attachments` | text | YES |  | NULL |  |
| `created_at` | datetime | YES |  | current_timestamp() |  |
| `updated_at` | datetime | YES |  | current_timestamp() | on update current_timestamp() |
| `reference_url` | varchar(255) | YES |  | NULL |  |
| `system_id` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_status` | BTREE |  | status |
| `idx_type` | BTREE |  | ticket_type_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_desk_type` | `ticket_type_id` | `support_desk_types`.`ticket_type_id` |

---

### `support_desk_activity`

**Rows:** 33 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) | NO | PRI | NULL | auto_increment |
| `ticket_id` | int(10) unsigned | NO | MUL | NULL |  |
| `user_id` | int(10) unsigned | YES | MUL | NULL |  |
| `event_type` | varchar(64) | NO |  | NULL |  |
| `event_details` | text | YES |  | NULL |  |
| `created_at` | datetime | NO | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_ticket_id` | BTREE |  | ticket_id |
| `idx_user_id` | BTREE |  | user_id |
| `idx_ticket` | BTREE |  | ticket_id |
| `idx_created` | BTREE |  | created_at |

---

### `support_desk_comments`

**Rows:** 5 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `ticket_id` | int(10) unsigned | NO | MUL | NULL |  |
| `user_id` | int(10) unsigned | NO | MUL | NULL |  |
| `username` | varchar(128) | NO |  | NULL |  |
| `comment` | text | NO |  | NULL |  |
| `is_internal` | tinyint(1) | YES |  | 0 |  |
| `attachments` | text | YES |  | NULL |  |
| `created_at` | datetime | YES |  | current_timestamp() |  |
| `deleted` | tinyint(1) | YES |  | 0 |  |
| `deleted_at` | datetime | YES |  | NULL |  |
| `is_announcement` | tinyint(1) | NO | MUL | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_ticket` | BTREE |  | ticket_id |
| `user_id` | BTREE |  | user_id |
| `idx_is_announcement` | BTREE |  | is_announcement |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_comments_ticket` | `ticket_id` | `support_desk`.`id` |

---

### `support_desk_files`

**Rows:** 18 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `ticket_id` | int(10) unsigned | NO | MUL | NULL |  |
| `comment_id` | int(10) unsigned | YES | MUL | NULL |  |
| `filename` | varchar(255) | NO |  | NULL |  |
| `filepath` | varchar(255) | NO |  | NULL |  |
| `uploaded_by` | int(10) unsigned | YES |  | NULL |  |
| `uploaded_at` | datetime | YES |  | current_timestamp() |  |
| `filetype` | varchar(128) | YES |  | NULL |  |
| `filesize` | int(10) unsigned | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_ticket` | BTREE |  | ticket_id |
| `idx_comment` | BTREE |  | comment_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_files_comment` | `comment_id` | `support_desk_comments`.`id` |
| `fk_files_ticket` | `ticket_id` | `support_desk`.`id` |

---

### `support_desk_types`

**Rows:** 5 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `ticket_type_id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `label` | varchar(40) | NO |  | NULL |  |
| `is_enabled` | tinyint(1) | YES |  | 1 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | ticket_type_id |

---

### `surcharges`

**Rows:** 4 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `surcharge_id` | int(11) | NO | PRI | NULL | auto_increment |
| `carrier_id` | int(11) | NO | MUL | NULL |  |
| `code` | varchar(64) | NO |  | NULL |  |
| `name` | varchar(150) | NO |  | NULL |  |
| `price` | decimal(10,2) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | surcharge_id |
| `uniq_surcharge` | BTREE | ✓ | carrier_id, code |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_surcharge_carrier` | `carrier_id` | `carriers`.`carrier_id` |

---

### `system_alerts`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `severity` | enum('info','warning','critical','emergency') | YES |  | NULL |  |
| `title` | varchar(255) | YES |  | NULL |  |
| `message` | mediumtext | YES |  | NULL |  |
| `data` | longtext | YES |  | NULL |  |
| `resolved_at` | timestamp | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `system_audit_log`

**Rows:** 5 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `user_id` | varchar(50) | YES | MUL | NULL |  |
| `action` | varchar(100) | NO | MUL | NULL |  |
| `entity_type` | varchar(50) | NO | MUL | NULL |  |
| `entity_id` | varchar(50) | YES |  | NULL |  |
| `old_values` | longtext | YES |  | NULL |  |
| `new_values` | longtext | YES |  | NULL |  |
| `ip_address` | varchar(45) | YES |  | NULL |  |
| `user_agent` | text | YES |  | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_user` | BTREE |  | user_id |
| `idx_action` | BTREE |  | action |
| `idx_entity` | BTREE |  | entity_type, entity_id |
| `idx_created` | BTREE |  | created_at |
| `idx_audit_action_date` | BTREE |  | action, created_at |

---

### `system_event_log`

**Rows:** 595 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `event_type` | varchar(64) | YES | MUL | NULL |  |
| `event_data` | mediumtext | YES |  | NULL |  |
| `source_module` | varchar(64) | YES |  | NULL |  |
| `actor_type` | enum('user','system','api') | YES | MUL | NULL |  |
| `actor_id` | varchar(64) | YES |  | NULL |  |
| `target_type` | varchar(64) | YES |  | NULL |  |
| `target_id` | varchar(64) | YES |  | NULL |  |
| `summary` | text | YES |  | NULL |  |
| `details_json` | longtext | YES |  | NULL |  |
| `severity` | enum('info','success','warning','error','critical') | NO | MUL | info |  |
| `created_at` | datetime | YES | MUL | NULL |  |
| `run_id` | varchar(64) | YES | MUL | NULL | STORED GENERATED |
| `description` | text | YES |  | NULL |  |
| `created_by_staff` | int(11) | YES |  | NULL |  |
| `metadata` | longtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_type` | BTREE |  | event_type |
| `idx_severity` | BTREE |  | severity |
| `idx_created` | BTREE |  | created_at |
| `ix_sel_created` | BTREE |  | created_at |
| `ix_sel_sev_created` | BTREE |  | severity, created_at |
| `ix_sel_type_created` | BTREE |  | event_type, created_at |
| `ix_sel_id` | BTREE |  | id |
| `ix_sel_run_created` | BTREE |  | run_id, created_at |
| `idx_system_event_log_monitoring` | BTREE |  | event_type, created_at, severity |
| `idx_system_event_created` | BTREE |  | created_at |
| `idx_system_event_type` | BTREE |  | event_type, severity |
| `idx_system_event_actor` | BTREE |  | actor_type, actor_id |

---

### `system_health_logs`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `check_type` | varchar(50) | NO | MUL | NULL |  |
| `alerts_triggered` | int(11) | YES | MUL | 0 |  |
| `checks_performed` | int(11) | YES |  | 0 |  |
| `results_data` | longtext | YES |  | NULL |  |
| `created_at` | timestamp | YES | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_check_type` | BTREE |  | check_type |
| `idx_created_at` | BTREE |  | created_at |
| `idx_alerts_triggered` | BTREE |  | alerts_triggered |

---

### `system_heartbeat`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `status` | enum('HEALTHY','DEGRADED','CRITICAL','EMERGENCY') | YES |  | HEALTHY |  |
| `health_data` | longtext | YES |  | NULL |  |
| `timestamp` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `system_profiling_log`

**Rows:** 21 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `session_id` | varchar(64) | YES |  | NULL |  |
| `user_id` | int(11) | YES | MUL | NULL |  |
| `endpoint` | varchar(255) | YES | MUL | NULL |  |
| `php_time_ms` | int(11) | YES |  | NULL |  |
| `sql_time_ms` | int(11) | YES |  | NULL |  |
| `sql_count` | int(11) | YES |  | NULL |  |
| `memory_mb` | decimal(6,2) | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_endpoint` | BTREE |  | endpoint, created_at |
| `idx_user` | BTREE |  | user_id, created_at |

---

### `system_status_snapshots`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `timestamp` | datetime | YES |  | current_timestamp() |  |
| `status_type` | varchar(50) | YES |  | general |  |
| `status_data` | longtext | YES |  | NULL |  |
| `source_system` | varchar(100) | YES |  | transfers |  |
| `created_at` | datetime | YES |  | current_timestamp() |  |
| `updated_at` | datetime | YES |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `systems`

**Rows:** 4 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `name` | varchar(128) | NO |  | NULL |  |
| `description` | text | YES |  | NULL |  |
| `active` | tinyint(1) | NO |  | 1 |  |
| `default` | tinyint(1) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `timesheet_amendments`

**Rows:** 1,260 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `staff_id` | int(11) | NO |  | NULL |  |
| `vend_outlet_id` | varchar(45) | NO |  | NULL |  |
| `actual_start_time` | timestamp | YES |  | NULL |  |
| `actual_end_time` | timestamp | YES |  | NULL |  |
| `explanation` | mediumtext | YES |  | NULL |  |
| `deleted_at` | timestamp | YES |  | NULL |  |
| `status` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `tool_calls`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `tool_call_id` | char(36) | NO | PRI | NULL |  |
| `message_id` | char(36) | NO | MUL | NULL |  |
| `tool_name` | varchar(128) | NO | MUL | NULL |  |
| `function_name` | varchar(128) | YES |  | NULL |  |
| `arguments` | longtext | NO |  | NULL |  |
| `result` | longtext | YES |  | NULL |  |
| `status` | enum('pending','completed','failed') | NO | MUL | pending |  |
| `duration_ms` | int(11) | YES | MUL | NULL |  |
| `created_at` | datetime | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | tool_call_id |
| `idx_tool_msg` | BTREE |  | message_id, created_at |
| `idx_tool_name_status` | BTREE |  | tool_name, status, created_at |
| `idx_tool_status` | BTREE |  | status, created_at |
| `idx_tool_duration` | BTREE |  | duration_ms, tool_name |
| `idx_tool_calls_tool_created` | BTREE |  | tool_name, created_at |
| `idx_tool_calls_message_created` | BTREE |  | message_id, created_at |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_tool_msg` | `message_id` | `messages`.`message_id` |

---

### `traffic_logs`

**Rows:** 5,348 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) | NO | PRI | NULL | auto_increment |
| `ip_address` | varchar(45) | NO | MUL | NULL |  |
| `user_agent` | text | YES |  | NULL |  |
| `request_uri` | varchar(512) | NO | MUL | NULL |  |
| `request_method` | varchar(10) | NO |  | GET |  |
| `response_status` | int(11) | NO | MUL | 200 |  |
| `response_time_ms` | int(11) | NO |  | 0 |  |
| `memory_usage_mb` | decimal(10,2) | YES |  | 0.00 |  |
| `country_code` | varchar(10) | YES |  | NULL |  |
| `is_bot` | tinyint(1) | YES | MUL | 0 |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_created_at` | BTREE |  | created_at |
| `idx_status` | BTREE |  | response_status |
| `idx_uri` | BTREE |  | request_uri |
| `idx_ip` | BTREE |  | ip_address |
| `idx_bot` | BTREE |  | is_bot |

---

### `transaction_rules`

**Rows:** 3,665 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `match_type` | enum('contact','reference','description','pattern','ai') | NO |  | pattern |  |
| `match_value` | varchar(255) | YES |  | NULL |  |
| `bankAccountID` | varchar(100) | YES |  | NULL |  |
| `ledgerAccountID` | varchar(100) | YES |  | NULL |  |
| `GSTCode` | varchar(45) | YES |  | NULL |  |
| `confidence_score` | float | YES |  | 1 |  |
| `ai_generated` | tinyint(1) | YES |  | 0 |  |
| `usage_count` | int(11) | YES |  | 0 |  |
| `last_used` | timestamp | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `approval_count` | int(11) | YES |  | 0 |  |
| `rejection_count` | int(11) | YES |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `transfer_ai_insights`

**Rows:** 112 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `transfer_id` | int(11) | NO | MUL | NULL |  |
| `insight_text` | text | NO |  | NULL |  |
| `insight_json` | longtext | YES |  | NULL |  |
| `insight_type` | varchar(50) | YES |  | general |  |
| `priority` | varchar(20) | YES | MUL | medium |  |
| `confidence_score` | decimal(3,2) | YES |  | 0.85 |  |
| `model_provider` | varchar(50) | NO |  | NULL |  |
| `model_name` | varchar(100) | NO |  | NULL |  |
| `tokens_used` | int(11) | YES |  | 0 |  |
| `processing_time_ms` | int(11) | YES |  | 0 |  |
| `generated_at` | datetime | NO | MUL | NULL |  |
| `expires_at` | datetime | NO | MUL | NULL |  |
| `created_by` | int(11) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_transfer_id` | BTREE |  | transfer_id |
| `idx_generated_at` | BTREE |  | generated_at |
| `idx_expires_at` | BTREE |  | expires_at |
| `idx_priority` | BTREE |  | priority |
| `idx_transfer_fresh` | BTREE |  | transfer_id, expires_at |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `transfer_ai_insights_ibfk_1` | `transfer_id` | `vend_consignments`.`id` |

---

### `transfer_alert_rules`

**Rows:** 4 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `rule_name` | varchar(100) | NO | UNI | NULL |  |
| `category` | varchar(50) | NO |  | NULL |  |
| `event_type` | varchar(100) | YES |  | NULL |  |
| `severity` | enum('debug','info','notice','warning','error','critical','alert','emergency') | NO |  | NULL |  |
| `threshold_count` | int(10) unsigned | NO |  | 1 |  |
| `threshold_window_min` | int(10) unsigned | NO |  | 5 |  |
| `alert_method` | enum('email','slack','webhook','sms') | NO |  | email |  |
| `alert_recipients` | longtext | NO |  | NULL |  |
| `alert_message_template` | text | YES |  | NULL |  |
| `is_active` | tinyint(1) | NO | MUL | 1 |  |
| `last_triggered_at` | timestamp | YES |  | NULL |  |
| `trigger_count` | int(10) unsigned | NO |  | 0 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `rule_name` | BTREE | ✓ | rule_name |
| `idx_active` | BTREE |  | is_active, category, severity |

---

### `transfer_audit_log`

**Rows:** 30,560 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `transaction_id` | varchar(50) | YES | MUL | NULL |  |
| `entity_type` | enum('transfer','po') | NO | MUL | transfer |  |
| `entity_pk` | int(11) | YES |  | NULL |  |
| `transfer_pk` | int(11) | YES | MUL | NULL |  |
| `transfer_id` | varchar(100) | YES | MUL | NULL |  |
| `vend_consignment_id` | varchar(100) | YES | MUL | NULL |  |
| `vend_transfer_id` | char(36) | YES | MUL | NULL |  |
| `action` | varchar(100) | NO | MUL | NULL |  |
| `operation_type` | varchar(50) | YES | MUL | NULL |  |
| `status` | varchar(50) | NO | MUL | NULL |  |
| `actor_type` | enum('system','user','api','cron','webhook') | NO | MUL | NULL |  |
| `actor_id` | varchar(100) | YES |  | NULL |  |
| `user_id` | int(11) | YES | MUL | NULL |  |
| `outlet_from` | varchar(100) | YES | MUL | NULL |  |
| `outlet_to` | varchar(100) | YES |  | NULL |  |
| `data_before` | longtext | YES |  | NULL |  |
| `data_after` | longtext | YES |  | NULL |  |
| `error_message` | text | YES |  | NULL |  |
| `rollback_details` | longtext | YES |  | NULL |  |
| `duration_seconds` | decimal(10,3) | YES | MUL | NULL |  |
| `metadata` | longtext | YES |  | NULL |  |
| `error_details` | longtext | YES |  | NULL |  |
| `processing_time_ms` | int(10) unsigned | YES |  | NULL |  |
| `api_response` | longtext | YES |  | NULL |  |
| `session_id` | varchar(255) | YES |  | NULL |  |
| `ip_address` | varchar(45) | YES |  | NULL |  |
| `user_agent` | text | YES |  | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |
| `completed_at` | datetime | YES | MUL | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_transfer_id` | BTREE |  | transfer_id |
| `idx_vend_consignment` | BTREE |  | vend_consignment_id |
| `idx_action_status` | BTREE |  | action, status |
| `idx_actor` | BTREE |  | actor_type, actor_id |
| `idx_outlet_from_to` | BTREE |  | outlet_from, outlet_to |
| `idx_created_at` | BTREE |  | created_at |
| `idx_error_tracking` | BTREE |  | status, created_at |
| `idx_transfer_pk` | BTREE |  | transfer_pk |
| `idx_vend_transfer` | BTREE |  | vend_transfer_id |
| `idx_entity` | BTREE |  | entity_type, entity_pk |
| `idx_audit_errors` | BTREE |  | status, created_at |
| `idx_tal_entity_action_time` | BTREE |  | entity_type, action, created_at |
| `idx_tal_transfer_time` | BTREE |  | transfer_id, created_at |
| `idx_tal_status_time` | BTREE |  | status, created_at |
| `idx_tal_actor_time` | BTREE |  | actor_type, actor_id, created_at |
| `idx_audit_transfer_created` | BTREE |  | transfer_id, created_at |
| `idx_audit_action_created` | BTREE |  | action, created_at |
| `idx_transaction_id` | BTREE |  | transaction_id |
| `idx_duration` | BTREE |  | duration_seconds |
| `idx_completed_at` | BTREE |  | completed_at |
| `idx_operation_type` | BTREE |  | operation_type |
| `idx_user_id` | BTREE |  | user_id |
| `idx_audit_transfer_id` | BTREE |  | transfer_id |
| `idx_audit_created` | BTREE |  | created_at |
| `idx_audit_action` | BTREE |  | action |
| `idx_audit_transfer_action` | BTREE |  | transfer_id, action, created_at |

---

### `transfer_config`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `config_key` | varchar(100) | NO | PRI | NULL |  |
| `config_value` | text | YES |  | NULL |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | config_key |

---

### `transfer_configurations`

**Rows:** 5 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `name` | varchar(100) | NO | UNI | NULL |  |
| `description` | text | YES |  | NULL |  |
| `allocation_method` | tinyint(1) | NO |  | 1 |  |
| `power_factor` | decimal(4,2) | NO |  | 2.00 |  |
| `min_allocation_pct` | decimal(5,2) | NO |  | 5.00 |  |
| `max_allocation_pct` | decimal(5,2) | NO |  | 50.00 |  |
| `rounding_method` | tinyint(1) | NO |  | 0 |  |
| `is_preset` | tinyint(1) | NO | MUL | 0 |  |
| `is_active` | tinyint(1) | NO | MUL | 1 |  |
| `enable_safety_checks` | tinyint(1) | NO |  | 1 |  |
| `enable_logging` | tinyint(1) | NO |  | 1 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `created_by` | varchar(50) | YES |  | NULL |  |
| `updated_by` | varchar(50) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uk_name` | BTREE | ✓ | name |
| `idx_preset` | BTREE |  | is_preset |
| `idx_active` | BTREE |  | is_active |

---

### `transfer_idempotency`

**Rows:** 436 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `idem_key` | varchar(128) | NO | PRI | NULL |  |
| `idem_hash` | char(64) | NO | MUL | NULL |  |
| `request_payload` | longtext | YES |  | NULL |  |
| `transfer_id` | int(10) unsigned | YES |  | NULL |  |
| `operation_type` | varchar(64) | YES |  | NULL |  |
| `vend_id` | varchar(64) | YES |  | NULL |  |
| `vend_number` | varchar(64) | YES |  | NULL |  |
| `response_json` | longtext | YES |  | NULL |  |
| `status_code` | smallint(6) | YES |  | NULL |  |
| `created_at` | timestamp | YES |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | idem_key |
| `idx_idem_hash` | BTREE |  | idem_hash |

---

### `transfer_logs`

**Rows:** 3,426 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) | NO | PRI | NULL | auto_increment |
| `transfer_id` | int(11) | YES | MUL | NULL |  |
| `shipment_id` | int(11) | YES | MUL | NULL |  |
| `item_id` | int(11) | YES | MUL | NULL |  |
| `parcel_id` | int(11) | YES | MUL | NULL |  |
| `staff_transfer_id` | int(10) unsigned | YES | MUL | NULL |  |
| `event_type` | varchar(100) | NO | MUL | NULL |  |
| `event_data` | longtext | YES |  | NULL |  |
| `actor_user_id` | int(11) | YES |  | NULL |  |
| `actor_role` | varchar(50) | YES |  | NULL |  |
| `severity` | enum('info','warning','error','critical') | YES |  | info |  |
| `source_system` | varchar(50) | NO | MUL | CIS |  |
| `trace_id` | varchar(64) | YES | MUL | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `customer_id` | varchar(45) | YES | MUL | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_logs_transfer` | BTREE |  | transfer_id, created_at |
| `idx_logs_shipment` | BTREE |  | shipment_id, created_at |
| `idx_logs_item` | BTREE |  | item_id, created_at |
| `idx_logs_parcel` | BTREE |  | parcel_id, created_at |
| `idx_logs_staff` | BTREE |  | staff_transfer_id, created_at |
| `idx_logs_event` | BTREE |  | event_type, created_at |
| `idx_logs_customer` | BTREE |  | customer_id |
| `idx_tl_transfer_type_time` | BTREE |  | transfer_id, event_type, created_at |
| `idx_tl_trace` | BTREE |  | trace_id |
| `idx_tl_source_severity_time` | BTREE |  | source_system, severity, created_at |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_logs_customer` | `customer_id` | `vend_customers`.`id` |
| `fk_logs_item` | `item_id` | `vend_consignment_line_items`.`id` |
| `fk_logs_parcel` | `parcel_id` | `transfer_parcels`.`id` |
| `fk_logs_shipment` | `shipment_id` | `transfer_shipments`.`id` |
| `fk_logs_staff` | `staff_transfer_id` | `staff_transfers`.`id` |
| `fk_logs_transfer` | `transfer_id` | `vend_consignments`.`id` |

---

### `transfer_metrics`

**Rows:** 40 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `transfer_id` | int(11) | YES | MUL | NULL |  |
| `source_outlet_id` | int(11) | YES | MUL | NULL |  |
| `destination_outlet_id` | int(11) | YES | MUL | NULL |  |
| `total_items` | int(11) | YES |  | 0 |  |
| `total_quantity` | int(11) | YES |  | 0 |  |
| `status` | varchar(50) | YES | MUL | pending |  |
| `processing_time_ms` | int(11) | YES |  | 0 |  |
| `api_calls_made` | int(11) | YES |  | 0 |  |
| `cost_calculated` | decimal(10,2) | YES |  | 0.00 |  |
| `created_at` | datetime | NO | MUL | NULL |  |
| `metadata` | longtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_transfer_id` | BTREE |  | transfer_id |
| `idx_source_outlet` | BTREE |  | source_outlet_id |
| `idx_destination_outlet` | BTREE |  | destination_outlet_id |
| `idx_status` | BTREE |  | status |
| `idx_created_at` | BTREE |  | created_at |
| `idx_date_status` | BTREE |  | created_at, status |

---

### `transfer_notes`

**Rows:** 2,908 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `transfer_id` | int(11) | NO | MUL | NULL |  |
| `note_text` | mediumtext | NO |  | NULL |  |
| `created_by` | int(11) | NO |  | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |
| `updated_at` | timestamp | YES |  | NULL | on update current_timestamp() |
| `deleted_at` | timestamp | YES | MUL | NULL |  |
| `deleted_by` | int(11) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_note_created` | BTREE |  | created_at |
| `idx_note_deleted` | BTREE |  | deleted_at |
| `idx_notes_transfer_created` | BTREE |  | transfer_id, created_at |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `transfer_notes_ibfk_1` | `transfer_id` | `vend_consignments`.`id` |

---

### `transfer_notifications`

**Rows:** 22 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `transaction_id` | varchar(50) | YES | MUL | NULL |  |
| `transfer_id` | int(11) | YES | MUL | NULL |  |
| `notification_type` | varchar(30) | NO | MUL | NULL |  |
| `severity` | enum('LOW','MEDIUM','HIGH','CRITICAL') | NO | MUL | MEDIUM |  |
| `title` | varchar(200) | NO |  | NULL |  |
| `message` | text | NO |  | NULL |  |
| `data` | longtext | YES |  | NULL |  |
| `created_at` | datetime | NO | MUL | current_timestamp() |  |
| `acknowledged_at` | datetime | YES | MUL | NULL |  |
| `acknowledged_by` | int(11) | YES |  | NULL |  |
| `requires_action` | tinyint(1) | YES | MUL | 0 |  |
| `action_taken` | text | YES |  | NULL |  |
| `resolved_at` | datetime | YES |  | NULL |  |
| `resolved_by` | int(11) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_transaction_id` | BTREE |  | transaction_id |
| `idx_transfer_id` | BTREE |  | transfer_id |
| `idx_notification_type` | BTREE |  | notification_type |
| `idx_severity` | BTREE |  | severity |
| `idx_requires_action` | BTREE |  | requires_action |
| `idx_created_at` | BTREE |  | created_at |
| `idx_acknowledged_at` | BTREE |  | acknowledged_at |

---

### `transfer_pack_locks`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `transfer_id` | int(10) unsigned | NO | PRI | NULL |  |
| `user_id` | int(10) unsigned | NO | MUL | NULL |  |
| `acquired_at` | datetime | NO |  | current_timestamp() |  |
| `expires_at` | datetime | NO | MUL | NULL |  |
| `heartbeat_at` | datetime | NO |  | current_timestamp() |  |
| `client_fingerprint` | varchar(255) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | transfer_id |
| `expires_at` | BTREE |  | expires_at |
| `user_id` | BTREE |  | user_id |

---

### `transfer_parcel_items`

**Rows:** 47,531 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `parcel_id` | int(11) | NO | MUL | NULL |  |
| `item_id` | int(11) | NO | MUL | NULL |  |
| `qty_received` | int(11) | NO |  | 0 |  |
| `locked_at` | timestamp | YES |  | current_timestamp() |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `qty` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uniq_parcel_item` | BTREE | ✓ | parcel_id, item_id |
| `idx_tpi_parcel` | BTREE |  | parcel_id |
| `idx_tpi_item` | BTREE |  | item_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_tpi_item` | `item_id` | `vend_consignment_line_items`.`id` |
| `fk_tpi_parcel` | `parcel_id` | `transfer_parcels`.`id` |

---

### `transfer_parcels`

**Rows:** 8,124 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `shipment_id` | int(11) | NO | MUL | NULL |  |
| `box_number` | int(11) | NO |  | NULL |  |
| `tracking_number` | varchar(191) | YES | MUL | NULL |  |
| `tracking_ref_raw` | text | YES |  | NULL |  |
| `courier` | varchar(50) | YES |  | NULL |  |
| `weight_grams` | int(10) unsigned | YES |  | NULL |  |
| `length_mm` | int(10) unsigned | YES |  | NULL |  |
| `width_mm` | int(10) unsigned | YES |  | NULL |  |
| `height_mm` | int(10) unsigned | YES |  | NULL |  |
| `weight_kg` | decimal(10,2) | YES |  | NULL |  |
| `label_url` | varchar(255) | YES |  | NULL |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `deleted_by` | int(11) | YES |  | NULL |  |
| `deleted_at` | timestamp | YES |  | NULL |  |
| `status` | enum('pending','labelled','manifested','in_transit','received','missing','damaged','cancelled','exception') | NO | MUL | pending |  |
| `notes` | mediumtext | YES |  | NULL |  |
| `received_at` | timestamp | YES |  | NULL |  |
| `created_at` | datetime | NO |  | current_timestamp() |  |
| `parcel_number` | int(11) | NO |  | 1 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uniq_parcel_boxnum` | BTREE | ✓ | shipment_id, box_number |
| `uq_parcel_box` | BTREE | ✓ | shipment_id, box_number |
| `idx_parcel_shipment` | BTREE |  | shipment_id |
| `idx_parcel_tracking` | BTREE |  | tracking_number |
| `idx_parcels_shipment_box` | BTREE |  | shipment_id, box_number |
| `idx_parcel_status_time` | BTREE |  | status, updated_at |
| `idx_parcel_shipment_status` | BTREE |  | shipment_id, status |
| `idx_parcels_shipment_updated` | BTREE |  | shipment_id, updated_at |
| `idx_parcels_shipment_id` | BTREE |  | shipment_id |
| `idx_parcels_tracking` | BTREE |  | tracking_number |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_parcels_shipment` | `shipment_id` | `transfer_shipments`.`id` |

---

### `transfer_performance_metrics`

**Rows:** 13 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `metric_date` | date | NO | MUL | NULL |  |
| `metric_hour` | tinyint(3) unsigned | YES |  | NULL |  |
| `category` | varchar(50) | NO | MUL | NULL |  |
| `operation` | varchar(100) | NO | MUL | NULL |  |
| `total_operations` | int(10) unsigned | NO |  | 0 |  |
| `total_duration_ms` | bigint(20) unsigned | NO |  | 0 |  |
| `avg_duration_ms` | int(10) unsigned | NO |  | 0 |  |
| `p50_duration_ms` | int(10) unsigned | YES |  | NULL |  |
| `p95_duration_ms` | int(10) unsigned | YES |  | NULL |  |
| `p99_duration_ms` | int(10) unsigned | YES |  | NULL |  |
| `success_count` | int(10) unsigned | NO |  | 0 |  |
| `error_count` | int(10) unsigned | NO |  | 0 |  |
| `error_rate` | decimal(5,4) | YES | MUL | NULL | STORED GENERATED |
| `ai_decisions` | int(10) unsigned | NO |  | 0 |  |
| `ai_avg_confidence` | decimal(5,4) | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_metric` | BTREE | ✓ | metric_date, metric_hour, category, operation |
| `idx_date` | BTREE |  | metric_date |
| `idx_category` | BTREE |  | category, metric_date |
| `idx_error_rate` | BTREE |  | error_rate, metric_date |
| `idx_tpm_op_date` | BTREE |  | operation, metric_date |

---

### `transfer_queue_log`

**Rows:** 40 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `trace_id` | varchar(100) | NO | MUL | NULL |  |
| `queue_name` | varchar(100) | NO | MUL | NULL |  |
| `operation` | varchar(50) | NO |  | NULL |  |
| `transfer_id` | int(10) unsigned | YES | MUL | NULL |  |
| `vend_consignment_id` | varchar(100) | YES | MUL | NULL |  |
| `idempotency_key` | varchar(255) | YES | MUL | NULL |  |
| `transaction_id` | varchar(50) | YES | MUL | NULL |  |
| `correlation_id` | varchar(50) | YES | MUL | NULL |  |
| `attempt_number` | int(10) unsigned | NO |  | 1 |  |
| `max_attempts` | int(10) unsigned | NO |  | 3 |  |
| `retry_delay_sec` | int(10) unsigned | YES |  | NULL |  |
| `next_retry_at` | timestamp | YES | MUL | NULL |  |
| `request_payload` | longtext | YES |  | NULL |  |
| `response_data` | longtext | YES |  | NULL |  |
| `error_message` | text | YES |  | NULL |  |
| `error_code` | varchar(50) | YES |  | NULL |  |
| `http_status` | int(10) unsigned | YES |  | NULL |  |
| `processing_ms` | int(10) unsigned | YES |  | NULL |  |
| `api_latency_ms` | int(10) unsigned | YES |  | NULL |  |
| `status` | enum('pending','processing','completed','failed','cancelled') | NO | MUL | NULL |  |
| `priority` | tinyint(3) unsigned | NO | MUL | 5 |  |
| `heartbeat_at` | datetime | YES | MUL | NULL |  |
| `worker_id` | varchar(50) | YES | MUL | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `completed_at` | timestamp | YES |  | NULL |  |
| `error_details` | longtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_trace` | BTREE |  | trace_id |
| `idx_queue` | BTREE |  | queue_name, status, priority, created_at |
| `idx_transfer` | BTREE |  | transfer_id |
| `idx_vend` | BTREE |  | vend_consignment_id |
| `idx_idempotency` | BTREE |  | idempotency_key |
| `idx_retry` | BTREE |  | next_retry_at, status |
| `idx_status` | BTREE |  | status, created_at |
| `idx_queue_retry_status` | BTREE |  | next_retry_at, status, priority |
| `idx_transaction_id` | BTREE |  | transaction_id |
| `idx_correlation_id` | BTREE |  | correlation_id |
| `idx_priority` | BTREE |  | priority |
| `idx_heartbeat` | BTREE |  | heartbeat_at |
| `idx_worker_id` | BTREE |  | worker_id |

---

### `transfer_queue_metrics`

**Rows:** 8 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `metric_type` | varchar(100) | NO | MUL | NULL |  |
| `queue_name` | varchar(255) | NO | MUL | default |  |
| `job_type` | varchar(100) | YES |  | NULL |  |
| `value` | decimal(15,4) | NO |  | NULL |  |
| `unit` | varchar(50) | NO |  | NULL |  |
| `metadata` | longtext | YES |  | NULL |  |
| `outlet_from` | varchar(50) | YES | MUL | NULL |  |
| `outlet_to` | varchar(50) | YES |  | NULL |  |
| `worker_id` | varchar(255) | YES | MUL | NULL |  |
| `recorded_at` | timestamp | NO | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_metric_type_recorded` | BTREE |  | metric_type, recorded_at |
| `idx_queue_job_type` | BTREE |  | queue_name, job_type |
| `idx_outlet_metrics` | BTREE |  | outlet_from, outlet_to, recorded_at |
| `idx_worker_metrics` | BTREE |  | worker_id, recorded_at |
| `idx_cleanup_old_metrics` | BTREE |  | recorded_at |

---

### `transfer_receipt_items`

**Rows:** 30,599 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `receipt_id` | int(11) | NO | MUL | NULL |  |
| `transfer_item_id` | int(11) | NO | MUL | NULL |  |
| `qty_received` | int(11) | NO |  | 0 |  |
| `condition` | varchar(32) | YES |  | ok |  |
| `notes` | text | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `u_receipt_item` | BTREE | ✓ | receipt_id, transfer_item_id |
| `idx_tri_receipt` | BTREE |  | receipt_id |
| `idx_tri_item` | BTREE |  | transfer_item_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_tri_item` | `transfer_item_id` | `vend_consignment_line_items`.`id` |
| `fk_tri_receipt` | `receipt_id` | `transfer_receipts`.`id` |

---

### `transfer_receipts`

**Rows:** 581 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `transfer_id` | int(11) | NO | MUL | NULL |  |
| `received_by` | int(11) | YES |  | NULL |  |
| `received_at` | timestamp | YES |  | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_tr_transfer` | BTREE |  | transfer_id |
| `idx_tr_created` | BTREE |  | created_at |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_tr_transfer` | `transfer_id` | `vend_consignments`.`id` |

---

### `transfer_shipment_items`

**Rows:** 46,476 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `shipment_id` | int(11) | NO | MUL | NULL |  |
| `item_id` | int(11) | NO | MUL | NULL |  |
| `qty_sent` | int(11) | NO |  | NULL |  |
| `qty_received` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uniq_shipment_item` | BTREE | ✓ | shipment_id, item_id |
| `idx_tsi_shipment` | BTREE |  | shipment_id |
| `idx_tsi_item` | BTREE |  | item_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_tsi_item` | `item_id` | `vend_consignment_line_items`.`id` |
| `fk_tsi_shipment` | `shipment_id` | `transfer_shipments`.`id` |

---

### `transfer_shipment_notes`

**Rows:** 665 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `shipment_id` | int(11) | NO | MUL | NULL |  |
| `note_text` | mediumtext | NO |  | NULL |  |
| `created_by` | int(11) | NO |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_shipment` | BTREE |  | shipment_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_shipment_notes` | `shipment_id` | `transfer_shipments`.`id` |

---

### `transfer_shipments`

**Rows:** 11,907 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `transfer_id` | int(11) | NO | MUL | NULL |  |
| `delivery_mode` | enum('auto','manual','dropoff','pickup','courier','internal_drive') | NO | MUL | auto |  |
| `dest_name` | varchar(160) | YES |  | NULL |  |
| `dest_company` | varchar(160) | YES |  | NULL |  |
| `dest_addr1` | varchar(160) | YES |  | NULL |  |
| `dest_addr2` | varchar(160) | YES |  | NULL |  |
| `dest_suburb` | varchar(120) | YES |  | NULL |  |
| `dest_city` | varchar(120) | YES |  | NULL |  |
| `dest_postcode` | varchar(16) | YES |  | NULL |  |
| `dest_email` | varchar(190) | YES |  | NULL |  |
| `dest_phone` | varchar(50) | YES |  | NULL |  |
| `dest_instructions` | varchar(500) | YES |  | NULL |  |
| `status` | enum('packed','in_transit','partial','received','cancelled') | NO | MUL | packed |  |
| `packed_at` | timestamp | YES | MUL | NULL |  |
| `packed_by` | int(11) | YES |  | NULL |  |
| `received_at` | timestamp | YES | MUL | NULL |  |
| `created_at` | datetime | NO |  | current_timestamp() |  |
| `received_by` | int(11) | YES |  | NULL |  |
| `driver_staff_id` | int(11) | YES |  | NULL |  |
| `nicotine_in_shipment` | tinyint(1) | NO |  | 0 |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `deleted_by` | int(11) | YES |  | NULL |  |
| `deleted_at` | timestamp | YES |  | NULL |  |
| `carrier_name` | varchar(120) | YES |  | NULL |  |
| `tracking_number` | varchar(120) | YES | MUL | NULL |  |
| `tracking_url` | varchar(300) | YES |  | NULL |  |
| `dispatched_at` | datetime | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_shipments_transfer` | BTREE |  | transfer_id |
| `idx_shipments_status` | BTREE |  | status |
| `idx_shipments_mode` | BTREE |  | delivery_mode |
| `idx_shipments_packed_at` | BTREE |  | packed_at |
| `idx_shipments_received_at` | BTREE |  | received_at |
| `idx_shipments_transfer_id` | BTREE |  | transfer_id |
| `idx_shipments_tracking` | BTREE |  | tracking_number |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_shipments_transfer` | `transfer_id` | `vend_consignments`.`id` |

---

### `transfer_system_health`

**Rows:** 4 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `check_name` | varchar(50) | NO | MUL | NULL |  |
| `status` | enum('HEALTHY','WARNING','CRITICAL') | NO | MUL | HEALTHY |  |
| `response_time_ms` | int(11) | YES |  | NULL |  |
| `error_message` | text | YES |  | NULL |  |
| `checked_at` | datetime | NO | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_check_name` | BTREE |  | check_name |
| `idx_status` | BTREE |  | status |
| `idx_checked_at` | BTREE |  | checked_at |

---

### `transfer_transactions`

**Rows:** 28 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `transaction_id` | varchar(50) | NO | UNI | NULL |  |
| `transfer_id` | int(11) | NO | MUL | NULL |  |
| `operation_type` | varchar(20) | NO |  | NULL |  |
| `status` | enum('STARTED','COMMITTED','FAILED','ROLLED_BACK') | NO | MUL | STARTED |  |
| `started_at` | datetime | NO | MUL | NULL |  |
| `completed_at` | datetime | YES |  | NULL |  |
| `data_snapshot` | longtext | YES |  | NULL |  |
| `error_message` | text | YES |  | NULL |  |
| `user_id` | int(11) | YES | MUL | NULL |  |
| `session_id` | varchar(64) | YES |  | NULL |  |
| `ip_address` | varchar(45) | YES |  | NULL |  |
| `user_agent` | text | YES |  | NULL |  |
| `created_at` | datetime | YES |  | current_timestamp() |  |
| `updated_at` | datetime | YES |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `transaction_id` | BTREE | ✓ | transaction_id |
| `idx_transaction_id` | BTREE |  | transaction_id |
| `idx_transfer_id` | BTREE |  | transfer_id |
| `idx_status` | BTREE |  | status |
| `idx_started_at` | BTREE |  | started_at |
| `idx_user_id` | BTREE |  | user_id |

---

### `transfer_ui_sessions`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `transfer_id` | int(11) | NO | MUL | NULL |  |
| `user_id` | int(11) | NO |  | NULL |  |
| `state_json` | longtext | NO |  | NULL |  |
| `autosave_at` | timestamp | NO |  | current_timestamp() |  |
| `resumed_at` | timestamp | YES |  | NULL |  |
| `expires_at` | timestamp | YES | MUL | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uq_ui_transfer_user` | BTREE | ✓ | transfer_id, user_id |
| `idx_ui_expiry` | BTREE |  | expires_at |
| `idx_tuis_transfer_exp` | BTREE |  | transfer_id, expires_at |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_ui_transfer` | `transfer_id` | `vend_consignments`.`id` |

---

### `transfer_unified_log`

**Rows:** 1,405 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `trace_id` | varchar(100) | NO | MUL | NULL |  |
| `correlation_id` | varchar(100) | YES | MUL | NULL |  |
| `category` | varchar(50) | NO | MUL | NULL |  |
| `event_type` | varchar(100) | NO | MUL | NULL |  |
| `severity` | enum('debug','info','notice','warning','error','critical','alert','emergency') | NO | MUL | info |  |
| `message` | text | NO | MUL | NULL |  |
| `transfer_id` | int(10) unsigned | YES | MUL | NULL |  |
| `shipment_id` | int(10) unsigned | YES | MUL | NULL |  |
| `parcel_id` | int(10) unsigned | YES |  | NULL |  |
| `item_id` | int(10) unsigned | YES |  | NULL |  |
| `outlet_id` | varchar(50) | YES |  | NULL |  |
| `vend_consignment_id` | varchar(100) | YES | MUL | NULL |  |
| `vend_transfer_id` | varchar(100) | YES |  | NULL |  |
| `ai_decision_id` | bigint(20) unsigned | YES | MUL | NULL |  |
| `ai_model_version` | varchar(50) | YES |  | NULL |  |
| `ai_confidence` | decimal(5,4) | YES |  | NULL |  |
| `actor_user_id` | int(10) unsigned | YES | MUL | NULL |  |
| `actor_role` | varchar(50) | YES |  | NULL |  |
| `actor_ip` | varchar(45) | YES |  | NULL |  |
| `event_data` | longtext | YES |  | NULL |  |
| `context_data` | longtext | YES |  | NULL |  |
| `tags` | longtext | YES |  | NULL |  |
| `duration_ms` | int(10) unsigned | YES |  | NULL |  |
| `memory_mb` | decimal(10,2) | YES |  | NULL |  |
| `api_latency_ms` | int(10) unsigned | YES |  | NULL |  |
| `db_query_ms` | int(10) unsigned | YES |  | NULL |  |
| `source_system` | varchar(50) | NO |  | CIS |  |
| `environment` | enum('dev','staging','production') | NO |  | production |  |
| `server_name` | varchar(100) | YES |  | NULL |  |
| `php_version` | varchar(20) | YES |  | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |
| `event_timestamp` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_trace` | BTREE |  | trace_id |
| `idx_correlation` | BTREE |  | correlation_id |
| `idx_category_severity` | BTREE |  | category, severity, created_at |
| `idx_transfer` | BTREE |  | transfer_id, created_at |
| `idx_shipment` | BTREE |  | shipment_id |
| `idx_vend_consignment` | BTREE |  | vend_consignment_id |
| `idx_ai_decision` | BTREE |  | ai_decision_id |
| `idx_actor` | BTREE |  | actor_user_id, created_at |
| `idx_event_type` | BTREE |  | event_type, created_at |
| `idx_created` | BTREE |  | created_at |
| `idx_severity_created` | BTREE |  | severity, created_at |
| `idx_tul_transfer_created_conf` | BTREE |  | transfer_id, created_at, ai_confidence |
| `idx_message` | FULLTEXT |  | message |

---

### `transfers_backup_20251013`

**Rows:** 11,790 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `public_id` | varchar(40) | NO | UNI | NULL |  |
| `vend_transfer_id` | char(36) | YES | UNI | NULL |  |
| `consignment_id` | bigint(20) unsigned | YES | MUL | NULL |  |
| `vend_resource` | enum('consignment','purchase_order','transfer') | NO | MUL | NULL |  |
| `vend_number` | varchar(64) | YES | MUL | NULL |  |
| `vend_url` | varchar(255) | YES |  | NULL |  |
| `type` | enum('stock','juice','staff','purchase_order','return') | NO | MUL | stock |  |
| `status` | enum('draft','open','sent','partial','received','cancelled','archived') | NO | MUL | draft |  |
| `transfer_type` | enum('GENERAL','JUICE','STAFF','AUTOMATED') | YES |  | GENERAL |  |
| `outlet_from` | varchar(100) | NO | MUL | NULL |  |
| `outlet_to` | varchar(100) | NO | MUL | NULL |  |
| `created_by` | int(11) | NO |  | NULL |  |
| `staff_transfer_id` | int(10) unsigned | YES | MUL | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `deleted_by` | int(11) | YES |  | NULL |  |
| `deleted_at` | timestamp | YES |  | NULL |  |
| `customer_id` | varchar(45) | YES | MUL | NULL |  |
| `state` | enum('OPEN','PACKING','PACKAGED','SENT','RECEIVING','RECEIVED','CLOSED','CANCELLED') | NO | MUL | OPEN |  |
| `total_boxes` | int(10) unsigned | NO |  | 0 |  |
| `total_weight_g` | bigint(20) unsigned | NO |  | 0 |  |
| `draft_data` | longtext | YES |  | NULL |  |
| `draft_updated_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uniq_transfers_public_id` | BTREE | ✓ | public_id |
| `uniq_transfers_vend_uuid` | BTREE | ✓ | vend_transfer_id |
| `idx_transfers_status` | BTREE |  | status |
| `idx_transfers_type_status` | BTREE |  | type, status |
| `idx_transfers_from_status_date` | BTREE |  | outlet_from, status, created_at |
| `idx_transfers_to_status_date` | BTREE |  | outlet_to, status, created_at |
| `idx_transfers_staff` | BTREE |  | staff_transfer_id |
| `idx_transfers_created` | BTREE |  | created_at |
| `idx_transfers_type_created` | BTREE |  | type, created_at |
| `idx_transfers_to_created` | BTREE |  | outlet_to, created_at |
| `idx_transfers_vend` | BTREE |  | vend_resource, vend_transfer_id |
| `idx_transfers_customer` | BTREE |  | customer_id |
| `idx_transfers_state` | BTREE |  | state |
| `idx_consignment_id` | BTREE |  | consignment_id |
| `idx_transfers_type_status_created` | BTREE |  | type, status, created_at |
| `idx_transfers_vend_number` | BTREE |  | vend_number |

---

### `user_average_sales_values`

**Rows:** 34,161 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `staff_user_id` | int(11) | NO | PRI | NULL |  |
| `outlet_id` | varchar(45) | NO | PRI | NULL |  |
| `date` | timestamp | NO | PRI | current_timestamp() | on update current_timestamp() |
| `total_sales` | decimal(13,5) | NO |  | NULL |  |
| `total_transactions` | int(11) | NO |  | NULL |  |
| `daily_average` | decimal(13,5) | NO | PRI | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id, staff_user_id, outlet_id, daily_average, date |

---

### `user_notifications`

**Rows:** 151 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `user_id` | int(11) | NO |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `deleted_at` | timestamp | YES |  | NULL |  |
| `read_at` | timestamp | YES |  | NULL |  |
| `archived_at` | timestamp | YES |  | NULL |  |
| `avatar` | varchar(45) | YES |  | NULL |  |
| `notification_subject` | mediumtext | YES |  | NULL |  |
| `notification_text` | mediumtext | NO |  | NULL |  |
| `full_text` | mediumtext | YES |  | NULL |  |
| `url` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `user_permissions`

**Rows:** 2,147 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `user_id` | int(11) | NO | PRI | NULL |  |
| `permission_id` | int(11) | NO | PRI | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | user_id, permission_id |

---

### `user_roles`

**Rows:** 9 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `role_name` | varchar(45) | NO |  | NULL |  |
| `manage_type` | varchar(45) | NO |  | NULL |  |
| `managed_by` | int(11) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `users`

**Rows:** 110 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `first_name` | mediumtext | NO |  | NULL |  |
| `last_name` | mediumtext | NO |  | NULL |  |
| `email` | mediumtext | NO |  | NULL |  |
| `password` | mediumtext | NO |  | NULL |  |
| `phone` | mediumtext | NO |  | NULL |  |
| `image` | mediumtext | YES |  | NULL |  |
| `last_active` | timestamp | YES |  | NULL |  |
| `default_outlet` | mediumtext | YES |  | NULL |  |
| `role_id` | int(11) | NO |  | NULL |  |
| `is_manager` | tinyint(1) | YES | MUL | 0 |  |
| `stored_dashboard_view` | mediumtext | YES |  | NULL |  |
| `xero_id` | varchar(45) | YES | UNI | NULL |  |
| `vend_id` | varchar(45) | YES | UNI | NULL |  |
| `vend_sync_at` | timestamp | YES |  | NULL |  |
| `deputy_id` | varchar(45) | YES | UNI | NULL |  |
| `account_locked` | int(11) | NO |  | 0 |  |
| `staff_active` | int(11) | NO |  | 1 |  |
| `nicknames` | mediumtext | YES |  | NULL |  |
| `vend_customer_account` | varchar(45) | YES | UNI | NULL |  |
| `gpt_access` | tinyint(1) | YES |  | 0 |  |
| `gpt_admin` | tinyint(1) | YES |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `vend_id_UNIQUE` | BTREE | ✓ | vend_id |
| `vend_customer_account_UNIQUE` | BTREE | ✓ | vend_customer_account |
| `deputy_id_UNIQUE` | BTREE | ✓ | deputy_id |
| `xero_id_UNIQUE` | BTREE | ✓ | xero_id |
| `idx_users_vend_id` | BTREE |  | vend_id |
| `idx_is_manager` | BTREE |  | is_manager |

---

### `vend_brands`

**Rows:** 229 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | varchar(45) | NO | PRI | NULL |  |
| `name` | varchar(100) | NO |  | NULL |  |
| `deleted_at` | varchar(45) | YES |  | NULL |  |
| `version` | varchar(45) | NO |  | NULL |  |
| `enable_store_transfers` | int(11) | NO | MUL | 1 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `ix_vend_brands_enable` | BTREE |  | enable_store_transfers |

---

### `vend_categories`

**Rows:** 187 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `categoryID` | varchar(50) | NO | UNI | NULL |  |
| `name` | varchar(255) | NO | MUL |  |  |
| `nodeDepth` | int(11) | YES | MUL | 0 |  |
| `fullPathName` | text | YES |  | NULL |  |
| `leftNode` | int(11) | YES |  | 0 |  |
| `rightNode` | int(11) | YES |  | 0 |  |
| `createTime` | datetime | YES |  | NULL |  |
| `timeStamp` | datetime | YES |  | NULL |  |
| `parentID` | varchar(50) | YES | MUL | NULL |  |
| `deleted_at` | datetime | YES | MUL | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `root_category_id` | varchar(255) | YES |  | NULL |  |
| `leaf_category` | tinyint(1) | YES |  | 0 |  |
| `category_path_json` | text | YES |  | NULL |  |
| `parent_category_id` | varchar(255) | YES |  | NULL |  |
| `category_path` | text | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `categoryID` | BTREE | ✓ | categoryID |
| `idx_name` | BTREE |  | name |
| `idx_parent` | BTREE |  | parentID |
| `idx_deleted` | BTREE |  | deleted_at |
| `idx_node_depth` | BTREE |  | nodeDepth |
| `ix_vc_deleted_name` | BTREE |  | deleted_at, name |
| `ix_vc_name` | BTREE |  | name |
| `ix_vc_categoryID` | BTREE |  | categoryID |

---

### `vend_category_map`

**Rows:** 143 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `vend_category_id` | varchar(50) | NO | PRI | NULL |  |
| `target_category_id` | varchar(50) | NO | MUL | NULL |  |
| `refinement_status` | enum('unknown','mapped','refined') | YES |  | mapped |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `notes` | varchar(255) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | vend_category_id |
| `uq_vcm_vendor` | BTREE | ✓ | vend_category_id |
| `idx_target` | BTREE |  | target_category_id |
| `ix_vend_category_map_vendid` | BTREE |  | vend_category_id |
| `ix_vcm_vendor` | BTREE |  | vend_category_id |
| `ix_vcm_target` | BTREE |  | target_category_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_vcm_target` | `target_category_id` | `categories`.`id` |

---

### `vend_consignment_line_items`

**Rows:** 128,415 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `transfer_id` | int(11) | NO | MUL | NULL |  |
| `product_id` | varchar(45) | NO | MUL | NULL |  |
| `sku` | varchar(100) | YES | MUL | NULL |  |
| `name` | varchar(255) | YES |  | NULL |  |
| `unit_cost` | decimal(10,4) | YES |  | 0.0000 |  |
| `unit_price` | decimal(10,4) | YES |  | 0.0000 |  |
| `total_cost` | decimal(10,2) | YES |  | 0.00 |  |
| `total_price` | decimal(10,2) | YES |  | 0.00 |  |
| `status` | enum('pending','sent','received','cancelled','damaged') | YES | MUL | pending |  |
| `sent_at` | datetime | YES |  | NULL |  |
| `received_at` | datetime | YES |  | NULL |  |
| `notes` | text | YES |  | NULL |  |
| `quantity` | int(11) | NO |  | NULL |  |
| `quantity_sent` | int(11) | YES |  | 0 |  |
| `quantity_received` | int(11) | YES |  | 0 |  |
| `confirmation_status` | enum('pending','accepted','declined') | NO | MUL | pending |  |
| `confirmed_by_store` | int(11) | YES |  | NULL |  |
| `created_at` | datetime | YES |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `deleted_by` | int(11) | YES |  | NULL |  |
| `deleted_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uniq_item_transfer_product` | BTREE | ✓ | transfer_id, product_id |
| `uniq_transfer_product` | BTREE | ✓ | transfer_id, product_id |
| `idx_item_transfer` | BTREE |  | transfer_id |
| `idx_item_product` | BTREE |  | product_id |
| `idx_item_confirm` | BTREE |  | confirmation_status |
| `idx_items_outstanding` | BTREE |  | transfer_id, confirmation_status |
| `idx_ti_transfer_product` | BTREE |  | transfer_id, product_id |
| `idx_items_transfer_status` | BTREE |  | transfer_id, confirmation_status |
| `idx_line_items_transfer_id` | BTREE |  | transfer_id |
| `idx_line_items_product_id` | BTREE |  | product_id |
| `idx_line_items_status` | BTREE |  | status |
| `idx_line_items_sku` | BTREE |  | sku |
| `idx_line_items_transfer_status` | BTREE |  | transfer_id, status |
| `idx_line_items_product_status` | BTREE |  | product_id, status |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_items_transfer` | `transfer_id` | `vend_consignments`.`id` |

---

### `vend_consignments`

**Rows:** 12,658 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `public_id` | varchar(40) | NO | UNI | NULL |  |
| `vend_transfer_id` | char(36) | YES | UNI | NULL |  |
| `consignment_id` | bigint(20) unsigned | YES | MUL | NULL |  |
| `transfer_category` | enum('STOCK','JUICE','STAFF','RETURN','PURCHASE_ORDER','INTERNAL') | NO | MUL | STOCK |  |
| `creation_method` | enum('MANUAL','AUTOMATED') | NO | MUL | MANUAL |  |
| `vend_number` | varchar(64) | YES | MUL | NULL |  |
| `vend_url` | varchar(255) | YES |  | NULL |  |
| `vend_origin` | enum('CONSIGNMENT','PURCHASE_ORDER','TRANSFER') | YES |  | NULL |  |
| `outlet_from` | varchar(100) | NO | MUL | NULL |  |
| `outlet_to` | varchar(100) | NO | MUL | NULL |  |
| `created_by` | int(11) | NO |  | NULL |  |
| `staff_transfer_id` | int(10) unsigned | YES | MUL | NULL |  |
| `supplier_id` | varchar(100) | YES | MUL | NULL |  |
| `supplier_invoice_number` | varchar(100) | YES |  | NULL |  |
| `supplier_reference` | varchar(100) | YES |  | NULL |  |
| `tracking_number` | varchar(100) | YES | MUL | NULL |  |
| `tracking_carrier` | varchar(50) | YES |  | NULL |  |
| `tracking_url` | varchar(255) | YES |  | NULL |  |
| `tracking_updated_at` | timestamp | YES | MUL | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |
| `expected_delivery_date` | date | YES | MUL | NULL |  |
| `due_at` | datetime | YES |  | NULL |  |
| `sent_at` | datetime | YES |  | NULL |  |
| `received_at` | datetime | YES |  | NULL |  |
| `supplier_sent_at` | timestamp | YES | MUL | NULL |  |
| `supplier_cancelled_at` | timestamp | YES |  | NULL |  |
| `supplier_acknowledged_at` | timestamp | YES | MUL | NULL |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `last_transaction_id` | varchar(50) | YES | MUL | NULL |  |
| `version` | int(11) | YES | MUL | 1 |  |
| `locked_at` | datetime | YES | MUL | NULL |  |
| `locked_by` | int(11) | YES |  | NULL |  |
| `lock_expires_at` | datetime | YES | MUL | NULL |  |
| `deleted_by` | int(11) | YES |  | NULL |  |
| `deleted_at` | timestamp | YES |  | NULL |  |
| `customer_id` | varchar(45) | YES | MUL | NULL |  |
| `state` | enum('DRAFT','OPEN','PACKING','PACKAGED','SENT','RECEIVING','PARTIAL','RECEIVED','CLOSED','CANCELLED','ARCHIVED') | NO | MUL | OPEN |  |
| `total_boxes` | int(10) unsigned | NO |  | 0 |  |
| `total_weight_g` | bigint(20) unsigned | NO |  | 0 |  |
| `total_count` | int(11) | YES |  | 0 |  |
| `total_cost` | decimal(10,2) | YES |  | 0.00 |  |
| `total_received` | int(11) | YES |  | 0 |  |
| `line_item_count` | int(11) | YES |  | 0 |  |
| `draft_data` | longtext | YES |  | NULL |  |
| `draft_updated_at` | timestamp | YES |  | NULL |  |
| `vend_consignment_id` | varchar(64) | YES |  | NULL |  |
| `lightspeed_sync_status` | enum('pending','synced','failed') | YES |  | pending |  |
| `lightspeed_last_sync_at` | timestamp | YES |  | NULL |  |
| `lightspeed_push_attempts` | int(11) | YES |  | 0 |  |
| `lightspeed_push_error` | text | YES |  | NULL |  |
| `status` | enum('STOCKTAKE','OPEN','SENT','RECEIVED','CANCELLED','DRAFT') | YES |  | OPEN |  |
| `type` | enum('SUPPLIER','OUTLET','CUSTOMER','RETURN') | YES |  | OUTLET |  |
| `consignment_notes` | text | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uniq_transfers_public_id` | BTREE | ✓ | public_id |
| `uniq_transfers_vend_uuid` | BTREE | ✓ | vend_transfer_id |
| `idx_transfers_from_status_date` | BTREE |  | outlet_from, created_at |
| `idx_transfers_to_status_date` | BTREE |  | outlet_to, created_at |
| `idx_transfers_staff` | BTREE |  | staff_transfer_id |
| `idx_transfers_created` | BTREE |  | created_at |
| `idx_transfers_type_created` | BTREE |  | created_at |
| `idx_transfers_to_created` | BTREE |  | outlet_to, created_at |
| `idx_transfers_vend` | BTREE |  | vend_transfer_id |
| `idx_transfers_customer` | BTREE |  | customer_id |
| `idx_transfers_state` | BTREE |  | state |
| `idx_consignment_id` | BTREE |  | consignment_id |
| `idx_transfers_type_status_created` | BTREE |  | created_at |
| `idx_transfers_vend_number` | BTREE |  | vend_number |
| `idx_transfers_category` | BTREE |  | transfer_category |
| `idx_transfers_creation_method` | BTREE |  | creation_method |
| `idx_transfers_from_to_state` | BTREE |  | outlet_from, outlet_to, state |
| `idx_transfers_created_at` | BTREE |  | created_at |
| `idx_supplier_id` | BTREE |  | supplier_id |
| `idx_last_transaction` | BTREE |  | last_transaction_id |
| `idx_version` | BTREE |  | version |
| `idx_locked_at` | BTREE |  | locked_at |
| `idx_lock_expires` | BTREE |  | lock_expires_at |
| `idx_expected_delivery` | BTREE |  | expected_delivery_date, state |
| `idx_supplier_actions` | BTREE |  | supplier_sent_at, supplier_cancelled_at |
| `idx_supplier_acknowledged` | BTREE |  | supplier_acknowledged_at |
| `idx_consignments_public_id` | BTREE |  | public_id |
| `idx_consignments_outlet_to` | BTREE |  | outlet_to |
| `idx_consignments_state` | BTREE |  | state |
| `idx_consignments_created` | BTREE |  | created_at |
| `idx_consignments_state_outlet` | BTREE |  | state, outlet_to, created_at |
| `idx_tracking_number` | BTREE |  | tracking_number |
| `idx_tracking_updated_at` | BTREE |  | tracking_updated_at |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `fk_transfers_consignment` | `consignment_id` | `queue_consignments`.`id` |
| `fk_transfers_customer` | `customer_id` | `vend_customers`.`id` |
| `fk_transfers_staff` | `staff_transfer_id` | `staff_transfers`.`id` |

---

### `vend_consignments_backup_before_po_migration`

**Rows:** 12,226 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO |  | 0 |  |
| `public_id` | varchar(40) | NO |  | NULL |  |
| `vend_transfer_id` | char(36) | YES |  | NULL |  |
| `consignment_id` | bigint(20) unsigned | YES |  | NULL |  |
| `transfer_category` | enum('STOCK','JUICE','STAFF','RETURN','PURCHASE_ORDER') | NO |  | STOCK |  |
| `creation_method` | enum('MANUAL','AUTOMATED') | NO |  | MANUAL |  |
| `vend_number` | varchar(64) | YES |  | NULL |  |
| `vend_url` | varchar(255) | YES |  | NULL |  |
| `vend_origin` | enum('CONSIGNMENT','PURCHASE_ORDER','TRANSFER') | YES |  | NULL |  |
| `outlet_from` | varchar(100) | NO |  | NULL |  |
| `outlet_to` | varchar(100) | NO |  | NULL |  |
| `created_by` | int(11) | NO |  | NULL |  |
| `staff_transfer_id` | int(10) unsigned | YES |  | NULL |  |
| `supplier_id` | varchar(100) | YES |  | NULL |  |
| `supplier_invoice_number` | varchar(100) | YES |  | NULL |  |
| `supplier_reference` | varchar(100) | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `expected_delivery_date` | date | YES |  | NULL |  |
| `due_at` | datetime | YES |  | NULL |  |
| `sent_at` | datetime | YES |  | NULL |  |
| `received_at` | datetime | YES |  | NULL |  |
| `supplier_sent_at` | timestamp | YES |  | NULL |  |
| `supplier_cancelled_at` | timestamp | YES |  | NULL |  |
| `supplier_acknowledged_at` | timestamp | YES |  | NULL |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `last_transaction_id` | varchar(50) | YES |  | NULL |  |
| `version` | int(11) | YES |  | 1 |  |
| `locked_at` | datetime | YES |  | NULL |  |
| `locked_by` | int(11) | YES |  | NULL |  |
| `lock_expires_at` | datetime | YES |  | NULL |  |
| `deleted_by` | int(11) | YES |  | NULL |  |
| `deleted_at` | timestamp | YES |  | NULL |  |
| `customer_id` | varchar(45) | YES |  | NULL |  |
| `state` | enum('DRAFT','OPEN','PACKING','PACKAGED','SENT','RECEIVING','PARTIAL','RECEIVED','CLOSED','CANCELLED','ARCHIVED') | NO |  | OPEN |  |
| `total_boxes` | int(10) unsigned | NO |  | 0 |  |
| `total_weight_g` | bigint(20) unsigned | NO |  | 0 |  |
| `total_count` | int(11) | YES |  | 0 |  |
| `total_cost` | decimal(10,2) | YES |  | 0.00 |  |
| `total_received` | int(11) | YES |  | 0 |  |
| `line_item_count` | int(11) | YES |  | 0 |  |
| `draft_data` | longtext | YES |  | NULL |  |
| `draft_updated_at` | timestamp | YES |  | NULL |  |
| `vend_consignment_id` | varchar(64) | YES |  | NULL |  |
| `lightspeed_sync_status` | enum('pending','synced','failed') | YES |  | pending |  |
| `lightspeed_last_sync_at` | timestamp | YES |  | NULL |  |
| `lightspeed_push_attempts` | int(11) | YES |  | 0 |  |
| `lightspeed_push_error` | text | YES |  | NULL |  |
| `status` | enum('STOCKTAKE','OPEN','SENT','RECEIVED','CANCELLED','DRAFT') | YES |  | OPEN |  |
| `type` | enum('SUPPLIER','OUTLET','CUSTOMER','RETURN') | YES |  | OUTLET |  |
| `consignment_notes` | text | YES |  | NULL |  |

---

### `vend_customers`

**Rows:** 96,648 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | varchar(45) | NO | PRI | NULL |  |
| `customer_code` | varchar(100) | YES | MUL | NULL |  |
| `first_name` | mediumtext | YES |  | NULL |  |
| `last_name` | mediumtext | YES |  | NULL |  |
| `email` | varchar(100) | YES |  | NULL |  |
| `year_to_date` | varchar(45) | YES |  | NULL |  |
| `balance` | varchar(45) | YES |  | NULL |  |
| `loyalty_balance` | varchar(45) | YES |  | NULL |  |
| `note` | mediumtext | YES |  | NULL |  |
| `gender` | varchar(45) | YES |  | NULL |  |
| `date_of_birth` | varchar(45) | YES |  | NULL |  |
| `company_name` | varchar(100) | YES |  | NULL |  |
| `do_not_email` | varchar(45) | YES |  | NULL |  |
| `phone` | varchar(100) | YES |  | NULL |  |
| `mobile` | varchar(100) | YES |  | NULL |  |
| `physical_suburb` | varchar(45) | YES |  | NULL |  |
| `physical_city` | varchar(45) | YES |  | NULL |  |
| `physical_postcode` | varchar(45) | YES |  | NULL |  |
| `physical_state` | varchar(45) | YES |  | NULL |  |
| `postal_suburb` | varchar(45) | YES |  | NULL |  |
| `postal_city` | varchar(45) | YES |  | NULL |  |
| `postal_state` | varchar(45) | YES |  | NULL |  |
| `customer_group_id` | varchar(45) | YES |  | NULL |  |
| `enable_loyalty` | varchar(45) | YES |  | NULL |  |
| `created_at` | varchar(45) | YES |  | NULL |  |
| `updated_at` | varchar(45) | YES |  | NULL |  |
| `deleted_at` | varchar(45) | YES |  | NULL |  |
| `version` | varchar(45) | YES |  | NULL |  |
| `postal_postcode` | varchar(45) | YES |  | NULL |  |
| `name` | mediumtext | YES |  | NULL |  |
| `physical_address_1` | varchar(200) | YES |  | NULL |  |
| `physical_address_2` | varchar(200) | YES |  | NULL |  |
| `physical_country_id` | varchar(45) | YES |  | NULL |  |
| `postal_address_1` | varchar(200) | YES |  | NULL |  |
| `postal_address_2` | varchar(200) | YES |  | NULL |  |
| `postal_country_id` | varchar(45) | YES |  | NULL |  |
| `custom_field_1` | mediumtext | YES |  | NULL |  |
| `custom_field_2` | varchar(200) | YES |  | NULL |  |
| `custom_field_3` | varchar(200) | YES |  | NULL |  |
| `custom_field_4` | varchar(200) | YES |  | NULL |  |
| `website` | varchar(200) | YES |  | NULL |  |
| `unsubscribe_account_balance` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `id_UNIQUE` | BTREE | ✓ | id |
| `customerID` | BTREE |  | id, customer_code, email |
| `vs_code_idx` | BTREE |  | customer_code |

---

### `vend_inventory`

**Rows:** 211,663 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | varchar(100) | NO | PRI | NULL |  |
| `outlet_id` | varchar(100) | NO | MUL | NULL |  |
| `product_id` | varchar(100) | NO | MUL | NULL |  |
| `inventory_level` | int(11) | NO | MUL | NULL |  |
| `current_amount` | int(11) | NO | MUL | NULL |  |
| `version` | bigint(20) | NO |  | NULL |  |
| `reorder_point` | int(11) | YES |  | NULL |  |
| `reorder_amount` | int(11) | YES |  | NULL |  |
| `deleted_at` | timestamp | YES |  | NULL |  |
| `average_cost` | decimal(16,6) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `product_id` | BTREE |  | product_id |
| `outlet_id` | BTREE |  | outlet_id |
| `ix_vend_inventory_outlet_product` | BTREE |  | outlet_id, product_id |
| `ix_vend_inventory_level` | BTREE |  | inventory_level |
| `ix_vi_outlet_amt_prod` | BTREE |  | outlet_id, deleted_at, current_amount, product_id |
| `idx_vend_inventory_covering` | BTREE |  | product_id, outlet_id, inventory_level |
| `idx_prod_outlet` | BTREE |  | product_id, outlet_id |
| `idx_inventory_product_outlet` | BTREE |  | product_id, outlet_id |
| `idx_inventory_stock_level` | BTREE |  | current_amount, reorder_point |

---

### `vend_inventory_sync`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `outlet_id` | varchar(100) | NO | MUL | NULL |  |
| `product_id` | varchar(100) | NO |  | NULL |  |
| `vend_id` | varchar(100) | YES |  | NULL |  |
| `old_amount` | int(11) | NO |  | 0 |  |
| `new_amount` | int(11) | NO |  | 0 |  |
| `sync_timestamp` | timestamp | NO | MUL | current_timestamp() |  |
| `operation_id` | varchar(100) | YES | MUL | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `unique_sync` | BTREE | ✓ | outlet_id, product_id, operation_id |
| `idx_outlet_product` | BTREE |  | outlet_id, product_id |
| `idx_operation` | BTREE |  | operation_id |
| `idx_sync_timestamp` | BTREE |  | sync_timestamp |

---

### `vend_outlets`

**Rows:** 27 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | varchar(100) | NO | PRI | NULL |  |
| `register_id` | varchar(100) | YES | UNI | NULL |  |
| `name` | varchar(100) | NO |  | NULL |  |
| `default_tax_id` | varchar(100) | YES |  | NULL |  |
| `currency` | varchar(100) | YES |  | NULL |  |
| `currency_symbol` | varchar(100) | YES |  | NULL |  |
| `display_prices` | varchar(100) | YES |  | NULL |  |
| `time_zone` | varchar(100) | YES |  | NULL |  |
| `physical_street_number` | varchar(45) | YES |  | NULL |  |
| `physical_street` | varchar(45) | YES |  | NULL |  |
| `physical_address_1` | varchar(100) | YES |  | NULL |  |
| `physical_address_2` | varchar(100) | YES |  | NULL |  |
| `physical_suburb` | varchar(100) | YES |  | NULL |  |
| `physical_city` | varchar(255) | YES |  | NULL |  |
| `physical_postcode` | varchar(100) | YES |  | NULL |  |
| `physical_state` | varchar(100) | YES |  | NULL |  |
| `physical_country_id` | varchar(100) | YES |  | NULL |  |
| `physical_phone_number` | varchar(45) | YES |  | NULL |  |
| `deleted_at` | timestamp | YES |  | NULL |  |
| `version` | bigint(20) | NO |  | NULL |  |
| `turn_over_rate` | float | NO |  | 5 |  |
| `automatic_ordering` | int(11) | NO |  | 1 |  |
| `facebook_page_id` | varchar(45) | YES |  | NULL |  |
| `gss_token` | varchar(100) | YES |  | NULL |  |
| `google_page_id` | varchar(100) | YES |  | NULL |  |
| `total_review_count` | int(11) | YES |  | NULL |  |
| `google_review_rating` | float(2,1) | YES |  | NULL |  |
| `store_code` | varchar(45) | YES |  | NULL |  |
| `magento_warehouse_id` | int(11) | YES |  | NULL |  |
| `google_link` | varchar(100) | YES |  | NULL |  |
| `outlet_lat` | varchar(45) | YES |  | NULL |  |
| `outlet_long` | varchar(45) | YES |  | NULL |  |
| `website_active` | int(11) | NO |  | 1 |  |
| `website_outlet_id` | int(11) | YES | UNI | NULL |  |
| `deposit_card_id` | int(11) | YES |  | NULL |  |
| `vape_hq_shipping_id` | varchar(45) | YES |  | NULL |  |
| `banking_days_allocated` | int(11) | NO |  | 7 |  |
| `email` | varchar(45) | YES |  | NULL |  |
| `nz_post_api_key` | varchar(45) | YES |  | NULL |  |
| `nz_post_subscription_key` | varchar(45) | YES |  | NULL |  |
| `ip_address` | varchar(45) | YES |  | NULL |  |
| `deputy_location_id` | int(11) | NO |  | 0 |  |
| `eftpos_merchant_id` | int(11) | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `is_warehouse` | int(11) | NO | MUL | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `register_id_UNIQUE` | BTREE | ✓ | register_id |
| `magento_warehouse_id_UNIQUE` | BTREE | ✓ | website_outlet_id |
| `ix_vend_outlets_warehouse` | BTREE |  | is_warehouse |

---

### `vend_outlets_closed_notifications`

**Rows:** 215 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `outlet_id` | varchar(45) | NO |  | NULL |  |
| `created_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `vend_outlets_open_hours`

**Rows:** 18 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `outlet_id` | varchar(45) | NO |  | NULL |  |
| `monday_open` | varchar(5) | NO |  | 0 |  |
| `monday_close` | varchar(5) | NO |  | 0 |  |
| `tuesday_open` | varchar(5) | NO |  | 0 |  |
| `tuesday_close` | varchar(5) | NO |  | 0 |  |
| `wednesday_open` | varchar(5) | NO |  | 0 |  |
| `wednesday_close` | varchar(5) | NO |  | 0 |  |
| `thursday_open` | varchar(5) | NO |  | 0 |  |
| `thursday_close` | varchar(5) | NO |  | 0 |  |
| `friday_open` | varchar(5) | NO |  | 0 |  |
| `friday_close` | varchar(5) | NO |  | 0 |  |
| `saturday_open` | varchar(5) | NO |  | 0 |  |
| `saturday_close` | varchar(5) | NO |  | 0 |  |
| `sunday_open` | varchar(5) | NO |  | 0 |  |
| `sunday_close` | varchar(5) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `vend_product_qty_history`

**Rows:** 79,440,912 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `product_id` | varchar(45) | NO | MUL | NULL |  |
| `qty` | int(11) | NO |  | NULL |  |
| `outlet_id` | varchar(45) | NO | MUL | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `outletIDIndex` | BTREE |  | outlet_id |
| `idx_product_outlet_created` | BTREE |  | product_id, outlet_id, created_at |
| `idx_created_outlet` | BTREE |  | created_at, outlet_id |

---

### `vend_products`

**Rows:** 8,381 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | varchar(100) | NO | PRI |  |  |
| `source_id` | varchar(200) | YES |  | NULL |  |
| `source_variant_id` | mediumtext | YES |  | NULL |  |
| `variant_parent_id` | mediumtext | YES |  | NULL |  |
| `name` | varchar(255) | YES | MUL | NULL |  |
| `variant_name` | varchar(255) | YES |  | NULL |  |
| `handle` | varchar(200) | YES |  | NULL |  |
| `sku` | varchar(200) | YES |  | NULL |  |
| `supplier_code` | int(11) | YES |  | NULL |  |
| `active` | int(11) | NO | MUL | 0 |  |
| `has_inventory` | int(11) | NO |  | 0 |  |
| `is_composite` | int(11) | NO |  | 0 |  |
| `description` | longtext | YES |  | NULL |  |
| `image_url` | text | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| `updated_at` | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| `deleted_at` | timestamp | YES | MUL | 0000-00-00 00:00:00 |  |
| `type` | mediumtext | YES |  | NULL |  |
| `account_code` | mediumtext | YES |  | NULL |  |
| `version` | bigint(20) | NO |  | 0 |  |
| `supplier` | mediumtext | YES |  | NULL |  |
| `source` | text | YES |  | NULL |  |
| `account_code_purchase` | mediumtext | YES |  | NULL |  |
| `has_variants` | mediumtext | YES |  | NULL |  |
| `brand` | varchar(255) | YES |  | NULL |  |
| `variant_options` | text | YES |  | NULL |  |
| `brand_id` | varchar(200) | YES | MUL | NULL |  |
| `price_including_tax` | decimal(13,5) | NO |  | 0.00000 |  |
| `loyalty_amount` | decimal(13,5) | YES |  | NULL |  |
| `price_excluding_tax` | decimal(13,5) | NO |  | 0.00000 |  |
| `product_type_id` | mediumtext | YES |  | NULL |  |
| `supplier_id` | varchar(200) | YES | MUL | NULL |  |
| `button_order` | int(11) | NO |  | 0 |  |
| `is_active` | int(11) | NO |  | 0 |  |
| `image_thumbnail_url` | text | YES |  | NULL |  |
| `supply_price` | decimal(13,5) | NO |  | 0.00000 |  |
| `avg_weight_grams` | int(11) | YES | MUL | NULL |  |
| `dont_show_in_low_stock` | int(11) | NO |  | 0 |  |
| `dont_insert_into_website` | int(11) | YES |  | 0 |  |
| `harp_product_status` | int(11) | NO |  | 0 |  |
| `is_deleted` | tinyint(1) | YES | MUL | NULL | STORED GENERATED |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_supplier_active` | BTREE |  | supplier_id, is_active, active, deleted_at |
| `ix_vend_products_brand_supplier` | BTREE |  | brand_id, supplier_id |
| `ix_vend_products_deleted` | BTREE |  | deleted_at |
| `ix_vend_products_active` | BTREE |  | active |
| `ix_vp_flags` | BTREE |  | is_deleted, is_active, active, has_inventory |
| `idx_vend_products_active_inventory` | BTREE |  | active, has_inventory, is_active, supplier_id, brand_id |
| `ix_vp_weight` | BTREE |  | avg_weight_grams |
| `ProductFullSearch` | FULLTEXT |  | name |

---

### `vend_products_default_transfer_settings`

**Rows:** 494 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `product_id` | varchar(45) | NO | PRI | NULL |  |
| `enable_product_transfer` | int(11) | NO |  | 1 |  |
| `enable_qty_transfer_limit` | int(11) | NO |  | 0 |  |
| `enable_transfer_threshold` | int(11) | NO |  | 0 |  |
| `maximum_qty_to_send` | int(11) | NO |  | 30 |  |
| `only_send_when_below` | int(11) | NO |  | 20 |  |
| `send_in_multiple_qty` | int(11) | NO |  | 1 |  |
| `minimum_qty_left_at_warehouse` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | product_id |
| `ux_vp_default_settings_prod` | BTREE | ✓ | product_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `productID` | `product_id` | `vend_products`.`id` |

---

### `vend_products_outlet_transfer_settings`

**Rows:** 16 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `product_id` | varchar(45) | NO | PRI | NULL |  |
| `outlet_id` | varchar(45) | NO | PRI | NULL |  |
| `override_default_product_settings` | int(11) | NO |  | 0 |  |
| `enable_product_transfer` | int(11) | NO |  | 1 |  |
| `enable_qty_transfer_limit` | int(11) | NO |  | 0 |  |
| `enable_transfer_threshold` | int(11) | NO |  | 0 |  |
| `maximum_qty_to_send` | int(11) | NO |  | 30 |  |
| `only_send_when_below` | int(11) | NO |  | 20 |  |
| `send_in_multiple_qty` | int(11) | NO |  | 1 |  |
| `minimum_qty_left_at_warehouse` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | product_id, outlet_id |
| `ux_vp_outlet_settings_prod_outlet` | BTREE | ✓ | product_id, outlet_id |
| `outletID_idx` | BTREE |  | outlet_id |

---

### `vend_queue`

**Rows:** 70,623 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `request_id` | char(16) | YES |  | NULL |  |
| `idempotency_key` | varchar(64) | YES | MUL | NULL |  |
| `correlation_id` | varchar(64) | YES |  | NULL |  |
| `vend_data` | mediumtext | NO |  | NULL |  |
| `status` | int(11) | NO | MUL | 0 |  |
| `retry_count` | int(11) | NO |  | 0 |  |
| `vend_url` | varchar(100) | NO |  | NULL |  |
| `http_method` | varchar(8) | NO |  | POST |  |
| `source` | varchar(50) | NO | MUL | system |  |
| `type` | varchar(50) | NO |  | generic |  |
| `result` | text | YES |  | NULL |  |
| `response_code` | int(11) | YES |  | NULL |  |
| `error_code` | varchar(64) | YES |  | NULL |  |
| `retryable` | tinyint(1) | NO |  | 1 |  |
| `timestamp` | timestamp | NO | MUL | current_timestamp() |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() |  |
| `next_attempt_at` | timestamp | YES |  | NULL |  |
| `locked_by` | varchar(64) | YES |  | NULL |  |
| `locked_at` | timestamp | YES |  | NULL |  |
| `endpoint` | varchar(255) | YES | MUL | NULL |  |
| `vend_resource` | varchar(64) | YES | MUL | NULL |  |
| `entity_type` | enum('transfer','po','inventory','product') | YES | MUL | NULL |  |
| `entity_pk` | int(11) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `queue_index` | BTREE |  | status, timestamp |
| `idx_vend_queue_status_id` | BTREE |  | status, id |
| `idx_vend_queue_status_retry` | BTREE |  | status, retry_count |
| `ix_vq_status` | BTREE |  | status |
| `ix_vq_status_retry` | BTREE |  | status, retry_count |
| `ix_vq_created` | BTREE |  | created_at |
| `ix_vq_timestamp` | BTREE |  | timestamp |
| `ix_vq_source_created` | BTREE |  | source, created_at |
| `ix_vq_endpoint_status` | BTREE |  | endpoint, status |
| `idx_status_due` | BTREE |  | status, next_attempt_at |
| `idx_locked` | BTREE |  | status, locked_by, locked_at |
| `idx_idempotency` | BTREE |  | idempotency_key |
| `idx_entity_ref` | BTREE |  | entity_type, entity_pk |
| `idx_resource_status` | BTREE |  | vend_resource, status |

---

### `vend_sales`

**Rows:** 1,604,562 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `increment_id` | int(11) | NO | PRI | NULL | auto_increment |
| `id` | varchar(36) | NO | UNI | NULL |  |
| `outlet_id` | varchar(36) | NO | MUL | NULL |  |
| `register_id` | varchar(36) | NO |  | NULL |  |
| `user_id` | varchar(36) | NO | MUL | NULL |  |
| `customer_id` | varchar(36) | NO | MUL | NULL |  |
| `invoice_number` | int(11) | NO |  | NULL |  |
| `status` | varchar(30) | NO |  | NULL |  |
| `note` | mediumtext | NO |  | NULL |  |
| `short_code` | varchar(15) | NO |  | NULL |  |
| `return_for` | varchar(100) | YES |  | NULL |  |
| `total_price` | decimal(16,6) | YES |  | NULL |  |
| `total_tax` | decimal(16,6) | YES |  | NULL |  |
| `total_loyalty` | decimal(16,6) | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| `updated_at` | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| `sale_date` | timestamp | NO | MUL | 0000-00-00 00:00:00 |  |
| `deleted_at` | timestamp | YES |  | NULL |  |
| `version` | bigint(100) | NO |  | NULL |  |
| `receipt_number` | int(11) | NO |  | NULL |  |
| `version_max` | bigint(100) | YES |  | NULL |  |
| `payments` | longtext | YES |  | NULL |  |
| `sale_date_d` | date | YES | MUL | NULL | STORED GENERATED |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | increment_id |
| `SALE_ID_INDEX` | BTREE | ✓ | id |
| `idx_user_id` | BTREE |  | user_id |
| `ix_vs_outlet_date_inc` | BTREE |  | outlet_id, sale_date, increment_id |
| `ix_vs_date_inc` | BTREE |  | sale_date, increment_id |
| `ix_vs_customer_date` | BTREE |  | customer_id, sale_date |
| `ix_vs_dateD_inc` | BTREE |  | sale_date_d, increment_id |
| `ix_vs_outlet_dateD_inc` | BTREE |  | outlet_id, sale_date_d, increment_id |
| `idx_vend_sales_performance` | BTREE |  | outlet_id, sale_date, status, total_price |
| `idx_vend_sales_user_date_status` | BTREE |  | user_id, created_at, status |
| `idx_vs_outlet_date_status` | BTREE |  | outlet_id, sale_date, status |
| `idx_vs_customer_date` | BTREE |  | customer_id, sale_date |
| `idx_vs_user_date_status` | BTREE |  | user_id, sale_date, status |
| `idx_vs_increment_outlet_date` | BTREE |  | increment_id, outlet_id, sale_date |
| `idx_sales_outlet_increment` | BTREE |  | outlet_id, increment_id |
| `idx_vend_sales_web_processing` | BTREE |  | outlet_id, status, sale_date, created_at |

---

### `vend_sales_line_items`

**Rows:** 2,703,132 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `sales_increment_id` | int(11) | NO | PRI | NULL |  |
| `id` | varchar(36) | NO | PRI | NULL |  |
| `product_id` | varchar(36) | NO | MUL | NULL |  |
| `tax_id` | varchar(36) | NO |  | NULL |  |
| `discount_total` | decimal(16,6) | YES |  | NULL |  |
| `discount` | decimal(16,6) | YES |  | NULL |  |
| `price_total` | decimal(16,6) | YES |  | NULL |  |
| `price` | decimal(16,6) | YES |  | NULL |  |
| `cost_total` | decimal(16,6) | NO |  | NULL |  |
| `cost` | decimal(16,6) | NO |  | NULL |  |
| `tax_total` | decimal(16,6) | YES |  | NULL |  |
| `tax` | decimal(16,6) | YES |  | NULL |  |
| `quantity` | int(11) | NO | MUL | NULL |  |
| `loyalty_value` | decimal(16,6) | YES |  | NULL |  |
| `note` | mediumtext | YES |  | NULL |  |
| `price_set` | int(11) | NO |  | NULL |  |
| `status` | varchar(30) | NO | MUL | NULL |  |
| `sequence` | int(11) | NO |  | NULL |  |
| `unit_discount` | decimal(16,6) | YES |  | NULL |  |
| `unit_loyalty_value` | decimal(13,5) | NO |  | NULL |  |
| `total_cost` | decimal(16,6) | YES |  | NULL |  |
| `unit_price` | decimal(16,6) | YES |  | NULL |  |
| `unit_cost` | decimal(16,6) | YES |  | NULL |  |
| `total_discount` | decimal(16,6) | YES |  | NULL |  |
| `total_price` | decimal(16,6) | YES |  | NULL |  |
| `total_loyalty_value` | decimal(16,6) | YES |  | NULL |  |
| `total_tax` | decimal(16,6) | YES |  | NULL |  |
| `is_return` | int(11) | NO |  | NULL |  |
| `unit_tax` | decimal(16,6) | YES |  | NULL |  |
| `sale_id` | varchar(36) | NO | MUL | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | sales_increment_id, id |
| `idx_quantity_return` | BTREE |  | quantity, is_return |
| `idx_status` | BTREE |  | status |
| `idx_sale_id` | BTREE |  | sale_id |
| `ix_vsli_salesinc_prod_status_return` | BTREE |  | sales_increment_id, product_id, status, is_return |
| `idx_vsli_status_product` | BTREE |  | status, product_id |
| `idx_vsli_covering_agg` | BTREE |  | status, product_id, quantity, price_total, tax_total |
| `idx_vsli_sales_inc_status` | BTREE |  | sales_increment_id, status |
| `idx_sale_line_product_outlet` | BTREE |  | product_id, sales_increment_id, is_return |
| `idx_covering_ultra_fast` | BTREE |  | product_id, is_return, sales_increment_id, quantity |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `vendSaleIncrementID` | `sales_increment_id` | `vend_sales`.`increment_id` |
| `vendUUIDIncrementID` | `sale_id` | `vend_sales`.`id` |

---

### `vend_suppliers`

**Rows:** 94 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | varchar(100) | NO | PRI | NULL |  |
| `name` | varchar(100) | NO | MUL | NULL |  |
| `source` | varchar(45) | YES |  | NULL |  |
| `description` | varchar(500) | YES |  | NULL |  |
| `deleted_at` | varchar(45) | YES |  | NULL |  |
| `version` | varchar(45) | YES |  | NULL |  |
| `email` | varchar(100) | YES | MUL | NULL |  |
| `claim_email` | varchar(150) | YES |  | NULL |  |
| `bank_account` | varchar(45) | YES |  | NULL |  |
| `phone` | varchar(45) | YES |  | NULL |  |
| `contact_name` | varchar(45) | YES |  | NULL |  |
| `show_in_system` | int(11) | YES | MUL | 1 |  |
| `automatic_ordering` | int(11) | NO | MUL | 0 |  |
| `automatic_transferring` | int(11) | NO | MUL | 1 |  |
| `automatic_transferring_based_on_sales_data` | int(11) | YES |  | 0 |  |
| `notification_eligible` | int(11) | NO | MUL | 1 |  |
| `credit_sla_days` | int(11) | YES |  | NULL |  |
| `enable_product_returns` | int(11) | NO | MUL | 1 |  |
| `enable_wholesale_show_estimated_delivery` | int(11) | NO |  | 0 |  |
| `automatic_ordering_moq` | int(11) | NO |  | 30 |  |
| `automatic_product_forecasting` | int(11) | NO |  | 0 |  |
| `website` | varchar(45) | YES |  | NULL |  |
| `contact_person` | varchar(45) | YES |  | NULL |  |
| `brand_logo_url` | varchar(45) | YES |  | NULL |  |
| `primary_color` | varchar(45) | YES |  | NULL |  |
| `secondary_color` | varchar(45) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `id_UNIQUE` | BTREE | ✓ | id |
| `ix_vend_suppliers_auto` | BTREE |  | automatic_transferring |
| `ix_vs_name` | BTREE |  | name |
| `ix_vs_email` | BTREE |  | email |
| `ix_vs_visible_name` | BTREE |  | show_in_system, name |
| `ix_vs_notify_enabled` | BTREE |  | notification_eligible, id |
| `ix_vs_returns_enabled` | BTREE |  | enable_product_returns, id |
| `ix_vs_auto_ordering` | BTREE |  | automatic_ordering, id |

---

### `vend_users`

**Rows:** 57 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | varchar(100) | NO | PRI | NULL |  |
| `username` | varchar(255) | YES | MUL | NULL |  |
| `email` | varchar(255) | YES | MUL | NULL |  |
| `first_name` | varchar(255) | YES |  | NULL |  |
| `last_name` | varchar(255) | YES |  | NULL |  |
| `display_name` | varchar(255) | YES |  | NULL |  |
| `account_type` | varchar(50) | YES |  | NULL |  |
| `is_account_owner` | tinyint(1) | YES |  | 0 |  |
| `is_primary_user` | tinyint(1) | YES |  | 0 |  |
| `user_can_see_cost_price` | tinyint(1) | YES |  | 0 |  |
| `user_can_see_supply_price` | tinyint(1) | YES |  | 0 |  |
| `restricted_outlet_id` | varchar(100) | YES |  | NULL |  |
| `outlet_id` | varchar(100) | YES | MUL | NULL |  |
| `version` | bigint(20) unsigned | YES | MUL | 0 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `deleted_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_vend_users_email` | BTREE |  | email |
| `idx_vend_users_username` | BTREE |  | username |
| `idx_vend_users_version` | BTREE |  | version |
| `idx_vend_users_outlet_id` | BTREE |  | outlet_id |

---

### `verifone_transactions`

**Rows:** 289,162 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `transasction_id` | bigint(20) | NO | MUL | NULL |  |
| `amount` | decimal(10,2) | NO |  | NULL |  |
| `currency_code` | varchar(3) | YES |  | NULL |  |
| `created_at` | datetime | NO |  | NULL |  |
| `country_code` | varchar(2) | YES |  | NULL |  |
| `payment_product` | varchar(50) | YES |  | NULL |  |
| `payment_product_type` | varchar(50) | YES |  | NULL |  |
| `status` | varchar(50) | NO |  | NULL |  |
| `rrn` | varchar(255) | YES |  | NULL |  |
| `cvv_present` | tinyint(1) | YES |  | NULL |  |
| `authorization_code` | varchar(255) | YES |  | NULL |  |
| `reason_code` | varchar(255) | YES |  | NULL |  |
| `shopper_interaction` | varchar(50) | YES |  | NULL |  |
| `pos_device_id` | varchar(255) | YES |  | NULL |  |
| `stan` | varchar(255) | YES |  | NULL |  |
| `card_brand` | varchar(50) | YES |  | NULL |  |
| `merchant_id` | varchar(255) | YES | MUL | NULL |  |
| `poi_id` | varchar(255) | YES |  | NULL |  |
| `masked_card_number` | varchar(255) | YES |  | NULL |  |
| `acquirer_response_code` | varchar(255) | YES |  | NULL |  |
| `transaction_status` | varchar(50) | YES |  | NULL |  |
| `transaction_type` | varchar(50) | YES |  | NULL |  |
| `entry_mode` | varchar(50) | YES |  | NULL |  |
| `invoice_number` | varchar(255) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `transactionIDIndex` | BTREE |  | transasction_id |
| `merchantIDIndex` | BTREE |  | merchant_id |

---

### `webhook_consignment_events`

**Rows:** 94 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `consignment_id` | bigint(20) unsigned | NO | MUL | NULL |  |
| `webhook_type` | enum('consignment.send','consignment.receive') | NO | MUL | NULL |  |
| `lightspeed_consignment_id` | varchar(100) | YES | MUL | NULL |  |
| `webhook_payload` | longtext | YES |  | NULL |  |
| `status` | enum('pending','processing','completed','failed') | YES | MUL | pending |  |
| `processing_attempts` | int(11) | YES |  | 0 |  |
| `processed_at` | timestamp | YES |  | NULL |  |
| `error_message` | text | YES |  | NULL |  |
| `trace_id` | varchar(64) | YES | MUL | NULL |  |
| `created_by` | varchar(100) | YES |  | WEBHOOK_SYSTEM |  |
| `updated_by` | varchar(100) | YES |  | WEBHOOK_SYSTEM |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_consignment_id` | BTREE |  | consignment_id |
| `idx_webhook_type` | BTREE |  | webhook_type |
| `idx_status` | BTREE |  | status |
| `idx_trace_id` | BTREE |  | trace_id |
| `idx_created_at` | BTREE |  | created_at |
| `idx_lightspeed_id` | BTREE |  | lightspeed_consignment_id |

---

### `webhook_data_collectors`

**Rows:** 9 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `collector_name` | varchar(100) | NO | UNI | NULL |  |
| `primary_table` | varchar(100) | NO | MUL | NULL |  |
| `related_tables` | longtext | NO |  | NULL |  |
| `collection_sql` | text | NO |  | NULL |  |
| `data_enrichment_rules` | longtext | YES |  | NULL |  |
| `cache_duration_minutes` | int(11) | YES |  | 5 |  |
| `is_active` | tinyint(1) | YES | MUL | 1 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `collector_name` | BTREE | ✓ | collector_name |
| `idx_primary_table` | BTREE |  | primary_table |
| `idx_active` | BTREE |  | is_active |

---

### `webhook_events_archive_20251015`

**Rows:** 10,326 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO |  | NULL |  |
| `webhook_id` | varchar(128) | NO |  | NULL |  |
| `webhook_type` | varchar(64) | NO |  | NULL |  |
| `payload` | longtext | NO |  | NULL |  |
| `raw_payload` | longtext | NO |  | NULL |  |
| `source_ip` | varchar(45) | YES |  | NULL |  |
| `user_agent` | text | YES |  | NULL |  |
| `headers` | longtext | YES |  | NULL |  |
| `status` | enum('received','processing','completed','failed','replayed') | NO |  | NULL |  |
| `received_at` | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| `processed_at` | timestamp | YES |  | NULL |  |
| `processing_attempts` | int(10) unsigned | NO |  | NULL |  |
| `processing_result` | longtext | YES |  | NULL |  |
| `error_message` | text | YES |  | NULL |  |
| `queue_job_id` | varchar(64) | YES |  | NULL |  |
| `replayed_from` | varchar(128) | YES |  | NULL |  |
| `replay_reason` | varchar(255) | YES |  | NULL |  |
| `replayed_by_user` | int(10) unsigned | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | 0000-00-00 00:00:00 |  |
| `updated_at` | timestamp | NO |  | 0000-00-00 00:00:00 |  |

---

### `webhook_monitoring`

**Rows:** 10 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `webhook_type` | varchar(50) | YES | UNI | NULL |  |
| `total_processed` | int(11) | YES |  | 0 |  |
| `total_failed` | int(11) | YES |  | 0 |  |
| `avg_processing_time` | decimal(10,6) | YES |  | NULL |  |
| `last_processed_at` | timestamp | YES |  | NULL |  |
| `last_failure_at` | timestamp | YES |  | NULL |  |
| `failure_rate` | decimal(5,2) | YES |  | NULL |  |
| `alert_threshold_reached` | tinyint(1) | YES |  | 0 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `unique_webhook_type` | BTREE | ✓ | webhook_type |

---

### `webhook_processing_log`

**Rows:** 416 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `webhook_queue_id` | int(11) | YES | MUL | NULL |  |
| `webhook_type` | varchar(50) | YES | MUL | NULL |  |
| `action` | varchar(100) | YES |  | NULL |  |
| `status` | varchar(50) | YES |  | NULL |  |
| `message` | text | YES |  | NULL |  |
| `execution_time` | decimal(10,6) | YES |  | NULL |  |
| `memory_usage` | int(11) | YES |  | NULL |  |
| `trace_id` | varchar(255) | YES | MUL | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_webhook_queue_id` | BTREE |  | webhook_queue_id |
| `idx_webhook_type` | BTREE |  | webhook_type |
| `idx_trace_id` | BTREE |  | trace_id |

---

### `webhook_registry`

**Rows:** 11 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `webhook_name` | varchar(100) | NO | UNI | NULL |  |
| `endpoint_url` | varchar(500) | NO |  | NULL |  |
| `method` | varchar(10) | YES |  | POST |  |
| `tables_monitored` | longtext | NO |  | NULL |  |
| `event_types` | longtext | NO |  | NULL |  |
| `data_sources` | longtext | NO |  | NULL |  |
| `is_active` | tinyint(1) | YES | MUL | 1 |  |
| `max_retries` | int(11) | YES |  | 3 |  |
| `timeout_seconds` | int(11) | YES |  | 30 |  |
| `headers` | longtext | YES |  | NULL |  |
| `authentication_type` | enum('none','basic','bearer','api_key') | YES |  | none |  |
| `auth_credentials` | longtext | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `webhook_name` | BTREE | ✓ | webhook_name |
| `idx_active` | BTREE |  | is_active |
| `idx_webhook_name` | BTREE |  | webhook_name |

---

### `webhooks_audit_log`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `raw_storage_id` | bigint(20) unsigned | YES | MUL | NULL |  |
| `webhook_type` | varchar(50) | NO | MUL | NULL |  |
| `operation` | varchar(100) | NO | MUL | NULL |  |
| `payload` | longtext | YES |  | NULL |  |
| `result` | longtext | YES |  | NULL |  |
| `success` | tinyint(1) | NO | MUL | NULL |  |
| `processing_time_ms` | decimal(10,2) | YES |  | NULL |  |
| `database_enabled` | tinyint(1) | YES |  | 1 |  |
| `error_message` | text | YES |  | NULL |  |
| `ip_address` | varchar(45) | YES |  | NULL |  |
| `user_agent` | text | YES |  | NULL |  |
| `trace_id` | varchar(64) | YES | MUL | NULL |  |
| `created_by` | varchar(100) | YES |  | WEBHOOK_SYSTEM |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_webhook_type` | BTREE |  | webhook_type |
| `idx_operation` | BTREE |  | operation |
| `idx_success` | BTREE |  | success |
| `idx_trace_id` | BTREE |  | trace_id |
| `idx_created_at` | BTREE |  | created_at |
| `idx_webhook_success` | BTREE |  | webhook_type, success |
| `idx_raw_storage` | BTREE |  | raw_storage_id |

---

### `webhooks_config`

**Rows:** 10 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `webhook_type` | varchar(50) | NO | UNI | NULL |  |
| `enabled` | tinyint(1) | YES | MUL | 1 |  |
| `database_enabled` | tinyint(1) | YES |  | 1 |  |
| `file_logging_enabled` | tinyint(1) | YES |  | 1 |  |
| `max_retries` | int(11) | YES |  | 3 |  |
| `timeout_seconds` | int(11) | YES |  | 30 |  |
| `priority` | enum('low','medium','high','critical','emergency') | YES |  | medium |  |
| `target_table` | varchar(100) | YES |  | NULL |  |
| `handler_class` | varchar(100) | YES |  | NULL |  |
| `config_json` | longtext | YES |  | NULL |  |
| `created_by` | varchar(100) | YES |  | WEBHOOK_SYSTEM |  |
| `updated_by` | varchar(100) | YES |  | WEBHOOK_SYSTEM |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `webhook_type` | BTREE | ✓ | webhook_type |
| `idx_enabled` | BTREE |  | enabled |
| `idx_webhook_type` | BTREE |  | webhook_type |

---

### `webhooks_monitoring`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `webhook_type` | varchar(50) | NO | MUL | NULL |  |
| `metric_type` | enum('request_count','success_count','failure_count','avg_response_time','max_response_time') | NO | MUL | NULL |  |
| `metric_value` | decimal(15,4) | NO |  | NULL |  |
| `time_bucket` | datetime | NO | MUL | NULL |  |
| `bucket_size` | enum('minute','hour','day') | YES | MUL | hour |  |
| `created_by` | varchar(100) | YES |  | WEBHOOK_SYSTEM |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `unique_metric_bucket` | BTREE | ✓ | webhook_type, metric_type, time_bucket, bucket_size |
| `idx_webhook_type` | BTREE |  | webhook_type |
| `idx_metric_type` | BTREE |  | metric_type |
| `idx_time_bucket` | BTREE |  | time_bucket |
| `idx_bucket_size` | BTREE |  | bucket_size |

---

### `webhooks_raw_storage`

**Rows:** 1,951 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `webhook_type` | varchar(50) | NO | MUL | NULL |  |
| `raw_payload` | longtext | NO |  | NULL |  |
| `raw_headers` | longtext | YES |  | NULL |  |
| `http_method` | varchar(10) | NO |  | POST |  |
| `request_uri` | text | YES |  | NULL |  |
| `remote_ip` | varchar(45) | YES |  | NULL |  |
| `user_agent` | text | YES |  | NULL |  |
| `content_type` | varchar(100) | YES |  | NULL |  |
| `content_length` | int(11) | YES |  | NULL |  |
| `status` | enum('raw','processing','completed','failed','retry_needed') | YES | MUL | raw |  |
| `processing_status` | varchar(50) | YES |  | NULL |  |
| `processing_attempts` | int(11) | YES | MUL | 0 |  |
| `first_processing_attempt` | timestamp | YES |  | NULL |  |
| `last_processing_attempt` | timestamp | YES |  | NULL |  |
| `processing_error` | text | YES |  | NULL |  |
| `trace_id` | varchar(64) | YES | MUL | NULL |  |
| `received_at` | timestamp | YES |  | NULL |  |
| `created_by` | varchar(100) | YES |  | WEBHOOK_SYSTEM |  |
| `updated_by` | varchar(100) | YES |  | WEBHOOK_SYSTEM |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `processed_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_webhook_type` | BTREE |  | webhook_type |
| `idx_status` | BTREE |  | status |
| `idx_created_at` | BTREE |  | created_at |
| `idx_trace_id` | BTREE |  | trace_id |
| `idx_processing_attempts` | BTREE |  | processing_attempts |
| `idx_webhook_status` | BTREE |  | webhook_type, status |
| `idx_retry_needed` | BTREE |  | status, processing_attempts, last_processing_attempt |

---

### `webhooks_replay_queue`

**Rows:** 397 | **Engine:** InnoDB | **Collation:** latin1_swedish_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `raw_storage_id` | bigint(20) unsigned | NO | MUL | NULL |  |
| `webhook_type` | varchar(50) | NO | MUL | NULL |  |
| `retry_reason` | enum('processing_failed','database_error','timeout','invalid_payload','manual_retry') | NO |  | NULL |  |
| `retry_priority` | enum('low','medium','high','critical','emergency') | YES | MUL | medium |  |
| `scheduled_retry_at` | timestamp | NO | MUL | current_timestamp() | on update current_timestamp() |
| `max_retry_attempts` | int(11) | YES |  | 5 |  |
| `current_retry_attempt` | int(11) | YES |  | 0 |  |
| `last_retry_at` | timestamp | YES |  | NULL |  |
| `last_retry_error` | text | YES |  | NULL |  |
| `status` | enum('pending','processing','completed','failed','abandoned') | YES | MUL | pending |  |
| `trace_id` | varchar(64) | YES | MUL | NULL |  |
| `created_by` | varchar(100) | YES |  | WEBHOOK_SYSTEM |  |
| `updated_by` | varchar(100) | YES |  | WEBHOOK_SYSTEM |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_webhook_type` | BTREE |  | webhook_type |
| `idx_status` | BTREE |  | status |
| `idx_scheduled_retry` | BTREE |  | scheduled_retry_at |
| `idx_retry_priority` | BTREE |  | retry_priority |
| `idx_raw_storage_id` | BTREE |  | raw_storage_id |
| `idx_trace_id` | BTREE |  | trace_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `webhooks_replay_queue_ibfk_1` | `raw_storage_id` | `webhooks_raw_storage`.`id` |

---

### `website_order_views`

**Rows:** 688,060 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `order_id` | int(11) | NO | MUL | NULL |  |
| `outlet_id` | varchar(45) | NO |  | NULL |  |
| `staff_id` | int(11) | NO |  | NULL |  |
| `timestamp` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `WebsiteOrderViewIndex` | BTREE |  | order_id, outlet_id, staff_id |

---

### `whatsapp_conversations`

**Rows:** 59,422 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `phone_number` | varchar(20) | NO | MUL | NULL |  |
| `user_message` | text | NO |  | NULL |  |
| `bot_response` | text | NO |  | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_phone` | BTREE |  | phone_number |
| `idx_created` | BTREE |  | created_at |

---

### `wholesale_customers`

**Rows:** 501 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `first_name` | varchar(100) | NO |  | NULL |  |
| `last_name` | varchar(100) | NO |  | NULL |  |
| `email` | varchar(100) | NO |  | NULL |  |
| `phone_number` | varchar(100) | YES |  | NULL |  |
| `password` | varchar(100) | YES |  | NULL |  |
| `legal_name` | varchar(100) | NO |  | NULL |  |
| `trading_name` | varchar(100) | YES |  | NULL |  |
| `website` | varchar(100) | YES |  | NULL |  |
| `mailing_address_one` | varchar(100) | NO |  | NULL |  |
| `mailing_address_two` | varchar(100) | YES |  | NULL |  |
| `suburb` | varchar(100) | NO |  | NULL |  |
| `postcode` | varchar(100) | NO |  | NULL |  |
| `city` | varchar(100) | NO |  | NULL |  |
| `gst_number` | varchar(100) | YES |  | NULL |  |
| `nzbn` | varchar(100) | YES |  | NULL |  |
| `approved` | int(11) | YES |  | 0 |  |
| `show_home_page` | varchar(45) | YES |  | 1 |  |
| `created` | timestamp | NO |  | current_timestamp() |  |
| `avp` | varchar(45) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `xero_email_enhancement_log`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `transaction_id` | varchar(100) | YES | UNI | NULL |  |
| `contact_name` | varchar(200) | YES | MUL | NULL |  |
| `enhanced_name` | varchar(200) | YES |  | NULL |  |
| `email_count` | int(11) | YES |  | 0 |  |
| `confidence_boost` | decimal(5,2) | YES |  | 0.00 |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `transaction_id` | BTREE | ✓ | transaction_id |
| `idx_transaction` | BTREE |  | transaction_id |
| `idx_contact` | BTREE |  | contact_name |
| `idx_created` | BTREE |  | created_at |

---

### `xero_payroll_deductions`

**Rows:** 248 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `payroll_id` | int(10) unsigned | NO | MUL | NULL |  |
| `xero_employee_id` | varchar(100) | NO | MUL | NULL |  |
| `employee_name` | varchar(255) | NO |  | NULL |  |
| `user_id` | int(10) unsigned | YES | MUL | NULL |  |
| `vend_customer_id` | varchar(100) | YES |  | NULL |  |
| `deduction_type` | varchar(100) | NO |  | NULL |  |
| `deduction_code` | varchar(50) | YES |  | NULL |  |
| `amount` | decimal(10,2) | NO |  | 0.00 |  |
| `description` | text | YES |  | NULL |  |
| `vend_payment_id` | varchar(100) | YES |  | NULL |  |
| `allocated_amount` | decimal(10,2) | YES |  | 0.00 |  |
| `allocation_status` | enum('pending','allocated','failed','partial') | YES | MUL | pending |  |
| `allocated_at` | datetime | YES |  | NULL |  |
| `allocation_error` | text | YES |  | NULL |  |
| `created_at` | datetime | YES |  | current_timestamp() |  |
| `updated_at` | datetime | YES |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_employee` | BTREE |  | xero_employee_id |
| `idx_user_vend` | BTREE |  | user_id, vend_customer_id |
| `idx_allocation_status` | BTREE |  | allocation_status |
| `idx_payroll_employee` | BTREE |  | payroll_id, xero_employee_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `xero_payroll_deductions_ibfk_1` | `payroll_id` | `xero_payrolls`.`id` |

---

### `xero_payrolls`

**Rows:** 68 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `xero_payroll_id` | varchar(100) | NO | UNI | NULL |  |
| `pay_period_start` | date | NO | MUL | NULL |  |
| `pay_period_end` | date | NO |  | NULL |  |
| `payment_date` | date | NO | MUL | NULL |  |
| `total_gross_pay` | decimal(10,2) | YES |  | 0.00 |  |
| `total_deductions` | decimal(10,2) | YES |  | 0.00 |  |
| `employee_count` | int(10) unsigned | YES |  | 0 |  |
| `status` | enum('draft','posted','processed') | YES |  | draft |  |
| `raw_data` | longtext | YES |  | NULL |  |
| `cached_at` | datetime | NO |  | NULL |  |
| `is_cached` | tinyint(1) | YES | MUL | 0 |  |
| `created_at` | datetime | YES |  | current_timestamp() |  |
| `updated_at` | datetime | YES |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uk_xero_payroll_id` | BTREE | ✓ | xero_payroll_id |
| `idx_payment_date` | BTREE |  | payment_date |
| `idx_cached` | BTREE |  | is_cached, cached_at |
| `idx_pay_period` | BTREE |  | pay_period_start, pay_period_end |

---

### `xero_reconcile_rules`

**Rows:** 351 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `contactName` | varchar(100) | YES | MUL | NULL |  |
| `reference` | varchar(100) | YES |  | NULL |  |
| `description` | varchar(100) | YES |  | NULL |  |
| `bankAccountID` | varchar(100) | NO |  | NULL |  |
| `ledgerAccountID` | varchar(100) | NO |  | NULL |  |
| `GSTCode` | varchar(45) | YES |  | NULL |  |
| `alternative_name` | varchar(100) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `xero_unique` | BTREE | ✓ | contactName, reference, description |

---

### `xero_submission_log`

**Rows:** 16 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `request_id` | varchar(50) | YES | UNI | NULL |  |
| `statement_line_id` | varchar(100) | YES | MUL | NULL |  |
| `account_id` | varchar(100) | YES |  | NULL |  |
| `status` | enum('SENDING','SUCCESS','ERROR','HTTP_ERROR') | YES |  | SENDING |  |
| `details` | text | YES |  | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `request_id` | BTREE | ✓ | request_id |
| `idx_statement_line` | BTREE |  | statement_line_id |
| `idx_created_at` | BTREE |  | created_at |

---

### `zzz_backup_freight_rules`

**Rows:** 30 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `container` | varchar(50) | NO | UNI | NULL |  |
| `container_id` | int(11) | NO | PRI | NULL |  |
| `max_weight_grams` | int(11) | YES | MUL | NULL |  |
| `max_units` | int(11) | YES |  | NULL |  |
| `cost` | decimal(10,2) | NO | MUL | 0.01 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | container_id |
| `uq_fr_container` | BTREE | ✓ | container |
| `idx_fr_container_id` | BTREE |  | container_id |
| `ix_fr_cap_cost` | BTREE |  | max_weight_grams, cost |
| `ix_fr_cost` | BTREE |  | cost |

---

