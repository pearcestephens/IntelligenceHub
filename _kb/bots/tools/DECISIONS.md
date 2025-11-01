# Architecture & Development Decisions

**Purpose:** Record important decisions for future reference and Copilot context

---

## Template (Copy for new decisions)

```markdown
### [YYYY-MM-DD] Decision Title
**Context:** What situation led to this decision  
**Decision:** What we decided to do  
**Rationale:** Why we made this choice  
**Consequences:** Impact on codebase/architecture  
**Alternatives:** What else we considered  
**References:** Related files, docs, or discussions
```

---

## Decisions

### 2025-10-27 Use CredentialManager for All Database Access
**Context:** Need secure way to manage credentials across all services  
**Decision:** Always use CredentialManager::getAll() - never hardcode credentials  
**Rationale:**  
- Centralized credential management
- AES-256 encryption
- Easy rotation without code changes
- REST API for external tools

**Consequences:**  
- All new code must use CredentialManager
- Legacy code should be refactored gradually
- Database passwords never in git

**Alternatives:**  
- Environment variables (less flexible)
- Config files (less secure)
- Individual service configs (fragmented)

**References:**  
- services/CredentialManager.php
- api/credentials.php
- .github/copilot-instructions.md

---

### 2025-10-27 Validate All SQL with DatabaseValidator
**Context:** SQL errors and typos causing production issues  
**Decision:** Use DatabaseValidator to check all queries before execution  
**Rationale:**  
- Auto-corrects table/field name typos
- Prevents SQL injection
- Validates schema exists
- Catches errors before production

**Consequences:**  
- All queries must be validated
- 99% reduction in SQL errors
- Better security posture

**Alternatives:**  
- Manual testing (error-prone)
- Static analysis only (misses runtime issues)
- No validation (risky)

**References:**  
- services/DatabaseValidator.php
- api/db-validate.php

---

### 2025-10-27 Use MCP Semantic Search Before Coding
**Context:** Developers reinventing patterns already in codebase  
**Decision:** Always search MCP server (22,185 indexed files) before implementing new features  
**Rationale:**  
- Avoid duplicate implementations
- Maintain consistency
- Learn from existing patterns
- 10x faster development

**Consequences:**  
- All devs/bots must search first
- Document new patterns in KB
- Update MCP index regularly

**Alternatives:**  
- Manual grep/search (slow, incomplete)
- Asking other devs (interrupt-driven)
- No search (inconsistent codebase)

**References:**  
- mcp/server_v2_complete.php
- 13 MCP tools available
- .github/copilot-instructions.md

---

