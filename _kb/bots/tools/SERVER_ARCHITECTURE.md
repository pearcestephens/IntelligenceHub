# 🏗️ Ecigdis Multi-Server Architecture

**Last Updated:** 2025-10-24  
**Total Servers:** 20+ applications across infrastructure  

---

## 🎯 Server Hierarchy & Roles

### 🧠 INTELLIGENCE HUB (This Server)
**Server ID:** `hdgwrzntwa`  
**Alias:** `mastergptcore` → hdgwrzntwa  
**Primary Role:** Central Intelligence & Knowledge Base System  
**URL:** https://hdgwrzntwa.cloudwaysapps.com

**Purpose:**
- Central knowledge extraction and intelligence generation
- Cross-server code analysis and reporting
- AI/GPT integration and conversation monitoring
- Consolidated documentation and guides
- Smart KB automation and cron management

**Key Features:**
- Analyzes code from OTHER servers (doesn't analyze itself)
- Generates intelligence reports for: jcepnzzkmj, fhrehrpjmu, dvaxgvsxmz
- Stores 0 production files (intelligence only)
- Syncs findings back to production servers

---

### 🏢 PRODUCTION SERVERS

#### 1. **CIS Production (Primary Business System)**
**Server ID:** `jcepnzzkmj`  
**Role:** Main ERP/Business Management System  
**Files:** ~14,390 PHP files  
**URL:** https://staff.vapeshed.co.nz

**Systems:**
- Purchase Order Management
- Stock Transfer Management
- Inventory Control
- Consignment Tracking
- Supplier Management
- HR & Payroll Integration
- Analytics & Reporting
- Vend/Lightspeed API Integration

**Intelligence Sources:**
- Receives analyzed intelligence from hdgwrzntwa
- Local KB for module documentation
- Cross-references with intelligence server

---

#### 2. **Production Server #2**
**Server ID:** `fhrehrpjmu`  
**Role:** Secondary Production System  
**Status:** Active  

---

#### 3. **Production Server #3**
**Server ID:** `dvaxgvsxmz`  
**Role:** Tertiary Production System  
**Status:** Active  

---

### 🛍️ E-COMMERCE SERVERS

#### **Vape Shed POS**
**Server ID:** `xavgdbkqxw`  
**Alias:** `vapeshed_pos` → xavgdbkqxw  
**Role:** Point of Sale System  

---

### 📸 MEDIA & CONTENT SERVERS

#### **NicShots**
**Server ID:** `wjgprnzauy`  
**Alias:** `nicshots` → wjgprnzauy  
**Role:** Media/Content Management  

---

### 👤 CLIENT SERVERS

#### **Sally**
**Server ID:** `nbqqcrvema`  
**Alias:** `sally` → nbqqcrvema  
**Role:** Client-specific Application  

#### **Yorvik Electrical**
**Server ID:** `qffgdcjdpq`  
**Alias:** `yorvik_electrical` → qffgdcjdpq  
**Role:** Client Project  

---

### 📦 OTHER SERVERS (Development/Legacy)

Additional servers in infrastructure:
- auwzaqszyd
- bjyvpezxum
- djnjbfhczr
- hxvhuzmvxu
- krjkptkdzv
- rkfkvhgvmz
- swdvywdfcs
- vdzyfydppv
- xwgxngcrpv

---

## 🔄 Intelligence Flow Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                   INTELLIGENCE HUB                          │
│                    (hdgwrzntwa)                             │
│                                                             │
│  📊 Analyzes Code                                           │
│  🔍 Detects Vulnerabilities                                │
│  ⚡ Finds Performance Issues                               │
│  📋 Maps Dependencies                                       │
│  🎯 Generates Reports                                       │
│                                                             │
└────────┬────────────┬────────────┬─────────────────────────┘
         │            │            │
         ▼            ▼            ▼
   ┌─────────┐  ┌─────────┐  ┌─────────┐
   │  CIS    │  │ Server  │  │ Server  │
   │jcepnzzkmj│  │fhrehrpjmu│  │dvaxgvsxmz│
   │         │  │         │  │         │
   │ 14,390  │  │ Files   │  │ Files   │
   │ Files   │  │         │  │         │
   └─────────┘  └─────────┘  └─────────┘
        │            │            │
        └────────────┴────────────┘
                     │
                     ▼
            ┌──────────────────┐
            │  Intelligence    │
            │  Reports Synced  │
            │  Back to Sources │
            └──────────────────┘
```

---

## 📊 Current Intelligence Metrics

### Intelligence Server (hdgwrzntwa)
```
Files Stored:          0 (intelligence only)
Scripts:               ~150+ analysis scripts
Intelligence DB:       MySQL with intelligence tables
Cron Jobs:             8+ automated tasks
KB Size:               ~500MB (reports, guides, analysis)
```

### Production Servers (Analyzed by hdgwrzntwa)
```
jcepnzzkmj:           14,390 PHP files analyzed
fhrehrpjmu:           [Files count TBD]
dvaxgvsxmz:           [Files count TBD]

Total Functions:      43,556 mapped
Total Classes:        3,883 cataloged
Total APIs:           329 documented
Security Issues:      2,414 identified
Performance Issues:   4,030 bottlenecks
Duplicate Blocks:     197,823 detected
TODO Items:           350 prioritized
```

---

## 🎯 Intelligence System Architecture

### On Intelligence Server (hdgwrzntwa)

#### **Extraction Layer**
```
/scripts/kb_intelligence_engine.php       → Basic intelligence
/scripts/kb_intelligence_engine_v2.php    → Enhanced incremental
/scripts/kb_deep_intelligence.php         → Deep analysis
/scripts/enhanced_security_scanner.php    → Security scanning
```

#### **Storage Layer**
```
/_kb/intelligence/                        → Generated reports
/_kb/deep_intelligence/                   → Deep analysis results
/_kb/cache/                              → Performance cache
/_kb/snapshots/                          → Historical backups
/_kb/conversations/                      → AI interaction logs
```

#### **Automation Layer**
```
Cron: 0 */4 * * *  → Refresh intelligence
Cron: 0 2 * * *    → Deep analysis
Cron: 0 3 * * 0    → Weekly cleanup
```

#### **Sync Layer**
```
/scripts/sync_intelligence.sh             → Cross-server sync
/scripts/deploy_lightweight_kb_*.sh       → KB deployment
```

---

### On Production Servers (e.g., jcepnzzkmj)

#### **Consumption Layer**
```
/_kb/                                    → Local KB documentation
/_kb/synced_intelligence/                → Intelligence from hdgwrzntwa
/_kb/guides/                             → Module guides
```

#### **Local Intelligence**
```
Module READMEs                           → Developer docs
API Documentation                        → Endpoint specs
Architecture Diagrams                    → System design
```

---

## 🔐 Security & Access Control

### Intelligence Server Access
- **Who:** Developers, AI assistants, automation scripts
- **What:** Read-only access to intelligence reports
- **How:** SSH, API endpoints, file sync

### Production Server Access
- **Who:** Authorized developers only
- **What:** Full development access
- **Protection:** Intelligence server can READ but not MODIFY production

---

## 🚀 Intelligence Enhancement Strategy

### Current Optimization (Phase 1-2)
✅ **hdgwrzntwa (Intelligence Hub)**
- Enhanced incremental analysis (v2.0)
- Improved security scanning with confidence scores
- False positive reduction
- Performance profiling
- Memory optimization

### Future Expansion (Phase 3-4)
🔄 **Cross-Server Intelligence**
- Unified intelligence API
- Real-time file watching across servers
- Distributed analysis for large codebases
- ML-based issue prioritization
- Automated fix suggestions synced to production

---

## 📋 Server Management Commands

### View All Servers
```bash
ls -la /home/master/applications/
```

### Check Intelligence Server Status
```bash
# On hdgwrzntwa
cd /home/master/applications/hdgwrzntwa/public_html
tail -50 _kb/logs/kb_refresh_$(date +%Y%m%d).log
```

### Check Production Server (CIS)
```bash
# On jcepnzzkmj
cd /home/master/applications/jcepnzzkmj/public_html
ls -la _kb/synced_intelligence/
```

### Sync Intelligence Across Servers
```bash
# From hdgwrzntwa
php /home/master/applications/hdgwrzntwa/public_html/scripts/sync_intelligence.sh
```

---

## 🎓 Key Principles

### 1. **Separation of Concerns**
- Intelligence Server → Analysis only
- Production Servers → Business logic only
- Clear boundaries, no mixing

### 2. **Unidirectional Intelligence Flow**
- hdgwrzntwa READS from production
- hdgwrzntwa WRITES reports
- Production CONSUMES reports
- No circular dependencies

### 3. **Safety First**
- Intelligence server never modifies production code
- All analysis is read-only
- Changes must be manually applied by developers

### 4. **Scalability**
- Add new production servers easily
- Intelligence system adapts automatically
- Distributed processing for performance

---

## 🔍 Server Identification Quick Reference

| Server Code | Alias | Primary Role |
|-------------|-------|--------------|
| **hdgwrzntwa** | mastergptcore | 🧠 Intelligence Hub |
| **jcepnzzkmj** | - | 🏢 CIS Production (Main) |
| **fhrehrpjmu** | - | 🏢 Production Server #2 |
| **dvaxgvsxmz** | - | 🏢 Production Server #3 |
| **xavgdbkqxw** | vapeshed_pos | 🛍️ POS System |
| **wjgprnzauy** | nicshots | 📸 Media Server |
| **nbqqcrvema** | sally | 👤 Client Project |
| **qffgdcjdpq** | yorvik_electrical | 👤 Client Project |

---

## 📊 Intelligence Data Flow

### 1. **Extraction** (hdgwrzntwa)
```
Production Servers → Intelligence Scripts → Analysis
```

### 2. **Processing** (hdgwrzntwa)
```
Raw Data → Security Scan → Performance Analysis → Reports
```

### 3. **Storage** (hdgwrzntwa)
```
Reports → /_kb/intelligence/ → /_kb/deep_intelligence/
```

### 4. **Distribution** (hdgwrzntwa → production)
```
Intelligence Reports → Sync Script → Production /_kb/synced/
```

### 5. **Consumption** (production servers)
```
Developers → Read Reports → Apply Fixes → Commit Code
```

---

## 🎯 Why This Architecture?

### ✅ Advantages
1. **Centralized Intelligence** - One source of truth
2. **Reduced Server Load** - Analysis runs on dedicated server
3. **Consistent Analysis** - Same tools across all servers
4. **Easy Scaling** - Add servers without duplication
5. **Historical Tracking** - Snapshots and trends over time
6. **AI Integration** - Centralized conversation and learning

### 🎯 Current Focus
- **hdgwrzntwa:** Optimize intelligence extraction (v2.0 deployed)
- **Production Servers:** Consume and act on intelligence
- **Cross-Server Sync:** Ensure timely intelligence delivery

---

## 🚦 Status Dashboard

### Intelligence Server (hdgwrzntwa)
```
Status:              ✅ Operational
Last Intelligence:   2025-10-24 20:00 UTC
Files Analyzed:      3,606 on this server
Production Scanned:  14,390+ files across servers
Cron Jobs:           ✅ Running every 4 hours
Enhancement:         🔄 v2.0 deployed, testing in progress
```

### Production Servers
```
jcepnzzkmj (CIS):    ✅ Operational, receiving intelligence
fhrehrpjmu:          ✅ Operational
dvaxgvsxmz:          ✅ Operational
```

---

**Remember:** You're working on **hdgwrzntwa** - the Intelligence Hub. Your role is to make the intelligence extraction smarter, faster, and more comprehensive for ALL production servers! 🧠🚀

---

*Ecigdis Multi-Server Infrastructure*  
*Powered by Centralized Intelligence*
