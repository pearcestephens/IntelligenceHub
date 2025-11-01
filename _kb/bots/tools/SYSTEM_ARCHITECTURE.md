# 🏗️ Ecigdis Enterprise AI Command Center - System Architecture

**Version:** 1.0.0  
**Last Updated:** October 21, 2025  
**Architect:** Enterprise Design Team

---

## Table of Contents

1. [Architecture Philosophy](#architecture-philosophy)
2. [System Layers](#system-layers)
3. [Centralized Hub Design](#centralized-hub-design)
4. [Multi-Tenant Architecture](#multi-tenant-architecture)
5. [Data Flow](#data-flow)
6. [Integration Architecture](#integration-architecture)
7. [Security Architecture](#security-architecture)
8. [Scalability Design](#scalability-design)
9. [Technology Stack](#technology-stack)
10. [Deployment Architecture](#deployment-architecture)

---

## 🎯 Architecture Philosophy

### Core Principles

1. **Centralization with Distributed Access**
   - Single source of truth (gpt.ecigdis.co.nz)
   - API-driven access from all sites
   - Consistent data across ecosystem

2. **Multi-Tenant by Design**
   - 5 business units coexist
   - Data isolation at business unit level
   - Shared infrastructure, segregated data

3. **API-First Approach**
   - All functionality exposed via REST APIs
   - Versioned endpoints
   - Backward compatibility guaranteed

4. **AI-Powered Intelligence**
   - Neural knowledge base
   - Predictive analytics
   - Context-aware responses

5. **Real-Time Everything**
   - Live chat across all sites
   - Real-time metrics and alerts
   - Event-driven architecture

---

## 🧱 System Layers

### Layer 1: Data Layer
```
┌─────────────────────────────────────────────┐
│           MySQL Database (hdgwrzntwa)       │
│                                             │
│  ecig_kb_*    │  Knowledge Base Tables     │
│  ecig_bi_*    │  Business Intelligence     │
│  ecig_ai_*    │  AI Agent Infrastructure   │
│  ecig_chat_*  │  Live Chat System          │
│  ecig_api_*   │  API Gateway               │
└─────────────────────────────────────────────┘
```

**Responsibilities:**
- Data persistence
- Relationships and integrity
- Transaction management
- Query optimization

### Layer 2: Business Logic Layer
```
┌─────────────────────────────────────────────┐
│         PHP Application Layer               │
│                                             │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐ │
│  │   KB     │  │    BI    │  │    AI    │ │
│  │ Services │  │ Services │  │ Services │ │
│  └──────────┘  └──────────┘  └──────────┘ │
│                                             │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐ │
│  │   Chat   │  │   API    │  │  Auth    │ │
│  │ Services │  │ Services │  │ Services │ │
│  └──────────┘  └──────────┘  └──────────┘ │
└─────────────────────────────────────────────┘
```

**Responsibilities:**
- Business rule enforcement
- Service orchestration
- Domain logic
- Event handling

### Layer 3: API Gateway Layer
```
┌─────────────────────────────────────────────┐
│            API Gateway                      │
│                                             │
│  Authentication  │  Rate Limiting           │
│  Authorization   │  Request Routing         │
│  Validation      │  Response Formatting     │
│  Logging         │  Error Handling          │
└─────────────────────────────────────────────┘
```

**Responsibilities:**
- API key validation
- Request throttling
- Route management
- Unified error responses

### Layer 4: Presentation Layer
```
┌─────────────────────────────────────────────┐
│         Web Interface / Dashboard           │
│                                             │
│  Vue.js  │  Charts  │  Real-time Updates   │
│  Tables  │  Forms   │  Interactive Widgets │
└─────────────────────────────────────────────┘
```

**Responsibilities:**
- User interface rendering
- Data visualization
- Real-time updates (WebSockets)
- Responsive design

---

## 🌐 Centralized Hub Design

### Why Centralization?

**Problems with Distributed Systems:**
- ❌ Data inconsistency across sites
- ❌ Duplicate logic in multiple places
- ❌ Difficult to maintain and update
- ❌ No single view of business metrics
- ❌ Complex integration patterns

**Benefits of Centralized Hub:**
- ✅ Single source of truth
- ✅ Consistent business logic
- ✅ Easier maintenance and updates
- ✅ Unified analytics and reporting
- ✅ Simple API integration pattern

### Hub Architecture

```
                gpt.ecigdis.co.nz
                    (THE HUB)
                        │
        ┌───────────────┼───────────────┐
        │               │               │
        ▼               ▼               ▼
    Vape Shed      Vaping Kiwi     Wholesale
        │               │               │
        └───────────────┴───────────────┘
                        │
                Knowledge Base
                Business Intelligence
                AI Agents
                Live Chat
                API Services
```

### Hub Components

1. **Knowledge Base Hub**
   - All technical documentation
   - Code intelligence
   - File relationships
   - Component registry

2. **Business Intelligence Hub**
   - Cross-business analytics
   - Multi-tenant metrics
   - Executive dashboards
   - Predictive insights

3. **AI Agent Hub**
   - Bot management
   - Model training
   - Conversation history
   - Deployment control

4. **Live Chat Hub**
   - Universal chat widget
   - Agent routing
   - Message history
   - Analytics

5. **API Gateway Hub**
   - Authentication
   - Rate limiting
   - Request routing
   - Webhook management

---

## 🏢 Multi-Tenant Architecture

### Business Unit Structure

```
Ecigdis Limited (Parent)
├── The Vape Shed (Retail - 17 stores)
│   └── Domains: Retail, POS, Inventory, CRM, Marketing
├── The Vaping Kiwi (E-commerce)
│   └── Domains: E-commerce, Shipping, Customer Service
├── VapeHQ (E-commerce)
│   └── Domains: E-commerce, Marketing, Affiliates
├── Ecigdis Wholesale (B2B)
│   └── Domains: Wholesale, Order Management, Logistics
└── Juice Manufacturing (Production)
    └── Domains: Production, Quality Control, Supply Chain
```

### Tenant Isolation

**Database Level:**
```sql
-- Every table has business_unit_id
CREATE TABLE ecig_bi_metrics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    business_unit_id INT NOT NULL,  -- Tenant identifier
    domain VARCHAR(100),
    metric_name VARCHAR(100),
    value DECIMAL(15,2),
    FOREIGN KEY (business_unit_id) REFERENCES ecig_bi_business_units(id),
    INDEX idx_tenant (business_unit_id)
);
```

**Application Level:**
```php
// Every query filtered by tenant
$metrics = $db->query("
    SELECT * FROM ecig_bi_metrics 
    WHERE business_unit_id = ? 
    AND domain = ?
", [$tenantId, $domain]);
```

**API Level:**
```http
GET /api/v1/bi/metrics
Authorization: Bearer {api_key}
X-Business-Unit: vapeshed  # Tenant specified in header
```

### Tenant Benefits

- **Data Isolation** - Each business unit's data separate
- **Independent Scaling** - Scale per business unit needs
- **Granular Permissions** - Staff access limited to their unit
- **Cross-Tenant Analytics** - GOD tier can view all units
- **Easy Onboarding** - Add new business units without code changes

---

## 🔄 Data Flow

### Request Flow (External Site → Hub)

```
1. Customer Action (www.vapeshed.co.nz)
   ├─→ User clicks chat widget
   │
2. API Request
   ├─→ POST https://gpt.ecigdis.co.nz/api/v1/chat/start
   │   Headers:
   │   - Authorization: Bearer {api_key}
   │   - X-Site-ID: vapeshed
   │   - X-Customer-ID: 12345
   │
3. API Gateway (gpt.ecigdis.co.nz)
   ├─→ Validate API key
   ├─→ Check rate limit
   ├─→ Route to Chat Service
   │
4. Chat Service
   ├─→ Create chat session
   ├─→ Assign AI agent
   ├─→ Load customer context from BI
   │
5. AI Agent
   ├─→ Generate response
   ├─→ Check KB for product info
   ├─→ Return personalized answer
   │
6. Response
   ├─→ Store in ecig_chat_messages
   ├─→ Log in ecig_api_logs
   ├─→ Return JSON to site
   │
7. Site Updates
   └─→ Display message in chat widget
```

### Real-Time Events Flow

```
1. Database Change (e.g., new order)
   │
2. Trigger Event
   ├─→ ecig_bi_events table
   │
3. Webhook Manager
   ├─→ Check subscriptions
   ├─→ Find listening sites
   │
4. HTTP POST to Subscribers
   ├─→ POST https://staff.vapeshed.co.nz/webhooks/order-created
   ├─→ POST https://www.vapeshed.co.nz/webhooks/order-created
   │
5. Sites Process Event
   └─→ Update local cache
   └─→ Trigger notifications
   └─→ Update UI
```

### BI Metrics Collection Flow

```
1. Business Activity (any site)
   ├─→ Sale completed
   ├─→ Product viewed
   ├─→ Customer registered
   │
2. API Call to Hub
   ├─→ POST /api/v1/bi/event
   ├─→ Body: {
   │     "business_unit": "vapeshed",
   │     "domain": "retail",
   │     "event_type": "sale",
   │     "value": 125.50,
   │     "metadata": {...}
   │   }
   │
3. BI Service (gpt.ecigdis.co.nz)
   ├─→ Validate event
   ├─→ Store in ecig_bi_events
   ├─→ Update ecig_bi_metrics
   ├─→ Check alert thresholds
   │
4. Alert Processing
   ├─→ If threshold exceeded
   ├─→ Create alert in ecig_bi_alerts
   ├─→ Notify stakeholders
   │
5. Dashboard Update
   └─→ Real-time chart update via WebSocket
```

---

## 🔌 Integration Architecture

### Integration Patterns

#### 1. REST API Integration (Primary)
```
Site → HTTPS → API Gateway → Service → Database
                     ↓
              Rate Limiter
              Auth Validator
              Request Logger
```

**Use Cases:**
- Chat widget integration
- BI metric submission
- KB queries
- AI agent interactions

#### 2. Webhook Integration (Events)
```
Database Event → Webhook Manager → HTTP POST → External Sites
                                              ↓
                                    Retry Logic (3 attempts)
                                    Failure Queue
```

**Use Cases:**
- Order notifications
- Inventory updates
- Alert distribution
- Real-time sync

#### 3. JavaScript Widget Integration (Chat)
```html
<!-- Embedded on any site -->
<script src="https://gpt.ecigdis.co.nz/widgets/chat.js"></script>
<script>
  EcigChat.init({
    apiKey: 'your_api_key',
    siteId: 'vapeshed',
    position: 'bottom-right'
  });
</script>
```

**Features:**
- Automatic connection
- Context-aware AI
- Customer history
- Typing indicators

---

## 🔐 Security Architecture

### Authentication Layers

#### 1. API Key Authentication
```
Request Header:
Authorization: Bearer {api_key}

Validation:
1. Check if key exists in ecig_api_keys
2. Check if key is active
3. Check expiration date
4. Verify site_id matches
5. Log access
```

#### 2. Role-Based Access Control (RBAC)
```
Roles Hierarchy:
GOD (Level 1000)
  └─> Full system access
  └─> Cross-tenant visibility
  └─> System configuration

SUPER ADMIN (Level 500)
  └─> Business unit management
  └─> Advanced features
  └─> Staff management

ADMIN (Level 100)
  └─> Domain management
  └─> Reports access
  └─> Basic configuration

STAFF (Level 10)
  └─> Read-only access
  └─> Limited features
  └─> No configuration

CUSTOMER (Level 1)
  └─> Chat access only
  └─> Personal data view
  └─> No backend access
```

#### 3. Network Security
- **HTTPS Enforced** - All traffic encrypted
- **CORS Configured** - Only allowed domains
- **Rate Limiting** - Prevent abuse
- **IP Whitelisting** - Optional for sensitive endpoints
- **DDoS Protection** - Cloudflare or similar

### Security Measures

```
Request → ┌──────────────────┐
          │  SSL/TLS Layer   │
          └────────┬─────────┘
                   ↓
          ┌──────────────────┐
          │  Firewall Rules  │
          └────────┬─────────┘
                   ↓
          ┌──────────────────┐
          │  Rate Limiter    │
          └────────┬─────────┘
                   ↓
          ┌──────────────────┐
          │  Auth Validator  │
          └────────┬─────────┘
                   ↓
          ┌──────────────────┐
          │  Input Sanitizer │
          └────────┬─────────┘
                   ↓
          ┌──────────────────┐
          │  Business Logic  │
          └────────┬─────────┘
                   ↓
          ┌──────────────────┐
          │  Audit Logger    │
          └──────────────────┘
```

---

## 📈 Scalability Design

### Horizontal Scaling Strategy

```
Load Balancer
     │
     ├─→ App Server 1 (PHP-FPM)
     ├─→ App Server 2 (PHP-FPM)
     ├─→ App Server 3 (PHP-FPM)
     │
     └─→ Database (MySQL Master)
          └─→ Read Replica 1
          └─→ Read Replica 2
```

### Database Scaling

**Read/Write Splitting:**
```php
// Writes go to master
$db->master()->insert('ecig_bi_events', $data);

// Reads from replicas
$metrics = $db->replica()->select('ecig_bi_metrics', $conditions);
```

**Partitioning Strategy:**
```sql
-- Partition large tables by date
CREATE TABLE ecig_bi_events (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    business_unit_id INT,
    event_date DATE,
    ...
) PARTITION BY RANGE (YEAR(event_date)) (
    PARTITION p2024 VALUES LESS THAN (2025),
    PARTITION p2025 VALUES LESS THAN (2026),
    PARTITION p2026 VALUES LESS THAN (2027)
);
```

### Caching Strategy

```
Request → ┌─────────────┐
          │ Redis Cache │ ← Hit? Return cached
          └──────┬──────┘
                 │ Miss
                 ↓
          ┌─────────────┐
          │  Database   │
          └──────┬──────┘
                 │
                 ├─→ Cache result
                 └─→ Return data
```

**Cache Keys:**
```
kb:file:{file_path}
bi:metrics:{business_unit}:{domain}:{date}
ai:conversation:{session_id}
chat:history:{user_id}
```

---

## 💻 Technology Stack

### Backend
- **Language:** PHP 8.1+
- **Framework:** Custom MVC
- **Database:** MySQL/MariaDB 10.5+
- **Cache:** Redis 6+
- **Queue:** MySQL-backed job queue

### Frontend
- **Framework:** Vue.js 3
- **State Management:** Pinia
- **UI Components:** Bootstrap 5 + Custom
- **Charts:** Chart.js + D3.js
- **Real-time:** Socket.io client

### Infrastructure
- **Web Server:** Apache 2.4 / Nginx
- **PHP Runtime:** PHP-FPM
- **SSL:** Let's Encrypt
- **CDN:** Cloudflare
- **Monitoring:** Custom + External

### Development Tools
- **Version Control:** Git
- **Dependency Management:** Composer + NPM
- **Code Quality:** PHP_CodeSniffer
- **Testing:** PHPUnit
- **Documentation:** Markdown

---

## 🚀 Deployment Architecture

### Production Environment

```
┌─────────────────────────────────────────┐
│         Cloudways Server                │
│                                         │
│  ┌─────────────────────────────────┐   │
│  │  Apache / Nginx                 │   │
│  │  SSL (Let's Encrypt)            │   │
│  └────────────┬────────────────────┘   │
│               ↓                         │
│  ┌─────────────────────────────────┐   │
│  │  PHP-FPM 8.1                    │   │
│  │  - Opcache enabled              │   │
│  │  - APCu for user cache          │   │
│  └────────────┬────────────────────┘   │
│               ↓                         │
│  ┌─────────────────────────────────┐   │
│  │  Application                    │   │
│  │  /public_html/                  │   │
│  │  /private_html/                 │   │
│  └────────────┬────────────────────┘   │
│               ↓                         │
│  ┌─────────────────────────────────┐   │
│  │  MySQL Database                 │   │
│  │  hdgwrzntwa                     │   │
│  └─────────────────────────────────┘   │
└─────────────────────────────────────────┘
```

### Directory Structure

```
/home/master/applications/hdgwrzntwa/
├── public_html/              # Web accessible
│   ├── api/                  # API endpoints
│   ├── widgets/              # JavaScript widgets
│   ├── assets/               # CSS, JS, images
│   ├── docs/                 # This documentation
│   └── index.php             # Entry point
│
├── private_html/             # Not web accessible
│   ├── services/             # Business logic
│   ├── models/               # Data models
│   ├── config/               # Configuration
│   ├── logs/                 # Application logs
│   └── cache/                # File-based cache
│
└── tmp/                      # Temporary files
    ├── uploads/
    ├── exports/
    └── backups/
```

---

## 🔄 Continuous Integration

### Deployment Pipeline

```
1. Code Push
   ├─→ Git commit
   │
2. Automated Tests
   ├─→ PHPUnit tests
   ├─→ Code style check
   │
3. Build Process
   ├─→ Composer install
   ├─→ NPM build
   ├─→ Asset compilation
   │
4. Deploy to Staging
   ├─→ Database migrations
   ├─→ File sync
   ├─→ Cache clear
   │
5. Automated Testing
   ├─→ Integration tests
   ├─→ API tests
   │
6. Manual Approval
   │
7. Deploy to Production
   ├─→ Backup database
   ├─→ Database migrations
   ├─→ File sync
   ├─→ Cache clear
   ├─→ Restart services
   │
8. Health Check
   └─→ Verify all services running
```

---

## 📊 Monitoring & Observability

### Metrics Tracked

```
System Metrics:
- CPU usage
- Memory usage
- Disk space
- Network I/O

Application Metrics:
- Request rate
- Response time (p50, p95, p99)
- Error rate
- Active sessions

Business Metrics:
- API calls per site
- Chat sessions per hour
- BI queries per day
- AI agent usage
```

### Logging Strategy

```
Logs Structure:
/private_html/logs/
├── application.log       # General application logs
├── api_access.log        # All API requests
├── api_errors.log        # API errors
├── bi_events.log         # BI metric submissions
├── chat_sessions.log     # Chat activity
├── security.log          # Security events
└── cron.log              # Scheduled tasks
```

---

## 🎯 Design Decisions

### Why This Architecture?

**Decision 1: Centralized Hub**
- **Reason:** Single source of truth, easier maintenance
- **Trade-off:** Single point of failure (mitigated with HA setup)

**Decision 2: Multi-Tenant**
- **Reason:** Multiple business units, data isolation needed
- **Trade-off:** More complex queries (mitigated with proper indexing)

**Decision 3: API-First**
- **Reason:** Flexibility for future integrations
- **Trade-off:** Network overhead (mitigated with caching)

**Decision 4: MySQL vs NoSQL**
- **Reason:** Strong relationships, ACID compliance critical
- **Trade-off:** Harder to scale horizontally (mitigated with read replicas)

**Decision 5: Monolithic vs Microservices**
- **Reason:** Team size, deployment simplicity
- **Trade-off:** Less flexible scaling (acceptable for current scale)

---

## 📖 References

- [Database Schema](../database/COMPLETE_SCHEMA.md)
- [API Reference](../api/API_REFERENCE.md)
- [Deployment Guide](../deployment/DEPLOYMENT_GUIDE.md)
- [Security Guide](../knowledge-base/security/SECURITY_GUIDE.md)

---

**Last Updated:** October 21, 2025  
**Version:** 1.0.0  
**Status:** Production Ready
