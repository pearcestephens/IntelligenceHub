# 🚀 Quick Start - Continue Dropbox Implementation

**For the next developer/AI picking up this work**

---

## ⚡ TL;DR

✅ **Phase 1 COMPLETE** - BusinessUnitManager fully refactored for dropbox workflow  
⏳ **Phase 2 NEXT** - Update cron-audit-scanner.js (~30 min)

**Current Status:** Audits save to `todo/` folder but scanner still looks for old `audits/` folder. Update scanner to process `todo/` and move files to `done/`.

---

## 📂 What You Need to Know

### The Dropbox System
```
reports/{unit_id}/
├── todo/              ← Inbox (audits saved here)
│   ├── README.md
│   └── {unit_id}_audit-TS.json + .html + .meta.json + .png
│
└── done/              ← Archive (scanner moves files here)
    ├── README.md
    └── (files moved from todo/)
```

**Workflow:** Audit saves to todo/ → Scanner processes → Moves to done/ → Dashboard displays

### What's Working ✅
- BusinessUnitManager saves to todo/ with dropbox metadata
- Both README files auto-generated
- Filenames have unit_id prefix
- All 4 files saved (JSON, HTML, meta, PNG)

### What's NOT Working ❌
- Scanner looks for old `audits/` folder (won't find files)
- Files stuck in todo/ (no move logic yet)
- Dashboard looks for old `audits/` folder (won't display)

---

## 🎯 Your Mission: Phase 2 (~30 min)

### File to Edit
`/home/master/applications/hdgwrzntwa/public_html/frontend-tools/cron-audit-scanner.js`

### Changes Needed

#### 1. Update Scan Path (~5 min)
```javascript
// FIND (around line 100-150):
const auditPath = path.join(basePath, unit.id, 'audits');

// REPLACE WITH:
const todoPath = path.join(basePath, unit.id, 'todo');
const donePath = path.join(basePath, unit.id, 'done');
```

#### 2. Add moveToArchive() Function (~15 min)

**Add this new function (suggested location: after scanAuditsForBusinessUnit):**

```javascript
/**
 * Move audit files from todo/ to done/ folder
 * Moves all 4 files atomically: JSON, HTML, meta.json, PNG
 * 
 * @param {string} auditFile - Base filename (e.g., "hdgwrzntwa_audit-1730050800.json")
 * @param {object} businessUnit - Business unit object with id property
 */
async function moveToArchive(auditFile, businessUnit) {
  const todoPath = path.join(basePath, businessUnit.id, 'todo');
  const donePath = path.join(basePath, businessUnit.id, 'done');
  
  const baseFilename = auditFile.replace('.json', '');
  
  // Define all 4 files to move
  const files = [
    {
      from: path.join(todoPath, `${baseFilename}.json`),
      to: path.join(donePath, `${baseFilename}.json`)
    },
    {
      from: path.join(todoPath, `${baseFilename}.html`),
      to: path.join(donePath, `${baseFilename}.html`)
    },
    {
      from: path.join(todoPath, `${baseFilename}.meta.json`),
      to: path.join(donePath, `${baseFilename}.meta.json`)
    },
    {
      from: path.join(todoPath, 'screenshots', `${baseFilename}.png`),
      to: path.join(donePath, 'screenshots', `${baseFilename}.png`)
    }
  ];
  
  try {
    // Step 1: Verify ALL files exist before moving
    for (const file of files) {
      if (!fs.existsSync(file.from)) {
        throw new Error(`Missing file: ${file.from}`);
      }
    }
    
    // Step 2: Move all files
    for (const file of files) {
      await fs.promises.rename(file.from, file.to);
      console.log(`✅ Moved: ${path.basename(file.from)}`);
    }
    
    // Step 3: Update JSON metadata
    const doneJsonPath = files[0].to;
    const data = JSON.parse(await fs.promises.readFile(doneJsonPath, 'utf8'));
    data.dropbox_status = 'processed';
    data.dropbox_location = 'done';
    data.processed_at = new Date().toISOString();
    await fs.promises.writeFile(doneJsonPath, JSON.stringify(data, null, 2));
    
    console.log(`✅ Audit archived: ${baseFilename}`);
    return true;
  } catch (error) {
    console.error(`❌ Failed to move ${baseFilename}:`, error.message);
    
    // Rollback: Try to move files back to todo/
    console.log('⏮️ Attempting rollback...');
    for (const file of files) {
      if (fs.existsSync(file.to)) {
        try {
          await fs.promises.rename(file.to, file.from);
          console.log(`✅ Rolled back: ${path.basename(file.from)}`);
        } catch (rollbackError) {
          console.error(`❌ Rollback failed for ${path.basename(file.from)}`);
        }
      }
    }
    
    return false;
  }
}
```

#### 3. Update scanAuditsForBusinessUnit() (~5 min)

**Find the section that processes JSON files. Add the move call:**

```javascript
// After processing each audit file, add:
const moveSuccess = await moveToArchive(auditFile, businessUnit);
if (!moveSuccess) {
  console.warn(`⚠️ File not moved, will retry next scan: ${auditFile}`);
}
```

#### 4. Update Index Generation (~5 min)

**Find where master-index.json paths are set:**

```javascript
// CHANGE:
path: `/reports/${unit.id}/audits/${filename}`

// TO:
path: `/reports/${unit.id}/done/${filename}`
```

---

## 🧪 Testing Phase 2

### Test 1: Run Audit
```bash
cd /home/master/applications/hdgwrzntwa/public_html/frontend-tools
node scripts/quick-page-audit.js --url="https://staff.vapeshed.co.nz" --mode=quick
```

**Check:** File created in `reports/hdgwrzntwa/todo/`

### Test 2: Run Scanner
```bash
node cron-audit-scanner.js
```

**Check:**
- [ ] Console shows "✅ Moved: ..." messages
- [ ] File moved from `todo/` to `done/`
- [ ] `todo/` folder empty (except README.md)
- [ ] JSON in `done/` has `dropbox_status: "processed"`

### Test 3: Check Index
```bash
cat reports/master-index.json | jq '.businessUnits["hdgwrzntwa"]'
```

**Check:** Paths reference `done/` not `audits/`

### Test 4: Error Recovery
```bash
# Delete one file (simulate error)
rm reports/hdgwrzntwa/todo/{unit_id}_audit-TS.html
node cron-audit-scanner.js
```

**Check:**
- [ ] Scanner logs error
- [ ] Files NOT moved (stays in todo/)
- [ ] No partial moves

---

## 📚 Documentation Reference

### For Implementation
- **DROPBOX_SYSTEM_STATUS.md** - Complete guide with all code examples
- **DROPBOX_VISUAL_GUIDE.md** - Data flow diagrams
- **PHASE1_COMPLETE.md** - What's already done

### Quick Commands
```bash
# View documentation
cat frontend-tools/DROPBOX_SYSTEM_STATUS.md
cat frontend-tools/DROPBOX_QUICK_REF.md

# Check current files
ls -la reports/hdgwrzntwa/todo/
ls -la reports/hdgwrzntwa/done/

# View scanner logs
tail -f logs/scanner.log

# Test full workflow
node scripts/quick-page-audit.js --url="https://staff.vapeshed.co.nz" --mode=quick && \
sleep 2 && \
node cron-audit-scanner.js && \
ls -la reports/hdgwrzntwa/done/
```

---

## ⚠️ Important Notes

### DO
- ✅ Test with real audit first
- ✅ Check all 4 files exist before moving
- ✅ Update JSON metadata after move
- ✅ Implement rollback on error
- ✅ Log all operations

### DON'T
- ❌ Move files individually (must be atomic)
- ❌ Skip error handling
- ❌ Forget to update master-index.json paths
- ❌ Test in production first

### Common Pitfalls
1. **Partial moves:** If error occurs mid-move, files split between folders
   - **Solution:** Verify all files exist first, rollback on error
   
2. **Missing screenshots:** PNG file might not exist for all audits
   - **Solution:** Check file exists before trying to move

3. **Hardcoded paths:** Using 'audits' string instead of variable
   - **Solution:** Use `todoPath` and `donePath` variables consistently

---

## 🎯 Success Criteria

Phase 2 is complete when:
- [ ] Scanner scans `todo/` folder (not `audits/`)
- [ ] Files move from `todo/` to `done/` atomically
- [ ] All 4 files move together (JSON, HTML, meta, PNG)
- [ ] JSON updated with `processed_at` timestamp
- [ ] master-index.json references `done/` paths
- [ ] Error handling works (rollback on failure)
- [ ] No errors in scanner logs
- [ ] All tests pass

---

## ⏭️ After Phase 2

### Phase 3: Update Dashboard (~20 min)
**File:** `audit_reports.php`
**Task:** Change discovery to look for `done/` folders

### Phase 4: Additional Tasks (~20 min)
- Add .gitignore entries
- Create README maintenance cron

### Phase 5: Testing (~20 min)
- End-to-end workflow test
- Error recovery scenarios
- Performance validation

**Total Time Remaining:** ~90 minutes

---

## 🆘 If You Get Stuck

1. **Check DROPBOX_SYSTEM_STATUS.md** - Has complete code examples for Phase 2
2. **Review DROPBOX_VISUAL_GUIDE.md** - See data flow diagrams
3. **Read scanner logs** - `tail -f logs/scanner.log`
4. **Verify file locations** - `ls -la reports/hdgwrzntwa/todo/`

---

## 📞 Quick Facts

- **Central Storage:** `/home/master/applications/hdgwrzntwa/public_html/reports/`
- **Scanner Location:** `frontend-tools/cron-audit-scanner.js`
- **BusinessUnitManager:** `frontend-tools/scripts/business-unit-manager.js` ✅ Complete
- **Dashboard:** `audit_reports.php` ⏳ Needs update (Phase 3)
- **Cron Schedule:** Every 5 minutes (0,5,10,15,20,25,30,35,40,45,50,55 * * * *)

---

## 🎉 You Got This!

Phase 1 laid the perfect foundation. Phase 2 is straightforward:
1. Change path from `audits` to `todo`
2. Add move function (copy from DROPBOX_SYSTEM_STATUS.md)
3. Call move after processing
4. Update index paths

**Estimated Time:** 30 minutes  
**Difficulty:** Medium (clear requirements, code examples provided)

Good luck! 🚀

---

**Last Updated:** October 27, 2025  
**Next Phase:** Phase 2 - Cron Scanner Updates  
**Status:** Ready to Begin
