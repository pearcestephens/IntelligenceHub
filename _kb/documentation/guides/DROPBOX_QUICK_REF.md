# 🎯 Dropbox System - Quick Reference

**Status:** Phase 1 Complete ✅  
**Next:** Phase 2 (Cron Scanner Updates)

---

## ✅ What's Done (Phase 1)

### BusinessUnitManager - FULLY UPDATED
**File:** `frontend-tools/scripts/business-unit-manager.js`

✅ **Path Methods:**
- `getAuditPath()` → Returns `todo/` path (inbox)
- `getDonePath()` → Returns `done/` path (archive) [NEW METHOD]
- `getScreenshotPath()` → Returns `todo/screenshots/`
- `getVideoPath()` → Returns `todo/videos/`

✅ **Directory Creation:**
- Creates 6 paths: todo/, done/, and subfolders

✅ **README Generation:**
- `ensureReadmes()` creates 2 README files:
  - `todo/README.md` - Explains inbox pattern (~250 lines)
  - `done/README.md` - Explains archive pattern (~300 lines)

✅ **File Saving:**
- `saveAuditReport()` saves to todo/ folder
- Adds dropbox metadata: `dropbox_status: 'pending'`, `dropbox_location: 'todo'`
- Calls `ensureReadmes()` automatically

✅ **Filename Format:**
- `{unit_id}_audit-{details}.json` format already implemented
- Example: `hdgwrzntwa_audit-1730050800.json`

---

## ⏳ What's Next (Phase 2)

### Cron Scanner - NEEDS UPDATES
**File:** `frontend-tools/cron-audit-scanner.js`

🔴 **Required Changes:**

1. **Scan Path** - Line ~100-150
   ```javascript
   // CHANGE FROM:
   const auditPath = path.join(basePath, unit.id, 'audits');
   
   // CHANGE TO:
   const todoPath = path.join(basePath, unit.id, 'todo');
   ```

2. **Add File Moving** - NEW FUNCTION
   ```javascript
   async function moveToArchive(auditFile, businessUnit) {
     // Move all 4 files: JSON, HTML, meta, PNG
     // From: todo/
     // To: done/
     // Atomically (all or none)
   }
   ```

3. **Filename Parsing** - Line ~200-250
   ```javascript
   // Extract unit_id from filename
   const match = filename.match(/^([^_]+)_audit-/);
   const unitId = match[1];
   ```

4. **Index Paths** - Line ~300-400
   ```javascript
   // master-index.json should reference done/ paths
   path: '/reports/{unit}/done/{filename}.json'  // not audits/
   ```

---

## ⏳ What's Next (Phase 3)

### Dashboard - NEEDS UPDATES
**File:** `audit_reports.php`

🔴 **Required Changes:**

1. **BusinessUnitScanner::discoverBusinessUnits()** - Line ~50-150
   ```php
   // CHANGE FROM:
   $auditPath = $reportsPath . '/' . $dir . '/audits';
   
   // CHANGE TO:
   $donePath = $reportsPath . '/' . $dir . '/done';
   ```

2. **Verify Delete Operations** - Line ~800-900
   ```php
   // Ensure deletes target done/ folder
   // Should use $unit['audit_path'] (dynamic)
   ```

---

## 📋 Quick Task Checklist

### Immediate Tasks
- [ ] Update cron-audit-scanner.js scan path (~5 min)
- [ ] Implement moveToArchive() function (~15 min)
- [ ] Update filename parsing (~5 min)
- [ ] Update master-index.json paths (~5 min)
- [ ] Update dashboard BusinessUnitScanner (~10 min)
- [ ] Add .gitignore entries (~2 min)
- [ ] Test basic workflow (~10 min)

**Total Time:** ~52 minutes

### Future Tasks
- [ ] Create README maintenance cron (~15 min)
- [ ] Full end-to-end testing (~20 min)
- [ ] Dashboard optimization (~30 min)

---

## 🧪 Quick Test

```bash
# 1. Run an audit
cd /home/master/applications/hdgwrzntwa/public_html/frontend-tools
node scripts/quick-page-audit.js --url="https://staff.vapeshed.co.nz" --mode=quick

# 2. Check files created in todo/
ls -la reports/hdgwrzntwa/todo/
# Should see: hdgwrzntwa_audit-*.json, .html, .meta.json
# Should see: todo/README.md

# 3. Check files NOT in audits/ (old folder)
ls -la reports/hdgwrzntwa/audits/ 2>/dev/null || echo "Folder doesn't exist (correct!)"

# 4. Check done/ folder exists but empty
ls -la reports/hdgwrzntwa/done/
# Should see: done/README.md only (no audits yet)

# 5. Run scanner (will fail without Phase 2 updates)
node cron-audit-scanner.js
# Expected: Errors about missing files in audits/ folder
```

---

## 🚨 Known Issues

### After Phase 1 (Current State)
✅ **BusinessUnitManager works perfectly**
- Audits save to todo/ folder
- READMEs generated automatically
- Filenames have unit_id prefix

❌ **Cron scanner won't work**
- Still looking for audits/ folder
- Won't find files in todo/
- Won't move files to done/

❌ **Dashboard won't work**
- Still looking for audits/ folder
- Won't find files in done/
- Timeline will be empty

### After Phase 2 (Scanner Updated)
✅ **Scanner will work**
- Scans todo/ folder
- Moves files to done/
- Updates master-index.json

❌ **Dashboard still won't work**
- Still looking for audits/ folder
- Needs Phase 3 updates

### After Phase 3 (Dashboard Updated)
✅ **Everything works!**
- Full dropbox workflow operational
- Dashboard displays audits from done/
- End-to-end testing can begin

---

## 📞 Support

**Full Documentation:** `DROPBOX_SYSTEM_STATUS.md`  
**Test Guide:** `END_TO_END_TESTING_GUIDE.md`  
**System Overview:** `AUDIT_SYSTEM_COMPLETE.md`

---

**Last Updated:** October 27, 2025 14:45 UTC
