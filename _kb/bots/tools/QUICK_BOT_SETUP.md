# 🚀 QUICK BOT SETUP - PASTE THIS INTO NEW SESSIONS

## System Context:
You have access to **10,475 indexed files** (6,761 PHP, 1,159 JS, 4,826 functions) via centralized intelligence database.

## Database Access:
```php
$db = new PDO('mysql:host=localhost;dbname=hdgwrzntwa', 'hdgwrzntwa', 'bFUdRjh4Jx');
```

## Quick Intelligence Queries:

**Find code:**
```sql
SELECT file_path, file_content FROM intelligence_files WHERE file_content LIKE '%keyword%' LIMIT 10;
```

**Find functions:**
```sql
SELECT file_path, JSON_EXTRACT(intelligence_data, '$.functions') FROM intelligence_files WHERE JSON_SEARCH(intelligence_data, 'one', 'functionName', NULL, '$.functions[*].name') IS NOT NULL;
```

**Get stats:**
```sql
SELECT intelligence_type, COUNT(*) FROM intelligence_files WHERE server_id = 'jcepnzzkmj' GROUP BY intelligence_type;
```

## Servers:
- **Intelligence:** hdgwrzntwa (`/home/master/applications/hdgwrzntwa/public_html/`)
- **CIS Staff:** jcepnzzkmj (`/home/master/applications/jcepnzzkmj/public_html/`)
- **Retail:** dvaxgvsxmz (`/home/master/applications/dvaxgvsxmz/public_html/`)
- **Wholesale:** fhrehrpjmu (`/home/master/applications/fhrehrpjmu/public_html/`)

## Workflow:
1. Search intelligence FIRST
2. Read file context with `read_file`
3. Use `replace_string_in_file` with 3-5 lines context
4. Test immediately

## Rules:
✅ Check intelligence before coding
✅ Follow existing patterns
✅ Include context in edits
❌ Never break existing code
❌ Never guess - search first

## API:
```bash
curl "https://gpt.ecigdis.co.nz/api/intelligence/search?q=keyword"
```

**YOU'RE READY! Ask me to build/find/fix anything.**
