# Quick Start Guide

**Date:** October 30, 2025
**Audience:** Developers starting work on Intelligence Hub
**Time to Read:** 10 minutes

---

## 🚀 Get Started in 5 Minutes

### Step 1: Access the Database (2 min)

```bash
# Test connection
mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa -e "SELECT COUNT(*) FROM intelligence_content;"

# Expected output: 22386 (or similar)
```

**Connection details:**
- Host: `localhost`
- Database: `hdgwrzntwa`
- Username: `hdgwrzntwa`
- Password: `bFUdRjh4Jx`

---

### Step 2: Explore Existing Structure (2 min)

```bash
# Navigate to project
cd /home/master/applications/hdgwrzntwa/public_html/

# Check what exists
ls -la

# Check documentation
ls -la docs/
ls -la _kb/
```

**Key directories:**
- `/docs/` - This documentation (you're reading it!)
- `/_kb/` - Knowledge base files
- `/ai-agent/` - AI automation scripts
- `/private_html/` - Private files, configs

---

### Step 3: Read Core Documentation (1 min)

**Must-read (in order):**
1. `docs/planning/01_project_requirements.md` - What we're building
2. `docs/database/01_current_tables.md` - What we have (78 tables!)
3. `docs/planning/02_timeline_estimates.md` - 12-week plan

---

## 📊 What You Have

### Massive Existing Infrastructure ✅
- **78 database tables** (operational)
- **22,386 files** indexed
- **14,545 files** with full content (263 MB)
- **6 active bots**
- **6 cron jobs** running
- **4 satellites** configured

### What We're Building 🚧
- **7 new tables** (context + restructure)
- **Context Generator** (comprehensive READMEs + .copilot/)
- **Hub Restructure** (safe organization)
- **Standards Library** (user preferences enforced)
- **One-button Dashboard**

---

## 🎯 Your First Task

### Phase 1: Foundation (Weeks 1-2)

**Goal:** Create 7 database tables and run discovery scan

**Steps:**
1. Review SQL schema: `docs/database/02_new_tables_design.md`
2. Create tables:
```bash
mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa < sql/create_new_tables.sql
```
3. Verify:
```bash
mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa -e "SHOW TABLES LIKE 'code_%';"
mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa -e "SHOW TABLES LIKE 'hub_%';"
```
4. Populate standards:
```bash
mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa < sql/insert_standards.sql
```

**Expected result:** 7 new tables with initial data

---

## 🔧 Development Workflow

### Daily Workflow
```bash
# 1. Pull latest (if using git)
git pull origin main

# 2. Check database connection
mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa -e "SELECT 1;"

# 3. Run your work

# 4. Test thoroughly

# 5. Update documentation if needed
```

### Testing Workflow
```bash
# Test database queries
php test-db-connection.php

# Test discovery scanner
php scripts/discovery-scanner.php --dry-run

# Test pattern scanner
php scripts/pattern-scanner.php --dry-run

# Verify no breakage
php scripts/verify-system-health.php
```

---

## 📋 Phase Overview

### Phase 1: Foundation (Weeks 1-2) ← **START HERE**
- Create 7 database tables
- Build discovery scanner
- Build dependency mapper
- Build lost knowledge finder
- Generate initial reports

### Phase 2: Deep Analysis (Weeks 3-4)
- Build pattern scanner
- Build security analyzer
- Build performance profiler
- Implement change detection

### Phase 3: Standards Library (Week 5) ⭐
- Populate standards from user preferences
- Build validation system
- Build enforcement rules

### Phase 4: Context Generation (Weeks 6-7)
- Build README generator
- Build .copilot/ generator
- Integrate standards
- Test on real projects

### Phase 5: Hub Restructure (Weeks 8-9)
- Design _organized/ structure
- Build migration system
- Test safe migration
- Execute restructure

### Phase 6: Dashboard (Week 10)
- Build one-button UI
- Connect all systems
- Real-time progress
- Action buttons

### Phase 7: Integration (Weeks 11-12)
- Connect MCP tools
- Update cron jobs
- Sync satellites
- Complete testing
- Deploy to production

---

## 🎓 Key Concepts

### Standards Library (User Emphasized)
```php
// From code_standards table
$standards = [
    'database.driver' => 'PDO',           // Always PDO
    'database.statements' => 'prepared',  // Always prepared
    'framework.frontend' => 'Bootstrap 4.2',
    'styling.standard' => 'PSR-12',
    'security.csrf' => 'always',          // Every form
];

// Used everywhere
- README generation (enforced)
- .copilot/ generation (enforced)
- Code validation (enforced)
- Pattern discovery (compare against)
```

### Context Generation (User's #1 Priority)
```markdown
NOT THIS:
# My Project
This is a project.

THIS:
# CIS - Central Information System
**Purpose:** Internal ERP...
**Stack:** PHP 8.1, MySQL, Bootstrap 4.2...
**Database:** jcepnzzkmj (password: wprKh9Jq63)

## Architecture
[Detailed architecture]

## Entry Points
[All entry points listed]

## Standards
[User preferences enforced]

## Common Tasks
[Real workflows from actual code]
```

### Hub Restructure (Safe Migration)
```
Current (messy):
public_html/
├── old-script-1.php
├── test-file.php
├── backup_2023.php
├── real-api.php
└── [hundreds more...]

After restructure:
public_html/
├── _organized/
│   ├── production/    # Live systems
│   ├── automation/    # Cron jobs
│   ├── library/       # Shared code
│   ├── development/   # WIP
│   └── archive/       # Old code (dated)
└── [legacy links maintained]
```

---

## ⚠️ Important Rules

### Must Do ✅
1. ✅ **Test before deploying** (always)
2. ✅ **Backup before migrating** (always)
3. ✅ **Use prepared statements** (always)
4. ✅ **Add CSRF to forms** (always)
5. ✅ **Follow PSR-12** (always)
6. ✅ **Update docs** (when you change things)

### Must Not Do ❌
1. ❌ **Don't break existing systems** (6 cron jobs must keep running)
2. ❌ **Don't use mysqli** (always PDO)
3. ❌ **Don't skip validation** (validate all input)
4. ❌ **Don't hardcode passwords** (use .env or config)
5. ❌ **Don't create files > 500 lines** (split them)
6. ❌ **Don't ignore user standards** (enforce them)

---

## 🔍 Common Commands

### Database
```bash
# Connect
mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa

# Check tables
mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa -e "SHOW TABLES;"

# Count records
mysql -u hdgwrzntwa -p'bFUdRjh4Jx' hdgwrzntwa -e "
SELECT
    TABLE_NAME,
    TABLE_ROWS
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'hdgwrzntwa'
ORDER BY TABLE_ROWS DESC
LIMIT 10;"
```

### File System
```bash
# Find PHP files
find . -name "*.php" -type f | wc -l

# Find large files
find . -name "*.php" -type f -exec wc -l {} + | sort -rn | head -20

# Search code
grep -r "PDO" --include="*.php" .
```

### Testing
```bash
# PHP syntax check
find . -name "*.php" -exec php -l {} \;

# Run specific test
php test-script.php

# Check for errors
tail -100 logs/error.log
```

---

## 📞 Need Help?

### Documentation
- Project requirements: `docs/planning/01_project_requirements.md`
- Database schema: `docs/database/02_new_tables_design.md`
- Timeline: `docs/planning/02_timeline_estimates.md`
- Systems: `docs/systems/*.md`

### Common Issues
- **Can't connect to database:** Check password is `bFUdRjh4Jx`
- **Tables don't exist:** Run `sql/create_new_tables.sql`
- **Permission denied:** Check file permissions
- **Port in use:** Kill existing process

---

## ✅ Next Steps

1. ✅ Read this guide (you're here!)
2. ✅ Read project requirements
3. ✅ Read database documentation
4. ✅ Connect to database successfully
5. ✅ Create 7 new tables
6. ✅ Populate standards
7. ✅ Start Phase 1 (discovery scanner)

---

**Last Updated:** October 30, 2025
**Version:** 1.0.0
**Status:** ✅ Ready for developers
