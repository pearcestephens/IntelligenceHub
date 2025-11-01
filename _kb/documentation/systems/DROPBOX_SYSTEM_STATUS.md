# 📦 Dropbox Workflow System - Implementation Status

**Date:** October 27, 2025  
**Status:** Phase 1 Complete ✅ | Phase 2 Pending  
**Component:** Enterprise Audit System  

---

## 🎯 Overview

Implementing a **dropbox-style workflow system** for audit report processing:

```
Audit Created → Save to todo/ (inbox) → Scanner processes → Move to done/ (archive) → Dashboard displays
```

This replaces the previous single `audits/` folder with a clear two-stage workflow.

---

## ✅ PHASE 1: BusinessUnitManager Updates (COMPLETE)

### Summary
All BusinessUnitManager changes complete. System now ready to save audits to dropbox inbox structure.

### Changes Made

#### 1. Path Methods Updated ✅
**File:** `frontend-tools/scripts/business-unit-manager.js` (lines 78-128)

**Before:**
```javascript
getAuditPath() { return '.../audits' }
getScreenshotPath() { return '.../audits/screenshots' }
getVideoPath() { return '.../audits/videos' }
```

**After:**
```javascript
getAuditPath() { return '.../todo' }          // Dropbox inbox
getDonePath() { return '.../done' }           // NEW METHOD - Archive
getScreenshotPath() { return '.../todo/screenshots' }
getVideoPath() { return '.../todo/videos' }
```

#### 2. Directory Creation Updated ✅
**Method:** `ensureDirectories()`

**Before:** Created 3 paths
```javascript
paths = [
  audits/,
  audits/screenshots/,
  audits/videos/
]
```

**After:** Creates 6 paths
```javascript
paths = [
  todo/,                    // Inbox
  done/,                    // Archive
  todo/screenshots/,        // Inbox screenshots
  todo/videos/,             // Inbox videos
  done/screenshots/,        // Archive screenshots
  done/videos/              // Archive videos
]
```

#### 3. README Generation Implemented ✅
**Method:** `ensureReadmes()` (renamed from singular `ensureReadme()`)

**Creates TWO README files:**

**A. todo/README.md** (~250 lines)
- Explains dropbox inbox pattern
- Critical warnings for AI/humans
- Workflow diagram
- File naming convention
- Status checking instructions
- Developer notes

**B. done/README.md** (~300 lines)
- Explains archive pattern
- Viewing options (dashboard/HTML/programmatic)
- Cleanup instructions
- Storage management tips
- Developer tools

**Auto-updates:** Regenerates if missing or > 7 days old

#### 4. saveAuditReport() Updated ✅
**Method:** `saveAuditReport(auditResult, businessUnit)`

**Changes:**
- ✅ Saves to `todo/` folder (was `audits/`)
- ✅ Calls `ensureReadmes()` (was `ensureReadme()`)
- ✅ Adds dropbox metadata to JSON:
  ```javascript
  {
    dropbox_status: 'pending',  // Will be 'processed' after move
    dropbox_location: 'todo'
  }
  ```
- ✅ Console logs mention "dropbox"
- ✅ Generates all 4 files: JSON, HTML, meta, PNG

**Note:** `generateFilename()` already adds unit_id prefix (no changes needed)

#### 5. Filename Format ✅
**Format:** `{unit_id}_audit_{hostname}_{path}_{mode}_{timestamp}.json`

**Example:**
```
hdgwrzntwa_audit_staff-vapeshed-co-nz_transfers-pack_comprehensive_2025-10-27T14-30-00.json
hdgwrzntwa_audit_staff-vapeshed-co-nz_transfers-pack_comprehensive_2025-10-27T14-30-00.html
hdgwrzntwa_audit_staff-vapeshed-co-nz_transfers-pack_comprehensive_2025-10-27T14-30-00.meta.json
screenshots/hdgwrzntwa_audit_staff-vapeshed-co-nz_transfers-pack_comprehensive_2025-10-27T14-30-00.png
```

---

## ⏳ PHASE 2: Cron Scanner Updates (PENDING)

### Required Changes

#### File: `cron-audit-scanner.js`
**Current Status:** Fully functional for OLD structure (audits/ folder)  
**Required:** Major refactoring for NEW dropbox structure

### Changes Needed

#### 1. Update Scan Path
**Current:** Scans `{basePath}/{unitId}/audits/`  
**Required:** Scan `{basePath}/{unitId}/todo/`

**Code Location:** ~Line 100-150 (scan logic)

#### 2. Implement File Moving Logic
**Required:** NEW atomic file move operation

**Pseudocode:**
```javascript
async function moveAuditToArchive(auditFile, businessUnit) {
  const todoPath = path.join(basePath, businessUnit.id, 'todo');
  const donePath = path.join(basePath, businessUnit.id, 'done');
  
  // Extract base filename (without extension)
  const baseFilename = auditFile.replace('.json', '');
  
  // Define all 4 files to move
  const filesToMove = [
    { from: `${todoPath}/${baseFilename}.json`, 
      to: `${donePath}/${baseFilename}.json` },
    { from: `${todoPath}/${baseFilename}.html`, 
      to: `${donePath}/${baseFilename}.html` },
    { from: `${todoPath}/${baseFilename}.meta.json`, 
      to: `${donePath}/${baseFilename}.meta.json` },
    { from: `${todoPath}/screenshots/${baseFilename}.png`, 
      to: `${donePath}/screenshots/${baseFilename}.png` }
  ];
  
  // Verify all 4 files exist before moving
  for (const file of filesToMove) {
    if (!fs.existsSync(file.from)) {
      throw new Error(`Missing file: ${file.from}`);
    }
  }
  
  // Move all 4 files atomically
  try {
    for (const file of filesToMove) {
      await fs.rename(file.from, file.to);
    }
    
    console.log(`✅ Moved audit to archive: ${baseFilename}`);
    
    // Update dropbox_status in JSON
    const donJson = path.join(donePath, `${baseFilename}.json`);
    const data = JSON.parse(fs.readFileSync(donJson, 'utf8'));
    data.dropbox_status = 'processed';
    data.dropbox_location = 'done';
    data.processed_at = new Date().toISOString();
    fs.writeFileSync(donJson, JSON.stringify(data, null, 2));
    
    return { success: true, moved_files: filesToMove.length };
    
  } catch (error) {
    // Rollback on failure
    console.error(`❌ Move failed, rolling back: ${error.message}`);
    // Attempt to move files back to todo/
    // ... rollback logic ...
    throw error;
  }
}
```

#### 3. Update Filename Parsing
**Current:** Expects `audit-{timestamp}.json`  
**Required:** Parse `{unit_id}_audit-{...}.json`

**Code:**
```javascript
// Extract unit_id from filename for validation
const match = filename.match(/^([^_]+)_audit-/);
if (match) {
  const extractedUnitId = match[1];
  // Verify matches expected business unit
  if (extractedUnitId !== businessUnit.id) {
    console.warn(`⚠️  Filename unit_id mismatch: ${extractedUnitId} !== ${businessUnit.id}`);
  }
}
```

#### 4. Update Index Generation
**Current:** master-index.json references `audits/` paths  
**Required:** Reference `done/` paths

**Changes:**
```javascript
// In master-index.json
{
  "businessUnits": {
    "hdgwrzntwa": {
      "audits": [
        {
          "filename": "hdgwrzntwa_audit-1730050800.json",
          "path": "/reports/hdgwrzntwa/done/hdgwrzntwa_audit-1730050800.json",  // ← done/ not audits/
          "dropbox_status": "processed",
          "processed_at": "2025-10-27T14:35:00Z",
          // ... other metadata
        }
      ]
    }
  }
}
```

#### 5. Add Move Timestamp
**Required:** Track when audit was moved from todo/ to done/

**Add to metadata:**
```javascript
{
  "move_timestamp": "2025-10-27T14:35:00Z",
  "processing_duration_ms": 1234  // Time between saved_at and move_timestamp
}
```

---

## ⏳ PHASE 3: Dashboard Updates (PENDING)

### Required Changes

#### File: `audit_reports.php`
**Current Status:** Fully functional for OLD structure (audits/ folder)  
**Required:** Update paths to read from done/ folder

### Changes Needed

#### 1. BusinessUnitScanner Class
**Current:** Discovers business units by looking for `audits/` folders  
**Required:** Look for `done/` folders

**Code Location:** ~Line 50-150

**Before:**
```php
$auditPath = $reportsPath . '/' . $dir . '/audits';
if (is_dir($auditPath)) {
  $businessUnits[] = [
    'id' => $dir,
    'audit_path' => $auditPath
  ];
}
```

**After:**
```php
$donePath = $reportsPath . '/' . $dir . '/done';  // ← Changed
if (is_dir($donePath)) {
  $businessUnits[] = [
    'id' => $dir,
    'audit_path' => $donePath  // ← Changed
  ];
}
```

#### 2. AuditRepository Class
**Current:** Reads from `{audit_path}/` (which was audits/)  
**Required:** No change needed (already uses audit_path property)

**Note:** This will automatically work once BusinessUnitScanner passes done/ path

#### 3. File Path Updates
**Required:** Update any hardcoded path references

**Search for:** `'/audits/'` in PHP code  
**Replace with:** `'/done/'`

#### 4. Delete Operations
**Current:** Deletes from audits/ folder  
**Required:** Delete from done/ folder

**Code Location:** ~Line 800-900 (delete functions)

**Verification needed:**
- Ensure delete operation uses `$unit['audit_path']` (dynamic)
- If hardcoded, update to use done/ folder

---

## 🔧 PHASE 4: Additional Tasks (PENDING)

### 1. Add .gitignore Entries ✅ Ready
**File:** `/.gitignore` (append to existing)

```gitignore
# Audit Reports - Exclude data, keep documentation
/reports/*/todo/*
/reports/*/done/*
!/reports/*/todo/README.md
!/reports/*/done/README.md
/reports/*.json
```

**Rationale:**
- Exclude audit data (can be large, regenerated)
- Keep README.md files (documentation, tracked in git)
- Exclude index files (regenerated by cron)

### 2. Create README Maintenance Cron Script
**File:** `cron-readme-maintenance.js` (NEW)

**Purpose:** Check/regenerate README.md files if missing or outdated

**Features:**
- Runs daily at 2 AM
- Scans all business unit folders
- Checks todo/README.md and done/README.md
- Regenerates if:
  - File missing
  - File > 7 days old
  - Template has been updated
- Logs to `/logs/readme-maintenance.log`

**Cron Entry:**
```cron
0 2 * * * cd /home/master/applications/hdgwrzntwa/public_html/frontend-tools && node cron-readme-maintenance.js >> /home/master/applications/hdgwrzntwa/logs/readme-maintenance.log 2>&1
```

---

## 🧪 PHASE 5: End-to-End Testing (PENDING)

### Test Scenario 1: Basic Workflow
1. Run audit: `node scripts/quick-page-audit.js --url="https://staff.vapeshed.co.nz" --mode=quick`
2. **Verify:** Files created in `todo/` folder with unit_id prefix
3. Run scanner: `node cron-audit-scanner.js`
4. **Verify:** Files moved to `done/` folder
5. Open dashboard: https://gpt.ecigdis.co.nz/audit_reports.php
6. **Verify:** Audit appears in timeline

### Test Scenario 2: README Files
1. Check `reports/hdgwrzntwa/todo/README.md` exists
2. Check `reports/hdgwrzntwa/done/README.md` exists
3. **Verify:** Content mentions dropbox workflow
4. Delete one README
5. Run audit again
6. **Verify:** README regenerated automatically

### Test Scenario 3: File Naming
1. Run audit
2. Check filenames in todo/: `hdgwrzntwa_audit-*.json`
3. **Verify:** All 4 files have unit_id prefix
4. Run scanner
5. Check filenames in done/: Same format
6. **Verify:** Filenames preserved during move

### Test Scenario 4: Error Recovery
1. Run audit
2. Manually delete JSON file from todo/ (leave other 3)
3. Run scanner
4. **Verify:** Scanner detects incomplete set, logs error
5. **Verify:** Remaining files NOT moved (atomic operation)

### Test Scenario 5: Dashboard Operations
1. View audit in dashboard
2. Filter by date, mode, URL
3. Search for keywords
4. Delete audit
5. **Verify:** All 4 files deleted from done/ folder
6. **Verify:** Audit removed from master-index.json

---

## 📊 Success Criteria

### Phase 1 (BusinessUnitManager) ✅
- [x] Path methods return todo/ and done/ paths
- [x] ensureDirectories() creates 6 paths
- [x] ensureReadmes() creates 2 README files
- [x] saveAuditReport() saves to todo/ folder
- [x] Filenames have unit_id prefix
- [x] Dropbox metadata added to JSON

### Phase 2 (Cron Scanner) ⏳
- [ ] Scans todo/ folder
- [ ] Atomic file move implemented
- [ ] Handles filename format with unit_id prefix
- [ ] Updates master-index.json with done/ paths
- [ ] Adds move timestamp to metadata
- [ ] Rollback on move failure
- [ ] Logs all move operations

### Phase 3 (Dashboard) ⏳
- [ ] BusinessUnitScanner discovers done/ folders
- [ ] AuditRepository reads from done/ folder
- [ ] Delete operations target done/ folder
- [ ] Handles filename format with unit_id prefix
- [ ] Timeline displays processed audits correctly
- [ ] Filtering/search works with new structure

### Phase 4 (Additional) ⏳
- [ ] .gitignore entries added
- [ ] README maintenance cron created
- [ ] README maintenance cron installed

### Phase 5 (Testing) ⏳
- [ ] All 5 test scenarios pass
- [ ] No errors in logs
- [ ] Performance acceptable (<5 min processing)

---

## 📁 Storage Structure (NEW)

```
/reports/
├── .gitignore                          ← PHASE 4
├── master-index.json                   ← Updated by PHASE 2
├── analytics.json                      ← Updated by PHASE 2
├── health.json                         ← Updated by PHASE 2
│
├── hdgwrzntwa/
│   ├── todo/                           ← PHASE 1 ✅
│   │   ├── README.md                   ← PHASE 1 ✅
│   │   ├── hdgwrzntwa_audit-1730050800.json
│   │   ├── hdgwrzntwa_audit-1730050800.html
│   │   ├── hdgwrzntwa_audit-1730050800.meta.json
│   │   └── screenshots/
│   │       └── hdgwrzntwa_audit-1730050800.png
│   │
│   └── done/                           ← PHASE 1 ✅
│       ├── README.md                   ← PHASE 1 ✅
│       ├── hdgwrzntwa_audit-1730050800.json  ← PHASE 2
│       ├── hdgwrzntwa_audit-1730050800.html
│       ├── hdgwrzntwa_audit-1730050800.meta.json
│       └── screenshots/
│           └── hdgwrzntwa_audit-1730050800.png
│
└── jcepnzzkmj/
    ├── todo/                           ← PHASE 1 ✅
    │   └── README.md                   ← PHASE 1 ✅
    └── done/                           ← PHASE 1 ✅
        └── README.md                   ← PHASE 1 ✅
```

---

## 🚀 Next Steps

### Immediate (Continue Implementation)
1. **Update cron-audit-scanner.js** (~30 min)
   - Change scan path to todo/
   - Implement atomic move function
   - Update filename parsing
   - Update index generation
   - Add error handling & rollback

2. **Update audit_reports.php** (~20 min)
   - Update BusinessUnitScanner discovery
   - Verify delete operations
   - Test with sample data

3. **Add .gitignore entries** (~2 min)
   - Append to existing .gitignore
   - Verify with `git status`

4. **Create README maintenance cron** (~15 min)
   - Write cron-readme-maintenance.js
   - Install cron job
   - Test execution

5. **End-to-end testing** (~20 min)
   - Run all 5 test scenarios
   - Document any issues
   - Fix and retest

### Future Enhancements
- [ ] Dashboard optimization with index files (10-100x faster)
- [ ] Automated cleanup of old audits (>30 days)
- [ ] Performance monitoring dashboard
- [ ] Audit comparison tool (before/after changes)

---

## 📝 Notes

### Why Dropbox Pattern?
- **Clear state:** Visual indicator of pending vs processed
- **Error recovery:** Failed processing leaves files in todo/
- **Audit trail:** File timestamps show creation vs processing time
- **No re-processing:** Once moved, never scanned again
- **Atomic operations:** All 4 files moved together
- **Human-friendly:** Team can see what's waiting
- **Bot-friendly:** Clear separation prevents confusion

### Breaking Changes
- **Path change:** audits/ → todo/ and done/
- **Scanner behavior:** Now moves files (was read-only)
- **Dashboard source:** Reads from done/ (was audits/)
- **Filename format:** Added unit_id prefix

### Migration Path
1. No migration needed for new installs
2. For existing systems:
   - Old audits/ folder will be ignored
   - New audits go to todo/ folder
   - Can manually move old audits to done/ if desired
   - Or leave old audits/ folder as archive

---

**Last Updated:** October 27, 2025 14:45 UTC  
**Status:** Phase 1 Complete ✅ | Phase 2-5 Pending ⏳  
**Estimated Time to Complete:** ~90 minutes remaining
