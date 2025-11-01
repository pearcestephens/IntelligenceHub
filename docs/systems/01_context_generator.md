# Context Generator System

**Date:** October 30, 2025
**Priority:** ⭐ User's #1 Focus
**Status:** Design complete, ready to build

---

## 🎯 Purpose

> **"I WANT THE APPLICATION TIED UP AND THAT COMPLETE CONTEXT GENERATION APPLICATION BUILT"**
> — User, October 30, 2025

Generate comprehensive, accurate context for AI assistants working on any project in the intelligence hub.

---

## 📋 Core Features

### 1. Comprehensive README Generation
**What it does:**
- Analyzes entire project structure
- Identifies all entry points
- Maps all features and modules
- Documents API endpoints
- Lists dependencies
- Generates 200-500 line READMEs

**Not like this:**
```markdown
# My Project
This is a project.
```

**Like this:**
```markdown
# CIS - Central Information System

**Purpose:** Internal ERP for inventory, transfers, POs, HR
**Stack:** PHP 8.1, MySQL, Bootstrap 4.2, jQuery 3.6
**Database:** jcepnzzkmj (password: wprKh9Jq63)

## Quick Start
```bash
cd /path/to/cis
php -S localhost:8000
# Visit: http://localhost:8000
```

## Architecture
- Modular MVC (17 modules)
- Shared kernel (_shared/lib/Kernel.php)
- PDO database layer (always prepared statements)

## Modules
1. **base** - Core framework, auth, routing
2. **consignments** - Vend consignment workflows
3. **transfers** - Stock transfer system
[... detailed descriptions]

## Entry Points
- `/modules/consignments/pack.php` - Pack consignments
- `/modules/transfers/send.php` - Send transfers
[... all entry points listed]

## Standards
- **Database:** PDO prepared statements (always)
- **Frontend:** Bootstrap 4.2, jQuery 3.6
- **Style:** PSR-12, 4 spaces, strict types
- **Security:** CSRF on all forms

## Common Tasks
...
```

---

### 2. .copilot/ Directory Generation
**What it does:**
- Discovers actual patterns from codebase
- Extracts real code examples
- Documents actual workflows
- Lists actual dependencies
- **NOT generic templates**

**Structure:**
```
.copilot/
├── PATTERNS.md           - Real patterns found
├── STANDARDS.md          - Enforced standards
├── WORKFLOWS.md          - Common workflows
├── DEPENDENCIES.md       - Actual dependencies
├── SECURITY.md           - Security patterns
├── EXAMPLES/             - Real code examples
│   ├── database.php      - Actual DB patterns
│   ├── form-handler.php  - Real form patterns
│   ├── api-endpoint.php  - Real API patterns
│   └── validation.php    - Real validation
└── MODULES/              - Per-module summaries
    ├── base.md
    ├── consignments.md
    └── [...]
```

---

### 3. Standards Library Integration
**What it does:**
- Reads from `code_standards` table
- Enforces user preferences in generated content
- Validates against standards
- Reports compliance %

**Example Standards:**
```php
// From code_standards table
[
    'database.driver' => 'PDO',
    'database.statements' => 'prepared',
    'framework.frontend' => 'Bootstrap 4.2',
    'styling.standard' => 'PSR-12',
    'security.csrf' => 'always',
    'performance.query_threshold' => '300ms'
]
```

**In Generated Content:**
```markdown
## Database Standards
- ✅ **Always use PDO** (never mysqli)
- ✅ **Always use prepared statements**
- ✅ **Query threshold: 300ms**

## Example (From Your Codebase)
```php
// Good (found in modules/base/lib/Db.php)
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);

// Bad (never do this)
$query = "SELECT * FROM users WHERE id = $userId"; // ❌
```
```

---

### 4. Deep Code Scanning
**What it scans:**
- All PHP files for patterns
- All SQL queries for optimization
- All forms for CSRF protection
- All functions for security issues
- All files for PSR-12 compliance
- All dependencies for circular refs

**Output:**
```markdown
## Code Analysis Results

### Patterns Discovered
- Database connection: 3 variations (2 good, 1 needs update)
- Form handling: 7 patterns (5 with CSRF, 2 missing)
- API endpoints: 42 patterns (standard format)

### Security Findings
- ✅ 94% of forms have CSRF protection
- ⚠️ 6% missing CSRF (12 forms) - list provided
- ✅ 98% SQL uses prepared statements
- ⚠️ 2% needs update (3 files) - list provided

### Performance
- ✅ 89% queries < 300ms
- ⚠️ 11% slow queries (23 queries) - optimizations suggested

### Standards Compliance
- ✅ PSR-12: 87% compliant
- ✅ PHPDoc: 76% coverage
- ⚠️ File size: 12 files > 500 lines
```

---

### 5. Change Detection
**What it tracks:**
- Files created, modified, deleted
- Functions added, changed, removed
- Classes added, modified, deleted
- Dependencies changed
- Breaking changes detected

**Example:**
```markdown
## Recent Changes (Last 7 Days)

### Modified Files (23)
1. **modules/transfers/pack.php** (Modified 2 days ago)
   - Function `validatePackItems()` updated
   - Added validation for quantity > 0
   - ⚠️ Impacts: modules/transfers/send.php (calls this function)

2. **modules/base/lib/Db.php** (Modified 5 days ago)
   - Added connection pooling
   - ✅ No breaking changes

### Impact Analysis
- 23 files modified
- 7 functions changed (4 with dependencies)
- 12 files potentially affected
- 0 breaking changes detected
```

---

## 🔧 Implementation Details

### Phase 4: Context Generation (Weeks 6-7)

**Week 6: README Generator**
```php
// Context generator class
class ContextGenerator {
    public function generateREADME(string $projectPath): string {
        // 1. Scan project structure
        $structure = $this->scanStructure($projectPath);

        // 2. Discover entry points
        $entryPoints = $this->findEntryPoints($projectPath);

        // 3. Map features
        $features = $this->mapFeatures($projectPath);

        // 4. Extract dependencies
        $dependencies = $this->extractDependencies($projectPath);

        // 5. Get standards from DB
        $standards = $this->getStandards();

        // 6. Generate comprehensive README
        return $this->renderREADME([
            'structure' => $structure,
            'entry_points' => $entryPoints,
            'features' => $features,
            'dependencies' => $dependencies,
            'standards' => $standards
        ]);
    }
}
```

**Week 7: .copilot/ Generator**
```php
class CopilotGenerator {
    public function generateCopilotDir(string $projectPath): void {
        // 1. Discover real patterns
        $patterns = $this->discoverPatterns($projectPath);

        // 2. Extract examples
        $examples = $this->extractExamples($projectPath, $patterns);

        // 3. Document workflows
        $workflows = $this->documentWorkflows($projectPath);

        // 4. Create .copilot/ structure
        $this->createDirectory($projectPath . '/.copilot');
        $this->writePatterns($patterns);
        $this->writeExamples($examples);
        $this->writeWorkflows($workflows);
        $this->writeModuleSummaries($projectPath);
    }
}
```

---

## 📊 Success Metrics

### Quality Metrics
- ✅ README 200-500 lines (not 20 lines)
- ✅ 100% entry points documented
- ✅ 100% modules documented
- ✅ Real patterns (not generic)
- ✅ Real examples (from actual code)
- ✅ Standards enforced (from DB)

### Performance Metrics
- ✅ Generate README: < 30 seconds
- ✅ Generate .copilot/: < 60 seconds
- ✅ Full project scan: < 5 minutes
- ✅ Change detection: < 10 seconds

### Coverage Metrics
- ✅ All PHP files scanned
- ✅ All SQL queries analyzed
- ✅ All forms checked for CSRF
- ✅ All dependencies mapped
- ✅ All patterns discovered

---

## 🎯 User Expectations

### What User Wants
1. ✅ Comprehensive context (not generic)
2. ✅ Real patterns (from actual codebase)
3. ✅ Standards enforced (from DB)
4. ✅ One-button generation
5. ✅ Immediate results

### What User Doesn't Want
1. ❌ Generic templates
2. ❌ Manual work required
3. ❌ Outdated information
4. ❌ Breaking existing systems

---

## 🚀 One-Button Dashboard

**UI Mockup:**
```
┌─────────────────────────────────────────────┐
│  Context Generator                           │
├─────────────────────────────────────────────┤
│                                              │
│  [Generate Complete Context] ← One button    │
│                                              │
│  Progress: ▓▓▓▓▓▓░░░░ 60%                   │
│                                              │
│  ✅ Project structure scanned                │
│  ✅ Entry points discovered (23 found)       │
│  ✅ Patterns analyzed (47 patterns)          │
│  ⏳ Standards validation (in progress...)    │
│  ⏸️ README generation (pending)             │
│  ⏸️ .copilot/ generation (pending)          │
│                                              │
└─────────────────────────────────────────────┘
```

---

**Last Updated:** October 30, 2025
**Version:** 1.0.0
**Status:** ✅ Ready to build (Phase 4)
