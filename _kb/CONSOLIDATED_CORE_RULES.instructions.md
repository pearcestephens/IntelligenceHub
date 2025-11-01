---
applyTo: '**'
priority: 100
---

# Core Development Rules - Consolidated

## 🔒 Security (NON-NEGOTIABLE)

### Database
- ✅ **ALWAYS** use prepared statements (PDO or mysqli)
- ❌ **NEVER** concatenate SQL strings
- ✅ Validate and sanitize ALL inputs
- ✅ Escape ALL outputs (htmlspecialchars, JSON encode)

```php
// ✅ CORRECT
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);

// ❌ WRONG
$result = $pdo->query("SELECT * FROM users WHERE email = '$email'");
```

### Sessions & Auth
- ✅ Check authentication before ANY sensitive operation
- ✅ Use CSRF tokens on all forms
- ✅ Regenerate session IDs after login
- ✅ Never store passwords in plain text

### Error Handling
- ✅ Log errors to files (never echo to users)
- ✅ Use structured error envelopes for APIs
- ❌ Never expose stack traces in production

## 📝 Code Quality

### PHP Standards
- ✅ Use `declare(strict_types=1);` in every PHP file
- ✅ Follow PSR-12 coding style
- ✅ Add PHPDoc blocks to all functions/classes
- ✅ Type-hint parameters and return values
- ✅ Use meaningful variable names

### File Organization
- ✅ Keep files under 500 lines (break up if larger)
- ✅ One class per file
- ✅ Group related functions
- ❌ No test/demo files in production
- ✅ One backup per file in `/backups` or `private_html/backups/`

### Error Checking
- ✅ Check logs FIRST before making changes:
  - `/home/master/applications/jcepnzzkmj/logs/apache_*.error.log`
  - `/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/logs/`
- ✅ Use `php -l` to check syntax
- ✅ Test changes before committing

## 🔧 Development Workflow

### Before Writing Code
1. **Search MCP Knowledge Base** - Use semantic_search tool
2. **Check existing implementations** - Don't reinvent the wheel
3. **Read error logs** - Understand what's actually broken
4. **Verify database schema** - Check table structure first

### While Writing Code
1. Follow existing patterns in the codebase
2. Use prepared statements for ALL database queries
3. Add proper error handling
4. Include logging for debugging
5. Comment complex logic

### After Writing Code
1. Run `php -l` to check syntax
2. Test functionality manually
3. Check error logs for new issues
4. Update documentation if needed
5. Create ONE backup if modifying existing file

## 🗄️ Database Standards

### Connection Info
- **Main CIS:** jcepnzzkmj / wprKh9Jq63 @ 127.0.0.1
- **Always use:** UTF-8 (utf8mb4), InnoDB engine
- **Always add:** Indexes for foreign keys

### Query Best Practices
- ✅ Use prepared statements (PDO)
- ✅ Use transactions for multi-step operations
- ✅ Add LIMIT clauses to prevent huge result sets
- ✅ Use proper JOIN types (INNER, LEFT, etc.)
- ❌ Never use SELECT * in production

## 🚨 Red Flags (Stop and Ask)

- Deleting files without checking dependencies
- Changing database schema without migration
- Hard-coding credentials or secrets
- Breaking API contracts
- Deploying untested code
- Ignoring security warnings

## ⚡ Quick Reference

### Common Paths
- Main CIS: `/home/master/applications/jcepnzzkmj/public_html/`
- Dashboard: `/home/129337.cloudwaysapps.com/hdgwrzntwa/public_html/`
- Logs: `/home/master/applications/jcepnzzkmj/logs/apache_*.error.log`

### Common Commands
```bash
# Check PHP syntax
php -l filename.php

# Check error logs
tail -100 /home/master/applications/jcepnzzkmj/logs/apache_*.error.log

# Search code
grep -r "function_name" modules/

# Test database connection
mysql -u jcepnzzkmj -p'wprKh9Jq63' jcepnzzkmj -e "SELECT 1;"
```

### API Response Format
```php
// Success
echo json_encode([
    'success' => true,
    'data' => $result,
    'message' => 'Operation completed'
]);

// Error
echo json_encode([
    'success' => false,
    'error' => [
        'code' => 'ERROR_CODE',
        'message' => 'User-friendly message'
    ]
]);
```

## 🎯 Quality Checklist

Before considering any work complete:
- [ ] Used prepared statements for SQL
- [ ] Validated all inputs
- [ ] Escaped all outputs
- [ ] Added error handling
- [ ] Checked syntax with `php -l`
- [ ] Tested functionality
- [ ] Created backup if modifying file
- [ ] No test files left behind
- [ ] Logged important operations

---

**Remember:** Security first, quality second, speed third. Never compromise security for convenience!
