# Project Requirements - Intelligence Hub

**Date:** October 30, 2025
**Status:** Approved ✅

---

## 🎯 Primary Goal

> **"THE COMPANY BEING AI CENTRAL SO IT PROVIDES INSIGHTS AND INFORMATION"**
> — User, October 30, 2025

Make the Intelligence Hub the central source of truth for all code, patterns, standards, and insights across all company projects.

---

## 📋 User Requirements (From Conversations)

### Explicitly Stated Requirements

**1. Complete Context Generation**
- ✅ "I WANT THE APPLICATION TIED UP AND THAT COMPLETE CONTEXT GENERATION APPLICATION BUILT"
- Generate comprehensive READMEs automatically
- Create .copilot/ directories with actual patterns (not generic)
- Deep code scanning and analysis
- Change detection and impact analysis

**2. Standards Library (User Emphasized)**
- ✅ "STANDARDS LIBRARY, NO USER PREFERENCE SYSTEM"
- Store user preferences: PDO (always), Bootstrap 4.2, PSR-12
- Database-driven standards
- Enforce across all generated content

**3. Hub Restructure**
- ✅ "APPLICATION NEEDS A MASSIVE RESTRUCTURE"
- Safe organization of existing files
- Zero-breaking-change migration
- Clear folder structure (_organized/)

**4. Find Lost Knowledge**
- ✅ "ALOT OF LOST AND FORGOTEN KNOWLEDGE HERE. WE NEED TO MAKE SURE WE CAN FIND IT"
- Catalog orphaned files
- Track unused code
- Identify missing documentation

**5. Maintain Operations**
- ✅ "ENSURING THAT ALL CURRENT SOFTWARE, CRONS AND EVERYTHING ELSE RELATED IS STILL OPERATIONAL"
- Don't break existing systems
- Preserve cron jobs (6 operational)
- Keep satellites working (4 active)

**6. Database-Driven**
- ✅ "I PREFER DATABASE DRIVEN WHERE POSSIBLE"
- Store everything in database
- Use existing infrastructure (78 tables)
- Minimal file-based config

---

## 🚫 Explicitly Deprioritized

**Bot Conversations (Deferred)**
- ✅ "IM INTERESTED MOSTLY IN THE CONTEXT, THE CONVERSATION PART CAN COME LATER"
- ✅ "WE DONT EVEN HAVE ANY BOTS SETUP YET"
- Tables exist (ai_conversations, ai_conversation_messages)
- Feature removed from initial scope
- Can be added later (Phase 8+)

---

## ✅ Success Criteria

### Must Have (MVP)
1. ✅ 7 new database tables created and operational
2. ✅ Context generator produces comprehensive READMEs
3. ✅ .copilot/ directories generated with real patterns
4. ✅ Standards library configured with user preferences
5. ✅ Hub restructured safely (no broken links)
6. ✅ One-button dashboard working
7. ✅ All existing systems still operational

### Should Have (Production)
1. ✅ Change detection tracks impact
2. ✅ Deep code scanning finds patterns
3. ✅ Lost knowledge cataloged
4. ✅ Performance analysis working
5. ✅ Security scanning operational
6. ✅ MCP tools integrated

### Nice to Have (Enhancement)
1. ✅ AI-powered content generation
2. ✅ Advanced analytics
3. ✅ Satellite orchestration
4. ✅ Bot conversations (deferred to Phase 8+)

---

## 📊 Scope Definition

### In Scope ✅
- Context generation (213 features)
- Hub restructure (safe migration)
- Standards library (user preferences)
- 7 new database tables
- Deep code analysis
- Change detection
- One-button dashboard
- Documentation

### Out of Scope ❌
- Bot conversation UI (deferred)
- VS Code extension (can't extract context well)
- Real-time collaboration
- External API integrations (for now)
- Mobile apps

---

## 🎯 Priorities (User Emphasized)

### Priority 1: Context Generation
- Comprehensive README generation
- .copilot/ directory creation
- Standards library integration
- Real patterns, not generic content

### Priority 2: Standards Library
- User specifically mentioned this
- Database-driven preferences
- PDO always, Bootstrap 4.2, PSR-12
- Enforce in all generated content

### Priority 3: Hub Restructure
- Safe organization
- Zero breaking changes
- Clear folder structure
- Find lost knowledge

### Priority 4: Application Organization
- "TIE UP THE APPLICATION"
- Clean structure
- Easy navigation
- Professional appearance

---

## 💾 Technical Constraints

### Database
- Use existing: hdgwrzntwa
- 78 tables already exist
- Only 7 new tables needed
- Password: bFUdRjh4Jx

### Existing Infrastructure
- 22,386 files in intelligence_content
- 14,545 files in intelligence_files
- 6 active bots
- 6 cron jobs operational
- 4 satellites configured

### Performance
- Query threshold: 300ms
- File size limit: 500 lines recommended
- Response time: < 500ms for dashboards

### Standards
- PHP: PSR-12, strict types
- Database: PDO prepared statements always
- Frontend: Bootstrap 4.2, jQuery 3.6
- Security: CSRF always, input validation always

---

**Last Updated:** October 30, 2025
**Version:** 1.0.0
**Status:** ✅ Approved by user
