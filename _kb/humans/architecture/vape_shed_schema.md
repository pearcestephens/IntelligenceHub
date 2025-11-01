# The Vape Shed Retail Commerce (www.vapeshed.co.nz)

**Database:** `dvaxgvsxmz`  
**Application:** dvaxgvsxmz (Retail Production)  
**Scanned:** 2025-10-25 13:04:51  

## Summary

- **Tables:** 84
- **Total Columns:** 904
- **Total Indexes:** 197
- **Foreign Keys:** 30

---

## Tables

### `abandoned_cart_reminders`

**Rows:** 37,354 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `user_id` | int(11) | NO | MUL | NULL |  |
| `cart_id` | bigint(20) | NO |  | NULL |  |
| `timestamp_first_reminder_sent` | timestamp | NO |  | current_timestamp() |  |
| `timestamp_second_reminder_sent` | varchar(45) | YES |  | NULL |  |
| `timestamp_third_reminder_sent` | varchar(45) | YES |  | NULL |  |
| `customer_visited_first_reminder` | timestamp | YES |  | NULL |  |
| `customer_visited_second_reminder` | timestamp | YES |  | NULL |  |
| `customer_visited_third_reminder` | timestamp | YES |  | NULL |  |
| `converted_order_id` | int(11) | YES |  | NULL |  |
| `unique_id` | varchar(45) | NO |  | NULL |  |
| `second_reminder_coupon_code_id` | int(11) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `customerIDKey_idx` | BTREE |  | user_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `customerIDKey` | `user_id` | `customers`.`id` |

---

### `bottle_sizes`

**Rows:** 9 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `size` | int(11) | NO |  | NULL |  |
| `status` | int(11) | NO |  | 1 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `career_opportunities`

**Rows:** 35 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `location` | varchar(100) | NO |  | NULL |  |
| `position` | varchar(100) | NO |  | NULL |  |
| `hours` | varchar(100) | NO |  | NULL |  |
| `employment_type` | varchar(100) | NO |  | NULL |  |
| `starting_date` | varchar(100) | NO |  | NULL |  |
| `active` | int(11) | NO |  | 1 |  |
| `outlet_id` | int(11) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `career_opportunities_submission`

**Rows:** 722 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `job_id` | int(11) | NO |  | NULL |  |
| `first_name` | varchar(100) | NO |  | NULL |  |
| `last_name` | varchar(100) | NO |  | NULL |  |
| `email` | varchar(100) | NO |  | NULL |  |
| `phone` | varchar(100) | NO |  | NULL |  |
| `vaping_experience` | mediumtext | NO |  | NULL |  |
| `cv_filename` | varchar(100) | NO |  | NULL |  |
| `date_created` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `categories`

**Rows:** 41 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `category_id` | smallint(6) | NO | PRI | NULL | auto_increment |
| `name` | varchar(100) | YES |  | NULL |  |
| `url_key` | varchar(100) | NO |  | NULL |  |
| `root_order` | smallint(6) | NO |  | 1 |  |
| `description` | mediumtext | YES |  | NULL |  |
| `sort_order` | decimal(5,2) | NO |  | 0.00 |  |
| `active` | smallint(6) | NO |  | 0 |  |
| `top` | smallint(6) | NO |  | 0 |  |
| `column` | smallint(6) | NO |  | 0 |  |
| `type` | smallint(6) | YES |  | 1 |  |
| `html` | mediumtext | YES |  | NULL |  |
| `is_retail_category` | smallint(6) | NO |  | 1 |  |
| `is_wholesale_category` | smallint(6) | NO |  | 0 |  |
| `wholesale_html` | mediumtext | YES |  | NULL |  |
| `wholesale_type` | smallint(6) | YES |  | 1 |  |
| `wholesale_top` | smallint(6) | YES |  | 0 |  |
| `wholesale_column` | smallint(6) | YES |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | category_id |
| `cat index` | BTREE |  | category_id, url_key, sort_order |

---

### `cities_towns`

**Rows:** 584 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `town_name` | varchar(45) | NO | UNI | NULL |  |
| `active` | int(11) | NO |  | 1 |  |
| `url_key` | varchar(100) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `town_name_UNIQUE` | BTREE | ✓ | town_name |

---

### `cities_towns_votes`

**Rows:** 7,183 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `city_id` | varchar(45) | NO |  | NULL |  |
| `ip_address` | varchar(45) | NO |  | NULL |  |
| `timestamp` | timestamp | NO |  | current_timestamp() |  |
| `session_id` | varchar(45) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `coinbase_payments`

**Rows:** 528 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `coinbase_id` | varchar(45) | NO |  | NULL |  |
| `created_at` | timestamp | YES |  | NULL |  |
| `expires_at` | timestamp | YES |  | NULL |  |
| `confirmed_at` | timestamp | YES |  | NULL |  |
| `status` | varchar(45) | NO |  | NULL |  |
| `order_total` | decimal(13,5) | NO |  | NULL |  |
| `order_id` | int(11) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `config`

**Rows:** 9 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `name` | varchar(45) | NO |  | NULL |  |
| `value` | varchar(1000) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `content_pages`

**Rows:** 61 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `meta_title` | varchar(100) | NO |  | NULL |  |
| `meta_desc` | varchar(200) | NO |  | NULL |  |
| `url_key` | varchar(100) | NO | UNI | NULL |  |
| `active` | varchar(45) | NO |  | 1 |  |
| `html` | longtext | NO |  | NULL |  |
| `include_php` | varchar(45) | YES |  | NULL |  |
| `page_title` | varchar(100) | NO | MUL | NULL |  |
| `last_updated` | timestamp | NO |  | current_timestamp() |  |
| `created` | timestamp | NO |  | current_timestamp() |  |
| `meta_keywords` | mediumtext | YES |  | NULL |  |
| `core_system_content` | int(11) | NO |  | 0 |  |
| `include_footer_headers` | int(11) | NO |  | 1 |  |
| `show_in_sitemap` | int(11) | YES |  | 1 |  |
| `is_retail_content` | int(11) | NO |  | 1 |  |
| `is_wholesale_content` | int(11) | NO |  | 0 |  |
| `wholesale_meta_title` | varchar(200) | YES |  | NULL |  |
| `wholesale_meta_desc` | varchar(200) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `url_key_UNIQUE` | BTREE | ✓ | url_key |
| `ContentPageIndex` | BTREE |  | id, url_key, include_php |
| `CMSSearchContent` | FULLTEXT |  | page_title, meta_desc, meta_title |

---

### `countries`

**Rows:** 5 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `name` | varchar(45) | NO |  | NULL |  |
| `currency_abbreviation` | varchar(45) | NO |  | NULL |  |
| `country_code` | int(11) | NO |  | NULL |  |
| `abbreviation` | varchar(45) | NO |  | NULL |  |
| `active` | int(11) | NO |  | 0 |  |
| `default` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `coupon_uses`

**Rows:** 1,790 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `coupon_id` | int(11) | NO |  | NULL |  |
| `customer_id` | int(11) | YES |  | NULL |  |
| `timestamp_used` | timestamp | NO |  | current_timestamp() |  |
| `status` | smallint(6) | NO |  | 1 |  |
| `cart_id` | bigint(20) | NO | MUL | NULL |  |
| `order_id` | int(11) | YES |  | NULL |  |
| `session_id` | varchar(45) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `CartCouponKey_idx` | BTREE |  | cart_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `CouponCart` | `cart_id` | `saved_carts`.`id` |

---

### `coupons`

**Rows:** 6 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `name` | varchar(45) | NO |  | NULL |  |
| `description` | varchar(45) | YES |  | NULL |  |
| `active` | smallint(6) | NO |  | NULL |  |
| `code` | varchar(45) | NO |  | NULL |  |
| `max_per_customer` | smallint(6) | NO |  | 0 |  |
| `max_total` | smallint(6) | NO |  | 0 |  |
| `created` | timestamp | NO |  | current_timestamp() |  |
| `free_shipping` | smallint(6) | NO |  | 0 |  |
| `percent` | smallint(6) | NO |  | 0 |  |
| `dollar_off` | decimal(10,4) | NO |  | 0.0000 |  |
| `apply_over_dollar_value` | decimal(10,4) | NO |  | 0.0000 |  |
| `coupon_ends` | timestamp | YES |  | NULL |  |
| `liquid_loyalty` | smallint(6) | NO |  | 0 |  |
| `is_retail_coupon` | int(11) | NO |  | 1 |  |
| `is_wholesale_coupon` | int(11) | NO |  | 0 |  |
| `gst` | int(11) | NO |  | 1 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `cron_output_logs`

**Rows:** 2,518,371 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `identifier` | varchar(100) | YES | MUL | NULL |  |
| `time_started` | timestamp | NO | MUL | current_timestamp() |  |
| `time_ended` | timestamp | YES |  | NULL |  |
| `status` | enum('success','failure') | YES | MUL | NULL |  |
| `error_message` | mediumtext | YES |  | NULL |  |
| `output` | mediumtext | YES |  | NULL |  |
| `command` | varchar(200) | YES | MUL | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_cron_identifier` | BTREE |  | identifier |
| `idx_cron_time_started` | BTREE |  | time_started |
| `idx_cron_status` | BTREE |  | status |
| `idx_cron_command` | BTREE |  | command |

---

### `customer_billing_address`

**Rows:** 46,491 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `customer_id` | int(11) | NO |  | NULL |  |
| `first_name` | varchar(100) | NO |  | NULL |  |
| `last_name` | varchar(100) | NO |  | NULL |  |
| `company` | varchar(100) | YES |  | NULL |  |
| `address_line_one` | varchar(100) | NO |  | NULL |  |
| `address_line_two` | varchar(100) | YES |  | NULL |  |
| `country` | varchar(100) | NO |  | NULL |  |
| `suburb` | varchar(100) | NO |  | NULL |  |
| `post_code` | varchar(100) | NO |  | NULL |  |
| `default` | int(11) | NO |  | 1 |  |
| `phone` | varchar(45) | YES |  | NULL |  |
| `city` | varchar(100) | NO |  | NULL |  |
| `state` | varchar(100) | YES |  | NULL |  |
| `email` | varchar(100) | NO |  | NULL |  |
| `same_as_shipping` | int(11) | NO |  | 1 |  |
| `created_at` | datetime | NO |  | current_timestamp() |  |
| `updated_at` | datetime | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `customer_feedback`

**Rows:** 1,632 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `user_id` | int(11) | NO |  | NULL |  |
| `feedback` | mediumtext | NO |  | NULL |  |
| `order_id` | int(11) | NO |  | NULL |  |
| `approved` | smallint(6) | NO |  | 0 |  |
| `time_submitted` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `customer_shipping_address`

**Rows:** 41,004 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `outlet_name` | varchar(100) | YES |  | NULL |  |
| `outlet_email` | varchar(100) | YES | MUL | NULL |  |
| `outlet_notes` | text | YES |  | NULL |  |
| `reference_code` | varchar(64) | YES |  | NULL |  |
| `customer_id` | int(11) | NO | MUL | NULL |  |
| `first_name` | varchar(100) | NO |  | NULL |  |
| `last_name` | varchar(100) | NO |  | NULL |  |
| `company` | varchar(100) | YES |  | NULL |  |
| `address_line_one` | varchar(100) | NO |  | NULL |  |
| `address_line_two` | varchar(100) | YES |  | NULL |  |
| `country` | varchar(100) | NO |  | NULL |  |
| `suburb` | varchar(255) | YES |  | NULL |  |
| `post_code` | varchar(100) | NO |  | NULL |  |
| `default` | int(11) | NO |  | 1 |  |
| `send_packing_slip` | tinyint(1) | YES |  | 0 |  |
| `is_active` | tinyint(1) | YES |  | 1 |  |
| `city` | varchar(100) | NO |  | NULL |  |
| `state` | varchar(100) | YES |  | NULL |  |
| `phone` | varchar(45) | YES |  | NULL |  |
| `address_json` | longtext | YES |  | NULL |  |
| `delivery_instructions` | text | YES |  | NULL |  |
| `first_name_store_manager` | varchar(100) | YES |  | NULL |  |
| `last_name_store_manager` | varchar(100) | YES |  | NULL |  |
| `created_at` | datetime | NO |  | current_timestamp() |  |
| `updated_at` | datetime | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `idx_outlet_email` | BTREE |  | outlet_email |
| `idx_customer_isactive` | BTREE |  | customer_id, is_active |
| `idx_customer_default` | BTREE |  | customer_id, default |
| `idx_customer_reference` | BTREE |  | customer_id, reference_code |

---

### `customers`

**Rows:** 42,262 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `first_name` | varchar(45) | NO |  | NULL |  |
| `last_name` | varchar(45) | NO |  | NULL |  |
| `business_name` | varchar(255) | YES |  | NULL |  |
| `trading_name` | varchar(255) | YES |  | NULL |  |
| `company_website` | varchar(255) | YES |  | NULL |  |
| `accounts_contact_first_name` | varchar(255) | YES |  | NULL |  |
| `accounts_contact_phone` | varchar(64) | YES |  | NULL |  |
| `accounts_contact_email` | varchar(255) | YES |  | NULL |  |
| `gst_number` | varchar(32) | YES |  | NULL |  |
| `nzbn_number` | varchar(32) | YES |  | NULL |  |
| `harp_avp_number` | varchar(64) | YES |  | NULL |  |
| `primary_phone` | mediumtext | YES |  | NULL |  |
| `email` | varchar(100) | NO | UNI | NULL |  |
| `password` | mediumtext | NO |  | NULL |  |
| `full_address` | mediumtext | YES |  | NULL |  |
| `google_address_object` | mediumtext | YES |  | NULL |  |
| `account_created` | timestamp | NO |  | current_timestamp() |  |
| `last_logged_in` | timestamp | NO |  | current_timestamp() |  |
| `guest_account` | smallint(6) | NO |  | 0 |  |
| `session_id` | varchar(45) | YES |  | NULL |  |
| `update_password` | smallint(6) | NO |  | 0 |  |
| `assigned_outlet_id` | varchar(45) | YES |  | NULL |  |
| `password_reset_hash` | varchar(45) | YES |  | NULL |  |
| `password_reset_timestamp` | timestamp | YES |  | NULL |  |
| `bank_account` | varchar(45) | YES |  | NULL |  |
| `is_wholesale_account` | int(1) | NO |  | 0 |  |
| `unsubscribe` | int(1) | NO |  | 0 |  |
| `wholesale_account_active` | int(1) | NO |  | 0 |  |
| `account_r18_verified` | int(1) | NO |  | 0 |  |
| `suspended_reason` | mediumtext | YES |  | NULL |  |
| `account_r18_email_sent` | timestamp | YES |  | NULL |  |
| `account_r18_sent_by_staff_id` | smallint(6) | YES |  | NULL |  |
| `account_r18_sent_by_outlet_id` | smallint(6) | YES |  | NULL |  |
| `review_admin` | int(1) | NO |  | 0 |  |
| `alias` | varchar(45) | YES |  | NULL |  |
| `accounts_contact_last_name` | varchar(255) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `email` | BTREE | ✓ | email |
| `customerIDIndex` | BTREE |  | id |

---

### `customers_identification`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `customer_id` | varchar(45) | NO |  | NULL |  |
| `created` | timestamp | NO |  | current_timestamp() |  |
| `verify_via_file_upload` | varchar(45) | NO |  | 0 |  |
| `verify_over_phone` | smallint(6) | NO |  | 0 |  |
| `verified_over_phone_number` | varchar(45) | YES |  | NULL |  |
| `verify_instore` | smallint(6) | NO |  | 0 |  |
| `verify_instore_details` | mediumtext | YES |  | NULL |  |
| `verify_instore_phone` | varchar(45) | YES |  | NULL |  |
| `send_email_through` | smallint(6) | NO |  | 0 |  |
| `verified_another_way` | smallint(6) | NO |  | 0 |  |
| `verify_another_way_notes` | varchar(45) | YES |  | NULL |  |
| `verify_another_way_phone` | varchar(100) | YES |  | NULL |  |
| `verify_another_way_facebook` | varchar(100) | YES |  | NULL |  |
| `verify_another_way_linkedIn` | varchar(100) | YES |  | NULL |  |
| `declared_under_18` | smallint(6) | NO |  | 0 |  |
| `declared_under_18_bank_account` | varchar(45) | YES |  | NULL |  |
| `file_name` | varchar(100) | YES |  | NULL |  |
| `approved` | smallint(6) | YES |  | 0 |  |
| `approved_timestamp` | timestamp | YES |  | NULL |  |
| `approved_by_staff` | smallint(6) | YES |  | NULL |  |
| `declined_reason` | mediumtext | YES |  | NULL |  |
| `notes` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `customers_underage`

**Rows:** 804 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `customer_id` | int(11) | NO | PRI | NULL |  |
| `vend_id` | varchar(100) | YES |  | NULL |  |
| `first_name` | varchar(100) | NO |  | NULL |  |
| `last_name` | varchar(100) | NO |  | NULL |  |
| `email` | varchar(100) | NO |  | NULL |  |
| `phone_number` | varchar(100) | YES |  | NULL |  |
| `billing_first_name` | varchar(100) | NO |  | NULL |  |
| `billing_last_name` | varchar(100) | NO |  | NULL |  |
| `billing_company` | varchar(100) | YES |  | NULL |  |
| `billing_phone` | varchar(100) | YES |  | NULL |  |
| `billing_address_line_one` | varchar(100) | NO |  | NULL |  |
| `billing_address_line_two` | varchar(100) | YES |  | NULL |  |
| `billing_city` | varchar(100) | NO |  | NULL |  |
| `billing_suburb` | varchar(100) | NO |  | NULL |  |
| `billing_post_code` | varchar(100) | NO |  | NULL |  |
| `billing_state` | varchar(100) | YES |  | NULL |  |
| `billing_country` | varchar(100) | NO |  | NULL |  |
| `shipping_first_name` | varchar(100) | NO |  | NULL |  |
| `shipping_last_name` | varchar(100) | NO |  | NULL |  |
| `shipping_company` | varchar(100) | YES |  | NULL |  |
| `shipping_phone` | varchar(100) | YES |  | NULL |  |
| `shipping_address_line_one` | varchar(100) | NO |  | NULL |  |
| `shipping_address_line_two` | varchar(100) | NO |  | NULL |  |
| `shipping_city` | varchar(100) | NO |  | NULL |  |
| `shipping_suburb` | varchar(100) | NO |  | NULL |  |
| `shipping_post_code` | varchar(100) | NO |  | NULL |  |
| `shipping_state` | varchar(100) | YES |  | NULL |  |
| `shipping_country` | varchar(100) | NO |  | NULL |  |
| `banned_created` | timestamp | NO |  | current_timestamp() |  |
| `order_id` | int(11) | NO | PRI | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id, order_id, customer_id |
| `customerIDIndex` | BTREE |  | customer_id |
| `customerUnderageOrderIDIndex` | BTREE |  | order_id |

---

### `debug_data_table`

**Rows:** 45 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `name` | mediumtext | YES |  | NULL |  |
| `value` | mediumtext | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `email_queue`

**Rows:** 2,182 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `email_from` | varchar(100) | NO |  | NULL |  |
| `name_from` | varchar(100) | NO |  | NULL |  |
| `name_to` | varchar(100) | NO |  | NULL |  |
| `email_to` | varchar(100) | NO | MUL | NULL |  |
| `subject` | varchar(100) | NO |  | NULL |  |
| `email_body` | mediumtext | YES |  | NULL |  |
| `time_created` | timestamp | NO | MUL | current_timestamp() |  |
| `sent` | int(11) | NO | MUL | 0 |  |
| `time_sent` | timestamp | YES |  | NULL |  |
| `error` | mediumtext | YES |  | NULL |  |
| `failed` | int(11) | NO | MUL | 0 |  |
| `email_reply_to` | varchar(100) | NO |  | NULL |  |
| `name_reply_to` | varchar(100) | NO |  | NULL |  |
| `message_id` | varchar(100) | YES | MUL | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `FailedIndex` | BTREE |  | failed |
| `SentIndex` | BTREE |  | sent |
| `timeCreated` | BTREE |  | time_created |
| `SendGridMessageID` | BTREE |  | message_id |
| `emailToIndex` | BTREE |  | email_to |

---

### `faulty_products`

**Rows:** 658 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `cis_fault_id` | int(11) | NO |  | NULL |  |
| `customer_id` | int(11) | NO |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `status` | int(11) | NO |  | 0 |  |
| `supply_cost` | decimal(13,5) | NO |  | 0.00000 |  |
| `order_id` | int(11) | NO |  | NULL |  |
| `order_product_id` | int(11) | NO |  | NULL |  |
| `deleted_at` | timestamp | YES |  | NULL |  |
| `coupon_id` | int(11) | YES |  | NULL |  |
| `coupon_created` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `flavour_categories`

**Rows:** 91 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `flavour_name` | varchar(45) | NO | UNI | NULL |  |
| `status` | int(11) | NO |  | 1 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `flavour_name_UNIQUE` | BTREE | ✓ | flavour_name |

---

### `flavour_categories_products`

**Rows:** 5,336 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `product_id` | int(11) | NO | MUL | NULL |  |
| `flavour_category_id` | int(11) | NO | MUL | NULL |  |
| `main_profile` | int(11) | NO |  | 0 |  |
| `secondary_profile` | int(11) | NO |  | 0 |  |
| `third_profile` | int(11) | NO |  | 0 |  |
| `fourth_profile` | int(11) | NO |  | 0 |  |
| `fifth_profile` | int(11) | NO |  | 0 |  |
| `sixth_profile` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `flavourProductKey_idx` | BTREE |  | product_id |
| `flavourCat_idx` | BTREE |  | flavour_category_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `flavourCat` | `flavour_category_id` | `flavour_categories`.`id` |
| `flavourProductCat` | `product_id` | `products`.`product_id` |

---

### `gst_types`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `label` | varchar(45) | NO |  | NULL |  |
| `active` | int(11) | NO |  | NULL |  |
| `gst_percent` | float | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `javascript_error_logging`

**Rows:** 19 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `user` | varchar(45) | YES |  | NULL |  |
| `cartContents` | mediumtext | YES |  | NULL |  |
| `page_url` | varchar(100) | YES |  | NULL |  |
| `error_message` | mediumtext | YES |  | NULL |  |
| `user_agent` | mediumtext | YES |  | NULL |  |
| `js_url` | mediumtext | YES |  | NULL |  |
| `line_number` | mediumtext | YES |  | NULL |  |
| `column` | mediumtext | YES |  | NULL |  |
| `stack_trace` | mediumtext | YES |  | NULL |  |
| `ip_address` | varchar(45) | YES |  | NULL |  |
| `created` | timestamp | NO |  | current_timestamp() |  |
| `client_url` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `juice_finder_logs`

**Rows:** 122,894 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `user_id` | int(11) | YES |  | NULL |  |
| `session_id` | varchar(45) | YES |  | NULL |  |
| `created_at` | varchar(45) | YES |  | NULL |  |
| `nicotine_type` | int(11) | YES |  | NULL |  |
| `pg_vg_ratio` | int(11) | YES |  | NULL |  |
| `bottle_size` | int(11) | YES |  | NULL |  |
| `price_min` | decimal(13,5) | YES |  | NULL |  |
| `price_max` | decimal(13,5) | YES |  | NULL |  |
| `flavour_profile` | int(11) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `location_content`

**Rows:** 18 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `location_name` | varchar(255) | NO | UNI | NULL |  |
| `heading` | varchar(255) | YES |  | NULL |  |
| `content` | text | YES |  | NULL |  |
| `active` | tinyint(1) | NO |  | 1 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `unique_location_name` | BTREE | ✓ | location_name |

---

### `login_attempts`

**Rows:** 69,506 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `email` | varchar(45) | NO |  | NULL |  |
| `ip_address` | varchar(45) | NO |  | NULL |  |
| `time_created` | timestamp | NO |  | current_timestamp() |  |
| `user_id` | int(11) | YES |  | NULL |  |
| `success` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `manufacturers`

**Rows:** 300 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | smallint(6) | NO | PRI | NULL | auto_increment |
| `name` | varchar(45) | NO | UNI | NULL |  |
| `url_key` | varchar(45) | NO | PRI | NULL |  |
| `active` | smallint(6) | NO |  | 1 |  |
| `image` | varchar(45) | YES |  | NULL |  |
| `available_for_sale_retail` | int(11) | NO |  | 1 |  |
| `available_for_sale_wholesale` | int(11) | NO |  | 1 |  |
| `retail_description` | mediumtext | YES |  | NULL |  |
| `website` | varchar(45) | YES |  | NULL |  |
| `retail_description_backup` | text | YES |  | NULL |  |
| `website_backup` | varchar(255) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id, url_key |
| `name_UNIQUE` | BTREE | ✓ | name |

---

### `nicotine_types`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `nicotine_label` | varchar(45) | NO |  | NULL |  |
| `status` | int(11) | NO |  | 1 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `orders`

**Rows:** 196,492 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `order_id` | int(11) | NO | PRI | NULL | auto_increment |
| `customer_id` | int(11) | NO | MUL | NULL |  |
| `order_created` | timestamp | NO |  | current_timestamp() |  |
| `order_status` | smallint(6) | NO |  | 1 |  |
| `payment_method` | varchar(100) | NO | MUL | NULL |  |
| `payment_method_label` | varchar(100) | NO |  | NULL |  |
| `shipping_method` | varchar(100) | NO |  | NULL |  |
| `shipping_method_label` | varchar(100) | NO |  | NULL |  |
| `sub_total_before_gst` | decimal(13,5) | NO |  | 0.00000 |  |
| `sub_total_after_gst` | decimal(13,5) | NO |  | 0.00000 |  |
| `shipping_cost_before_gst` | decimal(13,5) | NO |  | 0.00000 |  |
| `shipping_cost_after_gst` | decimal(13,5) | NO |  | 0.00000 |  |
| `order_total_before_gst` | decimal(13,5) | NO |  | 0.00000 |  |
| `coupon_total_before_gst` | decimal(13,5) | NO |  | 0.00000 |  |
| `order_total_after_gst` | decimal(13,5) | NO |  | 0.00000 |  |
| `coupon_data` | mediumtext | YES |  | NULL |  |
| `order_notes` | mediumtext | YES |  | NULL |  |
| `billing_first_name` | varchar(100) | NO |  | NULL |  |
| `billing_last_name` | varchar(100) | NO |  | NULL |  |
| `billing_email` | varchar(100) | NO |  | NULL |  |
| `billing_company` | varchar(100) | YES |  | NULL |  |
| `billing_phone` | varchar(100) | YES |  | NULL |  |
| `billing_address_one` | varchar(100) | NO |  | NULL |  |
| `billing_address_two` | varchar(100) | NO |  | NULL |  |
| `billing_city` | varchar(100) | NO |  | NULL |  |
| `billing_suburb` | varchar(100) | NO |  | NULL |  |
| `billing_country` | varchar(100) | NO |  | NULL |  |
| `billing_state` | varchar(100) | YES |  | NULL |  |
| `billing_postcode` | varchar(100) | NO |  | NULL |  |
| `shipping_first_name` | varchar(100) | NO |  | NULL |  |
| `shipping_last_name` | varchar(100) | NO |  | NULL |  |
| `shipping_company` | varchar(100) | YES |  | NULL |  |
| `shipping_phone` | varchar(100) | YES |  | NULL |  |
| `same_billing` | int(11) | NO |  | NULL |  |
| `shipping_address_one` | varchar(100) | NO |  | NULL |  |
| `shipping_address_two` | varchar(100) | NO |  | NULL |  |
| `shipping_city` | varchar(100) | NO |  | NULL |  |
| `shipping_suburb` | varchar(100) | NO |  | NULL |  |
| `shipping_postcode` | varchar(100) | NO |  | NULL |  |
| `shipping_state` | varchar(100) | YES |  | NULL |  |
| `shipping_country` | varchar(100) | NO |  | NULL |  |
| `ip_address` | varchar(100) | NO |  | NULL |  |
| `gst_percent_charged` | decimal(13,5) | NO |  | 15.00000 |  |
| `store_pickup_id` | int(11) | YES |  | NULL |  |
| `store_pickup_name` | varchar(100) | YES |  | NULL |  |
| `customer_first_name` | varchar(100) | NO |  | NULL |  |
| `customer_last_name` | varchar(100) | NO |  | NULL |  |
| `customer_email` | varchar(100) | NO |  | NULL |  |
| `customer_phone` | varchar(100) | YES |  | NULL |  |
| `on_hold` | smallint(6) | NO |  | 0 |  |
| `order_total_final` | decimal(13,5) | NO |  | 0.00000 |  |
| `seen_confirmation` | smallint(6) | NO |  | 0 |  |
| `customer_pickup_notified_email` | int(11) | NO |  | 0 |  |
| `loyalty_used` | decimal(13,5) | NO |  | 0.00000 |  |
| `is_wholesale_order` | int(11) | NO |  | 0 |  |
| `vape_drop_store_id` | smallint(6) | YES |  | NULL |  |
| `vape_drop_store_name` | varchar(45) | YES |  | NULL |  |
| `staff_delivered_id` | int(11) | YES |  | NULL |  |
| `original_order_total_final` | decimal(13,5) | YES |  | NULL |  |
| `original_order_contents_json` | longtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | order_id |
| `Customer` | BTREE |  | customer_id |
| `idx_orders_payment_shipping` | BTREE |  | payment_method, shipping_method, order_id |

---

### `orders_history`

**Rows:** 735,693 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `order_id` | int(11) | NO | MUL | NULL |  |
| `description` | mediumtext | NO |  | NULL |  |
| `staff_id` | smallint(6) | YES |  | NULL |  |
| `timestamp` | timestamp | NO |  | current_timestamp() |  |
| `manually_added` | int(11) | NO |  | 0 |  |
| `deleted_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `orderHistory_idx` | BTREE |  | order_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `OrderHistory` | `order_id` | `orders`.`order_id` |

---

### `orders_invoices`

**Rows:** 177,960 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `order_id` | int(11) | NO | MUL | NULL |  |
| `created` | timestamp | NO |  | current_timestamp() |  |
| `payment_method` | smallint(6) | NO |  | NULL |  |
| `created_by` | smallint(6) | YES |  | NULL |  |
| `total_paid` | decimal(10,4) | YES |  | NULL |  |
| `reference` | mediumtext | YES |  | NULL |  |
| `payment_method_label` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `OrderInvoices_idx` | BTREE |  | order_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `OrderInvoices` | `order_id` | `orders`.`order_id` |

---

### `orders_products`

**Rows:** 695,693 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `order_products_id` | int(11) | NO | PRI | NULL | auto_increment |
| `order_id` | int(11) | NO | MUL | NULL |  |
| `product_name` | mediumtext | NO |  | NULL |  |
| `product_qty` | int(11) | NO |  | NULL |  |
| `product_options` | mediumtext | YES |  | NULL |  |
| `product_price` | decimal(13,5) | NO |  | NULL |  |
| `product_image_link` | mediumtext | YES |  | NULL |  |
| `product_id` | int(11) | NO | MUL | NULL |  |
| `product_option` | int(11) | NO |  | 0 |  |
| `container_id` | int(11) | YES |  | NULL |  |
| `sold_in_qty` | smallint(6) | NO |  | 1 |  |
| `manually_added` | smallint(6) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | order_products_id |
| `Order ID` | BTREE |  | order_id |
| `Product_idx` | BTREE |  | product_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `Order` | `order_id` | `orders`.`order_id` |

---

### `orders_products_assigned_outlets`

**Rows:** 460,989 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `order_product_id` | int(11) | YES | MUL | NULL |  |
| `outlet_id` | int(11) | NO | MUL | NULL |  |
| `order_id` | int(11) | NO | MUL | NULL |  |
| `product_id` | int(11) | YES | MUL | NULL |  |
| `qty` | int(11) | NO |  | NULL |  |
| `time_assigned` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `Index` | BTREE |  | order_product_id, order_id |
| `PendingIndex` | BTREE |  | order_id |
| `OutletIndex` | BTREE |  | outlet_id |
| `ProductIndex` | BTREE |  | product_id |
| `idx_outlet_time_order` | BTREE |  | outlet_id, time_assigned, order_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `orders_products_assigned` | `order_product_id` | `orders_products`.`order_products_id` |

---

### `orders_shipments`

**Rows:** 193,452 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `order_id` | int(11) | NO | MUL | NULL |  |
| `created` | timestamp | NO | MUL | current_timestamp() |  |
| `created_by_user` | int(11) | NO |  | NULL |  |
| `shipment_notes` | mediumtext | YES |  | NULL |  |
| `tracking_number` | mediumtext | YES | MUL | NULL |  |
| `created_by_outlet` | int(11) | YES |  | NULL |  |
| `nzpost_order_id` | int(11) | YES |  | NULL |  |
| `gss_order_id` | int(11) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `ShipmentsProducts_idx` | BTREE |  | order_id |
| `ShipmentsIndex` | BTREE |  | created, order_id, created_by_outlet |
| `trackingNumberIndex` | BTREE |  | tracking_number |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `ShipmentsProducts` | `order_id` | `orders`.`order_id` |

---

### `orders_shipments_products`

**Rows:** 382,905 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `order_product_id` | int(11) | NO | MUL | NULL |  |
| `qty_sent` | int(11) | NO |  | NULL |  |
| `shipment_id` | int(11) | YES | MUL | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `ShipmentProducts_idx` | BTREE |  | shipment_id |
| `ProductAssignedIDIndex` | BTREE |  | order_product_id, shipment_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `ShipmentProducts` | `shipment_id` | `orders_shipments`.`id` |

---

### `orders_status`

**Rows:** 7 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `label` | varchar(45) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `outlet_inventory`

**Rows:** 162,800 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `outlet_id` | int(11) | NO | PRI | NULL |  |
| `product_id` | int(11) | NO | PRI | NULL |  |
| `qty` | int(11) | NO |  | 0 |  |
| `last_updated` | timestamp | NO | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | outlet_id, product_id |
| `INDEX` | BTREE |  | outlet_id, product_id, qty |
| `OutletInventory_idx` | BTREE |  | product_id |
| `idx_outlet_inventory_updated` | BTREE |  | last_updated |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `ProductInventoryKey` | `product_id` | `products`.`product_id` |

---

### `outlets`

**Rows:** 18 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `outlet_id` | smallint(6) | NO | PRI | NULL | auto_increment |
| `outlet_name` | varchar(45) | NO |  | NULL |  |
| `active` | smallint(6) | NO |  | NULL |  |
| `vend_id` | varchar(45) | NO |  | NULL |  |
| `google_link` | mediumtext | YES |  | NULL |  |
| `outlet_lat` | varchar(45) | NO |  | NULL |  |
| `outlet_long` | varchar(45) | NO |  | NULL |  |
| `address` | varchar(100) | NO |  | NULL |  |
| `wholesale_active` | smallint(6) | NO |  | 0 |  |
| `vape_drop_active` | smallint(6) | NO |  | 0 |  |
| `click_collect_active` | smallint(6) | NO |  | 1 |  |
| `location_page_active` | smallint(6) | NO |  | 1 |  |
| `phone` | varchar(45) | YES |  | NULL |  |
| `email_address` | varchar(45) | YES |  | NULL |  |
| `google_rating` | decimal(10,1) | YES |  | NULL |  |
| `google_review_count` | smallint(6) | YES |  | NULL |  |
| `physical_street_number` | varchar(45) | YES |  | NULL |  |
| `physical_street` | varchar(45) | YES |  | NULL |  |
| `physical_address_1` | varchar(45) | YES |  | NULL |  |
| `physical_suburb` | varchar(45) | YES |  | NULL |  |
| `physical_postcode` | varchar(45) | YES |  | NULL |  |
| `physical_city` | varchar(45) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | outlet_id |

---

### `outlets_hours`

**Rows:** 18 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `outlet_id` | int(11) | NO |  | NULL |  |
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

### `outlets_images`

**Rows:** 18 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `outlet_id` | int(11) | NO |  | NULL |  |
| `image_filename` | varchar(100) | NO |  | NULL |  |
| `active` | int(11) | NO |  | 1 |  |
| `default` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `paymark_eftpos`

**Rows:** 17,323 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `order_id` | int(11) | NO |  | NULL |  |
| `amount` | varchar(45) | NO |  | NULL |  |
| `status` | varchar(45) | NO |  | NULL |  |
| `paymark_id` | varchar(45) | NO | UNI | NULL |  |
| `access_token` | varchar(45) | NO |  | NULL |  |
| `time_created` | timestamp | YES |  | current_timestamp() |  |
| `time_updated` | timestamp | YES |  | NULL |  |
| `refund_json` | mediumtext | YES |  | NULL |  |
| `paymark_transaction_id` | varchar(45) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `paymark_id_UNIQUE` | BTREE | ✓ | paymark_id |

---

### `payment_methods`

**Rows:** 11 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `label` | varchar(200) | NO |  | NULL |  |
| `checkout_icon` | varchar(45) | YES |  | NULL |  |
| `description` | mediumtext | NO |  | NULL |  |
| `active` | smallint(6) | NO |  | 1 |  |
| `identifier` | varchar(45) | NO | UNI | NULL |  |
| `sort` | smallint(6) | NO |  | 0 |  |
| `default` | smallint(6) | NO |  | 0 |  |
| `additional_info` | mediumtext | YES |  | NULL |  |
| `is_retail_method` | int(11) | NO |  | 1 |  |
| `is_wholesale_method` | int(11) | NO |  | 0 |  |
| `wholesale_default` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `identifier_UNIQUE` | BTREE | ✓ | identifier |

---

### `pgvg_ratios`

**Rows:** 7 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `ratio` | varchar(45) | YES |  | NULL |  |
| `status` | int(11) | NO |  | 1 |  |
| `pg` | int(11) | NO |  | NULL |  |
| `vg` | int(11) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `php_sessions`

**Rows:** 135,195 | **Engine:** InnoDB | **Collation:** utf8mb4_general_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | varchar(128) | NO | PRI | NULL |  |
| `data` | mediumblob | NO |  | NULL |  |
| `expires_at` | datetime | NO | MUL | NULL |  |
| `updated_at` | datetime | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `expires_at_idx` | BTREE |  | expires_at |

---

### `poli_payments`

**Rows:** 29,178 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `order_id` | int(11) | NO | MUL | NULL |  |
| `transaction_ref_no` | varchar(45) | NO | PRI | NULL |  |
| `transaction_created` | timestamp | NO |  | current_timestamp() |  |
| `transaction_status` | varchar(45) | YES |  | NULL |  |
| `token` | varchar(45) | YES |  | NULL |  |
| `payment_amount` | varchar(45) | YES |  | NULL |  |
| `amount_paid` | varchar(45) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | transaction_ref_no |
| `poliPayments_idx` | BTREE |  | order_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `PoliOrdersKey` | `order_id` | `orders`.`order_id` |

---

### `prescriptions`

**Rows:** 558 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `customer_id` | int(11) | YES |  | NULL |  |
| `filename` | varchar(45) | NO |  | NULL |  |
| `created` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `price_beat_queries`

**Rows:** 14,407 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `product_id` | int(11) | NO |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |
| `session_id` | varchar(45) | YES |  | NULL |  |
| `customer_id` | int(11) | YES |  | NULL |  |
| `url_queried` | mediumtext | NO |  | NULL |  |
| `manual_check` | int(11) | NO |  | 0 |  |
| `status` | int(11) | NO |  | 0 |  |
| `found_match` | int(11) | NO |  | NULL |  |
| `found_price` | decimal(13,5) | NO |  | NULL |  |
| `offered_price` | decimal(13,5) | NO |  | NULL |  |
| `http_status_code` | int(11) | NO |  | NULL |  |
| `decision_made_by_staff` | int(11) | YES |  | NULL |  |
| `decision_made_by_staff_time` | timestamp | YES |  | NULL |  |
| `decline_reason` | mediumtext | YES |  | NULL |  |
| `made_by_ip` | varchar(45) | NO |  | NULL |  |
| `found_product_name` | mediumtext | YES |  | NULL |  |
| `engine_used` | varchar(45) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `price_beat_queries_helper_urls`

**Rows:** 9 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `url` | varchar(200) | YES |  | NULL |  |
| `product_id` | int(11) | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `product_addon_templates`

**Rows:** 14 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `name` | varchar(100) | NO |  | NULL |  |
| `required` | int(11) | NO |  | 0 |  |
| `type` | varchar(45) | NO |  | checkbox |  |
| `sort` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `product_addon_values`

**Rows:** 95 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `product_addon_id` | int(11) | NO |  | NULL |  |
| `product_id` | int(11) | YES |  | NULL |  |
| `option_label` | varchar(100) | NO |  | NULL |  |
| `qty_sold_in` | int(11) | NO |  | 1 |  |
| `default` | int(11) | NO |  | 0 |  |
| `price` | decimal(13,5) | NO |  | 0.00000 |  |
| `sort` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `product_categories`

**Rows:** 31,404 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `category_id` | smallint(6) | NO | MUL | NULL |  |
| `product_id` | int(11) | NO | MUL | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `Categories_id_index` | BTREE |  | category_id, product_id, id |
| `PCProdKey_idx` | BTREE |  | product_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `category_foreign_key` | `category_id` | `categories`.`category_id` |
| `product_categories` | `product_id` | `products`.`product_id` |

---

### `product_compatibility`

**Rows:** 205 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `product_id` | int(11) | NO | PRI | NULL |  |
| `compatible_product_id` | int(11) | NO | PRI | NULL |  |
| `compatible_product_name` | varchar(255) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | product_id, compatible_product_id |
| `compatible_product_id` | BTREE |  | compatible_product_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `product_compatibility_ibfk_1` | `product_id` | `products`.`product_id` |

---

### `product_images`

**Rows:** 7,452 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `product_id` | int(11) | NO | MUL | NULL |  |
| `image_url` | varchar(100) | NO |  | NULL |  |
| `sort_order` | smallint(6) | NO |  | 0 |  |
| `main_image` | smallint(6) | NO |  | 0 |  |
| `linked_product_id` | varchar(10) | YES | MUL | NULL |  |
| `small` | varchar(100) | YES |  | NULL |  |
| `medium` | varchar(100) | YES |  | NULL |  |
| `large` | varchar(100) | YES |  | NULL |  |
| `gradient_css` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `productImageIndex_idx` | BTREE |  | product_id |
| `ProductLinkedImages` | BTREE |  | linked_product_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `productImageIndex` | `product_id` | `products`.`product_id` |

---

### `product_in_stock_reminders`

**Rows:** 7,594 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `product_id` | int(11) | NO | MUL | NULL |  |
| `user_id` | int(11) | YES |  | NULL |  |
| `session_id` | varchar(45) | YES |  | NULL |  |
| `email` | mediumtext | NO |  | NULL |  |
| `time_created` | timestamp | NO |  | current_timestamp() |  |
| `reminded` | smallint(6) | NO |  | 0 |  |
| `reminded_time` | timestamp | YES |  | NULL |  |
| `failed` | int(11) | YES |  | 0 |  |
| `failed_time` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `ProductID` | BTREE |  | product_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `product_stock_reminder` | `product_id` | `products`.`product_id` |

---

### `product_layout_types`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `layout_id` | int(11) | NO | PRI | NULL | auto_increment |
| `layout_filename` | varchar(45) | NO |  | NULL |  |
| `layout_name` | varchar(45) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | layout_id |

---

### `product_option_values`

**Rows:** 12,300 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `product_option_id` | int(11) | NO | MUL | NULL |  |
| `default` | smallint(6) | NO |  | 0 |  |
| `price` | decimal(10,4) | NO |  | 0.0000 |  |
| `product_id` | int(11) | YES | MUL | NULL |  |
| `sort_order` | smallint(6) | NO |  | 0 |  |
| `name` | mediumtext | NO |  | NULL |  |
| `qty_sold_in` | smallint(6) | NO |  | 1 |  |
| `image_id` | varchar(10) | YES |  | NULL |  |
| `wholesale_price` | decimal(10,4) | NO |  | 0.0000 |  |
| `use_product_id_price` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `option_index` | BTREE |  | id, product_option_id, product_id, qty_sold_in |
| `ProductOptionValue_idx` | BTREE |  | product_option_id |
| `ProductKey_idx` | BTREE |  | product_id, qty_sold_in |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `product_options` | `product_option_id` | `product_options`.`id` |

---

### `product_options`

**Rows:** 2,587 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `option_type` | varchar(45) | NO |  | NULL |  |
| `option_name` | varchar(45) | NO |  | NULL |  |
| `is_required` | smallint(6) | NO |  | 0 |  |
| `sort_order` | smallint(6) | NO |  | 0 |  |
| `product_id` | int(11) | NO | MUL | NULL |  |
| `is_colour_dropdown` | smallint(6) | NO |  | 0 |  |
| `template_id` | smallint(6) | NO | MUL | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `productId` | BTREE |  | product_id |
| `productIndexID` | BTREE |  | id |
| `TemplateIDIndex` | BTREE |  | template_id |

---

### `product_questions`

**Rows:** 746 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `product_id` | int(11) | NO | MUL | NULL |  |
| `supplied_name` | varchar(45) | NO |  | NULL |  |
| `user_id` | int(11) | YES |  | NULL |  |
| `question` | mediumtext | NO |  | NULL |  |
| `answer` | mediumtext | YES |  | NULL |  |
| `date_asked` | timestamp | NO |  | current_timestamp() |  |
| `date_answered` | timestamp | YES |  | NULL |  |
| `email_address` | varchar(45) | NO |  | NULL |  |
| `status` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `product_questions_idx` | BTREE |  | product_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `product_questions` | `product_id` | `products`.`product_id` |

---

### `product_tiered_pricing`

**Rows:** 2,356 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `product_id` | int(11) | YES | UNI | NULL |  |
| `second_tier_price_excl_gst` | decimal(13,5) | NO |  | NULL |  |
| `third_tier_price_excl_gst` | decimal(13,5) | YES |  | NULL |  |
| `fourth_tier_price_excl_gst` | decimal(13,5) | YES |  | NULL |  |
| `fifth_tier_price_excl_gst` | decimal(13,5) | YES |  | NULL |  |
| `second_tier_qty` | smallint(6) | NO |  | NULL |  |
| `third_tier_qty` | smallint(6) | YES |  | NULL |  |
| `fourth_tier_qty` | smallint(6) | YES |  | NULL |  |
| `fifth_tier_qty` | smallint(6) | YES |  | NULL |  |
| `retail_pricing` | smallint(6) | NO |  | 1 |  |
| `wholesale_pricing` | smallint(6) | NO |  | 0 |  |
| `retail_automated_generated` | smallint(6) | NO |  | 1 |  |
| `wholesale_automated_generated` | smallint(6) | NO |  | 1 |  |
| `created` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `product_id_UNIQUE` | BTREE | ✓ | product_id |
| `productIDKey_idx` | BTREE |  | product_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `productIDKey` | `product_id` | `products`.`product_id` |

---

### `products`

**Rows:** 9,977 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `product_id` | int(11) | NO | PRI | NULL | auto_increment |
| `vend_id` | varchar(45) | YES | MUL | NULL |  |
| `name` | mediumtext | NO | MUL | NULL |  |
| `previously_known_as` | mediumtext | YES |  | NULL |  |
| `short_description` | mediumtext | YES |  | NULL |  |
| `description` | mediumtext | YES |  | NULL |  |
| `package_contents` | mediumtext | YES |  | NULL |  |
| `specifications` | mediumtext | YES |  | NULL |  |
| `sku` | varchar(100) | NO |  | NULL |  |
| `url_key` | varchar(100) | NO | MUL | NULL |  |
| `is_container` | smallint(6) | NO |  | 0 |  |
| `retail_price` | decimal(13,5) | NO |  | 0.00000 |  |
| `stock_status` | smallint(6) | NO | MUL | 1 |  |
| `visibility` | smallint(6) | NO | MUL | 1 |  |
| `unlimited_qty` | smallint(6) | NO |  | 0 |  |
| `product_created` | timestamp | NO | MUL | current_timestamp() |  |
| `special_price` | decimal(13,5) | NO |  | 0.00000 |  |
| `product_type` | smallint(6) | NO | MUL | 4 |  |
| `qty_sold_in` | smallint(6) | NO |  | 1 |  |
| `view_count` | int(11) | NO |  | 0 |  |
| `manufacturer_id` | int(11) | YES |  | NULL |  |
| `new_and_never_updated` | int(11) | NO |  | 1 |  |
| `bargain_bin_item` | int(11) | NO |  | 0 |  |
| `daily_deal_product` | int(11) | NO |  | 0 |  |
| `loyalty_reward` | decimal(13,5) | NO |  | 0.00000 |  |
| `coming_soon` | int(11) | YES |  | 0 |  |
| `keywords` | mediumtext | YES |  | NULL |  |
| `cost_price` | decimal(13,5) | NO |  | 0.00000 |  |
| `wholesale_markup` | decimal(13,5) | NO |  | 20.00000 |  |
| `is_retail_product` | int(11) | NO |  | 1 |  |
| `is_wholesale_product` | int(11) | NO |  | 1 |  |
| `wholesale_price` | decimal(13,5) | NO |  | 0.00000 |  |
| `discontinued` | smallint(1) | NO |  | 0 |  |
| `layout_id` | smallint(6) | NO |  | 1 |  |
| `available_for_sale_retail` | int(11) | NO |  | 1 |  |
| `available_for_sale_wholesale` | int(11) | NO |  | 1 |  |
| `pg_vg_ratio` | int(11) | YES |  | NULL |  |
| `bottle_size` | int(11) | YES |  | NULL |  |
| `nicotine_type` | int(11) | YES |  | NULL |  |
| `wholesale_fixed_cost_or_percentage` | smallint(6) | NO |  | 0 |  |
| `show_only_if_logged_in` | smallint(1) | NO |  | 0 |  |
| `embedding` | longtext | YES |  | NULL |  |
| `comp_short_desc` | text | YES |  | NULL |  |
| `comp_description` | text | YES |  | NULL |  |
| `comp_specs` | text | YES |  | NULL |  |
| `comp_package_contents` | text | YES |  | NULL |  |
| `meta_desc` | varchar(255) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | product_id |
| `vendID` | BTREE |  | vend_id |
| `productURL` | BTREE |  | url_key |
| `StockStatus` | BTREE |  | stock_status |
| `VisibilityIndex` | BTREE |  | visibility |
| `ProductTypeIndex` | BTREE |  | product_type |
| `idx_products_created` | BTREE |  | product_created |
| `SearchIndex` | FULLTEXT |  | name |
| `SearchIndex2` | FULLTEXT |  | name, short_description |

---

### `products_bought_together`

**Rows:** 1,394,651 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `product_id` | int(11) | NO | PRI | NULL |  |
| `product_id_bought` | int(11) | NO | PRI | NULL |  |
| `order_products_id` | int(11) | NO | PRI | NULL |  |
| `order_id` | int(11) | YES |  | NULL |  |
| `date_bought` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `UniqueIndexP` | BTREE | ✓ | product_id, product_id_bought, order_products_id |
| `IndexProducts` | BTREE |  | product_id |
| `IndexProductsTwo` | BTREE |  | product_id_bought |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `product_brought` | `product_id` | `products`.`product_id` |

---

### `products_viewed`

**Rows:** 1,504,487 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `product_id` | int(11) | NO | MUL | NULL |  |
| `time_visited` | timestamp | NO | MUL | current_timestamp() |  |
| `user` | varchar(45) | YES |  | NULL |  |
| `guest` | smallint(6) | YES |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `Views` | BTREE |  | product_id, user, guest, id |
| `idx_time_visited` | BTREE |  | time_visited |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `product_viewied` | `product_id` | `products`.`product_id` |

---

### `refunds`

**Rows:** 2,308 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `order_id` | int(11) | NO | MUL | NULL |  |
| `date_created` | timestamp | NO |  | current_timestamp() |  |
| `amount` | decimal(10,4) | NO |  | 0.0000 |  |
| `description` | mediumtext | YES |  | NULL |  |
| `bank_account` | varchar(45) | NO |  | NULL |  |
| `status` | int(11) | NO |  | 0 |  |
| `completed_date` | timestamp | YES |  | NULL |  |
| `refund_created_by_user` | int(11) | NO |  | NULL |  |
| `refund_additional_data` | mediumtext | YES |  | NULL |  |
| `original_refund_amount` | decimal(10,4) | NO |  | NULL |  |
| `ai_refund_object` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `orders_refunds_idx` | BTREE |  | order_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `refunds_orders_key` | `order_id` | `orders`.`order_id` |

---

### `review_reponses`

**Rows:** 76 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `in_reply_to` | int(11) | NO | MUL | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `user_id` | varchar(45) | NO |  | NULL |  |
| `response_text` | varchar(3000) | NO |  | NULL |  |
| `ip_address` | varchar(45) | NO |  | NULL |  |
| `active` | int(11) | NO |  | 0 |  |
| `deleted_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `product_reviews_idx` | BTREE |  | in_reply_to |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `product_responses` | `in_reply_to` | `reviews`.`id` |

---

### `reviews`

**Rows:** 1,961 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `product_id` | int(11) | NO | MUL | NULL |  |
| `user_id` | int(11) | YES |  | NULL |  |
| `name` | varchar(45) | NO |  | NULL |  |
| `rating` | int(11) | NO |  | NULL |  |
| `review_text` | mediumtext | YES |  | NULL |  |
| `session_id` | varchar(45) | YES |  | NULL |  |
| `summary` | mediumtext | YES |  | NULL |  |
| `status` | int(11) | YES |  | 0 |  |
| `date_created` | timestamp | NO |  | current_timestamp() |  |
| `anonymous` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `productID_idx` | BTREE |  | product_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `product_reviews` | `product_id` | `products`.`product_id` |

---

### `reviews_helpful`

**Rows:** 1,355 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `review_id` | int(11) | YES | MUL | NULL |  |
| `response_id` | int(11) | YES | MUL | NULL |  |
| `helpful` | int(11) | NO | MUL | NULL |  |
| `user_id` | int(11) | YES | MUL | NULL |  |
| `session_id` | varchar(45) | YES | MUL | NULL |  |
| `ip_address` | varchar(45) | NO |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `reviewIndex` | BTREE |  | review_id |
| `reviewUserIndex` | BTREE |  | user_id |
| `reviewSessionIndex` | BTREE |  | session_id |
| `reviewHelpfulIndex` | BTREE |  | helpful |
| `reviews_helpful_responses_idx` | BTREE |  | response_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `reviews_helpful_responses` | `response_id` | `review_reponses`.`id` |

---

### `saved_cart_products`

**Rows:** 123,064 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `product_id` | int(11) | NO | MUL | NULL |  |
| `qty` | varchar(45) | YES |  | NULL |  |
| `has_options` | smallint(6) | NO |  | 0 |  |
| `option_data` | mediumtext | YES |  | NULL |  |
| `saved_cart_id` | bigint(20) | NO | MUL | NULL |  |
| `custom_price` | decimal(13,5) | YES |  | NULL |  |
| `custom_price_expires` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `CartProducts_idx` | BTREE |  | saved_cart_id, product_id |
| `saved_cart_products_idx` | BTREE |  | product_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `saved_carts` | `saved_cart_id` | `saved_carts`.`id` |
| `saved_products` | `product_id` | `products`.`product_id` |

---

### `saved_carts`

**Rows:** 79,039 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) | NO | PRI | NULL | auto_increment |
| `session_id` | mediumtext | YES |  | NULL |  |
| `user_id` | int(11) | YES | MUL | NULL |  |
| `session_created` | timestamp | NO |  | current_timestamp() |  |
| `is_retail` | int(11) | NO |  | 1 |  |
| `is_wholesale` | int(11) | NO |  | 0 |  |
| `last_updated` | timestamp | YES |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `Customer Cart_idx` | BTREE |  | user_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `customers_saved_carts` | `user_id` | `customers`.`id` |

---

### `searches`

**Rows:** 260,261 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `user_id` | int(11) | YES |  | NULL |  |
| `session_id` | varchar(45) | YES |  | NULL |  |
| `search_term` | varchar(45) | NO | MUL | NULL |  |
| `result_count` | smallint(6) | NO |  | NULL |  |
| `timestamp` | timestamp | NO | MUL | current_timestamp() |  |
| `product_id_clicked_on` | int(11) | YES | MUL | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `productIDIndex` | BTREE |  | product_id_clicked_on |
| `searchTermIndex` | BTREE |  | search_term |
| `searchTimeIndex` | BTREE |  | timestamp |
| `searchIndex` | FULLTEXT |  | search_term |

---

### `sendgrid_webhook_data`

**Rows:** 2,638,773 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `json_object_string` | mediumtext | NO |  | NULL |  |
| `email` | varchar(200) | YES | MUL | NULL |  |
| `timestamp` | timestamp | YES |  | NULL |  |
| `event` | varchar(200) | YES |  | NULL |  |
| `sg_event_id` | varchar(200) | YES | UNI | NULL |  |
| `sg_message_id` | varchar(200) | YES | MUL | NULL |  |
| `response` | mediumtext | YES |  | NULL |  |
| `smtp_id` | varchar(200) | YES |  | NULL |  |
| `attempt` | varchar(200) | YES |  | NULL |  |
| `useragent` | varchar(200) | YES |  | NULL |  |
| `ip` | varchar(200) | YES |  | NULL |  |
| `url` | varchar(200) | YES |  | NULL |  |
| `status` | varchar(200) | YES |  | NULL |  |
| `category` | mediumtext | YES |  | NULL |  |
| `reason` | mediumtext | YES |  | NULL |  |
| `asm_group_id` | varchar(200) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `SendGridEventID` | BTREE | ✓ | sg_event_id |
| `SendGridEmail` | BTREE |  | email |
| `SendGridMessageID` | BTREE |  | sg_message_id |

---

### `sessions`

**Rows:** 1,287,818 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `session_id` | varchar(50) | NO | PRI | NULL |  |
| `session_expires` | datetime | YES | MUL | NULL |  |
| `session_created` | datetime | NO | MUL | current_timestamp() |  |
| `session_data` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | session_id |
| `idx_session_expires` | BTREE |  | session_expires |
| `idx_session_created` | BTREE |  | session_created |

---

### `shipping_methods`

**Rows:** 8 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `label` | varchar(45) | NO |  | NULL |  |
| `checkout_icon` | varchar(45) | YES |  | NULL |  |
| `description` | mediumtext | NO |  | NULL |  |
| `price` | decimal(10,4) | NO |  | 0.0000 |  |
| `is_pickup` | varchar(45) | NO |  | 0 |  |
| `active` | smallint(6) | NO |  | 1 |  |
| `identifier` | varchar(45) | NO | UNI | NULL |  |
| `sort` | smallint(6) | NO |  | NULL |  |
| `default` | smallint(6) | NO |  | 0 |  |
| `is_retail_method` | int(11) | NO |  | 1 |  |
| `is_wholesale_method` | int(11) | NO |  | 0 |  |
| `eligible_for_free_shipping` | int(11) | NO |  | 0 |  |
| `require_r18_verification` | int(11) | NO |  | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `identifier_UNIQUE` | BTREE | ✓ | identifier |

---

### `short_url_redirects`

**Rows:** 15 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `user_id` | int(11) | YES |  | NULL |  |
| `order_id` | int(11) | YES |  | NULL |  |
| `redirect_string` | varchar(45) | NO | UNI | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `last_visited_at` | timestamp | YES |  | NULL |  |
| `visited_by_ip` | varchar(45) | YES |  | NULL |  |
| `url_to_redirect_to` | mediumtext | YES |  | NULL |  |
| `comment` | varchar(200) | YES |  | NULL |  |
| `system_generated` | int(11) | NO |  | 1 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `redirectStringID` | BTREE | ✓ | redirect_string |

---

### `suburbs`

**Rows:** 202 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `suburb_id` | int(11) | NO | PRI | NULL | auto_increment |
| `suburb_name` | varchar(100) | NO |  | NULL |  |
| `city_town_id` | int(11) | YES | MUL | NULL |  |
| `city_name` | varchar(45) | NO | MUL | NULL |  |
| `url_key` | varchar(150) | NO | UNI | NULL |  |
| `active` | tinyint(4) | YES | MUL | 1 |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `updated_at` | timestamp | NO |  | current_timestamp() | on update current_timestamp() |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | suburb_id |
| `url_key` | BTREE | ✓ | url_key |
| `idx_city_town_id` | BTREE |  | city_town_id |
| `idx_city_name` | BTREE |  | city_name |
| `idx_url_key` | BTREE |  | url_key |
| `idx_active` | BTREE |  | active |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `suburbs_ibfk_1` | `city_town_id` | `cities_towns`.`id` |

---

### `till_payment_errors`

**Rows:** 2,394 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `data` | mediumtext | NO |  | NULL |  |
| `order_id` | int(11) | NO | MUL | NULL |  |
| `type` | varchar(45) | YES |  | NULL |  |
| `cardHolder` | varchar(45) | YES |  | NULL |  |
| `expiryMonth` | varchar(45) | YES |  | NULL |  |
| `expiryYear` | varchar(45) | YES |  | NULL |  |
| `lastFourDigits` | varchar(45) | YES |  | NULL |  |
| `firstSixDigits` | varchar(45) | YES |  | NULL |  |
| `binBrand` | varchar(45) | YES |  | NULL |  |
| `binBank` | varchar(100) | YES |  | NULL |  |
| `binType` | varchar(45) | YES |  | NULL |  |
| `binCountry` | varchar(45) | YES |  | NULL |  |
| `errorMessage` | varchar(255) | YES |  | NULL |  |
| `adapterMessage` | varchar(255) | YES |  | NULL |  |
| `customer_id` | int(11) | YES | MUL | NULL |  |
| `order_data` | mediumtext | YES |  | NULL |  |
| `created_at` | timestamp | NO | MUL | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `customerID` | BTREE |  | customer_id |
| `idx_tpe_created` | BTREE |  | created_at |
| `idx_tpe_order_id_created` | BTREE |  | order_id, created_at |

---

### `till_payment_reciept`

**Rows:** 54,583 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `success` | varchar(45) | NO |  | NULL |  |
| `uuid` | varchar(45) | NO | UNI | NULL |  |
| `purchase_id` | varchar(45) | NO |  | NULL |  |
| `return_type` | mediumtext | NO |  | NULL |  |
| `extra_data` | mediumtext | NO |  | NULL |  |
| `order_id` | int(11) | NO | MUL | NULL |  |
| `success_status_json` | mediumtext | YES |  | NULL |  |
| `created` | timestamp | NO |  | current_timestamp() |  |
| `refund_status_json` | mediumtext | YES |  | NULL |  |
| `amount` | varchar(45) | YES |  | NULL |  |
| `credit_card_number` | varchar(45) | YES |  | NULL |  |
| `expire` | varchar(45) | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uniq_tpr_uuid` | BTREE | ✓ | uuid |
| `idx_tpr_order_id_created` | BTREE |  | order_id, created |

---

### `wanted_products`

**Rows:** 3,884 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `user_id` | int(11) | YES |  | NULL |  |
| `session_id` | varchar(45) | YES |  | NULL |  |
| `product_id` | int(11) | YES |  | NULL |  |
| `created` | timestamp | YES |  | current_timestamp() |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `windcave_payments`

**Rows:** 1,492 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `TxnType` | varchar(45) | NO |  | NULL |  |
| `MerchantReference` | int(11) | NO | UNI | NULL |  |
| `ClientInfo` | varchar(45) | NO |  | NULL |  |
| `TxnId` | varchar(45) | NO |  | NULL |  |
| `EmailAddress` | varchar(45) | NO |  | NULL |  |
| `DpsTxnRef` | varchar(45) | NO |  | NULL |  |
| `AmountSettlement` | varchar(45) | NO |  | NULL |  |
| `ResponseText` | varchar(45) | NO |  | NULL |  |
| `CurrencyInput` | varchar(45) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `MerchantReference_UNIQUE` | BTREE | ✓ | MerchantReference |

---

### `wp_api_keys`

**Rows:** 1 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `label` | varchar(100) | NO |  | NULL |  |
| `key_hash` | char(64) | NO | UNI | NULL |  |
| `active` | tinyint(1) | NO |  | 1 |  |
| `allowed_ips` | longtext | YES |  | NULL |  |
| `created_at` | timestamp | NO |  | current_timestamp() |  |
| `last_used_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `uk_key_hash` | BTREE | ✓ | key_hash |

---

### `zip_payments`

**Rows:** 32,991 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(11) | NO | PRI | NULL | auto_increment |
| `order_id` | int(11) | NO |  | NULL |  |
| `token` | varchar(45) | NO |  | NULL |  |
| `expiryDateTime` | varchar(45) | NO |  | NULL |  |
| `zipOrderId` | varchar(45) | NO |  | NULL |  |
| `customer_pairing_token_active` | int(11) | YES |  | NULL |  |
| `payment_status` | varchar(45) | YES |  | NULL |  |
| `payment_amount` | varchar(45) | YES |  | NULL |  |
| `time_created` | timestamp | NO |  | current_timestamp() |  |
| `zip_refund_json` | mediumtext | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

