# Wiki Database (wiki.vapeshed.co.nz)

**Database:** `bjyvpezxum`  
**Application:** bjyvpezxum (Wiki System)  
**Scanned:** 2025-10-25 13:04:51  

## Summary

- **Tables:** 23
- **Total Columns:** 178
- **Total Indexes:** 90
- **Foreign Keys:** 6

---

## Tables

### `activities`

**Rows:** 3,415 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `type` | varchar(191) | NO | MUL | NULL |  |
| `detail` | text | NO |  | NULL |  |
| `user_id` | int(11) | NO | MUL | NULL |  |
| `ip` | varchar(45) | NO | MUL | NULL |  |
| `entity_id` | int(11) | YES | MUL | NULL |  |
| `entity_type` | varchar(191) | YES |  | NULL |  |
| `created_at` | timestamp | YES | MUL | NULL |  |
| `updated_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `activities_user_id_index` | BTREE |  | user_id |
| `activities_entity_id_index` | BTREE |  | entity_id |
| `activities_key_index` | BTREE |  | type |
| `activities_created_at_index` | BTREE |  | created_at |
| `activities_ip_index` | BTREE |  | ip |

---

### `attachments`

**Rows:** 2 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `name` | varchar(191) | NO |  | NULL |  |
| `path` | varchar(191) | NO |  | NULL |  |
| `extension` | varchar(20) | NO |  | NULL |  |
| `uploaded_to` | int(11) | NO | MUL | NULL |  |
| `external` | tinyint(1) | NO |  | NULL |  |
| `order` | int(11) | NO |  | NULL |  |
| `created_by` | int(11) | NO |  | NULL |  |
| `updated_by` | int(11) | NO |  | NULL |  |
| `created_at` | timestamp | YES |  | NULL |  |
| `updated_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `attachments_uploaded_to_index` | BTREE |  | uploaded_to |

---

### `books`

**Rows:** 43 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `name` | varchar(191) | NO |  | NULL |  |
| `slug` | varchar(191) | NO | MUL | NULL |  |
| `description` | text | NO |  | NULL |  |
| `created_at` | timestamp | YES |  | NULL |  |
| `updated_at` | timestamp | YES |  | NULL |  |
| `created_by` | int(11) | NO | MUL | NULL |  |
| `updated_by` | int(11) | NO | MUL | NULL |  |
| `image_id` | int(11) | YES |  | NULL |  |
| `deleted_at` | timestamp | YES |  | NULL |  |
| `owned_by` | int(10) unsigned | NO | MUL | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `books_slug_index` | BTREE |  | slug |
| `books_created_by_index` | BTREE |  | created_by |
| `books_updated_by_index` | BTREE |  | updated_by |
| `books_owned_by_index` | BTREE |  | owned_by |

---

### `bookshelves`

**Rows:** 6 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `name` | varchar(180) | NO |  | NULL |  |
| `slug` | varchar(180) | NO | MUL | NULL |  |
| `description` | text | NO |  | NULL |  |
| `created_by` | int(11) | YES | MUL | NULL |  |
| `updated_by` | int(11) | YES | MUL | NULL |  |
| `image_id` | int(11) | YES |  | NULL |  |
| `created_at` | timestamp | YES |  | NULL |  |
| `updated_at` | timestamp | YES |  | NULL |  |
| `deleted_at` | timestamp | YES |  | NULL |  |
| `owned_by` | int(10) unsigned | NO | MUL | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `bookshelves_slug_index` | BTREE |  | slug |
| `bookshelves_created_by_index` | BTREE |  | created_by |
| `bookshelves_updated_by_index` | BTREE |  | updated_by |
| `bookshelves_owned_by_index` | BTREE |  | owned_by |

---

### `bookshelves_books`

**Rows:** 42 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `bookshelf_id` | int(10) unsigned | NO | PRI | NULL |  |
| `book_id` | int(10) unsigned | NO | PRI | NULL |  |
| `order` | int(10) unsigned | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | bookshelf_id, book_id |
| `bookshelves_books_book_id_foreign` | BTREE |  | book_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `bookshelves_books_book_id_foreign` | `book_id` | `books`.`id` |
| `bookshelves_books_bookshelf_id_foreign` | `bookshelf_id` | `bookshelves`.`id` |

---

### `chapters`

**Rows:** 6 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `book_id` | int(11) | NO | MUL | NULL |  |
| `slug` | varchar(191) | NO | MUL | NULL |  |
| `name` | text | NO |  | NULL |  |
| `description` | text | NO |  | NULL |  |
| `priority` | int(11) | NO | MUL | NULL |  |
| `created_at` | timestamp | YES |  | NULL |  |
| `updated_at` | timestamp | YES |  | NULL |  |
| `created_by` | int(11) | NO | MUL | NULL |  |
| `updated_by` | int(11) | NO | MUL | NULL |  |
| `deleted_at` | timestamp | YES |  | NULL |  |
| `owned_by` | int(10) unsigned | NO | MUL | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `chapters_slug_index` | BTREE |  | slug |
| `chapters_book_id_index` | BTREE |  | book_id |
| `chapters_priority_index` | BTREE |  | priority |
| `chapters_created_by_index` | BTREE |  | created_by |
| `chapters_updated_by_index` | BTREE |  | updated_by |
| `chapters_owned_by_index` | BTREE |  | owned_by |

---

### `deletions`

**Rows:** 19 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `deleted_by` | int(11) | NO | MUL | NULL |  |
| `deletable_type` | varchar(100) | NO | MUL | NULL |  |
| `deletable_id` | int(11) | NO | MUL | NULL |  |
| `created_at` | timestamp | YES |  | NULL |  |
| `updated_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `deletions_deleted_by_index` | BTREE |  | deleted_by |
| `deletions_deletable_type_index` | BTREE |  | deletable_type |
| `deletions_deletable_id_index` | BTREE |  | deletable_id |

---

### `email_confirmations`

**Rows:** 12 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `user_id` | int(11) | NO | MUL | NULL |  |
| `token` | varchar(191) | NO | MUL | NULL |  |
| `created_at` | timestamp | YES |  | NULL |  |
| `updated_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `email_confirmations_user_id_index` | BTREE |  | user_id |
| `email_confirmations_token_index` | BTREE |  | token |

---

### `images`

**Rows:** 205 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `name` | varchar(191) | NO |  | NULL |  |
| `url` | varchar(191) | NO |  | NULL |  |
| `created_at` | timestamp | YES |  | NULL |  |
| `updated_at` | timestamp | YES |  | NULL |  |
| `created_by` | int(11) | NO |  | NULL |  |
| `updated_by` | int(11) | NO |  | NULL |  |
| `path` | varchar(400) | NO |  | NULL |  |
| `type` | varchar(191) | NO | MUL | NULL |  |
| `uploaded_to` | int(11) | NO | MUL | 0 |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `images_type_index` | BTREE |  | type |
| `images_uploaded_to_index` | BTREE |  | uploaded_to |

---

### `joint_permissions`

**Rows:** 924 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `role_id` | int(11) | NO | PRI | NULL |  |
| `entity_type` | varchar(191) | NO | PRI | NULL |  |
| `entity_id` | int(11) | NO | PRI | NULL |  |
| `status` | tinyint(3) unsigned | NO | MUL | NULL |  |
| `owner_id` | int(10) unsigned | YES | MUL | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | role_id, entity_type, entity_id |
| `joint_permissions_entity_id_entity_type_index` | BTREE |  | entity_id, entity_type |
| `joint_permissions_role_id_index` | BTREE |  | role_id |
| `joint_permissions_status_index` | BTREE |  | status |
| `joint_permissions_owner_id_index` | BTREE |  | owner_id |

---

### `migrations`

**Rows:** 72 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `migration` | varchar(191) | NO |  | NULL |  |
| `batch` | int(11) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |

---

### `page_revisions`

**Rows:** 607 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `page_id` | int(11) | NO | MUL | NULL |  |
| `name` | varchar(191) | NO |  | NULL |  |
| `html` | longtext | NO |  | NULL |  |
| `text` | longtext | NO |  | NULL |  |
| `created_by` | int(11) | NO |  | NULL |  |
| `created_at` | timestamp | YES |  | NULL |  |
| `updated_at` | timestamp | YES |  | NULL |  |
| `slug` | varchar(191) | NO | MUL | NULL |  |
| `book_slug` | varchar(191) | NO | MUL | NULL |  |
| `type` | varchar(191) | NO | MUL | version |  |
| `markdown` | longtext | NO |  | '' |  |
| `summary` | varchar(191) | YES |  | NULL |  |
| `revision_number` | int(11) | NO | MUL | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `page_revisions_page_id_index` | BTREE |  | page_id |
| `page_revisions_slug_index` | BTREE |  | slug |
| `page_revisions_book_slug_index` | BTREE |  | book_slug |
| `page_revisions_type_index` | BTREE |  | type |
| `page_revisions_revision_number_index` | BTREE |  | revision_number |

---

### `pages`

**Rows:** 132 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `book_id` | int(11) | NO | MUL | NULL |  |
| `chapter_id` | int(11) | NO | MUL | NULL |  |
| `name` | varchar(191) | NO |  | NULL |  |
| `slug` | varchar(191) | NO | MUL | NULL |  |
| `html` | longtext | NO |  | NULL |  |
| `text` | longtext | NO |  | NULL |  |
| `priority` | int(11) | NO | MUL | NULL |  |
| `created_at` | timestamp | YES |  | NULL |  |
| `updated_at` | timestamp | YES |  | NULL |  |
| `created_by` | int(11) | NO | MUL | NULL |  |
| `updated_by` | int(11) | NO | MUL | NULL |  |
| `draft` | tinyint(1) | NO | MUL | 0 |  |
| `markdown` | longtext | NO |  | '' |  |
| `revision_count` | int(11) | NO |  | NULL |  |
| `template` | tinyint(1) | NO | MUL | 0 |  |
| `deleted_at` | timestamp | YES |  | NULL |  |
| `owned_by` | int(10) unsigned | NO | MUL | NULL |  |
| `editor` | varchar(50) | NO |  |  |  |
| `embedding_vector` | mediumtext | YES |  | NULL |  |
| `embedding_updated_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `pages_slug_index` | BTREE |  | slug |
| `pages_book_id_index` | BTREE |  | book_id |
| `pages_chapter_id_index` | BTREE |  | chapter_id |
| `pages_priority_index` | BTREE |  | priority |
| `pages_created_by_index` | BTREE |  | created_by |
| `pages_updated_by_index` | BTREE |  | updated_by |
| `pages_draft_index` | BTREE |  | draft |
| `pages_template_index` | BTREE |  | template |
| `pages_owned_by_index` | BTREE |  | owned_by |

---

### `permission_role`

**Rows:** 111 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `permission_id` | int(10) unsigned | NO | PRI | NULL |  |
| `role_id` | int(10) unsigned | NO | PRI | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | permission_id, role_id |
| `permission_role_role_id_foreign` | BTREE |  | role_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `permission_role_permission_id_foreign` | `permission_id` | `role_permissions`.`id` |
| `permission_role_role_id_foreign` | `role_id` | `roles`.`id` |

---

### `references`

**Rows:** 3 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | bigint(20) unsigned | NO | PRI | NULL | auto_increment |
| `from_id` | int(10) unsigned | NO | MUL | NULL |  |
| `from_type` | varchar(25) | NO | MUL | NULL |  |
| `to_id` | int(10) unsigned | NO | MUL | NULL |  |
| `to_type` | varchar(25) | NO | MUL | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `references_from_id_index` | BTREE |  | from_id |
| `references_from_type_index` | BTREE |  | from_type |
| `references_to_id_index` | BTREE |  | to_id |
| `references_to_type_index` | BTREE |  | to_type |

---

### `role_permissions`

**Rows:** 59 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `name` | varchar(191) | NO | UNI | NULL |  |
| `display_name` | varchar(191) | YES |  | NULL |  |
| `description` | varchar(191) | YES |  | NULL |  |
| `created_at` | timestamp | YES |  | NULL |  |
| `updated_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `permissions_name_unique` | BTREE | ✓ | name |

---

### `role_user`

**Rows:** 46 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `user_id` | int(10) unsigned | NO | PRI | NULL |  |
| `role_id` | int(10) unsigned | NO | PRI | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | user_id, role_id |
| `role_user_role_id_foreign` | BTREE |  | role_id |

#### Foreign Keys

| Constraint | Column | References |
|------------|--------|------------|
| `role_user_role_id_foreign` | `role_id` | `roles`.`id` |
| `role_user_user_id_foreign` | `user_id` | `users`.`id` |

---

### `roles`

**Rows:** 4 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `display_name` | varchar(191) | YES |  | NULL |  |
| `description` | varchar(191) | YES |  | NULL |  |
| `created_at` | timestamp | YES |  | NULL |  |
| `updated_at` | timestamp | YES |  | NULL |  |
| `system_name` | varchar(191) | NO | MUL | NULL |  |
| `external_auth_id` | varchar(180) | NO | MUL |  |  |
| `mfa_enforced` | tinyint(1) | NO |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `roles_system_name_index` | BTREE |  | system_name |
| `roles_external_auth_id_index` | BTREE |  | external_auth_id |

---

### `search_terms`

**Rows:** 17,216 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `term` | varchar(180) | NO | MUL | NULL |  |
| `entity_type` | varchar(100) | NO | MUL | NULL |  |
| `entity_id` | int(11) | NO |  | NULL |  |
| `score` | int(11) | NO | MUL | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `search_terms_term_index` | BTREE |  | term |
| `search_terms_entity_type_index` | BTREE |  | entity_type |
| `search_terms_entity_type_entity_id_index` | BTREE |  | entity_type, entity_id |
| `search_terms_score_index` | BTREE |  | score |

---

### `settings`

**Rows:** 142 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `setting_key` | varchar(191) | NO | PRI | NULL |  |
| `value` | text | NO |  | NULL |  |
| `created_at` | timestamp | YES |  | NULL |  |
| `updated_at` | timestamp | YES |  | NULL |  |
| `type` | varchar(50) | NO |  | string |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | setting_key |

---

### `user_invites`

**Rows:** 10 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `user_id` | int(11) | NO | MUL | NULL |  |
| `token` | varchar(191) | NO | MUL | NULL |  |
| `created_at` | timestamp | YES |  | NULL |  |
| `updated_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `user_invites_user_id_index` | BTREE |  | user_id |
| `user_invites_token_index` | BTREE |  | token |

---

### `users`

**Rows:** 43 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `name` | varchar(191) | NO |  | NULL |  |
| `email` | varchar(191) | NO | UNI | NULL |  |
| `password` | varchar(60) | NO |  | NULL |  |
| `remember_token` | varchar(100) | YES |  | NULL |  |
| `created_at` | timestamp | YES |  | NULL |  |
| `updated_at` | timestamp | YES |  | NULL |  |
| `email_confirmed` | tinyint(1) | NO |  | 1 |  |
| `image_id` | int(11) | NO |  | 0 |  |
| `external_auth_id` | varchar(191) | NO | MUL | NULL |  |
| `system_name` | varchar(191) | YES | MUL | NULL |  |
| `slug` | varchar(180) | NO | UNI | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `users_email_unique` | BTREE | ✓ | email |
| `users_slug_unique` | BTREE | ✓ | slug |
| `users_external_auth_id_index` | BTREE |  | external_auth_id |
| `users_system_name_index` | BTREE |  | system_name |

---

### `views`

**Rows:** 5,125 | **Engine:** InnoDB | **Collation:** utf8mb4_unicode_ci

#### Columns

| Column | Type | Null | Key | Default | Extra |
|--------|------|------|-----|---------|-------|
| `id` | int(10) unsigned | NO | PRI | NULL | auto_increment |
| `user_id` | int(11) | NO | MUL | NULL |  |
| `viewable_id` | int(11) | NO | MUL | NULL |  |
| `viewable_type` | varchar(191) | NO |  | NULL |  |
| `views` | int(11) | NO |  | NULL |  |
| `created_at` | timestamp | YES |  | NULL |  |
| `updated_at` | timestamp | YES |  | NULL |  |

#### Indexes

| Index | Type | Unique | Columns |
|-------|------|--------|----------|
| `PRIMARY` | BTREE | ✓ | id |
| `views_user_id_index` | BTREE |  | user_id |
| `views_viewable_id_index` | BTREE |  | viewable_id |

---

